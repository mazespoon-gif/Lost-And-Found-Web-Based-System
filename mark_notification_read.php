<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn() && isset($_GET['id'])) {
    $notifId = (int)$_GET['id'];
    $stmt = db()->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$notifId, $_SESSION['user_id']]);
}

header('Content-Type: application/json');
echo json_encode(['ok' => true]);
