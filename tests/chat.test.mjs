import test from 'node:test';
import assert from 'node:assert/strict';
import { DatabaseSync } from 'node:sqlite';
import { readFileSync } from 'node:fs';
import { randomUUID } from 'node:crypto';
import { handleChat } from '../cloudflare/chat.mjs';
function fixture() {
  const db = new DatabaseSync(':memory:');
  db.exec(readFileSync(new URL('../cloudflare/chat-schema.sql', import.meta.url), 'utf8'));
  const env = { CHAT_SALT: 'test-only', CHAT_DB: { prepare(sql) {
    let args = [];
    const query = { bind(...values) { args = values; return query; },
      async first() { return db.prepare(sql).get(...args) || null; },
      async all() { return { results: db.prepare(sql).all(...args) }; },
      async run() { const r = db.prepare(sql).run(...args); return { meta: { changes: Number(r.changes), last_row_id: Number(r.lastInsertRowid) } }; }
    }; return query;
  } } };
  const post = (data = {}, extra = {}) => new Request('https://radio.pages.dev/ajax/chat.php', { method: 'POST', headers: { Origin: 'https://radio.pages.dev', 'Content-Type': 'application/json', 'CF-Connecting-IP': '192.0.2.1', ...extra }, body: JSON.stringify({ nickname: 'Oyente', message: '¿Qué canción suena?', client_id: randomUUID(), ...data }) });
  return { db, env, post, get: () => handleChat(new Request('https://radio.pages.dev/ajax/chat.php'), env) };
}
test('two visitors share messages; private identifiers are never returned; retries are idempotent', async () => {
  const { env, post, get } = fixture();
  const id = randomUUID();
  assert.equal((await handleChat(post({ client_id: id }), env)).status, 201);
  assert.equal((await handleChat(post({ client_id: id }), env)).status, 200);
  const response = await handleChat(post({ nickname: 'Otro', message: '<img src=x onerror=alert(1)>' }, { 'CF-Connecting-IP': '192.0.2.2' }), env);
  assert.equal(response.status, 201);
  const { messages } = await (await get()).json();
  assert.equal(messages.length, 2);
  assert.deepEqual(Object.keys(messages[0]).sort(), ['created_at', 'id', 'message', 'nickname']);
  assert.equal(messages[1].message, '<img src=x onerror=alert(1)>');
});
test('concurrent spam, cross-origin writes, oversized bodies and reserved names are rejected', async () => {
  const { env, post } = fixture();
  const results = await Promise.all([handleChat(post(), env), handleChat(post(), env)]);
  assert.deepEqual(results.map(r => r.status).sort(), [201, 429]);
  assert.equal((await handleChat(post({}, { Origin: 'https://evil.example' }), env)).status, 403);
  assert.equal((await handleChat(post({ message: 'a'.repeat(5000) }), env)).status, 413);
  assert.equal((await handleChat(post({ message: 'a'.repeat(301) }), env)).status, 400);
  assert.equal((await handleChat(post({ nickname: 'PANORAMA' }), env)).status, 400);
});
test('moderation, expiry and connection bans are enforced', async () => {
  const { env, post, db, get } = fixture();
  await handleChat(post(), env);
  db.exec('UPDATE chat_messages SET hidden = 1');
  assert.equal((await (await get()).json()).messages.length, 0);
  db.exec('UPDATE chat_messages SET hidden = 0, created_at = 0');
  assert.equal((await (await get()).json()).messages.length, 0);
  db.exec('INSERT INTO chat_bans SELECT author_hash FROM chat_messages');
  assert.equal((await handleChat(post(), env)).status, 403);
});
test('chat remains available without a radio origin and bounds retained history', async () => {
  const { env, post, db, get } = fixture();
  const insert = db.prepare('INSERT INTO chat_messages(nickname,message,created_at,author_hash,client_id) VALUES(?,?,?,?,?)');
  for (let i = 0; i < 205; i++) insert.run('Invitado', 'Mensaje ' + i, Date.now() - 10000, 'other', randomUUID());
  assert.equal((await handleChat(post(), env)).status, 201);
  assert.equal(db.prepare('SELECT COUNT(*) AS n FROM chat_messages').get().n, 200);
  const messages = (await (await get()).json()).messages;
  assert.equal(messages.length, 60);
  assert.ok(messages[0].id < messages[59].id);
});