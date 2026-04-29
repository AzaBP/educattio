<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "Llegamos a subir_foto.php<br>";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "Es POST<br>";
} else {
    echo "No es POST (es " . $_SERVER["REQUEST_METHOD"] . ")<br>";
}
?>