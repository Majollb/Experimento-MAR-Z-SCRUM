<?php
session_start();
require_once 'GEX-EC-05-SCRUM-Sprint-2-Desarrollo-ConfiguracionDeConexion-20261005-V1.0.php';

if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'Coordinador') {
    header('Location: GEX-EC-05-SCRUM-Sprint-2-Desarrollo-PanelPrincipal-20261005-V1.0.php');
    exit();
}

$prioridadesValidas = ['Alta', 'Media', 'Baja'];
$mensaje = '';
$mensajeError = '';

$agentesStmt = $pdo->query("SELECT id, nombre FROM usuarios WHERE rol = 'Agente' AND estado = 'Activo' ORDER BY nombre");
$agentes = $agentesStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asignar_solicitud'])) {
    $solicitudId = filter_input(INPUT_POST, 'solicitud_id', FILTER_VALIDATE_INT);
    $agenteId = filter_input(INPUT_POST, 'agente_id', FILTER_VALIDATE_INT);
    if (!$solicitudId || !$agenteId) {
        $mensajeError = 'Selecciona una solicitud y un agente.';
    } else {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("SELECT id, nombre FROM usuarios WHERE id = ? AND rol = 'Agente' AND estado = 'Activo'");
            $stmt->execute([$agenteId]);
            $agente = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$agente) {
                throw new RuntimeException('El agente no existe o no está activo.');
            }
            $stmt = $pdo->prepare('SELECT titulo, agente_id FROM solicitudes WHERE id = ? FOR UPDATE');
            $stmt->execute([$solicitudId]);
            $solicitudAsignada = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$solicitudAsignada) {
                throw new RuntimeException('La solicitud no existe.');
            }
            $stmt = $pdo->prepare('UPDATE solicitudes SET agente_id = ?, asignado_por = ?, fecha_asignacion = CURRENT_TIMESTAMP WHERE id = ?');
            $stmt->execute([$agenteId, $_SESSION['usuario_id'], $solicitudId]);
            $mensajeNotificacion = 'Solicitud #' . $solicitudId . ' asignada: ' . $solicitudAsignada['titulo'];
            $stmt = $pdo->prepare("INSERT INTO notificaciones (usuario_id, solicitud_id, tipo, mensaje) VALUES (?, ?, 'asignacion', ?)");
            $stmt->execute([$agenteId, $solicitudId, $mensajeNotificacion]);
            $pdo->commit();
            $mensaje = 'Solicitud asignada a ' . $agente['nombre'] . ' y notificación enviada.';
        } catch (Throwable $e) {
            $pdo->rollBack();
            $mensajeError = $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_prioridad'])) {
    $solicitudId = filter_input(INPUT_POST, 'solicitud_id', FILTER_VALIDATE_INT);
    $nuevaPrioridad = $_POST['prioridad'] ?? '';
    $justificacion = trim($_POST['justificacion_prioridad'] ?? '');
    $fechaObjetivo = trim($_POST['fecha_objetivo'] ?? '');

    if (!$solicitudId || !in_array($nuevaPrioridad, $prioridadesValidas, true)) {
        $mensajeError = 'La prioridad seleccionada no es válida.';
    } elseif ($nuevaPrioridad === 'Alta' && ($justificacion === '' || $fechaObjetivo === '')) {
        $mensajeError = 'Las solicitudes de prioridad Alta requieren justificación y fecha objetivo.';
    } elseif ($fechaObjetivo !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaObjetivo)) {
        $mensajeError = 'La fecha objetivo no es válida.';
    } else {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT prioridad, justificacion_prioridad, fecha_objetivo FROM solicitudes WHERE id = ? FOR UPDATE');
            $stmt->execute([$solicitudId]);
            $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$solicitud) {
                throw new RuntimeException('La solicitud no existe.');
            }

            if ($solicitud['prioridad'] !== $nuevaPrioridad || $solicitud['justificacion_prioridad'] !== $justificacion || $solicitud['fecha_objetivo'] !== ($fechaObjetivo ?: null)) {
                $stmt = $pdo->prepare('UPDATE solicitudes SET prioridad = ?, justificacion_prioridad = ?, fecha_objetivo = ? WHERE id = ?');
                $stmt->execute([$nuevaPrioridad, $justificacion ?: null, $fechaObjetivo ?: null, $solicitudId]);
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

$stmt = $pdo->query("SELECT s.id, s.titulo, s.categoria, s.estado, s.prioridad, s.fecha_creacion, s.fecha_actualizacion, s.agente_id, a.nombre AS agente, u.nombre AS solicitante FROM solicitudes s INNER JOIN usuarios u ON u.id = s.usuario_id LEFT JOIN usuarios a ON a.id = s.agente_id ORDER BY {$ordenes[$orden]}");
$solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Priorizar Solicitudes - Soporte</title><link rel="stylesheet" href="GEX-EC-05-SCRUM-Sprint-2-Desarrollo-EstilosDeInterfaz-20261005-V1.0.css">
</head>
<body><div class="dashboard-layout">
<?php include 'GEX-EC-05-SCRUM-Sprint-2-Desarrollo-MenuDeNavegacion-20261005-V1.0.php'; ?><div class="main-wrapper">
<header class="topbar"><div class="topbar-title"><h2>Priorizar Solicitudes</h2></div><div class="topbar-user"><span><strong><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></strong></span><span class="user-badge"><?php echo htmlspecialchars($_SESSION['usuario_rol']); ?></span><a href="GEX-EC-05-SCRUM-Sprint-2-Desarrollo-CierreDeSesion-20261005-V1.0.php" class="btn-logout">Cerrar Sesión</a></div></header>
<main class="content-area">
<div class="page-heading"><div><h1>Cola de atención</h1><p>Ordena las solicitudes y asigna una prioridad válida.</p></div><a href="GEX-EC-05-SCRUM-Sprint-2-Desarrollo-HistorialDePrioridades-20261005-V1.0.php" class="btn-primary-link">Ver historial</a></div>
<?php if ($mensaje): ?><div class="notice-success"><?php echo htmlspecialchars($mensaje); ?></div><?php endif; ?>
<?php if ($mensajeError): ?><div class="notice-error"><?php echo htmlspecialchars($mensajeError); ?></div><?php endif; ?>
<div class="ui-card assignment-card"><h3>Actualizar prioridad</h3><form method="POST" class="priority-update-form"><select name="solicitud_id" required><option value="">Selecciona una solicitud</option><?php foreach ($solicitudes as $solicitud): ?><option value="<?php echo (int) $solicitud['id']; ?>">#<?php echo (int) $solicitud['id']; ?> - <?php echo htmlspecialchars($solicitud['titulo']); ?></option><?php endforeach; ?></select><select name="prioridad" id="prioridad-general" required><option value="">Prioridad</option><?php foreach ($prioridadesValidas as $prioridad): ?><option value="<?php echo $prioridad; ?>"><?php echo $prioridad; ?></option><?php endforeach; ?></select><input type="text" name="justificacion_prioridad" id="justificacion-prioridad" placeholder="Justificación si es Alta"><input type="date" name="fecha_objetivo" id="fecha-objetivo"><button type="submit" name="actualizar_prioridad" class="btn-primary-link">Guardar prioridad</button></form></div>
<div class="ui-card assignment-card"><h3>Asignar solicitud</h3><form method="POST" class="assignment-inline"><select name="solicitud_id" required><option value="">Selecciona una solicitud</option><?php foreach ($solicitudes as $solicitud): ?><option value="<?php echo (int) $solicitud['id']; ?>">#<?php echo (int) $solicitud['id']; ?> - <?php echo htmlspecialchars($solicitud['titulo']); ?></option><?php endforeach; ?></select><select name="agente_id" required><option value="">Selecciona un agente activo</option><?php foreach ($agentes as $agente): ?><option value="<?php echo (int) $agente['id']; ?>"><?php echo htmlspecialchars($agente['nombre']); ?></option><?php endforeach; ?></select><button type="submit" name="asignar_solicitud" class="btn-primary-link">Asignar</button></form></div>
<div class="sort-bar"><span>Ordenar por:</span><a class="<?php echo $orden === 'prioridad' ? 'selected' : ''; ?>" href="GEX-EC-05-SCRUM-Sprint-2-Desarrollo-GestionDeSolicitudes-20261005-V1.0.php?orden=prioridad">Prioridad</a><a class="<?php echo $orden === 'estado' ? 'selected' : ''; ?>" href="GEX-EC-05-SCRUM-Sprint-2-Desarrollo-GestionDeSolicitudes-20261005-V1.0.php?orden=estado">Estado</a><a class="<?php echo $orden === 'fecha' ? 'selected' : ''; ?>" href="GEX-EC-05-SCRUM-Sprint-2-Desarrollo-GestionDeSolicitudes-20261005-V1.0.php?orden=fecha">Fecha</a></div>
<div class="ui-card"><div class="table-wrapper"><table class="modern-table"><thead><tr><th>ID</th><th>Solicitud</th><th>Solicitante</th><th>Estado</th><th>Prioridad</th><th>Creada</th><th>Acción</th></tr></thead><tbody>
<?php if (!$solicitudes): ?><tr><td colspan="7" class="empty-table">No hay solicitudes registradas.</td></tr><?php else: ?>
<?php foreach ($solicitudes as $solicitud): ?><tr><td>#<?php echo (int) $solicitud['id']; ?></td><td><strong><?php echo htmlspecialchars($solicitud['titulo']); ?></strong><small><?php echo htmlspecialchars($solicitud['categoria']); ?></small></td><td><?php echo htmlspecialchars($solicitud['solicitante']); ?></td><td><span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $solicitud['estado'])); ?>"><?php echo htmlspecialchars($solicitud['estado']); ?></span></td><td><span class="priority-badge priority-<?php echo strtolower($solicitud['prioridad']); ?>"><?php echo htmlspecialchars($solicitud['prioridad']); ?></span></td><td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($solicitud['fecha_creacion']))); ?></td><td><form method="POST" class="priority-form"><input type="hidden" name="solicitud_id" value="<?php echo (int) $solicitud['id']; ?>"><select name="prioridad" aria-label="Prioridad de solicitud #<?php echo (int) $solicitud['id']; ?>"><?php foreach ($prioridadesValidas as $prioridad): ?><option value="<?php echo $prioridad; ?>" <?php echo $solicitud['prioridad'] === $prioridad ? 'selected' : ''; ?>><?php echo $prioridad; ?></option><?php endforeach; ?></select><button type="submit" name="actualizar_prioridad" class="badge-btn">Guardar</button></form></td></tr><?php endforeach; ?>
<?php endif; ?></tbody></table></div></div>
</main></div></div></body></html>
