<?php
require_once '../includes/helpers.php';

$page_title = "How It Works - DevHire";
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

include '../includes/header.php';
include '../includes/navbar.php';
?>

    <!-- Page Header -->
    <section class="legal-hero">
        <div class="legal-hero-inner">
            <h1>How It Works</h1>
            <p class="quick-apply-subtitle">Get hired in 4 simple steps</p>
        </div>
    </section>

    <!-- Process Timeline -->
    <section class="timeline-section">
        <div class="how-wrap">
            <div class="timeline how-timeline-grid">
                <div class="timeline-item how-step-item">
                    <div class="timeline-step">1</div>
                    <h3 class="timeline-title">Create Your Profile</h3>
                    <p class="timeline-description">Sign up and build an impressive profile with your skills, experience, and portfolio links.</p>
                    <div class="how-step-icon-wrap">
                        <i class="fas fa-user-plus how-step-icon"></i>
                    </div>
                </div>

                <div class="timeline-item how-step-item how-step-item-delay-1">
                    <div class="timeline-step">2</div>
                    <h3 class="timeline-title">Apply to Jobs</h3>
                    <p class="timeline-description">Browse our curated list of opportunities and apply to positions that match your skills.</p>
                    <div class="how-step-icon-wrap">
                        <i class="fas fa-paper-plane how-step-icon"></i>
                    </div>
                </div>

                <div class="timeline-item how-step-item how-step-item-delay-2">
                    <div class="timeline-step">3</div>
                    <h3 class="timeline-title">Interview Process</h3>
                    <p class="timeline-description">Selected candidates go through technical and HR interviews with our partner companies.</p>
                    <div class="how-step-icon-wrap">
                        <i class="fas fa-video how-step-icon"></i>
                    </div>
                </div>

                <div class="timeline-item how-step-item how-step-item-delay-3">
                    <div class="timeline-step">4</div>
                    <h3 class="timeline-title">Get Hired</h3>
                    <p class="timeline-description">Receive your offer and start your exciting new role with a top-tier company.</p>
                    <div class="how-step-icon-wrap">
                        <i class="fas fa-trophy how-step-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Detailed Breakdown -->
    <section class="how-offer-shell">
        <div class="how-offer-card">
            <h2 class="how-offer-title">What We Offer</h2>

            <div class="how-offer-grid">
                <div>
                    <h4 class="how-offer-heading">For Job Seekers</h4>
                    <ul class="how-offer-list">
                        <li class="how-offer-item">
                            <i class="fas fa-check-circle how-offer-icon how-offer-icon-primary"></i>
                            Access to exclusive job listings
                        </li>
                        <li class="how-offer-item">
                            <i class="fas fa-check-circle how-offer-icon how-offer-icon-primary"></i>
                            Career guidance and mentorship
                        </li>
                        <li class="how-offer-item">
                            <i class="fas fa-check-circle how-offer-icon how-offer-icon-primary"></i>
                            Interview preparation resources
                        </li>
                        <li class="how-offer-item">
                            <i class="fas fa-check-circle how-offer-icon how-offer-icon-primary"></i>
                            Flexible working arrangements
                        </li>
                        <li class="how-offer-item">
                            <i class="fas fa-check-circle how-offer-icon how-offer-icon-primary"></i>
                            Competitive compensation packages
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="how-offer-heading">For Companies</h4>
                    <ul class="how-offer-list">
                        <li class="how-offer-item">
                            <i class="fas fa-check-circle how-offer-icon how-offer-icon-secondary"></i>
                            Access to vetted talent
                        </li>
                        <li class="how-offer-item">
                            <i class="fas fa-check-circle how-offer-icon how-offer-icon-secondary"></i>
                            Streamlined hiring process
                        </li>
                        <li class="how-offer-item">
                            <i class="fas fa-check-circle how-offer-icon how-offer-icon-secondary"></i>
                            Reduced time-to-hire
                        </li>
                        <li class="how-offer-item">
                            <i class="fas fa-check-circle how-offer-icon how-offer-icon-secondary"></i>
                            Cost-effective recruitment
                        </li>
                        <li class="how-offer-item">
                            <i class="fas fa-check-circle how-offer-icon how-offer-icon-secondary"></i>
                            Dedicated support team
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="how-offer-heading">Why Choose DevHire</h4>
                    <ul class="how-offer-list">
                        <li class="how-offer-item">
                            <i class="fas fa-check-circle how-offer-icon how-offer-icon-accent"></i>
                            100% transparent process
                        </li>
                        <li class="how-offer-item">
                            <i class="fas fa-check-circle how-offer-icon how-offer-icon-accent"></i>
                            No hidden fees
                        </li>
                        <li class="how-offer-item">
                            <i class="fas fa-check-circle how-offer-icon how-offer-icon-accent"></i>
                            24/7 customer support
                        </li>
                        <li class="how-offer-item">
                            <i class="fas fa-check-circle how-offer-icon how-offer-icon-accent"></i>
                            Community-driven platform
                        </li>
                        <li class="how-offer-item">
                            <i class="fas fa-check-circle how-offer-icon how-offer-icon-accent"></i>
                            Continuous innovation
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div class="cta-content">
            <h2>Ready to Get Started?</h2>
            <p>Join thousands of developers and companies finding success on DevHire</p>
            <div class="how-cta-actions">
                <a href="<?= appUrl('pages/register.php') ?>" class="btn-primary">Create Account</a>
                <a href="<?= appUrl('pages/jobs.php') ?>" class="btn-secondary">Browse Jobs</a>
            </div>
        </div>
    </section>

<?php include '../includes/footer.php'; ?>
