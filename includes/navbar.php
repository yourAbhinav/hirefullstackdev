<?php
require_once __DIR__ . '/helpers.php';
startSecureSession();
$isAuthenticated = isLoggedIn();
$displayName = currentUserName();
$displayEmail = currentUserEmail();
$displayPhoto = $_SESSION['admin_photo'] ?? '';
?>

<!-- Navigation Bar -->
<nav class="navbar">
<div class="nav-container">

<!-- Logo -->
<div class="nav-logo">
<a href="/hieringfullstackdeveloper/DevHire/index.php">

<span class="logo-icon">&lt;/&gt;</span>

<span class="logo-text">

DevHire

</span>

</a>
</div>


<!-- Hamburger -->

<div
class="hamburger"
id="hamburger">

<span></span>
<span></span>
<span></span>

</div>


<!-- Menu -->

<ul
class="nav-menu"
id="navMenu">

<li>
<a
href="/hieringfullstackdeveloper/DevHire/index.php"
class="nav-link">

Home

</a>
</li>

<li>
<a
href="/hieringfullstackdeveloper/DevHire/pages/jobs.php"
class="nav-link">

Jobs

</a>
</li>

<li>
<a
href="/hieringfullstackdeveloper/DevHire/pages/developers.php"
class="nav-link">

Developers

</a>
</li>

<li>
<a
href="/hieringfullstackdeveloper/DevHire/pages/how-it-works.php"
class="nav-link">

How It Works

</a>
</li>

<li>
<a
href="/hieringfullstackdeveloper/DevHire/pages/pricing.php"
class="nav-link">

Pricing

</a>
</li>

<li>
<a
href="/hieringfullstackdeveloper/DevHire/pages/testimonials.php"
class="nav-link">

Testimonials

</a>
</li>

<li>
<a
href="/hieringfullstackdeveloper/DevHire/pages/contact.php"
class="nav-link">

Contact

</a>
</li>

</ul>



<!-- Buttons -->

<div class="nav-buttons">

<div id="userArea">
<?php if ($isAuthenticated): ?>
	<a class="user-chip user-chip-link" href="<?= appUrl('pages/profile.php') ?>">
		<?php if (!empty($displayPhoto)): ?>
			<img src="<?= htmlspecialchars($displayPhoto, ENT_QUOTES, 'UTF-8') ?>" alt="User photo" class="user-avatar">
		<?php else: ?>
			<span class="user-avatar user-avatar-fallback"><?= htmlspecialchars(strtoupper(substr($displayName, 0, 1)), ENT_QUOTES, 'UTF-8') ?></span>
		<?php endif; ?>
		<div class="user-chip-copy">
			<strong><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></strong>
			<span>View profile</span>
		</div>
	</a>
<?php else: ?>
	<a
	href="<?= appUrl('pages/login.php') ?>"
	class="btn-login">

	Login

	</a>
<?php endif; ?>

</div>

<a
href="<?= appUrl('pages/apply.php') ?>"
class="btn-apply">

Apply Now

</a>


</div>

</div>
</nav>
