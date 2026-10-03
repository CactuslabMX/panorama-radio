<?php
require_once __DIR__ . '/../kernel/Core.php';
header('Cache-Control: public, max-age=3600');
$artist = trim(substr((string) ($_GET['artist'] ?? ''), 0, 200));
$track = trim(substr((string) ($_GET['track'] ?? ''), 0, 200));
$album = trim(substr((string) ($_GET['album'] ?? ''), 0, 200));
function normalized(string $value): string {
    $value = strtr($value, [
        'á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u', 'ü'=>'u', 'ñ'=>'n',
        'Á'=>'A', 'É'=>'E', 'Í'=>'I', 'Ó'=>'O', 'Ú'=>'U', 'Ü'=>'U', 'Ñ'=>'N',
    ]);
    $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    $value = str_replace(["'", '`', '"'], '', $value ?: '');
    return trim(preg_replace('/[^a-z0-9]+/', ' ', strtolower($value ?: '')));
}
if ($track === '') Core::json(['artwork' => null]);
// Un artista idéntico al título suele indicar etiquetas incompletas.
if (normalized($artist) === normalized($track)) $artist = '';
$directory = __DIR__ . '/../data/artwork';
if (!is_dir($directory)) @mkdir($directory, 0775, true);
$cache = $directory . '/' . hash('sha256', 'spotify-v1|' . $artist . '|' . $track . '|' . $album) . '.json';
if (is_file($cache) && filemtime($cache) > time() - 86400) {
    $cached = json_decode(file_get_contents($cache), true);
    if (is_array($cached)) Core::json($cached);
}
$credentialsPath = __DIR__ . '/../kernel/spotify.local.php';
$credentials = is_file($credentialsPath) ? require $credentialsPath : [];
$clientId = getenv('SPOTIFY_CLIENT_ID') ?: ($credentials['client_id'] ?? '');
$clientSecret = getenv('SPOTIFY_CLIENT_SECRET') ?: ($credentials['client_secret'] ?? '');
if (!$clientId || !$clientSecret) {
    header('Cache-Control: no-store');
    Core::json(['artwork' => null, 'provider' => 'spotify', 'reason' => 'not_configured']);
}
function spotifyRequest(string $url, array $options = []): array {
    $curl = curl_init($url);
    curl_setopt_array($curl, $options + [CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 7]);
    $response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return [$status, json_decode($response ?: '', true)];
}
// El token se almacena fuera del directorio público, separado por aplicación.
$tokenPath = sys_get_temp_dir() . '/panorama-spotify-' . hash('sha256', $clientId . $clientSecret) . '.json';
$tokenData = is_file($tokenPath) ? json_decode(file_get_contents($tokenPath), true) : null;
if (!$tokenData || ($tokenData['expires_at'] ?? 0) < time() + 60) {
    [$status, $token] = spotifyRequest('https://accounts.spotify.com/api/token', [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
        CURLOPT_HTTPHEADER => ['Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret), 'Content-Type: application/x-www-form-urlencoded'],
    ]);
    if ($status !== 200 || empty($token['access_token'])) {
        header('Cache-Control: no-store');
        Core::json(['artwork' => null, 'reason' => 'spotify_auth_unavailable'], 503);
    }
    $tokenData = ['token' => $token['access_token'], 'expires_at' => time() + (int) $token['expires_in']];
    @file_put_contents($tokenPath, json_encode($tokenData), LOCK_EX);
}
$query = 'track:' . $track . ($artist !== '' ? ' artist:' . $artist : '');
[$status, $data] = spotifyRequest('https://api.spotify.com/v1/search?' . http_build_query([
    'q' => $query, 'type' => 'track', 'market' => 'MX', 'limit' => 10,
]), [CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $tokenData['token']]]);
if ($status !== 200 || !isset($data['tracks']['items'])) {
    if ($status === 401) @file_put_contents($tokenPath, '{}', LOCK_EX);
    header('Cache-Control: no-store');
    Core::json(['artwork' => null, 'reason' => 'spotify_search_unavailable'], 503);
}
$matches = array_values(array_filter($data['tracks']['items'], static function ($item) use ($artist, $track, $album) {
    $artists = array_map('normalized', array_column($item['artists'] ?? [], 'name'));
    return normalized($item['name'] ?? '') === normalized($track)
        && ($artist === '' || in_array(normalized($artist), $artists, true))
        && ($album === '' || normalized($item['album']['name'] ?? '') === normalized($album));
}));
$result = ['artwork' => null, 'provider' => 'spotify'];
$artists = array_unique(array_map(static function ($item) { return $item['artists'][0]['id'] ?? ''; }, $matches));
if ($matches && ($artist !== '' || count($artists) === 1)) {
    $match = $matches[0];
    $artwork = $match['album']['images'][0]['url'] ?? '';
    $link = $match['external_urls']['spotify'] ?? '';
    if (preg_match('~^https://i\.scdn\.co/image/~', $artwork) && preg_match('~^https://open\.spotify\.com/track/~', $link)) {
        $result = ['artwork' => $artwork, 'album' => $match['album']['name'], 'url' => $link, 'provider' => 'spotify'];
    }
}
@file_put_contents($cache, json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
Core::json($result);
