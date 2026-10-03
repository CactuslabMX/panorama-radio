<?php
/**
 * player.php — Orquesta el layout: header + body + footer
 * Esta es la única vista que index.php invoca directamente
 * (Core::view('player', [...])), así que aquí decidimos qué
 * sub-vistas se cargan y en qué orden.
 */

$viewData = compact('siteName', 'baseUrl', 'streamUrl');

Core::view('header', $viewData);
Core::view('body', $viewData);
Core::view('footer', $viewData);