<?php

namespace App;

final class Application
{
    public static function router(): Router
    {
        $router = new Router;

        foreach (self::controllerClasses() as $controllerClass) {
            $router->registerController($controllerClass);
        }

        return $router;
    }

    /**
     * @return list<class-string>
     */
    public static function controllerClasses(): array
    {
        $classes = [];
        $controllerPath = __DIR__.'/Controller';

        foreach (glob($controllerPath.'/*.php') ?: [] as $file) {
            $class = __NAMESPACE__.'\Controller\\'.basename($file, '.php');

            if (class_exists($class)) {
                $classes[] = $class;
            }
        }

        sort($classes, SORT_STRING);

        return $classes;
    }
}
