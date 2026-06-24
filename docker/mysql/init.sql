CREATE DATABASE IF NOT EXISTS eventboard_laravel
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON eventboard_laravel.* TO 'eventboard'@'%';

FLUSH PRIVILEGES;
