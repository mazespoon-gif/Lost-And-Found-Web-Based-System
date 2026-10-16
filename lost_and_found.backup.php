<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/functions.php';

$status = isset($_GET['status']) && in_array($_GET['status'], ['lost', 'found']) ? $_GET['status'] : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

$totalItems = getTotalLostItems($status, $search);
$items = getLostItems($status, $perPage, $offset, $search);

$pageTitle = 'Lost & Found';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4" data-aos="fade-up">
        <div class="min-w-0">
            <h1 class="text-3xl font-bold text-gray-900 whitespace-nowrap">Lost & Found</h1>
            <p class="text-gray-500 mt-1 whitespace-nowrap">Report lost items or help others find theirs</p>
        </div>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <form method="GET" action="<?= SITE_URL ?>/lost_and_found" class="relative w-full sm:w-auto">
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search item name..."
                       class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-sm">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </form>
            <?php if (isLoggedIn()): ?>
                <a href="<?= SITE_URL ?>/report_item" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-900 text-white font-semibold rounded-lg hover:bg-blue-800 transition shadow-sm whitespace-nowrap w-full sm:w-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Report Item
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex flex-wrap gap-2 mt-6" data-aos="fade-up">
        <a href="<?= SITE_URL ?>/lost_and_found<?= $search ? '?search=' . urlencode($search) : '' ?>"
           class="px-4 py-2 text-sm font-medium rounded-lg transition <?= !$status ? 'bg-blue-900 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
            All
        </a>
        <a href="<?= SITE_URL ?>/lost_and_found?status=lost<?= $search ? '&search=' . urlencode($search) : '' ?>"
           class="px-4 py-2 text-sm font-medium rounded-lg transition <?= $status === 'lost' ? 'bg-red-600 text-white shadow-sm' : 'bg-red-50 text-red-700 hover:bg-red-100' ?>">
            Lost
        </a>
        <a href="<?= SITE_URL ?>/lost_and_found?status=found<?= $search ? '&search=' . urlencode($search) : '' ?>"
           class="px-4 py-2 text-sm font-medium rounded-lg transition <?= $status === 'found' ? 'bg-green-600 text-white shadow-sm' : 'bg-green-50 text-green-700 hover:bg-green-100' ?>">
            Found
        </a>
    </div>

    <?php if (empty($items)): ?>
        <div class="text-center py-20" data-aos="fade-up">
            <svg class="w-20 h-20 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <h3 class="text-lg font-semibold text-gray-600">No items reported</h3>
            <p class="text-gray-400 mt-1"><?= isLoggedIn() ? 'Be the first to report a lost or found item.' : 'Sign in to report a lost or found item.' ?></p>
        </div>
    <?php else: ?>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
            <?php foreach ($items as $item): ?>
                <a href="<?= SITE_URL ?>/item_detail?id=<?= $item['id'] ?>"
                   class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden card-hover" data-aos="fade-up">
                    <div class="h-44 <?= $item['status'] === 'lost' ? 'bg-red-50' : 'bg-green-50' ?> flex items-center justify-center">
                        <?php if ($item['image']): ?>
                            <img src="<?= e(imageUrl($item['image'])) ?>" alt="<?= e($item['title']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <svg class="w-14 h-14 <?= $item['status'] === 'lost' ? 'text-red-300' : 'text-green-300' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <?php endif; ?>
                    </div>
                    <div class="p-5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full <?= $item['status'] === 'lost' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?>">
                                <?= ucfirst($item['status']) ?>
                            </span>
                        </div>
                        <h3 class="text-base font-bold text-gray-900"><?= e($item['title']) ?></h3>
                        <p class="text-gray-500 text-sm mt-1.5 line-clamp-2"><?= e(excerpt($item['description'], 100)) ?></p>
                        <div class="flex items-center gap-4 text-xs text-gray-400 mt-3">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <?= e($item['location']) ?>
                            </span>
                            <span><?= timeAgo($item['created_at']) ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php 
        $paginationUrl = SITE_URL . '/lost_and_found?';
        if ($status) $paginationUrl .= 'status=' . $status . '&';
        if ($search) $paginationUrl .= 'search=' . urlencode($search) . '&';
        echo paginationLinks($totalItems, $perPage, $page, $paginationUrl);
        ?>
    <?php endif; ?>
</div>

<?php $toastMsg = flashSuccess(); ?>
<?php if ($toastMsg): ?>
<div id="actionToast" class="fixed top-4 right-4 z-[100]">
    <div class="flex items-center gap-3 bg-white text-gray-900 px-5 py-4 rounded-xl shadow-2xl border min-w-[320px] max-w-sm <?= strpos($toastMsg, 'successfully') !== false ? 'border-green-200' : 'border-red-200' ?>" data-aos="fade-down">
        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 <?= strpos($toastMsg, 'successfully') !== false ? 'bg-green-100' : 'bg-red-100' ?>">
            <svg class="w-5 h-5 <?= strpos($toastMsg, 'successfully') !== false ? 'text-green-600' : 'text-red-600' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <?php if (strpos($toastMsg, 'successfully') !== false): ?>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                <?php else: ?>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                <?php endif; ?>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold <?= strpos($toastMsg, 'successfully') !== false ? 'text-green-800' : 'text-red-800' ?>">
                <?= strpos($toastMsg, 'successfully') !== false ? 'Success!' : 'Error!' ?>
            </p>
            <p class="text-xs text-gray-500"><?= e($toastMsg) ?></p>
        </div>
        <button onclick="hideActionToast()" class="text-gray-400 hover:text-gray-600 transition flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
