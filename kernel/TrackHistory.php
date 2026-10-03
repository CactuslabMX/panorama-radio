<?php

class TrackHistory
{
    // Registra cambios observados mientras se consulta el estado de la emisora.
    public static function update(string $title): array
    {
        $directory = __DIR__ . '/../data';
        if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
            return [];
        }
        $file = @fopen($directory . '/history.json', 'c+');
        if (!$file) return [];
        if (!flock($file, LOCK_EX)) {
            fclose($file);
            return [];
        }
        try {
            $entries = json_decode(stream_get_contents($file), true) ?: [];
            $station = trim((string) Core::get('site', 'name', ''));
            $valid = $title !== 'Sin información' && $title !== '' && strcasecmp($title, $station) !== 0;
            if ($valid && ($entries[0]['title'] ?? null) !== $title) {
                array_unshift($entries, ['title' => $title, 'played_at' => gmdate('c')]);
                $entries = array_slice($entries, 0, 21);
                rewind($file);
                ftruncate($file, 0);
                fwrite($file, json_encode($entries, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                fflush($file);
            }
            return ($entries[0]['title'] ?? null) === $title ? array_slice($entries, 1) : $entries;
        } finally {
            flock($file, LOCK_UN);
            fclose($file);
        }
    }
}
