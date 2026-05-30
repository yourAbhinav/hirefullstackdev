<?php

if (php_sapi_name() !== 'cli') {
	fwrite(STDERR, "This script can only be run from the command line.\n");
	exit(1);
}

require_once __DIR__ . '/../config/db.php';

$applyChanges = in_array('--apply', $argv ?? [], true) || in_array('--run', $argv ?? [], true);

$candidateSql = 'SELECT a.id, a.email, a.full_name, a.created_at, u.id AS user_id, u.role, u.fullName AS user_name FROM applications a INNER JOIN users u ON LOWER(TRIM(u.email)) = LOWER(TRIM(a.email)) WHERE a.user_id IS NULL ORDER BY a.id ASC';
$candidateStmt = $conn->prepare($candidateSql);
$candidateStmt->execute();
$candidates = $candidateStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$candidateStmt->close();

$linkedCount = 0;
$skippedCount = 0;

foreach ($candidates as $candidate) {
	$applicationId = (int) ($candidate['id'] ?? 0);
	$userId = (int) ($candidate['user_id'] ?? 0);

	if ($applicationId <= 0 || $userId <= 0) {
		$skippedCount++;
		continue;
	}

	if ($applyChanges) {
		$updateStmt = $conn->prepare('UPDATE applications SET user_id = ?, updated_at = NOW() WHERE id = ? AND user_id IS NULL');
		$updateStmt->bind_param('ii', $userId, $applicationId);
		$updateStmt->execute();
		$rowsChanged = $updateStmt->affected_rows;
		$updateStmt->close();

		if ($rowsChanged > 0) {
			$linkedCount++;
		} else {
			$skippedCount++;
		}
	} else {
		$linkedCount++;
	}
}

$unmatchedStmt = $conn->prepare('SELECT COUNT(*) AS total FROM applications WHERE user_id IS NULL');
$unmatchedStmt->execute();
$unmatchedTotal = (int) ($unmatchedStmt->get_result()->fetch_assoc()['total'] ?? 0);
$unmatchedStmt->close();

echo $applyChanges ? "Applied backfill.\n" : "Dry run only.\n";
echo 'Matched applications: ' . count($candidates) . PHP_EOL;
echo 'Linked applications: ' . $linkedCount . PHP_EOL;
echo 'Skipped rows: ' . $skippedCount . PHP_EOL;
echo 'Remaining unlinked applications: ' . $unmatchedTotal . PHP_EOL;

if (!$applyChanges) {
	echo "Run again with --apply to write the changes.\n";
}
