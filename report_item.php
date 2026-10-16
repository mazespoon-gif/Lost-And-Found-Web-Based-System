<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/functions.php';

requireLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $status = $_POST['status'] ?? 'lost';
    $contactInfo = trim($_POST['contact_info'] ?? '');

    if (empty($title) || empty($description) || empty($location)) {
        $error = 'Please fill in all required fields.';
    } elseif (!in_array($status, ['lost', 'found'])) {
        $error = 'Invalid status.';
    } else {
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploaded = uploadLostImage($_FILES['image']);
            if ($uploaded) {
                $imagePath = $uploaded;
            } else {
                $error = 'Invalid image file. Only JPG, PNG, GIF, WEBP under 5MB allowed.';
            }
        }

        if (empty($error)) {
            $stmt = db()->prepare("INSERT INTO lost_items (user_id, title, description, location, image, status, contact_info) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $title, $description, $location, $imagePath, $status, $contactInfo ?: $_SESSION['user_email']]);
            $newItemId = db()->lastInsertId();

            $newItem = [
                'id' => $newItemId,
                'title' => $title,
                'description' => excerpt($description, 80),
                'location' => $location,
                'status' => $status,
                'image' => $imagePath,
                'author_name' => $_SESSION['user_name'],
                'author_avatar' => imageUrl($_SESSION['user_avatar'] ?? ''),
                'created_at' => timeAgo(date('Y-m-d H:i:s')),
            ];
            pusherTrigger('lost-items', 'new-item', $newItem);

            $_SESSION['success'] = 'Item reported successfully!';
            redirect('/lost_and_found');
        }
    }
}

$pageTitle = 'Report Item';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div data-aos="fade-up">
        <a href="<?= SITE_URL ?>/lost_and_found" class="relative group overflow-hidden bg-blue-900/10 p-0.5 h-9 w-28 rounded-md active:scale-100 hover:scale-105 transition-all duration-300 mb-4 inline-flex">
            <span class="text-white text-sm bg-gradient-to-t from-blue-900 to-blue-800 h-full w-full rounded flex items-center justify-center">Back</span>
            <div class="absolute -bottom-12 group-hover:-bottom-10 transition-all duration-200 left-1/2 -z-10 -translate-x-1/2 blur size-14 rounded-full bg-blue-400/40"></div>
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Report an Item</h1>
        <p class="text-gray-500 mt-1">Fill in the details below to report a lost or found item.</p>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg text-sm mt-6 border border-red-100"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data" class="mt-8 bg-white rounded-2xl shadow-md border border-gray-100 p-6 md:p-8 space-y-6" data-aos="fade-up">
        <?= csrfField() ?>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Is this item lost or found?</label>
            <div class="flex gap-4" id="statusRadios">
                <label class="flex items-center gap-2 px-5 py-3 border-2 rounded-lg cursor-pointer transition <?= ($_POST['status'] ?? 'lost') === 'lost' ? 'border-red-500 bg-red-50' : 'border-gray-200 hover:border-gray-300' ?>" onclick="selectStatus(this, 'lost')">
                    <input type="radio" name="status" value="lost" <?= ($_POST['status'] ?? 'lost') === 'lost' ? 'checked' : '' ?> class="sr-only">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    <span class="text-sm font-semibold text-gray-700">I Lost This</span>
                </label>
                <label class="flex items-center gap-2 px-5 py-3 border-2 rounded-lg cursor-pointer transition <?= ($_POST['status'] ?? '') === 'found' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300' ?>" onclick="selectStatus(this, 'found')">
                    <input type="radio" name="status" value="found" <?= ($_POST['status'] ?? '') === 'found' ? 'checked' : '' ?> class="sr-only">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-semibold text-gray-700">I Found This</span>
                </label>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Item Name <span class="text-red-500">*</span></label>
            <input type="text" name="title" required value="<?= e($_POST['title'] ?? '') ?>"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                   placeholder="e.g. Black Notebook, Water Bottle, Phone">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
            <textarea name="description" required rows="4"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition resize-none"
                      placeholder="Describe the item in detail — color, brand, distinguishing features..."><?= e($_POST['description'] ?? '') ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Where was it lost/found? <span class="text-red-500">*</span></label>
            <input type="text" name="location" required value="<?= e($_POST['location'] ?? '') ?>"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                   placeholder="e.g. TCC Hallway, Canteen, Room D202">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Image (optional)</label>
            <input type="file" name="image" accept="image/*"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <p class="text-xs text-gray-400 mt-1">JPG, PNG, GIF, WEBP. Max 5MB.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Contact Info <span class="text-gray-400">(optional)</span></label>
            <input type="text" name="contact_info" value="<?= e($_POST['contact_info'] ?? $_SESSION['user_email']) ?>"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                   placeholder="Email or phone number">
            <p class="text-xs text-gray-400 mt-1">Defaults to your registered email.</p>
        </div>

        <button type="submit" class="w-full py-3 bg-blue-900 text-white font-semibold rounded-lg hover:bg-blue-800 transition shadow-md">
            Submit Report
        </button>
    </form>
</div>
<script>
function selectStatus(label, value) {
    var radios = document.getElementById('statusRadios');
    radios.querySelectorAll('label').forEach(function(l) {
        l.classList.remove('border-red-500', 'border-green-500', 'bg-red-50', 'bg-green-50');
        l.classList.add('border-gray-200');
    });
    if (value === 'lost') {
        label.classList.remove('border-gray-200');
        label.classList.add('border-red-500', 'bg-red-50');
    } else {
        label.classList.remove('border-gray-200');
        label.classList.add('border-green-500', 'bg-green-50');
    }
    label.querySelector('input[type="radio"]').checked = true;
}
</script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="<?= SITE_URL ?>/assets/js/app.js?v=9"></script>
</body>
</html>
