<h1>Edit Appointment #<?= e($appointment['id'] ?? '') ?></h1>
<?php if (!empty($errors['general'])): ?><div class="alert danger"><?= e($errors['general']) ?></div><?php endif; ?>
<form class="form-card" method="POST" action="/appointments/update">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= e($appointment['id'] ?? '') ?>">
<div class="form-group">
    <label>Mã lịch hẹn</label>
    <input name="appointment_code" value="<?= e(old('appointment_code', $old ?? [])) ?>" placeholder="APT-2026-0001">
    <?= field_error($errors ?? [], 'appointment_code') ?>
</div>
<div class="form-group">
    <label>Tên bệnh nhân</label>
    <input name="patient_name" value="<?= e(old('patient_name', $old ?? [])) ?>">
    <?= field_error($errors ?? [], 'patient_name') ?>
</div>
<div class="form-group">
    <label>Email bệnh nhân</label>
    <input name="patient_email" value="<?= e(old('patient_email', $old ?? [])) ?>">
    <?= field_error($errors ?? [], 'patient_email') ?>
</div>
<div class="form-group">
    <label>Ngày giờ hẹn</label>
    <?php $dateValue = old('appointment_date', $old ?? []); if (strlen($dateValue) >= 16) $dateValue = str_replace(' ', 'T', substr($dateValue, 0, 16)); ?>
    <input type="datetime-local" name="appointment_date" value="<?= e($dateValue) ?>">
    <?= field_error($errors ?? [], 'appointment_date') ?>
</div>
<div class="form-group">
    <label>Dịch vụ</label>
    <input name="service_type" value="<?= e(old('service_type', $old ?? [])) ?>" placeholder="Khám tổng quát">
    <?= field_error($errors ?? [], 'service_type') ?>
</div>
<div class="form-group">
    <label>Phí dự kiến</label>
    <input type="number" min="0" step="1000" name="fee_amount" value="<?= e(old('fee_amount', $old ?? [], '0')) ?>">
    <?= field_error($errors ?? [], 'fee_amount') ?>
</div>
<div class="form-group">
    <label>Trạng thái</label>
    <?php $statusValue = old('status', $old ?? [], 'pending'); ?>
    <select name="status">
        <option value="pending" <?= selected($statusValue, 'pending') ?>>pending</option>
        <option value="confirmed" <?= selected($statusValue, 'confirmed') ?>>confirmed</option>
        <option value="completed" <?= selected($statusValue, 'completed') ?>>completed</option>
        <option value="cancelled" <?= selected($statusValue, 'cancelled') ?>>cancelled</option>
    </select>
    <?= field_error($errors ?? [], 'status') ?>
</div>
<div class="form-group">
    <label>Ghi chú</label>
    <textarea name="note" rows="4"><?= e(old('note', $old ?? [])) ?></textarea>
</div>
    <div class="form-actions"><button class="button" type="submit">Update</button><a class="button secondary" href="/appointments">Back</a></div>
</form>
<br>
<form method="POST" action="/appointments/delete" onsubmit="return confirm('Xóa lịch hẹn này?')">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= e($appointment['id'] ?? '') ?>">
    <button class="button danger" type="submit">Delete by POST</button>
</form>
