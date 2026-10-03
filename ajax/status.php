<?php
/**
 * ajax/status.php
 * Devuelve JSON con: si está en vivo o playlist, canción actual, oyentes.
 * Consulta el status-json.xsl de Icecast.
 */

require_once __DIR__ . '/../kernel/Core.php';
require_once __DIR__ . '/../kernel/TrackHistory.php';
require_once __DIR__ . '/../kernel/RadioDJHistory.php';
header('Cache-Control: no-store');
$radioHistory = RadioDJHistory::recent();

$statusUrl  = Core::get('icecast', 'status_url');
$statusUser = Core::get('icecast', 'status_user');
$statusPass = Core::get('icecast', 'status_pass');

$ch = curl_init($statusUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD => "{$statusUser}:{$statusPass}",
    CURLOPT_TIMEOUT => 5,
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false || $httpCode !== 200) {
    Core::json([
        'online' => false,
        'error' => 'No se pudo conectar con Icecast',
        'history' => $radioHistory ?? [],
    ], 503);
}

$data = json_decode($response, true);
$source = $data['icestats']['source'] ?? null;

// Icecast puede devolver un array si hay varios mountpoints, o un objeto si solo hay uno
if (isset($source[0])) {
    $source = $source[0];
}

if (!$source) {
    Core::json([
        'online' => false,
        'title' => null,
        'listeners' => 0,
        'history' => $radioHistory ?? [],
    ]);
}

// Liquidsoap suele mandar el título de la fuente en vivo con un prefijo distinto
// al de la playlist si se configura `metadata` en el .liq — ajusta según tu caso.
$title = 'Sin información';
foreach ([
    $source['title'] ?? null,
    $source['metadata']['x_icy_title'] ?? null,
    $source['yp_currently_playing'] ?? null,
    (static function (): ?string {
        $path = Core::get('radiodj', 'now_playing_file');
        if (!$path || !is_readable($path)) return null;
        $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) return null;
        foreach ($lines as $line) {
            $value = trim($line);
            if ($value !== '' && !in_array(strtolower($value), ['$autotitle$', 'autotitle', 'none', 'sin información'], true)) {
                return $value;
            }
        }
        return null;
    })(),
] as $candidate) {
    if (is_string($candidate) && trim($candidate) !== '') {
        $title = trim($candidate);
        break;
    }
}
$isLive = stripos($title, '[live]') !== false; // ejemplo: ajusta a tu convención de metadata

Core::json([
    'online' => true,
    'live' => $isLive,
    'title' => $title,
    'history' => $radioHistory ?? TrackHistory::update($title),
    'listeners' => (int)($source['listeners'] ?? 0),
    'stream_url' => Core::streamUrl(),
]);
