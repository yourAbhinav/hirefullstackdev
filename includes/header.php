<?php
require_once __DIR__ . '/helpers.php';
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
content="DevHire - Hire Top Full Stack Developers">

<meta
name="author"
content="DevHire">

<title>
<?php
echo isset($page_title)
? $page_title
: 'DevHire - Hire Top Full Stack Developers';
?>
</title>

<!-- Favicon -->

<link
rel="icon"
type="image/svg+xml"
href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%237c3aed' width='100' height='100'/><text x='50' y='60' font-size='55' fill='white' text-anchor='middle'>&lt;/&gt;</text></svg>">

<!-- MAIN CSS -->

<link
rel="stylesheet"
href="<?= htmlspecialchars(appUrl('assets/css/style.css?v=3'), ENT_QUOTES, 'UTF-8') ?>">

<!-- Notifications CSS -->

<link
rel="stylesheet"
href="<?= htmlspecialchars(appUrl('assets/css/notifications.css?v=3'), ENT_QUOTES, 'UTF-8') ?>">

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
