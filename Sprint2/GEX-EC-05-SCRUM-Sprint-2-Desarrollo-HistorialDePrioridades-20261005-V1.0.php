<?php
session_start();
require_once 'GEX-EC-05-SCRUM-Sprint-2-Desarrollo-ConfiguracionDeConexion-20261005-V1.0.php';
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'Coordinador') {
    header('Location: GEX-EC-05-SCRUM-Sprint-2-Desarrollo-PanelPrincipal-20261005-V1.0.php');
    exit();
}
$stmt = $pdo->query('SELECT h.solicitud_id, s.titulo, h.prioridad_anterior, h.prioridad_nueva, h.fecha_cambio, u.nombre AS coordinador FROM historial_prioridad h INNER JOIN solicitudes s ON s.id = h.solicitud_id INNER JOIN usuarios u ON u.id = h.usuario_id ORDER BY h.fecha_cambio DESC, h.id DESC');
$historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Historial de prioridades - Soporte</title><link rel="stylesheet" href="GEX-EC-05-SCRUM-Sprint-2-Desarrollo-EstilosDeInterfaz-20261005-V1.0.css"></head>
<body><div class="dashboard-layout"><?php include 'GEX-EC-05-SCRUM-Sprint-2-Desarrollo-MenuDeNavegacion-20261005-V1.0.php'; ?><div class="main-wrapper">
<header class="topbar"><div class="topbar-title"><h2>Historial de prioridades</h2></div><div class="topbar-user"><span><strong><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></strong></span><span class="user-badge">Coordinador</span><a href="GEX-EC-05-SCRUM-Sprint-2-Desarrollo-CierreDeSesion-20261005-V1.0.php" class="btn-logout">Cerrar Sesión</a></div></header>
<main class="content-area"><a href="GEX-EC-05-SCRUM-Sprint-2-Desarrollo-GestionDeSolicitudes-20261005-V1.0.php" class="back-link">← Volver a priorización</a><div class="ui-card"><div class="table-wrapper"><table class="modern-table"><thead><tr><th>Solicitud</th><th>Prioridad anterior</th><th>Nueva prioridad</th><th>Coordinador</th><th>Fecha del cambio</th></tr></thead><tbody>
<?php if (!$historial): ?><tr><td colspan="5" class="empty-table">Aún no hay cambios de prioridad.</td></tr><?php else: ?><?php foreach ($historial as $cambio): ?><tr><td><strong>#<?php echo (int) $cambio['solicitud_id']; ?></strong> <?php echo htmlspecialchars($cambio['titulo']); ?></td><td><span class="priority-badge priority-<?php echo strtolower($cambio['prioridad_anterior']); ?>"><?php echo htmlspecialchars($cambio['prioridad_anterior']); ?></span></td><td><span class="priority-badge priority-<?php echo strtolower($cambio['prioridad_nueva']); ?>"><?php echo htmlspecialchars($cambio['prioridad_nueva']); ?></span></td><td><?php echo htmlspecialchars($cambio['coordinador']); ?></td><td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($cambio['fecha_cambio']))); ?></td></tr><?php endforeach; ?><?php endif; ?>
</tbody></table></div></div></main></div></div></body></html>
