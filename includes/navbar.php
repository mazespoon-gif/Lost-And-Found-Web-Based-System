<nav class="sticky top-0 z-50 bg-white/90 nav-blur border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="<?= SITE_URL ?>/home" class="flex items-center gap-2">
                <img src="image/high.webp" alt="LookForit" class="w-9 h-9">
                <span class="text-xl font-bold text-blue-900">LookForit</span>
            </a>

            <div class="hidden md:flex items-center gap-6">
                <?php $currentPage = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: 'home'); ?>
                <a href="<?= SITE_URL ?>/home" class="text-sm font-medium <?= $currentPage === 'home' ? 'text-blue-700 font-bold' : 'text-gray-700' ?> hover:text-blue-700 transition">Home</a>
                <a href="<?= SITE_URL ?>/lost_and_found" class="text-sm font-medium <?= $currentPage === 'lost_and_found' ? 'text-blue-700 font-bold' : 'text-gray-700' ?> hover:text-blue-700 transition">Lost & Found</a>
            </div>

            <div class="hidden md:flex items-center gap-3">
                <?php if (isLoggedIn()): ?>
                    <?php $unreadCount = getUnreadNotificationCount($_SESSION['user_id']); ?>
                    <div class="relative" id="notif-wrapper">
                        <button onclick="toggleNotifDropdown()" class="relative p-2 text-gray-500 hover:text-blue-700 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <?php if ($unreadCount > 0): ?>
                                <span class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-xs font-bold w-4 h-4 flex items-center justify-center rounded-full"><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span>
                            <?php endif; ?>
                        </button>
                        <div id="notifDropdown" class="hidden absolute right-0 mt-1 w-80 max-w-[calc(100vw-2rem)] bg-white rounded-xl shadow-xl border border-gray-100 z-50">
                            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                                <span class="text-sm font-bold text-gray-900">Notifications</span>
                                <?php if ($unreadCount > 0): ?>
                                    <form method="POST" action="<?= SITE_URL ?>/mark_notifications" class="inline">
                                        <?= csrfField() ?>
                                        <button type="submit" class="text-xs text-blue-600 hover:underline">Mark all read</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                <?php
                                $notifications = getNotifications($_SESSION['user_id'], 10);
                                if (empty($notifications)):
                                ?>
                                    <div class="px-4 py-8 text-center text-sm text-gray-400">No notifications yet.</div>
                                <?php else: ?>
                                    <?php foreach ($notifications as $notif): ?>
                                        <div class="flex items-start gap-3 px-4 py-3 hover:bg-blue-50 transition border-b border-gray-50 <?= $notif['is_read'] ? '' : 'bg-blue-50/50' ?>">
                                            <a href="<?= e($notif['link']) ?>" target="_blank" onclick="markNotifRead(<?= $notif['id'] ?>)" class="flex-1 min-w-0 flex items-start gap-3">
                                                <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mt-0.5">
                                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm text-gray-700 leading-snug"><?= e($notif['message']) ?></p>
                                                    <p class="text-xs text-gray-400 mt-1"><?= timeAgo($notif['created_at']) ?></p>
                                                </div>
                                                <?php if (!$notif['is_read']): ?>
                                                    <div class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                                <?php endif; ?>
                                            </a>
                                            <button onclick="deleteNotification(<?= $notif['id'] ?>, this)" class="flex-shrink-0 text-gray-300 hover:text-red-500 transition mt-0.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="relative group">
                        <button class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 hover:text-blue-700 transition">
                            <?php if (!empty($_SESSION['user_avatar'])): ?>
                                <img src="<?= e(imageUrl($_SESSION['user_avatar'])) ?>" alt="" class="w-7 h-7 rounded-full object-cover">
                            <?php else: ?>
                                <span class="w-7 h-7 bg-blue-800 rounded-full flex items-center justify-center text-white text-xs font-bold"><?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?></span>
                            <?php endif; ?>
                            <span><?= e($_SESSION['user_name'] ?? 'User') ?></span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div class="absolute right-0 mt-1 w-48 bg-white rounded-xl shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 py-2">
                            <a href="<?= SITE_URL ?>/dashboard" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700">Dashboard</a>
                            <a href="<?= SITE_URL ?>/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700">Profile</a>
                            <?php if (isAdmin()):
                                $pendingCount = db()->query("SELECT COUNT(*) FROM users WHERE status = 'pending'")->fetchColumn();
                            ?>
                                <hr class="my-1 border-gray-100">
                                <a href="<?= SITE_URL ?>/admin" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700">Admin Panel</a>
                                <a href="<?= SITE_URL ?>/manage_lost_items" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700">Lost & Found</a>
                                <a href="<?= SITE_URL ?>/manage_users" class="relative block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700">Users
                                    <?php if ($pendingCount > 0): ?>
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-red-500 text-white text-xs font-bold w-5 h-5 flex items-center justify-center rounded-full"><?= $pendingCount ?></span>
                                    <?php endif; ?>
                                </a>
                            <?php endif; ?>
                            <hr class="my-1 border-gray-100">
                            <a href="<?= SITE_URL ?>/logout" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/login" class="px-4 py-2 text-sm font-medium text-blue-700 hover:text-blue-900 transition">Login</a>
                    <a href="<?= SITE_URL ?>/register" class="px-5 py-2 text-sm font-medium bg-blue-800 text-white rounded-lg hover:bg-blue-900 transition shadow-sm">Register</a>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-1 md:hidden">
                <?php if (isLoggedIn()): ?>
                <div class="relative" id="mobile-notif-wrapper">
                    <button onclick="toggleMobileNotifDropdown()" class="relative p-2 text-gray-500 hover:text-blue-700 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <?php if ($unreadCount > 0): ?>
                            <span class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-xs font-bold w-4 h-4 flex items-center justify-center rounded-full"><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span>
                        <?php endif; ?>
                    </button>
                    <div id="mobileNotifDropdown" class="hidden absolute right-0 mt-1 w-80 max-w-[calc(100vw-2rem)] bg-white rounded-xl shadow-xl border border-gray-100 z-50">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <span class="text-sm font-bold text-gray-900">Notifications</span>
                        <?php if ($unreadCount > 0): ?>
                            <form method="POST" action="<?= SITE_URL ?>/mark_notifications" class="inline">
                                <?= csrfField() ?>
                                <button type="submit" class="text-xs text-blue-600 hover:underline">Mark all read</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="max-h-80 overflow-y-auto">
                        <?php if (empty($notifications)): ?>
                            <div class="px-4 py-8 text-center text-sm text-gray-400">No notifications yet.</div>
                        <?php else: ?>
                            <?php foreach ($notifications as $notif): ?>
                                <div class="flex items-start gap-3 px-4 py-3 hover:bg-blue-50 transition border-b border-gray-50 <?= $notif['is_read'] ? '' : 'bg-blue-50/50' ?>">
                                    <a href="<?= e($notif['link']) ?>" target="_blank" class="flex-1 min-w-0 flex items-start gap-3">
                                        <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mt-0.5">
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-gray-700 leading-snug"><?= e($notif['message']) ?></p>
                                            <p class="text-xs text-gray-400 mt-1"><?= timeAgo($notif['created_at']) ?></p>
                                        </div>
                                        <?php if (!$notif['is_read']): ?>
                                            <div class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                        <?php endif; ?>
                                    </a>
                                    <button onclick="deleteNotification(<?= $notif['id'] ?>, this)" class="flex-shrink-0 text-gray-300 hover:text-red-500 transition mt-0.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

                <button id="mobile-menu-btn" class="relative w-10 h-10 flex items-center justify-center text-gray-700 hover:text-blue-700 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
    </div>
