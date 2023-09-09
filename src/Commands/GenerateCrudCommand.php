<?php

namespace Sakeef\CrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Sakeef\CrudGenerator\Traits\Utility;

class GenerateCrudCommand extends Command implements PromptsForMissingInput
{
    use Utility;

    protected $indentation = '    ';
    protected $signature = 'crud:generate {model} {--attributes= : Enter filelds with type like title:strin,description:text}';
    protected $description = 'Generate CRUD files, migration, views, requests and migrate for a model';

    public function handle()
    {
        $model = $this->argument('model');
        $options = $this->option('attributes');
        $fields = $this->getFields($options);

        // Generate the model
        Artisan::call('make:model', ['name' => $model]);

        // Generate the migration file
        $this->generateMigration($model, $fields);

        // Generate the controller
        Artisan::call('make:controller', ['name' => "{$model}Controller", '--model'=>$model, '--resource'=>true, '--requests'=>true],);

        // Generate the views
        $this->generateViews($model);

        // Generate a separate route file
        $this->generateRouteFile($model);

        // Migrate the database
        $this->migrateDatabase();

        $this->info("CRUD files, migration, views, and route file for $model generated successfully.");

    }

    protected function promptForMissingArgumentsUsing()
    {
        return [
            'model' => 'Please enter the Model name',
        ];
    }

    protected function validateFields($fields){
        $invalidTypes = array_diff(array_column($fields, 'type'),['string','text','boolean','decimal','float','integer']);
        
        if(count($invalidTypes) > 0){
            $this->error("Invalid attribute(s) type: ".join(",",$invalidTypes).".");
            exit;
        }
        return true;
    }

    protected function getFields($options)
    {
        if(count(explode(",",$options)) < 1 || strlen($options) < 1){
            $this->error("Invalid/missing attribute(s) and type(s)");
            exit;    
        }
        $fields = array_map(function($option){ 
            $x = explode(":",$option);
            return ["key"=>$x[0],"type"=>$x[1]];
        } , explode(",",$options));
        $this->validateFields($fields);
        return $fields;
    }

    protected function generateMigration($model,$fields)
    {

        $name = Str::snake(Str::plural($model));

        $migrationPath = base_path() . '/database/migrations/' . date('Y_m_d_His') . '_create_' . $name . '_table.php';

        $content = $this->getStubContent("migration.php");
        $content = $this->replaceTokens($content, $model);

        $migrationContent = '';
        $migrationContent .= str_repeat($this->indentation, 3) . '$table->id();' . PHP_EOL;
        foreach($fields as $field){
            $migrationContent .= str_repeat($this->indentation, 3) . '$table->'.$field['type'].'(\''.$field['key'].'\');' . PHP_EOL;
        }
        $migrationContent .= str_repeat($this->indentation, 3) . '$table->softDeletes();' . PHP_EOL;
        $migrationContent .= str_repeat($this->indentation, 3) . '$table->timestamps();' . PHP_EOL;

        $content = strtr($content, [
            '$MIGRATION_CONTENT$' => $migrationContent
        ]);

        file_put_contents("{$migrationPath}", $content);

        $this->line("Created Migration: {$migrationPath}");
    }

    protected function generateRouteFile($model)
    {
        $routeFileContents = "<?php

        use Illuminate\Support\Facades\Route;

        // Define your CRUD routes for $model here
        Route::resource('".strtolower($model)."', '{$model}Controller');
        ";
        $routeFilePath = base_path("routes/".strtolower($model).".php");

        // Create the route file
        File::put($routeFilePath, $routeFileContents);

        $routeFile = base_path('routes/web.php');
        $routeIncludeStatement = "\Sakeef\CrudGenerator\Crud::routes();";

        // Check if the include statement already exists
        $contents = File::get($routeFile);
        if (strpos($contents, $routeIncludeStatement) === false) {
            // If not, append it to the end of the file
            File::append($routeFile, PHP_EOL . $routeIncludeStatement);
        }
    }

    protected function generateViews($model){
        $viewsPath = resource_path("views/{$model}");

        // Create a directory for the model's views if it doesn't exist
        if (!File::exists($viewsPath)) {
            File::makeDirectory($viewsPath);
        }

        // Generate view files (you can customize this part based on your needs)
        File::put("{$viewsPath}/index.blade.php", '<!-- Your index view content here -->');
        File::put("{$viewsPath}/create.blade.php", '<!-- Your create view content here -->');
        File::put("{$viewsPath}/edit.blade.php", '<!-- Your edit view content here -->');
        File::put("{$viewsPath}/show.blade.php", '<!-- Your show view content here -->');
    }


    protected function migrateDatabase()
    {
        Artisan::call('migrate');
    }
}
