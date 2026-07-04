<?php
class AppointmentService
{
    private array $statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
    private array $sortMap = [
        'id' => 'id',
        'appointment_code' => 'appointment_code',
        'patient_name' => 'patient_name',
        'appointment_date' => 'appointment_date',
        'fee_amount' => 'fee_amount',
        'status' => 'status',
        'created_at' => 'created_at',
    ];

    public function __construct(private AppointmentRepository $appointments)
    {
    }

    public function getList(array $query): array
    {
        $keyword = trim($query['q'] ?? '');
        $page = max(1, (int)($query['page'] ?? 1));
        $perPage = 10;
        $sort = $this->sortMap[$query['sort'] ?? 'created_at'] ?? 'created_at';
        $direction = strtolower($query['direction'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

        $totalItems = $this->appointments->countAll($keyword);
        $totalPages = max(1, (int)ceil($totalItems / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        return [
            'appointments' => $this->appointments->getPaginated($keyword, $perPage, $offset, $sort, $direction),
            'keyword' => $keyword,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'sort' => array_search($sort, $this->sortMap, true) ?: 'created_at',
            'direction' => strtolower($direction),
        ];
    }

    public function find(int $id): ?array
    {
        return $this->appointments->findById($id);
    }

    public function create(array $input): array
    {
        $validation = $this->validate($input);
        if (!empty($validation['errors'])) {
            return ['success' => false, 'errors' => $validation['errors'], 'old' => $validation['values']];
        }

        try {
            $this->appointments->create($validation['values']);
            return ['success' => true, 'errors' => []];
        } catch (DuplicateRecordException) {
            return [
                'success' => false,
                'errors' => ['appointment_code' => 'Mã lịch hẹn này đã tồn tại.'],
                'old' => $validation['values'],
            ];
        }
    }

    public function update(int $id, array $input): array
    {
        if ($id <= 0 || !$this->appointments->findById($id)) {
            return ['success' => false, 'errors' => ['general' => 'Lịch hẹn không tồn tại.'], 'old' => $input];
        }

        $validation = $this->validate($input);
        if (!empty($validation['errors'])) {
            return ['success' => false, 'errors' => $validation['errors'], 'old' => $validation['values']];
        }

        try {
            $this->appointments->update($id, $validation['values']);
            return ['success' => true, 'errors' => []];
        } catch (DuplicateRecordException) {
            return [
                'success' => false,
                'errors' => ['appointment_code' => 'Mã lịch hẹn này đã tồn tại.'],
                'old' => $validation['values'],
            ];
        }
    }

    public function delete(int $id): array
    {
        if ($id <= 0) {
            return ['success' => false, 'errors' => ['general' => 'ID không hợp lệ.']];
        }
        $this->appointments->delete($id);
        return ['success' => true, 'errors' => []];
    }

    private function validate(array $input): array
    {
        $appointment_code = strtoupper(trim($input['appointment_code'] ?? ''));
        $patient_name = trim($input['patient_name'] ?? '');
        $patient_email = trim($input['patient_email'] ?? '');
        $appointment_date = trim($input['appointment_date'] ?? '');
        $service_type = trim($input['service_type'] ?? '');
        $fee_amount = trim((string)($input['fee_amount'] ?? '0'));
        $status = trim($input['status'] ?? 'pending');
        $note = trim($input['note'] ?? '');
        $errors = [];

        if ($appointment_code === '') {
            $errors['appointment_code'] = 'Mã lịch hẹn không được để trống.';
        } elseif (!preg_match('/^APT-[0-9]{4}-[0-9]{4}$/', $appointment_code)) {
            $errors['appointment_code'] = 'Mã lịch hẹn phải có dạng APT-2026-0001.';
        }

        if ($patient_name === '') {
            $errors['patient_name'] = 'Tên bệnh nhân không được để trống.';
        }

        if ($patient_email !== '' && !filter_var($patient_email, FILTER_VALIDATE_EMAIL)) {
            $errors['patient_email'] = 'Email bệnh nhân không đúng định dạng.';
        }

        if ($appointment_date === '') {
            $errors['appointment_date'] = 'Ngày giờ hẹn không được để trống.';
        } else {
            $appointment_date = str_replace('T', ' ', $appointment_date);
            if (strlen($appointment_date) === 16) {
                $appointment_date .= ':00';
            }
            $dt = DateTime::createFromFormat('Y-m-d H:i:s', $appointment_date);
            if (!$dt) {
                $errors['appointment_date'] = 'Ngày giờ hẹn không hợp lệ.';
            } else {
                $appointment_date = $dt->format('Y-m-d H:i:s');
            }
        }

        if ($service_type === '') {
            $errors['service_type'] = 'Dịch vụ khám không được để trống.';
        }

        if ($fee_amount === '' || !is_numeric($fee_amount) || (float)$fee_amount < 0) {
            $errors['fee_amount'] = 'Phí dự kiến phải là số không âm.';
        }

        if (!in_array($status, $this->statuses, true)) {
            $errors['status'] = 'Trạng thái lịch hẹn không hợp lệ.';
        }

        return [
            'errors' => $errors,
            'values' => [
                'appointment_code' => $appointment_code,
                'patient_name' => $patient_name,
                'patient_email' => $patient_email,
                'appointment_date' => $appointment_date,
                'service_type' => $service_type,
                'fee_amount' => (float)$fee_amount,
                'status' => $status,
                'note' => $note,
            ],
        ];
    }
}
