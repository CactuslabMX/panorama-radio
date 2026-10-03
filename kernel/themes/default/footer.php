</main>

    <footer class="site-footer">
        <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($siteName) ?></p>
    </footer>

</div>

<script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/player.js?v=<?= filemtime(__DIR__ . '/../../../assets/js/player.js') ?>"></script>
</body>
</html>
