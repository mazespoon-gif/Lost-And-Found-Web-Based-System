<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item'])) {
    requireCsrf();
    $itemId = (int)($_POST['item_id'] ?? 0);
    $item = getLostItemById($itemId);

    if (!$item) {
        $_SESSION['error'] = 'Item not found.';
        redirect('/lost_and_found');
    }

    if ($item['user_id'] != $_SESSION['user_id'] && !isAdmin()) {
        $_SESSION['error'] = 'You do not have permission to delete this item.';
        redirect('/lost_and_found');
    }

    if ($item['image']) {
        deleteImage($item['image']);
    }

    $stmt = db()->prepare("DELETE FROM lost_items WHERE id = ?");
    $stmt->execute([$itemId]);

    pusherTrigger('lost-items', 'item-deleted', ['id' => $itemId]);

    $_SESSION['success'] = 'Item deleted successfully.';
    redirect('/lost_and_found');
}

$_SESSION['error'] = 'Invalid request.';
redirect('/lost_and_found');
