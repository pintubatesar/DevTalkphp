<?php
require_once __DIR__ . '/db.php';

header("Content-Security-Policy-Report-Only: default-src 'self'; "
    . "script-src 'self'; "
    . "style-src 'self'; "
    . "img-src 'self' data:; "
    . "object-src 'none'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'");
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains'); // only honoured over HTTPS
header_remove('X-Powered-By');

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    // 'secure' => true,   // enable once served over HTTPS
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function require_csrf_token(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (!is_string($submitted) || !hash_equals(csrf_token(), $submitted)) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }
}

function render_header(string $title = APP_NAME): void
{
    $user = current_user();
?>
    <!doctype html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= htmlspecialchars($title) ?> · <?= APP_NAME ?></title>
        <link href="assets/bootstrap.min.css" rel="stylesheet">
        <link href="assets/app.css" rel="stylesheet">
    </head>

    <body class="bg-body-tertiary">
        <nav class="navbar navbar-expand-lg bg-dark" data-bs-theme="dark">
            <div class="container">
                <a class="navbar-brand fw-semibold" href="index.php">💬 <?= APP_NAME ?></a>
                <div class="d-flex align-items-center gap-2">
                    <form class="d-flex me-2" role="search" action="search.php" method="get">
                        <input class="form-control form-control-sm" type="search" name="q"
                            placeholder="Search topics…" aria-label="Search">
                    </form>
                    <?php if ($user): ?>
                        <a class="btn btn-sm btn-primary" href="create_topic.php">New Topic</a>
                        <a class="btn btn-sm btn-outline-light" href="profile.php?id=<?= $user['id'] ?>">
                            <?= htmlspecialchars($user['username']) ?>
                        </a>
                        <form method="post" action="logout.php" class="d-inline">
                            <input type="hidden" name="csrf_token"
                                value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                            <button class="btn btn-sm btn-outline-light">Log out</button>
                        </form>
                    <?php else: ?>
                        <a class="btn btn-sm btn-outline-light" href="login.php">Log in</a>
                        <a class="btn btn-sm btn-primary" href="register.php">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
        <main class="container py-4">
        <?php
    }

    function render_footer(): void
    {
        ?>
        </main>
        <footer class="container text-center text-body-secondary py-4 small">
            <?= APP_NAME ?> — CST8265 lab application. For educational use only.
        </footer>
        <script src="assets/bootstrap.bundle.min.js"></script>
        <script src="assets/app.js" defer></script>
    </body>

    </html>
<?php
    }

    function fmt_date(?string $ts): string
    {
        if (!$ts) {
            return '';
        }
        return date('M j, Y g:i A', strtotime($ts));
    }
