<h1>Đăng ký tư vấn khám</h1>
<p class="muted">Form công khai có honeypot và rate limit 5 giây/lần theo session.</p>
<?php if (!empty($errors['general'])): ?><div class="alert danger"><?= e($errors['general']) ?></div><?php endif; ?>
<form class="form-card" method="POST" action="/public-leads">
    <?= csrf_field() ?>
    <div class="hidden-hp">
        <label>Website</label>
        <input type="text" name="website" value="">
    </div>
    <div class="form-group">
        <label>Họ tên</label>
        <input name="name" value="<?= e(old('name', $old ?? [])) ?>" placeholder="Nguyễn Văn A">
        <?= field_error($errors ?? [], 'name') ?>
    </div>
    <div class="form-group">
        <label>Email</label>
        <input name="email" value="<?= e(old('email', $old ?? [])) ?>" placeholder="patient@example.com">
        <?= field_error($errors ?? [], 'email') ?>
    </div>
    <div class="form-group">
        <label>Số điện thoại</label>
        <input name="phone" value="<?= e(old('phone', $old ?? [])) ?>" placeholder="0909000001">
        <?= field_error($errors ?? [], 'phone') ?>
    </div>
    <div class="form-group">
        <label>Nội dung cần tư vấn</label>
        <textarea name="note" rows="4" placeholder="Tôi muốn đặt lịch khám tổng quát..."><?= e(old('note', $old ?? [])) ?></textarea>
    </div>
    <button class="button" type="submit">Gửi thông tin</button>
</form>
