</main>
</div>

    <footer class="site-footer">
        <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($siteName) ?></p>
        <div class="footer-socials" role="group" aria-label="Redes sociales de PANORAMA">
            <span class="footer-social-icon" role="img" aria-label="Instagram" title="Instagram">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
            </span>
            <span class="footer-social-icon" role="img" aria-label="TikTok" title="TikTok">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3h3c.3 2.4 1.8 4 4 4.3v3a9 9 0 0 1-4-1.5V16a6 6 0 1 1-6-6v3a3 3 0 1 0 3 3Z"/></svg>
            </span>
            <span class="footer-social-icon" role="img" aria-label="YouTube" title="YouTube">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="4"/><path d="m10 9 5 3-5 3Z" fill="currentColor" stroke="none"/></svg>
            </span>
        </div>
        <p>RADIO INDEPENDIENTE. ESCUCHA ABIERTA.</p>
    </footer>

<script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/player.js?v=<?= filemtime(__DIR__ . '/../../../assets/js/player.js') ?>"></script>
<script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/chat.js?v=<?= filemtime(__DIR__ . '/../../../assets/js/chat.js') ?>" defer></script>
</body>
</html>
