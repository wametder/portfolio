<?php

function get_project_by_id(int $id): array{
    // -> collect data
    $title = get_the_title($id);
    $image_id = carbon_get_post_meta($id, 'image');
    $image = get_media_by_id($image_id);
    $lead = carbon_get_post_meta($id, 'lead');
    $text = carbon_get_post_meta($id, 'text');
    $illustration_id = carbon_get_post_meta($id, 'illustration');
    $illustration = get_media_by_id($illustration_id);
    // return data
    return [
        'title' => $title,
        'image' => $image,
        'lead' => $lead,
        'text' => $text,
        'illustration' => $illustration
    ];
//    js_log('soll infos zum projekt zurückgebe mit ID:' . $id);

}