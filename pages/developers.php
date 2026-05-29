<?php
require_once '../includes/helpers.php';
require_once '../config/site.php';

$page_title = 'Developers - DevHire';
$page_description = 'Browse our directory of verified full stack developers. Find top talent for your next project.';
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

include '../includes/header.php';
include '../includes/navbar.php';

// Load developers directory CSS
echo '<link rel="stylesheet" href="' . htmlspecialchars(appUrl('assets/css/developers.css?v=1'), ENT_QUOTES, 'UTF-8') . '">';

// Get search and filter parameters
$search = trim((string) ($_GET['search'] ?? ''));
$techFilter = trim((string) ($_GET['tech'] ?? ''));
$experienceFilter = trim((string) ($_GET['experience'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;
?>

<!-- Page Header -->
<section class="page-hero">
    <div class="page-hero-inner">
        <span class="eyebrow">Developer Directory</span>
        <h1>Verified Talent Pool</h1>
        <p class="quick-apply-subtitle">Browse our curated directory of professional full stack developers ready to join your team.</p>
        <div class="hero-buttons">
            <a href="<?= appUrl('pages/jobs.php') ?>" class="btn-primary">Browse Jobs</a>
            <a href="<?= appUrl('pages/contact.php') ?>" class="btn-secondary">Contact Us</a>
        </div>
    </div>
</section>

<!-- Search and Filters -->
<section class="featured-jobs">
    <div class="filters-container">
        <form method="GET" class="search-filters-form">
            <div class="filter-group">
                <label for="search">Search Developers</label>
                <input type="text" id="search" name="search" placeholder="Search by name, skills..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            
            <div class="filter-group">
                <label for="tech">Tech Stack</label>
                <select id="tech" name="tech">
                    <option value="">All Technologies</option>
                    <option value="react" <?= $techFilter === 'react' ? 'selected' : '' ?>>React</option>
                    <option value="vue" <?= $techFilter === 'vue' ? 'selected' : '' ?>>Vue.js</option>
                    <option value="angular" <?= $techFilter === 'angular' ? 'selected' : '' ?>>Angular</option>
                    <option value="node" <?= $techFilter === 'node' ? 'selected' : '' ?>>Node.js</option>
                    <option value="python" <?= $techFilter === 'python' ? 'selected' : '' ?>>Python</option>
                    <option value="php" <?= $techFilter === 'php' ? 'selected' : '' ?>>PHP</option>
                    <option value="java" <?= $techFilter === 'java' ? 'selected' : '' ?>>Java</option>
                    <option value="typescript" <?= $techFilter === 'typescript' ? 'selected' : '' ?>>TypeScript</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="experience">Experience Level</label>
                <select id="experience" name="experience">
                    <option value="">All Levels</option>
                    <option value="junior" <?= $experienceFilter === 'junior' ? 'selected' : '' ?>>Junior (0-2 years)</option>
                    <option value="mid" <?= $experienceFilter === 'mid' ? 'selected' : '' ?>>Mid-Level (2-5 years)</option>
                    <option value="senior" <?= $experienceFilter === 'senior' ? 'selected' : '' ?>>Senior (5+ years)</option>
                </select>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn-primary btn-inline">Search</button>
                <a href="<?= appUrl('pages/developers.php') ?>" class="btn-secondary btn-inline">Reset</a>
            </div>
        </form>
    </div>
</section>

<!-- Developer Directory Coming Soon -->
<section class="featured-jobs">
    <div class="developers-directory-coming-soon">
        <div class="coming-soon-content">
            <div class="coming-soon-icon">
                <i class="fas fa-code"></i>
            </div>
            <h2>Developer Directory Coming Soon</h2>
            <p>We're currently curating our list of verified full stack developers. The developer directory will feature:</p>
            
            <div class="coming-soon-features">
                <div class="feature-item">
                    <i class="fas fa-user-check"></i>
                    <span>Verified Developer Profiles</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-search"></i>
                    <span>Advanced Search & Filtering</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-layer-group"></i>
                    <span>Tech Stack Filtering</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-clock"></i>
                    <span>Experience Level Filters</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-briefcase"></i>
                    <span>Portfolio & Work Samples</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-star"></i>
                    <span>Skills & Endorsements</span>
                </div>
            </div>
            
            <div class="coming-soon-cta">
                <p>Companies can manage jobs and applicants from the company panel.</p>
                <div class="cta-buttons">
                    <a href="<?= appUrl('pages/jobs.php') ?>" class="btn-primary">Browse Jobs</a>
                    <a href="<?= appUrl('pages/contact.php') ?>" class="btn-secondary">Get Notified</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>