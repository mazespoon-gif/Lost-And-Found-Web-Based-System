<?php
require_once __DIR__ . '/config.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/home');
        exit;
    }
}

function redirect($url) {
    header('Location: ' . SITE_URL . $url);
    exit;
}

function getUserById($id) {
    $stmt = db()->prepare("SELECT id, school_id, avatar, name, email, role, status, created_at FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function isApproved() {
    return isset($_SESSION['user_status']) && $_SESSION['user_status'] === 'approved';
}
