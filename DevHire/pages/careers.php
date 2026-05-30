<?php
require_once '../includes/helpers.php';
require_once '../includes/seo_content.php';

$page_title = 'Careers at DevHire - Join the Team';
$page_description = 'Explore careers at DevHire, our culture, benefits, learning programs, and hiring process.';
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');
$careers = devhire_careers_copy();

$careersImageHtml = renderResponsiveImage(
    'team-culture.jpg',
    'A collaborative engineering team discussing priorities and delivery',
    'careers-feature-image',
    '(max-width: 900px) 100vw, 520px'
);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<section class="page-hero">
    <div class="page-hero-inner">
        <span class="eyebrow">Careers</span>
        <h1>Build the future of developer hiring</h1>
        <p class="quick-apply-subtitle">Work on a platform that helps software professionals find meaningful roles and helps companies hire with confidence.</p>
    </div>
</section>

<section class="featured-jobs">
    <div class="jobs-grid">
        <div class="job-card">
            <?= $careersImageHtml ?>
        </div>
        <div class="job-card">
            <h2 class="job-title">A place for focused builders</h2>
            <p class="feature-description">DevHire teams work on tools that improve developer visibility, hiring quality, and the clarity of every application flow.</p>
            <p class="feature-description">You will help shape the experience for candidates, recruiters, and engineering leaders with practical, measurable product improvements.</p>
            <div class="job-tags">
                <span class="tag">Ownership</span>
                <span class="tag">Quality</span>
                <span class="tag">Impact</span>
            </div>
        </div>
    </div>
</section>

<section class="featured-jobs">
    <div class="section-title">
        <h2>Why Work Here</h2>
        <p>A place where ownership, quality, and practical impact matter</p>
    </div>
    <div class="about-copy-grid" style="display:grid;gap:18px;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));">
        <?php foreach ($careers['why']['paragraphs'] as $paragraph): ?>
            <div class="feature-card">
                <p class="feature-description" style="margin:0;line-height:1.75;">
                    <?= htmlspecialchars($paragraph, ENT_QUOTES, 'UTF-8') ?>
                </p>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="why-choose">
    <div class="section-title">
        <h2>Open Opportunities</h2>
        <p>Representative roles across product, engineering, and growth</p>
    </div>
    <div class="features-grid">
        <?php foreach ($careers['open_roles'] as $role): ?>
            <div class="feature-card">
                <h3 class="feature-title"><?= htmlspecialchars($role['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="feature-description"><?= htmlspecialchars($role['copy'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="timeline-section">
    <div class="section-title">
        <h2>Hiring Process</h2>
        <p>A transparent, structured interview flow</p>
    </div>
    <div class="timeline">
        <?php foreach ($careers['hiring_process'] as $index => $step): ?>
            <div class="timeline-item">
                <div class="timeline-step"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></div>
                <h3 class="timeline-title"><?= htmlspecialchars($step['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="timeline-description"><?= htmlspecialchars($step['copy'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="featured-jobs">
    <div class="section-title">
        <h2>Employee Benefits and Learning Programs</h2>
        <p>Support designed to help people do their best work</p>
    </div>
    <div class="jobs-grid">
        <div class="job-card">
            <div class="job-header"><h3 class="job-title"><?= htmlspecialchars($careers['benefits']['title'], ENT_QUOTES, 'UTF-8') ?></h3></div>
            <ul class="how-offer-list">
                <?php foreach ($careers['benefits']['items'] as $item): ?>
                    <li class="how-offer-item"><i class="fas fa-check-circle how-offer-icon how-offer-icon-primary"></i><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="job-card">
            <div class="job-header"><h3 class="job-title"><?= htmlspecialchars($careers['learning']['title'], ENT_QUOTES, 'UTF-8') ?></h3></div>
            <ul class="how-offer-list">
                <?php foreach ($careers['learning']['items'] as $item): ?>
                    <li class="how-offer-item"><i class="fas fa-check-circle how-offer-icon how-offer-icon-secondary"></i><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>

<section class="stats-section">
    <div class="section-title">
        <h2>Career Development</h2>
        <p>Growth paths that support long-term progress</p>
    </div>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number">Growth</div>
            <div class="stat-label"><?= htmlspecialchars($careers['development']['paragraphs'][0], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-number">Impact</div>
            <div class="stat-label"><?= htmlspecialchars($careers['development']['paragraphs'][1], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="cta-content">
        <h2>Interested in joining DevHire?</h2>
        <p>Explore open opportunities or reach out to our team if you want to learn more about the work.</p>
        <div class="hero-buttons">
            <a href="<?= appUrl('pages/contact.php') ?>" class="btn-primary">Contact Us</a>
            <a href="<?= appUrl('pages/jobs.php') ?>" class="btn-secondary">Browse Jobs</a>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
