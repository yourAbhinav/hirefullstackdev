<?php
require_once '../includes/helpers.php';
require_once '../config/site.php';

startSecureSession();

$page_title = "Contact Us - DevHire";
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

$contactSuccess = getFlash('success');
$contactError = getFlash('error');

include '../includes/header.php';
include '../includes/navbar.php';
?>

    <!-- Page Header -->
    <section class="legal-hero">
        <div class="legal-hero-inner">
            <h1>Get in Touch</h1>
            <p class="quick-apply-subtitle">We'd love to hear from you. Send us a message!</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="featured-jobs">
        <div class="contact-grid">
            
            <!-- Contact Info -->
            <div>
                <h2 class="contact-info-title">Contact Information</h2>

                <div class="contact-info-item">
                    <div class="contact-info-icon-wrap">
                        <i class="fas fa-map-marker-alt contact-info-icon"></i>
                    </div>
                    <div>
                        <h4 class="contact-info-heading">Address</h4>
                        <p class="contact-info-copy"><?= CONTACT_ADDRESS ?></p>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-info-icon-wrap">
                        <i class="fas fa-phone contact-info-icon"></i>
                    </div>
                    <div>
                        <h4 class="contact-info-heading">Phone</h4>
                        <p class="contact-info-copy"><?= CONTACT_PHONE ?><br>Mon - Fri, 9AM - 6PM PST</p>
                    </div>
                </div>

                <div class="contact-info-item contact-info-item-last">
                    <div class="contact-info-icon-wrap">
                        <i class="fas fa-envelope contact-info-icon"></i>
                    </div>
                    <div>
                        <h4 class="contact-info-heading">Email</h4>
                        <p class="contact-info-copy">info@devhire.com<br><?= CONTACT_SUPPORT_EMAIL ?></p>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div>
                <form method="POST" action="<?= appUrl('handlers/contact_handler.php') ?>" class="contact-form-card">
                    <?= csrfField() ?>

                    <?php if (!empty($contactSuccess)): ?>
                        <div class="notice notice-success notice-spaced">
                            <i class="fas fa-check-circle"></i>
                            <p><?= escape($contactSuccess) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($contactError)): ?>
                        <div class="notice notice-error notice-spaced">
                            <i class="fas fa-exclamation-circle"></i>
                            <p><?= escape($contactError) ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="form-group contact-form-group">
                        <label for="contactName">Full Name</label>
                        <input type="text" id="contactName" name="name" placeholder="Your name" required>
                    </div>

                    <div class="form-group contact-form-group">
                        <label for="contactEmail">Email Address</label>
                        <input type="email" id="contactEmail" name="email" placeholder="your@email.com" required>
                    </div>

                    <div class="form-group contact-form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" placeholder="How can we help?" required>
                    </div>

                    <div class="form-group contact-form-group">
                        <label for="contactMessage">Message</label>
                        <textarea id="contactMessage" name="message" placeholder="Your message..." required class="contact-message-area"></textarea>
                    </div>

                    <button type="submit" class="btn-submit btn-block">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>
        </div>
    </section>

<?php include '../includes/footer.php'; ?>