</nav>

<script>
function toggleNotifDropdown() {
    var dd = document.getElementById('notifDropdown');
    dd.classList.toggle('hidden');
    if (!dd.classList.contains('hidden')) {
        fetch('<?= SITE_URL ?>/mark_notifications', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'csrf_token=<?= $_SESSION["csrf_token"] ?? "" ?>' }).then(function() {
            document.querySelectorAll('#notifDropdown .bg-blue-500').forEach(function(d) { d.remove(); });
        });
    }
}
function markAllRead() {
    fetch('<?= SITE_URL ?>/mark_notifications', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'csrf_token=<?= $_SESSION["csrf_token"] ?? "" ?>' }).then(function() {
        document.querySelectorAll('#notifDropdown .bg-blue-500').forEach(function(d) { d.remove(); });
        var badge = document.querySelector('#notif-wrapper .bg-red-500');
        if (badge) badge.remove();
        var btn = document.querySelector('#notifDropdown button');
        if (btn) btn.remove();
    });
}
function markNotifRead(id) {
    fetch('<?= SITE_URL ?>/mark_notification_read?id=' + id, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'csrf_token=<?= $_SESSION["csrf_token"] ?? "" ?>' });
}
function deleteNotification(id, btn) {
    fetch('<?= SITE_URL ?>/delete_notification', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'csrf_token=<?= $_SESSION["csrf_token"] ?? "" ?>&id=' + id }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.success) {
            var item = btn.closest('.flex');
            if (item) { item.style.transition = 'opacity 0.3s ease, max-height 0.3s ease'; item.style.opacity = '0'; item.style.maxHeight = '0'; item.style.overflow = 'hidden'; setTimeout(function() { item.remove(); }, 300); }
        }
    });
}
function toggleMobileNotifDropdown() {
    var dd = document.getElementById('mobileNotifDropdown');
    if (dd) dd.classList.toggle('hidden');
}
document.addEventListener('click', function(e) {
    var wrapper = document.getElementById('notif-wrapper');
    var dd = document.getElementById('notifDropdown');
    if (wrapper && dd && !wrapper.contains(e.target)) {
        dd.classList.add('hidden');
    }
    var mWrapper = document.getElementById('mobile-notif-wrapper');
    var mDd = document.getElementById('mobileNotifDropdown');
    if (mWrapper && mDd && !mWrapper.contains(e.target)) {
        mDd.classList.add('hidden');
    }
});
</script>

