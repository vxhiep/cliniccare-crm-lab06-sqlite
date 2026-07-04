<h1>Login</h1>
<p class="muted">Tài khoản demo: <strong>admin@cliniccare.test</strong> / <strong>123456</strong></p>
<?php if (!empty($errors['general'])): ?><div class="alert danger"><?= e($errors['general']) ?></div><?php endif; ?>
<form class="form-card" method="POST" action="/login">
    <?= csrf_field() ?>
    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="<?= e(old('email', $old ?? [])) ?>" placeholder="admin@cliniccare.test">
        <?= field_error($errors ?? [], 'email') ?>
    </div>
    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="123456">
        <?= field_error($errors ?? [], 'password') ?>
    </div>
    <button class="button" type="submit">Login</button>
</form>
