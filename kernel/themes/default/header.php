<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" sizes="any" href="<?= htmlspecialchars($baseUrl) ?>/assets/img/favicon.svg?v=editorial1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteName) ?></title>
    <link rel="preload" href="<?= htmlspecialchars($baseUrl) ?>/assets/fonts/Helvetica-Bold.ttf" as="font" type="font/ttf" crossorigin>
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../../../assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/assets/css/editorial.css?v=<?= filemtime(__DIR__ . '/../../../assets/css/editorial.css') ?>">
    <meta name="theme-color" content="#0833A2">
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/assets/css/mosaico.css?v=<?= filemtime(__DIR__ . '/../../../assets/css/mosaico.css') ?>">
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
                <a class="n-contact" href="#escuchar">Escuchar</a>
                <a class="n-chat" href="#chat">La sala</a>
                <a class="n-about" href="#sobre">Sobre</a>
                <a class="n-merch" href="#merch">Merch</a>
            </nav>
        </div>
    </header>

    <main>
