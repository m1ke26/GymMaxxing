<?php
require_once __DIR__ . '/../utils/session.php';
startSession();
$pageTitle = null;
require_once __DIR__ . '/../templates/header.php';
?>

        <section id="FirstBanner">
            <img src="/images/homepage_img1.png" alt="Greek Man holding dumbbells on top of a Mountain with an ancient greek landscape">
        </section>

        <section id="services">
            <h1>WORK ON YOURSELF</h1>
            <ul class="line1">
                <li>
                    <a href="/pages/classes.php">
                        <img src="/images/homepage_outdoor.png" alt="Outdoor Classes">
                        <h3>OUT-DOOR CLASSES</h3>
                    </a>
                </li>
                <li>
                    <a href="/pages/classes.php">
                        <img src="/images/homepage_indoor.png" alt="Indoor Classes">
                        <h3>IN-DOOR CLASSES</h3>
                    </a>
                </li>
                <li>
                    <a href="/pages/classes.php">
                        <img src="/images/homepage_wellness.png" alt="Wellness">
                        <h3>WELLNESS</h3>
                    </a>
                </li>
            </ul>
            <ul class="line2">
                <li>
                    <a href="/pages/services.php">
                        <img src="/images/homepage_nutrition.png" alt="Nutrition">
                        <h3>NUTRITION</h3>
                    </a>
                </li>
                <li>
                    <a href="/pages/services.php">
                        <img src="/images/homepage_massages.png" alt="Massages">
                        <h3>MASSAGES</h3>
                    </a>
                </li>
                <li>
                    <a href="/pages/services.php">
                        <img src="/images/homepage_videos.png" alt="Videos">
                        <h3>VIDEOS</h3>
                    </a>
                </li>
            </ul>
        </section>

        <section id="SecondBanner">
            <img src="/images/homepage_img2.png" alt="Greek Man Holding Pillars with Chains on top of a Mountain with an ancient greek landscape">
        </section>

        <section id="subscriptions">
            <h2>SUBSCRIBE NOW</h2>
            <ul>
                <li>
                    <a href="/pages/joinus.php">
                        <img src="/images/homepage_tier1.png" alt="Citizen Tier">
                        <h3>CITIZEN</h3>
                    </a>
                    <p>2 DAYS A WEEK (flexible schedule)</p>
                </li>
                <li>
                    <a href="/pages/joinus.php">
                        <img src="/images/homepage_tier2.png" alt="Olympian Tier">
                        <h3>OLYMPIAN</h3>
                    </a>
                    <p>COMPLETELY FREE SCHEDULE</p>
                </li>
                <li>
                    <a href="/pages/joinus.php">
                        <img src="/images/homepage_tier3.png" alt="Zeus Tier">
                        <h3>ZEUS</h3>
                    </a>
                    <p>COMPLETE FREE TIME + WELLNESS + TOWELS</p>
                </li>
            </ul>
            <a href="/pages/joinus.php" class="button-join">JOIN US</a>
        </section>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
