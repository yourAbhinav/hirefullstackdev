<?php
require_once 'config/db.php';

$page_title = "DevHire - Hire Top Full Stack Developers";
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

$currentName = currentUserName();
$currentEmail = currentUserEmail();
$currentRole = currentUserRole();
$canQuickApply = isDeveloper();
$canSaveJobs = isDeveloper();
$applySuccess = getFlash('success');
$applyError = getFlash('error');

$featuredJobs = [];
$featuredStmt = $conn->prepare("SELECT j.id, j.title, j.description, j.salary_min, j.salary_max, j.experience_level, j.job_type, j.work_mode, j.location, j.tech_stack, COALESCE(u.company_name, u.fullName, 'Company') AS company_name FROM jobs j LEFT JOIN users u ON u.id = j.company_id WHERE j.status = ? ORDER BY j.featured DESC, j.created_at DESC LIMIT 4");
$featuredStatus = 'active';
$featuredStmt->bind_param('s', $featuredStatus);
$featuredStmt->execute();
$featuredJobs = $featuredStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$featuredStmt->close();

include 'includes/header.php';
include 'includes/navbar.php';
?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Hire Top <span class="highlight">Full Stack</span> Developers</h1>
            <p class="hero-subtitle">
                We help businesses and startups hire verified, skilled and experienced full stack developers for your next project.
            </p>

            <div class="hero-buttons">
                <a href="<?= appUrl('pages/apply.php') ?>" class="btn-primary">
                    <i class="fas fa-rocket"></i> Apply Now
                </a>
                <a href="<?= appUrl('pages/jobs.php') ?>" class="btn-secondary">
                    <i class="fas fa-briefcase"></i> View Open Positions
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
                    <h4>Trusted by 500+ Companies</h4>
                    <p>Leading tech companies trust us</p>
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
                <div style="text-align: center; position: relative; z-index: 10;">
                    <i class="fas fa-code" style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem; display: block;"></i>
                    <h3>Premium Talent</h3>
                    <p style="color: var(--text-secondary); margin-top: 0.5rem;">Handpicked developers ready to build</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Apply Form Section -->
    <section class="quick-apply">
        <h2>Quick Apply Now</h2>
        <p class="quick-apply-subtitle">Apply in under 2 minutes and get hired faster.</p>

        <?php if (!empty($applySuccess)): ?>
            <div class="notice notice-success" style="max-width: 900px; margin: 0 auto 1.5rem;">
                <i class="fas fa-check-circle"></i>
                <p><?= escape($applySuccess) ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($applyError)): ?>
            <div class="notice notice-error" style="max-width: 900px; margin: 0 auto 1.5rem;">
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
            <div class="panel" style="max-width: 900px; margin: 0 auto; text-align: center;">
                <span class="eyebrow">Developer Access Required</span>
                <h3 style="margin-top: 0.75rem;">Sign in to submit an application</h3>
                <p style="color: var(--text-secondary); line-height: 1.7; margin-top: 0.75rem;">
                    Guest users cannot submit applications from the homepage. Developers should sign in to keep every application linked to a valid account.
                </p>
                <?php if (isLoggedIn() && !isDeveloper()): ?>
                    <p style="margin-top: 1rem; color: var(--text-secondary);">Your current role is <?= escape(ucfirst($currentRole ?: 'user')) ?>, so application submission is disabled for this account.</p>
                    <div style="display:flex; gap: 1rem; justify-content:center; flex-wrap: wrap; margin-top: 1.5rem;">
                        <a href="<?= appUrl(roleDashboardPath()) ?>" class="btn-primary">Go to Dashboard</a>
                        <a href="<?= appUrl('pages/jobs.php') ?>" class="btn-secondary">Browse Jobs</a>
                    </div>
                <?php else: ?>
                    <div style="display:flex; gap: 1rem; justify-content:center; flex-wrap: wrap; margin-top: 1.5rem;">
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
            <p>Explore amazing opportunities at leading companies</p>
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

        <div style="text-align: center; margin-top: 3rem;">
            <a href="<?= appUrl('pages/jobs.php') ?>" class="btn-primary">
                <i class="fas fa-arrow-right"></i> View All Jobs
            </a>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose">
        <div class="section-title">
            <h2>Why Apply With Us?</h2>
            <p>We make hiring and finding jobs simple and effective</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-globe"></i>
                </div>
                <h3 class="feature-title">Remote Opportunities</h3>
                <p class="feature-description">Work from anywhere in the world. Enjoy complete flexibility and work-life balance.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-tachometer-alt"></i>
                </div>
                <h3 class="feature-title">Fast Hiring Process</h3>
                <p class="feature-description">Get hired in as little as 2 weeks. Our streamlined process ensures quick turnaround.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <h3 class="feature-title">Real World Projects</h3>
                <p class="feature-description">Work on meaningful projects that impact millions of users worldwide.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3 class="feature-title">Flexible Work Hours</h3>
                <p class="feature-description">Choose your own working hours and work at your own pace with no restrictions.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3 class="feature-title">Professional Growth</h3>
                <p class="feature-description">Continuous learning opportunities and mentorship from industry experts.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3 class="feature-title">Long-term Partnerships</h3>
                <p class="feature-description">Build lasting professional relationships with companies and fellow developers.</p>
            </div>
        </div>
    </section>

    <!-- How It Works Timeline Section -->
    <section class="timeline-section">
        <div class="section-title">
            <h2>Our Hiring Process</h2>
            <p>Four simple steps to your dream job</p>
        </div>

        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-step">1</div>
                <h3 class="timeline-title">Apply</h3>
                <p class="timeline-description">Submit your application with your resume and portfolio in under 2 minutes.</p>
            </div>

            <div class="timeline-item">
                <div class="timeline-step">2</div>
                <h3 class="timeline-title">Screening</h3>
                <p class="timeline-description">We review your profile and match you with suitable opportunities.</p>
            </div>

            <div class="timeline-item">
                <div class="timeline-step">3</div>
                <h3 class="timeline-title">Interview</h3>
                <p class="timeline-description">Technical and HR interviews to ensure the perfect fit for both sides.</p>
            </div>

            <div class="timeline-item">
                <div class="timeline-step">4</div>
                <h3 class="timeline-title">Offer</h3>
                <p class="timeline-description">Receive your offer and start your exciting journey with the company.</p>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="section-title">
            <h2>Our Impact</h2>
            <p>Trusted by thousands of developers and companies</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">500+</div>
                <div class="stat-label">Applications</div>
            </div>

            <div class="stat-card">
                <div class="stat-number">120+</div>
                <div class="stat-label">Developers Hired</div>
            </div>

            <div class="stat-card">
                <div class="stat-number">50+</div>
                <div class="stat-label">Partner Companies</div>
            </div>

            <div class="stat-card">
                <div class="stat-number">95%</div>
                <div class="stat-label">Satisfaction Rate</div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="section-title">
            <h2>Success Stories</h2>
            <p>Hear from developers who found their dream job</p>
        </div>

        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="testimonial-quote">
                    "DevHire helped me find a great remote job with an incredible team. The process was smooth and hassle-free."
                </p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">AJ</div>
                    <div class="testimonial-info">
                        <h4>Arjun Patel</h4>
                        <p class="testimonial-role">Full Stack Developer</p>
                    </div>
                </div>
            </div>

            <div class="testimonial-card">
                <div class="testimonial-stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="testimonial-quote">
                    "I got multiple interview calls within a week. The team is supportive and the hiring process is very transparent."
                </p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">PS</div>
                    <div class="testimonial-info">
                        <h4>Priya Sharma</h4>
                        <p class="testimonial-role">Frontend Developer</p>
                    </div>
                </div>
            </div>

            <div class="testimonial-card">
                <div class="testimonial-stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                </div>
                <p class="testimonial-quote">
                    "Best platform for developers! I'm now working on challenging projects and growing my career exponentially."
                </p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">RV</div>
                    <div class="testimonial-info">
                        <h4>Rohit Verma</h4>
                        <p class="testimonial-role">Backend Developer</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Technologies Section -->
    <section class="technologies-section">
        <div class="section-title">
            <h2>Technologies We Use</h2>
            <p>Work with cutting-edge technologies and frameworks</p>
        </div>

        <div class="tech-grid">
            <div class="tech-item">
                <div class="tech-icon">
                    <i class="fab fa-react"></i>
                </div>
                <div class="tech-name">React</div>
            </div>

            <div class="tech-item">
                <div class="tech-icon">
                    <i class="fab fa-js-square"></i>
                </div>
                <div class="tech-name">JavaScript</div>
            </div>

            <div class="tech-item">
                <div class="tech-icon">
                    TS
                </div>
                <div class="tech-name">TypeScript</div>
            </div>

            <div class="tech-item">
                <div class="tech-icon">
                    <i class="fab fa-node-js"></i>
                </div>
                <div class="tech-name">Node.js</div>
            </div>

            <div class="tech-item">
                <div class="tech-icon">
                    <i class="fab fa-php"></i>
                </div>
                <div class="tech-name">PHP</div>
            </div>

            <div class="tech-item">
                <div class="tech-icon">
                    <i class="fab fa-python"></i>
                </div>
                <div class="tech-name">Python</div>
            </div>

            <div class="tech-item">
                <div class="tech-icon">
                    <i class="fab fa-aws"></i>
                </div>
                <div class="tech-name">AWS</div>
            </div>

            <div class="tech-item">
                <div class="tech-icon">
                    <i class="fab fa-docker"></i>
                </div>
                <div class="tech-name">Docker</div>
            </div>

            <div class="tech-item">
                <div class="tech-icon">
                    <i class="fab fa-git-alt"></i>
                </div>
                <div class="tech-name">Git</div>
            </div>
        </div>
    </section>

    <!-- Final CTA Section -->
    <section class="cta-section">
        <div class="cta-content">
            <h2>Ready to Join Amazing Companies?</h2>
            <p>Apply now and take the next step in your career</p>

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

            <a href="<?= appUrl('pages/apply.php') ?>" class="btn-primary" style="display: inline-block; margin-top: 2rem;">
                <i class="fas fa-rocket"></i> Start Your Application
            </a>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>
