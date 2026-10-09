<?php
session_start();
require_once 'GEX-EC-05-SCRUM-Sprint-3-Desarrollo-ConfiguracionDeConexion-20261007-V1.0.php';

if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'Admin') {
    header("Location: GEX-EC-05-SCRUM-Sprint-3-Desarrollo-PanelPrincipal-20261007-V1.0.php");
    exit();
}

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_usuario'])) {
    $id_usuario = intval($_POST['usuario_id']);
    $nuevo_email = trim($_POST['email']);
    $nuevo_rol = $_POST['rol'];
    $nuevo_estado = $_POST['estado'] ?? 'Activo';

    $roles_permitidos = ['Admin', 'Solicitante', 'Coordinador', 'Agente', 'Auditor'];

    if (in_array($nuevo_rol, $roles_permitidos) && in_array($nuevo_estado, ['Activo', 'Inactivo'], true) && filter_var($nuevo_email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $pdo->prepare("UPDATE usuarios SET email = ?, rol = ?, estado = ? WHERE id = ?");
        $stmt->execute([$nuevo_email, $nuevo_rol, $nuevo_estado, $id_usuario]);
        $mensaje = "Usuario actualizado correctamente.";
    }
}

if (isset($_GET['eliminar'])) {
    $id_eliminar = intval($_GET['eliminar']);
    if ($id_eliminar !== $_SESSION['usuario_id']) {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id_eliminar]);
        $mensaje = "Perfil eliminado correctamente.";
    }
}

$query = $pdo->query("SELECT id, nombre, email, rol, estado FROM usuarios ORDER BY id ASC");
$usuarios = $query->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios - Admin</title>
    <link rel="stylesheet" href="GEX-EC-05-SCRUM-Sprint-3-Desarrollo-EstilosDeInterfaz-20261007-V1.0.css">
</head>
<body>

<div class="dashboard-layout">
    <?php include 'GEX-EC-05-SCRUM-Sprint-3-Desarrollo-MenuDeNavegacion-20261007-V1.0.php'; ?>

    <div class="main-wrapper">
        <header class="topbar">
            <div class="topbar-title">
                <h2>User Management</h2>
            </div>
            <div class="topbar-user">
                <span><strong><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></strong></span>
                <span class="user-badge"><?php echo htmlspecialchars($_SESSION['usuario_rol']); ?></span>
                <a href="GEX-EC-05-SCRUM-Sprint-3-Desarrollo-CierreDeSesion-20261007-V1.0.php" class="btn-logout">Cerrar Sesión</a>
            </div>
        </header>

        <main class="content-area">
            <?php if ($mensaje): ?>
                <div style="background:#dcfce7; color:#15803d; padding:12px; border-radius:8px; margin-bottom:20px;"><?php echo htmlspecialchars($mensaje); ?></div>
            <?php endif; ?>

            <div class="ui-card">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol Asignado</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <form method="POST" action="GEX-EC-05-SCRUM-Sprint-3-Desarrollo-AdministracionDeUsuarios-20261007-V1.0.php">
                                    <td>#<?php echo $u['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($u['nombre']); ?></strong></td>
                                    <td>
                                        <input type="email" name="email" value="<?php echo htmlspecialchars($u['email']); ?>" class="form-control-simple" style="padding:6px 10px; width:220px;">
                                    </td>
                                    <td>
                                        <select name="rol" class="form-control-simple" style="padding:6px 10px; width:140px;">
                                            <option value="Solicitante" <?php echo $u['rol'] === 'Solicitante' ? 'selected' : ''; ?>>Solicitante</option>
                                            <option value="Coordinador" <?php echo $u['rol'] === 'Coordinador' ? 'selected' : ''; ?>>Coordinador</option>
                                            <option value="Agente" <?php echo $u['rol'] === 'Agente' ? 'selected' : ''; ?>>Agente</option>
                                            <option value="Auditor" <?php echo $u['rol'] === 'Auditor' ? 'selected' : ''; ?>>Auditor</option>
                                            <option value="Admin" <?php echo $u['rol'] === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="estado" class="form-control-simple" style="padding:6px 10px; width:120px;">
                                            <option value="Activo" <?php echo $u['estado'] === 'Activo' ? 'selected' : ''; ?>>Activo</option>
                                            <option value="Inactivo" <?php echo $u['estado'] === 'Inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="hidden" name="usuario_id" value="<?php echo $u['id']; ?>">
                                        <button type="submit" name="actualizar_usuario" class="badge-btn">Guardar</button>
                                        <?php if ($u['id'] !== $_SESSION['usuario_id']): ?>
                                            <a href="GEX-EC-05-SCRUM-Sprint-3-Desarrollo-AdministracionDeUsuarios-20261007-V1.0.php?eliminar=<?php echo $u['id']; ?>" class="badge-btn badge-btn-danger" style="text-decoration:none;" onclick="return confirm('¿Eliminar usuario?');">Eliminar</a>
                                        <?php endif; ?>
                                    </td>
                                </form>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

</body>
</html>
