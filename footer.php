<?php
// -> Version = jüngste Änderung im Script-Ordner, damit nach jedem Upload neu geladen wird
$scripts_dir = get_template_directory() . '/frontend/scripts';
$scripts_version = max(array_map('filemtime', glob($scripts_dir . '/*.js') ?: [__FILE__]));
?>
<script src="<?= esc_url(get_template_directory_uri() . '/frontend/scripts/main.js?ver=' . $scripts_version) ?>" type="module"></script>
<?php wp_footer(); ?>
</body>
</html>
