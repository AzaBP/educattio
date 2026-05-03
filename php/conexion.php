<?php
// Configuración de conexión (Detecta si estamos en Docker o Local)
$host = getenv('DB_HOST') ?: "localhost";
$user = getenv('DB_USER') ?: "root";
$pass = getenv('DB_PASS') ?: ""; 
$db   = getenv('DB_NAME') ?: "educattio_db";
$puerto = getenv('DB_PORT') ?: 3307;

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