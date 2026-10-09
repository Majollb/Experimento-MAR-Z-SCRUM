CREATE DATABASE IF NOT EXISTS scrum_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE scrum_db;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('Admin', 'Solicitante', 'Coordinador', 'Agente', 'Auditor') DEFAULT 'Solicitante',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS solicitudes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    estado ENUM('Nuevo', 'En Proceso', 'Resuelta', 'Cerrada') DEFAULT 'Nuevo',
    prioridad ENUM('Alta', 'Media', 'Baja') NOT NULL DEFAULT 'Media',
    usuario_id INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS historial_prioridad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    solicitud_id INT NOT NULL,
    usuario_id INT NOT NULL,
    prioridad_anterior ENUM('Alta', 'Media', 'Baja') NOT NULL,
    prioridad_nueva ENUM('Alta', 'Media', 'Baja') NOT NULL,
    fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (solicitud_id) REFERENCES solicitudes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

INSERT INTO usuarios (nombre, email, password, rol) 
VALUES ('Administrador', 'admin@sistema.com', '$2y$10$w09uJ0UvD7fJ.Xk7H3g23e0QYyVjHkL0eD5V1ZgP8u7Y8X9W0Z1a2', 'Admin')
ON DUPLICATE KEY UPDATE id=id;
