<?php

require_once __DIR__ . '/../includes/helpers.php';
startSecureSession();

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'devhire';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
	$conn = new mysqli($host, $user, $password, $database);
	$conn->set_charset('utf8mb4');
} catch (Throwable $exception) {
	logError('Database connection failed', $exception->getMessage());
	http_response_code(500);
	die('Database connection failed.');
}

?>