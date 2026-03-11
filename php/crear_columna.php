<?php
session_start();
include 'conexion.php';

// Configuramos la respuesta como JSON
header('Content-Type: application/json');

// Seguridad básica
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'mensaje' => 'No autorizado']);
    exit();
}

// Recibir los datos enviados por JS
$datos = json_decode(file_get_contents("php://input"), true);

if(isset($datos['nombre_item']) && isset($datos['peso']) && isset($datos['periodo_evaluacion'])) {
    
    $titulo = trim($datos['nombre_item']);
    $peso = floatval($datos['peso']);
    $periodo = $datos['periodo_evaluacion']; // Ej: "1ª Evaluación"
    $asignatura_id = $datos['asignatura_id'] ?? 1; 

    try {
        // Insertamos la columna incluyendo el periodo de evaluación
        $sql = "INSERT INTO items_evaluacion (titulo, peso, periodo_evaluacion, asignatura_id) 
                VALUES (:titulo, :peso, :periodo, :asignatura)";
        $stmt = $conexion->prepare($sql);
        
        $stmt->execute([
            ':titulo' => $titulo,
            ':peso' => $peso,
            ':periodo' => $periodo,
            ':asignatura' => $asignatura_id
        ]);
        
        echo json_encode(['status' => 'success']);

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'mensaje' => 'Error BD: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'mensaje' => 'Faltan datos por enviar']);
}
?>