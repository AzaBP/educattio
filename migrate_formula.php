<?php
require_once 'php/conexion.php';
try {
    $conexion->exec("ALTER TABLE items_evaluacion ADD COLUMN formula VARCHAR(255) DEFAULT NULL");
    echo "Columna 'formula' añadida con éxito.";
} catch (PDOException $e) {
    echo "Error o la columna ya existe: " . $e->getMessage();
}
?>
