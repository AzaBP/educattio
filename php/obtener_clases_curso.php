<?php
session_start();
header('Content-Type: application/json');
require_once 'conexion.php';
if (!isset($_SESSION['usuario_id'])) exit(json_encode(['status'=>'error','message'=>'No autorizado']));
$curso_id = (int)$_GET['curso_id'];
$stmt = $conexion->prepare("SELECT id, nombre_clase, materia_principal, color_clase as color, icono_clase as icono FROM clases WHERE curso_id = :curso_id");
$stmt->execute([':curso_id' => $curso_id]);
$clases = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['status'=>'success', 'data'=>$clases]);
?>