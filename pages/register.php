<?php
require_once '../includes/helpers.php';
startSecureSession();

$page_title = 'Register - DevHire';
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

include '../includes/header.php';
include '../includes/navbar.php';

$registerErrors = $_SESSION['errors'] ?? [];
$registerError = $_SESSION['error'] ?? '';
$registerSuccess = $_SESSION['success'] ?? '';
unset($_SESSION['errors'], $_SESSION['error'], $_SESSION['success']);
?>

    <!-- Register Section -->
    <section class="register-shell">
        <div class="register-container">
            <div class="register-card">
                <h2 class="register-title">Create Account</h2>
                <p class="register-subtitle">Join thousands of developers and companies</p>

                <?php if (!empty($registerSuccess)): ?>
                    <div class="notice notice-success register-notice"><?= htmlspecialchars($registerSuccess, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <?php if (!empty($registerError)): ?>
                    <div class="notice notice-error register-notice"><?= htmlspecialchars($registerError, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <?php if (!empty($registerErrors)): ?>
                    <div class="notice notice-error register-notice">
                        <?php foreach ($registerErrors as $registerMessage): ?>
                            <div><?= htmlspecialchars($registerMessage, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= appUrl('auth/register_handler.php') ?>">
                    <?= csrfField() ?>
                    <div class="form-group register-form-group">
                        <label for="fullName">Full Name</label>
                        <input type="text" id="fullName" name="fullName" placeholder="John Doe" required>
                    </div>

                    <div class="form-group register-form-group">
                        <label for="regEmail">Email Address</label>
                        <input type="email" id="regEmail" name="email" placeholder="you@example.com" required>
                    </div>

                    <div class="form-group register-form-group">
                        <label for="regPassword">Password</label>
                        <input type="password" id="regPassword" name="password" placeholder="••••••••" required>
                    </div>

                    <div class="form-group register-form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="••••••••" required>
                    </div>

                    <div class="form-group register-form-group">
                        <label for="accountType">I am a</label>
                        <select id="accountType" name="accountType" required>
                            <option value="">Select account type</option>
                            <option value="developer">Developer</option>
                            <option value="company">Company</option>
                        </select>
                    </div>

                    <div class="register-terms-row">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms" class="register-terms-label">I agree to the <a href="<?= appUrl('pages/terms.php') ?>" class="register-terms-link">Terms of Service</a></label>
                    </div>

                    <button type="submit" class="btn-primary register-submit-btn">
                        Create Account
                    </button>
                </form>

                <div class="register-footer">
                    <p class="register-footer-copy">Already have an account?</p>
                    <a href="<?= appUrl('pages/login.php') ?>" class="register-footer-link">Sign In</a>
                </div>
            </div>
        </div>
    </section>

<?php include '../includes/footer.php'; ?>
