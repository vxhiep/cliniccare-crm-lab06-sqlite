<h1>Dashboard</h1>
<p class="muted">Xin chào <?= e(current_user()['name'] ?? '') ?>. Trang này yêu cầu session hợp lệ.</p>
<section class="grid">
    <div class="stat card"><span>New Patients</span><strong><?= e($newPatients) ?></strong></div>
    <div class="stat card"><span>Scheduled Patients</span><strong><?= e($scheduledPatients) ?></strong></div>
    <div class="stat card"><span>Pending Appointments</span><strong><?= e($pendingAppointments) ?></strong></div>
    <div class="stat card"><span>Completed</span><strong><?= e($completedAppointments) ?></strong></div>
</section>
<br>
<section class="grid">
    <div class="card"><h3>Revenue</h3><p><strong><?= number_format((float)$revenue) ?> VND</strong></p></div>
    <div class="card"><h3>Session</h3><p>Active, HttpOnly cookie, SameSite=Lax, timeout enabled.</p></div>
    <div class="card"><h3>Database</h3><p>PDO utf8mb4, prepared statements, unique/index.</p></div>
    <div class="card"><h3>Safe Errors</h3><p>Production không hiển thị SQLSTATE hoặc stack trace.</p></div>
</section>
