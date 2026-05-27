<?php
$page_title = "Pricing - DevHire";
$css_path = "/DevHire/assets/css/style.css";
$js_path = "/DevHire/assets/js/main.js";

include '../includes/header.php';
include '../includes/navbar.php';
?>

    <!-- Page Header -->
    <section style="padding: 4rem 2rem; text-align: center; background: rgba(30, 41, 59, 0.3);">
        <div style="max-width: 1400px; margin: 0 auto;">
            <h1>Simple, Transparent Pricing</h1>
            <p class="quick-apply-subtitle">Choose the perfect plan for your needs</p>
        </div>
    </section>

    <!-- Pricing Plans -->
    <section class="featured-jobs">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; max-width: 1200px; margin: 0 auto;">
            
            <!-- Starter Plan -->
            <div class="feature-card" style="display: flex; flex-direction: column;">
                <h3 style="margin-bottom: 0.5rem;">Starter</h3>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">Perfect for job seekers</p>
                
                <div style="margin: 2rem 0;">
                    <p style="font-size: 2.5rem; font-weight: 900; color: var(--primary); margin-bottom: 0.5rem;">Free</p>
                    <p style="color: var(--text-tertiary);">Forever</p>
                </div>

                <ul style="list-style: none; margin: 2rem 0; flex-grow: 1;">
                    <li style="display: flex; align-items: center; gap: 0.8rem; margin-bottom: 1rem; color: var(--text-secondary);">
                        <i class="fas fa-check" style="color: var(--primary);"></i>
                        Browse all jobs
                    </li>
                    <li style="display: flex; align-items: center; gap: 0.8rem; margin-bottom: 1rem; color: var(--text-secondary);">
                        <i class="fas fa-check" style="color: var(--primary);"></i>
                        Apply to positions
                    </li>
                    <li style="display: flex; align-items: center; gap: 0.8rem; margin-bottom: 1rem; color: var(--text-secondary);">
                        <i class="fas fa-check" style="color: var(--primary);"></i>
                        Save up to 5 jobs
                    </li>
                    <li style="display: flex; align-items: center; gap: 0.8rem; margin-bottom: 1rem; color: var(--text-tertiary);">
                        <i class="fas fa-times" style="color: var(--text-tertiary);"></i>
                        Priority support
                    </li>
                </ul>

                <a href="/DevHire/pages/register.php" class="btn-secondary" style="width: 100%; text-align: center; padding: 0.8rem; text-decoration: none; display: block;">
                    Get Started
                </a>
            </div>

            <!-- Professional Plan -->
            <div class="feature-card" style="display: flex; flex-direction: column; border: 2px solid var(--primary);">
                <div style="background: var(--primary); color: white; padding: 0.5rem 1rem; border-radius: 0.3rem; width: fit-content; font-size: 0.8rem; font-weight: 700; margin-bottom: 1rem;">RECOMMENDED</div>
                
                <h3 style="margin-bottom: 0.5rem;">Professional</h3>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">For active job seekers</p>
                
                <div style="margin: 2rem 0;">
                    <p style="font-size: 2.5rem; font-weight: 900; color: var(--primary); margin-bottom: 0.5rem;">$9<span style="font-size: 1.2rem;">/mo</span></p>
                    <p style="color: var(--text-tertiary);">Billed monthly</p>
                </div>

                <ul style="list-style: none; margin: 2rem 0; flex-grow: 1;">
                    <li style="display: flex; align-items: center; gap: 0.8rem; margin-bottom: 1rem; color: var(--text-secondary);">
                        <i class="fas fa-check" style="color: var(--primary);"></i>
                        All Starter benefits
                    </li>
                    <li style="display: flex; align-items: center; gap: 0.8rem; margin-bottom: 1rem; color: var(--text-secondary);">
                        <i class="fas fa-check" style="color: var(--primary);"></i>
                        Save unlimited jobs
                    </li>
                    <li style="display: flex; align-items: center; gap: 0.8rem; margin-bottom: 1rem; color: var(--text-secondary);">
                        <i class="fas fa-check" style="color: var(--primary);"></i>
                        Priority support
                    </li>
                    <li style="display: flex; align-items: center; gap: 0.8rem; margin-bottom: 1rem; color: var(--text-secondary);">
                        <i class="fas fa-check" style="color: var(--primary);"></i>
                        Job alerts
                    </li>
                </ul>

                <a href="/DevHire/pages/register.php" class="btn-primary" style="width: 100%; text-align: center; padding: 0.8rem; text-decoration: none; display: block;">
                    Start Free Trial
                </a>
            </div>

            <!-- Enterprise Plan -->
            <div class="feature-card" style="display: flex; flex-direction: column;">
                <h3 style="margin-bottom: 0.5rem;">Enterprise</h3>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">For companies & teams</p>
                
                <div style="margin: 2rem 0;">
                    <p style="font-size: 2.5rem; font-weight: 900; color: var(--primary); margin-bottom: 0.5rem;">Custom</p>
                    <p style="color: var(--text-tertiary);">Contact sales</p>
                </div>

                <ul style="list-style: none; margin: 2rem 0; flex-grow: 1;">
                    <li style="display: flex; align-items: center; gap: 0.8rem; margin-bottom: 1rem; color: var(--text-secondary);">
                        <i class="fas fa-check" style="color: var(--primary);"></i>
                        Unlimited listings
                    </li>
                    <li style="display: flex; align-items: center; gap: 0.8rem; margin-bottom: 1rem; color: var(--text-secondary);">
                        <i class="fas fa-check" style="color: var(--primary);"></i>
                        Dedicated account manager
                    </li>
                    <li style="display: flex; align-items: center; gap: 0.8rem; margin-bottom: 1rem; color: var(--text-secondary);">
                        <i class="fas fa-check" style="color: var(--primary);"></i>
                        Premium support
                    </li>
                    <li style="display: flex; align-items: center; gap: 0.8rem; margin-bottom: 1rem; color: var(--text-secondary);">
                        <i class="fas fa-check" style="color: var(--primary);"></i>
                        Custom integrations
                    </li>
                </ul>

                <a href="/DevHire/pages/contact.php" class="btn-secondary" style="width: 100%; text-align: center; padding: 0.8rem; text-decoration: none; display: block;">
                    Contact Sales
                </a>
            </div>
        </div>
    </section>

<?php include '../includes/footer.php'; ?>
