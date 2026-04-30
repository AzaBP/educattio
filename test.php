<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        echo "Archivo recibido: " . $_FILES['foto_perfil']['name'];
    } else {
        echo "No se recibió archivo o hubo error. Código: " . ($_FILES['foto_perfil']['error'] ?? 'sin archivo');
    }
}
?>