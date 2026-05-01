<?php
require_once __DIR__ . '/controllers/auth_check.php';

// Obtener datos completos del usuario para el sidebar
$usuario_id = $_SESSION['usuario_id'];
try {
    require_once 'conexion.php';
    $sql = "SELECT nombre_completo, nombre_usuario, foto_perfil FROM usuarios WHERE id = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $usuario_id]);
    $datos_usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $datos_usuario = null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educattio - Detalles de la Asignatura</title>

    <link rel="icon" type="image/png" href="../imagenes/dolphin.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/detalles_asignatura.css">
    <link rel="stylesheet" href="../css/portal_inicio_usuario.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'sidebar.php'; ?>
        <main class="main-content subject-detail-page">
            <header class="subject-header">
                <div class="subject-title-row">
                    <a id="backToClassLink" href="#" class="back-link">
                        <i class="fas fa-arrow-left"></i> Volver a clase
                    </a>
                    <div>
                        <button class="btn-settings" onclick="openSettingsModal()">
                            <i class="fas fa-cog"></i> Ajustes de Asignatura
                        </button>
                    </div>
                </div>
                <div class="subject-top">
                    <div>
                        <h1 id="asignaturaNombre">Asignatura</h1>
                        <p class="subject-meta" id="asignaturaMeta">Curso · Clase · Centro</p>
                    </div>
                    <div class="subject-actions">
                        <a id="irCuadernoLink" class="btn btn-primary" href="#">
                            <i class="fas fa-book"></i> Abrir Cuaderno de Evaluación
                        </a>
                    </div>
                </div>
            </header>

            <section class="subject-summary-grid">
                <article class="summary-card blue">
                    <h4>Alumnos</h4>
                    <strong id="summaryAlumnos">0</strong>
                    <p>Matriculados en esta clase</p>
                </article>
                <article class="summary-card green">
                    <h4>Periodos</h4>
                    <strong id="summaryPeriodos">0</strong>
                    <p>Periodos de evaluación definidos</p>
                </article>
                <article class="summary-card orange">
                    <h4>Eventos</h4>
                    <strong id="summaryEventos">0</strong>
                    <p>Próximos eventos relacionados</p>
                </article>
            </section>

            <section class="subject-detail-grid">
                <div class="box box-events">
                    <div class="box-header">
                        <div>
                            <h2>Calendario & Eventos</h2>
                            <p>Eventos próximos para esta clase</p>
                        </div>
                    </div>
                    <div id="eventosList" class="events-list"></div>
                    <div class="empty-state" id="eventosEmpty">No hay eventos programados para esta asignatura.</div>
                </div>

                <div class="box box-syllabus">
                    <div class="box-header">
                        <div>
                            <h2>Temario</h2>
                            <p>Organiza los contenidos y objetivos de la asignatura.</p>
                        </div>
                        <button class="btn btn-outline-primary" onclick="toggleSection('nuevoTemaSection')">
                            <i class="fas fa-plus"></i> Añadir tema
                        </button>
                    </div>
                    <div id="temasContainer" class="temas-list"></div>
                    <div class="empty-state" id="temasEmpty">Añade los primeros temas para compartir el temario.</div>
                    <div id="nuevoTemaSection" class="new-item-card hidden">
                        <form id="formAgregarTema">
                            <div class="mb-3">
                                <label class="form-label">Título del tema</label>
                                <input type="text" id="temaTitulo" class="form-control" placeholder="Ej: Ecosistemas" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea id="temaDescripcion" class="form-control" rows="3" placeholder="Describe el contenido del tema"></textarea>
                            </div>
                            <div class="d-flex gap-2 justify-content-end">
                                <button type="button" class="btn btn-outline-secondary" onclick="toggleSection('nuevoTemaSection')">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Guardar tema</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="box box-evaluation">
                    <div class="box-header">
                        <div>
                            <h2>Evaluaciones</h2>
                            <p>Gestiona los periodos y accede al cuaderno.</p>
                        </div>
                    </div>
                    <div class="evaluation-panel">
                        <div class="evaluation-card">
                            <h3>Ir al cuaderno</h3>
                            <p>Usa el cuaderno de evaluación para registrar notas y organizar items.</p>
                            <a id="abrirCuadernoBtn" class="btn btn-primary" href="#">
                                <i class="fas fa-book-open"></i> Abrir cuaderno</a>
                        </div>
                        <div class="periodos-card">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3>Periodos</h3>
                                <button class="btn btn-sm btn-outline-primary" onclick="toggleSection('nuevoPeriodoSection')">
                                    <i class="fas fa-plus"></i> Añadir periodo
                                </button>
                            </div>
                            <div id="periodosList"></div>
                            <div id="periodosEmpty" class="empty-state">Crea periodos para estructurar las evaluaciones.</div>
                            <div id="nuevoPeriodoSection" class="new-item-card hidden">
                                <form id="formAgregarPeriodos">
                                    <div class="mb-3">
                                        <label class="form-label">Nombres de periodos</label>
                                        <input type="text" id="periodosNombres" class="form-control" placeholder="Ej: 1º Trimestre, 2º Trimestre" required>
                                        <small class="form-text text-muted">Sepáralos con comas para crear varios a la vez.</small>
                                    </div>
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button type="button" class="btn btn-outline-secondary" onclick="toggleSection('nuevoPeriodoSection')">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">Guardar periodos</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/detalles_asignatura.js"></script>
</body>
</html>
