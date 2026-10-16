<aside class="space-y-6" data-aos="fade-up">
    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Recent Posts
        </h3>
        <div class="space-y-3">
            <?php $recent = getRecentPosts(4); ?>
            <?php foreach ($recent as $post):
                $color = getCategoryColor($post['category_name']);
            ?>
                <a href="<?= SITE_URL ?>/post_details?slug=<?= e($post['slug']) ?>" class="flex gap-3 group">
                    <div class="w-16 h-16 rounded-lg <?= $color['bg'] ?> flex-shrink-0 flex items-center justify-center <?= $color['text'] ?> font-bold text-sm">
                        <?= strtoupper(substr($post['title'], 0, 2)) ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 group-hover:text-blue-700 transition line-clamp-2"><?= e($post['title']) ?></p>
                        <p class="text-xs text-gray-500 mt-1"><?= formatDate($post['published_at']) ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="bg-gradient-to-br from-blue-800 to-blue-900 rounded-xl shadow-md p-6 text-white">
        <h3 class="text-lg font-bold mb-2">Newsletter</h3>
        <p class="text-sm text-blue-200 mb-4">Stay updated with the latest school news and announcements.</p>
        <form id="newsletterForm">
            <input type="email" id="newsletterEmail" placeholder="Enter your email" required class="w-full px-4 py-2.5 rounded-lg text-gray-900 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
            <button type="submit" class="w-full px-4 py-2.5 bg-white text-blue-900 font-semibold rounded-lg hover:bg-blue-50 transition text-sm">Subscribe</button>
        </form>
    </div>
</aside>

<div id="toast" class="fixed top-4 right-4 z-[100] hidden">
    <div class="flex items-center gap-3 bg-white text-gray-900 px-5 py-4 rounded-xl shadow-2xl border border-gray-200 min-w-[320px] max-w-sm" data-aos="fade-down">
        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold">Subscribed!</p>
            <p class="text-xs text-gray-500" id="toastMessage">Thank you for subscribing to our newsletter.</p>
        </div>
        <button onclick="hideToast()" class="text-gray-400 hover:text-gray-600 transition flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
</div>

<script>
function showToast(message) {
    var toast = document.getElementById('toast');
    var msgEl = document.getElementById('toastMessage');
    if (msgEl) msgEl.textContent = message || 'Thank you for subscribing to our newsletter.';
    toast.classList.remove('hidden');
    setTimeout(function () { toast.classList.add('hidden'); }, 4000);
}

function hideToast() {
    document.getElementById('toast').classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('newsletterForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var email = document.getElementById('newsletterEmail');
            if (email && email.value.trim()) {
                showToast('Thanks, ' + email.value.trim() + '! You are now subscribed.');
                email.value = '';
            }
        });
    }
});
</script>
