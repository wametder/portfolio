<?php
$menu = get_menu_by_slug( slug: 'legals');
?>

<footer data-layout="footer">
    <nav class="nav-container">
        <ul class="nav">
            <?php foreach ($menu as $item) : ?>
                <li class="nav-item">
                    <a href="<?= esc_url($item['url']) ?>" target="<?= esc_attr($item['target']) ?>">
                        <?= esc_html($item['title']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul
</footer>
