<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    // Si no hay sesión, mandamos al usuario al login
    header("Location: login.php");
    exit();
}
?>