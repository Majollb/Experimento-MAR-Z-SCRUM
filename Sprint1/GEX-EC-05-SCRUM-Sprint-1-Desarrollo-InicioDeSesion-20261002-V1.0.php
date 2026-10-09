<?php
session_start();
require_once 'GEX-EC-05-SCRUM-Sprint-1-Desarrollo-ConfiguracionDeConexion-20261002-V1.0.php';

$mensajeError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_rol'] = $usuario['rol'];
            header("Location: GEX-EC-05-SCRUM-Sprint-1-Desarrollo-PanelPrincipal-20261002-V1.0.php");
            exit();
        } else {
            $mensajeError = "Credenciales incorrectas.";
        }
    } else {
        $mensajeError = "Por favor, completa todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - Soporte</title>
    <link rel="stylesheet" href="GEX-EC-05-SCRUM-Sprint-1-Desarrollo-EstilosDeInterfaz-20261002-V1.0.css">
</head>
<body class="auth-bg-login">

    <div class="auth-brand-icon">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
    </div>

    <div class="auth-card">
        <h2 class="auth-title">Welcome Back</h2>
        <p class="auth-subtitle">Enter your credentials to access your account.</p>

        <?php if (!empty($mensajeError)): ?>
            <div style="background:#fee2e2; color:#ef4444; padding:10px; border-radius:8px; margin-bottom:15px; font-size:0.85rem;">
                <?php echo htmlspecialchars($mensajeError); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="GEX-EC-05-SCRUM-Sprint-1-Desarrollo-InicioDeSesion-20261002-V1.0.php">
            <div class="input-field-group">
                <div class="input-with-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>
            </div>

            <div class="input-field-group">
                <div class="input-with-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>
            </div>

            <button type="submit" class="btn-primary-btn">Sign In</button>
        </form>

        <div class="auth-footer-link">
            Don't have an account? <a href="GEX-EC-05-SCRUM-Sprint-1-Desarrollo-RegistroDeUsuario-20261002-V1.0.php">Create Account</a>
        </div>
    </div>

</body>
</html>