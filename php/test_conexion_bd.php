<?php
$host = "localhost";
$user = "root";
$pass = ""; 
$db   = "educattio_db";
$puerto = 3307;

echo "<h1>Test de Conexión a Educattio</h1>";
echo "<p>Intentando conectar a MySQL en <strong>$host</strong> (Puerto: <strong>$puerto</strong>) con el usuario <strong>$user</strong>...</p>";

try {
    $conexion = new PDO("mysql:host=$host;port=$puerto;dbname=$db;charset=utf8mb4", $user, $pass);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2 style='color: green;'>✅ ¡Conexión Exitosa!</h2>";
    echo "<p>Se ha conectado correctamente a la base de datos <strong>$db</strong>.</p>";
    
    // Vamos a buscar las tablas que has creado para confirmar que todo está en orden
    $stmt = $conexion->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Tus tablas detectadas en el sistema:</h3>";
    if (count($tablas) > 0) {
        echo "<ul>";
        foreach ($tablas as $tabla) {
            echo "<li style='color: #0277bd; font-size: 18px;'>👉 <strong>" . htmlspecialchars($tabla) . "</strong></li>";
        }
        echo "</ul>";
        echo "<p style='color: green; font-weight: bold;'>¡Perfecto! Tus tablas están creadas y el código PHP las reconoce sin problemas.</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ La conexión funciona, pero no se han encontrado tablas. Asegúrate de haber ejecutado tu código SQL en phpMyAdmin.</p>";
    }

} catch (PDOException $e) {
    echo "<h2 style='color: red;'>❌ Error de Conexión</h2>";
    echo "<div style='background: #ffebee; border: 1px solid #ef5350; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 16px;'>";
    echo $e->getMessage();
    echo "</div>";
}
?>