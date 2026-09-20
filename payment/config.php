<?php
// ===== Tripay Sandbox credentials =====
define('TRIPAY_API_KEY',       'DEV-I2nRxqZX4WE3IwdGAaD4OxVivJMjpmVX28z5gEue');
define('TRIPAY_PRIVATE_KEY',   'C7CSX-ds62g-l2IXl-KqYbx-w5YcE');
define('TRIPAY_MERCHANT_CODE', 'T24264');

define('TRIPAY_BASE_URL', 'https://tripay.co.id/api-sandbox');

// IMPORTANT: these now point to the FLF payment folder
define('TRIPAY_RETURN_URL',   'http://localhost/flf/payment/return.php');
define('TRIPAY_CALLBACK_URL', 'http://localhost/flf/payment/callback.php');