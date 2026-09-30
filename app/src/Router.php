<?php

namespace App;

use App\Attribute\Route;

final class Router
{
    private array $routes = [];

    public function registerController(string $class): void
    {
        $reflection = new \ReflectionClass($class);

        foreach ($reflection->getMethods() as $method) {
            foreach ($method->getAttributes(Route::class) as $attribute) {
                /** @var Route $route */
                $route = $attribute->newInstance();

                $this->routes[] = [
                    'path' => $route->path,
                    'regex' => $this->compilePath($route->path),
                    'methods' => $route->methods,
                    'class' => $class,
                    'method' => $method->getName(),
                    'name' => $route->name,
                ];
            }
        }
    }

    /**
     * @return list<array{path: string, regex: string, methods: list<string>, class: class-string, method: string, name: ?string}>
     */
    public function routes(): array
    {
        return $this->routes;
    }

    public function dispatch(string $method, string $uri): mixed
    {
        $path = parse_url($uri, PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if (!in_array($method, $route['methods'], true)) {
                continue;
            }

            if (preg_match($route['regex'], $path, $matches)) {
                // keep only the named params
                $args = array_filter(
                    $matches,
                    'is_string',
                    ARRAY_FILTER_USE_KEY
                );

                $controller = new $route['class']();

                return $controller->{$route['method']}(...$args);
            }
        }

        http_response_code(404);

        return '404 Not Found';
    }

    private function compilePath(string $path): string
    {
        // {id} -> (?<id>[^/]+)
        $regex = preg_replace('#\{(\w+)\}#', '(?<$1>[^/]+)', $path);

        return '#^'.$regex.'$#';
    }
}
