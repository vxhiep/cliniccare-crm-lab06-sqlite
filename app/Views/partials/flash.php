<?php foreach (get_flash() as $type => $message): ?>
    <div class="alert <?= e($type) ?>"><?= e($message) ?></div>
<?php endforeach; ?>
