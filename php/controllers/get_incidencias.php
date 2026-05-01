<?php
header('Content-Type: application/json');
session_start();
require_once '../conexion.php';

$usuario_id = $_SESSION['usuario_id'] ?? null;
if (!$usuario_id) {
    echo json_encode(['status' => 'error', 'message' => 'Usuario no autorizado']);
    exit;
}

try {
    $stmt = $conexion->prepare('SELECT i.fecha_incidencia, i.tipo, i.descripcion, c.nombre_centro, cl.nombre_clase, a.nombre_alumno
                                FROM incidencias i
                                JOIN cursos c ON i.curso_id = c.id
                                JOIN clases cl ON i.clase_id = cl.id
                                LEFT JOIN alumnos a ON i.alumno_id = a.id
                                WHERE i.usuario_id = :usuario_id
                                ORDER BY i.fecha_incidencia DESC');
    $stmt->execute([':usuario_id' => $usuario_id]);
    $incidencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'incidencias' => $incidencias]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
