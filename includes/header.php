<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? SITE_NAME) ?> - <?= SITE_NAME ?></title>
    <meta name="description" content="LookForit - Group 3 Generosity. Lost and found platform for the TCC community.">
    <?php
    $ogTitle = $ogTitle ?? (($pageTitle ?? SITE_NAME) . ' - ' . SITE_NAME);
    $ogDescription = $ogDescription ?? 'LookForit - Group 3 Generosity. Lost and found platform for the TCC community.';
    $ogImage = $ogImage ?? (SITE_URL . '/image/high.webp');
    $ogUrl = $ogUrl ?? (SITE_URL . (isset($_SERVER['REQUEST_URI']) ? strtok($_SERVER['REQUEST_URI'], '?') : ''));
    ?>
    <meta property="og:title" content="<?= e($ogTitle) ?>">
    <meta property="og:description" content="<?= e($ogDescription) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta property="og:url" content="<?= e($ogUrl) ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= SITE_NAME ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($ogTitle) ?>">
    <meta name="twitter:description" content="<?= e($ogDescription) ?>">
    <meta name="twitter:image" content="<?= e($ogImage) ?>">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/output.css?v=9">
    <link rel="icon" type="image/webp" href="image/high.webp">
    <link rel="apple-touch-icon" href="<?= SITE_URL ?>/image/apple-touch-icon.png">
    <link rel="manifest" href="<?= SITE_URL ?>/manifest.php">
    <meta name="theme-color" content="#1e3a8a">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        var pusher = new Pusher('<?= PUSHER_KEY ?>', {
            cluster: '<?= PUSHER_CLUSTER ?>',
            forceTLS: true
        });
    </script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('<?= SITE_URL ?>/sw.php').catch(function () {});
            });
        }
    </script>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased">
