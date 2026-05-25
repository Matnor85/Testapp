<?php

// =============================================================
//  helpers.php
//  Delade hjälpfunktioner för hela applikationen.
//  Inkluderas överst i index.php via require_once.
// =============================================================

// Starta session med säkra cookie-inställningar
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => false,       // Sätt true i produktion (HTTPS)
        'httponly' => true,        // JavaScript kan inte läsa cookien
        'samesite' => 'Strict',    // Förhindrar CSRF via cross-site requests
    ]);
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    session_start();
}

// =============================================================
//  Autentisering
// =============================================================

/**
 * Kräv inloggning i ATM-kontexten (vanlig användare).
 * Omdirigerar till atm_login om inte inloggad.
 */
function require_atm_login(): void
{
    check_idle_timeout();

    if (empty($_SESSION['user_id']) || ($_SESSION['context'] ?? '') !== 'atm') {
        header('Location: /index.php?page=atm_login');
        exit;
    }
}

/**
 * Kräv inloggning i admin-kontexten (admin-användare).
 * Omdirigerar till admin_login om inte inloggad som admin.
 */
function require_admin_login(): void
{
    check_idle_timeout();

    if (empty($_SESSION['user_id']) || ($_SESSION['context'] ?? '') !== 'admin') {
        header('Location: /index.php?page=admin_login');
        exit;
    }

    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        require __DIR__ . '/../templates/403.php';
        exit;
    }
}

/**
 * Kontrollera att användaren är inloggad (oavsett kontext).
 * Behålls för bakåtkompatibilitet.
 */
function require_login(): void
{
    check_idle_timeout();

    if (empty($_SESSION['user_id'])) {
        header('Location: /index.php?page=atm_login');
        exit;
    }
}

/**
 * Kräv en specifik roll. Omdirigerar med 403 om fel roll.
 */
function require_role(string $role): void
{
    require_login();

    if (($_SESSION['role'] ?? '') !== $role) {
        http_response_code(403);
        require __DIR__ . '/../templates/403.php';
        exit;
    }
}

/**
 * Kontrollera om den inloggade användaren har en viss roll.
 */
function has_role(string $role): bool
{
    return ($_SESSION['role'] ?? '') === $role;
}

/**
 * Idle-timeout: logga ut efter 30 minuters inaktivitet.
 */
function check_idle_timeout(int $minutes = 30): void
{
    if (empty($_SESSION['user_id'])) {
        return;
    }

    $lastActive = $_SESSION['last_active'] ?? 0;

    if (time() - $lastActive > $minutes * 60) {
        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
        header('Location: /index.php?page=login&reason=timeout');
        exit;
    }

    $_SESSION['last_active'] = time();
}

// =============================================================
//  CSRF-skydd
// =============================================================

/**
 * Hämta (eller skapa) CSRF-token lagrad i sessionen.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Returnera ett dolt input-fält med CSRF-token.
 * Användning: <?= csrf_field() ?> i alla formulär.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8')
        . '">';
}

/**
 * Validera CSRF-token från POST.
 * Avbryter med 403 om ogiltig.
 */
function csrf_verify(): void
{
    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        die('403 – Ogiltig CSRF-token.');
    }

    // Rotera token efter lyckad validering
    unset($_SESSION['csrf_token']);
}

// =============================================================
//  XSS-skydd
// =============================================================

/**
 * Escape HTML — använd alltid vid utskrift av dynamisk data.
 * Kortnamn: e() istället för htmlspecialchars().
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// =============================================================
//  Formatering
// =============================================================

/**
 * Formatera belopp som "1 234,50 kr"
 */
function format_money(float $amount): string
{
    return number_format($amount, 2, ',', ' ') . ' kr';
}

/**
 * Formatera datum som "2026-05-20 14:32"
 */
function format_date(string $datetime): string
{
    return date('Y-m-d H:i', strtotime($datetime));
}

/**
 * Returnera CSS-klass för transaktionstyp
 */
function tx_type_label(string $type): string
{
    return match($type) {
        'deposit'      => 'Insättning',
        'withdrawal'   => 'Uttag',
        'transfer'     => 'Överföring',
        'bill_payment' => 'Betalning',
        default        => $type,
    };
}
