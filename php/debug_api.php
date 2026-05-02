<?php
session_start();
$_SESSION['usuario_id'] = 1; // Forzar para pruebas si es necesario, o comentar esta línea
require_once 'conexion.php';

$usuario_id = isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : 0;
echo "Usuario ID en sesión: $usuario_id\n";

$tests = [
    [],
    ['curso_id' => 1],
    ['clase_id' => 1],
    ['asignatura_id' => 1]
];

foreach ($tests as $test) {
    echo "\nPrueba con: " . json_encode($test) . "\n";
    $_GET = $test;
    ob_start();
    include 'api_eventos.php';
    $res = ob_get_clean();
    echo "Resultado: " . $res . "\n";
}
?>
