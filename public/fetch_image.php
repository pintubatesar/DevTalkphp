<?php
require_once __DIR__ . '/../src/config.php';

$url = $_GET['url'] ?? '';

if (!is_string($url) || $url === '') {
    http_response_code(400);
    echo 'No url provided.';
    exit;
}

$parts = parse_url($url);
$scheme = is_array($parts) ? strtolower($parts['scheme'] ?? '') : '';
$host = is_array($parts) ? ($parts['host'] ?? '') : '';
$port = is_array($parts) ? ($parts['port'] ?? null) : null;

if (
    !in_array($scheme, ['http', 'https'], true)
    || $host === ''
    || isset($parts['user'], $parts['pass'])
    || ($port !== null && !in_array($port, [80, 443], true))
) {
    http_response_code(400);
    echo 'Only public HTTP(S) image URLs are allowed.';
    exit;
}

$addresses = filter_var($host, FILTER_VALIDATE_IP)
    ? [$host]
    : array_column(dns_get_record($host, DNS_A | DNS_AAAA) ?: [], 'ip');

if (!$addresses) {
    http_response_code(400);
    echo 'The image host could not be resolved.';
    exit;
}

foreach ($addresses as $address) {
    if (
        !filter_var(
            $address,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        )
    ) {
        http_response_code(400);
        echo 'Private or reserved image hosts are not allowed.';
        exit;
    }
}

$context = stream_context_create([
    'http' => [
        'timeout' => 5,
        'follow_location' => 0,
        'max_redirects' => 0,
        'ignore_errors' => false,
    ],
    'https' => [
        'timeout' => 5,
        'follow_location' => 0,
        'max_redirects' => 0,
        'ignore_errors' => false,
    ],
]);
$data = @file_get_contents($url, false, $context);

if ($data === false) {
    http_response_code(502);
    echo 'Could not fetch the image.';
    exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->buffer($data) ?: 'application/octet-stream';

if (strpos($mime, 'image/') !== 0) {
    http_response_code(415);
    echo 'The remote resource is not an image.';
    exit;
}

header('Content-Type: ' . $mime);
echo $data;
