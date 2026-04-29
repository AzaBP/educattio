-- 1. Crear la base de datos
CREATE DATABASE IF NOT EXISTS educattio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE educattio_db;

-- 2. Tabla de Usuarios (Profesores)
-- Basada en registro_usuario.html y perfil_usuario.html
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(100) NOT NULL,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    password VARCHAR(255) NOT NULL, -- Almacenaremos el hash de la clave
    fecha_nacimiento DATE,
    formacion_academica TEXT,
    experiencia_laboral TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabla de Cursos Académicos
-- Basada en portal_cursos.html y detalles_curso.html
CREATE TABLE cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_centro VARCHAR(150) NOT NULL,
    anio_academico VARCHAR(20) NOT NULL, -- Ej: "2025-2026"
    poblacion VARCHAR(100),
    provincia VARCHAR(100),
    usuario_id INT NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- 4. Tabla de Clases / Grupos
CREATE TABLE clases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_clase VARCHAR(50) NOT NULL, -- Ej: "1º ESO A"
    curso_id INT NOT NULL,
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE
);

-- 5. Tabla de Asignaturas
CREATE TABLE asignaturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_asignatura VARCHAR(100) NOT NULL,
    clase_id INT NOT NULL,
    FOREIGN KEY (clase_id) REFERENCES clases(id) ON DELETE CASCADE
);

-- 6. Tabla de Alumnos
CREATE TABLE alumnos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_alumno VARCHAR(150) NOT NULL,
    datos_personales TEXT,
    observaciones TEXT,
    clase_id INT NOT NULL,
    FOREIGN KEY (clase_id) REFERENCES clases(id) ON DELETE CASCADE
);

-- 7. Tabla de Evaluaciones (Notas)
-- Permite registrar diferentes tipos como exámenes o proyectos
CREATE TABLE evaluaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alumno_id INT NOT NULL,
    asignatura_id INT NOT NULL,
    tipo_evaluacion ENUM('Examen', 'Proyecto', 'Observación', 'Otro') DEFAULT 'Examen',
    nota DECIMAL(4,2),
    comentarios TEXT,
    fecha_evaluacion DATE,
    FOREIGN KEY (alumno_id) REFERENCES alumnos(id) ON DELETE CASCADE,
    FOREIGN KEY (asignatura_id) REFERENCES asignaturas(id) ON DELETE CASCADE
);

-- 8. Tabla de Eventos (Calendario)
CREATE TABLE eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha DATETIME NOT NULL,
    tipo_evento ENUM('Examen', 'Festivo', 'Excursión', 'Reunión') NOT NULL,
    clase_id INT, -- Puede ser NULL si es un evento general del profesor
    usuario_id INT NOT NULL,
    FOREIGN KEY (clase_id) REFERENCES clases(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla para las columnas del cuaderno (Ej: "Examen T1", "Participación", "Proyecto")
CREATE TABLE items_evaluacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    peso DECIMAL(5,2) DEFAULT 100.00, -- Porcentaje del valor de esta nota (0-100)
    asignatura_id INT NOT NULL,
    FOREIGN KEY (asignatura_id) REFERENCES asignaturas(id) ON DELETE CASCADE
);

-- Modificar o asegurar que la tabla evaluaciones permita la edición rápida
-- Relacionamos cada nota de un alumno con un ítem específico
ALTER TABLE evaluaciones ADD COLUMN item_id INT AFTER asignatura_id;
ALTER TABLE evaluaciones ADD FOREIGN KEY (item_id) REFERENCES items_evaluacion(id) ON DELETE CASCADE;



-- ################ NUEVO ##################
ALTER TABLE clases 
ADD COLUMN materia_principal VARCHAR(100) AFTER nombre_clase,
ADD COLUMN color_clase VARCHAR(30) DEFAULT 'color-1',
ADD COLUMN icono_clase VARCHAR(50) DEFAULT 'fa-chalkboard-teacher';

ALTER TABLE cursos ADD color VARCHAR(7) NOT NULL DEFAULT '#4a90e2';

-- Crear la tabla de periodos personalizables por asignatura
CREATE TABLE IF NOT EXISTS periodos_evaluacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_periodo VARCHAR(50) NOT NULL,
    asignatura_id INT NOT NULL,
    FOREIGN KEY (asignatura_id) REFERENCES asignaturas(id) ON DELETE CASCADE
);

-- Asegurarnos de que la tabla items_evaluacion apunte a este nuevo periodo
-- (Si ya tenías la tabla items_evaluacion creada, borramos la columna vieja y añadimos la nueva)
ALTER TABLE items_evaluacion DROP COLUMN IF EXISTS periodo_evaluacion;
ALTER TABLE items_evaluacion ADD COLUMN periodo_id INT NOT NULL;
ALTER TABLE items_evaluacion ADD FOREIGN KEY (periodo_id) REFERENCES periodos_evaluacion(id) ON DELETE CASCADE;

CREATE TABLE alumnos_asignaturas (
    alumno_id INT NOT NULL,
    asignatura_id INT NOT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (alumno_id, asignatura_id),
    FOREIGN KEY (alumno_id) REFERENCES alumnos(id) ON DELETE CASCADE,
    FOREIGN KEY (asignatura_id) REFERENCES asignaturas(id) ON DELETE CASCADE
);
