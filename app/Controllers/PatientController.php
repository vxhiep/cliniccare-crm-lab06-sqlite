<?php
class PatientController
{
    public function __construct(private PatientService $patients)
    {
    }

    public function index(): void
    {
        require_login();
        $data = $this->patients->getList($_GET);
        render('patients/index', ['title' => 'Patient Leads'] + $data);
    }

    public function create(): void
    {
        require_login();
        render('patients/create', ['title' => 'Create Patient Lead', 'errors' => [], 'old' => []]);
    }

    public function store(): void
    {
        require_login();
        verify_csrf();
        $result = $this->patients->create($_POST);
        if (!$result['success']) {
            render('patients/create', [
                'title' => 'Create Patient Lead',
                'errors' => $result['errors'],
                'old' => $result['old'] ?? $_POST,
            ]);
        }
        flash('success', 'Đã tạo hồ sơ bệnh nhân tiềm năng.');
        redirect('/patients');
    }

    public function edit(): void
    {
        require_login();
        $id = (int)($_GET['id'] ?? 0);
        $patient = $this->patients->find($id);
        if (!$patient) {
            http_response_code(404);
            render('errors/404', ['title' => 'Patient Not Found']);
        }
        render('patients/edit', ['title' => 'Edit Patient Lead', 'patient' => $patient, 'errors' => [], 'old' => $patient]);
    }

    public function update(): void
    {
        require_login();
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0);
        $result = $this->patients->update($id, $_POST);
        if (!$result['success']) {
            $patient = $this->patients->find($id) ?: ['id' => $id];
            render('patients/edit', [
                'title' => 'Edit Patient Lead',
                'patient' => $patient,
                'errors' => $result['errors'],
                'old' => $result['old'] ?? $_POST,
            ]);
        }
        flash('success', 'Đã cập nhật hồ sơ bệnh nhân tiềm năng.');
        redirect('/patients');
    }

    public function delete(): void
    {
        require_login();
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0);
        $result = $this->patients->delete($id);
        flash($result['success'] ? 'success' : 'error', $result['success'] ? 'Đã xóa hồ sơ bệnh nhân.' : 'Không thể xóa hồ sơ bệnh nhân.');
        redirect('/patients');
    }
}
