<?php
// Prevent direct access
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

// Authentication functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('auth/login.php');
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = Database::getInstance();
    return $db->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
}

// Sanitization and validation functions
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Flash messages
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Format functions
function formatDate($date) {
    return date('d M Y', strtotime($date));
}

function formatNumber($number) {
    return number_format($number, 2);
}

// File upload function
function uploadFile($file, $destination, $allowedTypes = []) {
    try {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Upload failed with error code ' . $file['error']);
        }

        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);

        if (!empty($allowedTypes) && !in_array($extension, $allowedTypes)) {
            throw new Exception('File type not allowed');
        }

        $newFilename = uniqid() . '.' . $extension;
        $targetPath = $destination . '/' . $newFilename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception('Failed to move uploaded file');
        }

        return $newFilename;
    } catch (Exception $e) {
        error_log('File upload error: ' . $e->getMessage());
        return false;
    }
}
