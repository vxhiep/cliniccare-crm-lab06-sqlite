<header class="topbar">
    <div class="brand">ClinicCare CRM</div>
    <nav>
        <a href="/">Home</a>
        <a href="/public-leads/create">Public Form</a>
        <?php if (is_logged_in()): ?>
            <a href="/dashboard">Dashboard</a>
            <a href="/patients">Patients</a>
            <a href="/patients/create">Create Patient</a>
            <a href="/appointments">Appointments</a>
            <a href="/appointments/create">Create Appointment</a>
            <a href="/health">Health</a>
            <form class="logout-form" method="POST" action="/logout">
                <?= csrf_field() ?>
                <button class="logout-button" type="submit">Logout</button>
            </form>
        <?php else: ?>
            <a href="/login">Login</a>
            <a href="/health">Health</a>
        <?php endif; ?>
    </nav>
</header>
