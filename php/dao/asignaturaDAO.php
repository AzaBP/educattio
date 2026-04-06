<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../vo/AsignaturaVO.php';

class AsignaturaDAO {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
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
}
?>