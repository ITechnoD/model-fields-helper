<?php

namespace ITechnoD\ModelFieldsHelper\DTO;

class ModelFieldDTO
{
    /** @var string */
    private string $fieldName;
    /** @var string */
    private string $parameterName;
    /** @var string */
    private string $functionName;
    /** @var string */
    private string $type;
    /** @var bool */
    private bool $canBeNull;
    /** @var bool */
    private bool $getterExists;
    /** @var bool */
    private bool $setterExists;

    const TYPE_INT = 'int',
        TYPE_FLOAT = 'float',
        TYPE_STRING = 'string',
        TYPE_BOOL = 'bool',
        TYPE_DATETIME = 'datetime';

    /**
     * Задаём название поля
     *
     * @param string $field
     * @return $this
     */
    public function setFieldName(string $fieldName): self
    {
        $this->fieldName = $fieldName;

        return $this;
    }

    /**
     * Задаём название параметра
     *
     * @param string $parameterName
     * @return $this
     */
    public function setParameterName(string $parameterName): self
    {
        $this->parameterName = $parameterName;

        return $this;
    }

    /**
     * Получаем название параметра
     *
     * @return string
     */
    public function getParameterName(): string
    {
        return $this->parameterName;
    }

    /**
     * Получаем название поля
     *
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * Задаём имя функции (без get или set)
     *
     * @param string $functionName
     * @return $this
     */
    public function setFunctionName(string $functionName): self
    {
        $this->functionName = $functionName;

        return $this;
    }

    /**
     * Получаем имя функции (без get или set)
     *
     * @return string
     */
    public function getFunctionName(): string
    {
        return $this->functionName;
    }

    /**
     * Задаём тип поля
     *
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Метод получения типа поля
     * 
     * @return string
     */
    public function getType() : string
    {
        return  $this->type;
    }

    /**
     * Задаём может ли поле быть пустым
     *
     * @param bool $canBeNull
     * @return $this
     */
    public function setCanBeNull(bool $canBeNull): self
    {
        $this->canBeNull = $canBeNull;

        return $this;
    }

    /**
     * Получаем может ли поле быть пустым
     *
     * @return bool
     */
    public function getCanBeNull(): bool
    {
        return $this->canBeNull;
    }

    /**
     * Задаём есть ли уже геттер
     *
     * @param bool $getterExists
     * @return $this
     */
    public function setGetterExists(bool $getterExists): self
    {
        $this->getterExists = $getterExists;

        return $this;
    }

    /**
     * Есть ли уже геттер
     *
     * @return bool
     */
    public function getGetterExists(): bool
    {
        return $this->getterExists;
    }

    /**
     * Задаём есть ли уже сеттер
     *
     * @param bool $setterExists
     * @return $this
     */
    public function setSetterExists(bool $setterExists): self
    {
        $this->setterExists = $setterExists;

        return $this;
    }

    /**
     * Есть ли уже сеттер
     *
     * @return bool
     */
    public function getSetterExists(): bool
    {
        return $this->setterExists;
    }

    /**
     * Получаем тим
     * либо специальный
     *
     * @return string|null
     */
    private function getTypeAsSpesial(): ?string
    {
        if ($this->type == self::TYPE_DATETIME) {
            return '\DateTime';
        }

        return $this->type;
    }

    /**
     * @param string $type
     * @param bool $canNull
     * @return string
     */
    private function getTypeForParam(): string
    {
        return $this->type ? (($this->canBeNull ? '?' : '') . $this->getTypeAsSpesial() . ' ') : '';
    }

    /**
     * @param string $type
     * @param bool $canNull
     * @return string
     */
    private function getTypeForAnnotationParam(): string
    {
        return $this->type ? ($this->getTypeAsSpesial() . ($this->canBeNull ? '|null' : '') . ' ') : '';
    }

    /**
     * @param string $type
     * @param bool $canNull
     * @return string
     */
    private function getTypeForAnnotationReturn()
    {
        return ($this->getTypeAsSpesial() ?? 'mixed') . ($this->canBeNull ? '|null' : '');
    }

    /**
     * @param string $type
     * @param bool $canNull
     * @return mixed
     */
    private function getTypeForReturn()
    {
        return $this->type ? (': ' . ($this->canBeNull ? '?' : '') . $this->getTypeAsSpesial()) : '';
    }

    /**
     * Создаём сеттер
     *
     * @return string
     */
    public function createSetter(): string
    {
        return
            "    /**\n" .
            "     * Auto generate setter for {$this->fieldName}\n" .
            "     *\n" .
            "     * @param {$this->getTypeForAnnotationParam()}\${$this->parameterName}\n" .
            "     * @return \$this\n" .
            "     */\n" .
            "    public function set{$this->functionName}({$this->getTypeForParam()}\${$this->parameterName}): self\n" .
            "    {\n" .
            "        \$this->{$this->fieldName} = \${$this->parameterName};\n" .
            "        return \$this;\n" .
            "    }\n\n";
    }

    /**
     * Создаём геттер
     *
     * @return string
     */
    public function createGetter(): string
    {
        return
            "    /**\n" .
            "     * Auto generate getter for {$this->fieldName}\n" .
            "     *\n" .
            "     * @return {$this->getTypeForAnnotationReturn()}\n" .
            "     */\n" .
            "    public function get{$this->functionName}(){$this->getTypeForReturn()}\n" .
            "    {\n" .
            "        return \$this->{$this->fieldName};\n" .
            "    }\n\n";
    }
}
