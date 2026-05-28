<?php
require_once '../includes/helpers.php';

$page_title = "How It Works - DevHire";
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

include '../includes/header.php';
include '../includes/navbar.php';
?>

    <!-- Page Header -->
    <section style="padding: 4rem 2rem; text-align: center; background: rgba(30, 41, 59, 0.3);">
        <div style="max-width: 1400px; margin: 0 auto;">
            <h1>How It Works</h1>
            <p class="quick-apply-subtitle">Get hired in 4 simple steps</p>
        </div>
    </section>

    <!-- Process Timeline -->
    <section class="timeline-section">
        <div style="max-width: 1200px; margin: 0 auto;">
            <div class="timeline" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
                <div class="timeline-item" style="animation: fadeInUp 0.6s ease-out;">
                    <div class="timeline-step">1</div>
                    <h3 class="timeline-title">Create Your Profile</h3>
                    <p class="timeline-description">Sign up and build an impressive profile with your skills, experience, and portfolio links.</p>
                    <div style="margin-top: 1rem;">
                        <i class="fas fa-user-plus" style="font-size: 2rem; color: var(--primary);"></i>
                    </div>
                </div>

                <div class="timeline-item" style="animation: fadeInUp 0.6s ease-out 0.1s both;">
                    <div class="timeline-step">2</div>
                    <h3 class="timeline-title">Apply to Jobs</h3>
                    <p class="timeline-description">Browse our curated list of opportunities and apply to positions that match your skills.</p>
                    <div style="margin-top: 1rem;">
                        <i class="fas fa-paper-plane" style="font-size: 2rem; color: var(--primary);"></i>
                    </div>
                </div>

                <div class="timeline-item" style="animation: fadeInUp 0.6s ease-out 0.2s both;">
                    <div class="timeline-step">3</div>
                    <h3 class="timeline-title">Interview Process</h3>
                    <p class="timeline-description">Selected candidates go through technical and HR interviews with our partner companies.</p>
                    <div style="margin-top: 1rem;">
                        <i class="fas fa-video" style="font-size: 2rem; color: var(--primary);"></i>
                    </div>
                </div>

                <div class="timeline-item" style="animation: fadeInUp 0.6s ease-out 0.3s both;">
                    <div class="timeline-step">4</div>
                    <h3 class="timeline-title">Get Hired</h3>
                    <p class="timeline-description">Receive your offer and start your exciting new role with a top-tier company.</p>
                    <div style="margin-top: 1rem;">
                        <i class="fas fa-trophy" style="font-size: 2rem; color: var(--primary);"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Detailed Breakdown -->
    <section style="padding: 4rem 2rem; max-width: 1200px; margin: 0 auto;">
        <div style="background: rgba(30, 41, 59, 0.3); border: 1px solid rgba(124, 58, 237, 0.2); border-radius: 1rem; padding: 3rem;">
            <h2 style="margin-bottom: 2rem; text-align: center;">What We Offer</h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                <div>
                    <h4 style="margin-bottom: 1rem;">For Job Seekers</h4>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.8rem;">
                        <li style="display: flex; align-items: center; gap: 0.8rem; color: var(--text-secondary);">
                            <i class="fas fa-check-circle" style="color: var(--primary);"></i>
                            Access to exclusive job listings
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.8rem; color: var(--text-secondary);">
                            <i class="fas fa-check-circle" style="color: var(--primary);"></i>
                            Career guidance and mentorship
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.8rem; color: var(--text-secondary);">
                            <i class="fas fa-check-circle" style="color: var(--primary);"></i>
                            Interview preparation resources
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.8rem; color: var(--text-secondary);">
                            <i class="fas fa-check-circle" style="color: var(--primary);"></i>
                            Flexible working arrangements
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.8rem; color: var(--text-secondary);">
                            <i class="fas fa-check-circle" style="color: var(--primary);"></i>
                            Competitive compensation packages
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 style="margin-bottom: 1rem;">For Companies</h4>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.8rem;">
                        <li style="display: flex; align-items: center; gap: 0.8rem; color: var(--text-secondary);">
                            <i class="fas fa-check-circle" style="color: var(--secondary);"></i>
                            Access to vetted talent
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.8rem; color: var(--text-secondary);">
                            <i class="fas fa-check-circle" style="color: var(--secondary);"></i>
                            Streamlined hiring process
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.8rem; color: var(--text-secondary);">
                            <i class="fas fa-check-circle" style="color: var(--secondary);"></i>
                            Reduced time-to-hire
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.8rem; color: var(--text-secondary);">
                            <i class="fas fa-check-circle" style="color: var(--secondary);"></i>
                            Cost-effective recruitment
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.8rem; color: var(--text-secondary);">
                            <i class="fas fa-check-circle" style="color: var(--secondary);"></i>
                            Dedicated support team
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 style="margin-bottom: 1rem;">Why Choose DevHire</h4>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.8rem;">
                        <li style="display: flex; align-items: center; gap: 0.8rem; color: var(--text-secondary);">
                            <i class="fas fa-check-circle" style="color: var(--accent);"></i>
                            100% transparent process
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.8rem; color: var(--text-secondary);">
                            <i class="fas fa-check-circle" style="color: var(--accent);"></i>
                            No hidden fees
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.8rem; color: var(--text-secondary);">
                            <i class="fas fa-check-circle" style="color: var(--accent);"></i>
                            24/7 customer support
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.8rem; color: var(--text-secondary);">
                            <i class="fas fa-check-circle" style="color: var(--accent);"></i>
                            Community-driven platform
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.8rem; color: var(--text-secondary);">
                            <i class="fas fa-check-circle" style="color: var(--accent);"></i>
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
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 2rem;">
                <a href="<?= appUrl('pages/register.php') ?>" class="btn-primary">Create Account</a>
                <a href="<?= appUrl('pages/jobs.php') ?>" class="btn-secondary">Browse Jobs</a>
            </div>
        </div>
    </section>

<?php include '../includes/footer.php'; ?>
