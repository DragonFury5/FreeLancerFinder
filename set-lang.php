<?php
require_once __DIR__ . '/includes/lang.php';

$to = $_GET['lang'] ?? 'en';
if (!in_array($to, ['en', 'id'], true)) {
    $to = 'en';
}
$_SESSION['lang'] = $to;

// Redirect back to where the user came from — reject anything external.
$back = $_GET['back'] ?? 'index.php';
if (preg_match('#^https?://#i', $back) || strpos($back, '//') === 0 || strpos($back, '\\') !== false) {
    $back = 'index.php';
}

header('Location: ' . $back);
exit;