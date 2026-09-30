<?php

namespace App\View;

final class Greeter
{
    public function message(): string
    {
        $_SESSION['visits'] = ($_SESSION['visits'] ?? 0) + 1;

        return sprintf('Hello world! Visits this session: %d', $_SESSION['visits']);
    }
}
