<?php
$page_title = "Developers - DevHire";
$css_path = "/DevHire/assets/css/style.css";
$js_path = "/DevHire/assets/js/main.js";

include '../includes/header.php';
include '../includes/navbar.php';
?>

    <!-- Page Header -->
    <section style="padding: 4rem 2rem; text-align: center; background: rgba(30, 41, 59, 0.3);">
        <div style="max-width: 1400px; margin: 0 auto;">
            <h1>Our Talent Pool</h1>
            <p class="quick-apply-subtitle">Browse our community of talented developers</p>
        </div>
    </section>

    <!-- Developer Profiles -->
    <section class="featured-jobs">
        <!-- Filter Section -->
        <div style="background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(10px); border: 1px solid rgba(124, 58, 237, 0.2); border-radius: 1rem; padding: 2rem; margin-bottom: 3rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                <div class="form-group">
                    <label>Specialization</label>
                    <select>
                        <option>All Specializations</option>
                        <option>Frontend</option>
                        <option>Backend</option>
                        <option>Full Stack</option>
                        <option>DevOps</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Experience</label>
                    <select>
                        <option>All Levels</option>
                        <option>Junior (0-2 years)</option>
                        <option>Mid (2-5 years)</option>
                        <option>Senior (5+ years)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <select>
                        <option>Any Location</option>
                        <option>Remote</option>
                        <option>USA</option>
                        <option>Europe</option>
                        <option>Asia</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Developer Cards Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2rem;">
            <?php for ($i = 0; $i < 12; $i++): ?>
                <div class="feature-card" style="display: flex; flex-direction: column;">
                    <div style="width: 100%; height: 120px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 0.8rem; margin-bottom: 1rem;"></div>
                    
                    <h3 style="margin-bottom: 0.3rem;">Developer <?php echo ($i + 1); ?></h3>
                    <p style="color: var(--text-tertiary); font-size: 0.9rem; margin-bottom: 0.5rem;">
                        <?php 
                        $roles = ['Full Stack Developer', 'Frontend Developer', 'Backend Developer', 'DevOps Engineer'];
                        echo $roles[$i % count($roles)];
                        ?>
                    </p>
                    
                    <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem; flex-wrap: wrap;">
                        <span class="tag">React</span>
                        <span class="tag">Node.js</span>
                        <span class="tag">TypeScript</span>
                    </div>

                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1rem; flex-grow: 1;">
                        Experienced developer with <?php echo (2 + ($i % 8)); ?> years of expertise in modern web technologies.
                    </p>

                    <div style="display: flex; gap: 1rem;">
                        <button style="flex: 1; padding: 0.6rem; background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white; border: none; border-radius: 0.4rem; font-weight: 600; cursor: pointer; text-decoration: none;">
                            View Profile
                        </button>
                        <button style="flex: 1; padding: 0.6rem; background: transparent; border: 1px solid var(--border-color); color: var(--text-secondary); border-radius: 0.4rem; font-weight: 600; cursor: pointer;">
                            Contact
                        </button>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </section>

    <!-- Pagination -->
    <div style="text-align: center; padding: 3rem 2rem;">
        <div style="display: flex; justify-content: center; gap: 0.5rem;">
            <button class="btn-secondary" style="padding: 0.6rem 1rem;">Previous</button>
            <button class="btn-primary" style="padding: 0.6rem 1rem;">1</button>
            <button class="btn-secondary" style="padding: 0.6rem 1rem;">2</button>
            <button class="btn-secondary" style="padding: 0.6rem 1rem;">3</button>
            <button class="btn-secondary" style="padding: 0.6rem 1rem;">Next</button>
        </div>
    </div>

<?php include '../includes/footer.php'; ?>
