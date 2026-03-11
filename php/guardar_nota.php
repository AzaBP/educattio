<?php
// guardar_nota.php
session_start();
include 'conexion.php'; 

// 1. Configuramos el archivo para que devuelva un formato JSON
header('Content-Type: application/json');

// 2. Seguridad: Comprobar que el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'mensaje' => 'No autorizado']);
    exit();
}

// 3. Recibir los datos crudos que nos manda JavaScript (en formato JSON)
$datos_recibidos = json_decode(file_get_contents("php://input"), true);

// 4. Verificamos que nos han enviado todos los datos necesarios
if(isset($datos_recibidos['alumno_id']) && isset($datos_recibidos['item_id']) && isset($datos_recibidos['nota'])) {
    
    $alumno_id = $datos_recibidos['alumno_id'];
    $item_id = $datos_recibidos['item_id'];
    $nota = floatval($datos_recibidos['nota']); 
    // Por si necesitas vincularlo a la asignatura directamente
    $asignatura_id = $datos_recibidos['asignatura_id'] ?? 1; 

    try {
        // A. Primero, comprobamos si YA existe una nota para este alumno en este examen/trabajo
        $sql_check = "SELECT id FROM evaluaciones WHERE alumno_id = :alumno AND item_id = :item";
        $stmt_check = $conexion->prepare($sql_check);
        $stmt_check->execute([':alumno' => $alumno_id, ':item' => $item_id]);

        if ($stmt_check->rowCount() > 0) {
            // Si la nota ya existe, hacemos un UPDATE (Actualizar)
            $sql = "UPDATE evaluaciones SET nota = :nota, fecha_evaluacion = CURRENT_DATE WHERE alumno_id = :alumno AND item_id = :item";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                ':nota' => $nota, 
                ':alumno' => $alumno_id, 
                ':item' => $item_id
            ]);
            echo json_encode(['status' => 'success', 'accion' => 'actualizado']);
        
        } else {
            // Si la nota NO existe, hacemos un INSERT (Crear nueva)
            $sql = "INSERT INTO evaluaciones (alumno_id, asignatura_id, item_id, nota, fecha_evaluacion) 
                    VALUES (:alumno, :asignatura, :item, :nota, CURRENT_DATE)";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                ':alumno' => $alumno_id, 
                ':asignatura' => $asignatura_id, 
                ':item' => $item_id, 
                ':nota' => $nota
            ]);
            echo json_encode(['status' => 'success', 'accion' => 'insertado']);
        }

    } catch (PDOException $e) {
        // Si la base de datos da un error, lo enviamos de vuelta al navegador
        echo json_encode(['status' => 'error', 'mensaje' => 'Error BD: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'mensaje' => 'Datos incompletos enviados desde la tabla']);
}
?>