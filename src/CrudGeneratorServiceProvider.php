<?php

namespace Sakeef\CrudGenerator;

use Illuminate\Support\ServiceProvider;
use Sakeef\CrudGenerator\Commands\GenerateCrudCommand;

class CrudGeneratorServiceProvider extends ServiceProvider
{
    
    public function boot()
    {
    }

    public function register()
    {
        $this->commands(GenerateCrudCommand::class);
    }
}
