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
    .pixel-corner-tl { top: 1.5rem; left: 1.5rem; }
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
    @keyframes lafPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(0.78); }
    }
</style>
<div class="laf-bg">
    <div class="pixel-corner pixel-corner-br" aria-hidden="true"><?= pixelCluster($brMap) ?></div>
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 relative z-10">
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
        <div id="fbFeed" class="max-w-5xl mx-auto mt-8 space-y-5">
            <?php foreach ($items as $item):
                $isLost = $item['status'] === 'lost';
                $isLong = mb_strlen($item['description']) > 220;
            ?>
                <article data-item-id="<?= $item['id'] ?>" class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden" data-aos="fade-up">
                    <div class="p-4 flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-800 rounded-full flex items-center justify-center text-white font-bold shrink-0">
                            <?= strtoupper(mb_substr($item['author_name'], 0, 1)) ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 text-sm truncate"><?= e($item['author_name']) ?></p>
                            <p class="text-xs text-gray-500"><?= timeAgo($item['created_at']) ?></p>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full <?= $isLost ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?>">
                            <?= ucfirst($item['status']) ?>
                        </span>
                    </div>

                    <div class="px-4 pb-3">
                        <h3 class="font-bold text-gray-900 mb-1"><?= e($item['title']) ?></h3>
                        <p id="desc-<?= $item['id'] ?>" class="text-gray-700 text-sm leading-relaxed <?= $isLong ? 'line-clamp-4' : '' ?>"><?= e($item['description']) ?></p>
                        <?php if ($isLong): ?>
                            <button type="button" onclick="toggleDesc(this, 'desc-<?= $item['id'] ?>')" class="mt-1 text-gray-500 hover:text-gray-700 text-sm font-medium">See more</button>
                        <?php endif; ?>
                    </div>

                    <?php if ($item['image']): ?>
                        <a href="<?= SITE_URL ?>/item_detail?id=<?= $item['id'] ?>">
                            <img src="<?= e(imageUrl($item['image'])) ?>" alt="<?= e($item['title']) ?>" class="w-full max-h-[440px] object-cover">
                        </a>
                    <?php endif; ?>

                    <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between gap-3">
                        <span class="text-sm text-gray-500 flex items-center gap-1 min-w-0">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="truncate"><?= e($item['location']) ?></span>
                        </span>
                        <a href="<?= SITE_URL ?>/item_detail?id=<?= $item['id'] ?>" class="shrink-0 px-4 py-2 text-sm font-semibold text-blue-800 bg-blue-50 rounded-lg hover:bg-blue-100 transition">View Details</a>
                    </div>
                </article>
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
function escHtml(s) { var d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }
function toggleDesc(btn, id) {
    var el = document.getElementById(id);
    if (!el) return;
    if (el.classList.contains('line-clamp-4')) {
        el.classList.remove('line-clamp-4');
        btn.textContent = 'See less';
    } else {
        el.classList.add('line-clamp-4');
        btn.textContent = 'See more';
    }
}
function buildFbPost(d) {
    var lost = d.status === 'lost';
    var initial = escHtml((d.author_name || '?').substring(0, 1).toUpperCase());
    var imgHtml = d.image
        ? '<a href="<?= SITE_URL ?>/item_detail?id=' + d.id + '"><img src="' + itemImgSrc(d.image) + '" alt="' + escHtml(d.title) + '" class="w-full max-h-[440px] object-cover"></a>'
        : '';
    return '<article data-item-id="' + d.id + '" class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden" style="opacity:0;transition:opacity 0.5s ease">'
        + '<div class="p-4 flex items-center gap-3">'
        + '<div class="w-10 h-10 bg-blue-800 rounded-full flex items-center justify-center text-white font-bold shrink-0">' + initial + '</div>'
        + '<div class="flex-1 min-w-0"><p class="font-semibold text-gray-900 text-sm truncate">' + escHtml(d.author_name) + '</p>'
        + '<p class="text-xs text-gray-500">' + escHtml(d.created_at || 'just now') + '</p></div>'
        + '<span class="text-xs font-semibold px-2.5 py-1 rounded-full ' + (lost ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700') + '">' + (lost ? 'Lost' : 'Found') + '</span>'
        + '</div>'
        + '<div class="px-4 pb-3"><h3 class="font-bold text-gray-900 mb-1">' + escHtml(d.title) + '</h3>'
        + '<p class="text-gray-700 text-sm leading-relaxed">' + escHtml(d.description) + '</p></div>'
        + imgHtml
        + '<div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between gap-3">'
        + '<span class="text-sm text-gray-500 flex items-center gap-1 min-w-0"><svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span class="truncate">' + escHtml(d.location) + '</span></span>'
        + '<a href="<?= SITE_URL ?>/item_detail?id=' + d.id + '" class="shrink-0 px-4 py-2 text-sm font-semibold text-blue-800 bg-blue-50 rounded-lg hover:bg-blue-100 transition">View Details</a>'
        + '</div></article>';
}

var lostChannel = pusher.subscribe('lost-items');
lostChannel.bind('new-item', function(data) {
    var feed = document.getElementById('fbFeed');
    if (!feed) return;
    var wrap = document.createElement('div');
    wrap.innerHTML = buildFbPost(data);
    var post = wrap.firstElementChild;
    feed.insertBefore(post, feed.firstChild);
    requestAnimationFrame(function() { post.style.opacity = '1'; });
});

lostChannel.bind('item-deleted', function(data) {
    var feed = document.getElementById('fbFeed');
    if (!feed) return;
    var post = feed.querySelector('article[data-item-id="' + data.id + '"]');
    if (!post) return;
    post.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    post.style.opacity = '0';
    post.style.transform = 'scale(0.96)';
    setTimeout(function() { post.remove(); }, 300);
});

lostChannel.bind('item-updated', function(data) {
    var feed = document.getElementById('fbFeed');
    if (!feed) return;
    var post = feed.querySelector('article[data-item-id="' + data.id + '"]');
    if (!post) return;
    var wrap = document.createElement('div');
    wrap.innerHTML = buildFbPost(data);
    var fresh = wrap.firstElementChild;
    fresh.style.opacity = '1';
    fresh.style.transition = 'background-color 0.5s ease';
    fresh.style.backgroundColor = '#f0f9ff';
    post.replaceWith(fresh);
    setTimeout(function() { fresh.style.backgroundColor = ''; }, 1500);
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
