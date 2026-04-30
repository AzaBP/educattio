<?php
session_start();
require_once 'conexion.php';
if (!isset($_SESSION['usuario_id'])) exit('[]');
$userId = $_SESSION['usuario_id'];
$hoy = date('Y-m-d H:i:s');
$semana = date('Y-m-d H:i:s', strtotime('+7 days'));
$stmt = $conexion->prepare("SELECT id, titulo, fecha, tipo_evento FROM eventos 
                            WHERE usuario_id = :uid AND fecha BETWEEN :hoy AND :semana
                            ORDER BY fecha ASC");
$stmt->execute([':uid' => $userId, ':hoy' => $hoy, ':semana' => $semana]);
$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
header('Content-Type: application/json');
echo json_encode($eventos);
?>