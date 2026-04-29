<?php
require_once '../conexion.php';
header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);
$accion = $data['accion'] ?? '';

try {
    if ($accion === 'get_alumnos_por_asig') {
        // Alumnos de la clase y marca de si están en la asignatura
        $sql = "SELECT a.id, a.nombre_alumno, 
                (SELECT COUNT(*) FROM alumnos_asignaturas WHERE alumno_id = a.id AND asignatura_id = :asig) as matriculado
                FROM alumnos a WHERE a.clase_id = :clase ORDER BY a.nombre_alumno";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':asig' => $data['asig_id'], ':clase' => $data['clase_id']]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } 
    elseif ($accion === 'get_asig_por_alumno') {
        // Asignaturas de la clase y marca de si el alumno está dentro
        $sql = "SELECT asig.id, asig.nombre_asignatura,
                (SELECT COUNT(*) FROM alumnos_asignaturas WHERE asignatura_id = asig.id AND alumno_id = :alum) as matriculado
                FROM asignaturas asig WHERE asig.clase_id = :clase";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':alum' => $data['alumno_id'], ':clase' => $data['clase_id']]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($accion === 'guardar_matricula') {
        $conexion->beginTransaction();
        if (isset($data['asig_id'])) {
            $conexion->prepare("DELETE FROM alumnos_asignaturas WHERE asignatura_id = ?")->execute([$data['asig_id']]);
            $stmt = $conexion->prepare("INSERT INTO alumnos_asignaturas (asignatura_id, alumno_id) VALUES (?, ?)");
            foreach ($data['lista_ids'] as $idAlum) $stmt->execute([$data['asig_id'], $idAlum]);
        } else {
            $conexion->prepare("DELETE FROM alumnos_asignaturas WHERE alumno_id = ?")->execute([$data['alumno_id']]);
            $stmt = $conexion->prepare("INSERT INTO alumnos_asignaturas (alumno_id, asignatura_id) VALUES (?, ?)");
            foreach ($data['lista_ids'] as $idAsig) $stmt->execute([$data['alumno_id'], $idAsig]);
        }
        $conexion->commit();
        echo json_encode(['status' => 'success']);
    }
} catch (Exception $e) {
    if($conexion->inTransaction()) $conexion->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}