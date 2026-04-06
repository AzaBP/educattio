<?php
class UsuarioVO {
    public $id;
    public $nombre_completo;
    public $nombre_usuario;
    public $email;
    public $telefono;
    public $password;
    public $fecha_nacimiento;
    public $formacion_academica;
    public $experiencia_laboral;

    public function __construct($id=null, $nombre_completo="", $nombre_usuario="", $email="", $telefono="", $password="", $fecha_nacimiento=null, $formacion="", $experiencia="") {
        $this->id = $id;
        $this->nombre_completo = $nombre_completo;
        $this->nombre_usuario = $nombre_usuario;
        $this->email = $email;
        $this->telefono = $telefono;
        $this->password = $password;
        $this->fecha_nacimiento = $fecha_nacimiento;
        $this->formacion_academica = $formacion;
        $this->experiencia_laboral = $experiencia;
    }
}
?>