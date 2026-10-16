<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/functions.php';

requireAdmin();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();

    if (isset($_POST['approve_user'])) {
        $userId = (int)($_POST['user_id'] ?? 0);
        $stmt = db()->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
        $stmt->execute([$userId]);
        $message = 'User approved successfully.';
        pusherTrigger('admin-channel', 'users-changed', ['action' => 'approve', 'id' => $userId]);
    }

    if (isset($_POST['reject_user'])) {
        $userId = (int)($_POST['user_id'] ?? 0);
        $stmt = db()->prepare("UPDATE users SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$userId]);
        $message = 'User rejected.';
        pusherTrigger('admin-channel', 'users-changed', ['action' => 'reject', 'id' => $userId]);
    }

    if (isset($_POST['delete_user'])) {
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId == $_SESSION['user_id']) {
            $message = 'You cannot ban your own account.';
        } else {
            $stmt = db()->prepare("UPDATE users SET status = 'banned' WHERE id = ?");
            $stmt->execute([$userId]);
            $message = 'User has been banned successfully.';
            pusherTrigger('admin-channel', 'users-changed', ['action' => 'ban', 'id' => $userId]);
        }
    }

    if (isset($_POST['destroy_user'])) {
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId == $_SESSION['user_id']) {
            $message = 'You cannot delete your own account.';
        } else {
            $stmt = db()->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $message = 'User has been deleted successfully.';
            pusherTrigger('admin-channel', 'users-changed', ['action' => 'delete', 'id' => $userId]);
        }
    }

    if (isset($_POST['unban_user'])) {
        $userId = (int)($_POST['user_id'] ?? 0);
        $stmt = db()->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
        $stmt->execute([$userId]);
        $message = 'User has been unbanned successfully.';
        pusherTrigger('admin-channel', 'users-changed', ['action' => 'unban', 'id' => $userId]);
    }

    if (isset($_POST['change_role'])) {
        $userId = (int)($_POST['user_id'] ?? 0);
        $newRole = $_POST['new_role'] ?? 'user';
        if (in_array($newRole, ['user', 'admin'])) {
            $stmt = db()->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$newRole, $userId]);
            $message = 'User role updated successfully.';
            pusherTrigger('admin-channel', 'users-changed', ['action' => 'role', 'id' => $userId]);
        }
    }
}

$users = db()->query("SELECT id, school_id, name, email, role, status, created_at FROM users ORDER BY FIELD(status,'pending','approved','rejected'), created_at DESC")->fetchAll();
$postCountMap = [];
$itemCounts = db()->query("SELECT user_id, COUNT(*) as cnt FROM lost_items GROUP BY user_id")->fetchAll();
foreach ($itemCounts as $ic) {
    $postCountMap[$ic['user_id']] = (int)$ic['cnt'];
}
$postCounts = db()->query("SELECT author_id, COUNT(*) as cnt FROM posts GROUP BY author_id")->fetchAll();
foreach ($postCounts as $pc) {
    $postCountMap[$pc['author_id']] = ($postCountMap[$pc['author_id']] ?? 0) + (int)$pc['cnt'];
}

$pageTitle = 'Manage Users';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<?php if ($message): ?>
<div id="actionToast" class="fixed top-4 right-4 z-[100]">
    <div class="flex items-center gap-3 bg-white text-gray-900 px-5 py-4 rounded-xl shadow-2xl border min-w-[320px] max-w-sm <?= strpos($message, 'successfully') !== false ? 'border-green-200' : 'border-red-200' ?>">
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
            <p class="text-xs text-gray-500"><?= e($message) ?></p>
        </div>
        <button onclick="hideActionToast()" class="text-gray-400 hover:text-gray-600 transition flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
