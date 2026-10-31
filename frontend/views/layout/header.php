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
    <?php if ($logo && $logo['type'] === 'image') : ?>
        <img class="logo" src="<?= esc_url($logo['src']) ?>" alt="<?= esc_attr($logo['alt']) ?>">
    <?php elseif ($logo && strpos($logo['type'], 'video/') === 0) : ?>
        <video class="logo" autoplay muted loop playsinline aria-label="<?= esc_attr($logo['alt']) ?>">
            <source src="<?= esc_url($logo['src']) ?>" type="<?= esc_attr($logo['type']) ?>">
        </video>
    <?php endif; ?>
</header>
