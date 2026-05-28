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
    <section style="padding: 4rem 2rem; text-align: center; background: rgba(30, 41, 59, 0.3);">
        <div style="max-width: 1400px; margin: 0 auto;">
            <h1>Privacy Policy</h1>
            <p class="quick-apply-subtitle">Last updated: <?php echo date('F Y'); ?></p>
        </div>
    </section>

    <!-- Content -->
    <section class="featured-jobs">
        <div style="max-width: 900px; margin: 0 auto;">
            <div style="background: rgba(30, 41, 59, 0.3); border: 1px solid rgba(124, 58, 237, 0.2); border-radius: 1rem; padding: 3rem;">
                
                <h2 style="margin-top: 2rem; margin-bottom: 1rem;">1. Information We Collect</h2>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    We collect information you provide directly, such as when you create an account, submit an application, or contact us. 
                    This includes your name, email address, phone number, resume, and professional information.
                </p>

                <h2 style="margin-top: 2rem; margin-bottom: 1rem;">2. How We Use Your Information</h2>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    We use the information we collect to provide, maintain, and improve our services, process your applications, 
                    and communicate with you about your account and our services.
                </p>

                <h2 style="margin-top: 2rem; margin-bottom: 1rem;">3. Information Sharing</h2>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    We do not sell your personal information. We may share your information with companies that you apply to, 
                    in order to process your application.
                </p>

                <h2 style="margin-top: 2rem; margin-bottom: 1rem;">4. Security</h2>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    We implement appropriate technical and organizational measures to protect your personal information against 
                    unauthorized access, alteration, disclosure, or destruction.
                </p>

                <h2 style="margin-top: 2rem; margin-bottom: 1rem;">5. Your Rights</h2>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    You have the right to access, correct, or delete your personal information. 
                    Contact us at privacy@devhire.com for any requests.
                </p>

                <h2 style="margin-top: 2rem; margin-bottom: 1rem;">6. Changes to This Policy</h2>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    We may update this privacy policy from time to time. We will notify you of any changes by 
                    posting the new policy on this page.
                </p>

                <h2 style="margin-top: 2rem; margin-bottom: 1rem;">7. Contact Us</h2>
                <p style="color: var(--text-secondary);">
                    If you have any questions about this privacy policy, please contact us at privacy@devhire.com
                </p>

            </div>
        </div>
    </section>

<?php include '../includes/footer.php'; ?>
