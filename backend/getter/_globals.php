<?php

// todo: Hier die Getter für die Globalen Informationen definieren
function get_globals_logo() {
    $logo_id = carbon_get_theme_option('logo');
    $logo = get_media_by_id($logo_id, 'full');
    return $logo;
}
function get_globals_illuface() {
    $illuface_id = carbon_get_theme_option('illuface');
    $illuface = get_media_by_id($illuface_id);
    return $illuface;
}
