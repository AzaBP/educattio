<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../vo/usuarioVO.php';

class UsuarioDAO {
    private $db;
    public function __construct($conexion) { $this->db = $conexion; }

    public function obtenerPorId($id) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new UsuarioVO($row['id'], $row['nombre_completo'], $row['nombre_usuario'], $row['email'], $row['telefono'], $row['password'], $row['fecha_nacimiento'], $row['formacion_academica'], $row['experiencia_laboral']);
        }
        return null;
    }
}
?>