<?php
class CursoVO {
    public $id;
    public $nombre_centro;
    public $anio_academico;
    public $poblacion;
    public $provincia;
    public $usuario_id;
    public $color;

    public function __construct($id, $nombre_centro, $anio_academico, $poblacion, $provincia, $usuario_id, $color = '#4a90e2') {
        $this->id = $id;
        $this->nombre_centro = $nombre_centro;
        $this->anio_academico = $anio_academico;
        $this->poblacion = $poblacion;
        $this->provincia = $provincia;
        $this->usuario_id = $usuario_id;
        $this->color = $color;
    }
}
?>