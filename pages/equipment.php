<?php
declare(strict_types=1);

require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../database/connection.db.php';
require_once __DIR__ . '/../database/equipment.class.php';

startSession();
$pageTitle = 'Equipment';

$db        = getDatabaseConnection();
$equipment = Equipment::getAll($db);

// Group by type for filter
$types = [];
foreach ($equipment as $item) {
    if (!in_array($item->type, $types)) {
        $types[] = $item->type;
    }
}
sort($types);

require_once __DIR__ . '/../templates/header.php';
?>

        <section class="page-hero">
            <h1>EQUIPMENT</h1>
            <p>State-of-the-art tools for your ascension</p>
        </section>

        <section class="equipment-filters">
            <select id="equipment-filter" aria-label="Filter by type">
                <option value="">ALL TYPES</option>
                <?php foreach ($types as $type): ?>
                    <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars(strtoupper($type)) ?></option>
                <?php endforeach; ?>
            </select>
        </section>

        <section class="equipment-grid" id="equipment-grid">
            <?php foreach ($equipment as $item):
                $statusClass = 'status-' . str_replace('_', '-', $item->status);
                $statusLabel = str_replace('_', ' ', ucfirst($item->status));
            ?>
            <article class="equipment-card" data-type="<?= htmlspecialchars($item->type) ?>">
                <div class="equipment-header">
                    <h2><?= htmlspecialchars($item->name) ?></h2>
                    <span class="equipment-status <?= $statusClass ?>"><?= htmlspecialchars($statusLabel) ?></span>
                </div>
                <p class="equipment-type"><?= htmlspecialchars(strtoupper($item->type)) ?></p>
                <?php if ($item->description): ?>
                    <p class="equipment-description"><?= htmlspecialchars($item->description) ?></p>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
        </section>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
