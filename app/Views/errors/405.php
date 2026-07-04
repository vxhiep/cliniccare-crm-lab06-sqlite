<section class="panel">
    <h1>405 Method Not Allowed</h1>
    <p>Route này có tồn tại nhưng HTTP method hiện tại không được hỗ trợ.</p>
    <p>Allowed: <strong><?= e(implode(', ', $allowedMethods ?? [])) ?></strong></p>
    <a class="button" href="/">Về trang chủ</a>
</section>
