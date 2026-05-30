<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../config/site.php';

startSecureSession();
$siteName = SITE_COMPANY_NAME;
$isAuthenticated = isLoggedIn();
$isDeveloperUser = isDeveloper();
?>

<!-- Footer -->
<footer class="footer">

<div class="footer-container">

<div class="footer-grid">

<!-- Brand -->
<div class="footer-column">

<div class="footer-logo">
<span class="logo-icon">&lt;/&gt;</span>
<span><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?></span>
</div>

<p class="footer-description">
We connect talented full stack developers with verified companies looking for top talent.
</p>

</div>

<!-- Quick Links -->
<div class="footer-column">

<h4 class="footer-title">
Quick Links
</h4>

<ul class="footer-links">

<li>
<a href="<?= appUrl('index.php') ?>">
Home
</a>
</li>

<li>
<a href="<?= appUrl('pages/jobs.php') ?>">
Jobs
</a>
</li>

<li>
<a href="<?= appUrl('pages/about.php') ?>">
About
</a>
</li>

<li>
<a href="<?= appUrl('pages/careers.php') ?>">
Careers
 </a>
 </li>

 <li>
<a href="<?= appUrl('pages/technologies.php') ?>">
Technologies
 </a>
 </li>

 <li>
<a href="<?= appUrl('pages/faq.php') ?>">
FAQ
</a>
</li>

<li>
<a href="<?= appUrl('pages/pricing.php') ?>">
Pricing
</a>
</li>

</ul>

</div>

<!-- Developers -->

<div class="footer-column">

<h4 class="footer-title">
For Developers
</h4>

<ul class="footer-links">

<li>
<a href="<?= appUrl('pages/jobs.php') ?>">
Browse Jobs
</a>
</li>

<li>
<a href="<?= appUrl('pages/developers.php') ?>">
Browse Developers
</a>
</li>

<?php if ($isDeveloperUser): ?>
<li>
<a href="<?= appUrl('pages/apply.php') ?>">
Apply Now
</a>
</li>

<li>
<a href="<?= appUrl('pages/profile.php') ?>">
My Profile
</a>
</li>

<li>
<a href="<?= appUrl('pages/applications.php') ?>">
Applications
</a>
</li>
<?php else: ?>
<li>
<a href="<?= appUrl('pages/login.php') ?>">
Developer Login
</a>
</li>

<li>
<a href="<?= appUrl('pages/register.php') ?>">
Create Developer Account
</a>
</li>
<?php endif; ?>

</ul>

</div>

<!-- Companies -->

<div class="footer-column">

<h4 class="footer-title">
For Companies
</h4>

<ul class="footer-links">

<li>
<a href="<?= appUrl('pages/contact.php') ?>">
Post a Job
</a>
</li>

<li>
<a href="<?= appUrl('pages/how-it-works.php') ?>">
How It Works
</a>
</li>

<li>
<a href="<?= appUrl('pages/testimonials.php') ?>">
Testimonials
</a>
</li>

<li>
<a href="<?= appUrl('pages/blog-ideas.php') ?>">
Blog Ideas
</a>
</li>

</ul>

</div>

<!-- Contact -->

<div class="footer-column">

<h4 class="footer-title">
Contact Us
</h4>

<div class="footer-contact">

<p>
<i class="fas fa-phone"></i>
<?= substr(CONTACT_PHONE, 0, 15) ?>
</p>

<p>
<i class="fas fa-envelope"></i>
<?= CONTACT_SUPPORT_EMAIL ?>
</p>

<p>
<i class="fas fa-briefcase"></i>
<a href="<?= appUrl('pages/contact.php') ?>">Post a job or request talent</a>
</p>

<p>
<i class="fas fa-map-marker-alt"></i>
San Francisco, CA
</p>

</div>

</div>

</div>

<!-- Bottom -->

<div class="footer-bottom">

<div class="footer-bottom-left">

<p>
&copy; 2026 <?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?>. All rights reserved.
</p>

</div>

<div class="footer-bottom-right">

<?php if ($isAuthenticated): ?>
<?= renderLogoutForm('Logout', 'btn-login footer-auth-link') ?>

<span>|</span>
<?php endif; ?>

<a href="<?= appUrl('pages/privacy.php') ?>">
Privacy Policy
</a>

<span>|</span>

<a href="<?= appUrl('pages/terms.php') ?>">
Terms
</a>

<span>|</span>

<a href="<?= appUrl('pages/cookies.php') ?>">
Cookies
</a>

</div>

</div>

</div>

<div class="footer-gradient"></div>

</footer>

<script>
window.DEVHIRE_BASE_URL = <?= json_encode(rtrim(APP_BASE_URL, '/'), JSON_UNESCAPED_SLASHES) ?>;
window.DEVHIRE_CSRF_TOKEN = <?= json_encode(csrfToken(), JSON_UNESCAPED_SLASHES) ?>;
</script>

<!-- JS -->
<?php
// Prefer built/minified JS when available
$mainMin = __DIR__ . '/../assets/js/main.min.js';
$navbarMin = __DIR__ . '/../assets/js/navbar.min.js';
$mainJsUrl = file_exists($mainMin) ? appUrl('assets/js/main.min.js') : appUrl('assets/js/main.js');
$navbarJsUrl = file_exists($navbarMin) ? appUrl('assets/js/navbar.min.js') : appUrl('assets/js/navbar.js');
?>
<script defer src="<?= $mainJsUrl ?>"></script>

<!-- Navbar JS - Premium Mobile Menu -->
<script defer src="<?= $navbarJsUrl ?>"></script>

</body>
</html>
