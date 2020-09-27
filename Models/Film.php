<?php

namespace Models;

use Helpers\ArrayHelper;

/**
 * Класс объектов Фильм, содержит поля:
    "id",
    "title",
    "year",
    "description",
    "picture",
    "category_id"
 */
class Film extends Model
{
    /** @var ArrayHelper - коллекция (массив) рейтингов за разные даты для фильма */
    private $Ratings;

    public function __construct()
    {
        parent::__construct([
            "id",
            "title",
            "year",
            "description",
            "picture",
            "category_id"
        ]);
        $this->Ratings = new ArrayHelper();
    }
    
    /**
     * Добавляет объект рейтинга к фильму
     * 
     * @param Rating $Rating
     */
    public function addRating(Rating $Rating)
    {
        $Rating->setId($this->getId());
        $this->Ratings->Value[] = $Rating;
    }
    
    /**
     * переопределяет базовую setId, устанавливает значение ID для фильма, 
     * и для всех дочерних объектов рейтинга для этого фильма
     * 
     * @param mixed $id
     * @return $this
     */
    public function setId($id)
    {
        parent::setId($id);
        
        foreach ($this->Ratings->Value as $Rating)
        {
            $Rating->setId($this->getId());
        }
        return $this;
    }
    
    public function jsonSerialize()
    {
        $ResArray = [];
        foreach ($this->data as $index => $value)
        {
            $ResArray[$index] = $value;
        }
        $ResArray["Ratings"] = $this->Ratings->Value;
        
        return $ResArray;
    }
}