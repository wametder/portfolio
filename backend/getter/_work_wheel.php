<?php

// -> Frames der Work-Rad-Animation laden (nur PNG und WebP), als Liste von URLs
function get_work_wheel_frames(string $size = 'm'): array
{
    $ids = carbon_get_theme_option('work_wheel_frames');
    if (empty($ids) || !is_array($ids)) {
        return [];
    }
    $allowed = ['image/png', 'image/webp'];
    $frames = [];
    foreach ($ids as $id) {
        $id = (int) $id;
        if (!$id || !in_array(get_post_mime_type($id), $allowed, true)) {
            continue;
        }
        $image = wp_get_attachment_image_src($id, $size);
        $src = $image ? $image[0] : wp_get_attachment_url($id);
        if ($src) {
            $frames[] = $src;
        }
    }
    return $frames;
}

// -> Wie viele Frames pro Projektschritt weitergehen (mind. 1, Standard 3)
function get_work_wheel_frames_per_step(): int
{
    $value = (int) carbon_get_theme_option('work_wheel_frames_per_step');
    return $value >= 1 ? $value : 3;
}
