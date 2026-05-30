<?php
require_once '../includes/helpers.php';
require_once '../includes/seo_content.php';

$page_title = 'FAQ - DevHire';
$page_description = 'Find answers to common questions about DevHire, developer jobs, remote hiring, and engineering careers.';
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');
$faqItems = devhire_faq_items();

$groupedFaq = [];
foreach ($faqItems as $item) {
    $groupedFaq[$item['category']][] = $item;
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<style>
    .faq-category { margin-bottom: 28px; }
    .faq-details { display: grid; gap: 12px; }
    .faq-item { border-radius: 18px; border: 1px solid rgba(148, 163, 184, 0.24); background: #fff; overflow: hidden; }
    .faq-item summary { cursor: pointer; list-style: none; padding: 18px 20px; font-weight: 700; color: #0f172a; }
    .faq-item summary::-webkit-details-marker { display: none; }
    .faq-answer { padding: 0 20px 18px; color: #475569; line-height: 1.75; }
</style>

<section class="page-hero">
    <div class="page-hero-inner">
        <span class="eyebrow">FAQ</span>
        <h1>Frequently asked questions for candidates and employers</h1>
        <p class="quick-apply-subtitle">Clear answers about applications, hiring, remote work, technology roles, and career growth.</p>
    </div>
</section>

<section class="featured-jobs">
    <?php foreach ($groupedFaq as $category => $items): ?>
        <div class="faq-category">
            <div class="section-title">
                <h2><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></h2>
                <p><?= count($items) ?> questions in this topic</p>
            </div>
            <div class="faq-details">
                <?php foreach ($items as $item): ?>
                    <details class="faq-item">
                        <summary><?= htmlspecialchars($item['q'], ENT_QUOTES, 'UTF-8') ?></summary>
                        <div class="faq-answer"><?= htmlspecialchars($item['a'], ENT_QUOTES, 'UTF-8') ?></div>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</section>

<section class="cta-section">
    <div class="cta-content">
        <h2>Still have questions?</h2>
        <p>Send us a message and we’ll help you with the next step.</p>
        <div class="hero-buttons">
            <a href="<?= appUrl('pages/contact.php') ?>" class="btn-primary">Contact Support</a>
            <a href="<?= appUrl('pages/jobs.php') ?>" class="btn-secondary">Browse Jobs</a>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
