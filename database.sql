CREATE DATABASE IF NOT EXISTS licitaciones_tjaech CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE licitaciones_tjaech;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'editor') DEFAULT 'editor',
    estatus ENUM('activo', 'inactivo') DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE licitaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_licitacion VARCHAR(50) NOT NULL UNIQUE,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    tipo_procedimiento VARCHAR(100) NOT NULL,
    anio YEAR NOT NULL,
    fecha_publicacion DATE NOT NULL,
    fecha_limite DATE NOT NULL,
    area_responsable VARCHAR(150) NOT NULL,
    pdf_principal VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    estatus ENUM('borrador', 'publicado') DEFAULT 'borrador',
    creado_por INT,
    actualizado_por INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (actualizado_por) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE TABLE anexos_licitacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    licitacion_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (licitacion_id) REFERENCES licitaciones(id) ON DELETE CASCADE
);

CREATE TABLE bitacora (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    accion VARCHAR(100) NOT NULL,
    entidad VARCHAR(50) NOT NULL,
    entidad_id INT,
    detalles TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Insert admin provisional (password: admin123 -> you must change this in production)
-- Hash generated from password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO usuarios (nombre, email, password, rol, estatus) VALUES 
('Administrador Informática', 'informatica@tjaech.gob.mx', '$2y$10$wN9iQO9O.8aB2kH/yJt7rOk0N6.bA2H1q4fR7.P/.S.HjE.6GzO8e', 'admin', 'activo');
