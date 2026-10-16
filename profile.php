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

    if (isset($_POST['change_avatar'])) {
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE || empty($_FILES['avatar']['name'])) {
            $message = 'Please choose a photo to upload.';
        } else {
            $newAvatar = uploadAvatar($_FILES['avatar']);
            if (!$newAvatar) {
                $message = 'Photo upload failed. Max size is 1 MB (JPG, PNG, GIF or WEBP).';
            } else {
                if (!empty($user['avatar'])) deleteImage($user['avatar']);
                $stmt = db()->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                $stmt->execute([$newAvatar, $user['id']]);
                $_SESSION['user_avatar'] = $newAvatar;
                $message = 'Profile photo updated successfully!';
            }
        }
    } elseif (isset($_POST['remove_avatar'])) {
        if (!empty($user['avatar'])) {
            deleteImage($user['avatar']);
            $stmt = db()->prepare("UPDATE users SET avatar = NULL WHERE id = ?");
            $stmt->execute([$user['id']]);
        }
        $_SESSION['user_avatar'] = null;
        $message = 'Profile photo removed successfully!';
    } else {
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
    }
    $user = getUserById($_SESSION['user_id']);
}

$pageTitle = 'My Profile';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$tlMap = [
    '#.#.....',
    '.....#..',
    '#.......',
    '...#....',
    '......#.',
    '..#.....',
    '.#....#.',
    '....#...',
];
$brMap = array_reverse(array_map(function ($row) { return strrev($row); }, $tlMap));
function pixelCluster($map) {
    $html = '';
    $i = 0;
    foreach ($map as $row) {
        foreach (str_split($row) as $c) {
            if ($c === '#') {
                $shade = ($i % 3 === 0) ? '' : (($i % 3 === 1) ? ' soft' : ' softer');
                $html .= '<span class="laf-dot' . $shade . '" style="animation-delay:' . ($i * 120) . 'ms"></span>';
            } else {
                $html .= '<span></span>';
            }
            $i++;
        }
    }
    return $html;
}
$pmFont = [
    'P' => ['####.', '#...#', '#...#', '####.', '#....', '#....', '#....'],
    'R' => ['####.', '#...#', '#...#', '####.', '#.#..', '#..#.', '#...#'],
    'O' => ['.###.', '#...#', '#...#', '#...#', '#...#', '#...#', '.###.'],
    'F' => ['#####', '#....', '#....', '####.', '#....', '#....', '#....'],
    'I' => ['#####', '..#..', '..#..', '..#..', '..#..', '..#..', '#####'],
    'L' => ['#....', '#....', '#....', '#....', '#....', '#....', '#####'],
    'E' => ['#####', '#....', '#....', '####.', '#....', '#....', '#####'],
    'H' => ['#...#', '#...#', '#...#', '#####', '#...#', '#...#', '#...#'],
    'T' => ['#####', '..#..', '..#..', '..#..', '..#..', '..#..', '..#..'],
];
$pmTitle = 'PROFILE PHOTO';
$pmDelay = 0;
?>
<style>
    .laf-bg {
        position: relative;
        overflow: hidden;
        min-height: calc(100vh - 4rem);
        background:
            radial-gradient(circle, rgba(30, 58, 138, 0.1) 1.6px, transparent 1.6px),
            radial-gradient(1100px 520px at 100% 0%, rgba(219, 234, 254, 0.6), transparent 62%),
            radial-gradient(950px 520px at 0% 100%, rgba(226, 232, 255, 0.55), transparent 62%),
            linear-gradient(158deg, #ffffff 0%, #fafcff 48%, #f0f6ff 100%);
        background-size: 24px 24px, auto, auto, auto;
    }
    .pixel-corner {
        position: absolute;
        display: grid;
        grid-template-columns: repeat(8, 10px);
        grid-auto-rows: 10px;
        gap: 4px;
        z-index: 0;
        pointer-events: none;
    }
    .pixel-corner-br { bottom: 1.5rem; right: 1.5rem; }
    .laf-dot {
        width: 100%;
        height: 100%;
        border-radius: 2px;
        background: #1e3a8a;
        opacity: 0.4;
        box-shadow: inset 0 -2px 0 rgba(0, 0, 0, 0.12);
        animation: lafPulse 4.5s ease-in-out infinite;
    }
    .laf-dot.soft { opacity: 0.22; }
    .laf-dot.softer { opacity: 0.1; }
    .glass-wrap { position: relative; }
    .glass-blob {
        position: absolute;
        border-radius: 50%;
        filter: blur(70px);
        opacity: 0.5;
        pointer-events: none;
        z-index: 0;
        animation: glassDrift 14s ease-in-out infinite alternate;
    }
    .glass-blob-1 { width: 320px; height: 320px; background: #93c5fd; top: -60px; left: -60px; }
    .glass-blob-2 { width: 260px; height: 260px; background: #c7d2fe; bottom: -50px; right: -40px; animation-delay: -7s; }
    .glass-blob-3 { width: 190px; height: 190px; background: #bfdbfe; top: 45%; right: 14%; animation-delay: -3s; }
    @keyframes glassDrift {
        from { transform: translate(0, 0) scale(1); }
        to { transform: translate(35px, 25px) scale(1.1); }
    }
    .glass-card {
        position: relative;
        z-index: 1;
        background: rgba(255, 255, 255, 0.66);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.85);
        border-radius: 1.5rem;
        box-shadow: 0 25px 60px rgba(30, 58, 138, 0.18);
    }
    .glass-header {
        position: relative;
        background: linear-gradient(115deg, #1e3a8a 0%, #1d4ed8 45%, #4f46e5 100%);
        overflow: hidden;
    }
    .glass-header::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(to top right, transparent 30%, rgba(255, 255, 255, 0.25) 50%, transparent 70%);
        pointer-events: none;
    }
    .glass-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.25em;
        color: rgba(255, 255, 255, 0.75);
        position: relative;
        z-index: 1;
    }
    .glass-name {
        background: linear-gradient(90deg, #1e3a8a, #4f46e5);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        color: transparent;
    }
    .glass-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.85);
        box-shadow: 0 2px 8px rgba(30, 58, 138, 0.08);
        color: #1d4ed8;
    }
    .glass-chip.chip-green { color: #15803d; background: rgba(240, 253, 244, 0.75); border-color: rgba(187, 247, 208, 0.8); }
    .glass-chip.chip-gray { color: #475569; background: rgba(248, 250, 252, 0.75); border-color: rgba(226, 232, 240, 0.85); }
    .glass-chip svg { width: 13px; height: 13px; }
    .glass-sect-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.18em;
        color: #1e3a8a;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .glass-sect-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, rgba(147, 197, 253, 0.7), transparent);
    }
    .glass-input {
        width: 100%;
        padding: 12px 16px;
        border-radius: 0.75rem;
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.9);
        box-shadow: inset 0 1px 2px rgba(30, 58, 138, 0.05), 0 2px 8px rgba(30, 58, 138, 0.05);
        font-size: 0.9rem;
        color: #1e293b;
        outline: none;
        transition: border 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
    }
    .glass-input:focus {
        background: rgba(255, 255, 255, 0.92);
        border-color: #93c5fd;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15), 0 4px 14px rgba(30, 58, 138, 0.08);
    }
    .glass-input::placeholder { color: #94a3b8; }
    .glass-label-text {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        letter-spacing: 0.03em;
        margin-bottom: 8px;
    }
    .glass-btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 13px 30px;
        border-radius: 0.75rem;
        font-weight: 600;
        font-size: 0.9rem;
        color: #fff;
        background: linear-gradient(115deg, #1e3a8a, #4f46e5);
        box-shadow: 0 10px 25px rgba(30, 58, 138, 0.3);
        transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
        border: 0;
        cursor: pointer;
    }
    .glass-btn-primary:hover {
        transform: translateY(-2px);
        filter: brightness(1.08);
        box-shadow: 0 14px 30px rgba(30, 58, 138, 0.38);
    }
    .glass-btn-ghost {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 28px;
        border-radius: 0.75rem;
        font-weight: 600;
        font-size: 0.9rem;
        color: #1e3a8a;
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.9);
        box-shadow: 0 4px 14px rgba(30, 58, 138, 0.08);
        transition: background 0.15s ease, transform 0.15s ease;
        cursor: pointer;
    }
    .glass-btn-ghost:hover { background: rgba(255, 255, 255, 0.95); transform: translateY(-2px); }
    .glass-pencil {
        position: absolute;
        right: -6px;
        bottom: -6px;
        width: 34px;
        height: 34px;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(8px);
        border: 2px solid #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1e3a8a;
        box-shadow: 0 4px 12px rgba(30, 58, 138, 0.22);
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .glass-pencil:hover { transform: scale(1.1); box-shadow: 0 6px 16px rgba(30, 58, 138, 0.3); }
    .pm-title { display: flex; align-items: center; gap: 2px; height: 20px; }
    .pm-letter { display: grid; grid-template-columns: repeat(5, 2px); grid-auto-rows: 2px; gap: 1px; }
    .pm-space { width: 4px; display: block; }
    .pm-px { width: 100%; height: 100%; background: rgba(30, 58, 138, 0.07); border-radius: 1px; }
    .pm-px.on { background: #1e3a8a; }
    .pm-title.play .pm-px.on {
        opacity: 0;
        transform: scale(0);
        animation: pmPixelIn 0.28s steps(3, end) forwards;
    }
    @keyframes pmPixelIn {
        to { opacity: 1; transform: scale(1); }
    }
    @keyframes lafPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(0.78); }
    }
</style>
<div class="laf-bg">
    <div class="pixel-corner pixel-corner-br" aria-hidden="true"><?= pixelCluster($brMap) ?></div>
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 relative z-10">
    <div data-aos="fade-up">
        <h1 class="text-3xl font-bold text-gray-900">My Profile</h1>
        <p class="text-gray-500 mt-1">Manage your account information</p>
    </div>

    <div class="glass-wrap mt-8">
        <div class="glass-blob glass-blob-1"></div>
        <div class="glass-blob glass-blob-2"></div>
        <div class="glass-blob glass-blob-3"></div>

        <div class="glass-card overflow-hidden" data-aos="fade-up">
            <!-- Header -->
            <div class="glass-header h-20 flex items-center justify-between px-6 md:px-8">
                <span class="glass-label"><?= $user['role'] !== 'admin' ? 'LookForit ID' : '' ?></span>
                <span class="glass-label" style="opacity:0.6">Profile</span>
            </div>

            <!-- Two columns -->
            <div class="grid md:grid-cols-5">
                <!-- Left: identity -->
                <div class="md:col-span-2 px-6 md:px-8 py-8 bg-white/45 backdrop-blur-xl border-b md:border-b-0 md:border-r border-white/60 flex flex-col items-center text-center gap-4">
                    <div class="relative">
                        <div class="w-28 h-28 rounded-full overflow-hidden ring-4 ring-white/90 shadow-xl flex items-center justify-center text-white text-4xl font-bold" style="background:linear-gradient(135deg,#1e3a8a,#4f46e5)">
                            <?php if (!empty($user['avatar'])): ?>
                                <img src="<?= e(imageUrl($user['avatar'])) ?>" alt="<?= e($user['name']) ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <?= strtoupper(substr($user['name'], 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" onclick="openAvatarModal()" title="Change profile photo" class="glass-pencil">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="preview-icon"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/><path d="m15 5 4 4"/></svg>
                        </button>
                    </div>
                    <div class="min-w-0 w-full">
                        <h2 class="glass-name text-2xl font-extrabold truncate"><?= e($user['name']) ?></h2>
                        <p class="text-gray-500 text-sm truncate mt-1"><?= e($user['email']) ?></p>
                    </div>
                    <div class="flex flex-wrap items-center justify-center gap-2">
                        <?php if ($user['role'] === 'admin'): ?>
                            <span class="glass-chip chip-green">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                Admin
                            </span>
                        <?php endif; ?>
                        <?php if ($user['role'] !== 'admin'): ?>
                            <span class="glass-chip">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                                ID: <?= e($user['school_id']) ?>
                            </span>
                        <?php endif; ?>
                        <span class="glass-chip chip-gray">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Since <?= date('M Y', strtotime($user['created_at'])) ?>
                        </span>
                    </div>
                </div>

                <!-- Right: form -->
                <div class="md:col-span-3 px-6 md:px-8 py-8">
                    <form method="POST" action="" class="space-y-8">
                        <?= csrfField() ?>

                        <section>
                            <h3 class="glass-sect-title mb-5">Account Details</h3>
                            <div class="grid sm:grid-cols-2 gap-5">
                                <div>
                                    <label class="glass-label-text">Full Name</label>
                                    <input type="text" name="name" value="<?= e($user['name']) ?>" required class="glass-input">
                                </div>
                                <div>
                                    <label class="glass-label-text">Email Address</label>
                                    <input type="email" name="email" value="<?= e($user['email']) ?>" required class="glass-input">
                                </div>
                            </div>
                        </section>

                        <section>
                            <h3 class="glass-sect-title mb-5">Security</h3>
                            <div class="grid sm:grid-cols-2 gap-5">
                                <div>
                                    <label class="glass-label-text">New Password</label>
                                    <input type="password" name="new_password" class="glass-input" placeholder="Leave blank to keep current">
                                </div>
                                <div>
                                    <label class="glass-label-text">Confirm New Password</label>
                                    <input type="password" name="confirm_new_password" class="glass-input" placeholder="Repeat new password">
                                </div>
                            </div>
                        </section>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 mt-10">
                            <button type="submit" class="glass-btn-primary w-full sm:w-auto">Save Changes</button>
                            <a href="<?= SITE_URL ?>/home" class="glass-btn-ghost w-full sm:w-auto text-center">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<div id="avatarModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50" onclick="if (event.target === this) closeAvatarModal()">
    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full mx-4 p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-gray-900" aria-label="Profile photo">
                <span class="pm-title" id="pmTitle" aria-hidden="true">
                    <?php foreach (str_split($pmTitle) as $pmCh): ?>
                        <?php if ($pmCh === ' '): ?>
                            <span class="pm-space"></span>
                        <?php else: ?>
                            <span class="pm-letter">
                                <?php foreach ($pmFont[$pmCh] as $pmRow): foreach (str_split($pmRow) as $pmC): ?>
                                    <span class="pm-px <?= $pmC === '#' ? 'on' : '' ?>"<?php if ($pmC === '#') echo ' style="animation-delay:' . ($pmDelay += 8) . 'ms"'; ?>></span>
                                <?php endforeach; endforeach; ?>
                            </span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </span>
            </h3>
            <button onclick="closeAvatarModal()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="flex justify-center mb-5">
            <div class="w-28 h-28 bg-blue-900 rounded-2xl flex items-center justify-center text-white text-4xl font-bold overflow-hidden">
                <?php if (!empty($user['avatar'])): ?>
                    <img src="<?= e(imageUrl($user['avatar'])) ?>" alt="<?= e($user['name']) ?>" class="w-full h-full object-cover">
                <?php else: ?>
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                <?php endif; ?>
            </div>
        </div>
        <form method="POST" action="" enctype="multipart/form-data" class="space-y-3" onsubmit="return checkAvatarSize()">
            <?= csrfField() ?>
            <input type="file" name="avatar" id="avatarInput" accept="image/jpeg,image/png,image/gif,image/webp"
                   class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:font-medium hover:file:bg-blue-100 file:cursor-pointer">
            <div class="flex items-center gap-3">
                <button type="submit" name="change_avatar" value="1" class="flex-1 px-5 py-2.5 bg-blue-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-800 transition shadow-sm">Upload photo</button>
                <?php if (!empty($user['avatar'])): ?>
                    <button type="submit" name="remove_avatar" value="1" formnovalidate class="flex-1 px-5 py-2.5 text-sm text-red-600 font-semibold border border-red-200 rounded-lg hover:bg-red-50 transition">Remove</button>
                <?php endif; ?>
            </div>
        </form>
        <p class="text-xs text-gray-400 mt-3 text-center">JPG, PNG, GIF or WEBP. Maximum size 1 MB.</p>
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
function checkAvatarSize() {
    var input = document.getElementById('avatarInput');
    if (input && input.files.length > 0 && input.files[0].size > 1024 * 1024) {
        alert('Photo is too large. Maximum size is 1 MB.');
        return false;
    }
    return true;
}
function openAvatarModal() {
    document.getElementById('avatarModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    document.getElementById('pmTitle').classList.add('play');
}
function closeAvatarModal() {
    document.getElementById('avatarModal').classList.add('hidden');
    document.getElementById('pmTitle').classList.remove('play');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeAvatarModal();
});
</script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="<?= SITE_URL ?>/assets/js/app.js?v=9"></script>
</body>
</html>
