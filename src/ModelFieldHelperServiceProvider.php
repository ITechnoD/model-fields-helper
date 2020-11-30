<?php


namespace ITechnoD\ModelFieldsHelper;


use Illuminate\Support\ServiceProvider;
use ITechnoD\ModelFieldsHelper\Commands\GenerateGetterSetterCommand;
use ITechnoD\ModelFieldsHelper\Commands\GenerateGetterSetterDbCommand;

class ModelFieldHelperServiceProvider extends ServiceProvider
{
    public function register()
    {
        $commands = [
            'GettersSetters' => 'models.getters-setters',
            'GettersSettersDb' => 'models.getters-setters.db',
        ];

        foreach (array_keys($commands) as $methodName) {
            $this->{"register{$methodName}Command"}();
        }

        $this->commands(array_values($commands));
    }

    private function registerGettersSettersCommand()
    {
        $this->app->singleton('models.getters-setters', function ($app) {
            return new GenerateGetterSetterCommand();
        });
    }

    private function registerGettersSettersDbCommand()
    {
        $this->app->singleton('models.getters-setters.db', function ($app) {
            return new GenerateGetterSetterDbCommand();
        });
    }

    public function boot()
    {
        //
    }
}
