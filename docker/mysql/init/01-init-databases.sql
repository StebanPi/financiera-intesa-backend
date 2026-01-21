-- Crear las bases de datos necesarias para la aplicación
-- DB_DATABASE (mysql) - localhost
CREATE DATABASE IF NOT EXISTS `u546175344_cartera` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- DB_DATABASE2 (mysql2) - localhost
CREATE DATABASE IF NOT EXISTS `u546175344_intesa` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Nota: DB_DATABASE3 y DB_DATABASE4 apuntan al servidor remoto 193.160.64.3
-- Si necesitas que también estén en Docker local, descomenta las siguientes líneas:
-- CREATE DATABASE IF NOT EXISTS `u546175344_cartera_remote` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- CREATE DATABASE IF NOT EXISTS `u546175344_intesa_remote` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Otorgar permisos al usuario root
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%';
FLUSH PRIVILEGES;

