<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../vo/claseVO.php';

class ClaseDAO {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    public function listarPorCurso($curso_id) {
        $sql = "SELECT id, nombre_clase, materia_principal, curso_id, color_clase, icono_clase FROM clases WHERE curso_id = :curso_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':curso_id', $curso_id, PDO::PARAM_INT);
        $stmt->execute();

        $clases = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $clases[] = new ClaseVO(
                $row['id'], 
                $row['nombre_clase'], 
                $row['materia_principal'], 
                $row['curso_id'],
                $row['color_clase'],
                $row['icono_clase']
            );
        }
        return $clases;
    }

    public function insertar(ClaseVO $clase) {
        $sql = "INSERT INTO clases (nombre_clase, materia_principal, curso_id, color_clase, icono_clase) 
                VALUES (:nombre, :materia, :curso_id, :color, :icono)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nombre' => $clase->nombre_clase,
            ':materia' => $clase->materia_principal,
            ':curso_id' => $clase->curso_id,
            ':color' => $clase->color_clase,
            ':icono' => $clase->icono_clase
        ]);
    }
    /**
     * Actualiza los datos de una clase existente
     */
    public function actualizarClase($id, $nombre, $materia, $color, $icono) {
        $sql = "UPDATE clases SET nombre_clase = :nombre, materia_principal = :materia, color_clase = :color, icono_clase = :icono WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nombre' => $nombre,
            ':materia' => $materia,
            ':color' => $color,
            ':icono' => $icono,
            ':id' => $id
        ]);
    }

    /**
     * Devuelve los datos de una clase por su ID
     */
    public function obtenerPorId($id) {
        $sql = "SELECT id, nombre_clase, materia_principal, curso_id, color_clase, icono_clase FROM clases WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new ClaseVO(
                $row['id'],
                $row['nombre_clase'],
                $row['materia_principal'],
                $row['curso_id'],
                $row['color_clase'],
                $row['icono_clase']
            );
        }
        return null;
    }
}
?>