<?php


namespace ITechnoD\ModelFieldsHelper;


use Illuminate\Support\ServiceProvider;
use ITechnoD\ModelFieldsHelper\Commands\GenerateGetterSetterCommand;

class ModelFieldHelperServiceProvider extends ServiceProvider
{
    public function register()
    {
        $commands = [
            'GettersSetters' => 'models.getters-setters'
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

    public function boot()
    {
        //
    }
}