<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/csrf.php";
require_once __DIR__ . "/functions.php";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$item = getLostItemById($id);

if (!$item) {
    redirect("/lost_and_found");
}

// Handle comment submission for lost items
if ($_SERVER["REQUEST_METHOD"] === "POST" && $item["status"] === "lost") {
    requireLogin();
    if (isset($_POST["add_comment"]) && !empty(trim($_POST["comment"]))) {
        $comment = trim($_POST["comment"]);
        addLostItemComment($item["id"], $_SESSION["user_id"], $comment);

        $newComment = [
            'item_id' => $item["id"],
            'user_name' => $_SESSION["user_name"],
            'comment' => $comment,
            'created_at' => 'Just now',
        ];
        pusherTrigger('item-comments-' . $item["id"], 'new-comment', $newComment);

        header("Location: " . SITE_URL . "/item_detail?id=" . $item["id"]);
        exit;
    }
    if (isset($_POST["edit_comment"]) && !empty(trim($_POST["comment"]))) {
        $commentId = (int)$_POST["comment_id"];
        $comment = trim($_POST["comment"]);
        updateLostItemComment($commentId, $_SESSION["user_id"], $comment);
        header("Location: " . SITE_URL . "/item_detail?id=" . $item["id"]);
        exit;
    }
    if (isset($_POST["delete_comment"])) {
        $commentId = (int)$_POST["comment_id"];
        deleteLostItemComment($commentId, $_SESSION["user_id"]);
        header("Location: " . SITE_URL . "/item_detail?id=" . $item["id"]);
        exit;
    }
}

// Handle claim submission for found items
if ($_SERVER["REQUEST_METHOD"] === "POST" && $item["status"] === "found") {
    requireLogin();
    if (isset($_POST["submit_claim"]) && !empty(trim($_POST["proof"]))) {
        $proof = trim($_POST["proof"]);
        addClaim($item["id"], $_SESSION["user_id"], $proof);
        header("Location: " . SITE_URL . "/item_detail?id=" . $item["id"]);
        exit;
    }
}

// Handle claim accept/reject for found item owner
if ($_SERVER["REQUEST_METHOD"] === "POST" && $item["status"] === "found" && $item["user_id"] == ($_SESSION["user_id"] ?? 0)) {
    if (isset($_POST["accept_claim"])) {
        $claimId = (int)$_POST["claim_id"];
        updateClaimStatus($claimId, "accepted");

        $claim = getClaimById($claimId);
        if ($claim) {
            $ownerEmail = e($item["contact_info"] ?? $item["author_email"]);
            $gmailLink = "https://mail.google.com/mail/?view=cm&fs=1&to=" . urlencode($item["author_email"]) . "&su=" . urlencode("Re: Found Item - " . $item["title"]) . "&body=" . urlencode("Hi " . $claim["claimant_name"] . ",\n\nI've accepted your claim for \"" . $item["title"] . "\". Please contact me to arrange the return.\n\nThank you!");
            createNotification($claim["claimant_id"], "Your claim for \"" . $item["title"] . "\" has been accepted by the owner. Contact them via email.", $gmailLink);

            // Send email to claimant
            $to = $claim["claimant_email"];
            $subject = "Re: Found Item - " . $item["title"];
            $message = "Hi " . $claim["claimant_name"] . ",\n\n";
            $message .= "I've seen your claim along with description for the found item \"" . $item["title"] . "\".\n\n";
            $message .= "If this is truly yours, you can claim it in OSAS for second verification.\n\n";
            $message .= "Thank You,\n" . e($item["author_name"]);
            $headers = "From: " . SITE_NAME . " <noreply@" . parse_url(SITE_URL, PHP_URL_HOST) . ">\r\n";
            $headers .= "Reply-To: " . ($item["contact_info"] ?? $item["author_email"]) . "\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            mail($to, $subject, $message, $headers);
        }

        $stmt = db()->prepare("UPDATE claims SET status = 'rejected' WHERE lost_item_id = ? AND id != ? AND status = 'pending'");
        $stmt->execute([(int)$item["id"], $claimId]);

        header("Location: " . SITE_URL . "/item_detail?id=" . $item["id"]);
        exit;
    }
    if (isset($_POST["reject_claim"])) {
        updateClaimStatus($_POST["claim_id"], "rejected");
        header("Location: " . SITE_URL . "/item_detail?id=" . $item["id"]);
        exit;
    }
}

