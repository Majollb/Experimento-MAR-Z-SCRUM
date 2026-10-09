<?php
session_start();
require_once 'GEX-EC-05-SCRUM-Sprint-3-Desarrollo-ConfiguracionDeConexion-20261007-V1.0.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: GEX-EC-05-SCRUM-Sprint-3-Desarrollo-InicioDeSesion-20261007-V1.0.php');
    exit();
}

$busqueda = trim($_GET['q'] ?? '');
$filtroEstado = $_GET['estado'] ?? '';
$filtroPrioridad = $_GET['prioridad'] ?? '';
$filtroCategoria = trim($_GET['categoria'] ?? '');
$sql = 'SELECT id, titulo, categoria, estado, fecha_creacion, fecha_actualizacion FROM solicitudes WHERE usuario_id = ?';
$parametros = [$_SESSION['usuario_id']];
if ($busqueda !== '') { $sql .= ' AND (titulo LIKE ? OR descripcion LIKE ?)'; $parametros[] = '%' . $busqueda . '%'; $parametros[] = '%' . $busqueda . '%'; }
if (in_array($filtroEstado, ['Nuevo', 'En Proceso', 'Resuelta', 'Cerrada'], true)) { $sql .= ' AND estado = ?'; $parametros[] = $filtroEstado; }
if (in_array($filtroPrioridad, ['Alta', 'Media', 'Baja'], true)) { $sql .= ' AND prioridad = ?'; $parametros[] = $filtroPrioridad; }
if ($filtroCategoria !== '') { $sql .= ' AND categoria = ?'; $parametros[] = $filtroCategoria; }
$sql .= ' ORDER BY fecha_actualizacion DESC, id DESC';
$stmt = $pdo->prepare($sql); $stmt->execute($parametros);
$solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Solicitudes - Soporte</title><link rel="stylesheet" href="GEX-EC-05-SCRUM-Sprint-3-Desarrollo-EstilosDeInterfaz-20261007-V1.0.css">
</head>
<body><div class="dashboard-layout">
<?php include 'GEX-EC-05-SCRUM-Sprint-3-Desarrollo-MenuDeNavegacion-20261007-V1.0.php'; ?><div class="main-wrapper">
<header class="topbar"><div class="topbar-title"><h2>Mis Solicitudes</h2></div><div class="topbar-user"><span><strong><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></strong></span><span class="user-badge"><?php echo htmlspecialchars($_SESSION['usuario_rol']); ?></span><a href="GEX-EC-05-SCRUM-Sprint-3-Desarrollo-CierreDeSesion-20261007-V1.0.php" class="btn-logout">Cerrar Sesión</a></div></header>
<main class="content-area">
<div class="page-heading"><div><h1>Seguimiento de solicitudes</h1><p>Consulta el estado y la última actualización de tus solicitudes.</p></div><a href="GEX-EC-05-SCRUM-Sprint-3-Desarrollo-PanelPrincipal-20261007-V1.0.php" class="btn-primary-link">Nueva solicitud</a></div>
<form method="GET" class="search-form"><input type="search" name="q" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Buscar en título o descripción"><select name="estado"><option value="">Todos los estados</option><?php foreach (['Nuevo', 'En Proceso', 'Resuelta', 'Cerrada'] as $estado): ?><option value="<?php echo $estado; ?>" <?php echo $filtroEstado === $estado ? 'selected' : ''; ?>><?php echo $estado; ?></option><?php endforeach; ?></select><select name="prioridad"><option value="">Todas las prioridades</option><?php foreach (['Alta', 'Media', 'Baja'] as $prioridad): ?><option value="<?php echo $prioridad; ?>" <?php echo $filtroPrioridad === $prioridad ? 'selected' : ''; ?>><?php echo $prioridad; ?></option><?php endforeach; ?></select><input type="text" name="categoria" value="<?php echo htmlspecialchars($filtroCategoria); ?>" placeholder="Categoría"><button class="badge-btn" type="submit">Buscar y filtrar</button><a href="GEX-EC-05-SCRUM-Sprint-3-Desarrollo-ListaDeSolicitudes-20261007-V1.0.php" class="back-link">Limpiar</a></form>
<div class="ui-card">
<?php if (!$solicitudes): ?><div class="empty-state"><h3>Aún no tienes solicitudes</h3><p>Crea una solicitud para pedir soporte.</p><a href="GEX-EC-05-SCRUM-Sprint-3-Desarrollo-PanelPrincipal-20261007-V1.0.php" class="btn-primary-link">Crear solicitud</a></div>
<?php else: ?><div class="table-wrapper"><table class="modern-table"><thead><tr><th>ID</th><th>Título</th><th>Categoría</th><th>Estado</th><th>Última actualización</th><th></th></tr></thead><tbody>
<?php foreach ($solicitudes as $solicitud): ?><tr><td>#<?php echo (int) $solicitud['id']; ?></td><td><strong><?php echo htmlspecialchars($solicitud['titulo']); ?></strong></td><td><?php echo htmlspecialchars($solicitud['categoria']); ?></td><td><span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $solicitud['estado'])); ?>"><?php echo htmlspecialchars($solicitud['estado']); ?></span></td><td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($solicitud['fecha_actualizacion']))); ?></td><td><a class="badge-btn" href="GEX-EC-05-SCRUM-Sprint-3-Desarrollo-DetalleDeSolicitud-20261007-V1.0.php?id=<?php echo (int) $solicitud['id']; ?>">Ver detalle</a></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></div></main></div></div></body></html>
