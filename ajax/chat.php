<?php
// Local XAMPP uses the same shared chat as the public site.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($method, ['GET', 'POST'], true)) { http_response_code(405); echo '{"error":"Método no permitido."}'; exit; }
if ($method === 'POST') {
    $origin = parse_url($_SERVER['HTTP_ORIGIN'] ?? '');
    $originHost = ($origin['host'] ?? '') . (isset($origin['port']) ? ':' . $origin['port'] : '');
    if ($originHost !== ($_SERVER['HTTP_HOST'] ?? '') || !in_array($origin['scheme'] ?? '', ['http', 'https'], true)) { http_response_code(403); echo '{"error":"Abre el chat desde PANORAMA."}'; exit; }
    if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== 0) { http_response_code(415); echo '{"error":"Formato no válido."}'; exit; }
}
$body = $method === 'POST' ? file_get_contents('php://input', false, null, 0, 4097) : '';
if (strlen($body) > 4096) { http_response_code(413); echo '{"error":"Mensaje demasiado largo."}'; exit; }
$ch = curl_init('https://panorama-radio.pages.dev/ajax/chat.php');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_TIMEOUT => 12, CURLOPT_FOLLOWLOCATION => false, CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Origin: https://panorama-radio.pages.dev']]);
if ($method === 'POST') { curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, $body); }
$result = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($result === false || !is_array(json_decode($result, true))) { http_response_code(503); echo '{"error":"El chat no está disponible por ahora."}'; exit; }
http_response_code($status);
echo $result;