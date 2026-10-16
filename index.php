<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/functions.php';

$recentItems = getLostItems(null, 6, 0);

$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<?php if ($msg = flashError()): ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg text-sm border border-red-100"><?= e($msg) ?></div>
    </div>
<?php endif; ?>

<section class="relative text-white bg-gray-900" style="background: url('image/monument.jpg') center/cover no-repeat;">
    <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/60 to-transparent"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div data-aos="fade-right">
                <div class="inline-block bg-blue-600/30 backdrop-blur-sm px-4 py-1.5 rounded-full text-sm font-medium text-blue-200 mb-4 border border-blue-400/30">
                    Welcome to LookForit
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight drop-shadow-lg mb-4">
                    Lost Something?<br><span class="text-blue-300">We're Here to Help.</span>
                </h1>
                <p class="text-lg md:text-xl text-gray-200 mb-8 max-w-lg drop-shadow">
                    Report lost items, browse found items, and reunite with your belongings.
                </p>
                <a href="#browse" class="inline-flex items-center gap-2 px-8 py-4 bg-white text-blue-900 font-bold rounded-xl hover:bg-blue-50 transition shadow-xl text-lg">
                    Find Items
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                </a>
            </div>
            <div class="hidden md:flex justify-center" data-aos="fade-left">
                <div class="relative">
                    <div class="w-72 h-72 md:w-96 md:h-96 bg-white/5 rounded-full backdrop-blur-sm border border-white/10"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                       <img src="image/high.webp" alt="LookForit Logo" class="w-48 h-48 rounded-full md:w-64 md:h-64 object-contain drop-shadow-2xl">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16" data-aos="fade-up">
    <div class="grid md:grid-cols-3 gap-8">
        <div class="relative overflow-hidden rounded-2xl p-8 text-center group cursor-pointer border border-white/20 shadow-lg hover:shadow-2xl hover:-translate-y-1 transition-all duration-300" style="background: linear-gradient(135deg, #ef4444 0%, #f97316 50%, #fbbf24 100%);">
            <div class="absolute inset-0 opacity-20">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white rounded-full blur-2xl"></div>
                <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-white rounded-full blur-2xl"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-24 h-24 border border-white/30 rounded-full"></div>
            </div>
            <div class="relative z-10">
                <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mx-auto mb-5 border border-white/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 10V9"/><path d="M9 10V9"/><path d="M9 16a5 5 0 016 0"/><circle cx="12" cy="12" r="10"/></svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Lost Something?</h3>
                <p class="text-white/80 text-sm leading-relaxed mb-5">Don't panic. Report the item you lost with details and a photo so the community can help you find it.</p>
                <a href="<?= SITE_URL ?>/report_item" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-white/20 backdrop-blur-sm px-5 py-2.5 rounded-full hover:bg-white/30 transition-all duration-300 border border-white/30">
                    Report Lost Item
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
        <div class="relative overflow-hidden rounded-2xl p-8 text-center group cursor-pointer border border-white/20 shadow-lg hover:shadow-2xl hover:-translate-y-1 transition-all duration-300" style="background: linear-gradient(135deg, #059669 0%, #10b981 50%, #34d399 100%);">
            <div class="absolute inset-0 opacity-20">
                <div class="absolute -top-8 -left-8 w-36 h-36 bg-white rounded-full blur-2xl"></div>
                <div class="absolute -bottom-12 -right-12 w-44 h-44 bg-white rounded-full blur-2xl"></div>
                <div class="absolute top-6 right-6 w-20 h-20 border border-white/30 rounded-full"></div>
            </div>
            <div class="relative z-10">
                <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mx-auto mb-5 border border-white/30">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Found Something?</h3>
                <p class="text-white/80 text-sm leading-relaxed mb-5">Found an item someone lost? Report it here and help reunite it with its owner.</p>
                <a href="<?= SITE_URL ?>/report_item" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-white/20 backdrop-blur-sm px-5 py-2.5 rounded-full hover:bg-white/30 transition-all duration-300 border border-white/30">
                    Report Found Item
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
        <div class="relative overflow-hidden rounded-2xl p-8 text-center group cursor-pointer border border-white/20 shadow-lg hover:shadow-2xl hover:-translate-y-1 transition-all duration-300" style="background: linear-gradient(135deg, #2563eb 0%, #7c3aed 50%, #a855f7 100%);">
            <div class="absolute inset-0 opacity-20">
                <div class="absolute -top-6 -right-14 w-48 h-48 bg-white rounded-full blur-2xl"></div>
                <div class="absolute -bottom-8 -left-8 w-36 h-36 bg-white rounded-full blur-2xl"></div>
                <div class="absolute bottom-8 right-8 w-16 h-16 border border-white/30 rounded-full"></div>
                <div class="absolute top-8 left-8 w-12 h-12 border border-white/30 rounded-full"></div>
            </div>
            <div class="relative z-10">
                <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mx-auto mb-5 border border-white/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15.914 4a1.5 1.5 0 00-2.474-1.561l-9 9A1.5 1.5 0 005.5 14h4.002a.5.5 0 01.471.666L8.086 20a1.5 1.5 0 002.475 1.56l9-9A1.5 1.5 0 0018.5 10h-3.997a.5.5 0 01-.472-.667z"/></svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">How It Works</h3>
                <p class="text-white/80 text-sm leading-relaxed mb-5">1. Report an item &middot; 2. Browse listings &middot; 3. Connect and reunite. Simple and fast.</p>
                <a href="<?= SITE_URL ?>/lost_and_found" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-white/20 backdrop-blur-sm px-5 py-2.5 rounded-full hover:bg-white/30 transition-all duration-300 border border-white/30">
                    Browse All Items
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>

