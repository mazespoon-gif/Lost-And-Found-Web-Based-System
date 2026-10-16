<?php
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function excerpt($text, $length = 150) {
    if (strlen($text) <= $length) return $text;
    $trimmed = substr($text, 0, $length);
    $lastSpace = strrpos($trimmed, ' ');
    return ($lastSpace !== false ? substr($trimmed, 0, $lastSpace) : $trimmed) . '...';
}

function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M j, Y', $timestamp);
}

function formatDate($datetime) {
    return date('M j, Y', strtotime($datetime));
}

function getCategories() {
    $stmt = db()->query("SELECT * FROM categories ORDER BY category_name ASC");
    return $stmt->fetchAll();
}

function getCategoryById($id) {
    $stmt = db()->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getPosts($limit = 10, $offset = 0, $categoryId = null, $search = null) {
    $sql = "SELECT p.*, c.category_name, u.name as author_name
            FROM posts p
            JOIN categories c ON p.category_id = c.id
            JOIN users u ON p.author_id = u.id
            WHERE 1=1";
    $params = [];

    if ($categoryId) {
        $sql .= " AND p.category_id = ?";
        $params[] = $categoryId;
    }

    if ($search) {
        $sql .= " AND (p.title LIKE ? OR p.content LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $sql .= " ORDER BY p.published_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getTotalPosts($categoryId = null, $search = null) {
    $sql = "SELECT COUNT(*) FROM posts p WHERE 1=1";
    $params = [];

    if ($categoryId) {
        $sql .= " AND p.category_id = ?";
        $params[] = $categoryId;
    }

    if ($search) {
        $sql .= " AND (p.title LIKE ? OR p.content LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

function getPostBySlug($slug) {
    $stmt = db()->prepare("SELECT p.*, c.category_name, u.name as author_name
                           FROM posts p
                           JOIN categories c ON p.category_id = c.id
                           JOIN users u ON p.author_id = u.id
                           WHERE p.slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function getPostById($id) {
    $stmt = db()->prepare("SELECT p.*, c.category_name, u.name as author_name
                           FROM posts p
                           JOIN categories c ON p.category_id = c.id
                           JOIN users u ON p.author_id = u.id
                           WHERE p.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getPinnedPosts() {
    $stmt = db()->query("SELECT p.*, c.category_name, u.name as author_name
                         FROM posts p
                         JOIN categories c ON p.category_id = c.id
                         JOIN users u ON p.author_id = u.id
                         WHERE p.is_pinned = 1
                         ORDER BY p.published_at DESC LIMIT 3");
    return $stmt->fetchAll();
}

function getPostsByCategory($categoryName) {
    $stmt = db()->prepare("SELECT p.*, c.category_name, u.name as author_name
                           FROM posts p
                           JOIN categories c ON p.category_id = c.id
                           JOIN users u ON p.author_id = u.id
                           WHERE c.category_name = ?
                           ORDER BY p.published_at DESC LIMIT 6");
    $stmt->execute([$categoryName]);
    return $stmt->fetchAll();
}

function getRecentPosts($limit = 5) {
    $stmt = db()->prepare("SELECT p.*, c.category_name, u.name as author_name
                           FROM posts p
                           JOIN categories c ON p.category_id = c.id
                           JOIN users u ON p.author_id = u.id
                           ORDER BY p.published_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getFeaturedPosts($limit = 3) {
    $stmt = db()->prepare("SELECT p.*, c.category_name, u.name as author_name
                           FROM posts p
                           JOIN categories c ON p.category_id = c.id
                           JOIN users u ON p.author_id = u.id
                           ORDER BY p.published_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getCommentsByPostId($postId) {
    $stmt = db()->prepare("SELECT c.*, u.name as user_name
                           FROM comments c
                           JOIN users u ON c.user_id = u.id
                           WHERE c.post_id = ?
                           ORDER BY c.created_at DESC");
    $stmt->execute([$postId]);
    return $stmt->fetchAll();
}

function getTotalUsers() {
    return db()->query("SELECT COUNT(*) FROM users")->fetchColumn();
}

function getTotalCategories() {
    return db()->query("SELECT COUNT(*) FROM categories")->fetchColumn();
}

function getTotalComments() {
    return db()->query("SELECT COUNT(*) FROM comments")->fetchColumn();
}

function isValidImage($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    return in_array($file['type'], $allowedTypes) && in_array($ext, $allowedExts) && $file['size'] <= MAX_FILE_SIZE;
}

function uploadToSupabase($file, $kind) {
    if (!defined('SUPABASE_URL') || !defined('SUPABASE_STORAGE_BUCKET') || !defined('SUPABASE_SERVICE_KEY')) return false;
    if (!SUPABASE_URL || !SUPABASE_STORAGE_BUCKET || !SUPABASE_SERVICE_KEY) return false;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid($kind . '_') . '.' . $ext;
    $objectPath = ($kind === 'post' ? 'posts/' : ($kind === 'avatar' ? 'avatars/' : 'lost/')) . $filename;
    $data = @file_get_contents($file['tmp_name']);
    if ($data === false) return false;
    $uploadUrl = rtrim(SUPABASE_URL, '/') . '/storage/v1/object/' . SUPABASE_STORAGE_BUCKET . '/' . $objectPath;
    $ch = curl_init($uploadUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . SUPABASE_SERVICE_KEY,
            'Content-Type: ' . ($file['type'] ?: 'application/octet-stream'),
            'x-upsert: false',
        ],
    ]);
    $res = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code >= 200 && $code < 300) {
        return rtrim(SUPABASE_URL, '/') . '/storage/v1/object/public/' . SUPABASE_STORAGE_BUCKET . '/' . $objectPath;
    }
    return false;
}

function imageUrl($path) {
    if (!$path) return '';
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) return $path;
    return SITE_URL . '/' . $path;
}

function verifyTurnstile($token) {
    if (!$token) return false;
    $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'secret' => defined('TURNSTILE_SECRET_KEY') ? TURNSTILE_SECRET_KEY : '',
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    if ($res === false) return false;
    $data = json_decode($res, true);
    return !empty($data['success']);
}

function uploadImage($file) {
    if (!isValidImage($file)) return false;
    $remote = uploadToSupabase($file, 'post');
    if ($remote) return $remote;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid('post_') . '.' . $ext;
    $dest = UPLOAD_PATH . '/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return 'assets/uploads/posts/' . $filename;
    }
    return false;
}

function uploadAvatar($file) {
    if (!isset($file['error']) || is_array($file['error'])) return false;
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] <= 0 || $file['size'] > 1024 * 1024) return false;
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file['type'], $allowedTypes) || !in_array($ext, $allowedExts)) return false;
    $info = @getimagesize($file['tmp_name']);
    if ($info === false) return false;
    $remote = uploadToSupabase($file, 'avatar');
    if ($remote) return $remote;
    $filename = uniqid('av_') . '.' . $ext;
    $dest = AVATAR_UPLOAD_PATH . '/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return 'assets/uploads/avatars/' . $filename;
    }
    return false;
}

function deleteImage($imagePath) {
    if (!$imagePath) return;
    if (strpos($imagePath, 'http://') === 0 || strpos($imagePath, 'https://') === 0) {
        $marker = '/storage/v1/object/public/' . SUPABASE_STORAGE_BUCKET . '/';
        $pos = strpos($imagePath, $marker);
        if ($pos !== false && defined('SUPABASE_URL') && defined('SUPABASE_SERVICE_KEY')) {
            $objectPath = substr($imagePath, $pos + strlen($marker));
            $deleteUrl = rtrim(SUPABASE_URL, '/') . '/storage/v1/object/' . SUPABASE_STORAGE_BUCKET . '/' . $objectPath;
            $ch = curl_init($deleteUrl);
            curl_setopt_array($ch, [
                CURLOPT_CUSTOMREQUEST => 'DELETE',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . SUPABASE_SERVICE_KEY],
            ]);
            curl_exec($ch);
            curl_close($ch);
        }
        return;
    }
    if (file_exists(__DIR__ . '/' . $imagePath)) {
        unlink(__DIR__ . '/' . $imagePath);
    }
}

// Lost & Found helpers
function getLostItems($status = null, $limit = 12, $offset = 0, $search = null) {
    $sql = "SELECT l.*, u.name as author_name, u.email as author_email, u.avatar as author_avatar FROM lost_items l JOIN users u ON l.user_id = u.id WHERE 1=1";
    $params = [];
    if ($status && in_array($status, ['lost', 'found'])) {
        $sql .= " AND l.status = ?";
        $params[] = $status;
    }
    if ($search) {
        $sql .= " AND l.title LIKE ?";
        $params[] = "%$search%";
    }
    $sql .= " ORDER BY l.created_at DESC LIMIT ? OFFSET ?";
    $params[] = (int)$limit;
    $params[] = (int)$offset;
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getTotalLostItems($status = null, $search = null) {
    $sql = "SELECT COUNT(*) FROM lost_items l WHERE 1=1";
    $params = [];
    if ($status && in_array($status, ['lost', 'found'])) {
        $sql .= " AND l.status = ?";
        $params[] = $status;
    }
    if ($search) {
        $sql .= " AND l.title LIKE ?";
        $params[] = "%$search%";
    }
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}

function getLostItemById($id) {
    $stmt = db()->prepare("SELECT l.*, u.name as author_name, u.email as author_email, u.avatar as author_avatar FROM lost_items l JOIN users u ON l.user_id = u.id WHERE l.id = ?");
    $stmt->execute([(int)$id]);
    return $stmt->fetch();
}

function getLostItemsByUser($userId) {
    $stmt = db()->prepare("SELECT * FROM lost_items WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([(int)$userId]);
    return $stmt->fetchAll();
}

function uploadLostImage($file) {
    if (!isValidImage($file)) return false;
    $remote = uploadToSupabase($file, 'lost');
    if ($remote) return $remote;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid('lost_') . '.' . $ext;
    $dest = LOST_UPLOAD_PATH . '/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return 'assets/uploads/lost/' . $filename;
    }
    return false;
}

function generateSlug($title) {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

function uniqueSlug($title, $excludeId = null) {
    $slug = generateSlug($title);
    $baseSlug = $slug;
    $counter = 1;
    while (true) {
        $stmt = db()->prepare("SELECT COUNT(*) FROM posts WHERE slug = ?" . ($excludeId ? " AND id != ?" : ""));
        $params = [$slug];
        if ($excludeId) $params[] = $excludeId;
        $stmt->execute($params);
        if ($stmt->fetchColumn() == 0) break;
        $slug = $baseSlug . '-' . $counter++;
    }
    return $slug;
}

function flash($key = 'success') {
    if (isset($_SESSION[$key])) {
        $msg = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $msg;
    }
    return null;
}

function flashError() {
    return flash('error');
}

function flashSuccess() {
    return flash('success');
}

function hasFlash($key = 'success') {
    return isset($_SESSION[$key]);
}

function paginationLinks($total, $perPage, $currentPage, $url) {
    $totalPages = ceil($total / $perPage);
    if ($totalPages <= 1) return '';

    $html = '<div class="flex items-center justify-center gap-2 mt-8">';

    if ($currentPage > 1) {
        $html .= '<a href="' . $url . '&page=' . ($currentPage - 1) . '" class="px-4 py-2 bg-white border rounded-lg hover:bg-gray-50">Previous</a>';
    }

    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == $currentPage) {
            $html .= '<span class="px-4 py-2 bg-blue-600 text-white rounded-lg">' . $i . '</span>';
        } else {
            $html .= '<a href="' . $url . '&page=' . $i . '" class="px-4 py-2 bg-white border rounded-lg hover:bg-gray-50">' . $i . '</a>';
        }
    }

    if ($currentPage < $totalPages) {
        $html .= '<a href="' . $url . '&page=' . ($currentPage + 1) . '" class="px-4 py-2 bg-white border rounded-lg hover:bg-gray-50">Next</a>';
    }

    $html .= '</div>';
    return $html;
}

function getCategoryColor($categoryName) {
    $colors = [
        'Announcements' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'badge' => 'bg-blue-600', 'icon' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z'],
        'Events'        => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'badge' => 'bg-green-600', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        'Enrollment'    => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'badge' => 'bg-purple-600', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
        'Exams'         => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'badge' => 'bg-red-600', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        'PE Activities' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'badge' => 'bg-orange-600', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
        'School News'   => ['bg' => 'bg-teal-100', 'text' => 'text-teal-700', 'badge' => 'bg-teal-600', 'icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z'],
        'Orientation'   => ['bg' => 'bg-cyan-100', 'text' => 'text-cyan-700', 'badge' => 'bg-cyan-600', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
    ];
    return $colors[$categoryName] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'badge' => 'bg-gray-600', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'];
}

// Lost item comments
function getLostItemComments($lostItemId) {
    $stmt = db()->prepare("SELECT c.*, u.name as user_name
                           FROM lost_item_comments c
                           JOIN users u ON c.user_id = u.id
                           WHERE c.lost_item_id = ?
                           ORDER BY c.created_at DESC");
    $stmt->execute([(int)$lostItemId]);
    return $stmt->fetchAll();
}

function addLostItemComment($lostItemId, $userId, $comment) {
    $stmt = db()->prepare("INSERT INTO lost_item_comments (lost_item_id, user_id, comment) VALUES (?, ?, ?)");
    return $stmt->execute([(int)$lostItemId, (int)$userId, $comment]);
}

function updateLostItemComment($commentId, $userId, $comment) {
    $stmt = db()->prepare("UPDATE lost_item_comments SET comment = ? WHERE id = ? AND user_id = ?");
    return $stmt->execute([$comment, (int)$commentId, (int)$userId]);
}

function deleteLostItemComment($commentId, $userId) {
    $stmt = db()->prepare("DELETE FROM lost_item_comments WHERE id = ? AND user_id = ?");
    return $stmt->execute([(int)$commentId, (int)$userId]);
}

function getLostItemCommentById($commentId) {
    $stmt = db()->prepare("SELECT * FROM lost_item_comments WHERE id = ?");
    $stmt->execute([(int)$commentId]);
    return $stmt->fetch();
}

// Claims
function getClaimsByItemId($lostItemId) {
    $stmt = db()->prepare("SELECT c.*, u.name as claimant_name, u.email as claimant_email
                           FROM claims c
                           JOIN users u ON c.claimant_id = u.id
                           WHERE c.lost_item_id = ?
                           ORDER BY c.created_at DESC");
    $stmt->execute([(int)$lostItemId]);
    return $stmt->fetchAll();
}

function getClaimById($claimId) {
    $stmt = db()->prepare("SELECT c.*, u.name as claimant_name, u.email as claimant_email
                           FROM claims c
                           JOIN users u ON c.claimant_id = u.id
                           WHERE c.id = ?");
    $stmt->execute([(int)$claimId]);
    return $stmt->fetch();
}

function addClaim($lostItemId, $claimantId, $proof) {
    $stmt = db()->prepare("INSERT INTO claims (lost_item_id, claimant_id, proof) VALUES (?, ?, ?)");
    return $stmt->execute([(int)$lostItemId, (int)$claimantId, $proof]);
}

function updateClaimStatus($claimId, $status) {
    $stmt = db()->prepare("UPDATE claims SET status = ? WHERE id = ?");
    return $stmt->execute([$status, (int)$claimId]);
}

// Notifications
function createNotification($userId, $message, $link) {
    $stmt = db()->prepare("INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)");
    $result = $stmt->execute([(int)$userId, $message, $link]);

    $notification = [
        'user_id' => (int)$userId,
        'message' => $message,
        'link' => $link,
        'created_at' => 'Just now',
    ];
    pusherTrigger('notifications-' . $userId, 'new-notification', $notification);

    return $result;
}

function getUnreadNotificationCount($userId) {
    $stmt = db()->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([(int)$userId]);
    return (int)$stmt->fetchColumn();
}

function getNotifications($userId, $limit = 10) {
    $stmt = db()->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([(int)$userId, (int)$limit]);
    return $stmt->fetchAll();
}

function markNotificationsRead($userId) {
    $stmt = db()->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
    return $stmt->execute([(int)$userId]);
}

function deleteNotification($notifId, $userId) {
    $stmt = db()->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
    return $stmt->execute([(int)$notifId, (int)$userId]);
}

function getCategoryPostCount() {
    $stmt = db()->query("SELECT c.id, c.category_name, COUNT(p.id) as post_count
                         FROM categories c
                         LEFT JOIN posts p ON c.id = p.category_id
                         GROUP BY c.id, c.category_name
                         ORDER BY c.category_name ASC");
    return $stmt->fetchAll();
}

function pusherTrigger($channel, $event, $data) {
    $appId = PUSHER_APP_ID;
    $key = PUSHER_KEY;
    $secret = PUSHER_SECRET;
    $cluster = PUSHER_CLUSTER;

    $url = "https://api-{$cluster}.pusher.com/apps/{$appId}/events";

    $payload = json_encode([
        'name' => $event,
        'channel' => $channel,
        'data' => json_encode($data),
    ]);

    $timestamp = time();
    $bodyMd5 = md5($payload);
    $signingString = "POST\n/apps/{$appId}/events\nauth_key={$key}&auth_timestamp={$timestamp}&auth_version=1.0&body_md5={$bodyMd5}";
    $signature = hash_hmac('sha256', $signingString, $secret);
    $auth = "auth_key={$key}&auth_timestamp={$timestamp}&auth_version=1.0&body_md5={$bodyMd5}&auth_signature={$signature}";

    $ch = curl_init($url . '?' . $auth);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $result = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($error || $httpCode !== 200) {
        error_log("Pusher error: $error | HTTP $httpCode | Response: $result | Channel: $channel | Event: $event");
    }

    return $result;
}
