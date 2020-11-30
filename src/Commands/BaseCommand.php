<?php


namespace ITechnoD\ModelFieldsHelper\Commands;


use Illuminate\Console\Command;
use ITechnoD\ModelFieldsHelper\DTO\FunctionNamesDTO;
use ITechnoD\ModelFieldsHelper\DTO\ModelDTO;
use ITechnoD\ModelFieldsHelper\DTO\ModelFieldDTO;
use ITechnoD\ModelFieldsHelper\Helpers\StringHelper;

abstract class BaseCommand extends Command
{
    /** @var ModelDTO */
    protected ModelDTO $modelDTO;
    /** @var string */
    protected string $className;
    /** @var string */
    protected string $classPath;
    /** @var string */
    protected string $classContent;
    /** @var FunctionNamesDTO */
    protected $functionNamesDTO;

    const TYPE_INT = 'int',
        TYPE_FLOAT = 'float',
        TYPE_STRING = 'string',
        TYPE_BOOL = 'bool',
        TYPE_DATETIME = 'datetime';

    protected $fieldTypes = [
        self::TYPE_INT,
        self::TYPE_FLOAT,
        self::TYPE_STRING,
        self::TYPE_BOOL,
        self::TYPE_DATETIME,
    ];

    /**
     * @return int
     */
    public function handle()
    {
        try {
            $this->executeCommand();
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        } finally {
            return 0;
        }
    }

    protected function setClassName(string $className): self
    {
        $this->className = $className;

        return $this;
    }

    protected function setClassPath(string $classPath): self
    {
        $this->classPath = $classPath;

        return $this;
    }

    protected function checkClassFileExists(string $classPath): bool
    {
        return file_exists($classPath);
    }

    protected function getClassFilePath(string $className): string
    {
        return dirname(__DIR__, 5) . '\\app\\Models\\' . $className . '.php';
    }

    protected function openClassFile()
    {
        $this->classContent = file_get_contents($this->classPath);
    }

    protected function createFunctionName(string $fieldName)
    {
        return StringHelper::toCamelCase($fieldName, true);
    }

    protected function createFieldAndAddToModel(
        string $fieldName,
        string $functionName,
        string $fieldType,
        bool $canBeNull,
        bool $getteExists,
        bool $setterExists
    )
    {
        $modelFieldDTO = (new ModelFieldDTO())
            ->setFieldName($fieldName)
            ->setParameterName(StringHelper::toCamelCase($fieldName))
            ->setFunctionName($functionName)
            ->setType($fieldType)
            ->setCanBeNull($canBeNull)
            ->setGetterExists($getteExists)
            ->setSetterExists($setterExists);

        if (empty($this->modelDTO)) {
            $this->modelDTO = new ModelDTO();
        }

        $this->modelDTO->addFieldDTO($modelFieldDTO);
    }

    /**
     * Метод для подготовки кода из класса к форматированию
     * путём удаления переноса строки и закрывающей фигурной скобки
     */
    protected function prepareClassContent()
    {
        // Заменяем перенос строки и закрывающую фигурную скобку
        // для того, чтобы можно было вместо них раместить новй код
        $classContent = preg_replace('/(.*)\n/msi', "$1", $this->classContent);

        //если поселдний символ закрывающаяся фигурная скобка
        //то то заменяем её
        if (strpos("}", substr(rtrim($classContent), -1)) !== false) {
            $classContent = preg_replace('/(.*)\}/msi', "$1", $classContent);
        }

        $this->classContent = $classContent;
    }

    /**
     * Получаем список функций которые уже есть
     *
     * @return FunctionNamesDTO
     */
    protected function getFunctionNames(): FunctionNamesDTO
    {
        $result = [];

        if (!is_null($this->functionNamesDTO)) {
            return $this->functionNamesDTO;
        }

        $this->functionNamesDTO = new FunctionNamesDTO();

        preg_match_all("/function get(.*)\(/", $this->classContent, $mathGetters);

        if (!empty($mathGetters) && !empty($mathGetters[1])) {
            $getters = !is_array($mathGetters[1]) ? [$mathGetters[1]] : $mathGetters[1];
            $this->functionNamesDTO->setGetters($getters);
        }

        preg_match_all("/function set(.*)\(/", $this->classContent, $mathSetters);

        if (!empty($mathSetters) && !empty($mathSetters[1])) {
            $setters = !is_array($mathSetters[1]) ? [$mathSetters[1]] : $mathSetters[1];
            $this->functionNamesDTO->setSetters($setters);
        }

        return $this->functionNamesDTO;
    }

    protected function finishAndSave()
    {
        if (is_null($this->modelDTO) || !$this->modelDTO->hasFields()) {
            $this->info('No changes, goodbye.');
            return;
        }

        $fieldsDTO = $this->modelDTO->getFieldsDTO();

        $result = '';

        /** @var ModelFieldDTO $fieldDTO */
        foreach ($fieldsDTO as $fieldDTO) {
            if (!$fieldDTO->getSetterExists()) {
                $result .= $fieldDTO->createSetter();
            }

            if (!$fieldDTO->getGetterExists()) {
                $result .= $fieldDTO->createGetter();
            }
        }

        $this->classContent .= "\n" . preg_replace('/(.*)\n/msi', '$1', $result) . "}\n";

        file_put_contents($this->classPath, $this->classContent);

        $this->info('Changes saved in: ' . $this->classPath);
    }

    protected abstract function executeCommand();
}