<section id="browse" class="bg-gray-50 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" data-aos="fade-up">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-8">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Recently Reported</h2>
                <p class="text-gray-500 mt-1">Latest lost and found items from the community</p>
            </div>
            <a href="<?= SITE_URL ?>/lost_and_found" class="text-blue-700 font-medium text-sm hover:underline whitespace-nowrap">View All</a>
        </div>
        <?php if ($recentItems): ?>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($recentItems as $item): ?>
                <a href="<?= SITE_URL ?>/item_detail?id=<?= $item['id'] ?>"
                   class="group block bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl border border-gray-100 transition-all duration-300 hover:-translate-y-1" data-aos="fade-up">
                    <?php if ($item['image']): ?>
                        <div class="relative h-44 overflow-hidden">
                            <img src="<?= e(imageUrl($item['image'])) ?>" alt="<?= e($item['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
                            <span class="absolute top-3 left-3 text-xs font-bold px-3 py-1 rounded-full backdrop-blur-sm <?= $item['status'] === 'lost' ? 'bg-red-500/90 text-white' : 'bg-green-500/90 text-white' ?>">
                                <?= ucfirst($item['status']) ?>
                            </span>
                            <div class="absolute bottom-3 left-3 flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold overflow-hidden <?= $item['status'] === 'lost' ? 'bg-red-500' : 'bg-green-500' ?>">
                                    <?php if (!empty($item['author_avatar'])): ?>
                                        <img src="<?= e(imageUrl($item['author_avatar'])) ?>" alt="" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <?= strtoupper(substr($item['author_name'], 0, 1)) ?>
                                    <?php endif; ?>
                                </div>
                                <span class="text-white text-xs font-medium drop-shadow"><?= e($item['author_name']) ?></span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="relative h-44 <?= $item['status'] === 'lost' ? 'bg-gradient-to-br from-red-50 to-red-100' : 'bg-gradient-to-br from-green-50 to-green-100' ?> flex items-center justify-center">
                            <svg class="w-16 h-16 <?= $item['status'] === 'lost' ? 'text-red-200' : 'text-green-200' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <span class="absolute top-3 left-3 text-xs font-bold px-3 py-1 rounded-full <?= $item['status'] === 'lost' ? 'bg-red-500 text-white' : 'bg-green-500 text-white' ?>">
                                <?= ucfirst($item['status']) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <div class="p-5">
                        <h4 class="font-bold text-gray-900 text-base mb-2 group-hover:text-blue-700 transition-colors"><?= e($item['title']) ?></h4>
                        <p class="text-gray-500 text-sm line-clamp-2 mb-3 leading-relaxed"><?= e(excerpt($item['description'], 80)) ?></p>
                        <div class="flex items-center justify-between text-xs text-gray-400">
                            <div class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <?= e($item['location']) ?>
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <?= timeAgo($item['created_at']) ?>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-16 bg-white rounded-2xl border border-gray-200">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <h3 class="text-lg font-semibold text-gray-600">No items reported yet</h3>
            <p class="text-gray-400 text-sm mt-1">Be the first to report a lost or found item.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 relative" data-aos="fade-up">
    <div class="absolute inset-0 opacity-[0.15] pointer-events-none rounded-2xl" style="background-image: radial-gradient(circle, #1e3a5f 1px, transparent 1px); background-size: 20px 20px; filter: blur(0.5px);"></div>
    <div class="text-center mb-12">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Frequently Asked Questions</h2>
        <p class="text-gray-500 mt-1">Everything you need to know about using LookForit</p>
    </div>
    <div class="space-y-3">
        <div class="border border-gray-200 rounded-xl overflow-hidden">
            <button onclick="toggleFAQ(this)" class="w-full flex items-center justify-between px-6 py-4 text-left font-semibold text-gray-900 hover:bg-gray-50 transition">
                How do I report a lost item?
                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="faq-answer hidden px-6 pb-4 text-sm text-gray-600 leading-relaxed">
                Click the "Report Item" button, select "Lost" as the status, fill in the details about the item including location and a photo, then submit. Your report will be visible to everyone in the community.
            </div>
        </div>
        <div class="border border-gray-200 rounded-xl overflow-hidden">
            <button onclick="toggleFAQ(this)" class="w-full flex items-center justify-between px-6 py-4 text-left font-semibold text-gray-900 hover:bg-gray-50 transition">
                How do I report a found item?
                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="faq-answer hidden px-6 pb-4 text-sm text-gray-600 leading-relaxed">
                Click the "Report Item" button, select "Found" as the status, describe the item and where you found it, add a photo, and submit. This helps the owner find their belonging.
            </div>
        </div>
        <div class="border border-gray-200 rounded-xl overflow-hidden">
            <button onclick="toggleFAQ(this)" class="w-full flex items-center justify-between px-6 py-4 text-left font-semibold text-gray-900 hover:bg-gray-50 transition">
                How do I claim a found item?
                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="faq-answer hidden px-6 pb-4 text-sm text-gray-600 leading-relaxed">
                Browse the "Found" items, open the item detail page, and use the contact information provided by the finder to reach out and arrange a reunion.
            </div>
        </div>
        <div class="border border-gray-200 rounded-xl overflow-hidden">
            <button onclick="toggleFAQ(this)" class="w-full flex items-center justify-between px-6 py-4 text-left font-semibold text-gray-900 hover:bg-gray-50 transition">
                Can I edit or delete my report?
                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="faq-answer hidden px-6 pb-4 text-sm text-gray-600 leading-relaxed">
                Yes. Sign in to your account, go to your item's detail page, and click the three-dot menu to edit or delete your report. You can only manage items you reported.
            </div>
        </div>
        <div class="border border-gray-200 rounded-xl overflow-hidden">
            <button onclick="toggleFAQ(this)" class="w-full flex items-center justify-between px-6 py-4 text-left font-semibold text-gray-900 hover:bg-gray-50 transition">
                Do I need an account to report or browse?
                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="faq-answer hidden px-6 pb-4 text-sm text-gray-600 leading-relaxed">
                Anyone can browse and view items. You need to sign up and be approved by an admin to report items, ensuring a trusted community.
            </div>
        </div>
    </div>
