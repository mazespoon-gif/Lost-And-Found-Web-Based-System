<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/functions.php';
requireAdmin();

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;
$totalItems = getTotalLostItems();
$items = getLostItems(null, $perPage, $offset);

$pageTitle = 'Manage Lost & Found';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between" data-aos="fade-up">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manage Lost & Found</h1>
            <p class="text-lg font-bold text-blue-800 mt-1"><?= $totalItems ?> total items</p>
        </div>
        <a href="<?= SITE_URL ?>/report_item" class="whitespace-nowrap px-6 py-3 bg-blue-900 text-white font-medium rounded-lg hover:bg-blue-800 transition shadow-sm flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Item
        </a>
    </div>

    <div class="mt-6 flex items-center justify-end">
        <div class="flex flex-wrap items-center justify-center gap-1 bg-gray-100/80 rounded-xl p-1 border border-blue-300 max-w-full" id="sortChips">
            <button type="button" data-sort="default" class="sort-chip active" title="Default order (newest first)">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 16h6"/></svg>
                Default
            </button>
            <button type="button" data-sort="newest" class="sort-chip" title="Newest first">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Newest
            </button>
            <button type="button" data-sort="oldest" class="sort-chip" title="Oldest first">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Oldest
            </button>
        </div>
    </div>

    <div class="mt-4 bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden" data-aos="fade-up">
        <?php if (empty($items)): ?>
            <div class="p-12 text-center">
                <p class="text-gray-500">No items found.</p>
                <a href="<?= SITE_URL ?>/report_item" class="inline-block mt-3 px-6 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800 transition">Report First Item</a>
            </div>
        <?php else: ?>
            <style>
            @media (max-width: 639px) {
                #itemsTable thead { display: none; }
                #itemsTable, #itemsTable tbody, #itemsTable tr, #itemsTable td { display: block; width: 100%; }
                #itemsTable { padding: 0.75rem; }
                #itemsTable tr {
                    border: 1px solid #e5e7eb;
                    border-radius: 0.75rem;
                    margin-bottom: 0.75rem;
                    padding: 0.35rem 0.25rem;
                    background: #fff;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
                }
                #itemsTable tr:last-child { margin-bottom: 0; }
                #itemsTable td {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 1rem;
                    padding: 0.5rem 0.75rem;
                    border: none !important;
                    text-align: left !important;
                }
                #itemsTable td::before {
                    content: attr(data-label);
                    font-size: 0.72rem;
                    font-weight: 700;
                    text-transform: uppercase;
                    letter-spacing: 0.03em;
                    color: #1e3a8a;
                    flex-shrink: 0;
                }
                #itemsTable td a, #itemsTable td span { text-align: right; }
            }
            .sort-chip {
                display: inline-flex;
                align-items: center;
                gap: 0.3rem;
                padding: 0.45rem 0.8rem;
                border-radius: 0.65rem;
                font-size: 0.75rem;
                font-weight: 600;
                color: #6b7280;
                transition: all 0.2s ease;
                user-select: none;
            }
            .sort-chip.active {
                background: #1e3a8a;
                color: #ffffff;
                box-shadow: 0 2px 10px rgba(30, 58, 138, 0.35);
                transform: scale(1.03);
            }
            .sort-chip:not(.active):hover {
                background: #ffffff;
                color: #111827;
                box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
            }
            </style>
            <div class="overflow-x-auto">
                <table id="itemsTable" class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-left text-sm font-medium text-gray-500">
                            <th class="px-6 py-4">Title</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Location</th>
                            <th class="px-6 py-4">Author</th>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($items as $i => $item): ?>
                            <tr class="hover:bg-gray-50" data-index="<?= $i ?>" data-status="<?= e($item['status']) ?>" data-date="<?= strtotime($item['created_at']) ?>">
                                <td class="px-6 py-4" data-label="Title">
                                    <a href="<?= SITE_URL ?>/item_detail?id=<?= $item['id'] ?>" class="font-medium text-gray-900 hover:text-blue-700">
                                        <?= e($item['title']) ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-sm" data-label="Status"><span class="text-xs font-medium <?= $item['status'] === 'lost' ? 'text-red-600 bg-red-100' : 'text-green-600 bg-green-100' ?> px-2.5 py-1 rounded-full"><?= ucfirst(e($item['status'])) ?></span></td>
                                <td class="px-6 py-4 text-sm text-gray-500" data-label="Location"><?= e($item['location']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500" data-label="Author"><?= e($item['author_name']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500" data-label="Date"><?= formatDate($item['created_at']) ?></td>
                                <td class="px-6 py-4 text-right" data-label="Actions">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="<?= SITE_URL ?>/edit_lost_item?id=<?= $item['id'] ?>" class="px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition">Edit</a>
                                        <button type="button"
                                                onclick="openDeleteModal(<?= $item['id'] ?>, '<?= e($item['title']) ?>')"
                                                class="px-4 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100">
                <?= paginationLinks($totalItems, $perPage, $page, SITE_URL . '/manage_lost_items?') ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="mt-4 text-right">
        <a href="<?= SITE_URL ?>/admin" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to Admin Dashboard</a>
    </div>
</div>

<div id="deleteItemModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 p-6" data-aos="zoom-in">
        <div class="text-center">
            <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Delete Item</h3>
            <p class="text-gray-600 text-sm mb-1">Are you sure you want to delete <strong id="deleteItemTitle" class="text-gray-900"></strong>?</p>
            <p class="text-gray-500 text-xs mb-6">This action cannot be undone.</p>
            <form id="deleteItemForm" method="POST" action="<?= SITE_URL ?>/delete_lost_item">
                <?= csrfField() ?>
                <input type="hidden" name="item_id" id="deleteItemId" value="">
                <div class="flex items-center justify-center gap-3">
                    <button type="button" onclick="closeDeleteItemModal()" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition shadow-sm">Delete Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="itemToast" class="fixed top-4 right-4 z-[100] hidden">
    <div class="flex items-center gap-3 bg-white text-gray-900 px-5 py-4 rounded-xl shadow-2xl border border-gray-200 min-w-[320px] max-w-sm" data-aos="fade-down">
        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold">Success!</p>
            <p class="text-xs text-gray-500" id="itemToastMsg">Item deleted successfully.</p>
        </div>
        <button onclick="hideItemToast()" class="text-gray-400 hover:text-gray-600 transition flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
</div>

<script>
<?php if ($msg = flashSuccess()): ?>
showItemToast('<?= e($msg) ?>');
<?php endif; ?>

function openDeleteModal(itemId, itemTitle) {
    document.getElementById('deleteItemId').value = itemId;
    document.getElementById('deleteItemTitle').textContent = itemTitle;
    document.getElementById('deleteItemModal').classList.remove('hidden');
}

function closeDeleteItemModal() {
    document.getElementById('deleteItemModal').classList.add('hidden');
}

function showItemToast(message) {
    var toast = document.getElementById('itemToast');
    var msgEl = document.getElementById('itemToastMsg');
    if (msgEl) msgEl.textContent = message || 'Item deleted successfully.';
    toast.classList.remove('hidden');
    setTimeout(function () { toast.classList.add('hidden'); }, 4000);
}

function hideItemToast() {
    document.getElementById('itemToast').classList.add('hidden');
}

document.getElementById('deleteItemModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteItemModal();
});

var currentSort = 'default';

document.querySelectorAll('#sortChips .sort-chip').forEach(function(btn) {
    btn.addEventListener('click', function() {
        currentSort = btn.dataset.sort;
        document.querySelectorAll('#sortChips .sort-chip').forEach(function(b) {
            b.classList.toggle('active', b === btn);
        });
        applyItemSort();
    });
});

function applyItemSort() {
    var mode = currentSort;
    var tbody = document.querySelector('#itemsTable tbody');
    if (!tbody) return;
    var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
    rows.sort(function(a, b) {
        if (mode === 'newest') return Number(b.dataset.date) - Number(a.dataset.date);
        if (mode === 'oldest') return Number(a.dataset.date) - Number(b.dataset.date);
        return Number(a.dataset.index) - Number(b.dataset.index);
    });
    rows.forEach(function(r) { tbody.appendChild(r); });
}
</script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="<?= SITE_URL ?>/assets/js/app.js?v=8"></script>
</body>
</html>
