<?php
$data = $args['data'];

// -> Projekte aus der manuellen Auswahl laden, unvollständige (ohne Link) überspringen
$projects = [];
foreach ($data['selection'] ?? [] as $project_id) {
    $project = get_project_by_id($project_id);
    if (!$project['link']) {
        continue;
    }
    $projects[] = $project;
}
if (empty($projects)) {
    return;
}

$frames = get_work_wheel_frames('m');
$frames_per_step = get_work_wheel_frames_per_step();
$first = $projects[0];
?>

<section
    data-module="overview_projects"
    class="work-wheel"
    data-frames-per-step="<?= esc_attr($frames_per_step) ?>"
    <?php if ($frames): ?>data-frames="<?= esc_attr(wp_json_encode($frames)) ?>"<?php endif; ?>
>
    <?php if (!empty($data['title'])): ?>
        <h1 class="visually-hidden"><?= esc_html($data['title']) ?></h1>
    <?php endif; ?>

    <a class="work-wheel__preview" href="<?= esc_url($first['link']) ?>">
        <?php if ($first['preview']): ?>
            <img
                class="work-wheel__layer is-current"
                src="<?= esc_url($first['preview']['src']) ?>"
                alt="<?= esc_attr($first['preview']['alt']) ?>"
            >
        <?php endif; ?>
    </a>

    <div class="work-wheel__illustration">
        <?php if ($first['illustration']): ?>
            <img
                class="work-wheel__layer is-current"
                src="<?= esc_url($first['illustration']['src']) ?>"
                alt="<?= esc_attr($first['illustration']['alt']) ?>"
            >
        <?php endif; ?>
    </div>

    <ul class="work-wheel__list" aria-label="<?= esc_attr(($data['title'] ?? '') ?: 'Projekte') ?>">
        <?php foreach ($projects as $index => $project): ?>
            <li
                class="work-wheel__item<?= $index === 0 ? ' work-wheel__item--active' : '' ?>"
                data-index="<?= esc_attr($index) ?>"
                <?php if ($project['preview']): ?>
                    data-preview-src="<?= esc_url($project['preview']['src']) ?>"
                    data-preview-alt="<?= esc_attr($project['preview']['alt']) ?>"
                <?php endif; ?>
                <?php if ($project['illustration']): ?>
                    data-illustration-src="<?= esc_url($project['illustration']['src']) ?>"
                    data-illustration-alt="<?= esc_attr($project['illustration']['alt']) ?>"
                <?php endif; ?>
            >
                <a class="work-wheel__link" href="<?= esc_url($project['link']) ?>"><?= esc_html($project['title']) ?></a>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="work-wheel__animation">
        <?php if ($frames): ?>
            <canvas aria-hidden="true"></canvas>
        <?php endif; ?>
    </div>
</section>
