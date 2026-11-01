<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field;

// -> Konfiguration der verschiedenen Module
Container::make('post_meta', 'Module')
    ->where('post_type', '=', 'page')
    ->add_fields([
        Field::make('complex', 'modules', 'Module')
            // -> example
            ->add_fields('example', 'Beispiel', [
                Field::make('text', 'title', 'Titel')->set_required(),
                Field::make('image', 'image', 'Bild')->set_required(),
            ])
            ->add_fields('hero_front', 'Hero Startseite', [
                Field:: make('text', 'title', 'Titel')->set_required(),
                Field:: make('textarea', 'subline', 'Subline'),
                Field:: make('image', 'image', 'Bild')->set_required(),
            ])

              ->add_fields('overview_projects', 'Übersicht Projekte', [
                Field:: make('text', 'title', 'Titel'),
                Field:: make('association', 'selection', 'Übersicht Projekte')
                    ->set_types([
                        [
                            'type' => 'post',
                            'post_type' => 'project',
                        ]
                    ])
                ])
              ->add_fields('about_me', 'Über mich (Nicolas)', [
                Field::make('text', 'title', 'Titel')->set_required(),
                Field::make('textarea', 'lead', 'Lead')->set_required(),
                Field::make('rich_text', 'rich_text', 'Text')->set_required(),
                Field::make('image', 'illustration', 'Illustration')->set_required(),
                Field::make('file', 'file', 'Bild / Video')
                    ->set_type(['image', 'video'])
                    ->set_required(),
              ])
              ->set_layout('tabbed-vertical')
    ]);
// todo: add new modules here
// -> Funktion, um die Module und die entsprechenden Templates auf der Seite zu laden
function render_modules (): void {
    $modules = carbon_get_the_post_meta('modules');
    if (empty($modules)) {
        return;
    }
    foreach ($modules as $module) {
        // -> Modifiziere die Daten des Moduls, falls nötig, basierend auf dem Modultyp
        switch ($module['_type']) {
            case 'example':
                $module['image'] = get_media_by_id($module['image'], 'full');
                break;
            case 'hero_front':
                $module['image'] = get_media_by_id($module['image'], 'full');
                break;
            case 'overview_projects':
                $module['selection'] = array_map(function ($item) {
                    return (int) $item['id'];
                }, $module['selection']);
                break;
            case 'about_me':
                $module['illustration'] = get_media_by_id($module['illustration'], 'full');
                $module['file'] = get_media_by_id($module['file'], 'full');
                break;
                // todo: add data modifications for modules here as a new case
        }
        // -> Laden des Templates basierend auf dem Modultyp und weitergeben der Daten
        get_template_part(
            'frontend/views/modules/' . $module['_type'],
            null,
            [
                'data' => $module
            ]
        );
    }
}
