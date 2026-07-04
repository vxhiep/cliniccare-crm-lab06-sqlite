<h1>Create Patient Lead</h1>
<?php if (!empty($errors['general'])): ?><div class="alert danger"><?= e($errors['general']) ?></div><?php endif; ?>
<form class="form-card" method="POST" action="/patients/store">
    <?= csrf_field() ?>
<div class="form-group">
    <label>Họ tên</label>
    <input name="name" value="<?= e(old('name', $old ?? [])) ?>">
    <?= field_error($errors ?? [], 'name') ?>
</div>
<div class="form-group">
    <label>Email</label>
    <input name="email" value="<?= e(old('email', $old ?? [])) ?>">
    <?= field_error($errors ?? [], 'email') ?>
</div>
<div class="form-group">
    <label>Số điện thoại</label>
    <input name="phone" value="<?= e(old('phone', $old ?? [])) ?>">
    <?= field_error($errors ?? [], 'phone') ?>
</div>
<div class="form-group">
    <label>Trạng thái</label>
    <?php $statusValue = old('status', $old ?? [], 'new'); ?>
    <select name="status">
        <option value="new" <?= selected($statusValue, 'new') ?>>new</option>
        <option value="contacted" <?= selected($statusValue, 'contacted') ?>>contacted</option>
        <option value="scheduled" <?= selected($statusValue, 'scheduled') ?>>scheduled</option>
        <option value="closed" <?= selected($statusValue, 'closed') ?>>closed</option>
    </select>
    <?= field_error($errors ?? [], 'status') ?>
</div>
<div class="form-group">
    <label>Nguồn</label>
    <?php $sourceValue = old('source', $old ?? [], 'phone'); ?>
    <select name="source">
        <option value="public_form" <?= selected($sourceValue, 'public_form') ?>>public_form</option>
        <option value="phone" <?= selected($sourceValue, 'phone') ?>>phone</option>
        <option value="facebook" <?= selected($sourceValue, 'facebook') ?>>facebook</option>
        <option value="referral" <?= selected($sourceValue, 'referral') ?>>referral</option>
        <option value="walk_in" <?= selected($sourceValue, 'walk_in') ?>>walk_in</option>
    </select>
    <?= field_error($errors ?? [], 'source') ?>
</div>
<div class="form-group">
    <label>Ghi chú</label>
    <textarea name="note" rows="4"><?= e(old('note', $old ?? [])) ?></textarea>
</div>
    <div class="form-actions"><button class="button" type="submit">Save Patient</button><a class="button secondary" href="/patients">Back</a></div>
</form>
