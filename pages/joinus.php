<?php
require_once __DIR__ . '/../utils/session.php';
startSession();
$pageTitle = 'Memberships';
require_once __DIR__ . '/../templates/header.php';
?>

        <section class="choice-section">
            <h1>CHOOSE YOUR ASCENSION</h1>
            <p class="subtitle">Leave the mundane training behind.</p>
        </section>

        <section id="pricing-plans">
            <article class="plan-card">
                <header class="plan-header">
                    <div class="tier-img-wrapper">
                        <img src="/images/homepage_tier1.png" alt="Citizen Icon">
                        <h2>CITIZEN</h2>
                    </div>
                    <p class="plan-subtitle">OFF-PEAK ACCESS</p>
                    <p class="schedule-highlight">06:00 - 09:30 | 20:00 - 00:00</p>
                </header>
                <div class="price-tag">
                    <span class="currency">&euro;20.90</span>
                    <span class="period">/ month</span>
                </div>
                <?php if (isLoggedIn() && hasRole('member')): ?>
                    <button class="button-join tier-select-btn" data-tier="citizen">SELECT &raquo;</button>
                <?php else: ?>
                    <a href="/pages/login.php" class="button-join">SELECT &raquo;</a>
                <?php endif; ?>
                <ul class="features-list">
                    <li>Access to all land-based classes</li>
                    <li>Max 2 class enrollments</li>
                    <li>Restricted schedule</li>
                    <li>Gym floor access only</li>
                    <li>Mandatory 52-week contract</li>
                </ul>
            </article>

            <article class="plan-card featured">
                <div class="ribbon">MOST POPULAR</div>
                <header class="plan-header">
                    <div class="tier-img-wrapper">
                        <img src="/images/homepage_tier2.png" alt="Olympian Icon">
                        <h2>OLYMPIAN</h2>
                    </div>
                    <p class="plan-subtitle">TOTAL ACCESS</p>
                    <p class="schedule-highlight">06:00 - 00:00</p>
                </header>
                <div class="price-tag">
                    <span class="currency">&euro;40.80</span>
                    <span class="period">/ month</span>
                </div>
                <?php if (isLoggedIn() && hasRole('member')): ?>
                    <button class="button-join tier-select-btn" data-tier="olympian">SELECT &raquo;</button>
                <?php else: ?>
                    <a href="/pages/login.php" class="button-join">SELECT &raquo;</a>
                <?php endif; ?>
                <ul class="features-list">
                    <li>Full equipment access</li>
                    <li>Max 5 class enrollments</li>
                    <li>Group land classes included</li>
                    <li>Business hours free access</li>
                    <li>Quarterly nutrition check</li>
                    <li>Showers included</li>
                    <li>Mandatory 52-week contract</li>
                </ul>
            </article>

            <article class="plan-card">
                <header class="plan-header">
                    <div class="tier-img-wrapper">
                        <img src="/images/homepage_tier3.png" alt="Zeus Icon">
                        <h2>ZEUS</h2>
                    </div>
                    <p class="plan-subtitle">ULTIMATE ACCESS</p>
                    <p class="schedule-highlight">24-HOUR ACADEMY</p>
                </header>
                <div class="price-tag">
                    <span class="currency">&euro;60.80</span>
                    <span class="period">/ month</span>
                </div>
                <?php if (isLoggedIn() && hasRole('member')): ?>
                    <button class="button-join tier-select-btn" data-tier="zeus">SELECT &raquo;</button>
                <?php else: ?>
                    <a href="/pages/login.php" class="button-join">SELECT &raquo;</a>
                <?php endif; ?>
                <ul class="features-list">
                    <li>Unlimited class enrollments</li>
                    <li>Land &amp; Water classes access</li>
                    <li>24-hour gym access</li>
                    <li>Weekly nutrition coaching</li>
                    <li>Exclusive Personal Trainer</li>
                    <li>Showers &amp; Towels included</li>
                    <li>Flexible 26-week contract</li>
                </ul>
            </article>
        </section>

        <input type="hidden" id="csrf-token" value="<?= htmlspecialchars($csrfToken) ?>">

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
