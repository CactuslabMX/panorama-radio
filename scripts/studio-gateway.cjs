// Only radio endpoints are exposed. Apache, its dashboard and private files stay local.
const http = require('node:http');
const allowed = new Set(['/ajax/status.php', '/ajax/artwork.php', '/ajax/stream.php']);
const server = http.createServer((req, res) => {
  const url = new URL(req.url, 'http://localhost');
  if (!allowed.has(url.pathname) || !['GET', 'HEAD'].includes(req.method)) { res.writeHead(404); res.end(); return; }
  const upstream = http.request({ hostname: '127.0.0.1', port: 80, path: '/miradio' + url.pathname + url.search, method: req.method }, response => {
    res.writeHead(response.statusCode, { 'Content-Type': response.headers['content-type'] || 'application/octet-stream', 'Cache-Control': 'no-store' });
    response.pipe(res);
  });
  upstream.setTimeout(15000, () => upstream.destroy());
  upstream.on('error', () => { if (!res.headersSent) res.writeHead(503); res.end(); });
  res.on('close', () => upstream.destroy());
  upstream.end();
});
server.listen(8787, '127.0.0.1', () => console.log('PANORAMA studio gateway: 127.0.0.1:8787'));
