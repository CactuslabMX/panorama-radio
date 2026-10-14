<section class="hero-banner" id="escuchar">
        <div class="hero-media">
            <div class="hero-photo"><img src="<?= htmlspecialchars($baseUrl) ?>/assets/img/dj.webp" alt="Tornamesas y cultura musical de PANORAMA"><span>SONIDOS QUE NOS ENCUENTRAN.</span></div>
            <div class="mosaic-shape" aria-hidden="true"></div>
            <div class="hero-overlay">
                <div class="hero-status">
                    <div class="hero-meta">Sonando ahora <span class="hero-listeners" id="listenerBadge" role="img" aria-label="Consultando oyentes" title="Oyentes conectados">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3 20v-2a6 6 0 0 1 12 0v2M16 5a3 3 0 0 1 0 6m3 9v-2a6 6 0 0 0-2-4"/></svg>
                        <span id="listenerCount">—</span>
                    </span></div>
                    <span id="heroTrack" aria-live="polite">Consultando canción…</span><span id="playbackMessage" role="status"></span>
                </div>
                <button class="hero-play" id="heroPlayBtn" aria-label="Reproducir">
                    <svg id="heroPlayIcon" viewBox="0 0 24 24"><polygon points="6,4 20,12 6,20"/></svg>
                </button>
                <div class="hero-volume">
                    <button type="button" id="audioMute" aria-label="Silenciar audio" aria-pressed="false" title="Silenciar audio"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 5 6 9H3v6h3l5 4Z"/><path class="volume-waves" d="M15 8a6 6 0 0 1 0 8m3-11a10 10 0 0 1 0 14"/><path class="volume-cross" d="m16 9 6 6m0-6-6 6"/></svg></button>
                    <input id="audioVolume" type="range" min="0" max="100" value="100" aria-label="Volumen de la radio">
                    <output id="audioVolumeValue" for="audioVolume">100%</output>
                </div>
            </div>
            <audio id="audioPlayer" preload="none" hidden>
                <source src="<?= htmlspecialchars($streamUrl) ?>" type="audio/mpeg">
            </audio>
        </div>
    </section>

<section class="recent-tracks" id="historial" aria-labelledby="historyHeading">
    <p class="section-eyebrow">El recorrido</p>
    <h2 class="section-label" id="historyHeading">Recién sonaron ↙</h2>
    <p id="historyEmpty">Consultando las últimas canciones de RadioDJ…</p>
    <ol id="trackHistory" tabindex="0" aria-label="Últimas canciones; desplázate para ver más"></ol>
</section>

<section class="radio-chat" id="chat" aria-labelledby="chatHeading">
    <div class="chat-section-heading"><div><span class="chat-eyebrow">COMUNIDAD PANORAMA</span><h2 id="chatHeading">La sala.</h2></div><p>La música sigue.<br> La conversación también.</p></div>
    <div class="chat-shell">
        <div class="chat-topline">
            <div class="chat-room-title"><span class="chat-monogram" aria-hidden="true">P</span><div><h3>Chat de oyentes</h3><span id="chatStatus" role="status">Sala abierta</span></div></div>
            <span class="chat-room-tag">RADIO INDEPENDIENTE</span>
        </div>
        <div id="chatWelcome" class="chat-welcome">
            <svg class="chat-welcome-icon" viewBox="0 0 64 64" fill="none" aria-hidden="true"><path d="M12 14h40v29H29L17 53V43h-5V14Z" stroke="currentColor" stroke-width="2"/><path d="M23 26h18M23 32h12" stroke="currentColor" stroke-width="2"/></svg>
            <h4>Hay alguien del otro lado.</h4>
            <p>Entra, saluda y comparte lo que suena.<br>Solo necesitas un apodo.</p>
            <button type="button" id="chatJoin">Entrar a la sala <span aria-hidden="true">↗</span></button>
        </div>
        <div id="chatRoom" hidden>
            <div class="chat-conversation">
                <div class="chat-messages" id="chatMessages" role="log" aria-label="Mensajes de los oyentes" aria-live="polite" tabindex="0"></div>
                <p id="chatEmpty" class="chat-empty" hidden>Todo empieza con un hola.<br><span>Sé la primera voz de la sala.</span></p>
                <button id="chatLatest" class="chat-latest" type="button" hidden>Mensajes nuevos ↓</button>
            </div>
            <form id="chatForm" class="chat-form">
                <div class="chat-name"><label for="chatNickname">ESCRIBES COMO</label><input id="chatNickname" name="nickname" minlength="2" maxlength="24" required autocomplete="nickname" placeholder="Tu apodo"><span id="chatCounter">0 / 300</span></div>
                <div class="chat-compose"><label class="chat-sr-only" for="chatMessage">Tu mensaje</label><textarea id="chatMessage" name="message" rows="1" maxlength="300" required placeholder="Súmate a la conversación…"></textarea><button type="submit" id="chatSend" aria-label="Enviar mensaje" title="Enviar mensaje"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m5 12 14-7-4 14-3-6-7-1Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m12 13 7-8" stroke="currentColor" stroke-width="1.7"/></svg></button></div>
            </form>
            <p id="chatError" class="chat-error" role="alert"></p>
        </div>
    </div>
    <p class="chat-note">Un espacio público, sin cuentas. Buena música y respeto.<br><span>Mensajes disponibles hasta 7 días · Máximo 200 mensajes.</span></p>
