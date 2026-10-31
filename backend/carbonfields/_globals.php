<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field;

// -> Inhaltsfeld-Konfiguration der globalen Einstellungen des Themes
Container::make('theme_options', 'Globales')
    ->add_fields([
        Field::make('file', 'logo', 'Logo')
            ->set_type(['image', 'video'])
            ->set_required(true),
        Field::make('image', 'illuface', 'Illustration_Gesicht'),
        // todo: Globale Inhaltsfelder hier ergänzen
    ]);
