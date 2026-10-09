<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$rolUsuario    = $_SESSION['usuario_rol'] ?? 'Solicitante';
$paginaActual  = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">

    <div class="sidebar-brand">
       
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        Scrum Support
    </div>

 
    <ul class="sidebar-menu">
        <li>
            <a href="GEX-EC-05-SCRUM-Sprint-2-Desarrollo-PanelPrincipal-20261005-V1.0.php" class="sidebar-link <?php echo ($paginaActual === 'GEX-EC-05-SCRUM-Sprint-2-Desarrollo-PanelPrincipal-20261005-V1.0.php') ? 'active' : ''; ?>">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>
                Crear Solicitud
            </a>
        </li>
        <li>
            <a href="GEX-EC-05-SCRUM-Sprint-2-Desarrollo-ListaDeSolicitudes-20261005-V1.0.php" class="sidebar-link <?php echo in_array($paginaActual, ['GEX-EC-05-SCRUM-Sprint-2-Desarrollo-ListaDeSolicitudes-20261005-V1.0.php', 'GEX-EC-05-SCRUM-Sprint-2-Desarrollo-DetalleDeSolicitud-20261005-V1.0.php'], true) ? 'active' : ''; ?>">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h10"></path></svg>
                Mis Solicitudes
            </a>
        </li>

        <?php if ($rolUsuario === 'Coordinador'): ?>
            <li><a href="GEX-EC-05-SCRUM-Sprint-2-Desarrollo-GestionDeSolicitudes-20261005-V1.0.php" class="sidebar-link <?php echo in_array($paginaActual, ['GEX-EC-05-SCRUM-Sprint-2-Desarrollo-GestionDeSolicitudes-20261005-V1.0.php', 'GEX-EC-05-SCRUM-Sprint-2-Desarrollo-HistorialDePrioridades-20261005-V1.0.php'], true) ? 'active' : ''; ?>">Priorizar Solicitudes</a></li>
        <?php endif; ?>

        <?php if ($rolUsuario === 'Agente'): ?>
            <li><a href="GEX-EC-05-SCRUM-Sprint-2-Desarrollo-AtencionDeAgente-20261005-V1.0.php" class="sidebar-link <?php echo ($paginaActual === 'GEX-EC-05-SCRUM-Sprint-2-Desarrollo-AtencionDeAgente-20261005-V1.0.php') ? 'active' : ''; ?>">Mis Atenciones</a></li>
        <?php endif; ?>

        <li><a href="GEX-EC-05-SCRUM-Sprint-2-Desarrollo-Notificaciones-20261005-V1.0.php" class="sidebar-link <?php echo ($paginaActual === 'GEX-EC-05-SCRUM-Sprint-2-Desarrollo-Notificaciones-20261005-V1.0.php') ? 'active' : ''; ?>">Notificaciones</a></li>

        <?php if ($rolUsuario === 'Auditor'): ?>
            <li><a href="#" class="sidebar-link disabled" title="Sección no funcional">Auditoría</a></li>
        <?php endif; ?>

        <?php if ($rolUsuario === 'Admin'): ?>
            <li>
                <a href="GEX-EC-05-SCRUM-Sprint-2-Desarrollo-AdministracionDeUsuarios-20261005-V1.0.php" class="sidebar-link <?php echo ($paginaActual === 'GEX-EC-05-SCRUM-Sprint-2-Desarrollo-AdministracionDeUsuarios-20261005-V1.0.php') ? 'active' : ''; ?>">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Gestión Usuarios
                </a>
            </li>
        <?php endif; ?>
    </ul>
</aside>
