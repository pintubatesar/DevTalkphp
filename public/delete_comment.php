<?php
require_once __DIR__ . '/../src/helpers.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

require_csrf_token();

$id      = $_POST['id'] ?? '';
$topicId = $_POST['topic_id'] ?? '';

$stmt = db()->prepare('DELETE FROM comments WHERE id = ?');
$stmt->execute([$id]);

header('Location: topic.php?id=' . (int) $topicId);
exit;
