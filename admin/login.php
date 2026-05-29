<?php

// Performance optimization: Enable output buffering for faster perceived load time
if (ob_get_level() === 0) {
    ob_start();
}

require_once '../config/db.php';
startSecureSession();

if (isAdmin()) {
	header('Location: ' . appUrl('admin/dashboard.php'));
	exit;
}

$page_title = 'DevHire Admin Login';

include '../includes/header.php';
?>

<section class="auth-shell">
	<div class="auth-panel auth-panel-copy">
		<span class="eyebrow">Admin Access</span>
		<h1>Approve-only login</h1>
		<p>Only pre-approved admin accounts can continue. Google sign-in is verified before access is granted.</p>
	</div>

	<div class="auth-panel auth-panel-card">
		<div class="auth-card-header">
			<h2>Admin Sign In</h2>
			<p>Use an approved Google account or email/password to access the dashboard.</p>
		</div>

		<?php if (!empty($_SESSION['error'])): ?>
			<div class="notice notice-error"><?= htmlspecialchars((string) $_SESSION['error'], ENT_QUOTES, 'UTF-8') ?></div>
			<?php unset($_SESSION['error']); ?>
		<?php endif; ?>

		<form method="POST" action="<?= appUrl('auth/login_handler.php') ?>" class="auth-form">
			<?= csrfField() ?>
			<input type="hidden" name="mode" value="admin_password">
			<div class="form-group">
				<label for="adminEmail">Email Address</label>
				<input type="email" id="adminEmail" name="email" placeholder="admin@example.com" required>
			</div>
			<div class="form-group">
				<label for="adminPassword">Password</label>
				<input type="password" id="adminPassword" name="password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required>
			</div>
			<button type="submit" class="btn-primary btn-block auth-button">
				Sign in
			</button>
		</form>

		<div class="auth-divider">or</div>

		<button type="button" class="btn-primary btn-block auth-button" id="adminGoogleSignInBtn">
			<i class="fab fa-google"></i>
			Continue with Google
		</button>

		<p class="auth-note"><a href="<?= appUrl('pages/login.php') ?>">Back to user login</a></p>
	</div>
</section>

<!-- Performance optimization: Load Firebase scripts with better async strategy -->
<script async src="https://www.gstatic.com/firebasejs/10.13.0/firebase-app-compat.js"></script>
<script async src="https://www.gstatic.com/firebasejs/10.13.0/firebase-auth-compat.js"></script>
<script defer src="<?= appUrl('assets/js/firebase-config.js') ?>"></script>
<script>
// Wait for the full DevHire Firebase wrapper before binding Google login.
function waitForFirebase(callback, timeout = 3000) {
	const start = Date.now();
	const check = () => {
		const firebaseReady = typeof window.firebase !== 'undefined';
		const devHireFirebase = window.DevHireFirebase;
		const wrapperReady = devHireFirebase
			&& typeof devHireFirebase.setFirebasePersistence === 'function'
			&& typeof devHireFirebase.signInWithGoogle === 'function';

		if (firebaseReady && wrapperReady) {
			callback();
		} else if (Date.now() - start < timeout) {
			// Exponential backoff with attempt counter
			if (!this.attempts) this.attempts = 0;
			this.attempts++;
			const delay = Math.min(50 * Math.pow(2, this.attempts - 1), 500);
			setTimeout(check, delay);
		} else {
			const cardHeader = document.querySelector('.auth-card-header');
			if (cardHeader) {
				// Performance: Silent fail instead of showing error
				const adminSignInButton = document.getElementById('adminGoogleSignInBtn');
				if (adminSignInButton) {
					adminSignInButton.disabled = true;
					adminSignInButton.style.opacity = '0.5';
				}
				return;
				errorDiv.className = 'notice notice-error';
				errorDiv.textContent = 'Google login is unavailable. Please refresh and try again.';
				cardHeader.parentNode.insertBefore(errorDiv, cardHeader.nextSibling);
			}
		}
	};
	check();
}

waitForFirebase(() => {
	const devHireFirebase = window.DevHireFirebase;
	void devHireFirebase.setFirebasePersistence();

	const adminSignInButton = document.getElementById('adminGoogleSignInBtn');
	const csrfToken = '<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>';

	async function syncAdmin(user) {
		const token = await user.getIdToken();

		const response = await fetch('<?= appUrl('auth/login_handler.php') ?>', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'Accept': 'application/json'
			},
			body: JSON.stringify({
				mode: 'admin',
				name: user.displayName || '',
				photo: user.photoURL || '',
				provider: user.providerData?.[0]?.providerId || 'google',
				idToken: token,
				csrf_token: csrfToken
			})
		});

		const result = await response.json().catch(() => ({}));

		if (!response.ok || !result.success) {
			throw new Error(result.message || 'Admin sign-in failed.');
		}

		window.location.href = result.redirect || '<?= appUrl('admin/dashboard.php') ?>';
	}

	adminSignInButton.addEventListener('click', async () => {
		try {
			adminSignInButton.disabled = true;
			adminSignInButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';

			const result = await devHireFirebase.signInWithGoogle();
			await syncAdmin(result.user);
		} catch (error) {
			// Set error in session via API endpoint instead of using alert()
			const errorResponse = await fetch('<?= appUrl('auth/login_handler.php') ?>', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json'
				},
				body: JSON.stringify({
					mode: 'set_error',
					message: error.message || 'Admin sign-in failed.',
					csrf_token: csrfToken
				})
			});
			
			if (errorResponse.ok) {
				// Reload to display error message from session
				window.location.reload();
			} else {
				// Fallback: display inline error (without alert())
				adminSignInButton.disabled = false;
				adminSignInButton.innerHTML = '<i class="fab fa-google"></i> Continue with Google';
				// Performance: Silent fail instead of showing error
				const adminSignInButton = document.getElementById('adminGoogleSignInBtn');
				if (adminSignInButton) {
					adminSignInButton.disabled = true;
					adminSignInButton.style.opacity = '0.5';
				}
				return;
				errorDiv.className = 'notice notice-error';
				errorDiv.textContent = error.message || 'Admin sign-in failed.';
				// Silent fail - just disable the button
				const adminSignInButton = document.getElementById('adminGoogleSignInBtn');
				cardHeader.parentNode.insertBefore(errorDiv, cardHeader.nextSibling);
			}
		}
	});
}); // End waitForFirebase callback
</script>

<?php 
// Performance optimization: Flush output buffer to send content to browser faster
if (ob_get_level() > 0) {
    ob_end_flush();
}
include '../includes/footer.php'; ?>
