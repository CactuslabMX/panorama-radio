# Chat de PANORAMA

El chat se aloja en Cloudflare Pages + D1 y no depende del estudio. Cada visitante elige un apodo sin cuenta (no es una identidad verificada). La sala se conecta al pulsar Entrar al chat y consulta mensajes cada 10 segundos mientras está visible.

- Base de datos: panorama-chat. Binding: CHAT_DB.
- Secreto CHAT_SALT: permite aplicar límites por conexión sin guardar IPs en texto claro. No cambiarlo rutinariamente: cambiarlo invalida los bloqueos existentes.
- Conserva hasta 200 mensajes durante un máximo de 7 días; muestra los 60 últimos. Se purgan al recibir mensajes nuevos y se ocultan los expirados en las consultas.
- Un envío cada 5 segundos, hasta 12 por minuto por conexión. Varias personas detrás de una misma red comparten este límite.
- Solo texto, máximo 300 caracteres; el navegador nunca interpreta HTML enviado por oyentes.
- Localmente ajax/chat.php conecta con la sala pública. Los visitantes locales comparten la IP de salida del estudio.

## Moderación desde Cloudflare

Abre Storage & databases → D1 → panorama-chat → Console.

Consultar mensajes (privado, visible solo para el administrador):

```sql
SELECT id, nickname, message, created_at FROM chat_messages ORDER BY id DESC LIMIT 60;
```

Ocultar un mensaje: sustituye 123 por el ID concreto que quieras moderar.

```sql
UPDATE chat_messages SET hidden = 1 WHERE id = 123;
```

Bloquear la conexión del autor de un mensaje concreto y ocultar sus mensajes:

```sql
INSERT OR IGNORE INTO chat_bans (author_hash) SELECT author_hash FROM chat_messages WHERE id = 123;
UPDATE chat_messages SET hidden = 1 WHERE author_hash IN (SELECT author_hash FROM chat_bans);
```

No hay un filtro automático de contenido ni cuentas verificadas. Los bloqueos son por conexión; pueden afectar a otros usuarios de la misma red. Los cambios de moderación aparecen con la siguiente actualización del chat.

## Desarrollo y publicación

```powershell
node node_modules/wrangler/bin/wrangler.js d1 execute panorama-chat --local --file cloudflare/chat-schema.sql
node scripts/build-pages.cjs
node node_modules/wrangler/bin/wrangler.js pages dev dist
```

Usa CHAT_SALT de prueba en .dev.vars (ignorado por Git). En producción el secreto se guarda en Pages, no en el repositorio. Antes de una primera publicación se aplica chat-schema.sql con --remote. El binding se define en wrangler.toml.
## Mensajes propios

Los mensajes enviados se identifican con sus IDs devueltos por la API y se guardan en localStorage del navegador (hasta 200 IDs). Se alinean a la derecha en azul editorial, incluso tras recargar, y se sincronizan entre pestañas del mismo navegador. Nunca se deduce la propiedad por apodo ni por IP. Otro navegador o borrar el almacenamiento local pierde esa identificación; los mensajes enviados antes de esta función no se atribuyen retroactivamente. Esto solo controla la presentación, no concede permisos de edición ni moderación.