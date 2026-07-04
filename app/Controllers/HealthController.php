<?php
class HealthController
{
    public function __construct(private array $dbConfig)
    {
    }

    public function index(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        try {
            $pdo = Database::connect($this->dbConfig);
            $pdo->query('SELECT 1');
            $db = 'ok';
        } catch (Throwable $e) {
            app_log($e);
            $db = 'error';
        }

        echo json_encode([
            'app' => 'ClinicCare Appointment CRM',
            'status' => $db === 'ok' ? 'ok' : 'degraded',
            'database' => $db,
            'time' => date('Y-m-d H:i:s'),
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}
