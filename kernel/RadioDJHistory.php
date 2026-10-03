<?php

class RadioDJHistory
{
    public static function recent(): ?array
    {
        $path = Core::get('radiodj', 'database_settings');
        if (!$path || !is_readable($path)) return null;
        try {
            // Reutiliza la configuración local sin publicar ni copiar credenciales.
            $xml = @simplexml_load_file($path, 'SimpleXMLElement', LIBXML_NONET);
            if ($xml === false) return null;
            $config = [];
            foreach ($xml->Settings as $setting) {
                $config[(string) $setting->Key] = (string) $setting->Value;
            }
            $db = new PDO(
                'mysql:host=' . $config['DbServer'] . ';dbname=' . $config['DbName'] . ';charset=utf8mb4',
                $config['DbUsername'],
                $config['DbPass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 3]
            );
            // RadioDJ registra el inicio. Excluye la canción hasta transcurrir su duración.
            $rows = $db->query(
                'SELECT ID, artist, title, album, date_played FROM history
                 WHERE song_type = 0 AND duration > 0 AND title <> \'\'
                   AND DATE_ADD(date_played, INTERVAL CEIL(duration) SECOND) <= NOW()
                 ORDER BY date_played DESC, ID DESC LIMIT 30'
            )->fetchAll(PDO::FETCH_ASSOC);
            $timezone = new DateTimeZone(Core::get('radiodj', 'timezone', 'America/Mexico_City'));
            return array_map(static function ($row) use ($timezone) {
                $artist = trim($row['artist'] ?? '');
                $title = trim($row['title']);
                return [
                    'id' => (int) $row['ID'],
                    'artist' => $artist,
                    'track' => $title,
                    'album' => trim($row['album'] ?? ''),
                    'title' => $artist !== '' && strcasecmp($artist, $title) !== 0 ? $artist . ' - ' . $title : $title,
                    'played_at' => (new DateTimeImmutable($row['date_played'], $timezone))->format(DATE_ATOM),
                ];
            }, $rows);
        } catch (Throwable $error) {
            error_log('No se pudo consultar el historial de RadioDJ (código ' . $error->getCode() . ').');
            return null;
        }
    }
}
