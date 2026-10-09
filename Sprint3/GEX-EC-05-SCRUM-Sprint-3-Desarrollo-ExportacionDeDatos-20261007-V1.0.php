<?php
session_start(); require_once 'GEX-EC-05-SCRUM-Sprint-3-Desarrollo-ConfiguracionDeConexion-20261007-V1.0.php';
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'Coordinador') { header('Location: GEX-EC-05-SCRUM-Sprint-3-Desarrollo-PanelPrincipal-20261007-V1.0.php'); exit(); }
$estado = $_GET['estado'] ?? ''; $prioridad = $_GET['prioridad'] ?? ''; $categoria = trim($_GET['categoria'] ?? '');
$where = []; $params = [];
if (in_array($estado, ['Nuevo', 'En Proceso', 'Resuelta', 'Cerrada'], true)) { $where[] = 'estado = ?'; $params[] = $estado; }
if (in_array($prioridad, ['Alta', 'Media', 'Baja'], true)) { $where[] = 'prioridad = ?'; $params[] = $prioridad; }
if ($categoria !== '') { $where[] = 'categoria = ?'; $params[] = $categoria; }
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$stmt = $pdo->prepare("SELECT id, categoria, estado, prioridad, fecha_creacion, fecha_objetivo, fecha_actualizacion FROM solicitudes{$whereSql} ORDER BY fecha_creacion DESC, id DESC"); $stmt->execute($params); $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
registrarEvento($pdo, 'exportacion', null, (int) $_SESSION['usuario_id'], $_SESSION['usuario_rol'], 'filtros', null, json_encode(['estado' => $estado, 'prioridad' => $prioridad, 'categoria' => $categoria], JSON_UNESCAPED_UNICODE));
header('Content-Type: text/csv; charset=utf-8'); header('Content-Disposition: attachment; filename="reporte_solicitudes_' . date('Ymd_His') . '.csv"');
$salida = fopen('php://output', 'w'); fwrite($salida, "\xEF\xBB\xBF");
fputcsv($salida, ['ID', 'Categoría', 'Estado', 'Prioridad', 'Fecha creación', 'Fecha objetivo', 'Última actualización']);
foreach ($filas as $fila) { fputcsv($salida, [$fila['id'], $fila['categoria'], $fila['estado'], $fila['prioridad'], $fila['fecha_creacion'], $fila['fecha_objetivo'], $fila['fecha_actualizacion']]); }
fclose($salida); exit();
