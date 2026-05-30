<?php
require_once '../includes/helpers.php';
startSecureSession();

$page_title = 'Login - DevHire';
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

if (isLoggedIn()) {
    header('Location: ' . appUrl(roleDashboardPath()));
    exit;
}

$loginError = $_SESSION['error'] ?? '';
$loginSuccess = $_SESSION['success'] ?? '';
unset($_SESSION['error']);
unset($_SESSION['success']);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<section class="auth-shell">
    <div class="auth-shell-inner">
        
        <!-- LEFT PANEL: Brand Story -->
        <div class="auth-panel-brand">
            <div class="auth-brand-header">
                <div class="auth-eyebrow">
                    <span class="auth-eyebrow-icon"><i class="fas fa-shield-alt"></i></span>
                    <span>Secure Authentication</span>
                </div>
                <h1 class="auth-headline">Sign in to DevHire</h1>
                <p class="auth-subheadline">Access your dashboard to manage applications, discover opportunities, and connect with top companies.</p>
            </div>

            <div class="auth-features">
                <div class="auth-feature">
                    <div class="auth-feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="auth-feature-content">
                        <div class="auth-feature-title">Protected Sessions</div>
                        <div class="auth-feature-description">Login survives refresh and browser restarts</div>
                    </div>
                </div>
                <div class="auth-feature">
                    <div class="auth-feature-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="auth-feature-content">
                        <div class="auth-feature-title">Unified Identity</div>
                        <div class="auth-feature-description">Profile data syncs automatically with MySQL</div>
                    </div>
                </div>
                <div class="auth-feature">
                    <div class="auth-feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div class="auth-feature-content">
                        <div class="auth-feature-title">Fast Access</div>
                        <div class="auth-feature-description">One click to enter the dashboard and apply flow</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL: Login Form -->
        <div class="auth-panel-form">
            <div class="auth-form-header">
                <h2 class="auth-form-title">Welcome back</h2>
                <p class="auth-form-subtitle">Sign in with email/password or Google to continue</p>
            </div>

            <?php if (!empty($loginSuccess)): ?>
                <div class="auth-notice auth-notice-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?= htmlspecialchars($loginSuccess, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($loginError)): ?>
                <div class="auth-notice auth-notice-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= appUrl('auth/login_handler.php') ?>" class="auth-form">
                <?= csrfField() ?>
                <div class="auth-form-group">
                    <label for="loginEmail" class="auth-form-label">Email address</label>
                    <input type="email" id="loginEmail" name="email" class="auth-form-input" placeholder="you@example.com" required>
                </div>
                <div class="auth-form-group">
                    <label for="loginPassword" class="auth-form-label">Password</label>
                    <input type="password" id="loginPassword" name="password" class="auth-form-input" placeholder="••••••••" required>
                </div>
                <label class="auth-remember">
                    <input type="checkbox" id="rememberMe" name="remember_me" value="1" class="auth-remember-checkbox">
                    <span class="auth-remember-label">Remember me on this device</span>
                </label>
                <button type="submit" class="auth-submit-btn">
                    Sign in
                </button>
            </form>

            <div class="auth-divider">
                <div class="auth-divider-line"></div>
                <span class="auth-divider-text">OR</span>
                <div class="auth-divider-line"></div>
            </div>

            <button type="button" class="auth-google-btn" id="googleSignInBtn">
                <i class="fab fa-google auth-google-icon"></i>
                Continue with Google
            </button>

            <p class="auth-note">
                <a href="<?= appUrl('admin/login.php') ?>">Admin login</a>
            </p>
        </div>

    </div>
</section>

<!-- Firebase scripts are deferred in dependency order so Google login initializes reliably. -->
<script defer src="https://www.gstatic.com/firebasejs/10.13.0/firebase-app-compat.js"></script>
<script defer src="https://www.gstatic.com/firebasejs/10.13.0/firebase-auth-compat.js"></script>
<script defer src="<?= appUrl('assets/js/firebase-config.js') ?>"></script>
<script>
// Wait for the full DevHire Firebase wrapper before binding Google login.
function waitForFirebase(callback, timeout = 5000) {
	const start = Date.now();
	const check = () => {
		const firebaseReady = typeof window.firebase !== 'undefined';
		const devHireFirebase = window.DevHireFirebase;
		const wrapperReady = devHireFirebase
			&& typeof devHireFirebase.getFirebaseAuth === 'function'
			&& typeof devHireFirebase.setFirebasePersistence === 'function'
			&& typeof devHireFirebase.signInWithGoogle === 'function';

		if (firebaseReady && wrapperReady) {
			callback();
		} else if (Date.now() - start < timeout) {
			setTimeout(check, 50);
		} else if (window.DevHire && typeof window.DevHire.showNotification === 'function') {
			window.DevHire.showNotification('Google login is unavailable. Please refresh and try again.', 'error');
		}
	};
	check();
}

waitForFirebase(() => {
	const devHireFirebase = window.DevHireFirebase;
	const firebaseAuth = devHireFirebase.getFirebaseAuth();
	void devHireFirebase.setFirebasePersistence();

const signInButton = document.getElementById('googleSignInBtn');
const rememberMeField = document.getElementById('rememberMe');
	const csrfToken = '<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>';

	async function syncUser(user) {
		const token = await user.getIdToken();

		const response = await fetch('<?= appUrl('auth/login_handler.php') ?>', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'Accept': 'application/json'
			},
			body: JSON.stringify({
				mode: 'google',
				name: user.displayName || '',
				photo: user.photoURL || '',
				provider: user.providerData?.[0]?.providerId || 'google',
				idToken: token,
				csrf_token: csrfToken,
				remember_me: rememberMeField && rememberMeField.checked ? '1' : '0'
			})
		});

		const result = await response.json().catch(() => ({}));

		if (!response.ok || !result.success) {
			throw new Error(result.message || 'Unable to sync login session.');
		}

		window.location.href = result.redirect || '<?= appUrl('pages/profile.php') ?>';
	}

	async function signInWithGoogle() {
		try {
			signInButton.disabled = true;
			signInButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';

			const result = await devHireFirebase.signInWithGoogle();
			await syncUser(result.user);
		} catch (error) {
			// Create error notification without using alert()
			const errorMessage = error.message || 'Login failed.';
			
			// Try to display via notification system first
			if (window.DevHire && typeof window.DevHire.showNotification === 'function') {
				window.DevHire.showNotification(errorMessage, 'error');
			} else {
				// Fallback: insert error div into page (not alert())
				const existingError = document.querySelector('.notice-login-error');
				if (existingError) {
					existingError.remove();
				}
				
				const errorDiv = document.createElement('div');
				errorDiv.className = 'auth-notice auth-notice-error';
				errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i><span>' + errorMessage + '</span>';
				
				const formHeader = document.querySelector('.auth-form-header');
				if (formHeader) {
					formHeader.parentNode.insertBefore(errorDiv, formHeader.nextSibling);
				}
			}
			
			signInButton.disabled = false;
			signInButton.innerHTML = '<i class="fab fa-google auth-google-icon"></i> Continue with Google';
		}
	}

	signInButton.addEventListener('click', signInWithGoogle);

	// Do not auto-sync an existing Firebase session from this page.
	// Users must explicitly click sign-in so stale browser auth state
	// cannot trigger unexpected "automatic login" behavior.
}); // End waitForFirebase callback
</script>

<?php include '../includes/footer.php'; ?>
