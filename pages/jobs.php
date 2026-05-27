<?php
$page_title = "Jobs - DevHire";
$css_path = "/DevHire/assets/css/style.css";
$js_path = "/DevHire/assets/js/main.js";

include '../includes/header.php';
include '../includes/navbar.php';
?>

    <!-- Page Header -->
    <section style="padding: 4rem 2rem; text-align: center; background: rgba(30, 41, 59, 0.3);">
        <div style="max-width: 1400px; margin: 0 auto;">
            <h1>Job Opportunities</h1>
            <p class="quick-apply-subtitle">Find your next amazing opportunity at top companies</p>
        </div>
    </section>

    <!-- Filter and Jobs Section -->
    <section class="featured-jobs">
        <!-- Filter Options -->
        <div style="background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(10px); border: 1px solid rgba(124, 58, 237, 0.2); border-radius: 1rem; padding: 2rem; margin-bottom: 3rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                <div class="form-group">
                    <label>Search Position</label>
                    <input type="text" placeholder="e.g. Frontend Developer" id="searchInput">
                </div>
                <div class="form-group">
                    <label>Experience Level</label>
                    <select id="experienceFilter">
                        <option value="">All Levels</option>
                        <option value="0-2">Entry Level</option>
                        <option value="2-5">Mid Level</option>
                        <option value="5+">Senior Level</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Work Type</label>
                    <select id="workTypeFilter">
                        <option value="">All Types</option>
                        <option value="remote">Remote</option>
                        <option value="hybrid">Hybrid</option>
                        <option value="on-site">On-site</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Salary Range</label>
                    <select id="salaryFilter">
                        <option value="">Any</option>
                        <option value="0-100">$0 - $100k</option>
                        <option value="100-150">$100k - $150k</option>
                        <option value="150-200">$150k - $200k</option>
                        <option value="200+">$200k+</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Jobs Grid -->
        <div class="jobs-grid">
            <?php for ($i = 0; $i < 12; $i++): ?>
                <div class="job-card">
                    <div class="job-header">
                        <h3 class="job-title">
                            <?php 
                            $titles = [
                                'Full Stack Developer',
                                'Frontend Developer', 
                                'Backend Developer',
                                'DevOps Engineer',
                                'MERN Stack Developer',
                                'React Native Developer'
                            ];
                            echo $titles[$i % count($titles)];
                            ?>
                        </h3>
                        <p class="job-company">Tech Company <?php echo ($i + 1); ?></p>
                    </div>

                    <div class="job-details">
                        <div class="job-meta">
                            <span class="meta-item">
                                <i class="fas fa-briefcase"></i> <?php echo (2 + ($i % 5)) . '-' . (5 + ($i % 5)); ?> Years
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php 
                                $locations = ['Remote', 'Hybrid', 'On-site'];
                                echo $locations[$i % 3];
                                ?>
                            </span>
                        </div>

                        <div class="job-tags">
                            <span class="tag">React</span>
                            <span class="tag">Node.js</span>
                            <span class="tag">TypeScript</span>
                        </div>

                        <div class="job-salary">$<?php echo (100 + ($i * 5)) . 'k - $' . (150 + ($i * 5)) . 'k'; ?> /yr</div>
                    </div>

                    <div class="job-footer">
                        <a href="/DevHire/pages/apply.php" class="btn-apply-job">Apply</a>
                        <button class="save-job" onclick="DevHire.saveJob(<?php echo $i; ?>, this)">
                            <i class="far fa-bookmark"></i>
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
