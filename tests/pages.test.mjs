import test from 'node:test';
import assert from 'node:assert/strict';
import worker from '../cloudflare/worker.mjs';

test('disconnected studio is reported honestly', async () => {
  const response = await worker.fetch(new Request('https://radio.pages.dev/ajax/status.php'), {});
  assert.equal((await response.json()).online, false);
  assert.equal((await worker.fetch(new Request('https://radio.pages.dev/ajax/stream.php'), {})).status, 503);
});
test('unknown endpoints and writes cannot reach the studio', async () => {
  assert.equal((await worker.fetch(new Request('https://radio.pages.dev/ajax/private.php'), {})).status, 404);
  assert.equal((await worker.fetch(new Request('https://radio.pages.dev/ajax/status.php', { method: 'POST' }), {})).status, 405);
});
test('status uses configured origin, strips query and returns same-origin stream', async t => {
  t.mock.method(globalThis, 'fetch', async url => {
    assert.equal(url.href, 'https://studio.example/miradio/ajax/status.php');
    return Response.json({ online: true, title: 'Artist - Track', stream_url: 'http://localhost:8000/stream', history: [] });
  });
  const response = await worker.fetch(new Request('https://radio.pages.dev/ajax/status.php?url=https://other.example'), { RADIO_ORIGIN: 'https://studio.example/miradio/' });
  const data = await response.json();
  assert.equal(data.title, 'Artist - Track');
  assert.equal(data.stream_url, '/ajax/stream.php');
});
test('audio body is passed through without buffering or credentials', async t => {
  t.mock.method(globalThis, 'fetch', async () => new Response(new Uint8Array([255, 251, 144, 100]), { headers: { 'content-type': 'audio/mpeg', 'set-cookie': 'private=value' } }));
  const response = await worker.fetch(new Request('https://radio.pages.dev/ajax/stream.php'), { RADIO_ORIGIN: 'https://studio.example' });
  assert.equal(response.headers.get('content-type'), 'audio/mpeg');
  assert.equal(response.headers.has('set-cookie'), false);
  assert.deepEqual(new Uint8Array(await response.arrayBuffer()), new Uint8Array([255, 251, 144, 100]));
});
test('a failed tunnel does not pretend the radio is online', async t => {
  t.mock.method(globalThis, 'fetch', async () => new Response('Tunnel down', { status: 502 }));
  const response = await worker.fetch(new Request('https://radio.pages.dev/ajax/status.php'), { RADIO_ORIGIN: 'https://studio.example' });
  assert.equal((await response.json()).online, false);
});