</div>
<?php endif; ?>
<script>
function itemImgSrc(p) { return (p.indexOf('http://') === 0 || p.indexOf('https://') === 0) ? p : '<?= SITE_URL ?>/' + p; }
var lostChannel = pusher.subscribe('lost-items');
lostChannel.bind('new-item', function(data) {
    var grid = document.querySelector('.grid.md\\:grid-cols-2');
    if (!grid) return;

    var statusClass = data.status === 'lost' ? 'bg-red-50' : 'bg-green-50';
    var svgClass = data.status === 'lost' ? 'text-red-300' : 'text-green-300';
    var badgeClass = data.status === 'lost' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700';
    var imageHtml = data.image
        ? '<img src="' + itemImgSrc(data.image) + '" alt="' + data.title + '" class="w-full h-full object-cover">'
        : '<svg class="w-14 h-14 ' + svgClass + '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>';

    var html = '<a href="<?= SITE_URL ?>/item_detail?id=' + data.id + '" class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden card-hover" data-aos="fade-up" style="opacity:0;transition:opacity 0.5s ease">'
        + '<div class="h-44 ' + statusClass + ' flex items-center justify-center">' + imageHtml + '</div>'
        + '<div class="p-5">'
        + '<div class="flex items-center justify-between mb-2"><span class="text-xs font-semibold px-2.5 py-1 rounded-full ' + badgeClass + '">' + data.status.charAt(0).toUpperCase() + data.status.slice(1) + '</span></div>'
        + '<h3 class="text-base font-bold text-gray-900">' + data.title + '</h3>'
        + '<p class="text-gray-500 text-sm mt-1.5 line-clamp-2">' + data.description + '</p>'
        + '<div class="flex items-center gap-4 text-xs text-gray-400 mt-3">'
        + '<span class="flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>' + data.location + '</span>'
        + '<span>' + data.created_at + '</span>'
        + '</div></div></a>';

    var tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;
    var newCard = tempDiv.firstElementChild;
    grid.insertBefore(newCard, grid.firstChild);

    requestAnimationFrame(function() {
        newCard.style.opacity = '1';
    });
});

lostChannel.bind('item-deleted', function(data) {
    var grid = document.querySelector('.grid.md\\:grid-cols-2');
    if (!grid) return;
    var cards = grid.querySelectorAll('a[href*="item_detail?id=' + data.id + '"]');
    cards.forEach(function(card) {
        card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        card.style.opacity = '0';
        card.style.transform = 'scale(0.95)';
        setTimeout(function() { card.remove(); }, 300);
    });
});

lostChannel.bind('item-updated', function(data) {
    var grid = document.querySelector('.grid.md\\:grid-cols-2');
    if (!grid) return;
    var cards = grid.querySelectorAll('a[href*="item_detail?id=' + data.id + '"]');
    cards.forEach(function(card) {
        var statusClass = data.status === 'lost' ? 'bg-red-50' : 'bg-green-50';
        var svgClass = data.status === 'lost' ? 'text-red-300' : 'text-green-300';
        var badgeClass = data.status === 'lost' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700';
        var imageHtml = data.image
            ? '<img src="' + itemImgSrc(data.image) + '" alt="' + data.title + '" class="w-full h-full object-cover">'
            : '<svg class="w-14 h-14 ' + svgClass + '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>';

        card.querySelector('.h-44').className = 'h-44 ' + statusClass + ' flex items-center justify-center';
        card.querySelector('.h-44').innerHTML = imageHtml;
        card.querySelector('.rounded-full').className = 'text-xs font-semibold px-2.5 py-1 rounded-full ' + badgeClass;
        card.querySelector('.rounded-full').textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
        card.querySelector('.font-bold.text-gray-900.text-base').textContent = data.title;
        card.querySelector('.text-gray-500').textContent = data.description;

        card.style.transition = 'background-color 0.5s ease';
        card.style.backgroundColor = '#f0f9ff';
        setTimeout(function() { card.style.backgroundColor = ''; }, 1500);
    });
});
</script>
<script>
<?php if ($toastMsg): ?>
setTimeout(function() { hideActionToast(); }, 5000);
<?php endif; ?>
function hideActionToast() {
    var t = document.getElementById('actionToast');
    if (t) { t.style.transition = 'opacity 0.3s ease'; t.style.opacity = '0'; setTimeout(function() { t.remove(); }, 300); }
}
</script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="<?= SITE_URL ?>/assets/js/app.js?v=8"></script>
</body>
</html>
