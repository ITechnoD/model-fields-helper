<?php


namespace ITechnoD\ModelFieldsHelper\DTO;


class ModelDTO
{
    /** @var ModelFieldDTO[] */
    private array $fieldsDTO;

    public function addFieldDTO(ModelFieldDTO $fieldDTO) : self
    {
        $this->fieldsDTO[] = $fieldDTO;

        return $this;
    }

    public function getFieldsDTO() : array
    {
        return  $this->fieldsDTO;
    }

    public function hasFields() : bool
    {
        return  !empty($this->fieldsDTO);
    }
}
