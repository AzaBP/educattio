<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    exit('No autorizado');
}

$userId = $_SESSION['usuario_id'];
$method = $_SERVER['REQUEST_METHOD'];

// Obtener eventos (GET)
if ($method === 'GET') {
    $start = $_GET['start'] ?? null;
    $end = $_GET['end'] ?? null;
    
    $sql = "SELECT e.id, e.titulo, e.descripcion, e.fecha, e.tipo_evento, e.clase_id,
                   c.nombre_clase, c.materia_principal, cu.id AS curso_id, cu.nombre_centro, cu.anio_academico, cu.poblacion, cu.provincia
            FROM eventos e
            LEFT JOIN clases c ON e.clase_id = c.id
            LEFT JOIN cursos cu ON c.curso_id = cu.id
            WHERE e.usuario_id = :user_id";
    $params = [':user_id' => $userId];
    
    if ($start && $end) {
        $sql .= " AND e.fecha BETWEEN :start AND :end";
        $params[':start'] = $start;
        $params[':end'] = $end;
    }
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear para FullCalendar
    $result = [];
    foreach ($eventos as $ev) {
        $result[] = [
            'id' => $ev['id'],
            'title' => $ev['titulo'],
            'start' => $ev['fecha'],
            'description' => $ev['descripcion'],
            'tipo' => $ev['tipo_evento'],
            'clase_id' => $ev['clase_id'],
            'class_name' => $ev['nombre_clase'],
            'subject' => $ev['materia_principal'],
            'course_id' => $ev['curso_id'],
            'center_name' => $ev['nombre_centro'],
            'course_year' => $ev['anio_academico'],
            'location' => trim(($ev['poblacion'] ?? '') . ' ' . ($ev['provincia'] ?? '')),
            'color' => getColorByTipo($ev['tipo_evento'])
        ];
    }
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

// Crear o actualizar evento (POST)
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    $titulo = $data['titulo'];
    $descripcion = $data['descripcion'] ?? '';
    $fecha = $data['fecha'];
    $tipo = $data['tipo'];
    $clase_id = !empty($data['clase_id']) ? $data['clase_id'] : null;
    
    if ($id) {
        // Actualizar
        $sql = "UPDATE eventos SET titulo=:titulo, descripcion=:descripcion, fecha=:fecha, tipo_evento=:tipo, clase_id=:clase_id 
                WHERE id=:id AND usuario_id=:user_id";
        $stmt = $conexion->prepare($sql);
        $success = $stmt->execute([
            ':titulo' => $titulo,
            ':descripcion' => $descripcion,
            ':fecha' => $fecha,
            ':tipo' => $tipo,
            ':clase_id' => $clase_id,
            ':id' => $id,
            ':user_id' => $userId
        ]);
    } else {
        // Insertar
        $sql = "INSERT INTO eventos (titulo, descripcion, fecha, tipo_evento, clase_id, usuario_id) 
                VALUES (:titulo, :descripcion, :fecha, :tipo, :clase_id, :user_id)";
        $stmt = $conexion->prepare($sql);
        $success = $stmt->execute([
            ':titulo' => $titulo,
            ':descripcion' => $descripcion,
            ':fecha' => $fecha,
            ':tipo' => $tipo,
            ':clase_id' => $clase_id,
            ':user_id' => $userId
        ]);
        if ($success) $id = $conexion->lastInsertId();
    }
    
    echo json_encode(['success' => $success, 'id' => $id]);
    exit;
}

// Eliminar evento (DELETE)
if ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        exit('ID requerido');
    }
    $stmt = $conexion->prepare("DELETE FROM eventos WHERE id = :id AND usuario_id = :user_id");
    $success = $stmt->execute([':id' => $id, ':user_id' => $userId]);
    echo json_encode(['success' => $success]);
    exit;
}

function getColorByTipo($tipo) {
    switch ($tipo) {
        case 'Examen': return '#f44336'; // rojo
        case 'Festivo': return '#4caf50'; // verde
        case 'Excursión': return '#ff9800'; // naranja
        case 'Reunión': return '#2196f3'; // azul
        default: return '#9e9e9e';
    }
}
?>