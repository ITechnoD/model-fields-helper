<?php


namespace ITechnoD\ModelFieldsHelper\Commands;


use Illuminate\Console\Command;

class GenerateGetterSetterCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:getters:setters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда для автоматической генерации геттеров и сеттеров';

    const TYPE_INT = 'int',
        TYPE_STRING = 'string',
        TYPE_BOOL = 'bool',
        TYPE_DATETIME = 'datetime';

    private $fieldTypes = [
        self::TYPE_INT,
        self::TYPE_STRING,
        self::TYPE_BOOL,
        self::TYPE_DATETIME,
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $className = $this->ask("In which model class do you want to add getters and setters? \n Example: User \n Example: User\UserDetail \n Note: the class will be searched from the app /Models derictory");
        $classPath = '';

        while (true) {
            if (empty($className)) {
                break;
            }

            $classPath = dirname(__DIR__, 2) . '\\Models\\' . $className . '.php';

            if (!file_exists($classPath)) {
                $className = $this->ask("The class on the path: {$classPath} was not found.\n Please check the class name is correct and try again.");
                continue;
            }

            break;
        }

        if (empty($className)) {
            $this->info("You haven't entered anything, goodbye.");
            return 0;
        }

        // Получаем класс
        $classContent = file_get_contents($classPath);

        // Подготавливаем класс удаляя перенос строки
        // и последнюю фигурную скобку
        $classContent = $this->prepareClassContent($classContent);

        $result = '';

        $functionNames = $this->getFunctionNames($classContent);

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

            $functionName = $this->toCamelCase($fieldName, true);

            if (!empty($functionNames['getters']) && in_array($functionName, $functionNames['getters'])) {
                $getterExists = true;
            } else {
                $getterExists = false;
            }

            if (!empty($functionNames['setters']) && in_array($functionName, $functionNames['setters'])) {
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

            $parameterName = $this->toCamelCase($fieldName);

            if (!$getterExists) {
                $result .= $this->addGetter($fieldName, $fieldType, $functionName, $canNull);
            }

            if (!$setterExists) {
                $result .= $this->addSetter($fieldName, $fieldType, $functionName, $parameterName, $canNull);
            }

            if ($this->confirm('Do you wish continue?', true)) {
                continue;
            }

            break;
        }

        if (!empty($result)) {
            $classContent .= "\n" . preg_replace('/(.*)\n/msi', '$1', $result) . "}\n";

            file_put_contents($classPath, $classContent);

            $this->info('Changes saved in: ' . $classPath);
        } else {
            $this->info('No change, goodbye.');
        }

        return 0;
    }

    /**
     * @param string $fieldName
     * @param string $fieldType
     * @param string $functionName
     * @param string $parameterName
     * @param bool $canNull
     * @return string
     */
    private function addSetter(
        string $fieldName,
        string $fieldType,
        string $functionName,
        string $parameterName,
        bool $canNull
    ): string
    {
        return
            "    /**\n" .
            "     * Auto generate setter for {$fieldName}\n" .
            "     *\n" .
            "     * @param {$this->getTypeForAnnotationParam($fieldType, $canNull)}\${$parameterName}\n" .
            "     * @return \$this\n" .
            "     */\n" .
            "    public function set{$functionName}({$this->getTypeForParam($fieldType, $canNull)}\${$parameterName}): self\n" .
            "    {\n" .
            "        \$this->{$fieldName} = \${$parameterName};\n" .
            "        return \$this;\n" .
            "    }\n\n";
    }

    /**
     * @param string $fieldName
     * @param string $fieldType
     * @param string $functionName
     * @param bool $canNull
     * @return string
     */
    private function addGetter(
        string $fieldName,
        string $fieldType,
        string $functionName,
        bool $canNull
    ): string
    {
        return
            "    /**\n" .
            "     * Auto generate getter for {$fieldName}\n" .
            "     *\n" .
            "     * @return {$this->getTypeForAnnotationReturn($fieldType, $canNull)}\n" .
            "     */\n" .
            "    public function get{$functionName}(){$this->getTypeForReturn($fieldType, $canNull)}\n" .
            "    {\n" .
            "        return \$this->{$fieldName};\n" .
            "    }\n\n";
    }

    /**
     * @param string|null $type
     * @return string|null
     */
    private function getType(?string $type): ?string
    {
        if ($type == self::TYPE_DATETIME) {
            return '\DateTime';
        }

        return $type;
    }

    /**
     * @param string $type
     * @param bool $canNull
     * @return string
     */
    private function getTypeForParam(string $type, bool $canNull): string
    {
        return $type ? (($canNull ? '?' : '') . $this->getType($type) . ' ') : '';
    }

    /**
     * @param string $type
     * @param bool $canNull
     * @return string
     */
    private function getTypeForAnnotationParam(string $type, bool $canNull): string
    {
        return $type ? ($this->getType($type) . ($canNull ? '|null' : '') . ' ') : '';
    }

    /**
     * @param string $type
     * @param bool $canNull
     * @return string
     */
    private function getTypeForAnnotationReturn(string $type, bool $canNull)
    {
        return ($this->getType($type) ?? 'mixed') . ($canNull ? '|null' : '');
    }

    /**
     * @param string $type
     * @param bool $canNull
     * @return mixed
     */
    private function getTypeForReturn(string $type, bool $canNull)
    {
        return $type ? (': ' . ($canNull ? '?' : '') . $this->getType($type)) : '';
    }

    /**
     * Метод для подготовки кода из класса к форматированию
     * путём удаления переноса строки и закрывающей фигурной скобки
     *
     * @param string $classContent
     * @return string
     */
    private function prepareClassContent(string $classContent): string
    {
        // Заменяем перенос строки и закрывающую фигурную скобку
        // для того, чтобы можно было вместо них раместить новй код
        $classContent = preg_replace('/(.*)\n/msi', "$1", $classContent);

        //если поселдний символ закрывающаяся фигурная скобка
        //то то заменяем её
        if (strpos("}", substr(rtrim($classContent), -1)) !== false) {
            $classContent = preg_replace('/(.*)\}/msi', "$1", $classContent);
        }

        return $classContent;
    }

    /**
     * @param $underscored
     * @param false $capitalizeFirst
     * @return string|string[]|null
     */
    private function toCamelCase($underscored, $capitalizeFirst = false)
    {
        $res = preg_replace_callback("|.*(_.).*|", "self::uppercase", $underscored);
        $res = preg_replace_callback("|.*(_.).*|", "self::uppercase", $res);

        if ($capitalizeFirst) {
            $res = strToUpper(substr($res, 0, 1)) . substr($res, 1);
        }

        return $res;
    }

    /**
     * @param $matches
     * @return mixed
     */
    private function uppercase($matches)
    {
        for ($i = 1; $i < count($matches); $i++) {
            $matches[0] = str_replace($matches[$i], strtoupper(substr($matches[$i], 1)), $matches[0]);
        }

        return $matches[0];
    }

    /**
     * Получаем список функций которые уже есть
     *
     * @param string $classContent
     * @return array
     */
    private function getFunctionNames(string $classContent): array
    {
        $result = [];

        preg_match("/function get(.*)\(/", $classContent, $mathGetters);

        if (!empty($mathGetters) && !empty($mathGetters[1])) {
            $result['getters'][] = $mathGetters[1];
        }

        preg_match("/function set(.*)\(/", $classContent, $mathSetters);

        if (!empty($mathSetters) && !empty($mathSetters[1])) {
            $result['setters'][] = $mathSetters[1];
        }

        return $result;
    }
}