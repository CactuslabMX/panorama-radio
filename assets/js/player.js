(function () {
    const clockEl = document.getElementById('headerClock');
    function tickClock() {
        if (!clockEl) return;
        const now = new Date();
        clockEl.textContent = now.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
    }
    tickClock();
    setInterval(tickClock, 1000);

    const statusText = document.getElementById('statusText');
    const statusDot = document.getElementById('statusDot');
    const trackTitle = document.getElementById('trackTitle');
    const listenerCount = document.getElementById('listenerCount');
    const heroTrack = document.getElementById('heroTrack');
    const historyList = document.getElementById('trackHistory');
    const historyEmpty = document.getElementById('historyEmpty');
    const artworkRequests = new Map();
    let historySignature = '';
    function loadArtwork(entry) {
        const query = new URLSearchParams({v: 'spotify-1', artist: entry.artist || '', track: entry.track || entry.title, album: entry.album || ''});
        const key = query.toString();
        if (!artworkRequests.has(key)) {
            artworkRequests.set(key, fetch((window.RADIO_BASE || '') + '/ajax/artwork.php?' + key)
                .then(res => res.ok ? res.json() : {artwork: null})
                .catch(() => ({artwork: null})));
        }
        return artworkRequests.get(key);
    }
    function renderHistory(entries) {
        if (!historyList || !Array.isArray(entries)) return;
        const signature = JSON.stringify(entries);
        if (signature === historySignature) return;
        historySignature = signature;
        const scrollTop = historyList.scrollTop;
        historyList.replaceChildren();
        entries.slice(0, 30).forEach(entry => {
            const row = document.createElement('li');
            const cover = document.createElement('div');
            cover.className = 'track-cover';
            cover.textContent = '♫';
            cover.setAttribute('aria-label', 'Portada no disponible');
            const details = document.createElement('div');
            details.className = 'track-details';
            const title = document.createElement('span');
            title.textContent = entry.track || entry.title;
            const artist = document.createElement('small');
            artist.textContent = entry.artist && entry.artist !== entry.track ? entry.artist : 'Artista sin especificar';
            details.append(title, artist);
            const link = document.createElement('a');
            link.href = 'https://open.spotify.com/search/' + encodeURIComponent((entry.artist || '') + ' ' + (entry.track || entry.title));
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            link.textContent = 'Buscar en Spotify ↗';
            details.append(link);
            loadArtwork(entry).then(data => {
                if (!data.artwork) return;
                const img = document.createElement('img');
                img.alt = 'Portada de ' + (data.album || title.textContent);
                img.loading = 'lazy';
                img.width = img.height = 64;
                img.addEventListener('error', () => { cover.textContent = '♫'; });
                img.src = data.artwork;
                cover.replaceChildren(img);
                cover.removeAttribute('aria-label');
                link.href = data.url;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                link.textContent = (data.album || 'Ver lanzamiento') + ' · Spotify';
            });
            const time = document.createElement('time');
            time.dateTime = entry.played_at;
            time.textContent = new Date(entry.played_at).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
            time.title = new Date(entry.played_at).toLocaleString('es-MX');
            row.append(cover, details, time);
            historyList.append(row);
        });
        historyList.scrollTop = scrollTop;
        if (historyEmpty) {
            historyEmpty.hidden = entries.length > 0;
            historyEmpty.textContent = 'Todavía no hay canciones finalizadas en el historial.';
        }
    }

    async function fetchStatus() {
        try {
            const base = window.RADIO_BASE || '';
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 8000);
            let res;
            try {
                res = await fetch(base + '/ajax/status.php', { cache: 'no-store', signal: controller.signal });
            } finally {
                clearTimeout(timeout);
            }
            const data = await res.json();
            renderHistory(data.history);

            if (!data.online) {
                statusText.textContent = 'Fuera de línea';
                statusDot.className = 'dot';
                trackTitle.textContent = '—';
                listenerCount.textContent = '0';
                if (heroTrack) heroTrack.textContent = '—';
                return;
            }

            statusText.textContent = data.live ? 'EN VIVO' : 'Playlist automática';
            statusDot.className = 'dot ' + (data.live ? 'live' : 'online');
            trackTitle.textContent = data.title || 'Sin información';
            listenerCount.textContent = data.listeners ?? 0;
            if (heroTrack) heroTrack.textContent = data.title || 'Sin información';
        } catch (err) {
            statusText.textContent = 'Sin conexión';
            statusDot.className = 'dot';
            trackTitle.textContent = 'Título no disponible';
            if (heroTrack) heroTrack.textContent = 'Título no disponible';
        } finally {
            setTimeout(fetchStatus, 5000);
        }
    }

    fetchStatus();

    // ---------- Botón play/pause del banner de inicio ----------
    const audio = document.getElementById('audioPlayer');
    const heroPlayBtn = document.getElementById('heroPlayBtn');
    const heroPlayIcon = document.getElementById('heroPlayIcon');
    const playbackMessage = document.getElementById('playbackMessage');
    const volumeControl = document.getElementById('audioVolume');
    const volumeValue = document.getElementById('audioVolumeValue');
    const muteControl = document.getElementById('audioMute');
    if (audio && volumeControl && volumeValue && muteControl) {
        function syncVolume() {
            const percent = Math.round(audio.volume * 100);
            volumeControl.value = percent;
            const silent = audio.muted || percent === 0;
            volumeValue.textContent = silent ? '0%' : percent + '%';
            volumeControl.style.setProperty('--volume', (silent ? 0 : percent) + '%');
            muteControl.setAttribute('aria-label', silent ? 'Activar sonido' : 'Silenciar audio');
            muteControl.title = silent ? 'Activar sonido' : 'Silenciar audio';
            muteControl.setAttribute('aria-pressed', String(silent));
        }
        volumeControl.addEventListener('input', () => {
            audio.volume = Number(volumeControl.value) / 100;
            audio.muted = false;
            syncVolume();
        });
        muteControl.addEventListener('click', () => {
            if (audio.muted || audio.volume === 0) {
                audio.muted = false;
                if (audio.volume === 0) audio.volume = 1;
            } else {
                audio.muted = true;
            }
            syncVolume();
        });
        audio.addEventListener('volumechange', syncVolume);
        syncVolume();
    }
    const ICON_PLAY = '<polygon points="6,4 20,12 6,20"/>';
    const ICON_PAUSE = '<rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/>';

    if (audio && heroPlayBtn && heroPlayIcon) {
        let connectionTimer;
        function showStopped(message = '') {
            clearTimeout(connectionTimer);
            heroPlayIcon.innerHTML = ICON_PLAY;
            heroPlayBtn.setAttribute('aria-label', 'Reproducir');
            heroPlayBtn.setAttribute('aria-pressed', 'false');
            heroPlayBtn.removeAttribute('aria-busy');
            playbackMessage.textContent = message;
        }
        function connecting() {
            clearTimeout(connectionTimer);
            playbackMessage.textContent = 'Conectando audio…';
            heroPlayBtn.setAttribute('aria-busy', 'true');
            connectionTimer = setTimeout(() => {
                audio.pause();
                showStopped('La radio no responde. Pulsa play para reconectar.');
            }, 15000);
        }
        heroPlayBtn.addEventListener('click', async () => {
            if (audio.paused) {
                connecting();
                try {
                    audio.muted = false;
                    if (audio.volume === 0) audio.volume = 1;
                    audio.load();
                    await audio.play();
                } catch (err) {
                    if (err.name !== 'AbortError') showStopped('No se pudo reproducir. Pulsa para reintentar.');
                }
            } else {
                audio.pause();
            }
        });
        audio.addEventListener('play', () => {
            heroPlayIcon.innerHTML = ICON_PAUSE;
            heroPlayBtn.setAttribute('aria-label', 'Pausar');
            heroPlayBtn.setAttribute('aria-pressed', 'true');
        });
        audio.addEventListener('playing', () => {
            clearTimeout(connectionTimer);
            heroPlayBtn.removeAttribute('aria-busy');
            playbackMessage.textContent = 'Reproduciendo en directo';
        });
        audio.addEventListener('waiting', connecting);
        audio.addEventListener('error', () => {
            const messages = {
                2: 'Se perdió la conexión con la radio. Pulsa para reintentar.',
                3: 'No se pudo decodificar el audio de la radio.',
                4: 'No se pudo abrir la transmisión. Comprueba el acceso a la radio.',
            };
            showStopped(messages[audio.error?.code] || 'No se pudo reproducir. Pulsa para reintentar.');
        });
        audio.addEventListener('pause', () => showStopped());
        audio.addEventListener('ended', () => showStopped('La transmisión terminó. Pulsa para reconectar.'));
    }
})();