$isOwner = $item["user_id"] == ($_SESSION["user_id"] ?? 0);
$comments = $item["status"] === "lost" ? getLostItemComments($item["id"]) : [];
$claims = ($item["status"] === "found" && $isOwner) ? getClaimsByItemId($item["id"]) : [];

$hasAcceptedClaim = false;
if ($item["status"] === "found") {
    $stmt = db()->prepare("SELECT COUNT(*) FROM claims WHERE lost_item_id = ? AND status = 'accepted'");
    $stmt->execute([(int)$item["id"]]);
    $hasAcceptedClaim = (int)$stmt->fetchColumn() > 0;
}

$pageTitle = e($item["title"]);
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/navbar.php";
?>

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
<div class="w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="<?= SITE_URL ?>/lost_and_found" class="relative group overflow-hidden bg-blue-900/10 p-0.5 h-9 w-28 rounded-md active:scale-100 hover:scale-105 transition-all duration-300 mb-6 inline-flex">
        <span class="text-white text-sm bg-gradient-to-t from-blue-900 to-blue-800 h-full w-full rounded flex items-center justify-center gap-1">
            Back
        </span>
        <div class="absolute -bottom-12 group-hover:-bottom-10 transition-all duration-200 left-1/2 -z-10 -translate-x-1/2 blur size-14 rounded-full bg-blue-400/40"></div>
    </a>

    <div class="w-full bg-white border border-gray-200 rounded-xl p-4 shadow-sm" data-aos="fade-up">
        <!-- Header -->
        <div class="flex items-start justify-between mb-3">
            <div class="flex items-center space-x-3 min-w-0 flex-1">
                <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center text-white text-sm font-bold border border-gray-200 <?= $item["status"] === "lost" ? "bg-red-500" : "bg-green-500" ?>">
                    <?= strtoupper(substr($item["author_name"], 0, 2)) ?>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center space-x-1">
                        <span class="flex items-center gap-1.5 font-bold text-gray-900 truncate">
                            <svg class="w-4 h-4 flex-shrink-0 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <?= e($item["author_name"]) ?>
                        </span>
                        <svg class="w-4 h-4 text-blue-500 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M22.5 12.5c0-1.58-.875-2.95-2.148-3.6.154-.435.238-.905.238-1.4 0-2.21-1.71-3.998-3.818-3.998-.47 0-.92.084-1.336.25C14.818 2.415 13.51 1.5 12 1.5s-2.816.917-3.437 2.25c-.415-.165-.866-.25-1.336-.25-2.11 0-3.818 1.79-3.818 4 0 .494.083.964.237 1.4-1.272.65-2.147 2.018-2.147 3.6 0 1.495.782 2.798 1.942 3.486-.02.17-.032.34-.032.514 0 2.21 1.708 4 3.818 4 .47 0 .92-.086 1.335-.25.62 1.334 1.926 2.25 3.437 2.25 1.512 0 2.818-.916 3.437-2.25.415.163.865.248 1.336.248 2.11 0 3.818-1.79 3.818-4 0-.174-.012-.344-.033-.513 1.158-.687 1.943-1.99 1.943-3.484zm-6.616-3.334l-4.334 6.5c-.145.217-.382.334-.625.334-.143 0-.288-.04-.416-.126l-.115-.094-2.415-2.415c-.293-.293-.293-.768 0-1.06s.768-.294 1.06 0l1.77 1.767 3.825-5.74c.23-.345.696-.436 1.04-.207.346.23.44.696.21 1.04z"/>
                        </svg>
                    </div>
                    <span class="flex items-center gap-1.5 text-gray-500 text-sm truncate">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <?= e($item["location"]) ?>
                    </span>
                </div>
            </div>
            <?php if ($isOwner || isAdmin()): ?>
                <div class="relative flex-shrink-0 ml-2">
                    <button onclick="toggleDropdown()" class="text-gray-400 hover:text-gray-600 transition-colors p-1">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M6 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm12 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-6 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                        </svg>
                    </button>
                    <div id="itemDropdown" class="hidden absolute right-0 top-full mt-1 w-36 bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden">
                        <a href="<?= SITE_URL ?>/edit_item?id=<?= $item["id"] ?>" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </a>
                        <button type="button" onclick="openDeleteModal()" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Delete
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Content -->
        <div class="flex items-center justify-between mb-2">
            <h1 class="text-xl md:text-2xl font-bold text-gray-900"><?= e($item["title"]) ?></h1>
            <span class="text-xs font-bold px-3 py-1 rounded-full flex-shrink-0 ml-3 <?= $item["status"] === "lost" ? "bg-red-100 text-red-600" : "bg-blue-100 text-blue-600" ?>">
                <?= ucfirst($item["status"]) ?>
            </span>
        </div>
        <div class="mb-3">
            <p class="text-gray-700 leading-relaxed whitespace-pre-line text-sm"><?= e($item["description"]) ?></p>
        </div>

        <!-- Image -->
        <?php if ($item["image"]): ?>
            <div class="mb-3">
                <div class="rounded-xl overflow-hidden border border-gray-200">
                    <img src="<?= e(imageUrl($item["image"])) ?>" alt="<?= e($item["title"]) ?>" class="w-full rounded-lg" />
                </div>
            </div>
        <?php endif; ?>

        <!-- Timestamp -->
        <div class="text-gray-500 text-xs"><?= timeAgo($item["created_at"]) ?></div>

        <!-- Contact -->
        <hr class="my-4 border-gray-200">
        <div class="bg-gray-50 rounded-xl p-4">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span class="text-sm font-semibold text-gray-900"><?= e($item["contact_info"] ?? $item["author_email"]) ?></span>
            </div>
            <p class="text-xs text-gray-400 ml-6">Contact the person who reported this item.</p>
        </div>
    </div>

    <?php if ($item["status"] === "lost"): ?>
    <!-- Comments Section for Lost Items -->
    <div class="mt-6 bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Comments <?= count($comments) ?></h2>

        <?php if (isLoggedIn()): ?>
        <form method="POST" class="mb-6">
            <?= csrfField() ?>
            <div class="flex gap-3">
                <textarea name="comment" rows="2" required placeholder="Add a comment..." class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                <button type="submit" name="add_comment" class="self-end px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition">Post</button>
            </div>
        </form>
        <?php else: ?>
        <p class="text-sm text-gray-500 mb-4"><a href="<?= SITE_URL ?>/login" class="text-blue-600 hover:underline">Log in</a> to comment.</p>
        <?php endif; ?>

        <?php if (empty($comments)): ?>
            <p class="text-sm text-gray-400">No comments yet.</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($comments as $c): ?>
                    <div class="flex gap-3">
                        <div class="flex-shrink-0 w-9 h-9 rounded-full bg-blue-900 flex items-center justify-center text-white text-xs font-bold">
                            <?= strtoupper(substr($c["user_name"], 0, 2)) ?>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-gray-900"><?= e($c["user_name"]) ?></span>
                                <span class="text-xs text-gray-400"><?= timeAgo($c["created_at"]) ?></span>
                                <?php if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] == $c["user_id"]): ?>
                                    <div class="ml-auto flex items-center gap-1">
                                        <button onclick="toggleEditComment(<?= $c['id'] ?>)" class="text-gray-400 hover:text-blue-600 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button type="button" onclick="openDeleteCommentModal(<?= $c['id'] ?>)" class="text-gray-400 hover:text-red-600 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm text-gray-700 mt-1"><?= e($c["comment"]) ?></p>
                            <?php if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] == $c["user_id"]): ?>
                                <div id="edit-form-<?= $c['id'] ?>" class="hidden mt-2">
                                    <form method="POST" class="flex gap-2">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
                                        <input type="text" name="comment" value="<?= e($c['comment']) ?>" class="flex-1 border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <button type="submit" name="edit_comment" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition">Save</button>
                                        <button type="button" onclick="toggleEditComment(<?= $c['id'] ?>)" class="px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-200 transition">Cancel</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($item["status"] === "found"): ?>
    <!-- Found Item Actions -->
    <div class="mt-6 bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
        <?php if ($hasAcceptedClaim): ?>
            <div class="flex items-center gap-2 text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-sm font-medium">This item is in process of return</span>
            </div>
        <?php elseif (isLoggedIn() && !$isOwner): ?>
            <button onclick="openClaimModal()" class="w-full px-5 py-2.5 bg-green-600 text-white text-sm font-medium rounded-xl hover:bg-green-700 transition">I Found This Item</button>
        <?php elseif (!isLoggedIn()): ?>
            <p class="text-sm text-gray-500"><a href="<?= SITE_URL ?>/login" class="text-blue-600 hover:underline">Log in</a> to claim this item.</p>
        <?php endif; ?>

        <!-- Claims List for Owner -->
        <?php if ($isOwner): ?>
            <h2 class="text-lg font-bold text-gray-900 mb-4">Claims <?= count($claims) ?></h2>
            <?php if (empty($claims)): ?>
                <p class="text-sm text-gray-400">No claims yet.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($claims as $cl): ?>
                        <div class="border border-gray-200 rounded-xl p-4">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="flex-shrink-0 w-9 h-9 rounded-full bg-green-600 flex items-center justify-center text-white text-xs font-bold">
                                    <?= strtoupper(substr($cl["claimant_name"], 0, 2)) ?>
                                </div>
                                <div>
                                    <span class="text-sm font-semibold text-gray-900"><?= e($cl["claimant_name"]) ?></span>
                                    <span class="text-xs text-gray-400 ml-2"><?= timeAgo($cl["created_at"]) ?></span>
                                </div>
                                <span class="ml-auto text-xs font-semibold px-2 py-0.5 rounded-full <?= $cl["status"] === "pending" ? "bg-yellow-100 text-yellow-700" : ($cl["status"] === "accepted" ? "bg-green-100 text-green-700" : "bg-red-100 text-red-700") ?>">
                                    <?= ucfirst($cl["status"]) ?>
                                </span>
                            </div>
                            <p class="text-sm text-gray-700 mb-3"><?= e($cl["proof"]) ?></p>
                            <?php if ($cl["status"] === "pending"): ?>
                                <div class="flex gap-2">
                                    <form method="POST" class="inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="claim_id" value="<?= $cl["id"] ?>">
                                        <button type="submit" name="accept_claim" class="px-4 py-1.5 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700 transition">Accept</button>
                                    </form>
                                    <form method="POST" class="inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="claim_id" value="<?= $cl["id"] ?>">
                                        <button type="submit" name="reject_claim" class="px-4 py-1.5 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700 transition">Reject</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 p-6" data-aos="zoom-in">
        <div class="text-center">
            <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Delete Item</h3>
            <p class="text-gray-600 text-sm mb-6">Are you sure you want to delete this report? This action cannot be undone.</p>
            <form id="deleteForm" method="POST" action="delete_item">
                <?= csrfField() ?>
                <input type="hidden" name="item_id" value="<?= $item["id"] ?>">
                <div class="flex items-center justify-center gap-3">
                    <button type="button" onclick="closeDeleteModal()" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">Cancel</button>
                    <button type="submit" name="delete_item" class="px-6 py-2.5 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition shadow-sm">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($item["status"] === "found" && isLoggedIn() && !$isOwner): ?>