</section>

<script>
function itemImgSrc(p) { return (p.indexOf('http://') === 0 || p.indexOf('https://') === 0) ? p : '<?= SITE_URL ?>/' + p; }
function toggleFAQ(btn) {
    const answer = btn.nextElementSibling;
    const icon = btn.querySelector('svg');
    const isOpen = !answer.classList.contains('hidden');
    answer.classList.toggle('hidden');
    icon.classList.toggle('rotate-180');
}

var channel = pusher.subscribe('lost-items');
channel.bind('new-item', function(data) {
    var grid = document.querySelector('#browse .grid');
    if (!grid) return;

    var statusClass = data.status === 'lost' ? 'bg-red-500' : 'bg-green-500';
    var gradientBg = data.status === 'lost' ? 'bg-gradient-to-br from-red-50 to-red-100' : 'bg-gradient-to-br from-green-50 to-green-100';
    var iconColor = data.status === 'lost' ? 'text-red-200' : 'text-green-200';
    var imageHtml = data.image
        ? '<div class="relative h-44 overflow-hidden">'
            + '<img src="' + itemImgSrc(data.image) + '" alt="' + data.title + '" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />'
            + '<div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>'
            + '<span class="absolute top-3 left-3 text-xs font-bold px-3 py-1 rounded-full backdrop-blur-sm ' + statusClass + ' text-white">' + data.status.charAt(0).toUpperCase() + data.status.slice(1) + '</span>'
            + '<div class="absolute bottom-3 left-3 flex items-center gap-2">'
            + '<div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold overflow-hidden ' + statusClass + '">' + (data.author_avatar ? '<img src="' + itemImgSrc(data.author_avatar) + '" alt="" class="w-full h-full object-cover">' : data.author_name.substring(0, 1).toUpperCase()) + '</div>'
            + '<span class="text-white text-xs font-medium drop-shadow">' + data.author_name + '</span></div></div>'
        : '<div class="relative h-44 ' + gradientBg + ' flex items-center justify-center">'
            + '<svg class="w-16 h-16 ' + iconColor + '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>'
            + '<span class="absolute top-3 left-3 text-xs font-bold px-3 py-1 rounded-full ' + statusClass + ' text-white">' + data.status.charAt(0).toUpperCase() + data.status.slice(1) + '</span></div>';

    var html = '<a href="<?= SITE_URL ?>/item_detail?id=' + data.id + '" class="group block bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl border border-gray-100 transition-all duration-300 hover:-translate-y-1" data-aos="fade-up" style="opacity:0;transform:translateY(20px);transition:all 0.5s ease">'
        + imageHtml
        + '<div class="p-5">'
        + '<h4 class="font-bold text-gray-900 text-base mb-2 group-hover:text-blue-700 transition-colors">' + data.title + '</h4>'
        + '<p class="text-gray-500 text-sm line-clamp-2 mb-3 leading-relaxed">' + data.description + '</p>'
        + '<div class="flex items-center justify-between text-xs text-gray-400">'
        + '<div class="flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>' + data.location + '</div>'
        + '<div class="flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' + data.created_at + '</div>'
        + '</div></div></a>';

    var tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;
    var newCard = tempDiv.firstElementChild;
    grid.insertBefore(newCard, grid.firstChild);

    requestAnimationFrame(function() {
        newCard.style.opacity = '1';
        newCard.style.transform = 'translateY(0)';
    });

    if (grid.children.length > 6) {
        grid.removeChild(grid.lastChild);
    }
});

