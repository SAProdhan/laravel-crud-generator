<?php

namespace Sakeef\CrudGenerator;

class Crud {
    public static function routes() {
        $routeDir = base_path("routes/crud");

        foreach (glob("$routeDir/*.php") as $file) {
            require $file;
        }
    }
}