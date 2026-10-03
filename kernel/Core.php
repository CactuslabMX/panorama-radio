<?php
/**
 * kernel/Core.php
 * Núcleo básico: carga configuración (init.conf) y expone helpers comunes.
 */

class Core
{
    private static ?array $config = null;

    /**
     * Lee init.conf (formato INI con secciones) y lo cachea en memoria.
     */
    public static function config(): array
    {
        if (self::$config === null) {
            $path = __DIR__ . '/../init.conf';
            if (!file_exists($path)) {
                throw new RuntimeException("No se encontró init.conf en $path");
            }
            self::$config = parse_ini_file($path, true);
        }
        return self::$config;
    }

    public static function get(string $section, string $key, $default = null)
    {
        $cfg = self::config();
        return $cfg[$section][$key] ?? $default;
    }

    /**
     * Carga una vista del theme activo (kernel/themes/<theme>/<view>.php)
     */
    public static function view(string $view, array $data = []): void
    {
        extract($data);
        $theme = self::get('site', 'theme', 'default');
        $path = __DIR__ . "/themes/{$theme}/{$view}.php";
        if (!file_exists($path)) {
            throw new RuntimeException("Vista no encontrada: $path");
        }
        require $path;
    }

    /**
     * Responde JSON y termina la ejecución (usado en ajax/)
     */
    public static function streamUrl(): string
    {
        return rtrim(self::get('site', 'base_url', ''), '/') . '/ajax/stream.php';
    }

    public static function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
