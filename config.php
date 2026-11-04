<?php

// SHOW ERRORS
error_reporting(E_ALL);
ini_set('display_errors', '1');

// START SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DATABASE SETTINGS
$host = 'localhost';
$port = '3307';
$db   = 'library_management_system';
$user = 'root';
$pass = '';

try {

    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 3
        ]
    );

} catch (PDOException $e) {

    http_response_code(500);

    die(
        '<h2>Database Connection Error</h2>' .
        '<p>' . htmlspecialchars($e->getMessage()) . '</p>'
    );
}


// SAFE HTML OUTPUT
function h(?string $value): string
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}


// CREATE CSRF TOKEN
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}


// VERIFY CSRF TOKEN
function verify_csrf(): void
{
    $submittedToken = (string)($_POST['csrf'] ?? '');

    if (
        empty($submittedToken) ||
        !hash_equals(csrf_token(), $submittedToken)
    ) {
        http_response_code(403);
        exit('Invalid form token. Refresh the page and try again.');
    }
}


// REDIRECT WITH MESSAGE
function redirect_with(string $message): never
{
    $_SESSION['flash'] = $message;

    header('Location: index.php');

    exit;
}