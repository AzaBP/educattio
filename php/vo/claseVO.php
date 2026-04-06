<?php
class ClaseVO {
    public $id;
    public $nombre_clase;
    public $materia_principal; // Nueva columna
    public $curso_id;
    public $color_clase;       // Nueva columna para la UI
    public $icono_clase;       // Nueva columna para la UI

    public function __construct($id=null, $nombre_clase="", $materia_principal="", $curso_id=null, $color="color-1", $icono="fa-chalkboard-teacher") {
        $this->id = $id;
        $this->nombre_clase = $nombre_clase;
        $this->materia_principal = $materia_principal;
        $this->curso_id = $curso_id;
        $this->color_clase = $color;
        $this->icono_clase = $icono;
    }
}
?>