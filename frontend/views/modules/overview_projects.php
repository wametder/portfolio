<?php
$data = $args['data'];
$illuface = get_globals_illuface();
js_log($data);
?>

<section data-module="overview_projects">
    <img class="illuface-background" src="<?= esc_url($illuface['src']) ?>" alt="" aria-hidden="true">
    <h2 class="title"><?= esc_html($data['title']) ?></h2>
    <div class="projects">
        <?php foreach ($data['selection'] as $project_id): ?>
            <?php
                $project = get_project_by_id($project_id);
            ?>
            <a class="project" href="<?= esc_url($project['link']) ?>" target="_self">
                <span class="image">
                    <img class="preview" src="<?= esc_url($project['preview']['src']) ?>" alt="<?= esc_attr($project['preview']['alt']) ?>">
                    <img class="illustration" src="<?= esc_url($project['illustration']['src']) ?>" alt="<?= esc_attr($project['illustration']['alt']) ?>">
                </span>
                <p class="project-title"><?= esc_html($project['title']) ?></p>
            </a>
        <?php endforeach; ?>
    </div>
</section>
