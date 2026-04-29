<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../vo/AsignaturaVO.php';

class AsignaturaDAO {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    public function insertar(AsignaturaVO $asignatura) {
        $sql = "INSERT INTO asignaturas (nombre_asignatura, clase_id) VALUES (:nombre, :clase_id)";
        $stmt = $this->db->prepare($sql);
        
        $nombre = $asignatura->getNombreAsignatura();
        $clase_id = $asignatura->getClaseId();
        
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':clase_id', $clase_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function listarPorClase($clase_id) {
        $sql = "SELECT * FROM asignaturas WHERE clase_id = :clase_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':clase_id', $clase_id, PDO::PARAM_INT);
        $stmt->execute();

        $asignaturas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $asignaturas[] = new AsignaturaVO($row['id'], $row['nombre_asignatura'], $row['clase_id']);
        }
        return $asignaturas;
    }

    public function actualizar($id, $nombre_asignatura) {
        $sql = "UPDATE asignaturas SET nombre_asignatura = :nombre WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':nombre', $nombre_asignatura, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function eliminar($id) {
        $sql = "DELETE FROM asignaturas WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>