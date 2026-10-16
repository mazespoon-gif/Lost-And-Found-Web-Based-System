<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 9;
$offset = ($page - 1) * $perPage;

$posts = [];
$totalPosts = 0;

if ($searchQuery) {
    $totalPosts = getTotalPosts(null, $searchQuery);
    $posts = getPosts($perPage, $offset, null, $searchQuery);
}

$pageTitle = 'Search';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid lg:grid-cols-4 gap-8">
        <div class="lg:col-span-3">
            <div data-aos="fade-up">
                <h1 class="text-3xl font-bold text-gray-900">Search</h1>
                <p class="text-gray-500 mt-1">Find posts, announcements, and news</p>
            </div>

            <div class="mt-6" data-aos="fade-up">
                <form action="" method="GET" class="flex gap-3">
                    <div class="relative flex-1">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="q" value="<?= e($searchQuery) ?>"
                               placeholder="Search titles, keywords..."
                               class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                               autofocus>
                    </div>
                    <button type="submit" class="px-8 py-3 bg-blue-900 text-white font-semibold rounded-xl hover:bg-blue-800 transition shadow-sm">
                        Search
                    </button>
                </form>
            </div>

            <?php if ($searchQuery): ?>
                <div class="mt-6" data-aos="fade-up">
                    <p class="text-gray-500">Showing results for "<strong class="text-gray-900"><?= e($searchQuery) ?></strong>" (<?= $totalPosts ?> results)</p>
                </div>

                <?php if (empty($posts)): ?>
                    <div class="text-center py-16" data-aos="fade-up">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <h3 class="text-lg font-semibold text-gray-600">No results found</h3>
                        <p class="text-gray-400 mt-1">Try different keywords.</p>
                    </div>
                <?php else: ?>
                    <div class="grid md:grid-cols-2 gap-6 mt-6">
                        <?php foreach ($posts as $post): ?>
                            <a href="<?= SITE_URL ?>/post_details?slug=<?= e($post['slug']) ?>"
                               class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden card-hover" data-aos="fade-up">
                                <?php $sc = getCategoryColor($post['category_name']); ?>
                                <div class="h-40 <?= $sc['bg'] ?> flex items-center justify-center">
                                    <svg class="w-10 h-10 <?= $sc['text'] ?> opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="<?= $sc['icon'] ?>"/></svg>
                                </div>
                                <div class="p-5">
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold <?= $sc['bg'] ?> <?= $sc['text'] ?> px-2.5 py-1 rounded-full">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $sc['icon'] ?>"/></svg>
                                        <?= e($post['category_name']) ?>
                                    </span>
                                    <h3 class="text-lg font-bold text-gray-900 mt-2"><?= e($post['title']) ?></h3>
                                    <p class="text-gray-500 text-sm mt-2 line-clamp-2"><?= e(excerpt(strip_tags($post['content']), 120)) ?></p>
                                    <div class="flex items-center gap-2 text-xs text-gray-400 mt-3">
                                        <span><?= e($post['author_name']) ?></span>
                                        <span>|</span>
                                        <span><?= timeAgo($post['published_at']) ?></span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <?= paginationLinks($totalPosts, $perPage, $page, SITE_URL . '/search?q=' . urlencode($searchQuery) . '&') ?>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-16" data-aos="fade-up">
                    <svg class="w-20 h-20 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <h3 class="text-xl font-semibold text-gray-600">Search TCC Blog</h3>
                    <p class="text-gray-400 mt-1">Enter a keyword above to search for posts, announcements, and news.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="lg:col-span-1">
            <?php require_once __DIR__ . '/includes/sidebar.php'; ?>
        </div>
    </div>
</div>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="<?= SITE_URL ?>/assets/js/app.js?v=9"></script>
</body>
</html>
