<?php
require_once __DIR__ . '/../src/helpers.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

require_csrf_token();

$id = $_POST['id'] ?? '';

$stmt = db()->prepare('SELECT user_id FROM topics WHERE id = ?');
$stmt->execute([$id]);
$topic = $stmt->fetch();

$user = current_user();
if (!$topic) {
    http_response_code(404);
    exit('Topic not found.');
}

if (!$user || ((int) $topic['user_id'] !== (int) $user['id'] && (int) $user['is_admin'] !== 1)) {
    http_response_code(403);
    exit('You are not allowed to delete this topic.');
}

$delete = db()->prepare('DELETE FROM topics WHERE id = ?');
$delete->execute([$id]);

header('Location: index.php');
exit;
