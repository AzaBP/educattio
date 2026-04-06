<?php
class AlumnoVO {
    private $id;
    private $nombre_alumno;
    private $datos_personales;
    private $observaciones;
    private $clase_id;

    public function __construct($id = null, $nombre_alumno = "", $datos_personales = "", $observaciones = "", $clase_id = null) {
        $this->id = $id;
        $this->nombre_alumno = $nombre_alumno;
        $this->datos_personales = $datos_personales;
        $this->observaciones = $observaciones;
        $this->clase_id = $clase_id;
    }

    // Getters y Setters
    public function getId() { return $this->id; }
    public function getNombreAlumno() { return $this->nombre_alumno; }
    public function getDatosPersonales() { return $this->datos_personales; }
    public function getObservaciones() { return $this->observaciones; }
    public function getClaseId() { return $this->clase_id; }

    public function setObservaciones($obs) { $this->observaciones = $obs; }
}
?>