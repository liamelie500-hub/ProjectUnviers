<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch($action) {
    case 'getAll':
        getAllFiles();
        break;
    case 'getLatest':
        getLatestFiles($_GET['limit'] ?? 6);
        break;
    case 'getOne':
        getOneFile($_GET['id'] ?? 0);
        break;
    case 'download':
        downloadFile($_GET['id'] ?? 0);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Action non valide']);
}

function getAllFiles() {
    $pdo = getDBConnection();
    try {
        $stmt = $pdo->query("SELECT * FROM files ORDER BY created_at DESC");
        $files = $stmt->fetchAll();
        echo json_encode(['success' => true, 'files' => $files]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors du chargement des fichiers']);
    }
}

function getLatestFiles($limit) {
    $pdo = getDBConnection();
    try {
        $stmt = $pdo->prepare("SELECT * FROM files ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        $files = $stmt->fetchAll();
        echo json_encode(['success' => true, 'files' => $files]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors du chargement des fichiers']);
    }
}

function getOneFile($id) {
    $pdo = getDBConnection();
    try {
        $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
        $stmt->execute([$id]);
        $file = $stmt->fetch();
        
        if ($file) {
            echo json_encode(['success' => true, 'file' => $file]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Fichier non trouvé']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors du chargement du fichier']);
    }
}

function downloadFile($id) {
    // Vérifier l'authentification
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Non authentifié']);
        return;
    }
    
    $pdo = getDBConnection();
    try {
        $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
        $stmt->execute([$id]);
        $file = $stmt->fetch();
        
        if (!$file) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Fichier non trouvé']);
            return;
        }
        
        // Incrémenter le compteur de téléchargements
        $stmt = $pdo->prepare("UPDATE files SET downloads = downloads + 1 WHERE id = ?");
        $stmt->execute([$id]);
        
        // Simuler le téléchargement
        $filePath = 'assets/uploads/' . $file['filename'];
        if (file_exists($filePath)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $file['filename'] . '"');
            readfile($filePath);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Fichier introuvable sur le serveur']);
        }
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors du téléchargement']);
    }
}
?>