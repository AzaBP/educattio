<?php
class EvaluacionDAO {
    private $db;
    public function __construct($conexion) { $this->db = $conexion; }

    public function obtenerNotasPorAsignatura($asignatura_id) {
        $stmt = $this->db->prepare("SELECT * FROM evaluaciones WHERE asignatura_id = ?");
        $stmt->execute([$asignatura_id]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'EvaluacionVO');
    }

    public function guardarNota(EvaluacionVO $ev) {
        $sql = "INSERT INTO evaluaciones (alumno_id, asignatura_id, item_id, tipo_evaluacion, nota, comentarios, fecha_evaluacion) 
                VALUES (?, ?, ?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE nota = VALUES(nota), comentarios = VALUES(comentarios)";
        return $this->db->prepare($sql)->execute([$ev->alumno_id, $ev->asignatura_id, $ev->item_id, $ev->tipo_evaluacion, $ev->nota, $ev->comentarios, $ev->fecha_evaluacion]);
    }
}
?>