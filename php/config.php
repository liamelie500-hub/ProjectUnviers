<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'univers');
define('DB_USER', 'root');
define('DB_PASS', '');
define('JWT_SECRET', 'votre_secret_jwt_tres_securise');

// Connexion à la base de données
function getDBConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch(PDOException $e) {
        die("Erreur de connexion: " . $e->getMessage());
    }
}

// Fonctions de sécurité
function hashPassword($password) {
    return password_hash($password, PASSWORD_ARGON2ID);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Rate limiting
function checkLoginAttempts($email) {
    $attempts = $_SESSION['login_attempts'][$email] ?? 0;
    $last_attempt = $_SESSION['login_last_attempt'][$email] ?? time();
    
    if ($attempts >= 5 && (time() - $last_attempt) < 1800) {
        return false; // Trop de tentatives
    }
    
    return true;
}

function logLoginAttempt($email, $success) {
    if (!$success) {
        $_SESSION['login_attempts'][$email] = ($_SESSION['login_attempts'][$email] ?? 0) + 1;
        $_SESSION['login_last_attempt'][$email] = time();
    } else {
        unset($_SESSION['login_attempts'][$email]);
        unset($_SESSION['login_last_attempt'][$email]);
    }
}
?>