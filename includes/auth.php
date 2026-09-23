<?php
// Session + helper functions

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_admin(): void {
    $u = current_user();
    if (!$u || ($u['role'] ?? '') !== 'admin') {
        header('Location: ../login.php');
        exit;
    }
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool {
    return current_user() !== null;
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

/** Escape for HTML output. */
function e(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** "Ariana Putri" → "AP" */
function initials(string $name): string {
    $parts = preg_split('/\s+/', trim($name));
    $out = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        if ($p !== '') $out .= strtoupper(mb_substr($p, 0, 1));
    }
    return $out !== '' ? $out : '?';
}

/** Render 5-star rating markup (rounded to nearest). */
function render_stars(float $rating): string {
    $full = (int) round(max(0, min(5, $rating)));
    $label = number_format($rating, 1) . ' out of 5';
    $out  = '<span class="stars" role="img" aria-label="Rated ' . $label . '">';
    for ($i = 1; $i <= 5; $i++) {
        $cls = $i <= $full ? 'star is-on' : 'star';
        $out .= '<svg class="' . $cls . '" viewBox="0 0 24 24" aria-hidden="true">'
              . '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26"/>'
              . '</svg>';
    }
    return $out . '</span>';
}