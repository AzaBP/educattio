<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['usuario_id'];

try {
    $sql = "SELECT nombre_completo, nombre_usuario, email, telefono, fecha_nacimiento, formacion_academica, experiencia_laboral, foto_perfil FROM usuarios WHERE id = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $id_usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) die("Usuario no encontrado.");
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CV - <?= htmlspecialchars($user['nombre_completo']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #0f172a;
            --secondary: #334155;
            --accent: #2563eb;
            --bg: #ffffff;
            --text: #1e293b;
            --light-text: #64748b;
        }

        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 40px 0;
            color: var(--text);
        }

        .cv-container {
            max-width: 850px;
            margin: 0 auto;
            background: white;
            padding: 60px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            border-radius: 4px;
            position: relative;
        }

        /* CABECERA */
        .cv-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 40px;
            margin-bottom: 40px;
        }

        .header-info h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.8rem;
            margin: 0 0 10px 0;
            color: var(--primary);
        }

        .header-info p {
            font-size: 1.1rem;
            color: var(--accent);
            font-weight: 600;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header-photo {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #f8fafc;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        /* CONTACTO */
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 40px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95rem;
            color: var(--secondary);
        }

        .contact-item i {
            color: var(--accent);
            width: 20px;
        }

        /* SECCIONES */
        .section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .section-content {
            line-height: 1.7;
            color: var(--secondary);
            white-space: pre-line;
            font-size: 1rem;
        }

        /* BOTONES (Solo se ven en pantalla) */
        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 1000;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            border: none;
            transition: 0.2s;
        }

        .btn-print { background: var(--accent); color: white; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3); }
        .btn-back { background: white; color: var(--secondary); border: 1px solid #e2e8f0; }
        .btn:hover { transform: translateY(-2px); opacity: 0.9; }

        @media print {
            body { background: white; padding: 0; }
            .cv-container { box-shadow: none; margin: 0; max-width: 100%; width: 100%; padding: 0; }
            .no-print { display: none !important; }
            @page { margin: 2cm; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <a href="perfil_usuario.php" class="btn btn-back"><i class="fas fa-arrow-left"></i> Volver</a>
        <button onclick="window.print()" class="btn btn-print"><i class="fas fa-file-pdf"></i> Guardar como PDF</button>
    </div>

    <div class="cv-container">
        <header class="cv-header">
            <div class="header-info">
                <h1><?= htmlspecialchars($user['nombre_completo']) ?></h1>
                <p>Perfil Profesional - Educattio</p>
            </div>
            <?php 
            $foto = $user['foto_perfil'] ? '../' . $user['foto_perfil'] : '../uploads/perfil/default-avatar.png';
            $foto .= '?v=' . time(); // Cache busting
            ?>
            <img src="<?= $foto ?>" alt="Foto" class="header-photo">
        </header>

        <div class="contact-grid">
            <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <span><?= htmlspecialchars($user['email']) ?></span>
            </div>
            <div class="contact-item">
                <i class="fas fa-user-circle"></i>
                <span>@<?= htmlspecialchars($user['nombre_usuario']) ?></span>
            </div>
            <?php if($user['telefono']): ?>
            <div class="contact-item">
                <i class="fas fa-phone"></i>
                <span><?= htmlspecialchars($user['telefono']) ?></span>
            </div>
            <?php endif; ?>
            <?php if($user['fecha_nacimiento']): ?>
            <div class="contact-item">
                <i class="fas fa-calendar"></i>
                <span>Nacido el <?= date('d/m/Y', strtotime($user['fecha_nacimiento'])) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <section class="section">
            <div class="section-title"><i class="fas fa-graduation-cap"></i> Formación Académica</div>
            <div class="section-content">
                <?= !empty($user['formacion_academica']) ? htmlspecialchars($user['formacion_academica']) : 'No se ha especificado formación académica.' ?>
            </div>
        </section>

        <section class="section">
            <div class="section-title"><i class="fas fa-briefcase"></i> Experiencia Laboral</div>
            <div class="section-content">
                <?= !empty($user['experiencia_laboral']) ? htmlspecialchars($user['experiencia_laboral']) : 'No se ha especificado experiencia laboral.' ?>
            </div>
        </section>

        <footer style="margin-top: 80px; text-align: center; font-size: 0.8rem; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 20px;">
            Documento generado automáticamente por Educattio - Plataforma de Gestión Educativa
        </footer>
    </div>

</body>
</html>
