<?php
// Performance optimization: Enable output buffering for faster perceived load time
if (ob_get_level() === 0) {
    ob_start();
}

require_once 'config/db.php';
require_once 'includes/seo_content.php';
require_once 'includes/helpers.php';

$page_title = 'DevHire - Software Developer Jobs and Hiring Platform';
$page_description = 'Hire software developers, browse full stack and remote developer jobs, and discover trusted engineering careers on DevHire.';
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');
$homeCopy = devhire_home_copy();
$heroTitleRaw = (string) ($homeCopy['hero']['title'] ?? '');
$heroTitleEscaped = htmlspecialchars($heroTitleRaw, ENT_QUOTES, 'UTF-8');
$heroTitleWithHighlight = preg_replace('/\bFull Stack\b/i', '<span class="highlight">$0</span>', $heroTitleEscaped, 1);

if ($heroTitleWithHighlight === null) {
    $heroTitleWithHighlight = $heroTitleEscaped;
}

$currentName = currentUserName();
$currentEmail = currentUserEmail();
$currentRole = currentUserRole();
$canQuickApply = isDeveloper();
$canSaveJobs = isDeveloper();
$applySuccess = getFlash('success');
$applyError = getFlash('error');

// Performance optimization: Cache featured jobs for 5 minutes to reduce database load
$featuredJobs = [];
$cacheKey = 'featured_jobs_cache';
$cacheTime = 300; // 5 minutes

if (isset($_SESSION[$cacheKey]) && isset($_SESSION[$cacheKey . '_time']) && (time() - $_SESSION[$cacheKey . '_time']) < $cacheTime) {
    $featuredJobs = $_SESSION[$cacheKey];
} else {
    $featuredStmt = $conn->prepare("SELECT j.id, j.title, j.description, j.salary_min, j.salary_max, j.experience_level, j.job_type, j.work_mode, j.location, j.tech_stack, COALESCE(u.company_name, u.fullName, 'Company') AS company_name FROM jobs j LEFT JOIN users u ON u.id = j.company_id WHERE j.status = ? ORDER BY j.featured DESC, j.created_at DESC LIMIT 4");
    $featuredStatus = 'active';
    $featuredStmt->bind_param('s', $featuredStatus);
    $featuredStmt->execute();
    $featuredJobs = $featuredStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $featuredStmt->close();
    
    // Cache the results
    $_SESSION[$cacheKey] = $featuredJobs;
    $_SESSION[$cacheKey . '_time'] = time();
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<style>
    .hero {
        min-height: 88vh;
        padding: 3.5rem 2rem 2.5rem;
    }

    .hero-content h1 {
        max-width: 11ch;
    }

    .hero-buttons {
        margin-bottom: 2rem;
    }

    .quick-apply,
    .featured-jobs,
    .why-choose,
    .timeline-section,
    .stats-section,
    .testimonials-section,
    .technologies-section {
        padding-top: 3.25rem;
        padding-bottom: 3.25rem;
    }

    .quick-apply {
        max-width: 1180px;
        margin: 0 auto;
        padding-left: 2rem;
        padding-right: 2rem;
    }

    .quick-apply h2,
    .section-title h2,
    .cta-content h2 {
        font-size: clamp(1.7rem, 2.8vw, 2.45rem);
        letter-spacing: -0.03em;
    }

    .section-title {
        margin-bottom: 2.25rem;
    }

    .section-title p {
        max-width: 760px;
        margin: 0.5rem auto 0;
        line-height: 1.7;
    }

    .job-card,
    .feature-card,
    .stat-card,
    .testimonial-card,
    .tech-item,
    .quick-apply-lock-card {
        border-radius: 1.25rem;
        border-color: rgba(148, 163, 184, 0.18);
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.16);
    }

    .job-card,
    .feature-card,
    .stat-card,
    .testimonial-card {
        background: linear-gradient(180deg, rgba(30, 41, 59, 0.42), rgba(15, 23, 42, 0.58));
    }

    .job-card,
    .feature-card,
    .testimonial-card {
        backdrop-filter: blur(14px);
    }

    .job-card {
        padding: 1.6rem;
    }

    .feature-card {
        padding: 1.6rem;
    }

    .feature-icon,
    .tech-icon,
    .testimonial-avatar,
    .timeline-step {
        box-shadow: 0 10px 24px rgba(79, 70, 229, 0.18);
    }

    .timeline-item {
        padding: 1.6rem;
    }

    .timeline-step {
        width: 52px;
        height: 52px;
        font-size: 1.2rem;
    }

    .timeline::before {
        top: 46px;
        opacity: 0.7;
    }

    .stat-card {
        padding: 2rem;
    }

    .stat-number {
        font-size: 2.15rem;
    }

    .testimonial-card {
        padding: 1.6rem;
    }

    .tech-grid {
        gap: 1.25rem;
    }

    .tech-item {
        padding: 1rem 0.75rem;
        background: rgba(15, 23, 42, 0.16);
        border: 1px solid rgba(148, 163, 184, 0.12);
    }

    .tech-icon {
        width: 72px;
        height: 72px;
    }

    .cta-section {
        margin: 2.5rem 0 0;
        padding: 4rem 2rem;
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.42), rgba(15, 23, 42, 0.64));
    }

    .cta-content {
        max-width: 760px;
    }

    .cta-content p {
        max-width: 640px;
        margin-left: auto;
        margin-right: auto;
    }

    .cta-features {
        gap: 1.25rem;
    }

    .section-cta {
        margin-top: 2rem;
        text-align: center;
    }

    .section-cta .btn-primary {
        min-width: 200px;
    }

    @media (max-width: 768px) {
        .hero {
            min-height: auto;
            padding: 2.5rem 1.25rem 2rem;
        }

        .hero-content h1 {
            max-width: 14ch;
        }

        .quick-apply,
        .featured-jobs,
        .why-choose,
        .timeline-section,
        .stats-section,
        .testimonials-section,
        .technologies-section {
            padding-top: 2.5rem;
            padding-bottom: 2.5rem;
        }

        .section-title {
            margin-bottom: 1.75rem;
        }
    }
