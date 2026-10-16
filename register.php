<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/functions.php';

if (isLoggedIn()) {
    redirect('/home');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $schoolId = trim($_POST['school_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $turnstileToken = $_POST['cf-turnstile-response'] ?? '';

    if (!verifyTurnstile($turnstileToken)) {
        $error = 'Please complete the CAPTCHA.';
    } elseif (empty($schoolId) || empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = db()->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = db()->prepare("INSERT INTO users (school_id, name, email, password, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->execute([$schoolId, $name, $email, $hashed]);

            pusherTrigger('admin-channel', 'users-changed', ['action' => 'register', 'id' => (int)db()->lastInsertId()]);

            $success = 'Your account has been created! An administrator will review your registration and approve your account. You will be able to log in once approved.';
        }
    }
}

$pageTitle = 'Register';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$pxFont = [
    'C' => ['.###.', '#...#', '#....', '#....', '#....', '#...#', '.###.'],
    'R' => ['####.', '#...#', '#...#', '####.', '#.#..', '#..#.', '#...#'],
    'E' => ['#####', '#....', '#....', '####.', '#....', '#....', '#####'],
    'A' => ['.###.', '#...#', '#...#', '#####', '#...#', '#...#', '#...#'],
    'T' => ['#####', '..#..', '..#..', '..#..', '..#..', '..#..', '..#..'],
    'O' => ['.###.', '#...#', '#...#', '#...#', '#...#', '#...#', '.###.'],
    'U' => ['#...#', '#...#', '#...#', '#...#', '#...#', '#...#', '.###.'],
    'N' => ['#...#', '##..#', '##..#', '#.#.#', '#..##', '#..##', '#...#'],
];
function pixelWord($word, $pxFont) {
    $html = '';
    foreach (str_split($word) as $ch) {
        $html .= '<div class="reg-letter">';
        foreach ($pxFont[$ch] as $row) {
            foreach (str_split($row) as $c) {
                $html .= '<span class="px' . ($c === '#' ? ' on' : '') . '"></span>';
            }
        }
        $html .= '</div>';
    }
    return $html;
}
?>
<style>
    .reg-pixel-title {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        margin-bottom: 0.35rem;
    }
    .reg-word {
        display: flex;
        justify-content: center;
        gap: 5px;
    }
    .reg-letter {
        display: grid;
        grid-template-columns: repeat(5, 5px);
        grid-auto-rows: 5px;
        gap: 1px;
    }
    .reg-pixel-title .px {
        width: 100%;
        height: 100%;
        border-radius: 2px;
        background: rgba(30, 58, 138, 0.06);
    }
    .reg-pixel-title .px.on {
        background: #1e3a8a;
        opacity: 0;
        transform: scale(0.3);
        transition: opacity 0.2s steps(2, end), transform 0.2s steps(2, end);
    }
    .reg-pixel-title .px.on.on-revealed {
        opacity: 1;
        transform: scale(1);
    }
</style>
<div class="min-h-[80vh] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md" data-aos="fade-up">
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
            <div class="text-center mb-8">
                <img src="image/high.webp" alt="LookForit" class="w-16 h-16 mx-auto mb-4">
                <div class="reg-pixel-title" aria-label="Create Account">
                    <div class="reg-word"><?= pixelWord('CREATE', $pxFont) ?></div>
                    <div class="reg-word"><?= pixelWord('ACCOUNT', $pxFont) ?></div>
                </div>
                <p class="text-gray-500 text-sm mt-1">Join the TCC LookForit</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg text-sm mb-6 border border-red-100"><?= e($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-50 text-green-700 px-6 py-5 rounded-lg text-sm mb-6 border border-green-100 text-center">
                    <svg class="w-12 h-12 text-green-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="font-semibold text-green-800 mb-1">Registration Submitted!</p>
                    <p><?= e($success) ?></p>
                    <a href="<?= SITE_URL ?>/login" class="inline-block mt-4 px-6 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition shadow-sm">Go to Login</a>
                </div>
            <?php else: ?>

            <form method="POST" action="" class="space-y-4" id="registerForm">
                <?= csrfField() ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <input type="text" name="name" value="<?= e($_POST['name'] ?? '') ?>"
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                               placeholder="Melany Mendelssohn Heart"
                               onblur="validateRequired(this)">
                    </div>
                    <p class="validation-msg text-xs mt-1 text-red-500 hidden" data-for="name">Name is required.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>"
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                               placeholder="you@gmail.com"
                               onblur="validateEmail(this)">
                    </div>
                    <p class="validation-msg text-xs mt-1 text-red-500 hidden" data-for="email">Please enter a valid email address.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">School ID</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 19a4 4 0 00-8 0"/><path d="M16 10h2"/><path d="M16 14h2"/><circle cx="9" cy="12" r="3"/><rect x="2" y="5" width="20" height="14" rx="2"/></svg>
                        <input type="text" name="school_id" value="<?= e($_POST['school_id'] ?? '') ?>"
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                               placeholder="2023-1210"
                               onblur="validateRequired(this)">
                    </div>
                    <p class="validation-msg text-xs mt-1 text-red-500 hidden" data-for="school_id">School ID is required.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <input type="password" name="password" id="reg-password"
                               class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                               placeholder="At least 6 characters"
                               onblur="validatePassword(this)">
                        <button type="button" onclick="togglePassword('reg-password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                            <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                    <p class="validation-msg text-xs mt-1 text-red-500 hidden" data-for="password">Password must be at least 6 characters.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <input type="password" name="confirm_password" id="reg-confirm-password"
                               class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                               placeholder="Repeat your password"
                               onblur="validateConfirmPassword(this)">
                        <button type="button" onclick="togglePassword('reg-confirm-password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                            <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                    <p class="validation-msg text-xs mt-1 text-red-500 hidden" data-for="confirm_password">Passwords do not match.</p>
                </div>
                <div class="flex justify-center my-3">
                    <div class="cf-turnstile" data-sitekey="<?= e(TURNSTILE_SITE_KEY) ?>"></div>
                </div>
                <button type="submit" class="w-full py-3 bg-blue-900 text-white font-semibold rounded-lg hover:bg-blue-800 transition shadow-md">
                    Create Account
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Already have an account? <a href="<?= SITE_URL ?>/login" class="text-blue-700 font-medium hover:underline">Sign in</a>
            </p>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
(function () {
    var pxs = document.querySelectorAll('.reg-pixel-title .px.on');
    var i = 0;
    function step() {
        var end = Math.min(i + 6, pxs.length);
        for (; i < end; i++) pxs[i].classList.add('on-revealed');
        if (i < pxs.length) setTimeout(step, 55);
    }
    setTimeout(step, 250);
})();
function togglePassword(inputId, btn) {
    var input = document.getElementById(inputId);
    var open = btn.querySelector('.eye-open');
    var closed = btn.querySelector('.eye-closed');
    if (input.type === 'password') {
        input.type = 'text';
        open.classList.add('hidden');
        closed.classList.remove('hidden');
    } else {
        input.type = 'password';
        open.classList.remove('hidden');
        closed.classList.add('hidden');
    }
}
function validateEmail(el) {
    var msg = el.parentElement.querySelector('.validation-msg');
    if (el.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(el.value)) {
        msg.classList.remove('hidden');
        el.classList.add('border-red-500');
    } else {
        msg.classList.add('hidden');
        el.classList.remove('border-red-500');
    }
}
function validateRequired(el) {
    var msg = el.parentElement.querySelector('.validation-msg');
    if (!el.value) {
        if (msg) msg.classList.remove('hidden');
        el.classList.add('border-red-500');
    } else {
        if (msg) msg.classList.add('hidden');
        el.classList.remove('border-red-500');
    }
}
function validatePassword(el) {
    var msg = el.parentElement.parentElement.querySelector('.validation-msg');
    if (el.value && el.value.length < 6) {
        msg.classList.remove('hidden');
        el.classList.add('border-red-500');
    } else {
        msg.classList.add('hidden');
        el.classList.remove('border-red-500');
    }
}
function validateConfirmPassword(el) {
    var pass = document.getElementById('reg-password');
    var msg = el.parentElement.parentElement.querySelector('.validation-msg');
    if (el.value && el.value !== pass.value) {
        msg.classList.remove('hidden');
        el.classList.add('border-red-500');
    } else {
        msg.classList.add('hidden');
        el.classList.remove('border-red-500');
    }
}
document.getElementById('registerForm').addEventListener('submit', function(e) {
    var name = this.querySelector('[name="name"]');
    var email = this.querySelector('[name="email"]');
    var schoolId = this.querySelector('[name="school_id"]');
    var pass = this.querySelector('[name="password"]');
    var confirm = this.querySelector('[name="confirm_password"]');
    var valid = true;
    if (!name.value) { validateRequired(name); valid = false; }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) { validateEmail(email); valid = false; }
    if (!schoolId.value) { validateRequired(schoolId); valid = false; }
    if (!pass.value || pass.value.length < 6) { validatePassword(pass); valid = false; }
    if (confirm.value !== pass.value) { validateConfirmPassword(confirm); valid = false; }
    if (!valid) e.preventDefault();
});
</script>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="<?= SITE_URL ?>/assets/js/app.js?v=9"></script>
</body>
</html>
