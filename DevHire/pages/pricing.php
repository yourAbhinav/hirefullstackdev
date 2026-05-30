<?php
require_once '../includes/helpers.php';

$page_title = "Pricing - DevHire";
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

$paymentPortalTarget = 'pages/payment.php';
$starterTarget = $paymentPortalTarget . '?plan=starter';
$professionalTarget = $paymentPortalTarget . '?plan=professional';
$enterpriseTarget = $paymentPortalTarget . '?plan=enterprise';

include '../includes/header.php';
include '../includes/navbar.php';
?>

<style>
    .pricing-hero {
        padding: 34px 0 8px;
    }

    .pricing-hero .legal-hero-inner {
        width: min(1180px, calc(100% - 32px));
        margin: 0 auto;
    }

    .pricing-hero h1 {
        font-size: clamp(2rem, 4vw, 3.25rem);
        letter-spacing: -0.03em;
        margin-bottom: 10px;
        color: #0f172a;
    }

    .pricing-hero .quick-apply-subtitle {
        color: #64748b;
        font-size: 0.98rem;
    }

    .pricing-grid {
        width: min(1180px, calc(100% - 32px));
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
        padding: 24px 0 72px;
    }

    .pricing-card {
        border-radius: 20px;
        border: 1px solid rgba(59, 130, 246, 0.18);
        background: linear-gradient(135deg, #0f172a 0%, #111827 58%, #1d4ed8 100%);
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.16);
        padding: 24px;
        color: #e2e8f0;
    }

    .pricing-card-featured {
        border-color: rgba(96, 165, 250, 0.28);
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
        transform: translateY(-2px);
    }

    .pricing-card-custom {
        background: linear-gradient(135deg, #0f172a 0%, #111827 55%, #1d4ed8 100%);
        border-color: rgba(59, 130, 246, 0.22);
        color: #e2e8f0;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.16);
    }

    .pricing-card-custom .pricing-badge {
        background: rgba(255, 255, 255, 0.1);
        color: #dbeafe;
    }

    .pricing-card-custom .pricing-price-wrap,
    .pricing-card-custom .pricing-feature-list {
        color: inherit;
    }

    .pricing-card-custom .pricing-card-title,
    .pricing-card-custom .pricing-price {
        color: #fff;
    }

    .pricing-card-custom .pricing-card-subtitle,
    .pricing-card-custom .pricing-period,
    .pricing-card-custom .pricing-feature-item {
        color: rgba(226, 232, 240, 0.78);
    }

    .pricing-card-custom .pricing-feature-icon {
        color: #93c5fd;
    }

    .pricing-card-custom .btn-secondary.pricing-action-btn {
        background: rgba(255, 255, 255, 0.08);
        color: #fff;
        border-color: rgba(255, 255, 255, 0.16);
    }

    .pricing-card-title {
        color: #fff;
        font-size: 1.25rem;
    }

    .pricing-card-subtitle,
    .pricing-period,
    .pricing-feature-item {
        color: rgba(226, 232, 240, 0.78);
    }

    .pricing-price {
        color: #fff;
    }

    .pricing-action-btn {
        border-radius: 12px;
        font-weight: 600;
        padding: 12px 16px;
    }

    .btn-primary.pricing-action-btn {
        background: linear-gradient(135deg, #0f172a, #1d4ed8);
        color: #fff;
    }

    .btn-secondary.pricing-action-btn {
        background: rgba(255, 255, 255, 0.08);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.16);
    }

    @media (max-width: 960px) {
        .pricing-grid {
            grid-template-columns: 1fr;
        }

        .pricing-card-featured {
            transform: none;
        }
    }
</style>

    <!-- Page Header -->
    <section class="legal-hero pricing-hero">
        <div class="legal-hero-inner">
            <h1>Simple, Transparent Pricing</h1>
            <p class="quick-apply-subtitle">Choose the perfect plan for your needs</p>
        </div>
    </section>

    <!-- Pricing Plans -->
    <section class="featured-jobs">
        <div class="pricing-grid">
            
            <!-- Starter Plan -->
            <div class="feature-card pricing-card pricing-card-custom">
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

                <a href="<?= appUrl($starterTarget) ?>" class="btn-secondary pricing-action-btn">
                    Get Started
                </a>
            </div>

            <!-- Professional Plan -->
            <div class="feature-card pricing-card pricing-card-featured pricing-card-custom">
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

                <a href="<?= appUrl($professionalTarget) ?>" class="btn-primary pricing-action-btn">
                    Start Free Trial
                </a>
            </div>

            <!-- Enterprise Plan -->
            <div class="feature-card pricing-card pricing-card-custom">
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

                <a href="<?= appUrl($enterpriseTarget) ?>" class="btn-secondary pricing-action-btn">
                    Contact Sales
                </a>
            </div>
        </div>
    </section>

<?php include '../includes/footer.php'; ?>
