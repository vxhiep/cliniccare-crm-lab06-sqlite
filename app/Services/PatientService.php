<?php
class PatientService
{
    private array $statuses = ['new', 'contacted', 'scheduled', 'closed'];
    private array $sources = ['public_form', 'phone', 'facebook', 'referral', 'walk_in'];
    private array $sortMap = [
        'id' => 'id',
        'name' => 'name',
        'email' => 'email',
        'status' => 'status',
        'created_at' => 'created_at',
    ];

    public function __construct(private PatientRepository $patients)
    {
    }

    public function getList(array $query): array
    {
        $keyword = trim($query['q'] ?? '');
        $page = max(1, (int)($query['page'] ?? 1));
        $perPage = 10;
        $sort = $this->sortMap[$query['sort'] ?? 'created_at'] ?? 'created_at';
        $direction = strtolower($query['direction'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

        $totalItems = $this->patients->countAll($keyword);
        $totalPages = max(1, (int)ceil($totalItems / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        return [
            'patients' => $this->patients->getPaginated($keyword, $perPage, $offset, $sort, $direction),
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
        return $this->patients->findById($id);
    }

    public function create(array $input): array
    {
        $validation = $this->validate($input);
        if (!empty($validation['errors'])) {
            return ['success' => false, 'errors' => $validation['errors'], 'old' => $validation['values']];
        }

        try {
            $this->patients->create($validation['values']);
            return ['success' => true, 'errors' => []];
        } catch (DuplicateRecordException) {
            return [
                'success' => false,
                'errors' => ['email' => 'Email bệnh nhân này đã tồn tại trong hệ thống.'],
                'old' => $validation['values'],
            ];
        }
    }

    public function createPublicLead(array $input): array
    {
        $honeypot = trim($input['website'] ?? '');
        if ($honeypot !== '') {
            return [
                'success' => false,
                'errors' => ['general' => 'Yêu cầu bị từ chối do phát hiện spam.'],
                'old' => [],
            ];
        }

        $lastSubmit = (int)($_SESSION['public_lead_last_submit'] ?? 0);
        if ($lastSubmit > 0 && time() - $lastSubmit < 5) {
            return [
                'success' => false,
                'errors' => ['general' => 'Bạn gửi form quá nhanh. Vui lòng chờ vài giây rồi thử lại.'],
                'old' => $input,
            ];
        }

        $input['status'] = 'new';
        $input['source'] = 'public_form';
        $result = $this->create($input);
        if ($result['success']) {
            $_SESSION['public_lead_last_submit'] = time();
        }
        return $result;
    }

    public function update(int $id, array $input): array
    {
        if ($id <= 0 || !$this->patients->findById($id)) {
            return ['success' => false, 'errors' => ['general' => 'Bệnh nhân tiềm năng không tồn tại.'], 'old' => $input];
        }

        $validation = $this->validate($input);
        if (!empty($validation['errors'])) {
            return ['success' => false, 'errors' => $validation['errors'], 'old' => $validation['values']];
        }

        try {
            $this->patients->update($id, $validation['values']);
            return ['success' => true, 'errors' => []];
        } catch (DuplicateRecordException) {
            return [
                'success' => false,
                'errors' => ['email' => 'Email bệnh nhân này đã tồn tại trong hệ thống.'],
                'old' => $validation['values'],
            ];
        }
    }

    public function delete(int $id): array
    {
        if ($id <= 0) {
            return ['success' => false, 'errors' => ['general' => 'ID không hợp lệ.']];
        }
        $this->patients->delete($id);
        return ['success' => true, 'errors' => []];
    }

    private function validate(array $input): array
    {
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $status = trim($input['status'] ?? 'new');
        $source = trim($input['source'] ?? 'phone');
        $note = trim($input['note'] ?? '');
        $errors = [];

        if ($name === '') {
            $errors['name'] = 'Tên bệnh nhân không được để trống.';
        } elseif (mb_strlen($name) > 100) {
            $errors['name'] = 'Tên bệnh nhân tối đa 100 ký tự.';
        }

        if ($email === '') {
            $errors['email'] = 'Email không được để trống.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không đúng định dạng.';
        }

        if ($phone !== '' && !preg_match('/^[0-9+\-\s]{8,30}$/', $phone)) {
            $errors['phone'] = 'Số điện thoại chỉ gồm số, dấu +, dấu - hoặc khoảng trắng.';
        }

        if (!in_array($status, $this->statuses, true)) {
            $errors['status'] = 'Trạng thái chăm sóc không hợp lệ.';
        }

        if (!in_array($source, $this->sources, true)) {
            $errors['source'] = 'Nguồn liên hệ không hợp lệ.';
        }

        return [
            'errors' => $errors,
            'values' => compact('name', 'email', 'phone', 'status', 'source', 'note'),
        ];
    }
}
