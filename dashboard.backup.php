<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

requireLogin();

$userId = $_SESSION['user_id'];
$userItems = db()->prepare("SELECT * FROM lost_items WHERE user_id = ? ORDER BY created_at DESC");
$userItems->execute([$userId]);
$userItems = $userItems->fetchAll();

$itemCount = count($userItems);

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <?php if ($msg = flashSuccess()): ?>
        <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg text-sm mb-6 border border-green-100"><?= e($msg) ?></div>
    <?php endif; ?>

    <div>
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-500 mt-1">Welcome back, <?= e($_SESSION['user_name']) ?>!</p>
    </div>

    <div class="grid md:grid-cols-3 gap-6 mt-8">
        <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900"><?= $itemCount ?></p>
                    <p class="text-sm text-gray-500">My Lost & Found</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900"><?= formatDate(date('Y-m-d')) ?></p>
                    <p class="text-sm text-gray-500">Member since</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-8 bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">My Lost & Found</h2>
            <a href="<?= SITE_URL ?>/lost_and_found" class="text-sm text-blue-700 hover:underline">Browse All Items</a>
        </div>
        <?php if (empty($userItems)): ?>
            <div class="p-12 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <p class="text-gray-500">You haven't reported any items yet.</p>
                <a href="<?= SITE_URL ?>/report_item" class="inline-block mt-3 px-6 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800 transition text-sm">Report Your First Item</a>
            </div>
        <?php else: ?>
            <style>
            @media (max-width: 639px) {
                #dashTable thead { display: none; }
                #dashTable, #dashTable tbody, #dashTable tr, #dashTable td { display: block; width: 100%; }
                #dashTable { padding: 0.75rem; }
                #dashTable tr {
                    border: 1px solid #e5e7eb;
                    border-radius: 0.75rem;
                    margin-bottom: 0.75rem;
                    padding: 0.35rem 0.25rem;
                    background: #fff;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
                }
                #dashTable tr:last-child { margin-bottom: 0; }
                #dashTable td {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 1rem;
                    padding: 0.5rem 0.75rem;
                    border: none !important;
                    text-align: left !important;
                }
                #dashTable td::before {
                    content: attr(data-label);
                    font-size: 0.72rem;
                    font-weight: 700;
                    text-transform: uppercase;
                    letter-spacing: 0.03em;
                    color: #1e3a8a;
                    flex-shrink: 0;
                }
                #dashTable td a, #dashTable td span { text-align: right; }
            }
            </style>
            <div class="overflow-x-auto">
                <table id="dashTable" class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-left text-sm font-medium text-gray-500">
                            <th class="px-6 py-3">Title</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Location</th>
                            <th class="px-6 py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($userItems as $item): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4" data-label="Title">
                                    <a href="<?= SITE_URL ?>/item_detail?id=<?= $item['id'] ?>" class="font-medium text-gray-900 hover:text-blue-700">
                                        <?= e($item['title']) ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4" data-label="Status"><span class="text-xs font-medium <?= $item['status'] === 'lost' ? 'text-red-600 bg-red-100' : 'text-green-600 bg-green-100' ?> px-2.5 py-1 rounded-full"><?= ucfirst(e($item['status'])) ?></span></td>
                                <td class="px-6 py-4 text-sm text-gray-500" data-label="Location"><?= e($item['location']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500" data-label="Date"><?= formatDate($item['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="<?= SITE_URL ?>/assets/js/app.js?v=8"></script>
</body>
</html>
