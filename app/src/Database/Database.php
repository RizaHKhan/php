<?php

namespace App\Database;

final class Database
{
    /**
     * Allow up to 30 seconds for Postgres to become ready when the app and db
     * containers start at the same time under Docker Compose.
     */
    private const CONNECTION_RETRY_ATTEMPTS = 30;

    public static function connect(): \PDO
    {
        $dsn      = getenv('DATABASE_URL') ?: 'pgsql:host=db;port=5432;dbname=app';
        $user     = getenv('DATABASE_USER') ?: 'app';
        $password = getenv('DATABASE_PASSWORD') ?: 'secret';
        $attempts = self::CONNECTION_RETRY_ATTEMPTS;

        while ($attempts-- > 0) {
            try {
                return new \PDO($dsn, $user, $password, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]);
            } catch (\PDOException $exception) {
                if (0 === $attempts) {
                    throw $exception;
                }

                sleep(1);
            }
        }

        throw new \RuntimeException('Could not connect to database.');
    }
}
