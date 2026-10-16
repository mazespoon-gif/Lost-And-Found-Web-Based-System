<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
requireAdmin();

$totalLost = getTotalLostItems('lost');
$totalFound = getTotalLostItems('found');
$totalUsers = getTotalUsers();
$recentLostItems = getLostItems(null, 5);

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <?php if ($msg = flashSuccess()): ?>
        <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg text-sm mb-6 border border-green-100"><?= e($msg) ?></div>
    <?php endif; ?>

    <div data-aos="fade-up">
        <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
        <p class="text-gray-500 mt-1">Manage your lost items</p>
    </div>

    <div class="grid md:grid-cols-3 gap-6 mt-8" data-aos="fade-up">
        <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6 card-hover">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <div>
                    <p class="text-3xl font-bold text-gray-900"><?= $totalLost ?></p>
                    <p class="text-sm text-gray-500">Lost Items</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6 card-hover">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-3xl font-bold text-gray-900"><?= $totalFound ?></p>
                    <p class="text-sm text-gray-500">Found Items</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6 card-hover">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                </div>
                <div>
                    <p class="text-3xl font-bold text-gray-900"><?= $totalUsers ?></p>
                    <p class="text-sm text-gray-500">Total Users</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-8 mt-8">
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden" data-aos="fade-up">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-xl font-bold text-gray-900">Recent Lost & Found</h2>
            </div>
            <?php if (empty($recentLostItems)): ?>
                <div class="p-8 text-center text-gray-500">No items yet.</div>
            <?php else: ?>
                <div class="divide-y divide-gray-100">
                    <?php foreach ($recentLostItems as $item): ?>
                        <div class="p-5 hover:bg-gray-50 flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <a href="<?= SITE_URL ?>/item_detail?id=<?= $item['id'] ?>" class="font-medium text-gray-900 hover:text-blue-700"><?= e($item['title']) ?></a>
                                <p class="text-sm text-gray-500"><span class="font-medium <?= $item['status'] === 'lost' ? 'text-red-600' : 'text-green-600' ?>"><?= ucfirst(e($item['status'])) ?></span> &middot; <?= timeAgo($item['created_at']) ?></p>
                            </div>
                            <div class="flex gap-2 ml-4">
                                <a href="<?= SITE_URL ?>/edit_lost_item?id=<?= $item['id'] ?>" class="text-sm text-blue-600 hover:underline">Edit</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="space-y-6" data-aos="fade-up">
            <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Quick Actions</h2>
                <div class="space-y-3">
                    <a href="<?= SITE_URL ?>/manage_lost_items" class="flex items-center gap-3 px-4 py-3 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition font-medium text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Create New Item
                    </a>
                    <a href="<?= SITE_URL ?>/manage_lost_items" class="flex items-center gap-3 px-4 py-3 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition font-medium text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        Manage Lost & Found
                    </a>

                    <a href="<?= SITE_URL ?>/manage_users" class="flex items-center gap-3 px-4 py-3 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition font-medium text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
                        Manage Users
                    </a>
                </div>
            </div>

            <div class="bg-gradient-to-br from-blue-800 to-blue-900 rounded-xl shadow-md p-6 text-white">
                <h3 class="font-bold text-lg mb-2"><?= SITE_NAME ?></h3>
                <p class="text-blue-200 text-sm"><?= SITE_TAGLINE ?></p>
                <div class="mt-4 text-xs text-blue-300">
                    Running smoothly with <?= $totalLost ?> lost and <?= $totalFound ?> found items.
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="<?= SITE_URL ?>/assets/js/app.js?v=8"></script>
</body>
</html>
