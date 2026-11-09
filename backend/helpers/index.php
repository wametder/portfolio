<?php
/**
 * Funktionen in dieser Datei können überall im Projekt verwendet werden.
 * Wenn eine neue globale Funktion benötigt wird, bitte hier ergänzen.
 *
 * Diese globalen Funktionen sind nicht Wordpress-spezifisch.
 */

// -> PHP-Daten in der Browser-Konsole anzeigen
function js_log(mixed ...$content): void
{
    foreach ($content as $c) {
        echo '<script>console.log(' . json_encode($c) . ')</script>';
    }
}

// -> Details einer Mediendatei anhand der ID laden
function get_media_by_id(int|string $id, string $size = 'm'): false|array
{
    $id = (int) $id;
    $data = [
        'title' => get_the_title($id),
        'alt' => get_post_meta($id, '_wp_attachment_image_alt', true) ?: get_the_title($id),
    ];
    $image = wp_get_attachment_image_src($id, $size);
    if ($image) {
        $data['src'] = $image[0];
        $data['type'] = 'image';
    } else {
        $data['src'] = wp_get_attachment_url($id);
        $data['type'] = get_post_mime_type($id);
    }
    return $data;
}

// -> Eine sichere Embed-URL aus einer YouTube- oder Vimeo-URL erstellen
function get_video_embed_url(?string $url): false|string
{
    $url = trim((string) $url);
    if ($url === '') {
        return false;
    }

    $parts = wp_parse_url($url);
    $host = strtolower($parts['host'] ?? '');
    $host = preg_replace('/^www\./', '', $host);
    $video_id = '';

    if ($host === 'youtu.be') {
        $video_id = trim($parts['path'] ?? '', '/');
    } elseif (in_array($host, ['youtube.com', 'm.youtube.com'], true)) {
        if (($parts['path'] ?? '') === '/watch') {
            parse_str($parts['query'] ?? '', $query);
            $video_id = $query['v'] ?? '';
        } elseif (preg_match('#^/(?:embed|shorts)/([^/?]+)#', $parts['path'] ?? '', $matches)) {
            $video_id = $matches[1];
        }

        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $video_id)) {
            return 'https://www.youtube.com/embed/' . $video_id;
        }
    } elseif (in_array($host, ['vimeo.com', 'player.vimeo.com'], true)) {
        if (preg_match('#/(?:video/)?([0-9]+)(?:$|/)#', $parts['path'] ?? '', $matches)) {
            $video_id = $matches[1];
        }

        if (preg_match('/^[0-9]+$/', $video_id)) {
            return 'https://player.vimeo.com/video/' . $video_id;
        }
    }

    return false;
}

// -> Das Seitenverhältnis eines externen Videos ermitteln
function get_video_embed_ratio(?string $url): float
{
    $fallback = 16 / 9;
    $url = trim((string) $url);
    $parts = wp_parse_url($url);
    $host = strtolower($parts['host'] ?? '');
    $host = preg_replace('/^www\./', '', $host);

    if (!in_array($host, ['vimeo.com', 'player.vimeo.com'], true)) {
        return $fallback;
    }

    $cache_key = 'video_ratio_v2_' . md5($url);
    $cached_ratio = get_transient($cache_key);
    if (is_numeric($cached_ratio) && (float) $cached_ratio > 0) {
        return (float) $cached_ratio;
    }

    $response = wp_remote_get(
        'https://vimeo.com/api/oembed.json?url=' . rawurlencode($url),
        ['timeout' => 3]
    );
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return $fallback;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    $width = $data['thumbnail_width'] ?? ($data['width'] ?? 0);
    $height = $data['thumbnail_height'] ?? ($data['height'] ?? 0);
    if (!is_numeric($width) || !is_numeric($height) || (float) $width <= 0 || (float) $height <= 0) {
        return $fallback;
    }

    $ratio = round((float) $width / (float) $height, 4);
    set_transient($cache_key, $ratio, DAY_IN_SECONDS);
    return $ratio;
}

// -> Ein Menu anhand des Keys laden
function get_menu_by_slug(string $slug): array
{
    $theme_locations = get_nav_menu_locations();
    if (!array_key_exists($slug, $theme_locations)) {
        return [];
    }
    $menu_obj = get_term($theme_locations[$slug], 'nav_menu');
    $menu = wp_get_nav_menu_items($menu_obj->name) ?: [];
    $items = [];
    foreach ($menu as $item) {
        $items[] = [
            'ID' => (int) $item->ID,
            'title' => $item->title,
            'url' => $item->url,
            'target' => $item->target,
        ];
    }
    return $items;
}
