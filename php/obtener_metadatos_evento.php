<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$type = $_GET['type'] ?? 'all';

try {
    if ($type === 'clases' || $type === 'all') {
        // Obtener clases del usuario con info del curso
        $sqlClases = "SELECT c.id, c.nombre_clase, cu.nombre_centro, cu.anio_academico, cu.id as curso_id
                      FROM clases c
                      JOIN cursos cu ON c.curso_id = cu.id
                      WHERE cu.usuario_id = :user_id
                      ORDER BY cu.anio_academico DESC, c.nombre_clase ASC";
        $stmt = $conexion->prepare($sqlClases);
        $stmt->execute([':user_id' => $usuario_id]);
        $clases = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    if ($type === 'asignaturas' || $type === 'all') {
        $clase_id = $_GET['clase_id'] ?? null;
        
        $sqlAsig = "SELECT a.id, a.nombre_asignatura, a.clase_id
                    FROM asignaturas a
                    JOIN clases c ON a.clase_id = c.id
                    JOIN cursos cu ON c.curso_id = cu.id
                    WHERE cu.usuario_id = :user_id";
        
        if ($clase_id) {
            $sqlAsig .= " AND a.clase_id = :clase_id";
            $stmt = $conexion->prepare($sqlAsig);
            $stmt->execute([':user_id' => $usuario_id, ':clase_id' => $clase_id]);
        } else {
            $stmt = $conexion->prepare($sqlAsig);
            $stmt->execute([':user_id' => $usuario_id]);
        }
        $asignaturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode([
        'success' => true,
        'clases' => $clases ?? [],
        'asignaturas' => $asignaturas ?? []
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
