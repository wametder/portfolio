<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

Container::make('post_meta', 'Projekt')
    ->where('post_type', '=', 'project')
    ->add_fields([
        Field:: make('image', 'preview', 'Vorschaubild') -> set_required(true),
        Field::make('file', 'image', 'Bild / Video')
            ->set_type(['image', 'video']),
        Field::make('text', 'youtube_url', 'YouTube-Video')
            ->set_attribute('type', 'url')
            ->set_help_text('Optional: YouTube-URL einfügen'),
        Field::make('complex', 'additional_images', 'Weitere Bilder')
            ->add_fields('image', 'Bild', [
                Field::make('image', 'image', 'Bild')->set_required(true),
            ])
            ->set_layout('tabbed-vertical'),
        Field:: make('image', 'illustration', 'Illustration') -> set_required(true),
        Field:: make('textarea', 'lead', 'Lead') -> set_required(true),
        Field:: make('rich_text', 'text', 'Text') -> set_required(true) ->set_help_text('Beschreibung des Projekts'),
    ]);