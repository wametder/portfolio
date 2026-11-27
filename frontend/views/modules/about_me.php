<?php
$data = $args['data'];
$legals = get_menu_by_slug(slug: 'legals');
?>

<section data-module="about_me">
    <div class="about-info">
        <img class="illustration" src="<?= esc_url($data['illustration']['src']) ?>" alt="<?= esc_attr($data['illustration']['alt']) ?>">
        <h1 class="title"><?= esc_html($data['title']) ?></h1>
        <p class="lead"><?= esc_html($data['lead']) ?></p>
        <div class="text">
            <?= wpautop($data['rich_text']) ?>
        </div>
    </div>
    <div class="media">
        <?php if (str_starts_with($data['file']['type'], 'video/')): ?>
            <video controls preload="metadata">
                <source
                    src="<?= esc_url(set_url_scheme($data['file']['src'])) ?>"
                    type="<?= esc_attr($data['file']['type']) ?>"
                >
                Dein Browser unterstützt dieses Videoformat nicht.
            </video>
        <?php else: ?>
            <img
                src="<?= esc_url(set_url_scheme($data['file']['src'])) ?>"
                alt="<?= esc_attr($data['file']['alt']) ?>"
            >
        <?php endif; ?>
    </div>
    <?php if ($legals): ?>
        <nav class="legal-links" aria-label="Rechtliches">
            <ul>
                <?php foreach ($legals as $item): ?>
                    <li>
                        <a href="<?= esc_url($item['url']) ?>" target="<?= esc_attr($item['target']) ?>"><?= esc_html($item['title']) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    <?php endif; ?>
</section>
