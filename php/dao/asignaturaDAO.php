<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../vo/AsignaturaVO.php';

class AsignaturaDAO {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    public function insertar(AsignaturaVO $asignatura) {
        $sql = "INSERT INTO asignaturas (nombre_asignatura, clase_id, color_asignatura, icono_asignatura) VALUES (:nombre, :clase_id, :color, :icono)";
        $stmt = $this->db->prepare($sql);
        
        $nombre = $asignatura->getNombreAsignatura();
        $clase_id = $asignatura->getClaseId();
        $color = $asignatura->getColorAsignatura();
        $icono = $asignatura->getIconoAsignatura();
        
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':clase_id', $clase_id, PDO::PARAM_INT);
        $stmt->bindParam(':color', $color, PDO::PARAM_STR);
        $stmt->bindParam(':icono', $icono, PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    public function listarPorClase($clase_id) {
        $sql = "SELECT * FROM asignaturas WHERE clase_id = :clase_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':clase_id', $clase_id, PDO::PARAM_INT);
        $stmt->execute();

        $asignaturas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $asignaturas[] = new AsignaturaVO(
                $row['id'], 
                $row['nombre_asignatura'], 
                $row['clase_id'], 
                $row['color_asignatura'], 
                $row['icono_asignatura']
            );
        }
        return $asignaturas;
    }

    public function actualizar($id, $nombre, $color = null, $icono = null) {
        $sql = "UPDATE asignaturas SET nombre_asignatura = :nombre, color_asignatura = :color, icono_asignatura = :icono WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':color', $color, PDO::PARAM_STR);
        $stmt->bindParam(':icono', $icono, PDO::PARAM_STR);
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