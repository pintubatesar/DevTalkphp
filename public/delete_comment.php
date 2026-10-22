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

$stmt = db()->prepare('SELECT user_id, topic_id FROM comments WHERE id = ?');
$stmt->execute([$id]);
$comment = $stmt->fetch();

$user = current_user();
if (!$comment) {
    http_response_code(404);
    exit('Comment not found.');
}

if (
    !$user
    || ((int) $comment['user_id'] !== (int) $user['id'] && (int) $user['is_admin'] !== 1)
) {
    http_response_code(403);
    exit('You are not allowed to delete this comment.');
}

$topicId = (int) $comment['topic_id'];
$delete = db()->prepare('DELETE FROM comments WHERE id = ?');
$delete->execute([$id]);

header('Location: topic.php?id=' . $topicId);
exit;
