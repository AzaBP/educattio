<?php
session_start();
session_destroy(); // Borra todos los datos de la sesión
header("Location: ../index.html"); // Te devuelve a la web principal
exit();
?>