(() => {
  const join = document.getElementById('chatJoin');
  if (!join) return;
  const $ = id => document.getElementById(id);
  const room = $('chatRoom'), list = $('chatMessages'), status = $('chatStatus');
  const nickname = $('chatNickname'), message = $('chatMessage'), form = $('chatForm');
  const endpoint = (window.RADIO_BASE || '') + '/ajax/chat.php';
  const ownKey = 'panorama-chat-sent-ids';
  let joined = false, visible = true, loading = false, sending = false, pendingId;
  let lastMessages = [];
  const ownIds = new Set();
  function loadOwnIds() {
    try {
      const saved = JSON.parse(localStorage.getItem(ownKey) || '[]');
      if (Array.isArray(saved)) for (const id of saved.slice(-200)) if (Number.isSafeInteger(id) && id > 0) ownIds.add(id);
    } catch {}
  }
  function rememberOwn(id) {
    loadOwnIds();
    if (!Number.isSafeInteger(id) || id < 1) return;
    ownIds.add(id);
    try { localStorage.setItem(ownKey, JSON.stringify([...ownIds].sort((a, b) => a - b).slice(-200))); } catch {}
  }
  loadOwnIds();
  try { nickname.value = localStorage.getItem('panorama-chat-name') || ''; } catch {}
  const timeFormat = new Intl.DateTimeFormat('es-MX', { hour: '2-digit', minute: '2-digit' });
  const bottom = () => list.scrollHeight - list.scrollTop - list.clientHeight < 65;
  function scrollLatest() { list.scrollTop = list.scrollHeight; $('chatLatest').hidden = true; }
  $('chatLatest').addEventListener('click', scrollLatest);
  list.addEventListener('scroll', () => { if (bottom()) $('chatLatest').hidden = true; });
  async function api(options = {}) {
    const response = await fetch(endpoint, { ...options, cache: 'no-store', signal: AbortSignal.timeout(12000) });
    const data = await response.json();
    if (!response.ok) throw new Error(data.error || 'El chat no está disponible. Inténtalo de nuevo.');
    return data;
  }
  function render(messages) {
    lastMessages = messages; loadOwnIds();
    const follow = bottom(), first = list.children.length === 0;
    const ids = new Set(messages.map(m => String(m.id)));
    for (const child of [...list.children]) if (!ids.has(child.dataset.id)) child.remove();
    const rendered = new Map([...list.children].map(child => [child.dataset.id, child]));
    let added = false;
    for (const item of messages) {
      let article = rendered.get(String(item.id));
      if (!article) {
        article = document.createElement('article');
        article.className = 'chat-message'; article.dataset.id = String(item.id);
        const meta = document.createElement('div'), name = document.createElement('strong'), time = document.createElement('time'), body = document.createElement('p');
        meta.className = 'chat-message-meta';
        const date = new Date(item.created_at); time.dateTime = date.toISOString(); time.textContent = timeFormat.format(date); time.title = date.toLocaleString('es-MX');
        body.textContent = item.message; meta.append(name, time); article.append(meta, body); list.append(article); added = true;
      }
      const own = ownIds.has(item.id);
      article.classList.toggle('chat-message--own', own);
      const label = (own ? 'Tú · ' : '') + item.nickname;
      if (article.querySelector('strong').textContent !== label) article.querySelector('strong').textContent = label;
    }
    $('chatEmpty').hidden = messages.length !== 0;
    if (follow || first) scrollLatest(); else if (added) $('chatLatest').hidden = false;
  }
  window.addEventListener('storage', event => { if (event.key === ownKey) render(lastMessages); });
  async function refresh() {
    if (loading || !joined || document.hidden || !visible) return;
    loading = true;
    try { const data = await api(); render(data.messages); status.textContent = 'Conectado'; status.dataset.state = 'online'; }
    catch { status.textContent = 'Reconectando…'; status.dataset.state = 'offline'; }
    finally { loading = false; }
  }
  join.addEventListener('click', () => {
    joined = true; $('chatWelcome').hidden = true; room.hidden = false; status.textContent = 'Conectando…';
    refresh(); setInterval(refresh, 10000); (nickname.value ? message : nickname).focus();
  }, { once: true });
  document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
  if ('IntersectionObserver' in window) new IntersectionObserver(entries => { visible = entries[0].isIntersecting; if (visible) refresh(); }, { rootMargin: '200px' }).observe($('chat'));
  function changed() {
    pendingId = undefined; $('chatCounter').textContent = message.value.length + ' / 300';
    message.style.height = 'auto'; message.style.height = Math.min(130, message.scrollHeight) + 'px';
  }
  message.addEventListener('input', changed); nickname.addEventListener('input', () => { pendingId = undefined; });
  message.addEventListener('keydown', event => { if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) { event.preventDefault(); form.requestSubmit(); } });
  form.addEventListener('submit', async event => {
    event.preventDefault(); if (sending || !form.reportValidity()) return;
    const text = message.value.trim(), name = nickname.value.trim();
    if (!text || name.length < 2) { $('chatError').textContent = 'Escribe tu apodo y un mensaje.'; return; }
    sending = true; $('chatSend').disabled = true; $('chatSend').setAttribute('aria-busy', 'true'); $('chatSend').setAttribute('aria-label', 'Enviando mensaje'); $('chatError').textContent = '';
    pendingId ||= crypto.randomUUID();
    const outgoingId = pendingId;
    try {
      const sent = await api({ method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ nickname: name, message: text, client_id: outgoingId }) });
      rememberOwn(sent.id); render(lastMessages);
      try { localStorage.setItem('panorama-chat-name', name); } catch {}
      if (message.value.trim() === text) { message.value = ''; changed(); }
      if (pendingId === outgoingId) pendingId = undefined;
      scrollLatest(); await refresh(); message.focus();
    } catch (error) { $('chatError').textContent = error instanceof TypeError || error.name === 'TimeoutError' ? 'No se pudo confirmar el envío. Puedes intentarlo de nuevo.' : error.message; }
    finally { sending = false; $('chatSend').disabled = false; $('chatSend').removeAttribute('aria-busy'); $('chatSend').setAttribute('aria-label', 'Enviar mensaje'); }
  });
})();