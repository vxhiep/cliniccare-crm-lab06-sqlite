<?php
class DashboardController
{
    public function __construct(
        private PatientRepository $patients,
        private AppointmentRepository $appointments
    ) {
    }

    public function index(): void
    {
        require_login();
        render('dashboard/index', [
            'title' => 'Dashboard',
            'newPatients' => $this->patients->countByStatus('new'),
            'scheduledPatients' => $this->patients->countByStatus('scheduled'),
            'pendingAppointments' => $this->appointments->countByStatus('pending'),
            'completedAppointments' => $this->appointments->countByStatus('completed'),
            'revenue' => $this->appointments->sumCompletedRevenue(),
        ]);
    }
}
