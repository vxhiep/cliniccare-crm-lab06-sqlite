<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'ClinicCare CRM') ?></title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <?php partial('nav'); ?>
    <main class="container">
        <?php partial('flash'); ?>
        <?= $content ?? '' ?>
    </main>
</body>
</html>
