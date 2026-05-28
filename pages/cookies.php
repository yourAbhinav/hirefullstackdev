<?php
/**
 * Cookie Policy Page
 */
require_once '../includes/helpers.php';

$page_title = "Cookie Policy - DevHire";
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

include '../includes/header.php';
include '../includes/navbar.php';
?>

    <!-- Page Header -->
    <section style="padding: 4rem 2rem; text-align: center; background: rgba(30, 41, 59, 0.3);">
        <div style="max-width: 1400px; margin: 0 auto;">
            <h1>Cookie Policy</h1>
            <p class="quick-apply-subtitle">Last updated: <?php echo date('F Y'); ?></p>
        </div>
    </section>

    <!-- Content -->
    <section class="featured-jobs">
        <div style="max-width: 900px; margin: 0 auto;">
            <div style="background: rgba(30, 41, 59, 0.3); border: 1px solid rgba(124, 58, 237, 0.2); border-radius: 1rem; padding: 3rem;">
                
                <h2 style="margin-top: 2rem; margin-bottom: 1rem;">What Are Cookies?</h2>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    Cookies are small files that are stored on your device (computer, mobile phone, tablet etc.) 
                    and contain information about your browsing activity and preferences.
                </p>

                <h2 style="margin-top: 2rem; margin-bottom: 1rem;">How We Use Cookies</h2>
                <p style="color: var(--text-secondary); margin-bottom: 1rem;">We use cookies for several purposes:</p>
                <ul style="color: var(--text-secondary); margin-left: 2rem; margin-bottom: 1.5rem;">
                    <li><strong>Session Management:</strong> To keep you logged in to your account</li>
                    <li><strong>Preference Storage:</strong> To remember your preferences and settings</li>
                    <li><strong>Analytics:</strong> To understand how users interact with our website</li>
                    <li><strong>Security:</strong> To protect against fraudulent activity</li>
                </ul>

                <h2 style="margin-top: 2rem; margin-bottom: 1rem;">Types of Cookies We Use</h2>
                
                <h3 style="margin-top: 1.5rem; margin-bottom: 0.8rem; color: var(--text-primary);">Essential Cookies</h3>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    These cookies are necessary for the website to function properly, including authentication and security cookies.
                </p>

                <h3 style="margin-top: 1.5rem; margin-bottom: 0.8rem; color: var(--text-primary);">Performance Cookies</h3>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    These cookies help us understand how visitors interact with our website by collecting and reporting information.
                </p>

                <h3 style="margin-top: 1.5rem; margin-bottom: 0.8rem; color: var(--text-primary);">Preference Cookies</h3>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    These cookies remember your choices to provide a personalized experience.
                </p>

                <h2 style="margin-top: 2rem; margin-bottom: 1rem;">Third-Party Cookies</h2>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    We may use third-party services that set cookies on your device for analytics, 
                    advertising, and other purposes as described in their privacy policies.
                </p>

                <h2 style="margin-top: 2rem; margin-bottom: 1rem;">Managing Cookies</h2>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    You can control and/or delete cookies as you wish. For details, 
                    visit <a href="https://www.aboutcookies.org" style="color: var(--primary);">aboutcookies.org</a>.
                </p>

                <h2 style="margin-top: 2rem; margin-bottom: 1rem;">Questions?</h2>
                <p style="color: var(--text-secondary);">
                    If you have any questions about our cookie policy, please contact us at cookies@devhire.com
                </p>

            </div>
        </div>
    </section>

<?php include '../includes/footer.php'; ?>
