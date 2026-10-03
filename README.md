# PANORAMA Radio

Sitio de radio con reproductor Icecast, canción actual, historial de RadioDJ, portadas de Spotify y colección de merch.

## Requisitos

- Servidor web con PHP 8.0 o posterior y extensiones cURL, SimpleXML y PDO MySQL.
- Icecast con una fuente de audio activa.
- RadioDJ y acceso local a su archivo de configuración de base de datos para consultar el historial.
- Credenciales de una aplicación de Spotify para buscar portadas (opcional).

## Instalación local

1. Coloca el proyecto en `C:/xampp/htdocs/miradio` o en el directorio público de tu servidor.
2. Copia `init.example.conf` como `init.conf` y configura Icecast, la ruta de RadioDJ y `base_url`. Usa una cadena vacía para `base_url` si el sitio está en la raíz del dominio.
3. Para las portadas, copia `kernel/spotify.local.example.php` como `kernel/spotify.local.php` y completa las credenciales de Spotify.
4. Inicia Apache, la base de datos de RadioDJ, Icecast y tu emisor de audio.
5. En RadioDJ, habilita la exportación de metadatos a la ruta indicada en `now_playing_file`.
6. Abre `http://localhost/miradio/`.

El proceso del servidor necesita leer la configuración y los archivos de RadioDJ, y escribir en `data/` para las cachés y el historial alternativo. El archivo de canción actual se utiliza cuando Icecast no proporciona un título.

## Configuración privada

`init.conf`, `kernel/spotify.local.php` y `data/` están excluidos de Git. Las plantillas incluidas no contienen contraseñas. No subas credenciales, registros ni copias de bases de datos al repositorio.

## Publicación

Este proyecto requiere PHP y no funciona directamente en GitHub Pages. Un servidor remoto deberá tener acceso a Icecast y a las fuentes de metadatos; las rutas locales de RadioDJ solo funcionan en la computadora que las contiene. Configura el servidor para impedir descargas directas de archivos de configuración y directorios internos.
