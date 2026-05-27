<?php

require_once '../includes/helpers.php';
startSecureSession();

$_SESSION = [];

if (ini_get('session.use_cookies')) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Signing out - DevHire</title>
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

		if (typeof firebase !== 'undefined' && !firebase.apps.length) {
			firebase.initializeApp(firebaseConfig);
			firebase.auth().signOut().finally(() => {
				window.location.href = '<?= appUrl('index.php') ?>';
			});
		} else {
			window.location.href = '<?= appUrl('index.php') ?>';
		}
	</script>
</head>
<body style="background:#0f172a;color:#f8fafc;font-family:Inter,sans-serif;display:grid;place-items:center;min-height:100vh;">
	<div>Signing out...</div>
</body>
</html>