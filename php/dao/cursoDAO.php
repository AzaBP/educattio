<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../vo/cursoVO.php';

class CursoDAO {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    public function listarPorUsuario($usuario_id) {
    $sql = "SELECT * FROM cursos WHERE usuario_id = :uid ORDER BY id DESC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':uid' => $usuario_id]);
    
    $cursos = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $cursos[] = new CursoVO(
            $row['id'], $row['nombre_centro'], $row['anio_academico'], 
            $row['poblacion'], $row['provincia'], $row['usuario_id'], 
            $row['color']
        );
    }
    return $cursos;
}

    public function obtenerPorId($id) {
        $sql = "SELECT * FROM cursos WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        return new CursoVO(
            $row['id'],
            $row['nombre_centro'],
            $row['anio_academico'],
            $row['poblacion'],
            $row['provincia'],
            $row['usuario_id'],
            $row['color'] // Nuevo campo color
        );
    }

    public function insertar(CursoVO $curso) {
        $sql = "INSERT INTO cursos (nombre_centro, anio_academico, poblacion, provincia, usuario_id, color) 
                VALUES (:centro, :anio, :pob, :prov, :uid, :color)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':centro' => $curso->nombre_centro,
            ':anio'   => $curso->anio_academico,
            ':pob'    => $curso->poblacion,
            ':prov'   => $curso->provincia,
            ':uid'    => $curso->usuario_id,
            ':color'  => $curso->color 
        ]);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM cursos WHERE id = :id";
        return $this->db->prepare($sql)->execute([':id' => $id]);
    }

    public function actualizar(CursoVO $curso) {
        $sql = "UPDATE cursos 
                SET nombre_centro = :centro, 
                    anio_academico = :anio, 
                    poblacion = :pob, 
                    provincia = :prov, 
                    color = :color 
                WHERE id = :id AND usuario_id = :uid";
                
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':centro' => $curso->nombre_centro,
            ':anio'   => $curso->anio_academico,
            ':pob'    => $curso->poblacion,
            ':prov'   => $curso->provincia,
            ':color'  => $curso->color,
            ':id'     => $curso->id,
            ':uid'    => $curso->usuario_id
        ]);
    }
}
?>