</div>
<script>
setTimeout(function() { hideActionToast(); }, 5000);
function hideActionToast() {
    var t = document.getElementById('actionToast');
    if (t) { t.style.transition = 'opacity 0.3s ease'; t.style.opacity = '0'; setTimeout(function() { t.remove(); }, 300); }
}
</script>
<?php endif; ?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div>
        <h1 class="text-3xl font-bold text-gray-900">Manage Users</h1>
    </div>

    <div class="mt-8 flex items-center justify-between flex-wrap gap-4">
        <div id="usersCount">
            <p class="text-lg font-bold text-blue-800"><?= count($users) ?> registered users</p>
        </div>
        <div class="flex flex-wrap items-center justify-center gap-1 bg-gray-100/80 rounded-xl p-1 border border-blue-300 max-w-full" id="sortChips">
            <button type="button" data-sort="default" class="sort-chip active" title="Default order (by status)">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 16h6"/></svg>
                Default
            </button>
            <button type="button" data-sort="az" class="sort-chip" title="Sort name A to Z">
                <span class="font-bold text-[11px] tracking-tight">A</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19l7-7 7 7"/></svg>
                <span class="font-bold text-[11px] tracking-tight">Z</span>
            </button>
            <button type="button" data-sort="za" class="sort-chip" title="Sort name Z to A">
                <span class="font-bold text-[11px] tracking-tight">Z</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 5l-7 7-7-7"/></svg>
                <span class="font-bold text-[11px] tracking-tight">A</span>
            </button>
            <button type="button" data-sort="newest" class="sort-chip" title="Newest accounts first">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Newest
            </button>
            <button type="button" data-sort="oldest" class="sort-chip" title="Oldest accounts first">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Oldest
            </button>
        </div>
    </div>

    <div id="usersTableWrap" class="mt-4 bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
        <?php if (empty($users)): ?>
            <div class="p-12 text-center text-gray-500">No users found.</div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table id="usersTable" class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-left text-sm font-medium text-gray-500">
                            <th class="px-6 py-4">School ID</th>
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Posts</th>
                            <th class="px-6 py-4">Joined</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($users as $i => $user): ?>
                            <tr class="hover:bg-gray-50" data-index="<?= $i ?>" data-name="<?= e(strtolower($user['name'])) ?>" data-joined="<?= strtotime($user['created_at']) ?>">
                                <td class="px-6 py-4 text-sm text-gray-500 font-mono" data-label="School ID"><?= e($user['school_id']) ?></td>
                                <td class="px-6 py-4" data-label="Name">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 bg-blue-800 rounded-full flex items-center justify-center text-white text-xs font-bold">
                                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                        </div>
                                        <span class="font-medium text-gray-900"><?= e($user['name']) ?></span>
                                        <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                            <span class="text-xs text-blue-600">(You)</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500" data-label="Email"><?= e($user['email']) ?></td>
                                <td class="px-6 py-4" data-label="Role">
                                    <span class="text-xs font-medium px-2.5 py-1 rounded-full <?= $user['role'] === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700' ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4" data-label="Status">
                                    <?php if ($user['status'] === 'pending'): ?>
                                        <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-yellow-100 text-yellow-700">Pending</span>
                                    <?php elseif ($user['status'] === 'approved'): ?>
                                        <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-green-100 text-green-700">Approved</span>
                                    <?php elseif ($user['status'] === 'banned'): ?>
                                        <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-red-100 text-red-700">Banned</span>
                                    <?php else: ?>
                                        <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-red-100 text-red-700">Rejected</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500" data-label="Posts"><?= $postCountMap[$user['id']] ?? 0 ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500" data-label="Joined"><?= formatDate($user['created_at']) ?></td>
                                <td class="px-6 py-4 text-right" data-label="Actions">
                                    <div class="flex items-center justify-end gap-2">
                                        <?php if ($user['status'] === 'pending'): ?>
                                            <form method="POST" action="" class="inline">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <button type="submit" name="approve_user" class="px-3 py-1.5 text-xs font-medium text-green-600 bg-green-50 rounded hover:bg-green-100 transition">Approve</button>
                                            </form>
                                            <form method="POST" action="" class="inline">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <button type="submit" name="reject_user" class="px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded hover:bg-red-100 transition">Reject</button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" action="">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <select name="new_role" onchange="this.form.submit()" class="text-xs border border-gray-200 rounded px-2 py-1.5 focus:ring-2 focus:ring-blue-500 outline-none">
                                                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            </select>
                                            <input type="hidden" name="change_role" value="1">
                                        </form>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <?php if ($user['status'] === 'banned'): ?>
                                                <form method="POST" action="" class="inline">
                                                    <?= csrfField() ?>
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <button type="submit" name="unban_user" class="px-3 py-1.5 text-xs font-medium text-green-600 bg-green-50 rounded hover:bg-green-100 transition">Unban</button>
                                                </form>
                                            <?php else: ?>
                                                <button type="button"
                                                        onclick="openDeleteModal(<?= $user['id'] ?>, '<?= e($user['name']) ?>')"
                                                        class="px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded hover:bg-red-100 transition">
                                                    Ban
                                                </button>
                                            <?php endif; ?>
                                            <button type="button"
                                                    onclick="openDestroyModal(<?= $user['id'] ?>, '<?= e($user['name']) ?>')"
                                                    class="px-3 py-1.5 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700 transition">
                                                Delete
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="deleteModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 p-6">
        <div class="text-center">
            <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Ban User</h3>
            <p class="text-gray-600 text-sm mb-1">Are you sure you want to ban <strong id="modalUserName" class="text-gray-900"></strong>?</p>
            <p class="text-gray-500 text-xs mb-6">They will no longer be able to access their account.</p>
            <form id="deleteForm" method="POST" action="">
                <?= csrfField() ?>
                <input type="hidden" name="user_id" id="modalUserId" value="">
                <div class="flex items-center justify-center gap-3">
                    <button type="button" onclick="closeDeleteModal()" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">Cancel</button>
                    <button type="submit" name="delete_user" class="px-6 py-2.5 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition shadow-sm">Ban User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="destroyModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 p-6">
        <div class="text-center">
            <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Delete User</h3>
            <p class="text-gray-600 text-sm mb-1">Are you sure you want to permanently delete <strong id="destroyUserName" class="text-gray-900"></strong>?</p>
            <p class="text-gray-500 text-xs mb-6">This action cannot be undone.</p>
            <form id="destroyForm" method="POST" action="">
                <?= csrfField() ?>
                <input type="hidden" name="user_id" id="destroyUserId" value="">
                <div class="flex items-center justify-center gap-3">
                    <button type="button" onclick="closeDestroyModal()" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">Cancel</button>
                    <button type="submit" name="destroy_user" class="px-6 py-2.5 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition shadow-sm">Delete User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
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
@media (max-width: 639px) {
    #usersTable thead { display: none; }
    #usersTable, #usersTable tbody, #usersTable tr, #usersTable td { display: block; width: 100%; }
    #usersTable { padding: 0.75rem; }
    #usersTable tr {
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        margin-bottom: 0.75rem;
        padding: 0.35rem 0.25rem;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    }
    #usersTable tr:last-child { margin-bottom: 0; }
    #usersTable td {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.5rem 0.75rem;
        border: none !important;
        text-align: left !important;
    }
    #usersTable td::before {
        content: attr(data-label);
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #1e3a8a;
        flex-shrink: 0;
    }
    #usersTable td a, #usersTable td span, #usersTable td form { text-align: right; }
    #usersTable td[data-label="Name"] > div { justify-content: flex-end; flex-wrap: wrap; }
}
</style>
<script>
function openDeleteModal(userId, userName) {
    document.getElementById('modalUserId').value = userId;
    document.getElementById('modalUserName').textContent = userName;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

function openDestroyModal(userId, userName) {
    document.getElementById('destroyUserId').value = userId;
    document.getElementById('destroyUserName').textContent = userName;
    document.getElementById('destroyModal').classList.remove('hidden');
}

function closeDestroyModal() {
    document.getElementById('destroyModal').classList.add('hidden');
}

document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});

