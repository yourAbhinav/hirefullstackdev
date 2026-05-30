<?php
require_once '../includes/helpers.php';

$page_title = "Pricing - DevHire";
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

include '../includes/header.php';
include '../includes/navbar.php';
?>

    <!-- Page Header -->
    <section class="legal-hero">
        <div class="legal-hero-inner">
            <h1>Simple, Transparent Pricing</h1>
            <p class="quick-apply-subtitle">Choose the perfect plan for your needs</p>
        </div>
    </section>

    <!-- Pricing Plans -->
    <section class="featured-jobs">
        <div class="pricing-grid">
            
            <!-- Starter Plan -->
            <div class="feature-card pricing-card">
                <h3 class="pricing-card-title">Starter</h3>
                <p class="pricing-card-subtitle">Perfect for job seekers</p>
                
                <div class="pricing-price-wrap">
                    <p class="pricing-price">Free</p>
                    <p class="pricing-period">Forever</p>
                </div>

                <ul class="pricing-feature-list">
                    <li class="pricing-feature-item">
                        <i class="fas fa-check pricing-feature-icon"></i>
                        Browse all jobs
                    </li>
                    <li class="pricing-feature-item">
                        <i class="fas fa-check pricing-feature-icon"></i>
                        Apply to positions
                    </li>
                    <li class="pricing-feature-item">
                        <i class="fas fa-check pricing-feature-icon"></i>
                        Save up to 5 jobs
                    </li>
                    <li class="pricing-feature-item pricing-feature-item-muted">
                        <i class="fas fa-times pricing-feature-icon pricing-feature-icon-muted"></i>
                        Priority support
                    </li>
                </ul>

                <a href="<?= appUrl('pages/register.php') ?>" class="btn-secondary pricing-action-btn">
                    Get Started
                </a>
            </div>

            <!-- Professional Plan -->
            <div class="feature-card pricing-card pricing-card-featured">
                <div class="pricing-badge">RECOMMENDED</div>
                
                <h3 class="pricing-card-title">Professional</h3>
                <p class="pricing-card-subtitle">For active job seekers</p>
                
                <div class="pricing-price-wrap">
                    <p class="pricing-price">$9<span class="pricing-price-suffix">/mo</span></p>
                    <p class="pricing-period">Billed monthly</p>
                </div>

                <ul class="pricing-feature-list">
                    <li class="pricing-feature-item">
                        <i class="fas fa-check pricing-feature-icon"></i>
                        All Starter benefits
                    </li>
                    <li class="pricing-feature-item">
                        <i class="fas fa-check pricing-feature-icon"></i>
                        Save unlimited jobs
                    </li>
                    <li class="pricing-feature-item">
                        <i class="fas fa-check pricing-feature-icon"></i>
                        Priority support
                    </li>
                    <li class="pricing-feature-item">
                        <i class="fas fa-check pricing-feature-icon"></i>
                        Job alerts
                    </li>
                </ul>

                <a href="<?= appUrl('pages/register.php') ?>" class="btn-primary pricing-action-btn">
                    Start Free Trial
                </a>
            </div>

            <!-- Enterprise Plan -->
            <div class="feature-card pricing-card">
                <h3 class="pricing-card-title">Enterprise</h3>
                <p class="pricing-card-subtitle">For companies & teams</p>
                
                <div class="pricing-price-wrap">
                    <p class="pricing-price">Custom</p>
                    <p class="pricing-period">Contact sales</p>
                </div>

                <ul class="pricing-feature-list">
                    <li class="pricing-feature-item">
                        <i class="fas fa-check pricing-feature-icon"></i>
                        Unlimited listings
                    </li>
                    <li class="pricing-feature-item">
                        <i class="fas fa-check pricing-feature-icon"></i>
                        Dedicated account manager
                    </li>
                    <li class="pricing-feature-item">
                        <i class="fas fa-check pricing-feature-icon"></i>
                        Premium support
                    </li>
                    <li class="pricing-feature-item">
                        <i class="fas fa-check pricing-feature-icon"></i>
                        Custom integrations
                    </li>
                </ul>

                <a href="<?= appUrl('pages/contact.php') ?>" class="btn-secondary pricing-action-btn">
                    Contact Sales
                </a>
            </div>
        </div>
    </section>

<?php include '../includes/footer.php'; ?>
