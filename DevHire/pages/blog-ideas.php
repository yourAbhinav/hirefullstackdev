<?php
require_once '../includes/helpers.php';
require_once '../includes/seo_content.php';

$page_title = 'Blog Content Ideas - DevHire';
$page_description = 'Explore 100 SEO-friendly blog titles, keyword targets, and category structures for a developer hiring website.';
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');
$blog = devhire_blog_content();
$titleChunks = array_chunk($blog['titles'], 10);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<style>
    .blog-grid { display: grid; gap: 18px; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }
    .blog-card { background: #fff; border: 1px solid rgba(148, 163, 184, 0.24); border-radius: 20px; padding: 22px; box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06); }
    .blog-card h3 { margin-bottom: 12px; color: #0f172a; }
    .blog-card ul { margin: 0; padding-left: 18px; color: #475569; line-height: 1.7; }
    .keyword-list { display: flex; flex-wrap: wrap; gap: 10px; }
    .keyword-pill { padding: 8px 12px; border-radius: 999px; background: #eff6ff; color: #1d4ed8; font-weight: 600; font-size: 0.88rem; }
</style>

<section class="page-hero">
    <div class="page-hero-inner">
        <span class="eyebrow">Blog Strategy</span>
        <h1>SEO blog content ideas for developer hiring</h1>
        <p class="quick-apply-subtitle">Use these topics to build authority, attract search traffic, and support software developer recruitment campaigns.</p>
    </div>
</section>

<section class="featured-jobs">
    <div class="section-title">
        <h2>Target Keywords</h2>
        <p>High-intent phrases to build search visibility</p>
    </div>
    <div class="keyword-list">
        <?php foreach ($blog['keywords'] as $keyword): ?>
            <span class="keyword-pill"><?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?></span>
        <?php endforeach; ?>
    </div>
</section>

<section class="featured-jobs">
    <div class="section-title">
        <h2>Category Structure</h2>
        <p>Content clusters for a strong internal linking strategy</p>
    </div>
    <div class="blog-grid">
        <?php foreach ($blog['categories'] as $category => $topics): ?>
            <div class="blog-card">
                <h3><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></h3>
                <ul>
                    <?php foreach ($topics as $topic): ?>
                        <li><?= htmlspecialchars($topic, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="featured-jobs">
    <div class="section-title">
        <h2>100 Blog Post Titles</h2>
        <p>Ready-to-use title ideas grouped by technology focus</p>
    </div>
    <div class="blog-grid">
        <?php foreach ($blog['topics'] as $index => $topic): ?>
            <div class="blog-card">
                <h3><?= htmlspecialchars($topic['label'], ENT_QUOTES, 'UTF-8') ?></h3>
                <ul>
                    <?php foreach ($titleChunks[$index] as $title): ?>
                        <li><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="cta-section">
    <div class="cta-content">
        <h2>Need help building an SEO content strategy?</h2>
        <p>Use these ideas as a starting point for blog posts, landing pages, and hiring guides.</p>
        <div class="hero-buttons">
            <a href="<?= appUrl('pages/contact.php') ?>" class="btn-primary">Contact Us</a>
            <a href="<?= appUrl('pages/jobs.php') ?>" class="btn-secondary">Browse Jobs</a>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
