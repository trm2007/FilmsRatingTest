<?php

namespace Storages;

class FilmStorage extends Storage
{
    /**
     * 
     * @param \DataBase\DB $db
     */
    public function __construct(\DataBase\DB $db)
    {
        parent::__construct($db, \Models\Film::class, "films");
    }

}