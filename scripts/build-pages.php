<?php
// Render the existing templates without loading local credentials or radio data.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
$out = $root . '/dist';
if (!is_dir($out)) mkdir($out, 0755, true);
$siteName = 'PANORAMA';
$baseUrl = '';
$streamUrl = '/ajax/stream.php';
ob_start();
foreach (['header', 'body', 'footer'] as $view) require $root . '/kernel/themes/default/' . $view . '.php';
$html = ob_get_clean();
file_put_contents($out . '/index.html', $html);
file_put_contents($out . '/404.html', '<!doctype html><html lang="es"><meta charset="utf-8"><title>PANORAMA</title><p>Página no encontrada. <a href="/">Volver a PANORAMA</a></p></html>');
$assets = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/assets', FilesystemIterator::SKIP_DOTS));
foreach ($assets as $file) {
    if (!$file->isFile() || $file->isLink()) continue;
    $relative = substr($file->getPathname(), strlen($root) + 1);
    $target = $out . '/' . $relative;
    if (!is_dir(dirname($target))) mkdir(dirname($target), 0755, true);
    copy($file->getPathname(), $target);
}
copy($root . '/cloudflare/worker.mjs', $out . '/_worker.js');
file_put_contents($out . '/_routes.json', json_encode(['version' => 1, 'include' => ['/ajax/*'], 'exclude' => []]));
file_put_contents($out . '/_headers', "/*\n  X-Content-Type-Options: nosniff\n  Referrer-Policy: strict-origin-when-cross-origin\n  X-Frame-Options: DENY\n");
echo "Cloudflare Pages build ready in dist/\n";
