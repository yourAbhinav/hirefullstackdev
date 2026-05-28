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
    <section style="min-height: 80vh; display: flex; align-items: center; padding: 4rem 2rem;">
        <div style="width: 100%; max-width: 500px; margin: 0 auto;">
            <div style="background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(10px); border: 1px solid rgba(124, 58, 237, 0.2); border-radius: 1rem; padding: 3rem;">
                <h2 style="margin-bottom: 0.5rem; text-align: center;">Create Account</h2>
                <p style="text-align: center; color: var(--text-secondary); margin-bottom: 2rem;">Join thousands of developers and companies</p>

                <?php if (!empty($registerSuccess)): ?>
                    <div class="notice notice-success" style="margin-bottom: 1rem;"><?= htmlspecialchars($registerSuccess, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <?php if (!empty($registerError)): ?>
                    <div class="notice notice-error" style="margin-bottom: 1rem;"><?= htmlspecialchars($registerError, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <?php if (!empty($registerErrors)): ?>
                    <div class="notice notice-error" style="margin-bottom: 1rem;">
                        <?php foreach ($registerErrors as $registerMessage): ?>
                            <div><?= htmlspecialchars($registerMessage, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= appUrl('auth/register_handler.php') ?>">
                    <?= csrfField() ?>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="fullName">Full Name</label>
                        <input type="text" id="fullName" name="fullName" placeholder="John Doe" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="regEmail">Email Address</label>
                        <input type="email" id="regEmail" name="email" placeholder="you@example.com" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="regPassword">Password</label>
                        <input type="password" id="regPassword" name="password" placeholder="••••••••" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="confirmPassword">Confirm Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="••••••••" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="accountType">I am a</label>
                        <select id="accountType" name="accountType" required>
                            <option value="">Select account type</option>
                            <option value="developer">Developer</option>
                            <option value="company">Company</option>
                        </select>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 2rem;">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms" style="cursor: pointer; margin: 0;">I agree to the <a href="<?= appUrl('pages/terms.php') ?>" style="color: var(--primary); text-decoration: none;">Terms of Service</a></label>
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; padding: 0.9rem; font-size: 1rem;">
                        Create Account
                    </button>
                </form>

                <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-light);">
                    <p style="color: var(--text-secondary); margin-bottom: 1rem;">Already have an account?</p>
                    <a href="<?= appUrl('pages/login.php') ?>" style="color: var(--primary); text-decoration: none; font-weight: 600;">Sign In</a>
                </div>
            </div>
        </div>
    </section>

<?php include '../includes/footer.php'; ?>
