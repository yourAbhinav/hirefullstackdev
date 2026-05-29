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
    <section class="legal-hero">
        <div class="legal-hero-inner">
            <h1>Cookie Policy</h1>
            <p class="quick-apply-subtitle">Last updated: <?php echo date('F Y'); ?></p>
        </div>
    </section>

    <!-- Content -->
    <section class="featured-jobs">
        <div class="legal-content-wrap">
            <div class="legal-content-card">
                
                <h2 class="legal-section-title">What Are Cookies?</h2>
                <p class="legal-paragraph">
                    Cookies are small files that are stored on your device (computer, mobile phone, tablet etc.) 
                    and contain information about your browsing activity and preferences.
                </p>

                <h2 class="legal-section-title">How We Use Cookies</h2>
                <p class="legal-paragraph legal-paragraph-tight">We use cookies for several purposes:</p>
                <ul class="legal-list">
                    <li><strong>Session Management:</strong> To keep you logged in to your account</li>
                    <li><strong>Preference Storage:</strong> To remember your preferences and settings</li>
                    <li><strong>Analytics:</strong> To understand how users interact with our website</li>
                    <li><strong>Security:</strong> To protect against fraudulent activity</li>
                </ul>

                <h2 class="legal-section-title">Types of Cookies We Use</h2>
                
                <h3 class="legal-subtitle">Essential Cookies</h3>
                <p class="legal-paragraph">
                    These cookies are necessary for the website to function properly, including authentication and security cookies.
                </p>

                <h3 class="legal-subtitle">Performance Cookies</h3>
                <p class="legal-paragraph">
                    These cookies help us understand how visitors interact with our website by collecting and reporting information.
                </p>

                <h3 class="legal-subtitle">Preference Cookies</h3>
                <p class="legal-paragraph">
                    These cookies remember your choices to provide a personalized experience.
                </p>

                <h2 class="legal-section-title">Third-Party Cookies</h2>
                <p class="legal-paragraph">
                    We may use third-party services that set cookies on your device for analytics, 
                    advertising, and other purposes as described in their privacy policies.
                </p>

                <h2 class="legal-section-title">Managing Cookies</h2>
                <p class="legal-paragraph">
                    You can control and/or delete cookies as you wish. For details, 
                    visit <a href="https://www.aboutcookies.org" class="legal-link">aboutcookies.org</a>.
                </p>

                <h2 class="legal-section-title">Questions?</h2>
                <p class="legal-paragraph legal-paragraph-last">
                    If you have any questions about our cookie policy, please contact us at cookies@devhire.com
                </p>

            </div>
        </div>
    </section>

<?php include '../includes/footer.php'; ?>