<?php if (isLoggedIn()): ?>
<script>
(function() {
    var userId = <?= $_SESSION['user_id'] ?>;
    var notifChannel = pusher.subscribe('notifications-' + userId);
    notifChannel.bind('new-notification', function(data) {
        var notifList = document.querySelector('#notifDropdown .max-h-80');
        if (notifList && !notifList.querySelector('.px-4.py-8')) {
            var html = '<a href="' + data.link + '" target="_blank" class="block px-4 py-3 hover:bg-blue-50 transition border-b border-gray-50 bg-blue-50/50">'
                + '<div class="flex items-start gap-3">'
                + '<div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mt-0.5">'
                + '<svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>'
                + '</div>'
                + '<div class="flex-1 min-w-0"><p class="text-sm text-gray-700 leading-snug">' + data.message + '</p>'
                + '<p class="text-xs text-gray-400 mt-1">' + data.created_at + '</p></div>'
                + '<div class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"></div>'
                + '</div></a>';
            var tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            notifList.insertBefore(tempDiv.firstElementChild, notifList.firstChild);
        }

        var existingBadge = document.querySelector('#notif-wrapper .bg-red-500');
        if (!existingBadge) {
            var btn = document.querySelector('#notif-wrapper button');
            if (btn) {
                var badge = document.createElement('span');
                badge.className = 'absolute -top-0.5 -right-0.5 bg-red-500 text-white text-xs font-bold w-4 h-4 flex items-center justify-center rounded-full';
                badge.textContent = '1';
                btn.appendChild(badge);
            }
        }
    });
})();
</script>
<?php endif; ?>

