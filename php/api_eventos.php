<?php
/**
 * api_eventos.php - Endpoint unificado para el sistema de calendarios
 *
 * Parámetros GET:
 *   ?curso_id=X       → eventos de todas las clases de ese curso
 *   ?clase_id=X       → eventos de esa clase
 *   ?asignatura_id=X  → eventos de la clase a la que pertenece la asignatura
 *   (ninguno)         → todos los eventos del usuario
 *
 * Respuesta: JSON { status: 'success', events: [...] }
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado', 'events' => []]);
    exit;
}

require_once 'conexion.php';
$usuario_id = (int)$_SESSION['usuario_id'];

// Auto-migración para añadir columnas si no existen
try {
    $conexion->exec("ALTER TABLE eventos ADD COLUMN asignatura_id INT NULL");
} catch (PDOException $e) {}
try {
    $conexion->exec("ALTER TABLE eventos ADD COLUMN curso_id INT NULL");
} catch (PDOException $e) {}

$curso_id      = isset($_GET['curso_id'])      ? (int)$_GET['curso_id']      : 0;
$clase_id      = isset($_GET['clase_id'])      ? (int)$_GET['clase_id']      : 0;
$asignatura_id = isset($_GET['asignatura_id']) ? (int)$_GET['asignatura_id'] : 0;

$clase_id_from_asig = 0;
// Si viene asignatura_id, resolver el clase_id correspondiente para búsquedas por clase si fuera necesario
if ($asignatura_id > 0) {
    $stmtAsig = $conexion->prepare("SELECT clase_id FROM asignaturas WHERE id = :id");
    $stmtAsig->execute([':id' => $asignatura_id]);
    $rowAsig = $stmtAsig->fetch(PDO::FETCH_ASSOC);
    if ($rowAsig) {
        $clase_id_from_asig = (int)$rowAsig['clase_id'];
    }
}

try {
    $sql = "SELECT 
                e.id,
                e.titulo,
                e.descripcion,
                e.fecha,
                e.tipo_evento,
                e.clase_id,
                e.asignatura_id,
                e.curso_id,
                c.nombre_clase,
                c.materia_principal,
                cu.nombre_centro,
                cu.anio_academico,
                a.nombre_asignatura
            FROM eventos e
            LEFT JOIN clases c       ON e.clase_id      = c.id
            LEFT JOIN asignaturas a  ON e.asignatura_id = a.id
            LEFT JOIN cursos cu      ON (e.curso_id = cu.id OR c.curso_id = cu.id)
            WHERE e.usuario_id = :user_id";

    $params = [':user_id' => $usuario_id];

    if ($asignatura_id > 0) {
        $sql .= " AND e.asignatura_id = :asignatura_id";
        $params[':asignatura_id'] = $asignatura_id;
    } elseif ($clase_id > 0) {
        $sql .= " AND (e.clase_id = :clase_id OR e.asignatura_id IN (SELECT id FROM asignaturas WHERE clase_id = :clase_id))";
        $params[':clase_id'] = $clase_id;
    } elseif ($curso_id > 0) {
        $sql .= " AND (e.curso_id = :curso_id OR e.clase_id IN (SELECT id FROM clases WHERE curso_id = :curso_id) OR e.asignatura_id IN (SELECT id FROM asignaturas WHERE clase_id IN (SELECT id FROM clases WHERE curso_id = :curso_id)))";
        $params[':curso_id'] = $curso_id;
    }
    // Sin filtro: todos los eventos del usuario

    $sql .= " ORDER BY e.fecha ASC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $events = [];
    foreach ($rows as $ev) {
        // Determinar origen para mostrar en la UI
        $sourceType = 'General';
        $sourceName = '';
        if ($ev['nombre_asignatura']) {
            $sourceType = 'Asignatura';
            $sourceName = $ev['nombre_asignatura'];
        } elseif ($ev['nombre_clase']) {
            $sourceType = 'Clase';
            $sourceName = $ev['nombre_clase'];
        } elseif ($ev['nombre_centro']) {
            $sourceType = 'Curso';
            $sourceName = $ev['nombre_centro'];
        }

        $events[] = [
            'id'             => $ev['id'],
            'titulo'         => $ev['titulo'],
            'descripcion'    => $ev['descripcion'],
            'fecha'          => $ev['fecha'],
            'fecha_formateada' => date('d/m/Y H:i', strtotime($ev['fecha'])),
            'tipo_evento'    => $ev['tipo_evento'],
            'clase_id'       => $ev['clase_id'],
            'asignatura_id'  => $ev['asignatura_id'],
            'curso_id'       => $ev['curso_id'],
            'nombre_clase'   => $ev['nombre_clase'] ?? 'General',
            'source_type'    => $sourceType,
            'source_name'    => $sourceName
        ];
    }

    echo json_encode(['status' => 'success', 'events' => $events]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage(), 'events' => []]);
}
