<?php
require_once '../includes/helpers.php';

startSecureSession();

$page_title = 'Payment Portal - DevHire';
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

$plans = [
    'starter' => [
        'name' => 'Starter',
        'price' => 'Free',
        'billing' => 'Forever',
        'description' => 'Best for job seekers who want to browse and apply.',
        'features' => ['Browse jobs', 'Apply to positions', 'Save up to 5 jobs'],
        'cta' => 'Continue Free',
    ],
    'professional' => [
        'name' => 'Professional',
        'price' => '$9',
        'billing' => 'per month',
        'description' => 'For active job seekers who want more visibility and alerts.',
        'features' => ['Unlimited saved jobs', 'Priority support', 'Job alerts'],
        'cta' => 'Proceed to Checkout',
    ],
    'enterprise' => [
        'name' => 'Enterprise',
        'price' => 'Custom',
        'billing' => 'Sales-led',
        'description' => 'For companies and teams that need a tailored plan.',
        'features' => ['Unlimited listings', 'Dedicated account manager', 'Custom integrations'],
        'cta' => 'Talk to Sales',
    ],
];

$planKey = strtolower(trim((string) ($_GET['plan'] ?? 'professional')));
if (!isset($plans[$planKey])) {
    $planKey = 'professional';
}
$plan = $plans[$planKey];

$currentRole = currentUserRole();
$headlineName = $plan['name'];
$checkoutAnchor = '#checkout';

include '../includes/header.php';
include '../includes/navbar.php';
?>

<section class="payment-shell">
    <div class="payment-hero">
        <div>
            <span class="eyebrow">Secure Checkout</span>
            <h1><?= htmlspecialchars($headlineName, ENT_QUOTES, 'UTF-8') ?> Plan</h1>
            <p class="payment-subtitle">Review your selected plan and continue to payment. This portal is ready for a real gateway integration when you connect one.</p>
        </div>
        <div class="payment-plan-card">
            <div class="payment-plan-top">
                <strong><?= htmlspecialchars($plan['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                <span><?= htmlspecialchars($plan['billing'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="payment-price"><?= htmlspecialchars($plan['price'], ENT_QUOTES, 'UTF-8') ?></div>
            <p><?= htmlspecialchars($plan['description'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </div>

    <div class="payment-grid">
        <section class="payment-panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">What you get</span>
                    <h2>Plan benefits</h2>
                </div>
            </div>
            <ul class="payment-feature-list">
                <?php foreach ($plan['features'] as $feature): ?>
                    <li><i class="fas fa-check-circle"></i> <?= htmlspecialchars($feature, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="payment-panel payment-checkout-panel" id="checkout">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Checkout</span>
                    <h2>Continue securely</h2>
                </div>
            </div>

            <div class="payment-summary">
                <div><span>Plan</span><strong><?= htmlspecialchars($plan['name'], ENT_QUOTES, 'UTF-8') ?></strong></div>
                <div><span>Billing</span><strong><?= htmlspecialchars($plan['billing'], ENT_QUOTES, 'UTF-8') ?></strong></div>
                <div><span>Status</span><strong>Ready</strong></div>
            </div>

            <p class="payment-note">No gateway is connected yet. This is the payment portal entry point, so you can wire Stripe, PayPal, or any external processor here next.</p>

            <div class="payment-actions">
                <?php if ($planKey === 'enterprise'): ?>
                    <a class="btn-primary payment-btn" href="<?= appUrl('pages/contact.php') ?>">Talk to Sales</a>
                <?php elseif ($planKey === 'starter'): ?>
                    <a class="btn-primary payment-btn" href="<?= htmlspecialchars($checkoutAnchor, ENT_QUOTES, 'UTF-8') ?>">Continue Free</a>
                <?php else: ?>
                    <a class="btn-primary payment-btn" href="<?= htmlspecialchars($checkoutAnchor, ENT_QUOTES, 'UTF-8') ?>">Proceed to Payment</a>
                <?php endif; ?>
                <a class="btn-secondary payment-btn" href="<?= appUrl('pages/pricing.php') ?>">Back to Pricing</a>
            </div>
        </section>
    </div>
</section>

<style>
.payment-shell {
    width: min(1180px, calc(100% - 48px));
    margin: 0 auto;
    padding: 40px 0 80px;
}

.payment-hero {
    display: grid;
    grid-template-columns: minmax(0, 1.45fr) minmax(320px, 0.85fr);
    gap: 24px;
    align-items: stretch;
    margin-bottom: 28px;
}

.payment-hero h1 {
    font-size: clamp(2rem, 4vw, 3.1rem);
    line-height: 1.02;
    margin: 14px 0 12px;
    letter-spacing: -0.03em;
}

.payment-subtitle {
    max-width: 60ch;
    color: #64748b;
    line-height: 1.7;
    font-size: 0.98rem;
}

.payment-plan-card,
.payment-panel {
    background: linear-gradient(135deg, #0f172a 0%, #111827 58%, #1d4ed8 100%);
    border: 1px solid rgba(59, 130, 246, 0.18);
    border-radius: 20px;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.14);
    color: #e2e8f0;
}

.payment-plan-card {
    padding: 22px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.payment-plan-top {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 16px;
    margin-bottom: 18px;
}

.payment-price {
    font-size: clamp(1.9rem, 4vw, 2.9rem);
    font-weight: 800;
    color: #fff;
    margin-bottom: 10px;
}

.payment-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    gap: 24px;
}

.payment-panel {
    padding: 24px;
}

.payment-panel h2,
.payment-panel strong {
    color: #fff;
}

.payment-feature-list {
    list-style: none;
    padding: 0;
    margin: 12px 0 0;
    display: grid;
    gap: 14px;
}

.payment-feature-list li {
    display: flex;
    align-items: center;
    gap: 10px;
    color: rgba(226, 232, 240, 0.84);
    font-weight: 450;
    font-size: 0.95rem;
}

.payment-feature-list i {
    color: #16a34a;
}

.payment-summary {
    display: grid;
    gap: 14px;
    margin: 18px 0 14px;
}

.payment-summary div {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 14px 16px;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.08);
}

.payment-summary span {
    color: rgba(226, 232, 240, 0.72);
}

.payment-summary strong {
    color: #fff;
}

.payment-note {
    color: rgba(226, 232, 240, 0.76);
    line-height: 1.7;
    margin: 0 0 20px;
    font-size: 0.95rem;
}

.payment-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.payment-btn {
    min-width: 180px;
    text-align: center;
}

.payment-panel .btn-primary,
.payment-panel .btn-secondary {
    border-radius: 12px;
    padding: 12px 18px;
    font-weight: 600;
}

.payment-panel .btn-primary {
    background: linear-gradient(135deg, #0f172a, #1d4ed8);
    color: #fff;
}

.payment-panel .btn-secondary {
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.16);
}

@media (max-width: 960px) {
    .payment-hero,
    .payment-grid {
        grid-template-columns: 1fr;
    }

    .payment-shell {
        width: min(100% - 28px, 1180px);
        padding-top: 28px;
    }
}
</style>

<?php include '../includes/footer.php'; ?>