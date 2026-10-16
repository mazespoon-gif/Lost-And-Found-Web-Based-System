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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $turnstileToken = $_POST['cf-turnstile-response'] ?? '';

    if (!verifyTurnstile($turnstileToken)) {
        $error = 'Please complete the CAPTCHA.';
    } elseif (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = db()->prepare("SELECT id, school_id, avatar, name, email, password, role, status FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'pending') {
                $error = 'Your account is pending approval. Please wait for an administrator to approve your registration.';
            } elseif ($user['status'] === 'rejected') {
                $error = 'Your registration request has been rejected. Please contact the administrator.';
            } elseif ($user['status'] === 'banned') {
                $error = 'Your account has been banned. Please contact the administrator.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_status'] = $user['status'];
                $_SESSION['user_school_id'] = $user['school_id'];
                $_SESSION['user_avatar'] = $user['avatar'] ?? null;
                redirect('/home');
            }
        } else {
            $error = 'Credentials is invalid.';
        }
    }
}
$pageTitle = 'Login';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$pxFont = [
    'W' => ['#...#', '#...#', '#...#', '#.#.#', '#.#.#', '##.##', '#...#'],
    'E' => ['#####', '#....', '#....', '####.', '#....', '#....', '#####'],
    'L' => ['#....', '#....', '#....', '#....', '#....', '#....', '#####'],
    'C' => ['.###.', '#...#', '#....', '#....', '#....', '#...#', '.###.'],
    'O' => ['.###.', '#...#', '#...#', '#...#', '#...#', '#...#', '.###.'],
    'M' => ['#...#', '##.##', '#.#.#', '#.#.#', '#...#', '#...#', '#...#'],
    'B' => ['####.', '#...#', '#...#', '####.', '#...#', '#...#', '####.'],
    'A' => ['.###.', '#...#', '#...#', '#####', '#...#', '#...#', '#...#'],
    'K' => ['#...#', '#...#', '#..#.', '###..', '#..#.', '#...#', '#...#'],
];
function pixelWord($word, $pxFont) {
    $html = '';
    foreach (str_split($word) as $ch) {
        $html .= '<div class="login-letter">';
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
    .login-pixel-title {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        margin-bottom: 0.35rem;
    }
    .login-word {
        display: flex;
        justify-content: center;
        gap: 5px;
    }
    .login-letter {
        display: grid;
        grid-template-columns: repeat(5, 5px);
        grid-auto-rows: 5px;
        gap: 1px;
    }
    .login-pixel-title .px {
        width: 100%;
        height: 100%;
        border-radius: 2px;
        background: rgba(30, 58, 138, 0.06);
    }
    .login-pixel-title .px.on {
        background: #1e3a8a;
        opacity: 0;
        transform: scale(0.3);
        transition: opacity 0.2s steps(2, end), transform 0.2s steps(2, end);
    }
    .login-pixel-title .px.on.on-revealed {
        opacity: 1;
        transform: scale(1);
    }
</style>
<div class="min-h-[80vh] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md" data-aos="fade-up">
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
            <div class="text-center mb-8">
                <img src="image/high.webp" alt="LookForit" class="w-16 h-16 mx-auto mb-4">
                <div class="login-pixel-title" aria-label="Welcome Back">
                    <div class="login-word"><?= pixelWord('WELCOME', $pxFont) ?></div>
                    <div class="login-word"><?= pixelWord('BACK', $pxFont) ?></div>
                </div>
                <p class="text-gray-500 text-sm mt-1">Sign in to your TCC LookForit account</p>
            </div>

            <?php if ($msg = flashSuccess()): ?>
                <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg text-sm mb-6 border border-green-100"><?= e($msg) ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-5" id="loginForm">
                <?= csrfField() ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        <input type="email" name="email"
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                               placeholder="you@gmail.com"
                               value="<?= e($email ?? '') ?>"
                               onblur="validateEmail(this)"
                               oninput="hideServerError()">
                    </div>
                    <p class="validation-msg text-xs mt-1 text-red-500 hidden" data-for="email">Please enter a valid email address.</p>
                    <?php if ($error): ?>
                        <p id="serverEmailError" class="text-xs mt-1 text-red-500 font-medium"><?= e($error) ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <input type="password" name="password" id="login-password"
                               class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                               placeholder="Enter your password"
                               onblur="validateRequired(this)">
                        <button type="button" onclick="togglePassword('login-password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                            <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                    <p class="validation-msg text-xs mt-1 text-red-500 hidden" data-for="password">Password is required.</p>
                </div>
                <div class="flex justify-center my-4">
                    <div class="cf-turnstile" data-sitekey="<?= e(TURNSTILE_SITE_KEY) ?>"></div>
                </div>
                <button type="submit" class="w-full py-3 bg-blue-900 text-white font-semibold rounded-lg hover:bg-blue-800 transition shadow-md">
                    Sign In
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Don't have an account? <a href="<?= SITE_URL ?>/register" class="text-blue-700 font-medium hover:underline">Register here</a>
            </p>
        </div>
    </div>
</div>
<script>
(function () {
    var pxs = document.querySelectorAll('.login-pixel-title .px.on');
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
function hideServerError() {
    var el = document.getElementById('serverEmailError');
    if (el) el.remove();
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
    var msg = el.parentElement.parentElement.querySelector('.validation-msg');
    if (!el.value) {
        msg.classList.remove('hidden');
        el.classList.add('border-red-500');
    } else if (msg) {
        msg.classList.add('hidden');
        el.classList.remove('border-red-500');
    }
}
document.getElementById('loginForm').addEventListener('submit', function(e) {
    var email = this.querySelector('[name="email"]');
    var pass = this.querySelector('[name="password"]');
    var valid = true;
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) { validateEmail(email); valid = false; }
    if (!pass.value) { validateRequired(pass); valid = false; }
    if (!valid) e.preventDefault();
});
</script>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="<?= SITE_URL ?>/assets/js/app.js?v=9"></script>
</body>
</html>
