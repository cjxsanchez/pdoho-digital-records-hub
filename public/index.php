<?php
session_start();
require_once '../app/config/db.php';

// Simple PDF Parser Autoload (Assumes composer require smalot/pdfparser)
if(file_exists('../vendor/autoload.php')) {
    require '../vendor/autoload.php';
}

// Router
$route = $_GET['route'] ?? 'login';

// Middleware: Check Auth
$public_routes = ['login', 'auth/login', 'auth/forgot_password', 'auth/reset_password'];
if (!isset($_SESSION['admin_id']) && !in_array($route, $public_routes)) {
    header("Location: index.php?route=login");
    exit;
}

// Route Dispatcher
switch ($route) {
    case 'login':
        require '../app/views/login.php';
        break;
    case 'auth/login':
        require '../app/controllers/AuthController.php';
        AuthController::login($pdo);
        break;
    case 'auth/forgot_password':
        require '../app/controllers/AuthController.php';
        AuthController::forgotPassword($pdo);
        break;
    case 'auth/reset_password':
        require '../app/controllers/AuthController.php';
        AuthController::resetPassword($pdo);
        break;
    case 'logout':
        require '../app/controllers/AuthController.php';
        AuthController::logout();
        break;
    case 'dashboard':
        require '../app/controllers/FileController.php';
        FileController::index($pdo);
        break;

    // ==========================================
    // FILE MANAGEMENT ROUTES
    // ==========================================
    case 'files/upload':
        require '../app/controllers/FileController.php';
        FileController::upload($pdo);
        break;
    case 'files/download':
        require '../app/controllers/FileController.php';
        FileController::download($pdo);
        break;
    case 'files/delete':
        require '../app/controllers/FileController.php';
        FileController::delete($pdo);
        break;
    case 'files/rename':
        require '../app/controllers/FileController.php';
        FileController::renameFile($pdo);
        break;
    case 'files/archive':
        require '../app/controllers/FileController.php';
        FileController::archiveFile($pdo);
        break;
    case 'files/restore':
        require '../app/controllers/FileController.php';
        FileController::restoreFile($pdo);
        break;

    // ==========================================
    // FOLDER MANAGEMENT ROUTES
    // ==========================================
    case 'folder/create':
        require '../app/controllers/FileController.php';
        FileController::createFolder($pdo);
        break;
    case 'folder/view':
        require '../app/controllers/FileController.php';
        FileController::folderView($pdo);
        break;
    case 'folder/edit':
        require '../app/controllers/FileController.php';
        FileController::editFolder($pdo);
        break;
    case 'folder/delete':
        require '../app/controllers/FileController.php';
        FileController::deleteFolder($pdo);
        break;
    case 'folder/archive':
        require '../app/controllers/FileController.php';
        FileController::archiveFolder($pdo);
        break;
    case 'folder/restore':
        require '../app/controllers/FileController.php';
        FileController::restoreFolder($pdo);
        break;

    // ==========================================
    // SYSTEM PAGES & TOOLS
    // ==========================================
    case 'categories':
        require '../app/controllers/FileController.php';
        FileController::categories($pdo);
        break;
    case 'archives':
        require '../app/controllers/FileController.php';
        FileController::archives($pdo);
        break;
    case 'backup':
        require '../app/controllers/BackupController.php';
        BackupController::manage($pdo);
        break;
    case 'audit':
        require '../app/controllers/AuditController.php';
        AuditController::index($pdo);
        break; 
    case 'audit/export':
        require '../app/controllers/AuditController.php';
        AuditController::export($pdo);
        break;

    // ==========================================
    // ACCOUNT & PROFILE
    // ==========================================
    case 'profile':
        require '../app/controllers/AdminController.php';
        AdminController::profile($pdo);
        break;
    case 'profile/update':
        require '../app/controllers/AdminController.php';
        AdminController::updateProfile($pdo);
        break;
    case 'password':
        require '../app/views/change_password.php';
        break;
    case 'password/update':
        require '../app/controllers/AdminController.php';
        AdminController::updatePassword($pdo);
        break;
    case 'files/move':
        require '../app/controllers/FileController.php';
        FileController::moveFile($pdo);
        break;
    case 'files/bulk_delete':
        require '../app/controllers/FileController.php';
        FileController::bulkDelete($pdo);
        break;
    // ==========================================
    // ERROR HANDLING
    // ==========================================
    case 'files/search_folder':
        require '../app/controllers/FileController.php';
        FileController::searchFolderFiles($pdo);
        break;
    default:
        echo "404 Not Found";
}
?>