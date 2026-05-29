<?php
require_once '../includes/helpers.php';

$page_title = "Testimonials - DevHire";
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

include '../includes/header.php';
include '../includes/navbar.php';
?>

    <!-- Page Header -->
    <section class="legal-hero">
        <div class="legal-hero-inner">
            <h1>Success Stories</h1>
            <p class="quick-apply-subtitle">Hear from developers and companies who found success with DevHire</p>
        </div>
    </section>

    <!-- Developer Testimonials -->
    <section class="testimonials-section">
        <div class="section-title">
            <h2>Developer Stories</h2>
            <p>Real experiences from developers who landed their dream jobs</p>
        </div>

        <div class="testimonials-grid">
            <?php for ($i = 0; $i < 9; $i++): ?>
                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        <?php for ($j = 0; $j < 5; $j++): ?>
                            <i class="fas fa-star <?= $j < 4 ? 'testimonial-star-active' : '' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="testimonial-quote">
                        "DevHire helped me find an incredible remote position with a team I truly enjoy working with. The process was smooth and the support was amazing!"
                    </p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar"><?php echo substr(array('John D', 'Sarah M', 'Raj P', 'Emma L', 'Mike T', 'Lisa C', 'Alex K', 'Anna B', 'David R')[$i], 0, 1); ?><?php echo substr(array('John D', 'Sarah M', 'Raj P', 'Emma L', 'Mike T', 'Lisa C', 'Alex K', 'Anna B', 'David R')[$i], 6, 1); ?></div>
                        <div class="testimonial-info">
                            <h4><?php echo array('John Davis', 'Sarah Miller', 'Raj Patel', 'Emma Lopez', 'Mike Thompson', 'Lisa Chen', 'Alex Kumar', 'Anna Brown', 'David Rodriguez')[$i]; ?></h4>
                            <p class="testimonial-role">
                                <?php 
                                $roles = ['Full Stack Developer', 'Frontend Developer', 'Backend Developer', 'DevOps Engineer', 'MERN Developer'];
                                echo $roles[$i % count($roles)];
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </section>

    <!-- Company Testimonials -->
    <section class="testimonials-company-section">
        <div class="section-title">
            <h2>Company Reviews</h2>
            <p>What hiring managers say about DevHire</p>
        </div>

        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-stars">
                    <i class="fas fa-star testimonial-star-active"></i>
                    <i class="fas fa-star testimonial-star-active"></i>
                    <i class="fas fa-star testimonial-star-active"></i>
                    <i class="fas fa-star testimonial-star-active"></i>
                    <i class="fas fa-star testimonial-star-active"></i>
                </div>
                <p class="testimonial-quote">
                    "DevHire has been a game-changer for our hiring process. We found top-tier talent in record time and the candidates were pre-vetted."
                </p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">TC</div>
                    <div class="testimonial-info">
                        <h4>Tech Innovations Inc.</h4>
                        <p class="testimonial-role">Hiring Manager - Sarah Johnson</p>
                    </div>
                </div>
            </div>

            <div class="testimonial-card">
                <div class="testimonial-stars">
                    <i class="fas fa-star testimonial-star-active"></i>
                    <i class="fas fa-star testimonial-star-active"></i>
                    <i class="fas fa-star testimonial-star-active"></i>
                    <i class="fas fa-star testimonial-star-active"></i>
                    <i class="fas fa-star-half-alt testimonial-star-active"></i>
                </div>
                <p class="testimonial-quote">
                    "We reduced our hiring cycle from 3 months to just 4 weeks. The quality of candidates is exceptional and the platform is very user-friendly."
                </p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">ES</div>
                    <div class="testimonial-info">
                        <h4>Enterprise Solutions LLC</h4>
                        <p class="testimonial-role">CTO - Michael Chen</p>
                    </div>
                </div>
            </div>

            <div class="testimonial-card">
                <div class="testimonial-stars">
                    <i class="fas fa-star testimonial-star-active"></i>
                    <i class="fas fa-star testimonial-star-active"></i>
                    <i class="fas fa-star testimonial-star-active"></i>
                    <i class="fas fa-star testimonial-star-active"></i>
                    <i class="fas fa-star testimonial-star-active"></i>
                </div>
                <p class="testimonial-quote">
                    "Outstanding platform! The customer support is responsive, the candidates are talented, and the ROI is incredible."
                </p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">SF</div>
                    <div class="testimonial-info">
                        <h4>StartUp Finance Group</h4>
                        <p class="testimonial-role">HR Director - Jessica Martinez</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics -->
    <section class="stats-section">
        <div class="section-title">
            <h2>Our Track Record</h2>
            <p>Proven results speak for themselves</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">4.9/5</div>
                <div class="stat-label">Average Rating</div>
            </div>

            <div class="stat-card">
                <div class="stat-number">95%</div>
                <div class="stat-label">Satisfaction Rate</div>
            </div>

            <div class="stat-card">
                <div class="stat-number">2 Weeks</div>
                <div class="stat-label">Avg. Time to Hire</div>
            </div>

            <div class="stat-card">
                <div class="stat-number">500+</div>
                <div class="stat-label">Successful Placements</div>
            </div>
        </div>
    </section>

<?php include '../includes/footer.php'; ?>
