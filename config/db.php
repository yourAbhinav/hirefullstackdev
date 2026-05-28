<?php

require_once __DIR__ . '/../includes/helpers.php';

// Do not start browser sessions when running from CLI tools (e.g. migration scripts).
if (php_sapi_name() !== 'cli') {
	startSecureSession();
}

// Support env-driven configuration with strict requirements for production.
$isProduction = (getenv('APP_ENV') === 'production' || getenv('APP_ENV') === 'prod');

$host = getenv('DB_HOST') !== false && getenv('DB_HOST') !== '' ? getenv('DB_HOST') : ($isProduction ? null : 'localhost');
$user = getenv('DB_USER') !== false && getenv('DB_USER') !== '' ? getenv('DB_USER') : ($isProduction ? null : 'root');
$password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : ($isProduction ? null : '');
$database = getenv('DB_NAME') !== false && getenv('DB_NAME') !== '' ? getenv('DB_NAME') : ($isProduction ? null : 'devhire');
$port = getenv('DB_PORT') !== false && ctype_digit((string) getenv('DB_PORT')) ? (int) getenv('DB_PORT') : null;

// Production safety: fail if critical env vars are missing
if ($isProduction) {
	$requiredEnvVars = ['DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME'];
	$missing = array_filter($requiredEnvVars, fn($var) => !getenv($var) || getenv($var) === '');
	if (!empty($missing)) {
		throw new RuntimeException('Production deployment missing required environment variables: ' . implode(', ', $missing));
	}
}

// Additional safety check: never allow empty credentials even in local dev if explicitly set
if (!$host || !$database) {
	throw new RuntimeException('Database configuration is incomplete. Please set DB_HOST and DB_NAME environment variables.');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
	if ($port !== null) {
		$conn = new mysqli($host, $user, $password, $database, $port);
	} else {
		$conn = new mysqli($host, $user, $password, $database);
	}

	// ensure utf8mb4 is used without raw query
	$conn->set_charset('utf8mb4');

	// Create logs/error.log eagerly so errors during early bootstrap are captured
	ensureLogPath();

	// attempt remember-me authentication only in web contexts where sessions exist
	if (php_sapi_name() !== 'cli') {
		authenticateFromRememberMe($conn);
	}
} catch (Throwable $exception) {
	// Centralized failure handling: log and return a minimal, safe response depending on context
	logError('Database connection failed', $exception->getMessage());

	$userMessage = 'Service unavailable.';

	// CLI: write to STDERR and exit non-zero
	if (php_sapi_name() === 'cli') {
		fwrite(STDERR, "Database connection failed: " . $exception->getMessage() . PHP_EOL);
		exit(1);
	}

	// API/JSON callers
	$accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
	if (str_contains($accept, 'application/json') || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
		http_response_code(500);
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode(['success' => false, 'message' => $userMessage], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		exit;
	}

	// Default: minimal HTML message
	http_response_code(500);
	echo '<!doctype html><html><head><meta charset="utf-8"><title>Service unavailable</title></head><body><h1>Service unavailable</h1><p>We are experiencing technical difficulties. Please try again later.</p></body></html>';
	exit;
}

?>