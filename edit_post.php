<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/functions.php';

requireAdmin();

$postId = (int)($_GET['id'] ?? 0);
$post = getPostById($postId);

if (!$post) {
    redirect('/admin');
}

$categories = getCategories();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $title = trim($_POST['title'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $content = trim($_POST['content'] ?? '');
    $isPinned = isset($_POST['is_pinned']) ? 1 : 0;
    $imagePath = $post['image'];

    if (empty($title) || empty($content) || !$categoryId) {
        $error = 'Please fill in all required fields.';
    } elseif (!getCategoryById($categoryId)) {
        $error = 'Invalid category selected.';
    } else {
        $removeImage = isset($_POST['remove_image']) ? true : false;

        if ($removeImage && $post['image']) {
            deleteImage($post['image']);
            $imagePath = null;
        }

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploaded = uploadImage($_FILES['image']);
            if ($uploaded) {
                if ($post['image'] && !$removeImage) deleteImage($post['image']);
                $imagePath = $uploaded;
            } else {
                $error = 'Invalid image file. Only JPG, PNG, GIF, WEBP under 5MB allowed.';
            }
        }

        if (empty($error)) {
            if ($title !== $post['title']) {
                $newSlug = uniqueSlug($title, $postId);
            } else {
                $newSlug = $post['slug'];
            }

            $stmt = db()->prepare("UPDATE posts SET category_id = ?, title = ?, slug = ?, content = ?, image = ?, is_pinned = ? WHERE id = ?");
            $stmt->execute([$categoryId, $title, $newSlug, $content, $imagePath, $isPinned, $postId]);

            $_SESSION['success'] = 'Post updated successfully!';
            redirect('/admin');
        }
    }
    $post = getPostById($postId);
}

$pageTitle = 'Edit Post';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div data-aos="fade-up">
        <h1 class="text-3xl font-bold text-gray-900">Edit Post</h1>
        <p class="text-gray-500 mt-1">Update your post content</p>
    </div>

    <?php if ($error): ?>
        <div class="mt-6 bg-red-50 text-red-700 px-4 py-3 rounded-lg text-sm border border-red-100"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="mt-8 bg-white rounded-2xl shadow-md border border-gray-100 p-6 md:p-8" data-aos="fade-up">
        <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
            <?= csrfField() ?>
            <div class="grid md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Post Title *</label>
                    <input type="text" name="title" required value="<?= e($_POST['title'] ?? $post['title']) ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           placeholder="Enter post title">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                    <select name="category_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-white">
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (($post['category_id'] ?? 0) == $cat['id']) ? 'selected' : '' ?>>
                                <?= e($cat['category_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Featured Image</label>
                    <?php if ($post['image']): ?>
                        <div class="mb-2">
                            <img src="<?= e(imageUrl($post['image'])) ?>" alt="Current image" class="h-20 w-auto rounded-lg object-cover border border-gray-200">
                            <label class="inline-flex items-center gap-1.5 mt-2 text-sm text-red-600 cursor-pointer hover:text-red-700">
                                <input type="checkbox" name="remove_image" value="1" class="w-4 h-4 text-red-500 border-gray-300 rounded focus:ring-red-400">
                                Remove current image
                            </label>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/*"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-400 mt-1">JPG, PNG, GIF, WEBP. Max 5MB.</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Content *</label>
                <textarea name="content" rows="15" required
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition font-mono text-sm"
                          placeholder="Write your post content here."><?= e($_POST['content'] ?? $post['content']) ?></textarea>
                <p class="text-xs text-gray-400 mt-1">You can use basic HTML tags: &lt;p&gt;, &lt;br&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;h2&gt;, &lt;h3&gt;</p>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_pinned" id="is_pinned" value="1" <?= $post['is_pinned'] ? 'checked' : '' ?>
                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label for="is_pinned" class="text-sm font-medium text-gray-700">Pin this post</label>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="px-8 py-3 bg-blue-900 text-white font-semibold rounded-lg hover:bg-blue-800 transition shadow-sm">
                    Update Post
                </button>
                <a href="<?= SITE_URL ?>/admin" class="px-6 py-3 text-gray-700 font-medium hover:text-gray-900 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="<?= SITE_URL ?>/assets/js/app.js?v=9"></script>
</body>
</html>
