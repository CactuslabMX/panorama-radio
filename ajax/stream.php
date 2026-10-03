<?php
// Audio de Icecast servido desde el mismo origen que la página.
require_once __DIR__ . '/../kernel/Core.php';
set_time_limit(0);
ini_set('zlib.output_compression', '0');
while (ob_get_level() > 0) ob_end_clean();

header('Cache-Control: no-store, no-transform');
header('X-Accel-Buffering: no');
header('Content-Type: audio/mpeg');
header('X-Content-Type-Options: nosniff');

$url = sprintf('http://%s:%d%s', Core::get('icecast', 'stream_host'),
    Core::get('icecast', 'stream_port'), Core::get('icecast', 'mount'));
$sent = false;
$curl = curl_init($url);
curl_setopt_array($curl, [
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_LOW_SPEED_LIMIT => 1,
    CURLOPT_LOW_SPEED_TIME => 15,
    CURLOPT_HTTPHEADER => ['Icy-MetaData: 0'],
    CURLOPT_BUFFERSIZE => 8192,
    CURLOPT_WRITEFUNCTION => static function ($handle, $chunk) use (&$sent) {
        if (connection_aborted() || curl_getinfo($handle, CURLINFO_HTTP_CODE) !== 200) return 0;
        $sent = true;
        echo $chunk;
        flush();
        return strlen($chunk);
    },
]);
curl_exec($curl);
curl_close($curl);
if (!$sent && !headers_sent()) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'La transmisión no está disponible.';
}
