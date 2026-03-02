<?php
$host = "localhost";
$user = "root";
$pass = ""; // En XAMPP por defecto está vacío
$db   = "educattio_db";

try {
    // Usamos PDO porque es más seguro y moderno
    $conexion = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>