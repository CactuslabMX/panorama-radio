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

## Cloudflare Pages

La versión pública se publica en https://panorama-radio.pages.dev. El sitio PHP de XAMPP se conserva para servir el audio y los datos del estudio.

- `scripts/build-pages.php` genera `dist/` a partir de las plantillas, sin cargar `init.conf` ni credenciales. Solo incluye HTML, recursos y el Worker público.
- `cloudflare/worker.mjs` conecta los tres servicios de radio con `RADIO_ORIGIN`, una variable privada de Pages. El navegador solo usa HTTPS en el mismo dominio.
- `scripts/studio-gateway.cjs` escucha en `127.0.0.1:8787` y permite únicamente estado, portadas y audio. El túnel nunca debe apuntarse directamente al puerto 80 de XAMPP.
- `.htaccess` bloquea descargas de configuraciones, Git y archivos internos en Apache.

### Volver a conectar después de reiniciar la computadora

Inicia Apache, RadioDJ, Icecast y tu emisor. Luego ejecuta desde PowerShell:

```powershell
cd C:/xampp/htdocs/miradio
./scripts/Start-RadioPages.ps1
```

El script inicia la pasarela y el túnel en segundo plano, actualiza `RADIO_ORIGIN` y vuelve a publicar la web. La dirección `pages.dev` permanece fija; el túnel interno gratuito cambia al reiniciarse. No se instala un servicio de arranque automático.

### Preparación en otra computadora

Instala Node.js y PHP 8 o posterior, ejecuta `npm ci`, descarga cloudflared desde Cloudflare a `.tools/cloudflared.exe`, y autoriza tu cuenta con `npx wrangler login`. Define `PHP_BINARY` si PHP no está en PATH ni en `C:/xampp/php/php.exe`. Completa las configuraciones locales indicadas arriba. Los ejecutables, credenciales, logs y `dist/` no se suben a Git.

Para publicar cambios: `npm run deploy`. Para ejecutar las pruebas: `npm test`.

### Disponibilidad

La web sigue disponible si el estudio se apaga, pero el audio, las portadas consultadas y los datos en directo necesitan la conexión del estudio. Quick Tunnels está orientado a pruebas y no garantiza disponibilidad; esta conexión inicial debe sustituirse por un origen estable para una radio de producción. Las peticiones dinámicas consumen la cuota de Pages Functions y las conexiones de escucha usan recursos del equipo local. La publicación actual es directa con Wrangler: hacer push a GitHub por sí solo no despliega cambios.
