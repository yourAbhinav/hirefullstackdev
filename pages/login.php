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
    <div class="auth-panel auth-panel-copy">
        <span class="eyebrow">Firebase Authentication</span>
        <h1>Sign in to DevHire</h1>
        <p>Sign in with email/password or Google. Approved admins use the separate admin entrypoint.</p>

        <div class="auth-benefits">
            <div class="auth-benefit">
                <i class="fas fa-shield-alt"></i>
                <div>
                    <strong>Protected sessions</strong>
                    <span>Login survives refresh and browser restarts.</span>
                </div>
            </div>
            <div class="auth-benefit">
                <i class="fas fa-layer-group"></i>
                <div>
                    <strong>Unified identity</strong>
                    <span>Profile data syncs into MySQL automatically.</span>
                </div>
            </div>
            <div class="auth-benefit">
                <i class="fas fa-bolt"></i>
                <div>
                    <strong>Fast access</strong>
                    <span>One click to enter the dashboard and apply flow.</span>
                </div>
            </div>
        </div>
    </div>

    <div class="auth-panel auth-panel-card">
        <div class="auth-card-header">
            <h2>Account Login</h2>
            <p>Developer and company accounts can use email/password or Google.</p>
        </div>

        <?php if (!empty($loginSuccess)): ?>
            <div class="notice notice-success"><?= htmlspecialchars($loginSuccess, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if (!empty($loginError)): ?>
            <div class="notice notice-error"><?= htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= appUrl('auth/login_handler.php') ?>" class="auth-form">
            <?= csrfField() ?>
            <div class="form-group">
                <label for="loginEmail">Email Address</label>
                <input type="email" id="loginEmail" name="email" placeholder="you@example.com" required>
            </div>
            <div class="form-group">
                <label for="loginPassword">Password</label>
                <input type="password" id="loginPassword" name="password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required>
            </div>
            <label class="form-group" style="display:flex; align-items:center; gap:.5rem; flex-direction:row;">
                <input type="checkbox" id="rememberMe" name="remember_me" value="1">
                <span>Remember me on this device</span>
            </label>
            <button type="submit" class="btn-primary btn-block auth-button">
                Sign in
            </button>
        </form>

        <div class="auth-divider">or</div>

        <button type="button" class="btn-primary btn-block auth-button" id="googleSignInBtn">
            <i class="fab fa-google"></i>
            Continue with Google
        </button>

        <p class="auth-note"><a href="<?= appUrl('admin/login.php') ?>">Admins should use the dedicated admin login page.</a></p>
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
            errorDiv.className = 'notice notice-error notice-login-error';
            errorDiv.textContent = errorMessage;
            errorDiv.style.marginBottom = '1rem';
            
            const formContainer = document.querySelector('.auth-card-header');
            if (formContainer) {
                formContainer.parentNode.insertBefore(errorDiv, formContainer.nextSibling);
            }
        }
        
        signInButton.disabled = false;
        signInButton.innerHTML = '<i class="fab fa-google"></i> Sign in with Google';
    }
}

signInButton.addEventListener('click', signInWithGoogle);

// Do not auto-sync an existing Firebase session from this page.
// Users must explicitly click sign-in so stale browser auth state
// cannot trigger unexpected "automatic login" behavior.
}); // End waitForFirebase callback
</script>

<?php include '../includes/footer.php'; ?>
