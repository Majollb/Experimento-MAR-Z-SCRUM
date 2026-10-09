<?php
session_start();
require_once 'GEX-EC-05-SCRUM-Sprint-3-Desarrollo-ConfiguracionDeConexion-20261007-V1.0.php';

$mensajeError = '';
$mensajeExito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($nombre) && !empty($email) && !empty($password)) {
        $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmtCheck->execute([$email]);

        if ($stmtCheck->rowCount() > 0) {
            $mensajeError = "El correo ya está registrado.";
        } else {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $stmtInsert = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, 'Solicitante')");
            
            if ($stmtInsert->execute([$nombre, $email, $passwordHash])) {
                $mensajeExito = "¡Cuenta creada! Redirigiendo...";
                header("refresh:2;url=GEX-EC-05-SCRUM-Sprint-3-Desarrollo-InicioDeSesion-20261007-V1.0.php");
            } else {
                $mensajeError = "Error al crear la cuenta.";
            }
        }
    } else {
        $mensajeError = "Por favor completa todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Cuenta - Soporte</title>
    <link rel="stylesheet" href="GEX-EC-05-SCRUM-Sprint-3-Desarrollo-EstilosDeInterfaz-20261007-V1.0.css">
</head>
<body class="auth-bg-register">

    <div class="auth-card" style="max-width: 500px;">
        <h2 class="auth-title">Great!</h2>
        <p class="auth-subtitle">Now, Let's open a secure Account for you.</p>

        <?php if (!empty($mensajeError)): ?>
            <div style="background:#fee2e2; color:#ef4444; padding:10px; border-radius:8px; margin-bottom:15px; font-size:0.85rem;"><?php echo htmlspecialchars($mensajeError); ?></div>
        <?php endif; ?>

        <?php if (!empty($mensajeExito)): ?>
            <div style="background:#dcfce7; color:#15803d; padding:10px; border-radius:8px; margin-bottom:15px; font-size:0.85rem;"><?php echo htmlspecialchars($mensajeExito); ?></div>
        <?php endif; ?>

        <form method="POST" action="GEX-EC-05-SCRUM-Sprint-3-Desarrollo-RegistroDeUsuario-20261007-V1.0.php">
            <div class="input-field-group">
                <label>Full Name</label>
                <input type="text" name="nombre" class="form-control-simple" placeholder="e.g. John Doe" required>
            </div>

            <div class="input-field-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control-simple" placeholder="e.g. johndoe@mail.com" required>
            </div>

            <div class="input-field-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control-simple" placeholder="Choose a password" required>
            </div>

            <button type="submit" class="btn-primary-btn">Agree and Create Account &rarr;</button>
        </form>

        <div class="auth-footer-link">
            Already have an account? <a href="GEX-EC-05-SCRUM-Sprint-3-Desarrollo-InicioDeSesion-20261007-V1.0.php">Sign In</a>
        </div>
    </div>

</body>
</html>