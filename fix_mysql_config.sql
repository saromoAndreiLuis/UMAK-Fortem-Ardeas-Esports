-- MySQL Configuration Fix for Large Image Uploads
-- Run this script in your MySQL client (phpMyAdmin, MySQL Workbench, etc.)

-- Set max_allowed_packet to 16MB (16777216 bytes)
SET GLOBAL max_allowed_packet = 16777216;

-- Verify the setting
SHOW VARIABLES LIKE 'max_allowed_packet';

-- Alternative: Set to 32MB if you need larger images
-- SET GLOBAL max_allowed_packet = 33554432;

-- Note: This setting will reset when MySQL restarts
-- To make it permanent, add this line to your my.ini or my.cnf file:
-- max_allowed_packet = 16M 