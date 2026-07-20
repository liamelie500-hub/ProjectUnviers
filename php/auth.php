<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);

switch($action) {
    case 'register':
        registerUser($data);
        break;
    case 'login':
        loginUser($data);
        break;
    case 'logout':
        logoutUser();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Action non valide']);
}

function registerUser($data) {
    $pdo = getDBConnection();
    
    // Validation des données
    $username = trim($data['username'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    
    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis']);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email invalide']);
        return;
    }
    
    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères']);
        return;
    }
    
    try {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
            return;
        }
        
        // Vérifier si le nom d'utilisateur existe déjà
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Ce nom d\'utilisateur est déjà utilisé']);
            return;
        }
        
        // Créer l'utilisateur
        $hashedPassword = hashPassword($password);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$username, $email, $hashedPassword]);
        
        echo json_encode(['success' => true, 'message' => 'Compte créé avec succès']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'inscription']);
    }
}

function loginUser($data) {
    $pdo = getDBConnection();
    
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email et mot de passe requis']);
        return;
    }
    
    // Vérifier les tentatives de connexion
    if (!checkLoginAttempts($email)) {
        echo json_encode(['success' => false, 'message' => 'Trop de tentatives. Réessayez dans 30 minutes.']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user || !verifyPassword($password, $user['password'])) {
            logLoginAttempt($email, false);
            echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect']);
            return;
        }
        
        // Connexion réussie
        logLoginAttempt($email, true);
        
        // Créer un token JWT simple
        $token = bin2hex(random_bytes(32));
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['token'] = $token;
        
        echo json_encode([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email']
            ]
        ]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur de connexion']);
    }
}

function logoutUser() {
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Déconnexion réussie']);
}
?>