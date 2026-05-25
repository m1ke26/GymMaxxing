<?php
require_once __DIR__ . '/../utils/session.php';
startSession();
$pageTitle = 'Contact';

$successMsg = getFlash('success');

require_once __DIR__ . '/../templates/header.php';
?>

        <section class="page-hero">
            <h1>CONTACT US</h1>
            <p>Reach out to Olympus</p>
        </section>

        <section class="contact-content">
            <div class="contact-info">
                <h2>Find Us</h2>
                <address>
                    <p>123 Olympus Avenue</p>
                    <p>4200-465 Porto, Portugal</p>
                    <p>Phone: +351 912 000 000</p>
                    <p>Email: info@gymmaxxing.com</p>
                </address>

                <h2>Opening Hours</h2>
                <table class="hours-table">
                    <tr><td>Monday &mdash; Friday</td><td>06:00 &mdash; 00:00</td></tr>
                    <tr><td>Saturday</td><td>08:00 &mdash; 22:00</td></tr>
                    <tr><td>Sunday</td><td>08:00 &mdash; 20:00</td></tr>
                </table>
            </div>

            <div class="contact-form-wrapper">
                <h2>Send a Message</h2>
                <?php if ($successMsg): ?>
                    <div class="flash-success"><?= htmlspecialchars($successMsg) ?></div>
                <?php endif; ?>
                <form class="contact-form" action="/actions/contact.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="text"  name="name"    placeholder="Name *"    required>
                    <input type="email" name="email"   placeholder="Email *"   required>
                    <input type="text"  name="subject" placeholder="Subject">
                    <textarea           name="message" placeholder="Your message *" required></textarea>
                    <button type="submit">SEND MESSAGE</button>
                </form>
            </div>
        </section>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
