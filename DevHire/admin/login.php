<?php
require_once '../config/db.php';
require_once '../includes/admin_helpers.php';

$siteName = getSiteName();

// Redirect if already logged in as admin
if (isAdminLoggedIn()) {
    header('Location: ' . appUrl('admin/dashboard.php'));
    exit;
}

$page_title = 'Admin Login - ' . $siteName;
$loginError = $_SESSION['admin_error'] ?? '';
$googleError = $_SESSION['google_error'] ?? '';
$requestSuccess = '';
unset($_SESSION['admin_error']);
unset($_SESSION['google_error']);

// Handle secure admin access request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['form_action'] ?? '') === 'request_admin_access')) {
    if (trim((string) ($_POST['website'] ?? '')) !== '') {
        $requestSuccess = 'Your request was sent. A super admin must approve it before you can sign in.';
    } elseif (!verifyCsrf($_POST['csrf_token'] ?? null)) {
            $loginError = 'Security check failed. Please refresh and try again.';
    } elseif ((string) ($_POST['request_password'] ?? '') !== (string) ($_POST['request_password_confirm'] ?? '')) {
            $loginError = 'Password confirmation does not match.';
    } else {
        $requestResult = submitAdminAccessRequest(
            $conn,
            (string) ($_POST['request_name'] ?? ''),
            (string) ($_POST['request_email'] ?? ''),
            (string) ($_POST['request_password'] ?? ''),
            (string) ($_POST['request_note'] ?? '')
        );

        if ($requestResult['success']) {
            $requestSuccess = $requestResult['message'];
        } else {
                $loginError = $requestResult['message'];
        }
    }
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['form_action'] ?? '') !== 'request_admin_access')) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($email) || empty($password)) {
        $loginError = 'Please enter both email and password';
    } elseif (in_array($password, ['admin123', 'Admin@123'], true)) {
        $loginError = 'Default passwords are disabled. Use your assigned secure password or Google sign-in.';
    } else {
        $result = adminLogin($conn, $email, $password, $remember);
        
        if ($result['success']) {
            header('Location: ' . appUrl('admin/dashboard.php'));
            exit;
        } else {
            $loginError = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://www.gstatic.com/firebasejs/10.13.0/firebase-app-compat.js"></script>
    <script defer src="https://www.gstatic.com/firebasejs/10.13.0/firebase-auth-compat.js"></script>
    <script defer src="<?= appUrl('assets/js/firebase-config.js') ?>"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            display: flex;
            max-width: 1000px;
            width: 100%;
            min-height: 600px;
        }

        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            padding: 60px 50px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-left h1 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .login-left p {
            font-size: 18px;
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 40px;
        }

        .login-left .stats {
            display: flex;
            gap: 30px;
            margin-top: 40px;
        }

        .login-left .stat-item {
            text-align: center;
        }

        .login-left .stat-number {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .login-left .stat-label {
            font-size: 14px;
            opacity: 0.8;
        }

        .login-right {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-right h2 {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 10px;
        }

        .login-right p {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4F46E5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .form-group input::placeholder {
            color: #999;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .remember-forgot label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 14px;
            color: #666;
        }

        .remember-forgot input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .remember-forgot a {
            color: #4F46E5;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .remember-forgot a:hover {
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 20px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .google-btn {
            width: 100%;
            padding: 14px;
            background: white;
            color: #333;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .google-btn:hover {
            background: #f8f9fa;
            border-color: #4F46E5;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .google-btn:active {
            transform: translateY(0);
        }

        .google-btn img {
            width: 20px;
            height: 20px;
        }

        .google-btn.loading {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
            color: #999;
            font-size: 14px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        .divider span {
            padding: 0 15px;
        }

        .error-message {
            background: #FEE2E2;
            color: #DC2626;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-message i {
            font-size: 18px;
        }

        .back-link {
            text-align: center;
            color: #666;
            font-size: 14px;
        }

        .back-link a {
            color: #4F46E5;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .success-message {
            background: #DCFCE7;
            color: #166534;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .request-admin-btn {
            width: 100%;
            padding: 14px;
            background: #0f172a;
            color: #e2e8f0;
            border: 2px solid #334155;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
        }

        .request-admin-btn:hover {
            border-color: #4F46E5;
            color: #fff;
        }

        .request-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.75);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .request-modal.is-open {
            display: flex;
        }

        .request-modal-card {
            width: min(520px, 100%);
            background: #fff;
            border-radius: 16px;
            padding: 28px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .request-modal-card h3 {
            color: #1a1a2e;
            margin-bottom: 8px;
        }

        .request-modal-card p {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .request-modal-close {
            float: right;
            border: 0;
            background: transparent;
            font-size: 22px;
            cursor: pointer;
            color: #64748b;
        }

        .password-hint {
            display: block;
            color: #64748b;
            font-size: 12px;
            margin-top: 6px;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                min-height: auto;
            }

            .login-left {
                padding: 40px 30px;
            }

            .login-left h1 {
                font-size: 32px;
            }

            .login-right {
                padding: 40px 30px;
            }

            .login-left .stats {
                flex-direction: column;
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <h1><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?> Admin Dashboard</h1>
            <p>Complete control over your hiring platform. Manage users, applications, jobs, and platform settings from one powerful interface.</p>
            
            <div class="stats">
                <div class="stat-item">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Monitoring</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">100%</div>
                    <div class="stat-label">Control</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">SSL</div>
                    <div class="stat-label">Secured</div>
                </div>
            </div>
        </div>
        
        <div class="login-right">
            <h2><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?> Admin Login</h2>
            <p>Sign in to access your admin dashboard</p>
            
            <?php if ($loginError): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($loginError) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($googleError): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($googleError) ?>
                </div>
            <?php endif; ?>

            <?php if ($requestSuccess): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($requestSuccess) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="adminLoginForm">
                <input type="hidden" name="form_action" value="admin_login">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="name@company.com" required autocomplete="username">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                </div>
                
                <div class="remember-forgot">
                    <label>
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>
                    <a href="../index.php">Back to Home</a>
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>
            
            <div class="divider">
                <span>Or</span>
            </div>
            
            <button type="button" class="google-btn" id="googleSignInBtn">
                <img src="https://www.google.com/favicon.ico" alt="Google">
                Sign in with Google
            </button>

            <button type="button" class="request-admin-btn" id="openRequestAdminModal">
                <i class="fas fa-user-shield"></i> Request Admin Account
            </button>
        </div>
    </div>

    <div class="request-modal" id="requestAdminModal" aria-hidden="true">
        <div class="request-modal-card">
            <button type="button" class="request-modal-close" id="closeRequestAdminModal" aria-label="Close">&times;</button>
            <h3>Request Admin Access</h3>
            <p>Submit a secure request. A super admin will receive an email to approve or reject your account.</p>
            <form method="POST" action="">
                <input type="hidden" name="form_action" value="request_admin_access">
                <?= csrfField() ?>
                <input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;" aria-hidden="true">

                <div class="form-group">
                    <label for="request_name">Full Name</label>
                    <input type="text" id="request_name" name="request_name" required minlength="2">
                </div>
                <div class="form-group">
                    <label for="request_email">Work Email</label>
                    <input type="email" id="request_email" name="request_email" required>
                </div>
                <div class="form-group">
                    <label for="request_password">Secure Password</label>
                    <input type="password" id="request_password" name="request_password" required minlength="12">
                    <small class="password-hint">Minimum 12 characters with uppercase, lowercase, and a number.</small>
                </div>
                <div class="form-group">
                    <label for="request_password_confirm">Confirm Password</label>
                    <input type="password" id="request_password_confirm" name="request_password_confirm" required minlength="12">
                </div>
                <div class="form-group">
                    <label for="request_note">Reason (optional)</label>
                    <input type="text" id="request_note" name="request_note" maxlength="255" placeholder="Why do you need admin access?">
                </div>
                <button type="submit" class="login-btn">
                    <i class="fas fa-paper-plane"></i> Submit for Approval
                </button>
            </form>
        </div>
    </div>

    <script>
        const adminGoogleHandlerUrl = <?= json_encode(appUrl('auth/admin_google_handler.php'), JSON_UNESCAPED_SLASHES) ?>;
        const adminDashboardUrl = <?= json_encode(appUrl('admin/dashboard.php'), JSON_UNESCAPED_SLASHES) ?>;

        function showLoginError(message) {
            document.querySelectorAll('.error-message').forEach((node) => node.remove());
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + message;
            const loginForm = document.getElementById('adminLoginForm');
            if (loginForm && loginForm.parentNode) {
                loginForm.parentNode.insertBefore(errorDiv, loginForm);
            }
        }

        function googleAuthErrorMessage(error) {
            const code = error && error.code ? error.code : '';
            if (code === 'auth/popup-closed-by-user' || code === 'auth/cancelled-popup-request') {
                return 'Google sign-in was cancelled.';
            }
            if (code === 'auth/popup-blocked') {
                return 'Popup blocked. Allow popups for this site and try again.';
            }
            if (code === 'auth/unauthorized-domain') {
                return 'Add localhost to Firebase Authentication authorized domains.';
            }
            return (error && error.message) ? error.message : 'Google sign-in failed. Please try again.';
        }

        function resetGoogleButton(btn) {
            btn.classList.remove('loading');
            btn.disabled = false;
            btn.innerHTML = '<img src="https://www.google.com/favicon.ico" alt="Google"> Sign in with Google';
        }

        async function syncAdminGoogleSession(user) {
            const idToken = await user.getIdToken(true);
            const controller = new AbortController();
            const timeoutId = window.setTimeout(() => controller.abort(), 20000);

            try {
                const response = await fetch(adminGoogleHandlerUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ idToken }),
                    signal: controller.signal
                });

                const data = await response.json().catch(() => ({}));

                if (response.ok && data.success) {
                    window.location.assign(adminDashboardUrl);
                    return;
                }

                throw new Error(data.message || 'Google sign-in was not authorized for this admin account.');
            } finally {
                window.clearTimeout(timeoutId);
            }
        }

        function waitForFirebase(callback, timeout = 8000) {
            const start = Date.now();
            const check = () => {
                const ready = typeof window.firebase !== 'undefined'
                    && window.DevHireFirebase
                    && typeof window.DevHireFirebase.signInWithGoogle === 'function';

                if (ready) {
                    callback();
                } else if (Date.now() - start < timeout) {
                    window.setTimeout(check, 50);
                }
            };
            check();
        }

        waitForFirebase(() => {
            const devHireFirebase = window.DevHireFirebase;
            const signInButton = document.getElementById('googleSignInBtn');
            if (!signInButton) {
                return;
            }

            void devHireFirebase.setFirebasePersistence();

            signInButton.addEventListener('click', async function () {
                const btn = this;
                btn.classList.add('loading');
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';

                try {
                    const result = await devHireFirebase.signInWithGoogle();
                    await syncAdminGoogleSession(result.user);
                } catch (error) {
                    console.error('Google Sign-In Error:', error);
                    showLoginError(googleAuthErrorMessage(error));
                    resetGoogleButton(btn);
                }
            });
        });

        const requestModal = document.getElementById('requestAdminModal');
        const openRequestModalBtn = document.getElementById('openRequestAdminModal');
        const closeRequestModalBtn = document.getElementById('closeRequestAdminModal');

        function openRequestModal() {
            requestModal.classList.add('is-open');
            requestModal.setAttribute('aria-hidden', 'false');
        }

        function closeRequestModal() {
            requestModal.classList.remove('is-open');
            requestModal.setAttribute('aria-hidden', 'true');
        }

        if (openRequestModalBtn) {
            openRequestModalBtn.addEventListener('click', openRequestModal);
        }
        if (closeRequestModalBtn) {
            closeRequestModalBtn.addEventListener('click', closeRequestModal);
        }
        if (requestModal) {
            requestModal.addEventListener('click', (event) => {
                if (event.target === requestModal) {
                    closeRequestModal();
                }
            });
        }

        const requestForm = requestModal ? requestModal.querySelector('form') : null;
        if (requestForm) {
            requestForm.addEventListener('submit', (event) => {
                const password = document.getElementById('request_password').value;
                const confirm = document.getElementById('request_password_confirm').value;
                if (password !== confirm) {
                    event.preventDefault();
                    alert('Passwords do not match.');
                }
            });
        }
    </script>
</body>
</html>