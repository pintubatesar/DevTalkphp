<?php
require_once __DIR__ . '/../src/helpers.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

require_csrf_token();

$_SESSION = [];
session_destroy();
header('Location: index.php');
exit;
