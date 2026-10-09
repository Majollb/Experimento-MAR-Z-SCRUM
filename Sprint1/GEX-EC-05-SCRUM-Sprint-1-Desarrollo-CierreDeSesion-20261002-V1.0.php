<?php
session_start();
session_unset();
session_destroy();
header("Location: GEX-EC-05-SCRUM-Sprint-1-Desarrollo-InicioDeSesion-20261002-V1.0.php");
exit();
?>