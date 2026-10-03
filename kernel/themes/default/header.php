<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteName) ?></title>
    <link rel="preload" href="<?= htmlspecialchars($baseUrl) ?>/assets/fonts/Helvetica-Bold.ttf" as="font" type="font/ttf" crossorigin>
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../../../assets/css/style.css') ?>">
</head>
<body>
<script>window.RADIO_BASE = <?= json_encode($baseUrl) ?>;</script>

<div class="site-frame">

    <header class="header">
        <div class="header-inner">
            <div class="flag">
                <h1 class="wordmark"><?= htmlspecialchars($siteName) ?></h1>
            </div>
            <nav class="nav">
                <a class="n-about" href="#sobre">Sobre</a>
                <a class="n-dates" href="#dates">Fechas</a>
                <a class="n-merch" href="#merch">Merch</a>
                <a class="n-gallery" href="#programacion">Programación</a>
                <a class="n-contact" href="#escuchar">Escuchar</a>
            </nav>
        </div>
    </header>

    <main>