<div id="mobile-overlay" onclick="closeMobileMenu()" class="fixed inset-0 bg-black/50 z-[60] hidden opacity-0 transition-opacity duration-300"></div>
<div id="mobile-menu" class="fixed top-0 right-0 z-[70] h-full w-72 max-w-[85vw] bg-white shadow-2xl border-l border-gray-200 hidden translate-x-full transition-transform duration-300 ease-in-out">
    <div class="bg-blue-900 flex items-center justify-between px-5 h-16">
        <span class="text-sm font-bold text-white tracking-wide">Menu</span>
        <button id="mobile-close-btn" onclick="closeMobileMenu()" class="p-2 text-blue-200 hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
        <div class="px-3 py-3 space-y-0.5 overflow-y-auto mobile-menu-scroll" style="max-height: calc(100vh - 4rem);">
            <a href="<?= SITE_URL ?>/home" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold <?= $currentPage === 'home' ? 'text-blue-700 bg-blue-50 border-l-2 border-blue-700' : 'text-gray-700 hover:text-blue-900 hover:bg-gradient-to-r hover:from-blue-50 hover:to-white hover:border-l-2 hover:border-blue-700 border-l-2 border-transparent' ?> rounded-xl transition-all duration-200 -ml-px">
                <svg class="w-5 h-5 <?= $currentPage === 'home' ? 'text-blue-700' : 'text-blue-600' ?> shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Home
            </a>
            <a href="<?= SITE_URL ?>/lost_and_found" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold <?= $currentPage === 'lost_and_found' ? 'text-blue-700 bg-blue-50 border-l-2 border-blue-700' : 'text-gray-700 hover:text-blue-900 hover:bg-gradient-to-r hover:from-blue-50 hover:to-white hover:border-l-2 hover:border-blue-700 border-l-2 border-transparent' ?> rounded-xl transition-all duration-200 -ml-px">
                <svg class="w-5 h-5 <?= $currentPage === 'lost_and_found' ? 'text-blue-700' : 'text-blue-600' ?> shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Lost & Found
            </a>
            <?php if (isLoggedIn()): ?>
                <hr class="my-2 border-gray-200">
                <a href="<?= SITE_URL ?>/dashboard" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold <?= $currentPage === 'dashboard' ? 'text-blue-700 bg-blue-50 border-l-2 border-blue-700' : 'text-gray-700 hover:text-blue-900 hover:bg-gradient-to-r hover:from-blue-50 hover:to-white hover:border-l-2 hover:border-blue-700 border-l-2 border-transparent' ?> rounded-xl transition-all duration-200 -ml-px">
                    <svg class="w-5 h-5 <?= $currentPage === 'dashboard' ? 'text-blue-700' : 'text-blue-600' ?> shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Dashboard
                </a>
                <a href="<?= SITE_URL ?>/profile" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold <?= $currentPage === 'profile' ? 'text-blue-700 bg-blue-50 border-l-2 border-blue-700' : 'text-gray-700 hover:text-blue-900 hover:bg-gradient-to-r hover:from-blue-50 hover:to-white hover:border-l-2 hover:border-blue-700 border-l-2 border-transparent' ?> rounded-xl transition-all duration-200 -ml-px">
                    <svg class="w-5 h-5 <?= $currentPage === 'profile' ? 'text-blue-700' : 'text-blue-600' ?> shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Profile
                </a>
                <?php if (isAdmin()):
                    $pendingCountMobile = db()->query("SELECT COUNT(*) FROM users WHERE status = 'pending'")->fetchColumn();
                ?>
                    <hr class="my-2 border-gray-200">
                    <p class="px-4 py-2 text-xs font-bold text-blue-800 uppercase tracking-widest">Admin</p>
                    <a href="<?= SITE_URL ?>/admin" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold <?= $currentPage === 'admin' ? 'text-blue-700 bg-blue-50 border-l-2 border-blue-700' : 'text-gray-700 hover:text-blue-900 hover:bg-gradient-to-r hover:from-blue-50 hover:to-white hover:border-l-2 hover:border-blue-700 border-l-2 border-transparent' ?> rounded-xl transition-all duration-200 -ml-px">
                        <svg class="w-5 h-5 <?= $currentPage === 'admin' ? 'text-blue-700' : 'text-blue-600' ?> shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Admin Panel
                    </a>
                    <a href="<?= SITE_URL ?>/manage_lost_items" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold <?= $currentPage === 'manage_lost_items' ? 'text-blue-700 bg-blue-50 border-l-2 border-blue-700' : 'text-gray-700 hover:text-blue-900 hover:bg-gradient-to-r hover:from-blue-50 hover:to-white hover:border-l-2 hover:border-blue-700 border-l-2 border-transparent' ?> rounded-xl transition-all duration-200 -ml-px">
                        <svg class="w-5 h-5 <?= $currentPage === 'manage_lost_items' ? 'text-blue-700' : 'text-blue-600' ?> shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Lost & Found
                    </a>
                    <a href="<?= SITE_URL ?>/manage_users" class="relative flex items-center gap-3 px-4 py-3 text-sm font-semibold <?= $currentPage === 'manage_users' ? 'text-blue-700 bg-blue-50 border-l-2 border-blue-700' : 'text-gray-700 hover:text-blue-900 hover:bg-gradient-to-r hover:from-blue-50 hover:to-white hover:border-l-2 hover:border-blue-700 border-l-2 border-transparent' ?> rounded-xl transition-all duration-200 -ml-px">
                        <svg class="w-5 h-5 <?= $currentPage === 'manage_users' ? 'text-blue-700' : 'text-blue-600' ?> shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Users
                        <?php if ($pendingCountMobile > 0): ?>
                            <span class="absolute right-4 bg-red-500 text-white text-xs font-bold w-5 h-5 flex items-center justify-center rounded-full"><?= $pendingCountMobile ?></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
                <hr class="my-2 border-gray-200">
                <a href="<?= SITE_URL ?>/logout" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold text-red-600 hover:text-red-800 rounded-xl hover:bg-gradient-to-r hover:from-red-50 hover:to-white hover:border-l-2 hover:border-red-500 transition-all duration-200 -ml-px border-l-2 border-transparent">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </a>
            <?php else: ?>
                <hr class="my-2 border-gray-200">
                <a href="<?= SITE_URL ?>/login" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold text-blue-800 hover:text-blue-900 rounded-xl hover:bg-gradient-to-r hover:from-blue-50 hover:to-white hover:border-l-2 hover:border-blue-700 transition-all duration-200 -ml-px border-l-2 border-transparent">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    Login
                </a>
                <a href="<?= SITE_URL ?>/register" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold text-white bg-gradient-to-r from-blue-900 to-blue-800 hover:from-blue-800 hover:to-blue-700 rounded-xl transition-all duration-200 mt-1 shadow-md ml-1">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    Register
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>
