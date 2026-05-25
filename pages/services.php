<?php
require_once __DIR__ . '/../utils/session.php';
startSession();
$pageTitle = 'Services';
require_once __DIR__ . '/../templates/header.php';
?>

        <section class="page-hero">
            <h1>OUR SERVICES</h1>
            <p>Everything you need to reach your maximum potential</p>
        </section>

        <section id="svc-grid">
            <article class="svc-card">
                <h2 class="svc-card-label">OUT-DOOR CLASSES</h2>
                <div class="svc-card-body">
                    <p>Train under the open sky. Our outdoor classes combine fresh air with intense workouts to push
                        your limits beyond the gym walls.</p>
                    <a href="/pages/classes.php" class="svc-btn">LEARN MORE</a>
                </div>
            </article>

            <article class="svc-card">
                <h2 class="svc-card-label">IN-DOOR CLASSES</h2>
                <div class="svc-card-body">
                    <p>State-of-the-art equipment and expert trainers await you inside. From strength training to
                        cardio, we have everything covered.</p>
                    <a href="/pages/classes.php" class="svc-btn">LEARN MORE</a>
                </div>
            </article>

            <article class="svc-card">
                <h2 class="svc-card-label">WELLNESS</h2>
                <div class="svc-card-body">
                    <p>Balance your body and mind. Our wellness programs are designed to restore energy, reduce stress
                        and keep you at your peak.</p>
                    <a href="/pages/classes.php" class="svc-btn">LEARN MORE</a>
                </div>
            </article>

            <article class="svc-card">
                <h2 class="svc-card-label">NUTRITION</h2>
                <div class="svc-card-body">
                    <p>Fuel your performance. Our certified nutritionists create personalized meal plans tailored to
                        your training goals.</p>
                    <a href="/pages/contact.php" class="svc-btn">BOOK NOW</a>
                </div>
            </article>

            <article class="svc-card">
                <h2 class="svc-card-label">MASSAGES</h2>
                <div class="svc-card-body">
                    <p>Recover faster and perform better. Our professional massage therapists specialize in sports
                        recovery and muscle relief.</p>
                    <a href="/pages/contact.php" class="svc-btn">BOOK NOW</a>
                </div>
            </article>

            <article class="svc-card">
                <h2 class="svc-card-label">VIDEOS</h2>
                <div class="svc-card-body">
                    <p>Train anywhere, anytime. Access our exclusive library of workout videos led by our expert
                        trainers at your own pace.</p>
                    <a href="/pages/contact.php" class="svc-btn">COMING SOON</a>
                </div>
            </article>
        </section>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
