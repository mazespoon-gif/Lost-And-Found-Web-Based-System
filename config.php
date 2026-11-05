<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

ob_start();
register_shutdown_function(function () {
    $err = error_get_last();
    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    $isFatal = $err && in_array($err['type'], $fatalTypes, true);
    $code = http_response_code();
    $isEmpty500 = is_int($code) && $code >= 500 && ob_get_length() === 0;
    if (!$isFatal && !$isEmpty500) {
        return;
    }
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
        || (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=UTF-8');
    }
    if ($isAjax) {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json');
        }
        echo json_encode(['ok' => false, 'error' => 'Server error']);
        exit;
    }
    if (!headers_sent()) {
        http_response_code(500);
    }
    include __DIR__ . '/includes/fatal-500.php';
    exit;
});

define('DB_HOST', 'localhost');
define('DB_NAME', 'lost');
define('DB_USER', 'root');
define('DB_PASS', '');




define('SITE_URL', 'http://localhost/bach-fourth');
define('SITE_NAME', 'LookForit');
define('SITE_TAGLINE', 'Group 3 Generosity');
define('UPLOAD_PATH', __DIR__ . '/assets/uploads/posts');
define('LOST_UPLOAD_PATH', __DIR__ . '/assets/uploads/lost');
define('AVATAR_UPLOAD_PATH', __DIR__ . '/assets/uploads/avatars');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);

define('PUSHER_APP_ID', '2193620');
define('PUSHER_KEY', '8e89a8fbbd041d695092');
define('PUSHER_SECRET', 'c52b1ea79908f35001eb');
define('PUSHER_CLUSTER', 'ap1');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
