<div class="page-header">
    <div><h1>Appointments</h1><p class="muted">Unique appointment_code + search + pagination + safe sort.</p></div>
    <a class="button" href="/appointments/create">+ Create Appointment</a>
</div>
<form class="search-bar" method="GET" action="/appointments">
    <input name="q" value="<?= e($keyword ?? '') ?>" placeholder="Tìm mã hẹn, tên, email, dịch vụ">
    <select name="sort">
        <?php foreach (['created_at'=>'Created At','appointment_date'=>'Appointment Date','appointment_code'=>'Code','patient_name'=>'Patient','fee_amount'=>'Fee','status'=>'Status','id'=>'ID'] as $key=>$label): ?>
            <option value="<?= e($key) ?>" <?= selected($sort ?? 'created_at', $key) ?>><?= e($label) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="direction">
        <option value="desc" <?= selected($direction ?? 'desc', 'desc') ?>>DESC</option>
        <option value="asc" <?= selected($direction ?? 'desc', 'asc') ?>>ASC</option>
    </select>
    <button class="button" type="submit">Filter</button>
</form>
<table>
    <thead><tr><th>ID</th><th>Code</th><th>Patient</th><th>Email</th><th>Date</th><th>Service</th><th>Fee</th><th>Status</th><th>Action</th></tr></thead>
    <tbody>
    <?php foreach ($appointments as $appointment): ?>
        <tr>
            <td><?= e($appointment['id']) ?></td>
            <td><?= e($appointment['appointment_code']) ?></td>
            <td><?= e($appointment['patient_name']) ?></td>
            <td><?= e($appointment['patient_email']) ?></td>
            <td><?= e($appointment['appointment_date']) ?></td>
            <td><?= e($appointment['service_type']) ?></td>
            <td><?= number_format((float)$appointment['fee_amount']) ?> VND</td>
            <td><span class="badge <?= e($appointment['status']) ?>"><?= e($appointment['status']) ?></span></td>
            <td><a class="button small" href="/appointments/edit?id=<?= e($appointment['id']) ?>">Edit</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<div class="pagination">
    <span>Showing page <?= e($page) ?> / <?= e($totalPages) ?>, total <?= e($totalItems) ?></span>
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a class="<?= $i === $page ? 'active' : '' ?>" href="/appointments?page=<?= e($i) ?>&q=<?= urlencode($keyword ?? '') ?>&sort=<?= e($sort ?? 'created_at') ?>&direction=<?= e($direction ?? 'desc') ?>"><?= e($i) ?></a>
    <?php endfor; ?>
</div>
