<?php
require_once 'conexion.php';
try {
    $stmt = $conexion->query("SHOW COLUMNS FROM eventos LIKE 'asignatura_id'");
    $column = $stmt->fetch();
    if ($column) {
        echo "COLUMNA EXISTE";
    } else {
        echo "COLUMNA NO EXISTE";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
