<?php
/**
 * index.php — Front controller
 */

require_once __DIR__ . '/kernel/Core.php';
header('Cache-Control: no-store');

Core::view('player', [
    'siteName'  => Core::get('site', 'name'),
    'baseUrl'   => Core::get('site', 'base_url', ''),
    'streamUrl' => Core::streamUrl(),
]);
