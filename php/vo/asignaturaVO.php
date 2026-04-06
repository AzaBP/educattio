<?php
class AsignaturaVO {
    private $id;
    private $nombre_asignatura;
    private $clase_id;

    public function __construct($id = null, $nombre_asignatura = "", $clase_id = null) {
        $this->id = $id;
        $this->nombre_asignatura = $nombre_asignatura;
        $this->clase_id = $clase_id;
    }

    public function getId() { return $this->id; }
    public function getNombreAsignatura() { return $this->nombre_asignatura; }
    public function getClaseId() { return $this->clase_id; }
}
?>