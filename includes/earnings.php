<?php
// FLF — earnings / balance helpers

/** Normalize any Tripay status into 3 canonical states. */
function earnings_status(string $raw): string {
    $s = strtoupper(trim($raw));
    if (in_array($s, ['PAID', 'SUCCESS', 'SETTLED'], true)) return 'PAID';
    if ($s === 'REFUNDED') return 'REFUNDED';
    return 'UNPAID';
}

/** Compute all the money buckets for a Lancer. */
function compute_lancer_balance(PDO $pdo, int $lancerId): array {
    $stmt = $pdo->prepare(
        "SELECT
            COALESCE(SUM(CASE WHEN UPPER(status) IN ('PAID','SUCCESS','SETTLED')
                              AND (available_at IS NULL OR available_at <= NOW())
                              THEN total ELSE 0 END), 0) AS available_gross,
            COALESCE(SUM(CASE WHEN UPPER(status) IN ('PAID','SUCCESS','SETTLED')
                              AND available_at > NOW()
                              THEN total ELSE 0 END), 0) AS held,
            COALESCE(SUM(CASE WHEN UPPER(status) IN ('PAID','SUCCESS','SETTLED')
                              THEN total ELSE 0 END), 0) AS paid_total,
            COALESCE(SUM(CASE WHEN UPPER(status) NOT IN ('PAID','SUCCESS','SETTLED','REFUNDED')
                              THEN total ELSE 0 END), 0) AS pending_total,
            COALESCE(SUM(CASE WHEN UPPER(status) <> 'REFUNDED' THEN total ELSE 0 END), 0) AS grand_total
         FROM transactions WHERE lancer_id = ?"
    );
    $stmt->execute([$lancerId]);
    $t = $stmt->fetch() ?: [];

    // Money already promised to a withdrawal (pending or sent)
    $stmt = $pdo->prepare(
        "SELECT COALESCE(SUM(amount + CASE WHEN flf_covers_fee = 0 THEN fee ELSE 0 END), 0)
         FROM withdrawals WHERE lancer_id = ? AND status IN ('PENDING','SENT')"
    );
    $stmt->execute([$lancerId]);
    $committed = (int)$stmt->fetchColumn();

    // Money already sent to the bank
    $stmt = $pdo->prepare(
        "SELECT COALESCE(SUM(amount), 0) FROM withdrawals
         WHERE lancer_id = ? AND status = 'SENT'"
    );
    $stmt->execute([$lancerId]);
    $withdrawn = (int)$stmt->fetchColumn();

    $available = max(0, (int)($t['available_gross'] ?? 0) - $committed);

    return [
        'available'     => $available,
        'held'          => (int)($t['held'] ?? 0),
        'paid_total'    => (int)($t['paid_total'] ?? 0),
        'pending_total' => (int)($t['pending_total'] ?? 0),
        'grand_total'   => (int)($t['grand_total'] ?? 0),
        'committed'     => $committed,
        'withdrawn'     => $withdrawn,
    ];
}