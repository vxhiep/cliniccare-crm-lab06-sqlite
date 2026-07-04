<?php
class AppointmentController
{
    public function __construct(private AppointmentService $appointments)
    {
    }

    public function index(): void
    {
        require_login();
        $data = $this->appointments->getList($_GET);
        render('appointments/index', ['title' => 'Appointments'] + $data);
    }

    public function create(): void
    {
        require_login();
        render('appointments/create', ['title' => 'Create Appointment', 'errors' => [], 'old' => []]);
    }

    public function store(): void
    {
        require_login();
        verify_csrf();
        $result = $this->appointments->create($_POST);
        if (!$result['success']) {
            render('appointments/create', [
                'title' => 'Create Appointment',
                'errors' => $result['errors'],
                'old' => $result['old'] ?? $_POST,
            ]);
        }
        flash('success', 'Đã tạo lịch hẹn khám.');
        redirect('/appointments');
    }

    public function edit(): void
    {
        require_login();
        $id = (int)($_GET['id'] ?? 0);
        $appointment = $this->appointments->find($id);
        if (!$appointment) {
            http_response_code(404);
            render('errors/404', ['title' => 'Appointment Not Found']);
        }
        render('appointments/edit', ['title' => 'Edit Appointment', 'appointment' => $appointment, 'errors' => [], 'old' => $appointment]);
    }

    public function update(): void
    {
        require_login();
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0);
        $result = $this->appointments->update($id, $_POST);
        if (!$result['success']) {
            $appointment = $this->appointments->find($id) ?: ['id' => $id];
            render('appointments/edit', [
                'title' => 'Edit Appointment',
                'appointment' => $appointment,
                'errors' => $result['errors'],
                'old' => $result['old'] ?? $_POST,
            ]);
        }
        flash('success', 'Đã cập nhật lịch hẹn khám.');
        redirect('/appointments');
    }

    public function delete(): void
    {
        require_login();
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0);
        $result = $this->appointments->delete($id);
        flash($result['success'] ? 'success' : 'error', $result['success'] ? 'Đã xóa lịch hẹn.' : 'Không thể xóa lịch hẹn.');
        redirect('/appointments');
    }
}