<!-- Claim Modal -->
<div id="claimModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 p-6">
        <div class="text-center mb-4">
            <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Claim This Item</h3>
            <p class="text-gray-600 text-sm mb-4">Please provide proof that this item is yours (e.g., describe the item, provide identifying details).</p>
        </div>
        <form method="POST">
            <?= csrfField() ?>
            <textarea name="proof" rows="4" required placeholder="Describe how you can prove this item is yours..." class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 resize-none mb-4"></textarea>
            <div class="flex gap-3">
                <button type="button" onclick="closeClaimModal()" class="flex-1 px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" name="submit_claim" class="flex-1 px-5 py-2.5 text-sm font-medium text-white bg-green-600 rounded-xl hover:bg-green-700 transition">Submit Claim</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div id="deleteCommentModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 p-6">
        <div class="text-center">
            <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Delete Comment</h3>
            <p class="text-gray-600 text-sm mb-6">Are you sure you want to delete this comment? This action cannot be undone.</p>
            <form id="deleteCommentForm" method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="comment_id" id="deleteCommentId" value="">
                <div class="flex items-center justify-center gap-3">
                    <button type="button" onclick="closeDeleteCommentModal()" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">Cancel</button>
                    <button type="submit" name="delete_comment" class="px-6 py-2.5 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition shadow-sm">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openDeleteModal() {
    document.getElementById('deleteModal').classList.remove('hidden');
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
function toggleDropdown() {
    document.getElementById('itemDropdown').classList.toggle('hidden');
}
document.addEventListener('click', function(e) {
    const dd = document.getElementById('itemDropdown');
    if (dd && !dd.parentElement.contains(e.target)) {
        dd.classList.add('hidden');
    }
});
function openClaimModal() {
    document.getElementById('claimModal').classList.remove('hidden');
}
function closeClaimModal() {
    document.getElementById('claimModal').classList.add('hidden');
}
function toggleEditComment(id) {
    var form = document.getElementById('edit-form-' + id);
    if (form) form.classList.toggle('hidden');
}
function openDeleteCommentModal(id) {
    document.getElementById('deleteCommentId').value = id;
    document.getElementById('deleteCommentModal').classList.remove('hidden');
}
function closeDeleteCommentModal() {
    document.getElementById('deleteCommentModal').classList.add('hidden');
}
document.getElementById('deleteCommentModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeDeleteCommentModal();
});
document.getElementById('claimModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeClaimModal();
});

