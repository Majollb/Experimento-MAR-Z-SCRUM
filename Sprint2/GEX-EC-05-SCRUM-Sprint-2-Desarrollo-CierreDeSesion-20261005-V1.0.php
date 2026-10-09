<?php
session_start();
session_unset();
session_destroy();
header("Location: GEX-EC-05-SCRUM-Sprint-2-Desarrollo-InicioDeSesion-20261005-V1.0.php");
exit();
?>