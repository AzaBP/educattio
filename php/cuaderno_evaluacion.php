<?php
session_start();
include 'conexion.php';

// Verificación de seguridad básica
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../interfaces/inicio_sesion.html");
    exit();
}

// Simulamos la obtención de datos de la base de datos para este ejemplo
// En la vida real, sacarías esto con un SELECT WHERE clase_id = X
$asignatura_id = 1; 
$alumnos = [
    ['id' => 1, 'nombre' => 'García, Ana'],
    ['id' => 2, 'nombre' => 'López, Carlos'],
    ['id' => 3, 'nombre' => 'Martínez, Lucía'],
    ['id' => 4, 'nombre' => 'Pérez, Javier']
];

$items_evaluacion = [
    ['id' => 101, 'titulo' => 'Examen T1', 'peso' => 40],
    ['id' => 102, 'titulo' => 'Proyecto', 'peso' => 40],
    ['id' => 103, 'titulo' => 'Actitud', 'peso' => 20]
];

// Función temporal para simular notas (aquí harías un SELECT a la tabla evaluaciones)
function obtenerNotaTemporal($alumno_id, $item_id) {
    return ""; // Devuelve vacío por defecto
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educattio - Cuaderno de Notas</title>
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/portal_inicio_usuario.css"> <link rel="stylesheet" href="../css/cuaderno.css"> <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

    <div class="dashboard-layout">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header-flex">
                <div>
                    <h1>Cuaderno de Evaluación</h1>
                    <p>Matemáticas - 1º Bachillerato A</p>
                </div>
                <div class="header-actions">
                    <button class="btn btn-secondary"><i class="fas fa-file-excel"></i> Exportar</button>
                    <button class="btn btn-primary" onclick="abrirModalColumna()"><i class="fas fa-plus"></i> Añadir Columna</button>
                </div>
            </div>

            <div class="spreadsheet-container">
                <table class="gradebook-table">
                    <thead>
                        <tr>
                            <th class="sticky-col">Alumno</th>
                            <?php foreach ($items_evaluacion as $item): ?>
                                <th>
                                    <div class="col-title"><?php echo htmlspecialchars($item['titulo']); ?></div>
                                    <div class="col-weight"><?php echo $item['peso']; ?>%</div>
                                </th>
                            <?php endforeach; ?>
                            <th class="col-final">Media Final</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alumnos as $alumno): ?>
                        <tr>
                            <td class="sticky-col student-name">
                                <?php echo htmlspecialchars($alumno['nombre']); ?>
                            </td>
                            
                            <?php foreach ($items_evaluacion as $item): ?>
                                <td>
                                    <input type="number" 
                                           class="grade-input" 
                                           data-alumno="<?php echo $alumno['id']; ?>" 
                                           data-item="<?php echo $item['id']; ?>" 
                                           data-peso="<?php echo $item['peso']; ?>"
                                           value="<?php echo obtenerNotaTemporal($alumno['id'], $item['id']); ?>" 
                                           step="0.1" min="0" max="10" placeholder="-">
                                </td>
                            <?php endforeach; ?>
                            
                            <td class="final-grade" id="media-<?php echo $alumno['id']; ?>">0.00</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="../js/cuaderno.js"></script>
</body>
</html>