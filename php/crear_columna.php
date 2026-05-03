<?php
session_start();
include 'conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'mensaje' => 'No autorizado']);
    exit();
}

$datos = json_decode(file_get_contents("php://input"), true);

if(isset($datos['nombre_item']) && isset($datos['peso']) && isset($datos['periodo_evaluacion'])) {
    
    $titulo = trim($datos['nombre_item']);
    $peso = floatval($datos['peso']);
    // Ahora 'periodo_evaluacion' trae un número (el ID del periodo) desde el select oculto
    $periodo_id = intval($datos['periodo_evaluacion']); 
    $asignatura_id = $datos['asignatura_id']; 

    $formula = isset($datos['formula']) ? trim($datos['formula']) : null;

    try {
        // Insertamos usando la nueva columna periodo_id y la columna formula
        $sql = "INSERT INTO items_evaluacion (titulo, peso, periodo_id, asignatura_id, formula) 
                VALUES (:titulo, :peso, :periodo, :asignatura, :formula)";
        $stmt = $conexion->prepare($sql);
        
        $stmt->execute([
            ':titulo' => $titulo,
            ':peso' => $peso,
            ':periodo' => $periodo_id,
            ':asignatura' => $asignatura_id,
            ':formula' => $formula
        ]);
        
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'mensaje' => 'Datos incompletos']);
}
?>