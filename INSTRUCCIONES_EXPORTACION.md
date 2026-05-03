# 🐳 Guía de Despliegue con Docker - Educattio

Para que otra persona pueda utilizar este proyecto sin necesidad de instalar PHP, Apache o MySQL manualmente, se ha incluido una configuración basada en contenedores.

## 📋 Requisitos Previos
Tener instalado [Docker Desktop](https://www.docker.com/products/docker-desktop/).

## 🚀 Instrucciones de Inicio

1. **Abrir una terminal** en la carpeta raíz del proyecto.
2. **Ejecutar el siguiente comando**:
   ```bash
   docker-compose up -d --build
   ```
3. **Esperar a que termine**: Docker descargará las imágenes, configurará la base de datos (importando automáticamente `educattio_BD.sql`) y levantará el servidor web.

## 🔗 Acceso a la Aplicación
Una vez finalizado el proceso, puedes entrar en:
👉 **http://localhost:8080**

## ⚙️ Detalles Técnicos
- **Puerto Web**: 8080 (Docker) -> 80 (Interno).
- **Puerto DB**: 3306 (Docker) -> 3306 (Interno).
- **Persistencia**: Los datos de la base de datos se guardan en un volumen llamado `db_data` para que no se pierdan al apagar los contenedores.
- **Configuración Automática**: El archivo `php/conexion.php` detecta automáticamente si estás en Docker y ajusta los credenciales.
