<?php
class PatientRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function countAll(string $keyword = ''): int
    {
        $sql = "SELECT COUNT(*) AS total FROM patients";
        $params = [];
        if ($keyword !== '') {
            $sql .= " WHERE name LIKE :keyword OR email LIKE :keyword OR phone LIKE :keyword";
            $params['keyword'] = '%' . $keyword . '%';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)($stmt->fetch()['total'] ?? 0);
    }

    public function getPaginated(string $keyword, int $limit, int $offset, string $sort, string $direction): array
    {
        $sql = "SELECT id, name, email, phone, status, source, created_at FROM patients";
        $params = [];
        if ($keyword !== '') {
            $sql .= " WHERE name LIKE :keyword OR email LIKE :keyword OR phone LIKE :keyword";
            $params['keyword'] = '%' . $keyword . '%';
        }

        $sql .= " ORDER BY {$sort} {$direction} LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM patients WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $patient = $stmt->fetch();
        return $patient ?: null;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO patients (name, email, phone, status, source, note)
                VALUES (:name, :email, :phone, :status, :source, :note)";
        $stmt = $this->db->prepare($sql);
        try {
            return $stmt->execute($data);
        } catch (PDOException $e) {
            if ($this->isDuplicateUniqueError($e)) {
                throw new DuplicateRecordException('Duplicate patient email.');
            }
            throw $e;
        }
    }

    public function update(int $id, array $data): bool
    {
        $data['id'] = $id;
        $sql = "UPDATE patients
                SET name = :name,
                    email = :email,
                    phone = :phone,
                    status = :status,
                    source = :source,
                    note = :note,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";
        try {
            return $this->db->prepare($sql)->execute($data);
        } catch (PDOException $e) {
            if ($this->isDuplicateUniqueError($e)) {
                throw new DuplicateRecordException('Duplicate patient email.');
            }
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM patients WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    private function isDuplicateUniqueError(PDOException $e): bool
    {
        $sqlState = $e->errorInfo[0] ?? '';
        $driverCode = isset($e->errorInfo[1]) ? (int)$e->errorInfo[1] : 0;
        $message = strtolower($e->getMessage());

        return ($sqlState === '23000' && str_contains($message, 'unique'))
            || ($driverCode === 19 && str_contains($message, 'unique'));
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM patients WHERE status = :status");
        $stmt->execute(['status' => $status]);
        return (int)($stmt->fetch()['total'] ?? 0);
    }
}
