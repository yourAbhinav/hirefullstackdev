<?php
/**
 * Terms of Service Page
 */
$page_title = "Terms of Service - DevHire";
$css_path = "/DevHire/assets/css/style.css";
$js_path = "/DevHire/assets/js/main.js";

include '../includes/header.php';
include '../includes/navbar.php';
?>

    <!-- Page Header -->
    <section style="padding: 4rem 2rem; text-align: center; background: rgba(30, 41, 59, 0.3);">
        <div style="max-width: 1400px; margin: 0 auto;">
            <h1>Terms of Service</h1>
            <p class="quick-apply-subtitle">Last updated: <?php echo date('F Y'); ?></p>
        </div>
    </section>

    <!-- Content -->
    <section class="featured-jobs">
        <div style="max-width: 900px; margin: 0 auto;">
            <div style="background: rgba(30, 41, 59, 0.3); border: 1px solid rgba(124, 58, 237, 0.2); border-radius: 1rem; padding: 3rem;">
                
                <h2 style="margin-top: 2rem; margin-bottom: 1rem;">1. Acceptance of Terms</h2>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    By accessing and using DevHire, you accept and agree to be bound by the terms and provision of this agreement.
                </p>

                <h2 style="margin-top: 2rem; margin-bottom: 1rem;">2. Use License</h2>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    Permission is granted to temporarily download one copy of the materials (information or software) on DevHire for 
                    personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and 
                    under this license you may not:
                </p>
                <ul style="color: var(--text-secondary); margin-left: 2rem; margin-bottom: 1.5rem;">
                    <li>Modifying or copying the materials</li>
                    <li>Using the materials for any commercial purpose or for any public display</li>
                    <li>Attempting to decompile or reverse engineer any software contained on DevHire</li>
                    <li>Removing any copyright or other proprietary notations from the materials</li>
                </ul>

                <h2 style="margin-top: 2rem; margin-bottom: 1rem;">3. Disclaimer</h2>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    The materials on DevHire are provided on an 'as is' basis. DevHire makes no warranties, expressed or implied, 
                    and hereby disclaims and negates all other warranties including, without limitation, implied warranties or 
                    conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or 
                    other violation of rights.
                </p>

                <h2 style="margin-top: 2rem; margin-bottom: 1rem;">4. Limitations</h2>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    In no event shall DevHire or its suppliers be liable for any damages (including, without limitation, damages for 
                    loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials 
                    on DevHire, even if DevHire or a DevHire authorized representative has been notified orally or in writing of the 
                    possibility of such damage.
                </p>

                <h2 style="margin-top: 2rem; margin-bottom: 1rem;">5. Accuracy of Materials</h2>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    The materials appearing on DevHire could include technical, typographical, or photographic errors. DevHire does 
                    not warrant that any of the materials on our website is accurate, complete, or current. DevHire may make changes 
                    to the materials contained on its website at any time without notice.
                </p>

                <h2 style="margin-top: 2rem; margin-bottom: 1rem;">6. Links</h2>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    DevHire has not reviewed all of the sites linked to its website and is not responsible for the contents of any 
                    such linked site. The inclusion of any link does not imply endorsement by DevHire of the site. Use of any such 
                    linked website is at the user's own risk.
                </p>

                <h2 style="margin-top: 2rem; margin-bottom: 1rem;">7. Modifications</h2>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    DevHire may revise these terms of service for its website at any time without notice. By using this website, you 
                    are agreeing to be bound by the then current version of these terms of service.
                </p>

                <h2 style="margin-top: 2rem; margin-bottom: 1rem;">8. Contact Information</h2>
                <p style="color: var(--text-secondary);">
                    If you have any questions about these Terms of Service, please contact us at terms@devhire.com
                </p>

            </div>
        </div>
    </section>

<?php include '../includes/footer.php'; ?>
