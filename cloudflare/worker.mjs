const endpoints = new Set(['/ajax/status.php', '/ajax/artwork.php', '/ajax/stream.php']);
function unavailable(path) {
  const data = path === '/ajax/status.php'
    ? { online: false, title: null, listeners: 0, history: [], error: 'El estudio está desconectado' }
    : path === '/ajax/artwork.php' ? { artwork: null } : { error: 'La transmisión no está disponible' };
  return Response.json(data, { status: path === '/ajax/stream.php' ? 503 : 200, headers: { 'Cache-Control': 'no-store' } });
}
export default {
  async fetch(request, env) {
    const incoming = new URL(request.url);
    if (!incoming.pathname.startsWith('/ajax/')) return env.ASSETS.fetch(request);
    if (!endpoints.has(incoming.pathname)) return new Response('Not found', { status: 404 });
    if (!['GET', 'HEAD'].includes(request.method)) return new Response('Method not allowed', { status: 405, headers: { Allow: 'GET, HEAD' } });
    if (!env.RADIO_ORIGIN) { console.error('RADIO_ORIGIN is not configured'); return unavailable(incoming.pathname); }
    try {
      const origin = new URL(env.RADIO_ORIGIN);
      if (origin.protocol !== 'https:' || origin.username || origin.password) return unavailable(incoming.pathname);
      const target = new URL(origin.href);
      target.pathname = origin.pathname.replace(/\/$/, '') + incoming.pathname;
      target.search = '';
      if (incoming.pathname === '/ajax/artwork.php') {
        for (const key of ['artist', 'track', 'album']) target.searchParams.set(key, (incoming.searchParams.get(key) || '').slice(0, 200));
      }
      const controller = new AbortController();
      const timeout = setTimeout(() => controller.abort(), 6500);
      let upstream;
      try {
        upstream = await fetch(target, { method: 'GET', redirect: 'manual', signal: controller.signal, headers: { 'Accept': incoming.pathname === '/ajax/stream.php' ? 'audio/mpeg' : 'application/json' } });
      } finally { clearTimeout(timeout); }
      if (!upstream.ok) { console.error('Studio upstream HTTP status', upstream.status); await upstream.body?.cancel(); return unavailable(incoming.pathname); }
      if (incoming.pathname === '/ajax/stream.php') {
        if (!upstream.headers.get('content-type')?.startsWith('audio/')) { await upstream.body?.cancel(); return unavailable(incoming.pathname); }
        return new Response(upstream.body, { headers: { 'Content-Type': upstream.headers.get('content-type'), 'Cache-Control': 'no-store, no-transform', 'X-Content-Type-Options': 'nosniff' } });
      }
      const data = await upstream.json();
      if (incoming.pathname === '/ajax/status.php') data.stream_url = '/ajax/stream.php';
      return Response.json(data, { headers: { 'Cache-Control': 'no-store', 'X-Content-Type-Options': 'nosniff' } });
    } catch (error) { console.error('Studio connection error', error.name, error.message); return unavailable(incoming.pathname); }
  }
};
