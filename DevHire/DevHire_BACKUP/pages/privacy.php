<?php
/**
 * Privacy Policy Page
 */
require_once '../includes/helpers.php';

$page_title = "Privacy Policy - DevHire";
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

include '../includes/header.php';
include '../includes/navbar.php';
?>

    <!-- Page Header -->
    <section class="legal-hero">
        <div class="legal-hero-inner">
            <h1>Privacy Policy</h1>
            <p class="quick-apply-subtitle">Last updated: <?php echo date('F Y'); ?></p>
        </div>
    </section>

    <!-- Content -->
    <section class="featured-jobs">
        <div class="legal-content-wrap">
            <div class="legal-content-card">
                
                <h2 class="legal-section-title">1. Information We Collect</h2>
                <p class="legal-paragraph">
                    We collect information you provide directly, such as when you create an account, submit an application, or contact us. 
                    This includes your name, email address, phone number, resume, and professional information.
                </p>

                <h2 class="legal-section-title">2. How We Use Your Information</h2>
                <p class="legal-paragraph">
                    We use the information we collect to provide, maintain, and improve our services, process your applications, 
                    and communicate with you about your account and our services.
                </p>

                <h2 class="legal-section-title">3. Information Sharing</h2>
                <p class="legal-paragraph">
                    We do not sell your personal information. We may share your information with companies that you apply to, 
                    in order to process your application.
                </p>

                <h2 class="legal-section-title">4. Security</h2>
                <p class="legal-paragraph">
                    We implement appropriate technical and organizational measures to protect your personal information against 
                    unauthorized access, alteration, disclosure, or destruction.
                </p>

                <h2 class="legal-section-title">5. Your Rights</h2>
                <p class="legal-paragraph">
                    You have the right to access, correct, or delete your personal information. 
                    Contact us at privacy@devhire.com for any requests.
                </p>

                <h2 class="legal-section-title">6. Changes to This Policy</h2>
                <p class="legal-paragraph">
                    We may update this privacy policy from time to time. We will notify you of any changes by 
                    posting the new policy on this page.
                </p>

                <h2 class="legal-section-title">7. Contact Us</h2>
                <p class="legal-paragraph legal-paragraph-last">
                    If you have any questions about this privacy policy, please contact us at privacy@devhire.com
                </p>

            </div>
        </div>
    </section>

<?php include '../includes/footer.php'; ?>
