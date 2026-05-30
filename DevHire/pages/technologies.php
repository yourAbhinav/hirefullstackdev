<?php
require_once '../includes/helpers.php';
require_once '../includes/seo_content.php';

$page_title = 'Technologies We Hire For - DevHire';
$page_description = 'Explore the technologies, skills, and career opportunities that DevHire supports across frontend, backend, cloud, AI, and more.';
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');
$techSections = devhire_technology_sections();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<style>
    .tech-page-grid { display: grid; gap: 18px; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }
    .tech-detail-card { border-radius: 20px; padding: 24px; background: #fff; border: 1px solid rgba(148, 163, 184, 0.22); box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06); }
    .tech-detail-card h3 { margin-bottom: 10px; color: #0f172a; }
    .tech-detail-label { font-weight: 700; color: #1d4ed8; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.08em; font-size: 0.78rem; }
    .tech-detail-copy { color: #334155; line-height: 1.7; margin: 0 0 12px; }
    .tech-tag-row { display: flex; flex-wrap: wrap; gap: 8px; }
    .tech-tag { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 999px; background: #eff6ff; color: #1d4ed8; font-weight: 600; font-size: 0.88rem; }
</style>

<section class="page-hero">
    <div class="page-hero-inner">
        <span class="eyebrow">Technologies</span>
        <h1>Hire across the full modern software stack</h1>
        <p class="quick-apply-subtitle">From frontend and backend development to cloud, AI, security, design, and quality engineering, DevHire covers the roles modern teams need most.</p>
    </div>
</section>

<section class="featured-jobs">
    <div class="section-title">
        <h2>Technologies We Hire For</h2>
        <p>Each specialty includes a clear hiring overview, skills profile, and growth outlook</p>
    </div>
    <div class="tech-page-grid">
        <?php foreach ($techSections as $section): ?>
            <article class="tech-detail-card">
                <div class="tech-detail-label"><?= htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8') ?></div>
                <h3><?= htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="tech-detail-copy"><strong>Overview:</strong> <?= htmlspecialchars($section['overview'], ENT_QUOTES, 'UTF-8') ?></p>
                <p class="tech-detail-copy"><strong>Career Opportunities:</strong> <?= htmlspecialchars($section['opportunities'], ENT_QUOTES, 'UTF-8') ?></p>
                <p class="tech-detail-copy"><strong>Required Skills:</strong> <?= htmlspecialchars($section['skills'], ENT_QUOTES, 'UTF-8') ?></p>
                <p class="tech-detail-copy"><strong>Industry Demand:</strong> <?= htmlspecialchars($section['demand'], ENT_QUOTES, 'UTF-8') ?></p>
                <p class="tech-detail-copy"><strong>Growth Prospects:</strong> <?= htmlspecialchars($section['growth'], ENT_QUOTES, 'UTF-8') ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="cta-section">
    <div class="cta-content">
        <h2>Looking for a role in a specific stack?</h2>
        <p>Search current opportunities or contact us if you want help matching your experience to the right role.</p>
        <div class="hero-buttons">
            <a href="<?= appUrl('pages/jobs.php') ?>" class="btn-primary">Browse Jobs</a>
            <a href="<?= appUrl('pages/contact.php') ?>" class="btn-secondary">Contact Us</a>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
