CREATE TABLE IF NOT EXISTS chat_messages (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  nickname TEXT NOT NULL,
  message TEXT NOT NULL,
  created_at INTEGER NOT NULL,
  author_hash TEXT NOT NULL,
  client_id TEXT NOT NULL UNIQUE,
  hidden INTEGER NOT NULL DEFAULT 0
);
CREATE INDEX IF NOT EXISTS chat_author_time ON chat_messages(author_hash, created_at);
CREATE TABLE IF NOT EXISTS chat_bans (author_hash TEXT PRIMARY KEY);