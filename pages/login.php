<?php
require_once '../includes/helpers.php';
startSecureSession();

$page_title = 'Login - DevHire';
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

if (isLoggedIn()) {
    header('Location: ' . appUrl('admin/dashboard.php'));
    exit;
}

$loginError = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<section class="auth-shell">
    <div class="auth-panel auth-panel-copy">
        <span class="eyebrow">Firebase Authentication</span>
        <h1>Sign in to DevHire</h1>
        <p>Secure Google login with persistent sessions, application tracking, and access to the admin dashboard.</p>

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
            <h2>Continue with Google</h2>
            <p>Use the same account across the platform.</p>
        </div>

        <?php if (!empty($loginError)): ?>
            <div class="notice notice-error"><?= htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <button type="button" class="btn-primary btn-block auth-button" id="googleSignInBtn">
            <i class="fab fa-google"></i>
            Sign in with Google
        </button>

        <p class="auth-note">By continuing you agree to the platform terms and privacy policy.</p>
    </div>
</section>

<script src="https://www.gstatic.com/firebasejs/10.13.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.13.0/firebase-auth-compat.js"></script>
<script>
const firebaseConfig = {
  apiKey: "AIzaSyAXU64W3PalTEkDHy0CbYkqsHBZsKH0MY0",
  authDomain: "abhhire-e8807.firebaseapp.com",
  projectId: "abhhire-e8807",
  storageBucket: "abhhire-e8807.firebasestorage.app",
  messagingSenderId: "173557301887",
  appId: "1:173557301887:web:dd10d71b680477c555354a",
  measurementId: "G-5KN443QPP4"
};

firebase.initializeApp(firebaseConfig);
firebase.auth().setPersistence(firebase.auth.Auth.Persistence.LOCAL);

const signInButton = document.getElementById('googleSignInBtn');

async function syncUser(user) {
    const token = await user.getIdToken();

    const response = await fetch('<?= appUrl('auth/login_handler.php') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            mode: 'firebase',
            uid: user.uid,
            name: user.displayName || '',
            email: user.email || '',
            photo: user.photoURL || '',
            provider: user.providerData?.[0]?.providerId || 'google',
            idToken: token
        })
    });

    const result = await response.json();

    if (!response.ok || !result.success) {
        throw new Error(result.message || 'Unable to sync login session.');
    }

    window.location.href = result.redirect || '<?= appUrl('admin/dashboard.php') ?>';
}

async function signInWithGoogle() {
    try {
        signInButton.disabled = true;
        signInButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';

        const provider = new firebase.auth.GoogleAuthProvider();
        const result = await firebase.auth().signInWithPopup(provider);
        await syncUser(result.user);
    } catch (error) {
        if (window.DevHire && typeof window.DevHire.showNotification === 'function') {
            window.DevHire.showNotification(error.message || 'Login failed.', 'error');
        } else {
            alert(error.message || 'Login failed.');
        }
        signInButton.disabled = false;
        signInButton.innerHTML = '<i class="fab fa-google"></i> Sign in with Google';
    }
}

signInButton.addEventListener('click', signInWithGoogle);

firebase.auth().onAuthStateChanged(async (user) => {
    if (!user) {
        return;
    }

    try {
        await syncUser(user);
    } catch (error) {
        signInButton.disabled = false;
        signInButton.innerHTML = '<i class="fab fa-google"></i> Sign in with Google';
    }
});
</script>

<?php include '../includes/footer.php'; ?>
