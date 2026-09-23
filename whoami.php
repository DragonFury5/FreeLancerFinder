<?php
require_once __DIR__ . '/includes/auth.php';
header('Content-Type: text/plain; charset=utf-8');

$u = current_user();
echo "Session user:\n";
var_dump($u);
echo "\nSession ID: " . session_id() . "\n";
echo "Session file: " . session_save_path() . "\n";