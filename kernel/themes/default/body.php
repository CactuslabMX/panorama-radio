<section class="hero-banner" id="escuchar">
        <div class="hero-media">
            <img src="<?= htmlspecialchars($baseUrl) ?>/assets/img/radio.gifv" alt="">
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
    <h2 class="section-label" id="historyHeading">recién sonaron_</h2>
    <p id="historyEmpty">Consultando las últimas canciones de RadioDJ…</p>
    <ol id="trackHistory" tabindex="0" aria-label="Últimas canciones; desplázate para ver más"></ol>
</section>

<section class="about-wrapper" id="sobre">
            <div class="section-label">sobre_</div>

            <div class="about">
                <img class="photo" src="<?= htmlspecialchars($baseUrl) ?>/assets/img/dj.webp" alt="">
                <div class="about-text">
                    <p>
                        En Panorama valoramos la calidad musical y nos esforzamos por traer a artistas de renombre y talentos emergentes que representan lo mejor de la escena. Creemos en la importancia de crear un entorno inclusivo donde todos sean bienvenidos, independientemente de su origen, género o preferencias musicales.
                    </p>
                    <p class="section-sublabel">AL AIRE 24/7, SIN ANUNCIOS, SIN PROPAGANDA</p>
                </div>
            </div>
        </section>

        <div class="section-label">programación_</div>
        <section class="arrow-banner" id="programacion">
            <div class="slot"><span class="time">00:00&ndash;08:00</span>Playlist automática</div>
            <div class="slot"><span class="time">08:00&ndash;10:00</span>En vivo</div>
            <div class="slot"><span class="time">10:00&ndash;24:00</span>Playlist automática</div>
        </section>

        <section class="merch-collection" id="merch" aria-labelledby="merchHeading">
            <div class="merch-heading"><div><p class="merch-eyebrow">PANORAMA / RADIO INDEPENDIENTE</p><h2 id="merchHeading">Para escuchar distinto.</h2></div><span class="merch-edition">Música, arte y otras frecuencias.<br>Colección 01</span></div>
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
