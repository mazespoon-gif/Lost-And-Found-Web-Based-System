<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/functions.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin');
}

requireCsrf();

$postId = (int)($_POST['post_id'] ?? 0);
$post = getPostById($postId);

if (!$post) {
    $_SESSION['error'] = 'Post not found.';
    redirect('/admin');
}

if ($post['image']) {
    deleteImage($post['image']);
}

$stmt = db()->prepare("DELETE FROM posts WHERE id = ?");
$stmt->execute([$postId]);

$_SESSION['success'] = 'Post deleted successfully!';
redirect('/admin');
