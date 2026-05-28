<?php
require_once '../includes/helpers.php';

$page_title = 'Developers - DevHire';
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

include '../includes/header.php';
include '../includes/navbar.php';
?>

<section class="page-hero">
    <div class="page-hero-inner">
        <span class="eyebrow">Developer Directory</span>
        <h1>Verified Talent Pool</h1>
        <p class="quick-apply-subtitle">Public developer browsing is being prepared. Companies can manage jobs and applicants from the company panel.</p>
        <div class="hero-buttons">
            <a href="<?= appUrl('pages/jobs.php') ?>" class="btn-primary">Browse Jobs</a>
            <a href="<?= appUrl('pages/contact.php') ?>" class="btn-secondary">Contact Us</a>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
