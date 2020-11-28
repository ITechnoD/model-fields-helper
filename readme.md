## Установка

``composer require itechnod/model-fields-helper``

Прописать в ``config/app.php``
```
'providers' => [
    ...
    \ITechnoD\ModelFieldsHelper\ModelFieldHelperServiceProvider,
]
```

## Model auto create getters and setters
Команда предназначенная для автоматической генерации геттеров и сеттеров

Путём ввода значений на задаваемые вопросы

Для работы введите команду ``php artisan model:generate:getters-setters``

<p align="center"><img src=""></p>

Результат выполнения: 
```php
    /**
     * Auto generate getter for id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Auto generate setter for id
     *
     * @param int $id
     * @return $this
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Auto generate getter for user_name
     *
     * @return string
     */
    public function getUserName(): string
    {
        return $this->user_name;
    }

    /**
     * Auto generate setter for user_name
     *
     * @param string $userName
     * @return $this
     */
    public function setUserName(string $userName): self
    {
        $this->user_name = $userName;
        return $this;
    }
```