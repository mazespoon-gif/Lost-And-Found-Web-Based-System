<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/functions.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/manage_lost_items');
}

requireCsrf();

$itemId = (int)($_POST['item_id'] ?? 0);
$item = getLostItemById($itemId);

if (!$item) {
    $_SESSION['error'] = 'Item not found.';
    redirect('/manage_lost_items');
}

if ($item['image']) {
    deleteImage($item['image']);
}

$stmt = db()->prepare("DELETE FROM lost_items WHERE id = ?");
$stmt->execute([$itemId]);

pusherTrigger('lost-items', 'item-deleted', ['id' => $itemId]);

$_SESSION['success'] = 'Item deleted successfully!';
redirect('/manage_lost_items');
