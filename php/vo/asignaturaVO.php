<?php
class AsignaturaVO {
    private $color_asignatura;
    private $icono_asignatura;

    public function __construct($id = null, $nombre_asignatura = "", $clase_id = null, $color = "#4facfe", $icono = "fa-book") {
        $this->id = $id;
        $this->nombre_asignatura = $nombre_asignatura;
        $this->clase_id = $clase_id;
        $this->color_asignatura = $color;
        $this->icono_asignatura = $icono;
    }

    public function getId() { return $this->id; }
    public function getNombreAsignatura() { return $this->nombre_asignatura; }
    public function getClaseId() { return $this->clase_id; }
    public function getColorAsignatura() { return $this->color_asignatura; }
    public function getIconoAsignatura() { return $this->icono_asignatura; }
}
?>