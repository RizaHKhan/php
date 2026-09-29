<?php

namespace App\Database;

use PDO;
use Throwable;

final class SqlFileRunner
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function ensureTrackingTables(): void
    {
        $this->pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS schema_migrations (
    filename TEXT PRIMARY KEY,
    applied_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS schema_seeds (
    filename TEXT PRIMARY KEY,
    applied_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
SQL);
    }

    /**
     * @return list<string>
     */
    public function run(string $path, string $trackingTable): array
    {
        $this->ensureTrackingTables();
        $applied = $this->appliedFiles($trackingTable);
        $ran = [];

        foreach ($this->sqlFiles($path) as $file) {
            $filename = basename($file);

            if (isset($applied[$filename])) {
                continue;
            }

            $sql = file_get_contents($file);

            if ($sql === false) {
                throw new \RuntimeException("Could not read {$file}");
            }

            $this->pdo->beginTransaction();

            try {
                $this->pdo->exec($sql);
                $statement = $this->pdo->prepare("INSERT INTO {$trackingTable} (filename) VALUES (:filename)");
                $statement->execute(['filename' => $filename]);
                $this->pdo->commit();
                $ran[] = $filename;
            } catch (Throwable $throwable) {
                $this->pdo->rollBack();
                throw $throwable;
            }
        }

        return $ran;
    }

    /**
     * @return list<array{filename: string, state: string}>
     */
    public function status(string $path, string $trackingTable): array
    {
        $this->ensureTrackingTables();
        $applied = $this->appliedFiles($trackingTable);
        $status = [];

        foreach ($this->sqlFiles($path) as $file) {
            $filename = basename($file);
            $status[] = [
                'filename' => $filename,
                'state' => isset($applied[$filename]) ? 'applied' : 'pending',
            ];
        }

        return $status;
    }

    /**
     * @return list<string>
     */
    public function pending(string $path, string $trackingTable): array
    {
        $this->ensureTrackingTables();
        $applied = $this->appliedFiles($trackingTable);

        return array_values(array_filter(
            array_map('basename', $this->sqlFiles($path)),
            static fn (string $filename): bool => !isset($applied[$filename])
        ));
    }

    /**
     * @return list<string>
     */
    private function sqlFiles(string $path): array
    {
        $files = glob(rtrim($path, '/') . '/*.sql') ?: [];
        sort($files, SORT_STRING);

        return $files;
    }

    /**
     * @return array<string, bool>
     */
    private function appliedFiles(string $trackingTable): array
    {
        $rows = $this->pdo->query("SELECT filename FROM {$trackingTable}")->fetchAll(PDO::FETCH_COLUMN);

        return array_fill_keys($rows, true);
    }
}
