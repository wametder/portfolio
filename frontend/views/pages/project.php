<?php
$project = get_project_by_id(get_the_ID());
?>

<main data-page="project">
    <div class="project-info">
        <img class="illustration" src="<?= $project ['illustration']['src'] ?>" alt="<?= $project ['illustration']['alt'] ?>">
        <h1 class="title" ><?= $project['title'] ?></h1>
        <p class="lead"><?= $project['lead'] ?></p>
        <div class="text">
            <?= wpautop($project['text']) ?>
        </div>
    </div>
    <div class="media">
    <?php if (str_starts_with($project['image']['type'], 'video/')): ?>
        <video controls preload="metadata">
            <source
                src="<?= esc_url(set_url_scheme($project['image']['src'])) ?>"
                type="<?= esc_attr($project['image']['type']) ?>"
            >
            Dein Browser unterstützt dieses Videoformat nicht.
        </video>
    <?php else: ?>
        <img
            src="<?= esc_url(set_url_scheme($project['image']['src'])) ?>"
            alt="<?= esc_attr($project['image']['alt']) ?>"
        >
    <?php endif; ?>
    </div>
</main>
