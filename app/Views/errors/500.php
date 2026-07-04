<section class="panel">
    <h1><?= e($title ?? '500 Server Error') ?></h1>
    <p><?= e($message ?? 'Hệ thống đang gặp lỗi. Vui lòng thử lại sau.') ?></p>
    <p class="muted">Lỗi chi tiết được ghi vào <code>storage/logs/app.log</code>.</p>
    <a class="button" href="/">Về trang chủ</a>
</section>
