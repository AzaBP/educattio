<?php
require_once 'conexion.php';
try {
    $conexion->exec("ALTER TABLE temas_asignatura ADD COLUMN documento VARCHAR(255) NULL");
    echo "Columna documento añadida correctamente.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "La columna documento ya existe.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
