const reply = (data, status = 200) => Response.json(data, { status, headers: { 'Cache-Control': 'no-store', 'X-Content-Type-Options': 'nosniff' } });

export async function handleChat(request, env) {
  if (!['GET', 'POST'].includes(request.method)) return reply({ error: 'Método no permitido.' }, 405);
  if (!env.CHAT_DB || !env.CHAT_SALT) return reply({ error: 'El chat no está disponible por ahora.' }, 503);
  try {
    const now = Date.now();
    if (request.method === 'GET') {
      const { results } = await env.CHAT_DB.prepare('SELECT id, nickname, message, created_at FROM chat_messages WHERE hidden = 0 AND created_at > ? ORDER BY id DESC LIMIT 60').bind(now - 7 * 86400000).all();
      return reply({ messages: results.reverse() });
    }
    if (request.headers.get('Origin') !== new URL(request.url).origin) return reply({ error: 'Abre el chat desde PANORAMA.' }, 403);
    if (!request.headers.get('Content-Type')?.startsWith('application/json')) return reply({ error: 'Formato no válido.' }, 415);
    // Bound the actual body as well as its declared size.
    let bytes = 0, chunks = [];
    const reader = request.body?.getReader();
    if (!reader) return reply({ error: 'Escribe un mensaje.' }, 400);
    while (true) {
      const { done, value } = await reader.read();
      if (done) break;
      bytes += value.byteLength;
      if (bytes > 4096) { await reader.cancel(); return reply({ error: 'El mensaje es demasiado largo.' }, 413); }
      chunks.push(value);
    }
    const buffer = new Uint8Array(bytes);
    let offset = 0;
    for (const chunk of chunks) { buffer.set(chunk, offset); offset += chunk.length; }
    let data;
    try { data = JSON.parse(new TextDecoder().decode(buffer)); } catch { return reply({ error: 'Mensaje no válido.' }, 400); }
    if (!data || typeof data.nickname !== 'string' || typeof data.message !== 'string') return reply({ error: 'Escribe tu apodo y un mensaje.' }, 400);
    const nickname = data.nickname.normalize('NFC').replace(/[\u0000-\u001f\u007f-\u009f\u202a-\u202e\u2066-\u2069]/g, '').trim();
    const message = data.message.normalize('NFC').replace(/[\u0000-\u0008\u000b-\u001f\u007f-\u009f\u202a-\u202e\u2066-\u2069]/g, '').trim();
    if (nickname.length < 2 || nickname.length > 24 || message.length < 1 || message.length > 300) return reply({ error: 'Usa un apodo de 2 a 24 caracteres y un mensaje de hasta 300.' }, 400);
    if (/panorama|admin|moderador|moderator/i.test(nickname)) return reply({ error: 'Elige un apodo personal; ese nombre está reservado.' }, 400);
    if (typeof data.client_id !== 'string' || !/^[a-f0-9-]{36}$/i.test(data.client_id)) return reply({ error: 'Identificador no válido.' }, 400);
    const ip = request.headers.get('CF-Connecting-IP');
    if (!ip) return reply({ error: 'No se pudo verificar la conexión.' }, 400);
    const digest = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(env.CHAT_SALT + ':' + ip));
    const author = Array.from(new Uint8Array(digest), b => b.toString(16).padStart(2, '0')).join('');
    if (await env.CHAT_DB.prepare('SELECT 1 FROM chat_bans WHERE author_hash = ?').bind(author).first()) return reply({ error: 'El envío de mensajes está suspendido para esta conexión.' }, 403);
    const existing = await env.CHAT_DB.prepare('SELECT id FROM chat_messages WHERE client_id = ? AND author_hash = ?').bind(data.client_id, author).first();
    if (existing) return reply({ ok: true, id: existing.id });
    // Single atomic statement prevents concurrent requests from bypassing the cooldown.
    const result = await env.CHAT_DB.prepare(`INSERT INTO chat_messages (nickname, message, created_at, author_hash, client_id)
      SELECT ?, ?, ?, ?, ? WHERE NOT EXISTS (SELECT 1 FROM chat_messages WHERE author_hash = ? AND created_at > ?)
      AND (SELECT COUNT(*) FROM chat_messages WHERE author_hash = ? AND created_at > ?) < 12
      ON CONFLICT(client_id) DO NOTHING`).bind(nickname, message, now, author, data.client_id, author, now - 5000, author, now - 60000).run();
    if (!result.meta.changes) return reply({ error: 'Espera unos segundos antes de enviar otro mensaje.' }, 429);
    // Retain at most 200 messages and seven days of history.
    await env.CHAT_DB.prepare('DELETE FROM chat_messages WHERE created_at < ? OR id < (SELECT COALESCE(MAX(id), 0) - 199 FROM chat_messages)').bind(now - 7 * 86400000).run();
    return reply({ ok: true, id: result.meta.last_row_id }, 201);
  } catch (error) {
    console.error('Chat error', error.name);
    return reply({ error: 'No pudimos conectar con el chat. Inténtalo de nuevo.' }, 503);
  }
}