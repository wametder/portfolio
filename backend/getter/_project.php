<?php

function get_project_by_id(int $id): array{
    // -> collect data
    $title = get_the_title($id);
    $image_id = carbon_get_post_meta($id, 'image');
    $image = $image_id ? get_media_by_id($image_id) : false;
    $youtube_url = carbon_get_post_meta($id, 'youtube_url');
    $additional_images = carbon_get_post_meta($id, 'additional_images') ?: [];
    $additional_images = array_values(array_filter(array_map(
        static function (array $item): false|array {
            return !empty($item['image']) ? get_media_by_id($item['image'], 'full') : false;
        },
        $additional_images
    )));
    $preview_id = carbon_get_post_meta($id, 'preview');
    $preview = get_media_by_id($preview_id);
    $lead = carbon_get_post_meta($id, 'lead');
    $text = carbon_get_post_meta($id, 'text');
    $illustration_id = carbon_get_post_meta($id, 'illustration');
    $illustration = get_media_by_id($illustration_id);
    $link = get_permalink($id);
    // return data
    return [
        'title' => $title,
        'image' => $image,
        'youtube_url' => $youtube_url,
        'additional_images' => $additional_images,
        'preview' => $preview,
        'lead' => $lead,
        'text' => $text,
        'illustration' => $illustration,
        'link' => $link,
    ];
//    js_log('soll infos zum projekt zurückgebe mit ID:' . $id);

}