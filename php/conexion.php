<?php
$host = "localhost";
$user = "root";
$pass = ""; 
$db   = "educattio_db";
$puerto = 3307;

try {
    // Conexión usando PDO, especificando el puerto 3307 y la codificación utf8mb4 (para tildes y emojis)
    $conexion = new PDO("mysql:host=$host;port=$puerto;dbname=$db;charset=utf8mb4", $user, $pass);
    
    // Configuramos PDO para que nos avise si hay algún error en las consultas SQL
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // Si falla la conexión, detenemos el proceso y mostramos el error
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>