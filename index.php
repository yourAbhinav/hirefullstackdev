<?php
require_once 'includes/helpers.php';
startSecureSession();

$page_title = "DevHire - Hire Top Full Stack Developers";
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

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

        <form
class="apply-form"
method="POST"
    action="<?= appUrl('pages/apply.php') ?>"
enctype="multipart/form-data">
            <div class="form-group">
                <label for="fullName">Full Name</label>
                <input type="text" id="full_name" name="full_name" placeholder="Enter your full name" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
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
                <label for="resume">Upload Resume</label>
                <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx" onchange="DevHire.handleFileUpload(this)">
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane"></i> Apply Now
            </button>
        </form>
    </section>

    <!-- Featured Jobs Section -->
    <section class="featured-jobs">
        <div class="section-title">
            <h2>Featured Open Positions</h2>
            <p>Explore amazing opportunities at leading companies</p>
        </div>

        <div class="jobs-grid">
            <!-- Full Stack Developer -->
            <div class="job-card">
                <div class="job-header">
                    <h3 class="job-title">Full Stack Developer</h3>
                    <p class="job-company">Tech Startup Inc.</p>
                </div>

                <div class="job-details">
                    <div class="job-meta">
                        <span class="meta-item">
                            <i class="fas fa-briefcase"></i> 3-5 Years
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-map-marker-alt"></i> Remote
                        </span>
                    </div>

                    <div class="job-tags">
                        <span class="tag">React</span>
                        <span class="tag">Node.js</span>
                        <span class="tag">MongoDB</span>
                    </div>

                    <div class="job-salary">$150k - $250k /yr</div>
                </div>

                <div class="job-footer">
                    <a href="<?= appUrl('pages/apply.php') ?>" class="btn-apply-job">Apply</a>
                    <button class="save-job" onclick="DevHire.saveJob(1, this)">
                        <i class="far fa-bookmark"></i>
                    </button>
                </div>
            </div>

            <!-- Frontend Developer -->
            <div class="job-card">
                <div class="job-header">
                    <h3 class="job-title">Frontend Developer</h3>
                    <p class="job-company">NextGen Solutions</p>
                </div>

                <div class="job-details">
                    <div class="job-meta">
                        <span class="meta-item">
                            <i class="fas fa-briefcase"></i> 2-4 Years
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-map-marker-alt"></i> Hybrid
                        </span>
                    </div>

                    <div class="job-tags">
                        <span class="tag">Vue.js</span>
                        <span class="tag">TypeScript</span>
                        <span class="tag">Tailwind</span>
                    </div>

                    <div class="job-salary">$120k - $180k /yr</div>
                </div>

                <div class="job-footer">
                    <a href="<?= appUrl('pages/apply.php') ?>" class="btn-apply-job">Apply</a>
                    <button class="save-job" onclick="DevHire.saveJob(2, this)">
                        <i class="far fa-bookmark"></i>
                    </button>
                </div>
            </div>

            <!-- Backend Developer -->
            <div class="job-card">
                <div class="job-header">
                    <h3 class="job-title">Backend Developer</h3>
                    <p class="job-company">Enterprise Systems</p>
                </div>

                <div class="job-details">
                    <div class="job-meta">
                        <span class="meta-item">
                            <i class="fas fa-briefcase"></i> 3-6 Years
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-map-marker-alt"></i> Remote
                        </span>
                    </div>

                    <div class="job-tags">
                        <span class="tag">Python</span>
                        <span class="tag">PostgreSQL</span>
                        <span class="tag">Docker</span>
                    </div>

                    <div class="job-salary">$130k - $210k /yr</div>
                </div>

                <div class="job-footer">
                    <a href="<?= appUrl('pages/apply.php') ?>" class="btn-apply-job">Apply</a>
                    <button class="save-job" onclick="DevHire.saveJob(3, this)">
                        <i class="far fa-bookmark"></i>
                    </button>
                </div>
            </div>

            <!-- DevOps Engineer -->
            <div class="job-card">
                <div class="job-header">
                    <h3 class="job-title">DevOps Engineer</h3>
                    <p class="job-company">Cloud Innovators</p>
                </div>

                <div class="job-details">
                    <div class="job-meta">
                        <span class="meta-item">
                            <i class="fas fa-briefcase"></i> 4-7 Years
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-map-marker-alt"></i> Remote
                        </span>
                    </div>

                    <div class="job-tags">
                        <span class="tag">Kubernetes</span>
                        <span class="tag">AWS</span>
                        <span class="tag">CI/CD</span>
                    </div>

                    <div class="job-salary">$140k - $220k /yr</div>
                </div>

                <div class="job-footer">
                    <a href="<?= appUrl('pages/apply.php') ?>" class="btn-apply-job">Apply</a>
                    <button class="save-job" onclick="DevHire.saveJob(4, this)">
                        <i class="far fa-bookmark"></i>
                    </button>
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 3rem;">
            <a href="/DevHire/pages/jobs.php" class="btn-primary">
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

            <a href="/DevHire/pages/apply.php" class="btn-primary" style="display: inline-block; margin-top: 2rem;">
                <i class="fas fa-rocket"></i> Start Your Application
            </a>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>
