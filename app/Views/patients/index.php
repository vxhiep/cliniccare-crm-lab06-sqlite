<div class="page-header">
    <div><h1>Patient Leads</h1><p class="muted">List + search + pagination + safe sort.</p></div>
    <a class="button" href="/patients/create">+ Create Patient</a>
</div>
<form class="search-bar" method="GET" action="/patients">
    <input name="q" value="<?= e($keyword ?? '') ?>" placeholder="Tìm tên, email, phone">
    <select name="sort">
        <?php foreach (['created_at'=>'Created At','name'=>'Name','email'=>'Email','status'=>'Status','id'=>'ID'] as $key=>$label): ?>
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
    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Source</th><th>Created</th><th>Action</th></tr></thead>
    <tbody>
    <?php foreach ($patients as $patient): ?>
        <tr>
            <td><?= e($patient['id']) ?></td>
            <td><?= e($patient['name']) ?></td>
            <td><?= e($patient['email']) ?></td>
            <td><?= e($patient['phone']) ?></td>
            <td><span class="badge <?= e($patient['status']) ?>"><?= e($patient['status']) ?></span></td>
            <td><span class="badge <?= e($patient['source']) ?>"><?= e($patient['source']) ?></span></td>
            <td><?= e($patient['created_at']) ?></td>
            <td><a class="button small" href="/patients/edit?id=<?= e($patient['id']) ?>">Edit</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<div class="pagination">
    <span>Showing page <?= e($page) ?> / <?= e($totalPages) ?>, total <?= e($totalItems) ?></span>
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a class="<?= $i === $page ? 'active' : '' ?>" href="/patients?page=<?= e($i) ?>&q=<?= urlencode($keyword ?? '') ?>&sort=<?= e($sort ?? 'created_at') ?>&direction=<?= e($direction ?? 'desc') ?>"><?= e($i) ?></a>
    <?php endfor; ?>
</div>
