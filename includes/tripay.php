<?php
// FLF — Tripay disbursement helper
require_once __DIR__ . '/../payment/config.php';

/**
 * Fire a Tripay disbursement request.
 * @return array ['ok'=>bool, 'data'=>?, 'error'=>?, 'merchant_ref'=>string]
 */
function tripay_disbursement_create(array $p): array {
    $ref = 'WD-' . time() . '-' . rand(1000, 9999);

    $signature = hash_hmac(
        'sha256',
        TRIPAY_MERCHANT_CODE . $ref . $p['amount'] . $p['bank'] . $p['account'],
        TRIPAY_PRIVATE_KEY
    );

    $payload = [
        'merchant_ref' => $ref,
        'amount'       => (int)$p['amount'],
        'bank'         => $p['bank'],
        'account'      => $p['account'],
        'account_name' => $p['account_name'],
        'signature'    => $signature,
        'note'         => $p['note'] ?? '',
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_FRESH_CONNECT  => true,
        CURLOPT_URL            => TRIPAY_BASE_URL . '/disbursement/create',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . TRIPAY_API_KEY,
            'Content-Type: application/json',
        ],
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
    ]);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        return ['ok' => false, 'error' => $err, 'merchant_ref' => $ref];
    }

    $json = json_decode($res, true);
    if (empty($json['success'])) {
        $msg = $json['message'] ?? 'Unknown error from Tripay.';
        return ['ok' => false, 'error' => $msg, 'merchant_ref' => $ref, 'raw' => $json];
    }

    return ['ok' => true, 'data' => $json['data'] ?? [], 'merchant_ref' => $ref];
}