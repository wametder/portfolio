<!DOCTYPE html>
<html lang="<?php bloginfo('language') ?>">
<head>
    <meta charset="<?php bloginfo('charset') ?>">
    <?php $main_css = get_template_directory() . '/frontend/styles/main.css'; ?>
    <link rel="stylesheet" href="<?= esc_url(get_template_directory_uri() . '/frontend/styles/main.css') ?>?ver=<?= esc_attr(filemtime($main_css)) ?>">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover" />
    <title><?php bloginfo('name') ?></title>
    <?php wp_head(); ?>
</head>
<body>
