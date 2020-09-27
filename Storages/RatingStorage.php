<?php

namespace Storages;

use DataBase\DB;
use Models\Film;
use Models\Rating;
use Storages\Exceptions\StorageException;

class RatingStorage extends Storage
{
    /** @var Film - объект текущего фильма, 
     * для которого создаются, получаются и сохраняются рейтинги
     */
    protected $CurrentFilm = null;

    /**
     * 
     * @param DB $db
     */
    public function __construct(DB $db)
    {
        parent::__construct($db, Rating::class, "ratings");
    }

    public function getModel()
    {
        if(!$this->CurrentFilm)
        {
            throw new StorageException(0, "Не установлен объект родительского фильма в RatingStorage");
        }
        
        return new Rating($this->CurrentFilm);
    }
    
    /**
     * @return Film
     */
    public function getCurrentFilm(): Film
    {
        return $this->CurrentFilm;
    }

    /**
     * @param Film $CurrentFilm
     * @return $this
     */
    public function setCurrentFilm(Film $CurrentFilm)
    {
        $this->CurrentFilm = $CurrentFilm;
        return $this;
    }

}