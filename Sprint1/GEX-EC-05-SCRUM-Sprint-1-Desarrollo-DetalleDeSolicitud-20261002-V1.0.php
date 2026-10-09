<?php
session_start();
require_once 'GEX-EC-05-SCRUM-Sprint-1-Desarrollo-ConfiguracionDeConexion-20261002-V1.0.php';
if (!isset($_SESSION['usuario_id'])) { header('Location: GEX-EC-05-SCRUM-Sprint-1-Desarrollo-InicioDeSesion-20261002-V1.0.php'); exit(); }
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: GEX-EC-05-SCRUM-Sprint-1-Desarrollo-ListaDeSolicitudes-20261002-V1.0.php'); exit(); }
$stmt = $pdo->prepare('SELECT id, titulo, descripcion, categoria, estado, fecha_creacion, fecha_actualizacion FROM solicitudes WHERE id = ? AND usuario_id = ?');
$stmt->execute([$id, $_SESSION['usuario_id']]);
$solicitud = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$solicitud) { http_response_code(404); $mensaje = 'La solicitud no existe o no pertenece a tu cuenta.'; }
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Detalle de solicitud - Soporte</title><link rel="stylesheet" href="GEX-EC-05-SCRUM-Sprint-1-Desarrollo-EstilosDeInterfaz-20261002-V1.0.css"></head>
<body><div class="dashboard-layout"><?php include 'GEX-EC-05-SCRUM-Sprint-1-Desarrollo-MenuDeNavegacion-20261002-V1.0.php'; ?><div class="main-wrapper">
<header class="topbar"><div class="topbar-title"><h2>Detalle de solicitud</h2></div><div class="topbar-user"><span><strong><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></strong></span><span class="user-badge"><?php echo htmlspecialchars($_SESSION['usuario_rol']); ?></span><a href="GEX-EC-05-SCRUM-Sprint-1-Desarrollo-CierreDeSesion-20261002-V1.0.php" class="btn-logout">Cerrar Sesión</a></div></header>
<main class="content-area"><a href="GEX-EC-05-SCRUM-Sprint-1-Desarrollo-ListaDeSolicitudes-20261002-V1.0.php" class="back-link">← Volver a mis solicitudes</a>
<?php if (!$solicitud): ?><div class="ui-card detail-card"><h1>Solicitud no encontrada</h1><p><?php echo htmlspecialchars($mensaje); ?></p></div>
<?php else: ?><div class="ui-card detail-card"><div class="detail-header"><div><span class="detail-id">Solicitud #<?php echo (int) $solicitud['id']; ?></span><h1><?php echo htmlspecialchars($solicitud['titulo']); ?></h1></div><span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $solicitud['estado'])); ?>"><?php echo htmlspecialchars($solicitud['estado']); ?></span></div><div class="detail-grid"><div><span class="detail-label">Categoría</span><strong><?php echo htmlspecialchars($solicitud['categoria']); ?></strong></div><div><span class="detail-label">Creada</span><strong><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($solicitud['fecha_creacion']))); ?></strong></div><div><span class="detail-label">Última actualización</span><strong><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($solicitud['fecha_actualizacion']))); ?></strong></div></div><div class="description-block"><span class="detail-label">Descripción</span><p><?php echo nl2br(htmlspecialchars($solicitud['descripcion'])); ?></p></div></div><?php endif; ?></main></div></div></body></html>
