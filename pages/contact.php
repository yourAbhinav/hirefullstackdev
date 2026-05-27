<?php
$page_title = "Contact Us - DevHire";
$css_path = "/DevHire/assets/css/style.css";
$js_path = "/DevHire/assets/js/main.js";

include '../includes/header.php';
include '../includes/navbar.php';
?>

    <!-- Page Header -->
    <section style="padding: 4rem 2rem; text-align: center; background: rgba(30, 41, 59, 0.3);">
        <div style="max-width: 1400px; margin: 0 auto;">
            <h1>Get in Touch</h1>
            <p class="quick-apply-subtitle">We'd love to hear from you. Send us a message!</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="featured-jobs">
        <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 4rem;">
            
            <!-- Contact Info -->
            <div>
                <h2 style="margin-bottom: 2rem;">Contact Information</h2>

                <div style="display: flex; gap: 1.5rem; margin-bottom: 2rem;">
                    <div style="width: 50px; height: 50px; background: rgba(124, 58, 237, 0.1); border: 2px solid rgba(124, 58, 237, 0.3); border-radius: 0.8rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-map-marker-alt" style="color: var(--primary); font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 0.3rem;">Address</h4>
                        <p style="color: var(--text-secondary);">123 Tech Street<br>San Francisco, CA 94102<br>United States</p>
                    </div>
                </div>

                <div style="display: flex; gap: 1.5rem; margin-bottom: 2rem;">
                    <div style="width: 50px; height: 50px; background: rgba(124, 58, 237, 0.1); border: 2px solid rgba(124, 58, 237, 0.3); border-radius: 0.8rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-phone" style="color: var(--primary); font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 0.3rem;">Phone</h4>
                        <p style="color: var(--text-secondary);">+1 (234) 567-8900<br>Mon - Fri, 9AM - 6PM PST</p>
                    </div>
                </div>

                <div style="display: flex; gap: 1.5rem;">
                    <div style="width: 50px; height: 50px; background: rgba(124, 58, 237, 0.1); border: 2px solid rgba(124, 58, 237, 0.3); border-radius: 0.8rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-envelope" style="color: var(--primary); font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 0.3rem;">Email</h4>
                        <p style="color: var(--text-secondary);">info@devhire.com<br>support@devhire.com</p>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div>
                <form method="POST" action="/DevHire/handlers/contact_handler.php" style="background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(10px); border: 1px solid rgba(124, 58, 237, 0.2); border-radius: 1rem; padding: 2rem;">
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="contactName">Full Name</label>
                        <input type="text" id="contactName" name="name" placeholder="Your name" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="contactEmail">Email Address</label>
                        <input type="email" id="contactEmail" name="email" placeholder="your@email.com" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" placeholder="How can we help?" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="contactMessage">Message</label>
                        <textarea id="contactMessage" name="message" placeholder="Your message..." required style="resize: vertical; min-height: 150px; padding: 0.8rem; background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: 0.5rem; color: var(--text-primary); font-family: inherit;"></textarea>
                    </div>

                    <button type="submit" class="btn-submit" style="width: 100%;">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>
        </div>
    </section>

<?php include '../includes/footer.php'; ?>
