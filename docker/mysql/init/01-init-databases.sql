-- Crear las bases de datos necesarias para la aplicación
-- DB_DATABASE (mysql) - localhost
CREATE DATABASE IF NOT EXISTS `u546175344_cartera` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- DB_DATABASE2 (mysql2) - localhost
CREATE DATABASE IF NOT EXISTS `u546175344_intesa` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Otorgar permisos al usuario root
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%';
FLUSH PRIVILEGES;

