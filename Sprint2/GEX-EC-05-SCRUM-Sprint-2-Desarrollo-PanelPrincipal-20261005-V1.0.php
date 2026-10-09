<?php
session_start();
require_once 'GEX-EC-05-SCRUM-Sprint-2-Desarrollo-ConfiguracionDeConexion-20261005-V1.0.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: GEX-EC-05-SCRUM-Sprint-2-Desarrollo-InicioDeSesion-20261005-V1.0.php");
    exit();
}

$mensaje = "";
$mensajeError = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_solicitud'])) {
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $usuario_id = $_SESSION['usuario_id'];

    if (!empty($titulo) && !empty($descripcion) && !empty($categoria)) {
        $stmt = $pdo->prepare("INSERT INTO solicitudes (titulo, descripcion, categoria, usuario_id) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$titulo, $descripcion, $categoria, $usuario_id])) {
            $solicitudId = $pdo->lastInsertId();
            $stmtHistorial = $pdo->prepare("INSERT INTO historial_estados (solicitud_id, usuario_id, rol, estado_anterior, estado_nuevo, motivo) VALUES (?, ?, 'Solicitante', NULL, 'Nuevo', 'Solicitud creada')");
            $stmtHistorial->execute([$solicitudId, $usuario_id]);
            $mensaje = "¡Solicitud registrada correctamente! ID: #" . $solicitudId;
        }
    } else {
        $mensajeError = "Título, descripción y categoría son obligatorios.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Solicitud - Soporte</title>
    <link rel="stylesheet" href="GEX-EC-05-SCRUM-Sprint-2-Desarrollo-EstilosDeInterfaz-20261005-V1.0.css">
</head>
<body>

<div class="dashboard-layout">
    <?php include 'GEX-EC-05-SCRUM-Sprint-2-Desarrollo-MenuDeNavegacion-20261005-V1.0.php'; ?>

    <div class="main-wrapper">
        <header class="topbar">
            <div class="topbar-title">
                <h2>Gestión de Solicitudes</h2>
            </div>
            <div class="topbar-user">
                <span><strong><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></strong></span>
                <span class="user-badge"><?php echo htmlspecialchars($_SESSION['usuario_rol']); ?></span>
                <a href="GEX-EC-05-SCRUM-Sprint-2-Desarrollo-CierreDeSesion-20261005-V1.0.php" class="btn-logout">Cerrar Sesión</a>
            </div>
        </header>

        <main class="content-area">
            <?php if ($mensaje): ?>
                <div style="background:#dcfce7; color:#15803d; padding:12px; border-radius:8px; margin-bottom:20px;"><?php echo htmlspecialchars($mensaje); ?></div>
            <?php endif; ?>
            <?php if ($mensajeError): ?>
                <div style="background:#fee2e2; color:#b91c1c; padding:12px; border-radius:8px; margin-bottom:20px;"><?php echo htmlspecialchars($mensajeError); ?></div>
            <?php endif; ?>

            <div class="ui-card" style="max-width: 600px;">
                <h3 style="margin-bottom:20px;">Crear Nueva Solicitud</h3>
                <form method="POST" action="GEX-EC-05-SCRUM-Sprint-2-Desarrollo-PanelPrincipal-20261005-V1.0.php">
                    <div class="input-field-group">
                        <label>Título de la Solicitud</label>
                        <input type="text" name="titulo" class="form-control-simple" required placeholder="Ej. Error en acceso">
                    </div>

                    <div class="input-field-group">
                        <label>Categoría</label>
                        <select name="categoria" class="form-control-simple" required>
                            <option value="Hardware">Hardware</option>
                            <option value="Software">Software</option>
                            <option value="Redes">Redes</option>
                        </select>
                    </div>

                    <div class="input-field-group">
                        <label>Descripción</label>
                        <textarea name="descripcion" class="form-control-simple" rows="4" required placeholder="Detalla el inconveniente..."></textarea>
                    </div>

                    <button type="submit" name="crear_solicitud" class="btn-primary-btn">Enviar Solicitud</button>
                </form>
            </div>
        </main>
    </div>
</div>

</body>
</html>
