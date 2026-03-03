<?php
// Datos de configuración
$host = "localhost";
$user = "root";
$pass = ""; // Si descubriste tu contraseña de MySQL, ponla entre estas comillas
$db   = "educattio_db";

echo "<h1>Test de Conexión a Educattio</h1>";
echo "<p>Intentando conectar a MySQL en <strong>$host</strong> con el usuario <strong>$user</strong>...</p>";

try {
    // Intentamos la conexión
    $conexion = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Si llegamos aquí, es que ha funcionado
    echo "<h2 style='color: green;'>✅ ¡Conexión Exitosa!</h2>";
    echo "<p>El sistema ha logrado entrar a la base de datos <strong>$db</strong> sin problemas. ¡Todo está listo para funcionar!</p>";
    
} catch (PDOException $e) {
    // Si falla, capturamos el error exacto y lo mostramos bonito
    echo "<h2 style='color: red;'>❌ Error de Conexión</h2>";
    echo "<p>No se ha podido conectar. El servidor MySQL dice lo siguiente:</p>";
    
    echo "<div style='background: #ffebee; border: 1px solid #ef5350; padding: 15px; border-radius: 8px; font-family: monospace;'>";
    echo $e->getMessage();
    echo "</div>";
    
    echo "<h3>🕵️‍♀️ Diagnóstico rápido:</h3>";
    echo "<ul>";
    echo "<li><strong>Si dice 'Access denied for user':</strong> Significa que el usuario 'root' SÍ tiene contraseña y la variable \$pass está vacía (o tiene la clave incorrecta).</li>";
    echo "<li><strong>Si dice 'Unknown database':</strong> Significa que la base de datos <em>$db</em> no existe aún en phpMyAdmin.</li>";
    echo "<li><strong>Si dice 'Connection refused':</strong> MySQL está apagado en el panel de XAMPP.</li>";
    echo "</ul>";
}
?>