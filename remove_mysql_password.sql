-- Run this in MySQL command line to remove root password
-- First login with: mysql -u root -p
-- Then paste these commands:

ALTER USER 'root'@'localhost' IDENTIFIED BY '';
FLUSH PRIVILEGES;
