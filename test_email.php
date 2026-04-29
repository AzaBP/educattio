<?php
$to = "azaharabarjola@gmail.com";
$subject = "Prueba de correo desde XAMPP";
$message = "¡Hola! Si estás leyendo esto, el envío de correos está funcionando perfectamente.";
$headers = "From: no-reply@tusitio.com\r\n";

if(mail($to, $subject, $message, $headers)) {
    echo "Correo enviado con éxito.";
} else {
    echo "Error al enviar el correo. Revisa la configuración de sendmail.ini y php.ini.";
}
?>