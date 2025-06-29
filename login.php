<?php
// Security headers to prevent inspection and attacks
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self'");

// Disable error reporting in production
error_reporting(0);
ini_set('display_errors', 0);

// Start session for login management
session_start();

// Check if user is already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: admin_events.php');
    exit();
}

$error_message = '';
$success_message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Include database connection
        if (file_exists('database_connection.php')) {
            include 'database_connection.php';
            
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            
            // Basic validation
            if (empty($username) || empty($password)) {
                $error_message = 'Please enter both username and password.';
            } else {
                // Check credentials against database
                $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ? AND status = 'active' LIMIT 1");
                $stmt->execute([$username]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password_hash'])) {
                    // Login successful
                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['login_time'] = time();
                    
                    // Regenerate session ID for security
                    session_regenerate_id(true);
                    
                    header('Location: admin_events.php');
                    exit();
                } else {
                    $error_message = 'Invalid username or password.';
                }
            }
        } else {
            $error_message = 'System configuration error.';
        }
    } catch (Exception $e) {
        // Don't expose error details to users
        $error_message = 'Login service temporarily unavailable.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UMAK Fortem Ardeas Esports</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: #0f172a;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            /* Disable text selection and right-click */
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        /* Hide elements from inspect */
        .security-hidden {
            display: none !important;
        }

        .login-container {
            background-color: #1e293b;
            padding: 2rem 3rem;
            border-radius: 0.75rem;
            box-shadow: 0 0 20px rgba(0, 238, 255, 0.3);
            width: 100%;
            max-width: 400px;
            position: relative;
            overflow: hidden;
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 238, 255, 0.1), transparent);
            transition: left 0.5s;
        }
        
        .login-container:hover::before {
            left: 100%;
        }

        h1 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #facc15;
            font-size: 2rem;
            text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 1;
        }

        .form-group {
            margin-bottom: 1rem;
            position: relative;
        }

        input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #334155;
            border-radius: 0.5rem;
            background-color: #334155;
            color: #fff;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            z-index: 1;
        }
        
        input:focus {
            outline: none;
            border-color: #06b6d4;
            box-shadow: 0 0 10px rgba(6, 182, 212, 0.3);
        }
        
        input::placeholder {
            color: #9ca3af;
        }

        button {
            width: 100%;
            padding: 0.75rem;
            margin-top: 1rem;
            background: linear-gradient(135deg, #c4b61b, #06b6d4);
            border: none;
            border-radius: 0.5rem;
            color: #000;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }

        button:hover {
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(6, 182, 212, 0.4);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        .error-message {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .success-message {
            background-color: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .back-link {
            text-align: center;
            margin-top: 1rem;
        }
        
        .back-link a {
            color: #60a5fa;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }
        
        .back-link a:hover {
            color: #3b82f6;
        }

        @media (max-width: 768px) {
            .login-container {
                padding: 2rem;
                margin: 1rem;
            }

            h1 {
                font-size: 1.75rem;
            }

            input,
            button {
                font-size: 0.95rem;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 1.5rem;
            }

            h1 {
                font-size: 1.5rem;
            }

            input,
            button {
                font-size: 0.9rem;
                padding: 0.65rem;
            }
        }
    </style>
</head>
<body oncontextmenu="return false;" onselectstart="return false;" oncopy="return false;">
    <div class="login-container">
        <h1>Login</h1>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="login.php">
            <div class="form-group">
                <input type="text" name="username" placeholder="Username" required autocomplete="username">
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
            </div>
            <button type="submit">Login</button>
        </form>
        
        <div class="back-link">
            <a href="index.php">← Back to Home</a>
        </div>
    </div>

    <script>
        // Security measures to prevent inspection
        (function() {
            'use strict';
            
            // Disable F12, Ctrl+Shift+I, Ctrl+U, Ctrl+Shift+C
            document.addEventListener('keydown', function(e) {
                if (e.key === 'F12' || 
                    (e.ctrlKey && e.shiftKey && e.key === 'I') ||
                    (e.ctrlKey && e.key === 'u') ||
                    (e.ctrlKey && e.shiftKey && e.key === 'C')) {
                    e.preventDefault();
                    return false;
                }
            });
            
            // Disable right-click context menu
            document.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                return false;
            });
            
            // Disable text selection
            document.addEventListener('selectstart', function(e) {
                e.preventDefault();
                return false;
            });
            
            // Disable copy
            document.addEventListener('copy', function(e) {
                e.preventDefault();
                return false;
            });
            
            // Disable drag and drop
            document.addEventListener('dragstart', function(e) {
                e.preventDefault();
                return false;
            });
            
            // Clear console on page load
            console.clear();
            
            // Override console methods
            console.log = function() {};
            console.info = function() {};
            console.warn = function() {};
            console.error = function() {};
            console.debug = function() {};
            
            // Disable developer tools detection
            setInterval(function() {
                const devtools = {
                    open: false,
                    orientation: null
                };
                
                const threshold = 160;
                
                if (window.outerHeight - window.innerHeight > threshold || 
                    window.outerWidth - window.innerWidth > threshold) {
                    devtools.open = true;
                    document.body.innerHTML = '<div style="text-align:center;padding:50px;color:white;">Access Denied</div>';
                }
            }, 1000);
            
            // Additional security: Clear form data on page unload
            window.addEventListener('beforeunload', function() {
                const forms = document.querySelectorAll('form');
                forms.forEach(form => {
                    const inputs = form.querySelectorAll('input');
                    inputs.forEach(input => {
                        if (input.type === 'password') {
                            input.value = '';
                        }
                    });
                });
            });
            
        })();
    </script>
</body>
</html>