document.getElementById('destroyModal').addEventListener('click', function(e) {
    if (e.target === this) closeDestroyModal();
});

var currentSort = 'default';

document.querySelectorAll('#sortChips .sort-chip').forEach(function(btn) {
    btn.addEventListener('click', function() {
        currentSort = btn.dataset.sort;
        document.querySelectorAll('#sortChips .sort-chip').forEach(function(b) {
            b.classList.toggle('active', b === btn);
        });
        applyUserSort();
    });
});

function applyUserSort() {
    var mode = currentSort;
    var tbody = document.querySelector('#usersTableWrap tbody');
    if (!tbody) return;
    var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
    rows.sort(function(a, b) {
        if (mode === 'az') return a.dataset.name.localeCompare(b.dataset.name);
        if (mode === 'za') return b.dataset.name.localeCompare(a.dataset.name);
        if (mode === 'newest') return Number(b.dataset.joined) - Number(a.dataset.joined);
        if (mode === 'oldest') return Number(a.dataset.joined) - Number(b.dataset.joined);
        return Number(a.dataset.index) - Number(b.dataset.index);
    });
    rows.forEach(function(r) { tbody.appendChild(r); });
}

var usersChannel = pusher.subscribe('admin-channel');
usersChannel.bind('users-changed', function() {
    refreshUsersTable();
});

function refreshUsersTable() {
    fetch(window.location.href).then(function(r) { return r.text(); }).then(function(html) {
        var doc = new DOMParser().parseFromString(html, 'text/html');
        var freshWrap = doc.getElementById('usersTableWrap');
        var curWrap = document.getElementById('usersTableWrap');
        var freshCount = doc.getElementById('usersCount');
        var curCount = document.getElementById('usersCount');
        if (freshWrap && curWrap) {
            curWrap.innerHTML = freshWrap.innerHTML;
            applyUserSort();
        }
        if (freshCount && curCount) curCount.innerHTML = freshCount.innerHTML;
    });
}
</script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="<?= SITE_URL ?>/assets/js/app.js?v=8"></script>
</body>
</html>