</style>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <span class="eyebrow"><?= htmlspecialchars($homeCopy['hero']['eyebrow'], ENT_QUOTES, 'UTF-8') ?></span>
            <h1><?= $heroTitleWithHighlight ?></h1>
            <p class="hero-subtitle"><?= htmlspecialchars($homeCopy['hero']['lead'], ENT_QUOTES, 'UTF-8') ?></p>

            <div class="hero-buttons">
                <a href="<?= appUrl($homeCopy['hero']['primary_cta']['url']) ?>" class="btn-primary">
                    <i class="fas fa-rocket"></i> <?= htmlspecialchars($homeCopy['hero']['primary_cta']['label'], ENT_QUOTES, 'UTF-8') ?>
                </a>
                <a href="<?= appUrl($homeCopy['hero']['secondary_cta']['url']) ?>" class="btn-secondary">
                    <i class="fas fa-briefcase"></i> <?= htmlspecialchars($homeCopy['hero']['secondary_cta']['label'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            </div>

            <div class="trust-badge">
                <div class="trust-avatars">
                    <div class="avatar">M</div>
                    <div class="avatar">T</div>
                    <div class="avatar">A</div>
                    <div class="avatar">+</div>
                </div>
                <div class="trust-text">
                    <h4>Trusted by candidates and hiring teams</h4>
                    <p><?= htmlspecialchars(implode(' • ', $homeCopy['hero']['trust_points']), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
        </div>

        <div class="hero-illustration">
            <div class="hero-card">
                <div class="floating-icons">
                    <div class="floating-icon icon-react">
                        <i class="fab fa-react"></i>
                    </div>
                    <div class="floating-icon icon-js">
                        <i class="fab fa-js-square"></i>
                    </div>
                    <div class="floating-icon icon-ts">
                        TS
                    </div>
                    <div class="floating-icon icon-node">
                        <i class="fab fa-node-js"></i>
                    </div>
                </div>
                <div class="hero-card-copy">
                    <i class="fas fa-code hero-card-icon"></i>
                    <h3>Premium Talent</h3>
                    <p class="hero-card-subtitle">Handpicked developers ready to build</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Apply Form Section -->
    <section class="quick-apply">
        <h2>Quick Apply Now</h2>
        <p class="quick-apply-subtitle">Apply in under 2 minutes and get matched with roles that fit your skills, stack, and experience.</p>

        <?php if (!empty($applySuccess)): ?>
            <div class="notice notice-success notice-compact">
                <i class="fas fa-check-circle"></i>
                <p><?= escape($applySuccess) ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($applyError)): ?>
            <div class="notice notice-error notice-compact">
                <i class="fas fa-exclamation-circle"></i>
                <p><?= escape($applyError) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($canQuickApply): ?>
        <form class="apply-form" method="POST" action="<?= appUrl('handlers/apply.php') ?>" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="redirect_to" value="index.php#quick-apply">
            <div class="form-group">
                <label for="fullName">Full Name</label>
                <input type="text" id="full_name" name="full_name" placeholder="Enter your full name" value="<?= escape($currentName) ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" value="<?= escape($currentEmail) ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required>
            </div>

            <div class="form-group">
                <label for="experience">Experience</label>
                <select id="experience" name="experience" required>
                    <option value="">Select experience</option>
                    <option value="0-1">0-1 Years</option>
                    <option value="1-3">1-3 Years</option>
                    <option value="3-5">3-5 Years</option>
                    <option value="5-10">5-10 Years</option>
                    <option value="10+">10+ Years</option>
                </select>
            </div>

            <div class="form-group">
                <label for="techStack">Tech Stack</label>
                <select id="tech_stack" name="tech_stack" required>
                    <option value="">Select tech stack</option>
                    <option value="full-stack">Full Stack</option>
                    <option value="frontend">Frontend</option>
                    <option value="backend">Backend</option>
                    <option value="devops">DevOps</option>
                </select>
            </div>

            <div class="form-group">
                <label for="job_position">Desired Position</label>
                <select id="job_position" name="job_position" required>
                    <option value="">Select a position</option>
                    <option value="Full Stack Developer">Full Stack Developer</option>
                    <option value="Frontend Developer">Frontend Developer</option>
                    <option value="Backend Developer">Backend Developer</option>
                    <option value="DevOps Engineer">DevOps Engineer</option>
                </select>
            </div>

            <div class="form-group">
                <label for="portfolio_url">Portfolio URL</label>
                <input type="url" id="portfolio_url" name="portfolio_url" placeholder="https://yourportfolio.com">
            </div>

            <div class="form-group">
                <label for="resume">Upload Resume</label>
                <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx" onchange="DevHire.handleFileUpload(this)">
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane"></i> Apply Now
            </button>
        </form>
        <?php else: ?>
            <div class="panel quick-apply-lock-card">
                <span class="eyebrow">Developer Access Required</span>
                <h3 class="quick-apply-lock-title">Sign in to submit an application</h3>
                <p class="quick-apply-lock-copy">
                    Guest users cannot submit applications from the homepage. Developers should sign in to keep every application linked to a valid account.
                </p>
                <?php if (isLoggedIn() && !isDeveloper()): ?>
                    <p class="quick-apply-lock-note">Your current role is <?= escape(ucfirst($currentRole ?: 'user')) ?>, so application submission is disabled for this account.</p>
                    <div class="quick-apply-lock-actions">
                        <a href="<?= appUrl(roleDashboardPath()) ?>" class="btn-primary">Go to Dashboard</a>
                        <a href="<?= appUrl('pages/jobs.php') ?>" class="btn-secondary">Browse Jobs</a>
                    </div>
                <?php else: ?>
                    <div class="quick-apply-lock-actions">
                        <a href="<?= appUrl('pages/login.php') ?>" class="btn-primary">Login</a>
                        <a href="<?= appUrl('pages/register.php') ?>" class="btn-secondary">Register</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- Featured Jobs Section -->
    <section class="featured-jobs">
        <div class="section-title">
            <h2>Featured Open Positions</h2>
            <p>Explore software developer jobs, remote developer jobs, and in-demand engineering careers from verified employers</p>
        </div>

        <div class="jobs-grid">
            <?php if (!empty($featuredJobs)): ?>
                <?php foreach ($featuredJobs as $job): ?>
                    <div class="job-card">
                        <div class="job-header">
                            <h3 class="job-title"><?= escape($job['title'] ?? 'Untitled role') ?></h3>
                            <p class="job-company"><?= escape($job['company_name'] ?? 'Company') ?></p>
                        </div>

                        <div class="job-details">
                            <div class="job-meta">
                                <span class="meta-item">
                                    <i class="fas fa-briefcase"></i> <?= escape($job['experience_level'] ?? 'Not specified') ?>
                                </span>
                                <span class="meta-item">
                                    <i class="fas fa-map-marker-alt"></i> <?= escape(ucfirst((string) ($job['work_mode'] ?? 'remote'))) ?>
                                </span>
                            </div>

                            <div class="job-tags">
                                <?php foreach (array_slice(array_filter(array_map('trim', explode(',', (string) ($job['tech_stack'] ?? '')))), 0, 3) as $tech): ?>
                                    <span class="tag"><?= escape($tech) ?></span>
                                <?php endforeach; ?>
                            </div>

                            <div class="job-salary">
                                <?= !empty($job['salary_min']) || !empty($job['salary_max']) ? '$' . number_format((int) ($job['salary_min'] ?? 0)) . ' - $' . number_format((int) ($job['salary_max'] ?? $job['salary_min'] ?? 0)) . ' /yr' : 'Salary hidden' ?>
                            </div>
                        </div>

                        <div class="job-footer">
                            <a href="<?= appUrl('pages/apply.php?job_id=' . (int) $job['id']) ?>" class="btn-apply-job">Apply</a>
                            <?php if ($canSaveJobs): ?>
                                <button type="button" class="save-job" data-save-job="<?= (int) $job['id'] ?>" aria-label="Save job">
                                    <i class="far fa-bookmark"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">No active jobs are available right now.</div>
            <?php endif; ?>
        </div>

        <div class="section-cta">
            <a href="<?= appUrl('pages/jobs.php') ?>" class="btn-primary">
                <i class="fas fa-arrow-right"></i> View All Jobs
            </a>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose">
        <div class="section-title">
            <h2>Why Choose DevHire?</h2>
            <p>A platform built for trust, relevance, and better hiring outcomes</p>
        </div>

        <div class="features-grid">
            <?php foreach ($homeCopy['why_choose'] as $card): ?>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="<?= htmlspecialchars($card['icon'], ENT_QUOTES, 'UTF-8') ?>"></i>
                    </div>
                    <h3 class="feature-title"><?= htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="feature-description"><?= htmlspecialchars($card['copy'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- About DevHire Section -->
    <section class="featured-jobs">
        <div class="section-title">
            <h2><?= htmlspecialchars($homeCopy['about']['title'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p><?= htmlspecialchars($homeCopy['about']['eyebrow'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="jobs-grid">
            <div class="job-card">
                <?php foreach ($homeCopy['about']['paragraphs'] as $paragraph): ?>
                    <p class="feature-description"><?= htmlspecialchars($paragraph, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endforeach; ?>
                <div class="job-tags">
                    <?php foreach ($homeCopy['about']['stats'] as $stat): ?>
                        <span class="tag"><?= htmlspecialchars($stat['value'] . ' ' . $stat['label'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="job-card">
                <?= renderResponsiveImage(
                    'team-culture.jpg',
                    'A modern software team reviewing hiring priorities together',
                    'feature-image',
                    '(max-width: 900px) 100vw, 520px'
                ) ?>
                <h3 class="job-title" style="margin-top:18px;">What makes the platform different</h3>
                <ul class="how-offer-list">
                    <li class="how-offer-item"><i class="fas fa-check-circle how-offer-icon how-offer-icon-primary"></i>Verified developer profiles with stronger skill signals</li>
                    <li class="how-offer-item"><i class="fas fa-check-circle how-offer-icon how-offer-icon-primary"></i>Software developer jobs that support clear stack matching</li>
                    <li class="how-offer-item"><i class="fas fa-check-circle how-offer-icon how-offer-icon-primary"></i>Hiring journeys designed for trust and higher conversion</li>
                    <li class="how-offer-item"><i class="fas fa-check-circle how-offer-icon how-offer-icon-primary"></i>Career pages built to improve SEO and user engagement</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- How It Works Timeline Section -->
    <section class="timeline-section">
        <div class="section-title">
            <h2>Our Hiring Process</h2>
            <p>Four simple steps to a better hiring decision</p>
        </div>

        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-step">1</div>
                    <h3 class="timeline-title">Apply</h3>
                    <p class="timeline-description">Submit your application with your resume, portfolio, and stack details in under 2 minutes.</p>
            </div>

            <div class="timeline-item">
                <div class="timeline-step">2</div>
                    <h3 class="timeline-title">Screening</h3>
                    <p class="timeline-description">We review your profile and match you with roles based on stack, seniority, and hiring need.</p>
            </div>

            <div class="timeline-item">
                <div class="timeline-step">3</div>
                    <h3 class="timeline-title">Interview</h3>
                    <p class="timeline-description">Technical and HR interviews help both sides confirm culture fit, communication, and delivery expectations.</p>
            </div>

            <div class="timeline-item">
                <div class="timeline-step">4</div>
                    <h3 class="timeline-title">Offer</h3>
                    <p class="timeline-description">Receive your offer and start a new role with clearer expectations and better long-term growth potential.</p>
            </div>
        </div>
    </section>

    <!-- Engineering Culture Section -->
    <section class="featured-jobs">
        <div class="section-title">
            <h2><?= htmlspecialchars($homeCopy['culture']['title'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p>How high-performing engineering teams work</p>
        </div>
        <div class="jobs-grid">
            <?php foreach ($homeCopy['culture']['paragraphs'] as $paragraph): ?>
                <div class="job-card">
                    <p class="feature-description"><?= htmlspecialchars($paragraph, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="section-title">
            <h2>Our Impact</h2>
            <p>Signals that reflect the trust and momentum behind the platform</p>
        </div>

        <div class="stats-grid">
            <?php foreach ($homeCopy['stats'] as $stat): ?>
                <div class="stat-card">
                    <div class="stat-number"><?= htmlspecialchars($stat['value'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="stat-label"><?= htmlspecialchars($stat['label'], ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Career Growth and Benefits Section -->
    <section class="featured-jobs">
        <div class="section-title">
            <h2><?= htmlspecialchars($homeCopy['growth']['title'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p><?= htmlspecialchars($homeCopy['benefits']['title'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="jobs-grid">
            <div class="job-card">
                <h3 class="job-title"><?= htmlspecialchars($homeCopy['growth']['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <ul class="how-offer-list">
                    <?php foreach ($homeCopy['growth']['items'] as $item): ?>
                        <li class="how-offer-item"><i class="fas fa-check-circle how-offer-icon how-offer-icon-primary"></i><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="job-card">
                <h3 class="job-title"><?= htmlspecialchars($homeCopy['benefits']['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <ul class="how-offer-list">
                    <?php foreach ($homeCopy['benefits']['items'] as $item): ?>
                        <li class="how-offer-item"><i class="fas fa-check-circle how-offer-icon how-offer-icon-secondary"></i><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="section-title">
            <h2>Success Stories</h2>
            <p>Hear from developers and hiring teams who value a clearer process</p>
        </div>

        <div class="testimonials-grid">
            <?php foreach ($homeCopy['testimonials'] as $testimonial): ?>
                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-quote">&quot;<?= htmlspecialchars($testimonial['quote'], ENT_QUOTES, 'UTF-8') ?>&quot;</p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar"><?= htmlspecialchars(strtoupper(substr($testimonial['name'], 0, 2)), ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="testimonial-info">
                            <h4><?= htmlspecialchars($testimonial['name'], ENT_QUOTES, 'UTF-8') ?></h4>
                            <p class="testimonial-role"><?= htmlspecialchars($testimonial['role'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Industry Focus Section -->
    <section class="technologies-section">
        <div class="section-title">
            <h2><?= htmlspecialchars($homeCopy['industry_focus']['title'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p>Where we see the strongest demand for hiring and growth</p>
        </div>
        <div class="tech-grid">
            <?php foreach ($homeCopy['industry_focus']['items'] as $item): ?>
                <div class="tech-item">
                    <div class="tech-icon"><i class="fas fa-building"></i></div>
                    <div class="tech-name"><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="why-choose">
        <div class="section-title">
            <h2>Frequently Asked Questions</h2>
            <p>Quick answers for candidates and hiring teams</p>
        </div>
        <div class="features-grid">
            <?php foreach ($homeCopy['faq'] as $faqItem): ?>
                <div class="feature-card">
                    <h3 class="feature-title"><?= htmlspecialchars($faqItem['q'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="feature-description"><?= htmlspecialchars($faqItem['a'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Technologies Section -->
    <section class="technologies-section">
        <div class="section-title">
            <h2>Technologies We Hire For</h2>
            <p>Frontend, backend, mobile, cloud, AI, security, design, and quality engineering</p>
        </div>

        <div class="tech-grid">
            <?php foreach ($homeCopy['technologies'] as $tech): ?>
                <div class="tech-item">
                    <div class="tech-icon">
                        <i class="<?= htmlspecialchars($tech['icon'], ENT_QUOTES, 'UTF-8') ?>"></i>
                    </div>
                    <div class="tech-name"><?= htmlspecialchars($tech['name'], ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Final CTA Section -->
    <section class="cta-section">
        <div class="cta-content">
            <h2>Ready to join the right team?</h2>
            <p>Apply now, explore developer careers, and take the next step with a clearer hiring experience.</p>

            <div class="cta-features">
                <div class="cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>100% Free to Apply</span>
                </div>
                <div class="cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>Verified Companies</span>
                </div>
                <div class="cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>Secure & Confidential</span>
                </div>
            </div>

            <a href="<?= appUrl('pages/apply.php') ?>" class="btn-primary final-cta-button">
                <i class="fas fa-rocket"></i> Start Your Application
            </a>
        </div>
    </section>

<?php 
// Performance optimization: Flush output buffer to send content to browser faster
if (ob_get_level() > 0) {
    ob_end_flush();
}
include 'includes/footer.php'; ?>
