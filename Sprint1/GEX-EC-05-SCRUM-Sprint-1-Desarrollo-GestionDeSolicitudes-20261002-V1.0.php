<?php
session_start();
require_once 'GEX-EC-05-SCRUM-Sprint-1-Desarrollo-ConfiguracionDeConexion-20261002-V1.0.php';

if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'Coordinador') {
    header('Location: GEX-EC-05-SCRUM-Sprint-1-Desarrollo-PanelPrincipal-20261002-V1.0.php');
    exit();
}

$prioridadesValidas = ['Alta', 'Media', 'Baja'];
$mensaje = '';
$mensajeError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_prioridad'])) {
    $solicitudId = filter_input(INPUT_POST, 'solicitud_id', FILTER_VALIDATE_INT);
    $nuevaPrioridad = $_POST['prioridad'] ?? '';

    if (!$solicitudId || !in_array($nuevaPrioridad, $prioridadesValidas, true)) {
        $mensajeError = 'La prioridad seleccionada no es válida.';
    } else {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT prioridad FROM solicitudes WHERE id = ? FOR UPDATE');
            $stmt->execute([$solicitudId]);
            $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$solicitud) {
                throw new RuntimeException('La solicitud no existe.');
            }

            if ($solicitud['prioridad'] !== $nuevaPrioridad) {
                $stmt = $pdo->prepare('UPDATE solicitudes SET prioridad = ? WHERE id = ?');
                $stmt->execute([$nuevaPrioridad, $solicitudId]);
                $stmt = $pdo->prepare('INSERT INTO historial_prioridad (solicitud_id, usuario_id, prioridad_anterior, prioridad_nueva) VALUES (?, ?, ?, ?)');
                $stmt->execute([$solicitudId, $_SESSION['usuario_id'], $solicitud['prioridad'], $nuevaPrioridad]);
                $mensaje = 'Prioridad actualizada y cambio registrado en el historial.';
            } else {
                $mensaje = 'La solicitud ya tenía esa prioridad.';
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            $mensajeError = $e->getMessage();
        }
    }
}

// Solo se permiten estos tres criterios para evitar inyección SQL en ORDER BY.
$ordenes = [
    'prioridad' => "FIELD(s.prioridad, 'Alta', 'Media', 'Baja'), s.fecha_actualizacion DESC",
    'estado' => "FIELD(s.estado, 'Nuevo', 'En Proceso', 'Resuelta', 'Cerrada'), s.fecha_actualizacion DESC",
    'fecha' => 's.fecha_creacion DESC, s.id DESC'
];
$orden = $_GET['orden'] ?? 'prioridad';
if (!array_key_exists($orden, $ordenes)) {
    $orden = 'prioridad';
}

$stmt = $pdo->query("SELECT s.id, s.titulo, s.categoria, s.estado, s.prioridad, s.fecha_creacion, s.fecha_actualizacion, u.nombre AS solicitante FROM solicitudes s INNER JOIN usuarios u ON u.id = s.usuario_id ORDER BY {$ordenes[$orden]}");
$solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Priorizar Solicitudes - Soporte</title><link rel="stylesheet" href="GEX-EC-05-SCRUM-Sprint-1-Desarrollo-EstilosDeInterfaz-20261002-V1.0.css">
</head>
<body><div class="dashboard-layout">
<?php include 'GEX-EC-05-SCRUM-Sprint-1-Desarrollo-MenuDeNavegacion-20261002-V1.0.php'; ?><div class="main-wrapper">
<header class="topbar"><div class="topbar-title"><h2>Priorizar Solicitudes</h2></div><div class="topbar-user"><span><strong><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></strong></span><span class="user-badge"><?php echo htmlspecialchars($_SESSION['usuario_rol']); ?></span><a href="GEX-EC-05-SCRUM-Sprint-1-Desarrollo-CierreDeSesion-20261002-V1.0.php" class="btn-logout">Cerrar Sesión</a></div></header>
<main class="content-area">
<div class="page-heading"><div><h1>Cola de atención</h1><p>Ordena las solicitudes y asigna una prioridad válida.</p></div><a href="GEX-EC-05-SCRUM-Sprint-1-Desarrollo-HistorialDePrioridades-20261002-V1.0.php" class="btn-primary-link">Ver historial</a></div>
<?php if ($mensaje): ?><div class="notice-success"><?php echo htmlspecialchars($mensaje); ?></div><?php endif; ?>
<?php if ($mensajeError): ?><div class="notice-error"><?php echo htmlspecialchars($mensajeError); ?></div><?php endif; ?>
<div class="sort-bar"><span>Ordenar por:</span><a class="<?php echo $orden === 'prioridad' ? 'selected' : ''; ?>" href="GEX-EC-05-SCRUM-Sprint-1-Desarrollo-GestionDeSolicitudes-20261002-V1.0.php?orden=prioridad">Prioridad</a><a class="<?php echo $orden === 'estado' ? 'selected' : ''; ?>" href="GEX-EC-05-SCRUM-Sprint-1-Desarrollo-GestionDeSolicitudes-20261002-V1.0.php?orden=estado">Estado</a><a class="<?php echo $orden === 'fecha' ? 'selected' : ''; ?>" href="GEX-EC-05-SCRUM-Sprint-1-Desarrollo-GestionDeSolicitudes-20261002-V1.0.php?orden=fecha">Fecha</a></div>
<div class="ui-card"><div class="table-wrapper"><table class="modern-table"><thead><tr><th>ID</th><th>Solicitud</th><th>Solicitante</th><th>Estado</th><th>Prioridad</th><th>Creada</th><th>Acción</th></tr></thead><tbody>
<?php if (!$solicitudes): ?><tr><td colspan="7" class="empty-table">No hay solicitudes registradas.</td></tr><?php else: ?>
<?php foreach ($solicitudes as $solicitud): ?><tr><td>#<?php echo (int) $solicitud['id']; ?></td><td><strong><?php echo htmlspecialchars($solicitud['titulo']); ?></strong><small><?php echo htmlspecialchars($solicitud['categoria']); ?></small></td><td><?php echo htmlspecialchars($solicitud['solicitante']); ?></td><td><span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $solicitud['estado'])); ?>"><?php echo htmlspecialchars($solicitud['estado']); ?></span></td><td><span class="priority-badge priority-<?php echo strtolower($solicitud['prioridad']); ?>"><?php echo htmlspecialchars($solicitud['prioridad']); ?></span></td><td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($solicitud['fecha_creacion']))); ?></td><td><form method="POST" class="priority-form"><input type="hidden" name="solicitud_id" value="<?php echo (int) $solicitud['id']; ?>"><select name="prioridad" aria-label="Prioridad de solicitud #<?php echo (int) $solicitud['id']; ?>"><?php foreach ($prioridadesValidas as $prioridad): ?><option value="<?php echo $prioridad; ?>" <?php echo $solicitud['prioridad'] === $prioridad ? 'selected' : ''; ?>><?php echo $prioridad; ?></option><?php endforeach; ?></select><button type="submit" name="actualizar_prioridad" class="badge-btn">Guardar</button></form></td></tr><?php endforeach; ?>
<?php endif; ?></tbody></table></div></div>
</main></div></div></body></html>
