<?php


namespace ITechnoD\ModelFieldsHelper;


use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Application as Artisan;
use ITechnoD\ModelFieldsHelper\Commands\GenerateGetterSetterCommand;

class ModelFieldHelperServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('models.getters-setters', function ($app) {
            return new GenerateGetterSetterCommand();
        });

        $commands = ['models.getters-setters'];

        Artisan::starting(function ($artisan) use ($commands) {
            $artisan->resolveCommands($commands);
        });
    }

    public function boot()
    {
        //
    }
}