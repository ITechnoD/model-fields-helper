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

<p align="center"><img src="https://downloader.disk.yandex.ru/preview/c2def88a3a3655af0b842a642845d2a277d0ad1bec1d80ada817edc299dcb4d8/5fc27273/pdh6LSGSZC3IDkEtBlCXV1gZqozbaVnCRZYuAK1UGeg8S1d9KjxiNzGAoKwC9wcHJ7wGg7771miE3_ZQA4hzaQ%3D%3D?uid=0&filename=command-screen.jpg&disposition=inline&hash=&limit=0&content_type=image%2Fjpeg&owner_uid=0&tknv=v2&size=2048x2048"></p>

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