</section>
<section class="about-wrapper" id="sobre">
            <div class="section-label">Esto es PANORAMA</div>
            <h2 class="about-title">Música para<br>escuchar distinto.</h2>

            <div class="about">
                <div class="about-text">
                    <p>
                        En Panorama valoramos la calidad musical y nos esforzamos por traer a artistas de renombre y talentos emergentes que representan lo mejor de la escena. Creemos en la importancia de crear un entorno inclusivo donde todos sean bienvenidos, independientemente de su origen, género o preferencias musicales.
                    </p>
                    <p class="section-sublabel">AL AIRE 24/7, SIN ANUNCIOS, SIN PROPAGANDA</p>
                </div>
            </div>
        </section>

        <section class="merch-collection" id="merch" aria-labelledby="merchHeading">
            <div class="merch-heading"><div><p class="merch-eyebrow">PANORAMA / RADIO INDEPENDIENTE</p><h2 id="merchHeading">Objetos de la radio.</h2></div><span class="merch-edition">Música, arte y otras frecuencias.<br>Colección 01</span></div>
            <div class="merch-grid">
                <?php foreach ([
                    ['001', 'Señal', 'Playera negra / Tipografía y sonido en verde menta', 'panorama-indie-shirt.png', 'Playera negra PANORAMA Radio Independiente con gráfica sonora en verde menta'],
                    ['002', 'Frecuencia', 'Gorra 5 panel / Taupe, bordado marfil y menta', 'panorama-indie-cap.png', 'Gorra PANORAMA de cinco paneles, visera plana y logotipo bordado'],
                    ['003', 'En el aire', 'Hoodie marfil / Gráfica de señal en rojo óxido', 'panorama-indie-hoodie.png', 'Hoodie marfil PANORAMA Radio Independiente con pequeña onda sonora roja'],
                ] as [$number, $name, $description, $file, $alt]): ?>
                <article class="merch-item">
                    <a class="merch-image" href="<?= htmlspecialchars($baseUrl) ?>/assets/img/<?= $file ?>" target="_blank" rel="noopener" aria-label="Ampliar diseño <?= $name ?>">
                        <img class="photo" src="<?= htmlspecialchars($baseUrl) ?>/assets/img/<?= $file ?>" alt="<?= $alt ?>" loading="lazy" width="1254" height="1254">
                        <span class="merch-zoom">Ver diseño ↗</span>
                    </a>
                    <div class="merch-name"><h3><?= $name ?></h3><span>/ <?= $number ?></span></div>
                    <p class="merch-description"><?= $description ?></p>
                </article>
                <?php endforeach; ?>
            </div>
            <p class="merch-note">Vista previa de la colección · Próximamente</p>
        </section>
