<?php

namespace Models;

/**
 * Класс объектов Рейтинг для фильма, соержит поля:
    "id",
    "date",
    "place",
    "grade",
    "voites",
    "average_grade"
 */
class Rating extends Model
{
    /** @var Film - объект роительского фильма для рейтинга */
    private $ParentFilm;
    
    /**
     * Создает объект рейтинг, рейтинг сразу добавляется в коллекцию к фильму,
     * без фильма рейтинг создать быть не может!
     * @param Film $Film - в качетсве аргумента должен быть передан объект родительского фильма, 
     * для которого создается рейтинг
     */
    public function __construct(Film $Film)
    {
        parent::__construct([
            "id",
            "date",
            "place",
            "grade",
            "voites",
            "average_grade"
        ]);
        $this->ParentFilm = $Film;
        // в функйии Film->addRating устанавливается ID 
        // для связи с фильмом добавляемого рейтинга
        $this->ParentFilm->addRating($this);
    }
}