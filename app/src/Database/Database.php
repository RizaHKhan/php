<?php

namespace App\Database;

use PDO;
use PDOException;

final class Database
{
    public static function connect(): PDO
    {
        $dsn = getenv('DATABASE_URL') ?: 'pgsql:host=db;port=5432;dbname=app';
        $user = getenv('DATABASE_USER') ?: 'app';
        $password = getenv('DATABASE_PASSWORD') ?: 'secret';
        $attempts = 30;

        while ($attempts-- > 0) {
            try {
                return new PDO($dsn, $user, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $exception) {
                if ($attempts === 0) {
                    throw $exception;
                }

                sleep(1);
            }
        }

        throw new \RuntimeException('Could not connect to database.');
    }
}
