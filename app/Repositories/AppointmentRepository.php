<?php
class AppointmentRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function countAll(string $keyword = ''): int
    {
        $sql = "SELECT COUNT(*) AS total FROM appointments";
        $params = [];
        if ($keyword !== '') {
            $sql .= " WHERE appointment_code LIKE :keyword
                      OR patient_name LIKE :keyword
                      OR patient_email LIKE :keyword
                      OR service_type LIKE :keyword";
            $params['keyword'] = '%' . $keyword . '%';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)($stmt->fetch()['total'] ?? 0);
    }

    public function getPaginated(string $keyword, int $limit, int $offset, string $sort, string $direction): array
    {
        $sql = "SELECT id, appointment_code, patient_name, patient_email, appointment_date, service_type, fee_amount, status, created_at
                FROM appointments";
        $params = [];
        if ($keyword !== '') {
            $sql .= " WHERE appointment_code LIKE :keyword
                      OR patient_name LIKE :keyword
                      OR patient_email LIKE :keyword
                      OR service_type LIKE :keyword";
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
        $stmt = $this->db->prepare("SELECT * FROM appointments WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $appointment = $stmt->fetch();
        return $appointment ?: null;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO appointments
                (appointment_code, patient_name, patient_email, appointment_date, service_type, fee_amount, status, note)
                VALUES
                (:appointment_code, :patient_name, :patient_email, :appointment_date, :service_type, :fee_amount, :status, :note)";
        $stmt = $this->db->prepare($sql);
        try {
            return $stmt->execute($data);
        } catch (PDOException $e) {
            if ($this->isDuplicateUniqueError($e)) {
                throw new DuplicateRecordException('Duplicate appointment code.');
            }
            throw $e;
        }
    }

    public function update(int $id, array $data): bool
    {
        $data['id'] = $id;
        $sql = "UPDATE appointments
                SET appointment_code = :appointment_code,
                    patient_name = :patient_name,
                    patient_email = :patient_email,
                    appointment_date = :appointment_date,
                    service_type = :service_type,
                    fee_amount = :fee_amount,
                    status = :status,
                    note = :note,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";
        try {
            return $this->db->prepare($sql)->execute($data);
        } catch (PDOException $e) {
            if ($this->isDuplicateUniqueError($e)) {
                throw new DuplicateRecordException('Duplicate appointment code.');
            }
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM appointments WHERE id = :id");
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
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM appointments WHERE status = :status");
        $stmt->execute(['status' => $status]);
        return (int)($stmt->fetch()['total'] ?? 0);
    }

    public function sumCompletedRevenue(): float
    {
        $stmt = $this->db->query("SELECT COALESCE(SUM(fee_amount), 0) AS total FROM appointments WHERE status = 'completed'");
        return (float)($stmt->fetch()['total'] ?? 0);
    }
}