<?php if ($toastMsg): ?>
setTimeout(function() { hideActionToast(); }, 5000);
<?php endif; ?>
function hideActionToast() {
    var t = document.getElementById('actionToast');
    if (t) { t.style.transition = 'opacity 0.3s ease'; t.style.opacity = '0'; setTimeout(function() { t.remove(); }, 300); }
}

var itemChannel = pusher.subscribe('item-comments-<?= $item["id"] ?>');
itemChannel.bind('new-comment', function(data) {
    var commentsDiv = document.querySelector('.space-y-4');
    if (!commentsDiv || !commentsDiv.closest('.mt-6')) return;

    var html = '<div class="flex gap-3" style="opacity:0;transition:opacity 0.5s ease">'
        + '<div class="flex-shrink-0 w-9 h-9 rounded-full bg-blue-900 flex items-center justify-center text-white text-xs font-bold">' + data.user_name.substring(0, 2).toUpperCase() + '</div>'
        + '<div class="flex-1">'
        + '<div class="flex items-center gap-2">'
        + '<span class="text-sm font-semibold text-gray-900">' + data.user_name + '</span>'
        + '<span class="text-xs text-gray-400">' + data.created_at + '</span></div>'
        + '<p class="text-sm text-gray-700 mt-1">' + data.comment + '</p></div></div>';

    var tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;
    var newComment = tempDiv.firstElementChild;
    commentsDiv.insertBefore(newComment, commentsDiv.firstChild);

    requestAnimationFrame(function() {
        newComment.style.opacity = '1';
    });
});
</script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="<?= SITE_URL ?>/assets/js/app.js?v=8"></script>
</body>
</html>
