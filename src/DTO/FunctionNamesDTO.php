<?php


namespace ITechnoD\ModelFieldsHelper\DTO;


class FunctionNamesDTO
{
    /** @var string[] */
    protected $getters;
    /** @var string[] */
    protected $setters;

    public function setSetters(array $setters): self
    {
        $this->setters = $setters;

        return $this;
    }

    public function getSetters(): ?array
    {
        return $this->setters;
    }

    public function setGetters(array $getters): self
    {
        $this->getters = $getters;

        return $this;
    }

    public function getGetters(): ?array
    {
        return $this->getters;
    }
}
