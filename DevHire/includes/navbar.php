<?php
require_once __DIR__ . '/helpers.php';
startSecureSession();
$isAuthenticated = isPublicUserLoggedIn();
$displayName = currentUserName();
$displayEmail = currentUserEmail();
$displayPhoto = currentUserPhoto();
$currentRole = currentUserRole();
$roleHome = roleDashboardPath($currentRole);
?>

<!-- Navigation Bar -->
<nav class="navbar">
    <div class="nav-container">
        
        <!-- LEFT: Logo -->
        <div class="nav-section nav-section-left">
            <div class="nav-logo">
                <a href="<?= appUrl('index.php') ?>">
                    <span class="logo-icon">&lt;/&gt;</span>
                    <span class="logo-text"><?= htmlspecialchars(SITE_COMPANY_NAME) ?></span>
                </a>
            </div>
        </div>

        <!-- CENTER: Navigation Menu -->
        <div class="nav-section nav-section-center">
            <ul class="nav-menu" id="navMenu">
                <li>
                    <a href="<?= appUrl('index.php') ?>" class="nav-link">Home</a>
                </li>
                <li>
                    <a href="<?= appUrl('pages/jobs.php') ?>" class="nav-link">Jobs</a>
                </li>
                <li>
                    <a href="<?= appUrl('pages/developers.php') ?>" class="nav-link">Developers</a>
                </li>
                <li>
                    <a href="<?= appUrl('pages/how-it-works.php') ?>" class="nav-link">How It Works</a>
                </li>
                <li>
                    <a href="<?= appUrl('pages/pricing.php') ?>" class="nav-link">Pricing</a>
                </li>
                <li>
                    <a href="<?= appUrl('pages/testimonials.php') ?>" class="nav-link">Testimonials</a>
                </li>
                <li>
                    <a href="<?= appUrl('pages/contact.php') ?>" class="nav-link">Contact</a>
                </li>
            </ul>
        </div>

        <!-- RIGHT: User Actions -->
        <div class="nav-section nav-section-right">
            <div class="nav-buttons">
                <div id="userArea">
                    <?php if ($isAuthenticated): ?>
                        <a class="user-chip user-chip-link" href="<?= appUrl($roleHome) ?>">
                            <?php if (!empty($displayPhoto)): ?>
                                <img src="<?= htmlspecialchars($displayPhoto, ENT_QUOTES, 'UTF-8') ?>" alt="User photo" class="user-avatar">
                            <?php else: ?>
                                <span class="user-avatar user-avatar-fallback"><?= htmlspecialchars(strtoupper(substr($displayName, 0, 1)), ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                            <div class="user-chip-copy">
                                <strong><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></strong>
                                <span><?= htmlspecialchars(roleLabel($currentRole), ENT_QUOTES, 'UTF-8') ?> account</span>
                            </div>
                        </a>
                    <?php else: ?>
                        <div class="nav-auth-buttons">
                            <a href="<?= appUrl('pages/login.php') ?>" class="btn-login">Login</a>
                            <a href="<?= appUrl('pages/register.php') ?>" class="btn-apply">Register</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Hamburger Menu -->
        <div class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
</nav>

<!-- Mobile Menu Overlay -->
<div class="mobile-menu-overlay" id="mobileMenuOverlay">
    <div class="mobile-menu-content">
        <?php if ($isAuthenticated): ?>
            <?php if (isAdmin()): ?>
                <a href="<?= appUrl('admin/dashboard.php') ?>" class="mobile-menu-link">Admin Dashboard</a>
                <a href="<?= appUrl('admin/applications.php') ?>" class="mobile-menu-link">Applications</a>
            <?php elseif (isCompany()): ?>
                <a href="<?= appUrl('company/dashboard.php') ?>" class="mobile-menu-link">Company Dashboard</a>
                <a href="<?= appUrl('company/jobs.php') ?>" class="mobile-menu-link">Manage Jobs</a>
                <a href="<?= appUrl('company/applicants.php') ?>" class="mobile-menu-link">Applicants</a>
            <?php else: ?>
                <a href="<?= appUrl('pages/profile.php') ?>" class="mobile-menu-link">My Profile</a>
                <a href="<?= appUrl('pages/apply.php') ?>" class="mobile-menu-link">Apply</a>
                <a href="<?= appUrl('pages/applications.php') ?>" class="mobile-menu-link">Applications</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
