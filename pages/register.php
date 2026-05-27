<?php
$page_title = "Register - DevHire";
$css_path = "/DevHire/assets/css/style.css";
$js_path = "/DevHire/assets/js/main.js";

include '../includes/header.php';
include '../includes/navbar.php';
?>

    <!-- Register Section -->
    <section style="min-height: 80vh; display: flex; align-items: center; padding: 4rem 2rem;">
        <div style="width: 100%; max-width: 500px; margin: 0 auto;">
            <div style="background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(10px); border: 1px solid rgba(124, 58, 237, 0.2); border-radius: 1rem; padding: 3rem;">
                <h2 style="margin-bottom: 0.5rem; text-align: center;">Create Account</h2>
                <p style="text-align: center; color: var(--text-secondary); margin-bottom: 2rem;">Join thousands of developers and companies</p>

                <form method="POST" action="/DevHire/auth/register_handler.php">
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
                        <label for="terms" style="cursor: pointer; margin: 0;">I agree to the <a href="#" style="color: var(--primary); text-decoration: none;">Terms of Service</a></label>
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; padding: 0.9rem; font-size: 1rem;">
                        Create Account
                    </button>
                </form>

                <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-light);">
                    <p style="color: var(--text-secondary); margin-bottom: 1rem;">Already have an account?</p>
                    <a href="/DevHire/pages/login.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Sign In</a>
                </div>
            </div>
        </div>
    </section>

<?php include '../includes/footer.php'; ?>
