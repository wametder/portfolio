<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field;

// -> Einstellungen für das Drehrad auf der Work-Seite (Animation rechts)
Container::make('theme_options', 'Work-Rad')
    ->set_page_file('work-wheel')
    ->set_icon('dashicons-image-rotate')
    ->set_page_menu_position(21)
    ->add_fields([
        Field::make('media_gallery', 'work_wheel_frames', 'Animations-Frames')
            ->set_type(['image'])
            ->set_duplicates_allowed(false)
            ->set_help_text('PNG oder WebP mit Transparenz. Reihenfolge per Drag-and-drop = Frame-Reihenfolge. Andere Formate werden im Frontend ignoriert.'),
        Field::make('text', 'work_wheel_frames_per_step', 'Frames pro Projektschritt')
            ->set_attribute('type', 'number')
            ->set_attribute('min', '1')
            ->set_attribute('step', '1')
            ->set_default_value(3)
            ->set_help_text('Wie viele Frames die Animation weiterläuft, wenn das Rad um ein Projekt dreht.'),
    ]);
