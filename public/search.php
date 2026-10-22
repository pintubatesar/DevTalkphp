<?php
require_once __DIR__ . '/../src/helpers.php';

$q = $_GET['q'] ?? '';

$results = [];
$sqlError = '';

if ($q !== '') {
    try {
        $stmt = db()->prepare(
            'SELECT id, title, body
             FROM topics
             WHERE title LIKE :title_query OR body LIKE :body_query
             ORDER BY created_at DESC'
        );
        $likeQuery = '%' . $q . '%';
        $stmt->execute([
            ':title_query' => $likeQuery,
            ':body_query' => $likeQuery,
        ]);
        $results = $stmt->fetchAll();
    } catch (PDOException $e) {
        $sqlError = 'Search failed. Please try again later.';
    }
}

render_header('Search');
?>
<h1 class="h4 mb-3">Search</h1>

<form method="get" class="mb-4">
    <div class="input-group">
        <input name="q" class="form-control" value="<?= htmlspecialchars($q) ?>" placeholder="Search topics…">
        <button class="btn btn-primary">Search</button>
    </div>
</form>

<?php if ($q !== ''): ?>
    <p class="text-body-secondary">Results for: <?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<?php if ($sqlError): ?>
    <div class="alert alert-danger"><strong>Database error:</strong> <?= htmlspecialchars($sqlError) ?></div>
<?php endif; ?>

<?php if ($q !== '' && !$results && !$sqlError): ?>
    <p class="text-body-secondary">No matches found.</p>
<?php endif; ?>

<div class="vstack gap-3">
    <?php foreach ($results as $r): ?>
        <div class="card">
            <div class="card-body">
                <h2 class="h6 mb-1">
                    <a href="topic.php?id=<?= (int) $r['id'] ?>"><?= htmlspecialchars($r['title']) ?></a>
                </h2>
                <p class="small text-body-secondary mb-0">
                    <?= htmlspecialchars(mb_substr(strip_tags($r['body']), 0, 160)) ?>
                </p>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php render_footer(); ?>