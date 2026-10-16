<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/functions.php';

$slug = $_GET['slug'] ?? '';
$post = getPostBySlug($slug);

if (!$post) {
    redirect('/home');
}

$comments = getCommentsByPostId($post['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    requireLogin();
    requireCsrf();
    $comment = trim($_POST['comment'] ?? '');
    if (!empty($comment)) {
        $stmt = db()->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->execute([$post['id'], $_SESSION['user_id'], $comment]);

        $newComment = [
            'post_id' => $post['id'],
            'user_name' => $_SESSION['user_name'],
            'comment' => $comment,
            'created_at' => 'Just now',
        ];
        pusherTrigger('post-comments-' . $post['id'], 'new-comment', $newComment);

        $_SESSION['success'] = 'Comment added successfully!';
        redirect('/post_details?slug=' . $slug);
    }
}

$relatedStmt = db()->prepare("SELECT p.*, c.category_name, u.name as author_name
                              FROM posts p
                              JOIN categories c ON p.category_id = c.id
                              JOIN users u ON p.author_id = u.id
                              WHERE p.category_id = ? AND p.id != ?
                              ORDER BY p.published_at DESC LIMIT 3");
$relatedStmt->execute([$post['category_id'], $post['id']]);
$relatedPosts = $relatedStmt->fetchAll();

$pageTitle = $post['title'];
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <?php if ($msg = flashSuccess()): ?>
        <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg text-sm mb-6 border border-green-100"><?= e($msg) ?></div>
    <?php endif; ?>

    <div class="grid lg:grid-cols-4 gap-8">
        <article class="lg:col-span-3" data-aos="fade-up">
            <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
                <?php if ($post['image']): ?>
                    <img src="<?= e(imageUrl($post['image'])) ?>" alt="<?= e($post['title']) ?>" class="w-full object-contain bg-gray-100">
                <?php else: ?>
                    <div class="w-full h-72 md:h-96 bg-gradient-to-br from-blue-800 to-blue-900 flex items-center justify-center">
                        <div class="text-center">
                            <span class="text-white/20 text-8xl font-bold"><?= strtoupper(substr($post['title'], 0, 1)) ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="p-6 md:p-8">
                    <?php $catColor = getCategoryColor($post['category_name']); ?>
                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500 mb-4">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold <?= $catColor['bg'] ?> <?= $catColor['text'] ?> px-3 py-1.5 rounded-full">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $catColor['icon'] ?>"/></svg>
                            <?= e($post['category_name']) ?>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <?= e($post['author_name']) ?>
                        </span>
                        <span class="text-gray-300">|</span>
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <?= formatDate($post['published_at']) ?>
                        </span>
                        <?php if ($post['is_pinned']): ?>
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-yellow-600 bg-yellow-100 px-3 py-1.5 rounded-full">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                Pinned
                            </span>
                        <?php endif; ?>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 leading-tight"><?= e($post['title']) ?></h1>
                    <div class="mt-6 prose prose-gray max-w-none text-gray-700 leading-relaxed">
                        <?= $post['content'] ?>
                    </div>
                </div>
            </div>

            <?php if (isLoggedIn()): ?>
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 md:p-8 mt-6" data-aos="fade-up">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Leave a Comment</h3>
                    <form method="POST" action="">
                        <?= csrfField() ?>
                        <textarea name="comment" rows="4" required
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition resize-none"
                                  placeholder="Share your thoughts..."></textarea>
                        <button type="submit" name="add_comment" class="mt-3 px-6 py-2.5 bg-blue-900 text-white font-medium rounded-lg hover:bg-blue-800 transition shadow-sm">
                            Post Comment
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="bg-gray-50 rounded-2xl border border-gray-200 p-6 mt-6 text-center" data-aos="fade-up">
                    <p class="text-gray-600"><a href="<?= SITE_URL ?>/login" class="text-blue-700 font-medium hover:underline">Sign in</a> to leave a comment.</p>
                </div>
            <?php endif; ?>

            <?php if ($comments): ?>
                <div class="mt-6 space-y-4" data-aos="fade-up">
                    <h3 class="text-xl font-bold text-gray-900">Comments (<?= count($comments) ?>)</h3>
                    <?php foreach ($comments as $comment): ?>
                        <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-9 h-9 bg-blue-800 rounded-full flex items-center justify-center text-white text-xs font-bold">
                                    <?= strtoupper(substr($comment['user_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900"><?= e($comment['user_name']) ?></p>
                                    <p class="text-xs text-gray-400"><?= timeAgo($comment['created_at']) ?></p>
                                </div>
                            </div>
                            <p class="text-gray-700 text-sm"><?= e($comment['comment']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>

        <div class="lg:col-span-1">
            <?php require_once __DIR__ . '/includes/sidebar.php'; ?>
        </div>
    </div>

    <?php if ($relatedPosts): ?>
        <section class="mt-12" data-aos="fade-up">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Posts</h2>
            <div class="grid md:grid-cols-3 gap-6">
                <?php foreach ($relatedPosts as $rp):
                    $rpColor = getCategoryColor($rp['category_name']);
                ?>
                    <a href="<?= SITE_URL ?>/post_details?slug=<?= e($rp['slug']) ?>"
                       class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden card-hover">
                        <div class="h-36 <?= $rpColor['bg'] ?> flex items-center justify-center">
                            <svg class="w-10 h-10 <?= $rpColor['text'] ?> opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="<?= $rpColor['icon'] ?>"/></svg>
                        </div>
                        <div class="p-4">
                            <span class="inline-flex items-center gap-1 text-xs font-semibold <?= $rpColor['bg'] ?> <?= $rpColor['text'] ?> px-2 py-1 rounded-full">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $rpColor['icon'] ?>"/></svg>
                                <?= e($rp['category_name']) ?>
                            </span>
                            <h3 class="font-bold text-gray-900 mt-2"><?= e($rp['title']) ?></h3>
                            <p class="text-xs text-gray-400 mt-2"><?= timeAgo($rp['published_at']) ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>
<script>
var postChannel = pusher.subscribe('post-comments-<?= $post["id"] ?>');
postChannel.bind('new-comment', function(data) {
    var commentsSection = document.querySelector('.mt-6.space-y-4');
    if (!commentsSection) return;

    var html = '<div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm" style="opacity:0;transition:opacity 0.5s ease">'
        + '<div class="flex items-center gap-3 mb-2">'
        + '<div class="w-9 h-9 bg-blue-800 rounded-full flex items-center justify-center text-white text-xs font-bold">' + data.user_name.substring(0, 1).toUpperCase() + '</div>'
        + '<div><p class="text-sm font-medium text-gray-900">' + data.user_name + '</p>'
        + '<p class="text-xs text-gray-400">' + data.created_at + '</p></div></div>'
        + '<p class="text-gray-700 text-sm">' + data.comment + '</p></div>';

    var tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;
    var newComment = tempDiv.firstElementChild;
    commentsSection.insertBefore(newComment, commentsSection.firstChild);

    requestAnimationFrame(function() {
        newComment.style.opacity = '1';
    });
});
</script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="<?= SITE_URL ?>/assets/js/app.js?v=9"></script>
</body>
</html>
