<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/functions.php';

requireLogin();

$user = getUserById($_SESSION['user_id']);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmNewPassword = $_POST['confirm_new_password'] ?? '';

    if (empty($name) || empty($email)) {
        $message = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
    } else {
        $stmt = db()->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user['id']]);
        if ($stmt->fetchColumn() > 0) {
            $message = 'This email is already in use.';
        } else {
            $stmt = db()->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->execute([$name, $email, $user['id']]);
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $message = 'Profile updated successfully!';

            if (!empty($newPassword)) {
                if (strlen($newPassword) < 6) {
                    $message = 'New password must be at least 6 characters.';
                } elseif ($newPassword !== $confirmNewPassword) {
                    $message = 'New passwords do not match.';
                } else {
                    $stmt = db()->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $user['id']]);
                    $message = 'Profile and password updated successfully!';
                }
            }
        }
    }
    $user = getUserById($_SESSION['user_id']);
}

$pageTitle = 'My Profile';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div data-aos="fade-up">
        <h1 class="text-3xl font-bold text-gray-900">My Profile</h1>
        <p class="text-gray-500 mt-1">Manage your account information</p>
    </div>

    <div class="mt-8 bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden" data-aos="fade-up">
        <div class="p-6 md:p-8">
            <div class="flex items-center gap-4 mb-8 pb-6 border-b border-gray-100">
                <div class="w-16 h-16 min-w-16 min-h-16 bg-blue-900 rounded-2xl flex items-center justify-center text-white text-2xl font-bold flex-shrink-0">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-xl font-bold text-gray-900 truncate"><?= e($user['name']) ?></h2>
                    <p class="text-gray-500 text-sm truncate"><?= e($user['email']) ?></p>
                </div>
                <?php if ($user['role'] === 'admin'): ?>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-green-50 text-green-600 px-3 py-1.5 rounded-full">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Admin
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-blue-100 text-blue-700 px-3 py-1.5 rounded-full">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                        ID: <?= e($user['school_id']) ?>
                    </span>
                <?php endif; ?>
            </div>

            <form method="POST" action="" class="space-y-6">
                <?= csrfField() ?>
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" name="name" value="<?= e($user['name']) ?>" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" value="<?= e($user['email']) ?>" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                </div>

                <hr class="border-gray-200">

                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Change Password</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input type="password" name="new_password"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <input type="password" name="confirm_new_password"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-4">
                    <button type="submit" class="px-8 py-3 bg-blue-900 text-white font-semibold rounded-lg hover:bg-blue-800 transition shadow-sm w-full sm:w-auto">
                        Save Changes
                    </button>
                    <a href="<?= SITE_URL ?>/home" class="px-6 py-3 text-gray-700 font-medium hover:text-gray-900 transition text-center w-full sm:w-auto">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="profileToast" class="fixed top-4 right-4 z-[100] hidden">
    <div class="flex items-center gap-3 bg-white text-gray-900 px-5 py-4 rounded-xl shadow-2xl border min-w-[320px] max-w-sm <?= strpos($message, 'successfully') !== false ? 'border-green-200' : 'border-red-200' ?>" data-aos="fade-down">
        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 <?= strpos($message, 'successfully') !== false ? 'bg-green-100' : 'bg-red-100' ?>">
            <svg class="w-5 h-5 <?= strpos($message, 'successfully') !== false ? 'text-green-600' : 'text-red-600' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <?php if (strpos($message, 'successfully') !== false): ?>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                <?php else: ?>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                <?php endif; ?>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold <?= strpos($message, 'successfully') !== false ? 'text-green-800' : 'text-red-800' ?>">
                <?= strpos($message, 'successfully') !== false ? 'Success!' : 'Error!' ?>
            </p>
            <p class="text-xs text-gray-500" id="profileToastMsg"><?= e($message) ?></p>
        </div>
        <button onclick="hideProfileToast()" class="text-gray-400 hover:text-gray-600 transition flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
</div>

<script>
<?php if ($message): ?>
showProfileToast();
<?php endif; ?>

function showProfileToast() {
    document.getElementById('profileToast').classList.remove('hidden');
    setTimeout(function () { hideProfileToast(); }, 5000);
}
function hideProfileToast() {
    document.getElementById('profileToast').classList.add('hidden');
}
</script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="<?= SITE_URL ?>/assets/js/app.js?v=8"></script>
</body>
</html>
