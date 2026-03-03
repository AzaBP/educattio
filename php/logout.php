<?php
session_start();
session_destroy(); // Borra todos los datos de la sesión
header("Location: inicio_web.html"); // Te devuelve a la web principal
exit();
?>