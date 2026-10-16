<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    markNotificationsRead($_SESSION['user_id']);
}

$referer = $_SERVER['HTTP_REFERER'] ?? SITE_URL . '/home';
header('Location: ' . $referer);
exit;
