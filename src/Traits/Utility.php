<?php

namespace Sakeef\CrudGenerator\Traits;

use Illuminate\Support\Str;

trait Utility{
    protected function getStubContent($path)
    {
        return file_get_contents(__DIR__ . '/../stubs/' . $path . '.stub');
    }

    protected function replaceTokens($content,$model)
    {
        $map = [
            '$CONTROLLER_NAME$' => Str::studly("{$model}Controller"),
            '$MIGRATION_NAME$' => Str::studly("Create{$model}Table")
        ];

        return strtr($content, $map);
    }
}