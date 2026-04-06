<?php
class EvaluacionVO {
    public $id;
    public $alumno_id;
    public $asignatura_id;
    public $item_id;
    public $tipo_evaluacion;
    public $nota;
    public $comentarios;
    public $fecha_evaluacion;

    public function __construct($id=null, $alumno_id=null, $asignatura_id=null, $item_id=null, $tipo='Examen', $nota=null, $comentarios="", $fecha=null) {
        $this->id = $id;
        $this->alumno_id = $alumno_id;
        $this->asignatura_id = $asignatura_id;
        $this->item_id = $item_id;
        $this->tipo_evaluacion = $tipo;
        $this->nota = $nota;
        $this->comentarios = $comentarios;
        $this->fecha_evaluacion = $fecha;
    }
}
?>