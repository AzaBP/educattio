<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../vo/AlumnoVO.php';

class AlumnoDAO {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    // Listar alumnos de una clase específica
    public function listarPorClase($clase_id) {
        $sql = "SELECT * FROM alumnos WHERE clase_id = :clase_id ORDER BY nombre_alumno ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':clase_id', $clase_id, PDO::PARAM_INT);
        $stmt->execute();

        $alumnos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $alumnos[] = new AlumnoVO($row['id'], $row['nombre_alumno'], $row['datos_personales'], $row['observaciones'], $row['clase_id']);
        }
        return $alumnos;
    }

    // Obtener todos los datos de un alumno (Ficha individual)
    public function obtenerPorId($id) {
        $sql = "SELECT * FROM alumnos WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new AlumnoVO($row['id'], $row['nombre_alumno'], $row['datos_personales'], $row['observaciones'], $row['clase_id']);
        }
        return null;
    }

    // Actualizar registro de observaciones
    public function actualizarObservaciones($id, $observaciones) {
        $sql = "UPDATE alumnos SET observaciones = :obs WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':obs', $observaciones);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>