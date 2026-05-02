<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$userId = $_SESSION['usuario_id'];
$method = $_SERVER['REQUEST_METHOD'];

// Obtener eventos (GET)
if ($method === 'GET') {
    $start = $_GET['start'] ?? null;
    $end = $_GET['end'] ?? null;
    
    $sql = "SELECT e.id, e.titulo, e.descripcion, e.fecha, e.tipo_evento, e.clase_id, e.asignatura_id, e.curso_id,
                   c.nombre_clase, c.materia_principal, cu.nombre_centro, cu.anio_academico, cu.poblacion, cu.provincia
            FROM eventos e
            LEFT JOIN clases c ON e.clase_id = c.id
            LEFT JOIN cursos cu ON (e.curso_id = cu.id OR c.curso_id = cu.id)
            WHERE e.usuario_id = :user_id";
    $params = [':user_id' => $userId];
    
    if ($start && $end) {
        $sql .= " AND e.fecha BETWEEN :start AND :end";
        $params[':start'] = $start;
        $params[':end'] = $end;
    }
    
    try {
        $stmt = $conexion->prepare($sql);
        $stmt->execute($params);
        $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error obteniendo eventos']);
        exit;
    }
    
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
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'JSON inválido']);
        exit;
    }

    if (!empty($input['id'])) {
        // Actualizar (admite actualización parcial: solo fecha al arrastrar/resize)
        $stmtCurrent = $conexion->prepare("SELECT titulo, fecha, tipo_evento, descripcion, clase_id, asignatura_id FROM eventos WHERE id = :id AND usuario_id = :user_id");
        $stmtCurrent->execute([':id' => $input['id'], ':user_id' => $userId]);
        $current = $stmtCurrent->fetch(PDO::FETCH_ASSOC);
        if (!$current) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Evento no encontrado']);
            exit;
        }

        $titulo = $input['titulo'] ?? $current['titulo'];
        $fecha = $input['fecha'] ?? $current['fecha'];
        $tipo = $input['tipo'] ?? $current['tipo_evento'] ?? 'Reunión';
        $descripcion = array_key_exists('descripcion', $input) ? $input['descripcion'] : ($current['descripcion'] ?? '');
        $claseId = array_key_exists('clase_id', $input) ? $input['clase_id'] : $current['clase_id'];
        $asignaturaId = array_key_exists('asignatura_id', $input) ? $input['asignatura_id'] : ($current['asignatura_id'] ?? null);

        $sql = "UPDATE eventos SET titulo = :titulo, fecha = :fecha, tipo_evento = :tipo, descripcion = :descripcion, clase_id = :clase_id, asignatura_id = :asignatura_id, curso_id = :curso_id WHERE id = :id AND usuario_id = :user_id";
        $stmt = $conexion->prepare($sql);
        $ok = $stmt->execute([
            ':id' => $input['id'],
            ':titulo' => $titulo,
            ':fecha' => $fecha,
            ':tipo' => $tipo,
            ':descripcion' => $descripcion,
            ':clase_id' => $claseId,
            ':asignatura_id' => $asignaturaId,
            ':curso_id' => $input['curso_id'] ?? $current['curso_id'],
            ':user_id' => $userId
        ]);
        header('Content-Type: application/json');
        echo json_encode(['success' => (bool)$ok, 'message' => $ok ? 'Evento actualizado' : 'No se pudo actualizar']);
    } else {
        // Crear
        $titulo = trim((string)($input['titulo'] ?? ''));
        $fecha = $input['fecha'] ?? null;
        if ($titulo === '' || empty($fecha)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Título y fecha son obligatorios']);
            exit;
        }

        $sql = "INSERT INTO eventos (usuario_id, titulo, fecha, tipo_evento, descripcion, clase_id, asignatura_id, curso_id) VALUES (:user_id, :titulo, :fecha, :tipo, :descripcion, :clase_id, :asignatura_id, :curso_id)";
        $stmt = $conexion->prepare($sql);
        $ok = $stmt->execute([
            ':user_id' => $userId,
            ':titulo' => $titulo,
            ':fecha' => $fecha,
            ':tipo' => $input['tipo'] ?? 'Reunión',
            ':descripcion' => $input['descripcion'] ?? '',
            ':clase_id' => $input['clase_id'] ?? null,
            ':asignatura_id' => $input['asignatura_id'] ?? null,
            ':curso_id' => $input['curso_id'] ?? null
        ]);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => (bool)$ok,
            'message' => $ok ? 'Evento creado' : 'No se pudo crear',
            'id' => $ok ? $conexion->lastInsertId() : null
        ]);
    }
    exit;
}

// Eliminar evento (DELETE)
if ($method === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (empty($input['id'])) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'ID requerido']);
        exit;
    }
    $sql = "DELETE FROM eventos WHERE id = :id AND usuario_id = :user_id";
    $stmt = $conexion->prepare($sql);
    $ok = $stmt->execute([':id' => $input['id'], ':user_id' => $userId]);
    header('Content-Type: application/json');
    echo json_encode(['success' => (bool)$ok]);
    exit;
}

function getColorByTipo($tipo) {
    $colors = [
        'Examen' => '#e74c3c',
        'Festivo' => '#3498db',
        'Excursión' => '#2ecc71',
        'Reunión' => '#f39c12',
        'General' => '#95a5a6'
    ];
    return $colors[$tipo] ?? '#f39c12';
}
header('Content-Type: application/json');
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Método no permitido']);