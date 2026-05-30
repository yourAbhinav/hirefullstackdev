<?php
require_once '../includes/helpers.php';
require_once '../includes/seo_content.php';

$page_title = 'About DevHire - Developer Hiring Platform';
$page_description = 'Learn how DevHire helps companies hire software developers and helps candidates find trusted engineering careers.';
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');
$about = devhire_about_copy();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<section class="page-hero">
    <div class="page-hero-inner">
        <span class="eyebrow">About DevHire</span>
        <h1><?= htmlspecialchars($about['story']['title'], ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="quick-apply-subtitle">A developer hiring platform built to improve trust, visibility, and long-term hiring quality.</p>
    </div>
</section>

<section class="featured-jobs">
    <div class="section-title">
        <h2>Company Story</h2>
        <p>Why DevHire exists and what we are building for the engineering market</p>
    </div>
    <div class="about-copy-grid" style="display:grid;gap:18px;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));">
        <?php foreach ($about['story']['paragraphs'] as $paragraph): ?>
            <div class="feature-card">
                <p class="feature-description" style="margin:0;line-height:1.75;">
                    <?= htmlspecialchars($paragraph, ENT_QUOTES, 'UTF-8') ?>
                </p>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="stats-section">
    <div class="section-title">
        <h2>Mission and Vision</h2>
        <p>What guides the platform as it grows</p>
    </div>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number">Mission</div>
            <div class="stat-label"><?= htmlspecialchars($about['mission'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-number">Vision</div>
            <div class="stat-label"><?= htmlspecialchars($about['vision'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>
</section>

<section class="why-choose">
    <div class="section-title">
        <h2>Core Values</h2>
        <p>The principles that shape our hiring experience</p>
    </div>
    <div class="features-grid">
        <?php foreach ($about['values'] as $value): ?>
            <div class="feature-card">
                <h3 class="feature-title"><?= htmlspecialchars($value['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="feature-description"><?= htmlspecialchars($value['copy'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="timeline-section">
    <div class="section-title">
        <h2>Leadership Philosophy</h2>
        <p>How we think about teams, accountability, and product outcomes</p>
    </div>
    <div class="timeline">
        <?php foreach ($about['leadership']['paragraphs'] as $index => $paragraph): ?>
            <div class="timeline-item">
                <div class="timeline-step"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></div>
                <h3 class="timeline-title"><?= htmlspecialchars($about['leadership']['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="timeline-description"><?= htmlspecialchars($paragraph, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="featured-jobs">
    <div class="section-title">
        <h2>Diversity and Engineering Excellence</h2>
        <p>Building fairer hiring paths and stronger technical teams</p>
    </div>
    <div class="jobs-grid">
        <div class="job-card">
            <div class="job-header"><h3 class="job-title"><?= htmlspecialchars($about['diversity']['title'], ENT_QUOTES, 'UTF-8') ?></h3></div>
            <?php foreach ($about['diversity']['paragraphs'] as $paragraph): ?>
                <p class="feature-description"><?= htmlspecialchars($paragraph, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endforeach; ?>
        </div>
        <div class="job-card">
            <div class="job-header"><h3 class="job-title"><?= htmlspecialchars($about['excellence']['title'], ENT_QUOTES, 'UTF-8') ?></h3></div>
            <ul class="how-offer-list">
                <?php foreach ($about['excellence']['items'] as $item): ?>
                    <li class="how-offer-item"><i class="fas fa-check-circle how-offer-icon how-offer-icon-primary"></i><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="cta-content">
        <h2>Join a hiring platform built on trust</h2>
        <p>Browse active roles, connect with companies, and explore a more transparent hiring experience.</p>
        <div class="hero-buttons">
            <a href="<?= appUrl('pages/jobs.php') ?>" class="btn-primary">Browse Jobs</a>
            <a href="<?= appUrl('pages/contact.php') ?>" class="btn-secondary">Contact Us</a>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
