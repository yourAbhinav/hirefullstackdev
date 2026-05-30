<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../config/site.php';


// SEO Configuration
$siteName = SITE_COMPANY_NAME;
$currentPageTitle = isset($page_title) ? $page_title : SEO_DEFAULT_TITLE;
$currentPageTitle = str_replace('DevHire', $siteName, $currentPageTitle);
$currentPageTitle = str_replace('Devhire', $siteName, $currentPageTitle);
$currentPageTitle = str_replace('DevHire Admin', $siteName . ' Admin', $currentPageTitle);
$currentPageTitle = str_replace('DevHire -', $siteName . ' -', $currentPageTitle);
$currentDescription = isset($page_description) ? $page_description : SEO_DEFAULT_DESCRIPTION;

// Detect scheme for canonical URL
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443) ? 'https' : 'http';
$currentUrl = isset($page_url) ? $page_url : $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// Only set OG image if the file exists or a custom image is provided
$defaultOgImage = SITE_URL . '/assets/images/devhire-og.jpg';
$ogImagePath = __DIR__ . '/../assets/images/devhire-og.jpg';
$currentImage = isset($page_image) ? $page_image : (file_exists($ogImagePath) ? $defaultOgImage : '');
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<meta
name="description"
content="<?= htmlspecialchars($currentDescription, ENT_QUOTES, 'UTF-8') ?>">

<meta
name="author"
content="<?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?>">

<title><?= htmlspecialchars($currentPageTitle, ENT_QUOTES, 'UTF-8') ?></title>

<!-- Canonical URL -->
<link rel="canonical" href="<?= htmlspecialchars($currentUrl, ENT_QUOTES, 'UTF-8') ?>">

<!-- OpenGraph Tags -->
<meta property="og:title" content="<?= htmlspecialchars($currentPageTitle, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:description" content="<?= htmlspecialchars($currentDescription, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= htmlspecialchars($currentUrl, ENT_QUOTES, 'UTF-8') ?>">
<?php if ($currentImage !== ''): ?>
<meta property="og:image" content="<?= htmlspecialchars($currentImage, ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>
<meta property="og:site_name" content="<?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?>">

<!-- Twitter Cards -->
<?php if ($currentImage !== ''): ?>
<meta name="twitter:card" content="summary_large_image">
<?php else: ?>
<meta name="twitter:card" content="summary">
<?php endif; ?>
<meta name="twitter:title" content="<?= htmlspecialchars($currentPageTitle, ENT_QUOTES, 'UTF-8') ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($currentDescription, ENT_QUOTES, 'UTF-8') ?>">
<?php if ($currentImage !== ''): ?>
<meta name="twitter:image" content="<?= htmlspecialchars($currentImage, ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>

<!-- Favicon -->

<link
rel="icon"
type="image/svg+xml"
href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%237c3aed' width='100' height='100'/><text x='50' y='60' font-size='55' fill='white' text-anchor='middle'>&lt;/&gt;</text></svg>">

<!-- MAIN CSS -->
<?php
// Prefer a production-built minified stylesheet when available
$mainCssPath = __DIR__ . '/../assets/css/style.min.css';
if (file_exists($mainCssPath)) {
	$cssUrl = appUrl('assets/css/style.min.css?v=3');
} else {
	$cssUrl = appUrl('assets/css/style.css?v=3');
}
?>
<link rel="stylesheet" href="<?= htmlspecialchars($cssUrl, ENT_QUOTES, 'UTF-8') ?>">

<!-- Notifications CSS -->

<link
rel="stylesheet"
href="<?= htmlspecialchars(appUrl('assets/css/notifications.css?v=3'), ENT_QUOTES, 'UTF-8') ?>">

<!-- NAVBAR CSS - Premium SaaS Design -->
<link
rel="stylesheet"
href="<?= htmlspecialchars(appUrl('assets/css/navbar.css?v=1'), ENT_QUOTES, 'UTF-8') ?>">

<?php if (basename($_SERVER['PHP_SELF']) === 'login.php' || basename($_SERVER['PHP_SELF']) === 'register.php' || strpos($_SERVER['PHP_SELF'], 'admin/login.php') !== false): ?>
<!-- AUTH CSS - Premium SaaS Authentication -->
<link
rel="stylesheet"
href="<?= htmlspecialchars(appUrl('assets/css/auth.css?v=1'), ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>

<!-- Font Awesome (async load) -->
<link
rel="preload"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
as="style"
onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>

<!-- Google Fonts (async load with preconnect) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
rel="preload"
href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Sora:wght@400;500;600;700;800&display=swap"
as="style"
onload="this.onload=null;this.rel='stylesheet'">
<noscript><link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet"></noscript>

</head>

<body>