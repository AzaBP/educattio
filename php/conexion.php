<?php
$host = "localhost";
$user = "root";
$pass = ""; 
$db   = "educattio_db";

try {
    // Usamos PDO porque es más seguro y moderno
    $conexion = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    // Configuramos la conexión para que nos muestre los errores si algo falla
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Si falla la conexión, mostramos el error
    echo "Error de conexión: " . $e->getMessage();
}
?>