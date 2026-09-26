<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

// Inline admin check (require_admin() sends an HTTP redirect, which breaks JSON)
$u = current_user();
if (!$u || ($u['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Forbidden']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$id     = (int)($_POST['id'] ?? 0);
$status = strtoupper(trim($_POST['status'] ?? ''));

$allowed = ['UNPAID', 'PAID', 'REFUNDED'];
if ($id <= 0 || !in_array($status, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid input']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE transactions SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);

    echo json_encode(['ok' => true, 'id' => $id, 'status' => $status]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database error']);
}