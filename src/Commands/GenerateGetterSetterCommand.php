<?php


namespace ITechnoD\ModelFieldsHelper\Commands;


use ITechnoD\ModelFieldsHelper\DTO\ModelDTO;
use ITechnoD\ModelFieldsHelper\DTO\ModelFieldDTO;

class GenerateGetterSetterCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'model:generate:getters-setters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда для автоматической генерации геттеров и сеттеров';

    protected function executeCommand()
    {
        $this->info(' In which model class do you want to add getters and setters?');
        $this->info(' Example: <fg=cyan>User');
        $this->info(' Example: <fg=cyan>User\UserDetail');
        $this->info(' Note: the class will be searched from the app/Models directory');

        $className = $this->ask("Please enter model class name");
        $classPath = '';
        $modelDTO = new ModelDTO();

        while (true) {
            if (empty($className)) {
                break;
            }
            $classPath = $this->getClassFilePath($className);

            if (!$this->checkClassFileExists($classPath)) {
                $className = $this->ask("The class on the path: {$classPath} was not found.\n Please check the class name is correct and try again.");
                continue;
            }

            break;
        }

        if (empty($className)) {
            $this->info("You haven't entered anything, goodbye.");
            return 0;
        }

        $this->setClassName($className);
        $this->setClassPath($classPath);
        $this->openClassFile();
        $this->prepareClassContent();

        $functionNames = $this->getFunctionNames();

        while (true) {
            $fieldName = $this->ask("Enter field name. example id");

            if (
                empty($fieldName)
                && $this->confirm('You haven\'t entered anything, would you like to continue?')
            ) {
                continue;
            }

            if (empty($fieldName)) {
                break;
            }

            $functionName = $this->createFunctionName($fieldName);

            if (!empty($functionNames->getGetters()) && in_array($functionName, $functionNames->getGetters())) {
                $getterExists = true;
            } else {
                $getterExists = false;
            }

            if (!empty($functionNames->getSetters()) && in_array($functionName, $functionNames->getSetters())) {
                $setterExists = true;
            } else {
                $setterExists = false;
            }

            if ($getterExists && $setterExists) {
                $this->info('Getter and Setter have already been added');
                $this->info('Choose another field');
                continue;
            }

            if ($getterExists) {
                $this->info('Getter have already been added');
                $this->info('Only the setter will be added');
            }

            if ($setterExists) {
                $this->info('Setter have already been added');
                $this->info('Only the getter will be added');
            }

            $fieldType = $this->choice("Enter field type", $this->fieldTypes, self::TYPE_STRING);

            $canNull = $this->confirm('Can a value be nullable?');

            $this->createFieldAndAddToModel($fieldName, $functionName, $fieldType, $canNull, $getterExists, $setterExists);

            if ($this->confirm('Do you wish continue?', true)) {
                continue;
            }

            break;
        }

        $this->finishAndSave();
    }
}