channel.bind('item-deleted', function(data) {
    var grid = document.querySelector('#browse .grid');
    if (!grid) return;
    var cards = grid.querySelectorAll('a[href*="item_detail?id=' + data.id + '"]');
    cards.forEach(function(card) {
        card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        card.style.opacity = '0';
        card.style.transform = 'scale(0.95)';
        setTimeout(function() { card.remove(); }, 300);
    });
});

channel.bind('item-updated', function(data) {
    var grid = document.querySelector('#browse .grid');
    if (!grid) return;
    var cards = grid.querySelectorAll('a[href*="item_detail?id=' + data.id + '"]');
    cards.forEach(function(card) {
        var statusClass = data.status === 'lost' ? 'bg-red-500' : 'bg-green-500';
        var gradientBg = data.status === 'lost' ? 'bg-gradient-to-br from-red-50 to-red-100' : 'bg-gradient-to-br from-green-50 to-green-100';
        var iconColor = data.status === 'lost' ? 'text-red-200' : 'text-green-200';
        var imageHtml = data.image
            ? '<div class="relative h-44 overflow-hidden"><img src="' + itemImgSrc(data.image) + '" alt="' + data.title + '" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" /><div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div><span class="absolute top-3 left-3 text-xs font-bold px-3 py-1 rounded-full backdrop-blur-sm ' + statusClass + ' text-white">' + data.status.charAt(0).toUpperCase() + data.status.slice(1) + '</span><div class="absolute bottom-3 left-3 flex items-center gap-2"><div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold overflow-hidden ' + statusClass + '">' + (data.author_avatar ? '<img src="' + itemImgSrc(data.author_avatar) + '" alt="" class="w-full h-full object-cover">' : data.author_name.substring(0, 1).toUpperCase()) + '</div><span class="text-white text-xs font-medium drop-shadow">' + data.author_name + '</span></div></div>'
            : '<div class="relative h-44 ' + gradientBg + ' flex items-center justify-center"><svg class="w-16 h-16 ' + iconColor + '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg><span class="absolute top-3 left-3 text-xs font-bold px-3 py-1 rounded-full ' + statusClass + ' text-white">' + data.status.charAt(0).toUpperCase() + data.status.slice(1) + '</span></div>';
        var html = imageHtml
            + '<div class="p-5"><h4 class="font-bold text-gray-900 text-base mb-2 group-hover:text-blue-700 transition-colors">' + data.title + '</h4>'
            + '<p class="text-gray-500 text-sm line-clamp-2 mb-3 leading-relaxed">' + data.description + '</p>'
            + '<div class="flex items-center justify-between text-xs text-gray-400">'
            + '<div class="flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>' + data.location + '</div>'
            + '<div class="flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Just updated</div></div></div>';
        card.innerHTML = html;
        card.style.transition = 'background-color 0.5s ease';
        card.style.backgroundColor = '#f0f9ff';
        setTimeout(function() { card.style.backgroundColor = ''; }, 1500);
    });
});
</script>
<script type="text/javascript" async src="https://tenor.com/embed.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
