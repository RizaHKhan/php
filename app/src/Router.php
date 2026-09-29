<?php

namespace App;

final class Router
{
    public function dispatch(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        return match ($path) {
            '/' => (new Greeter())->message(),
            '/about' => 'About this PHP app',
            default => $this->notFound($path),
        };
    }

    private function notFound(string $path): string
    {
        http_response_code(404);

        return sprintf('Page not found: %s', htmlspecialchars($path, ENT_QUOTES, 'UTF-8'));
    }
}
