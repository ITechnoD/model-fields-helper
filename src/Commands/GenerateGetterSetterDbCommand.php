<?php


namespace ITechnoD\ModelFieldsHelper\Commands;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use ITechnoD\ModelFieldsHelper\DTO\ModelDTO;
use ITechnoD\ModelFieldsHelper\DTO\ModelFieldDTO;

class GenerateGetterSetterDbCommand extends BaseCommand
{
    private $types = [
        self::TYPE_INT => [
            'tinyint',
            'smallint',
            'mediumint',
            'int',
        ],
        self::TYPE_FLOAT => [
            'bigint',
            'float',
            'double',
            'decimal',
        ],
        self::TYPE_STRING => [
            'text',
            'tinytext',
            'mediumtext',
            'longtext',
            'varchar',
            'nvarchar',
            'char',
        ],
        self::TYPE_DATETIME => [
            'date',
            'datetime',
            'timestamp',
            'time',
            'year',
        ],
    ];

    const DRIVER_SQLSRV = 'sqlsrv',
        DRIVER_MYSQL = 'mysql';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'model:generate:getters-setters:db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда для автоматической генерации геттеров и сеттеров из полей в базе';

    protected function executeCommand()
    {
        $this->info(' In which model class do you want to add getters and setters?');
        $this->info(' Example: <fg=cyan>User');
        $this->info(' Example: <fg=cyan>User\UserDetail');
        $this->info(' Note: the class will be searched from the app/Models directory');
        $classPath = '';

        $className = $this->ask("Please enter model class name");

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

        $classNameReplaced = preg_replace("/\//", "\\", $this->className);

        $modelClass = "\\App\\Models\\$classNameReplaced";
        /** @var Model $modelClass */
        $modelClass = new $modelClass;

        $dbName = $modelClass->getConnection()->getDatabaseName();

        switch ($modelClass->getConnection()->getDriverName()) {
            case Self::DRIVER_SQLSRV:
                $columns = DB::connection($modelClass->getConnectionName())
                    ->select(DB::raw(
                        "SELECT COLUMN_NAME AS 'Field', DATA_TYPE AS 'Type', IS_NULLABLE AS 'Null'
                        FROM information_schema.columns WHERE TABLE_NAME = '{$modelClass->getTable()}'
                        AND TABLE_CATALOG = '{$dbName}'"));
                break;
            case self::DRIVER_MYSQL:
                $columns = DB::connection($modelClass->getConnectionName())
                    ->select(DB::raw(
                        "SELECT COLUMN_NAME AS 'Field', DATA_TYPE AS 'Type', IS_NULLABLE AS 'Null'
                        FROM information_schema.columns WHERE TABLE_NAME = '{$modelClass->getTable()}'
                        AND TABLE_SCHEMA = '{$dbName}'"));
                break;
            default:
                throw new \Exception('Driver not supported');
        }

        foreach ($columns as $column) {
            $fieldType = '';

            foreach ($this->types as $key => $values) {
                $implodeValues = implode('|', $values);

                if (preg_match('/^(' . $implodeValues . ')/', $column->Type)) {
                    $fieldType = $key;
                    break;
                }
            }

            $functionName = $this->createFunctionName($column->Field);

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
                $this->alert('Field: ' . $column->Field);
                $this->newLine();
                continue;
            }

            if ($getterExists) {
                $this->info('Getter have already been added');
                $this->alert('Field: ' . $column->Field);
                $this->info('Only the setter will be added');
                $this->newLine();
            }

            if ($setterExists) {
                $this->info('Setter have already been added');
                $this->alert('Field: ' . $column->Field);
                $this->info('Only the getter will be added');
                $this->newLine();
            }

            $this->createFieldAndAddToModel(
                $column->Field,
                $functionName,
                $fieldType,
                $column->Null == 'YES',
                $getterExists,
                $setterExists
            );
        }

        while (true) {
            $fieldsDTO = $this->modelDTO->getFieldsDTO();

            $fields = ['I choose nothing'];
            $line = '';

            foreach ($fieldsDTO as $index => $fieldDTO) {
                $fields[] = $fieldDTO->getFieldName();

                $line .= '[' . $fieldDTO->getType() . '] ' .
                    $fieldDTO->getFieldName() .
                    ($fieldDTO->getCanBeNull() ? ' NULL' : '') .
                    ($index < count($fieldsDTO) - 1 ? ',' : '');
            }

            $this->info('The following fields were obtained from the table');
            $this->info($line);
            $choiceColumn = $this->choice('Which field do you want to modify?', $fields, 0);

            if ($choiceColumn == 'I choose nothing') {
                break;
            }

            $needFieldDTO = null;

            /** @var ModelFieldDTO $fieldDTO */
            foreach ($fieldsDTO as $fieldDTO) {
                if ($fieldDTO->getFieldName() == $choiceColumn) {
                    $needFieldDTO = $fieldDTO;
                    break;
                }
            }

            while (true) {
                $choiceVariant = $this->choice('What do you want to change?', ['type', 'can be null', 'nothing'], 'nothing');

                if ($choiceVariant == 'nothing') {
                    break;
                }

                if ($choiceVariant == 'type') {
                    $newType = $this->choice('What type do you want to change to?', $this->fieldTypes, self::TYPE_STRING);

                    $needFieldDTO->setType($newType);
                }

                if ($choiceVariant == 'can be null') {
                    $canBeNull = $this->confirm('Can a value be nullable?');

                    $needFieldDTO->setCanBeNull($canBeNull);
                }
            }
        }

        $this->finishAndSave();
    }
}
