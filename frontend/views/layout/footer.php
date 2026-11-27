<?php
$illuface = get_globals_illuface();
$band = !empty($illuface['src']) ? $illuface['src'] : '';
?>

<footer
    data-layout="footer"
    <?php if ($band): ?>style="--footer-band: url('<?= esc_url($band) ?>')"<?php endif; ?>
>
    <p class="copyright">&copy;Nicolas Egger</p>
</footer>
