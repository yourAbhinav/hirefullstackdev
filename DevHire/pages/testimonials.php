<?php
require_once '../includes/helpers.php';
require_once '../includes/seo_content.php';

$page_title = 'Testimonials - DevHire';
$page_description = 'Read testimonials and success stories from developers, hiring teams, and talent partners using DevHire.';
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');
$testimonials = devhire_testimonials();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<section class="page-hero">
    <div class="page-hero-inner">
        <span class="eyebrow">Testimonials</span>
        <h1>Success stories that build trust</h1>
        <p class="quick-apply-subtitle">See how developers and hiring teams describe their experience with DevHire.</p>
    </div>
</section>

<section class="stats-section">
    <div class="section-title">
        <h2>Platform Signals</h2>
        <p>Reasons users trust the experience</p>
    </div>
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-number">Trusted</div><div class="stat-label">Professional experience for candidates and employers</div></div>
        <div class="stat-card"><div class="stat-number">Relevant</div><div class="stat-label">Role matches aligned to real technical skills</div></div>
        <div class="stat-card"><div class="stat-number">Transparent</div><div class="stat-label">Clear expectations and better hiring clarity</div></div>
        <div class="stat-card"><div class="stat-number">Growth</div><div class="stat-label">Opportunities that support long-term career development</div></div>
    </div>
</section>

<section class="testimonials-section">
    <div class="section-title">
        <h2>Developer and employer feedback</h2>
        <p>What users say after working with the platform</p>
    </div>
    <div class="testimonials-grid">
        <?php foreach ($testimonials as $testimonial): ?>
            <div class="testimonial-card">
                <div class="testimonial-stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="testimonial-quote">"<?= htmlspecialchars($testimonial['quote'], ENT_QUOTES, 'UTF-8') ?>"</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar"><?= htmlspecialchars(strtoupper(substr($testimonial['name'], 0, 2)), ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="testimonial-info">
                        <h4><?= htmlspecialchars($testimonial['name'], ENT_QUOTES, 'UTF-8') ?></h4>
                        <p class="testimonial-role"><?= htmlspecialchars($testimonial['role'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="cta-section">
    <div class="cta-content">
        <h2>Want to be next?</h2>
        <p>Join the platform and start exploring better developer opportunities today.</p>
        <div class="hero-buttons">
            <a href="<?= appUrl('pages/register.php') ?>" class="btn-primary">Create Account</a>
            <a href="<?= appUrl('pages/jobs.php') ?>" class="btn-secondary">Browse Jobs</a>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
