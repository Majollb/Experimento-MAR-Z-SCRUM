<?php


$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'scrum_db';

try {
    
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE $dbname;");

   
    $queryUsuarios = "CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        rol ENUM('Admin', 'Solicitante', 'Coordinador', 'Agente', 'Auditor') DEFAULT 'Solicitante',
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";
    $pdo->exec($queryUsuarios);

    try {
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN estado ENUM('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo' AFTER rol");
    } catch (PDOException $e) {
        // La columna ya existe.
    }

    
    $querySolicitudes = "CREATE TABLE IF NOT EXISTS solicitudes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titulo VARCHAR(150) NOT NULL,
        descripcion TEXT NOT NULL,
        categoria VARCHAR(50) NOT NULL,
        estado ENUM('Nuevo', 'En Proceso', 'Resuelta', 'Cerrada') DEFAULT 'Nuevo',
        usuario_id INT NOT NULL,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    );";
    $pdo->exec($querySolicitudes);

    // Compatible con bases ya creadas antes de agregar el seguimiento de cambios.
    try {
        $pdo->exec("ALTER TABLE solicitudes ADD COLUMN fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER fecha_creacion");
    } catch (PDOException $e) {
        // La columna ya existe.
    }

    try {
        $pdo->exec("ALTER TABLE solicitudes ADD COLUMN prioridad ENUM('Alta', 'Media', 'Baja') NOT NULL DEFAULT 'Media' AFTER estado");
    } catch (PDOException $e) {
        // La columna ya existe.
    }

    try {
        $pdo->exec("ALTER TABLE solicitudes ADD COLUMN justificacion_prioridad TEXT NULL AFTER prioridad");
    } catch (PDOException $e) {
        // La columna ya existe.
    }
    try {
        $pdo->exec("ALTER TABLE solicitudes ADD COLUMN fecha_objetivo DATE NULL AFTER justificacion_prioridad");
    } catch (PDOException $e) {
        // La columna ya existe.
    }

    try {
        $pdo->exec("ALTER TABLE solicitudes ADD COLUMN agente_id INT NULL AFTER usuario_id");
    } catch (PDOException $e) {
        // La columna ya existe.
    }
    try {
        $pdo->exec("ALTER TABLE solicitudes ADD COLUMN asignado_por INT NULL AFTER agente_id");
    } catch (PDOException $e) {
        // La columna ya existe.
    }
    try {
        $pdo->exec("ALTER TABLE solicitudes ADD COLUMN fecha_asignacion TIMESTAMP NULL AFTER asignado_por");
    } catch (PDOException $e) {
        // La columna ya existe.
    }

    $queryHistorialPrioridad = "CREATE TABLE IF NOT EXISTS historial_prioridad (
        id INT AUTO_INCREMENT PRIMARY KEY,
        solicitud_id INT NOT NULL,
        usuario_id INT NOT NULL,
        prioridad_anterior ENUM('Alta', 'Media', 'Baja') NOT NULL,
        prioridad_nueva ENUM('Alta', 'Media', 'Baja') NOT NULL,
        fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (solicitud_id) REFERENCES solicitudes(id) ON DELETE CASCADE,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    );";
    $pdo->exec($queryHistorialPrioridad);

    $pdo->exec("CREATE TABLE IF NOT EXISTS notificaciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        solicitud_id INT NULL,
        tipo VARCHAR(50) NOT NULL,
        mensaje VARCHAR(255) NOT NULL,
        leida TINYINT(1) NOT NULL DEFAULT 0,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (solicitud_id) REFERENCES solicitudes(id) ON DELETE CASCADE
    );");

    $pdo->exec("CREATE TABLE IF NOT EXISTS comentarios_trabajo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        solicitud_id INT NOT NULL,
        agente_id INT NOT NULL,
        comentario TEXT NOT NULL,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (solicitud_id) REFERENCES solicitudes(id) ON DELETE CASCADE,
        FOREIGN KEY (agente_id) REFERENCES usuarios(id) ON DELETE CASCADE
    );");

    $pdo->exec("CREATE TABLE IF NOT EXISTS historial_estados (
        id INT AUTO_INCREMENT PRIMARY KEY,
        solicitud_id INT NOT NULL,
        usuario_id INT NOT NULL,
        rol VARCHAR(30) NOT NULL,
        estado_anterior VARCHAR(30) NULL,
        estado_nuevo VARCHAR(30) NOT NULL,
        motivo TEXT NULL,
        fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (solicitud_id) REFERENCES solicitudes(id) ON DELETE CASCADE,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    );");

    
    $emailAdmin = 'admin@sistema.com';
    $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmtCheck->execute([$emailAdmin]);

    if ($stmtCheck->rowCount() === 0) {
        $passwordHash = password_hash('admin123', PASSWORD_BCRYPT);
        $stmtInsert = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, 'Admin')");
        $stmtInsert->execute(['Administrador', $emailAdmin, $passwordHash]);
    }

} catch (PDOException $e) {
    die("Error crítico de conexión: " . $e->getMessage());
}
?>
