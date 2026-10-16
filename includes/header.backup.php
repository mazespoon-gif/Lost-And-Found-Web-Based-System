<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? SITE_NAME) ?> - <?= SITE_NAME ?></title>
    <meta name="description" content="LookForit - Group 3 Generosity. Lost and found platform for the TCC community.">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/output.css">
    <link rel="icon" type="image/webp" href="image/high.webp">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        var pusher = new Pusher('<?= PUSHER_KEY ?>', {
            cluster: '<?= PUSHER_CLUSTER ?>',
            forceTLS: true
        });
    </script>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased">
