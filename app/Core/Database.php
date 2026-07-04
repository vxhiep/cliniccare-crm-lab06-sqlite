<?php
class Database
{
    private static ?PDO $connection = null;

    public static function connect(array $config): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $path = $config['path'] ?? dirname(__DIR__, 2) . '/database/cliniccare.sqlite';
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $shouldInitialize = !is_file($path) || filesize($path) === 0;

        self::$connection = new PDO('sqlite:' . $path, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        self::$connection->exec('PRAGMA foreign_keys = ON');

        if ($shouldInitialize) {
            self::initializeSqlite(self::$connection);
        }

        return self::$connection;
    }

    private static function initializeSqlite(PDO $pdo): void
    {
        $schemaFile = dirname(__DIR__, 2) . '/database/schema.sql';
        $seedFile = dirname(__DIR__, 2) . '/database/seed.sql';

        if (is_file($schemaFile)) {
            $pdo->exec(file_get_contents($schemaFile));
        }

        if (is_file($seedFile)) {
            $pdo->exec(file_get_contents($seedFile));
        }
    }
}
