# Usamos una imagen oficial de PHP con Apache
FROM php:8.1-apache

# Instalamos extensiones necesarias para MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Habilitamos el módulo rewrite de Apache
RUN a2enmod rewrite

# Copiamos los archivos del proyecto al contenedor
COPY . /var/www/html/

# Ajustamos permisos
RUN chown -R www-data:www-data /var/www/html/

# Exponemos el puerto 80
EXPOSE 80
