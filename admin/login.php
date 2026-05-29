<?php
require_once '../config/db.php';
require_once '../includes/admin_helpers.php';

// Redirect if already logged in as admin
if (isAdminLoggedIn()) {
    header('Location: ' . appUrl('admin/dashboard.php'));
    exit;
}

$page_title = 'Admin Login - DevHire';
$loginError = $_SESSION['admin_error'] ?? '';
$googleError = $_SESSION['google_error'] ?? '';
unset($_SESSION['admin_error']);
unset($_SESSION['google_error']);

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($email) || empty($password)) {
        $loginError = 'Please enter both email and password';
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
    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-auth-compat.js"></script>
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
            <h1>Admin Dashboard</h1>
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
            <h2>Admin Login</h2>
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
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="admin@devhire.com" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
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
        </div>
    </div>

    <script>
        // Firebase configuration
        const firebaseConfig = {
            apiKey: '<?= getenv('FIREBASE_API_KEY') ?: 'AIzaSyD-7hW5l6k7j8m9n0p1q2r3s4t5u6v7w8x9y0z' ?>',
            authDomain: '<?= getenv('FIREBASE_AUTH_DOMAIN') ?: 'abhhire-e8807.firebaseapp.com' ?>',
            projectId: '<?= getenv('FIREBASE_PROJECT_ID') ?: 'abhhire-e8807' ?>',
            storageBucket: '<?= getenv('FIREBASE_STORAGE_BUCKET') ?: 'abhhire-e8807.appspot.com' ?>',
            messagingSenderId: '<?= getenv('FIREBASE_MESSAGING_SENDER_ID') ?: '123456789012' ?>',
            appId: '<?= getenv('FIREBASE_APP_ID') ?: '1:123456789012:web:abcdef123456' ?>'
        };

        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);
        const auth = firebase.auth();

        // Google Sign-In
        document.getElementById('googleSignInBtn').addEventListener('click', async function() {
            const btn = this;
            btn.classList.add('loading');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';

            try {
                const provider = new firebase.auth.GoogleAuthProvider();
                const result = await auth.signInWithPopup(provider);
                const idToken = await result.user.getIdToken();

                // Send to server for verification
                const response = await fetch('../auth/admin_google_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ idToken: idToken })
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = '../admin/dashboard.php';
                } else {
                    // Show error message
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'error-message';
                    errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.message;
                    
                    // Remove any existing error messages
                    const existingErrors = document.querySelectorAll('.error-message');
                    existingErrors.forEach(e => e.remove());
                    
                    // Insert error message before the form
                    const form = document.querySelector('form');
                    form.parentNode.insertBefore(errorDiv, form);
                    
                    btn.classList.remove('loading');
                    btn.disabled = false;
                    btn.innerHTML = '<img src="https://www.google.com/favicon.ico" alt="Google"> Sign in with Google';
                }
            } catch (error) {
                console.error('Google Sign-In Error:', error);
                
                // Show error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Google sign-in failed. Please try again.';
                
                // Remove any existing error messages
                const existingErrors = document.querySelectorAll('.error-message');
                existingErrors.forEach(e => e.remove());
                
                // Insert error message before the form
                const form = document.querySelector('form');
                form.parentNode.insertBefore(errorDiv, form);
                
                btn.classList.remove('loading');
                btn.disabled = false;
                btn.innerHTML = '<img src="https://www.google.com/favicon.ico" alt="Google"> Sign in with Google';
            }
        });

        // Handle auth state changes
        auth.onAuthStateChanged(function(user) {
            if (user) {
                // User is signed in, but we still need server verification
                // So we don't redirect automatically
            }
        });
    </script>
</body>
</html>