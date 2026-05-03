<?php
require_once __DIR__ . '/controllers/auth_check.php';
require_once 'conexion.php';

$usuario_id = $_SESSION['usuario_id'];
try {
    $stmt = $conexion->prepare('SELECT id, nombre_centro, anio_academico FROM cursos WHERE usuario_id = :uid ORDER BY nombre_centro');
    $stmt->execute([':uid' => $usuario_id]);
    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $cursos = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educattio - Incidencias</title>
    <link rel="icon" type="image/png" href="../imagenes/dolphin.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/portal_inicio_usuario.css">
    <link rel="stylesheet" href="../css/incidencias.css?v=2.5">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <!-- CABECERA PREMIUM -->
            <header class="course-page-header-modern mb-4">
                <div class="header-main-content">
                    <div class="header-left">
                        <h1 class="course-title-animate">Registro de Incidencias</h1>
                        <p class="text-white-50 m-0">Gestiona y exporta reportes detallados sobre el centro, clases y alumnos.</p>
                    </div>
                </div>
            </header>

            <section class="incidencias-grid">
                <div class="incidencias-form-card">
                    <div class="card-header mb-3">
                        <h2>Crear nueva incidencia</h2>
                        <p>Selecciona centro, curso, clase y alumno (opcional).</p>
                    </div>
                    <form id="formIncidencia">
                        <div class="mb-3">
                            <label for="cursoSelect" class="form-label">Centro / Curso</label>
                            <select id="cursoSelect" class="form-select" required>
                                <option value="">Selecciona un curso</option>
                                <?php foreach ($cursos as $curso): ?>
                                    <option value="<?php echo $curso['id']; ?>"><?php echo htmlspecialchars($curso['nombre_centro'] . ' - ' . $curso['anio_academico']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="claseSelect" class="form-label">Clase</label>
                            <select id="claseSelect" class="form-select" disabled required>
                                <option value="">Selecciona primero un curso</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="alumnoSelect" class="form-label">Alumno (opcional)</label>
                            <select id="alumnoSelect" class="form-select" disabled>
                                <option value="">Selecciona primero una clase</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tipoIncidencia" class="form-label">Tipo de incidencia</label>
                            <select id="tipoIncidencia" class="form-select">
                                <option value="General">General</option>
                                <option value="Conducta">Conducta</option>
                                <option value="Salud">Salud</option>
                                <option value="Organización">Organización</option>
                                <option value="Ausencia">Ausencia</option>
                                <option value="Retraso">Retraso</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="descripcionIncidencia" class="form-label">Descripción</label>
                            <textarea id="descripcionIncidencia" class="form-control" rows="5" placeholder="Describe lo sucedido..."></textarea>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-primary">Guardar incidencia</button>
                            <button type="button" id="btnExportarPDF" class="btn btn-outline-secondary">Exportar a PDF</button>
                        </div>
                    </form>
                </div>

                <div class="incidencias-list-card">
                    <div class="card-header mb-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h2>Incidencias registradas</h2>
                            <p>Últimas entradas guardadas en tu cuenta.</p>
                        </div>
                        <button id="btnExportarListaPDF" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-file-export"></i> Exportar Historial
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Curso</th>
                                    <th>Clase</th>
                                    <th>Alumno</th>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                </tr>
                            </thead>
                            <tbody id="incidenciasTableBody">
                                <tr><td colspan="6" class="text-center text-muted py-4">Cargando incidencias...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
    <script src="../js/incidencias.js"></script>
</body>
</html>
