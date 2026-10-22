<?php
$menu = get_menu_by_slug( slug: 'main');
$logo = get_globals_logo();
?>

<header data-layout="header">
    <nav class="nav-container">
        <ul class="nav">
            <?php foreach ($menu as $item) : ?>
                <li class="nav-item">
                    <a href="<?= esc_url($item['url']) ?>" target="<?= esc_attr($item['target']) ?>">
                        <?= esc_html($item['title']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <img class="logo" src="<?= ($logo['src']) ?>" alt="<?= ($logo['alt']) ?>">
</header>
