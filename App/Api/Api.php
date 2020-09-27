<?php

namespace App\Api;

use DataBase\DB;
use Helpers\ArrayHelper;
use Models\Film;
use Models\Rating;

final class Api
{
    /** @var DB - объект базы данных */
    private $DB;
    /** @var \mysqli */
    private $MySQL;
    
    public function __construct(DB $db)
    {
        $this->DB = $db;
        $this->MySQL = $db->getSQLInstance();
        
        header("Content-Type: application/json; charset=utf-8");
    }
    
    /**
     * возвращает декодированные в массив (или в объекты) данные JSON,
     * поступившие из запроса
     * 
     * @param bool $Assoc
     * @return mixed
     */
    private function getJsonRequest($Assoc = null)
    {
        $json = file_get_contents('php://input');
        return json_decode($json, $Assoc);
    }
    /**
     * Если в запросе $RequestData есть поле "date",
     * тогда вернется оно, иначе текущая дата в формате ГГГГ-ММ-ДД - формат SQL
     * @param array $RequestData
     * @return date
     */
    private function getDateFromRequest($RequestData)
    {
        if(isset($RequestData["date"]))
        {
            return $RequestData["date"];
        }
        return date("Y-m-d");
    }

    /**
     * выводит все категории в видк JSON-строки
     */
    public function actionAllCategories()
    {
        $Query = "SELECT * FROM `categories`";
        $Res = $this->MySQL->query($Query);
        if(!$Res || !$Res->num_rows) { exit; }
        
        $Categories = new ArrayHelper();
        
        while ($Category = $Res->fetch_array(MYSQLI_ASSOC))
        {
            $Categories->Value[] = $Category;
        }

        $this->responseJson($Categories);
    }

    /**
     * выводит все фильмы в видк JSON-строки
     */
    public function actionAllFilms()
    {
        $Query = "SELECT * FROM `films`";
        $Films = $this->getFilmsCollection($Query);
        $RequestData = $this->getJsonRequest(true);
        if(isset($RequestData["withrating"]) && $RequestData["withrating"])
        {
            $Date = $this->getDateFromRequest($RequestData);
            $this->getFilmsRatings($Films, $Date);
        }
        $this->responseJson($Films);
    }
    
    /**
     * выводит все фильмы из категории в видк JSON-строки
     */
    public function actionFilmsFromCategory()
    {
        $RequestData = $this->getJsonRequest(true);
        if(!isset($RequestData["categories"])) { exit; }

        $Categories = [];
        foreach( $RequestData["categories"] as $category_id => $Checked )
        {
            if($Checked)
            {
                $Categories[] = "'" . addcslashes($category_id, "'") . "'";
            }
        }
        $Query = "SELECT * FROM `films` WHERE `category_id` IN (" . implode($Categories, ",") . ")";
        $Films = $this->getFilmsCollection($Query);

        if(isset($RequestData["withrating"]) && $RequestData["withrating"])
        {
            $Date = $this->getDateFromRequest($RequestData);
            $this->getFilmsRatings($Films, $Date);
        }
        $this->responseJson($Films);
    }
    
    public function actionFilmsWithRatingFromCategory()
    {
        $RequestData = $this->getJsonRequest(true);
        if(!isset($RequestData["categories"])) { exit; }

        $Categories = [];
        foreach( $RequestData["categories"] as $category_id => $Checked )
        {
            if($Checked)
            {
                $Categories[] = "'" . addcslashes($category_id, "'") . "'";
            }
        }
        $Date = $this->getDateFromRequest($RequestData);
        $Query = "SELECT * FROM `films`";
        if(isset($RequestData["withrating"]) && $RequestData["withrating"])
        {
            $Query .= " JOIN `ratings` ON `ratings`.`id`=`films`.`id`";
        }
        $Query .= " WHERE `category_id` IN (" . implode($Categories, ",") . ")";
        if(isset($RequestData["withrating"]) && $RequestData["withrating"])
        {
            $Query .= " AND `ratings`.`date`='" . addcslashes($Date, "'") . "'";
        }
        if( isset($RequestData["sortby"]) && isset($RequestData["sortby"]["field"]) )
        {
            $Query .= " ORDER BY `" . addcslashes($RequestData["sortby"]["field"], "`") . "`";
            if( isset($RequestData["sortby"]["direction"]) )
            {
                if( $RequestData["sortby"]["direction"] == 1 )
                {
                    $Direction = "ASC";
                }
                else if( $RequestData["sortby"]["direction"] == -1 )
                {
                    $Direction = "DESC";
                }
                $Query .= " " . $Direction;
            }
        }
        $Films = $this->getFilmsWithRatingFromCategory($Query);
        $this->responseJson($Films);
    }

    /**
     * при простом запросе 1-фильм 1-рейтинг (га 1 дату) используется JOIN с 1-й результирующей строкой,
     * в этом случае вызывается данная функция, она формирует для каждого фильма 
     * только один объект рейтинга на указанную дату
     * @param string $Query
     * @return ArrayHelper
     */
    private function getFilmsWithRatingFromCategory($Query)
    {
        $Res = $this->MySQL->query($Query);
        if(!$Res || !$Res->num_rows) { return null; }
        
        $Films = new ArrayHelper();
        while( $Row = $Res->fetch_assoc() )
        {
            $Film = new Film();
            $Film->initFromArray($Row);
            
            $Rating = new Rating($Film);
            $Rating->initFromArray($Row);
            
            $Films->Value[] = $Film;
        }
        return $Films;
    }


    /**
     * получает рейтинги фильмов из БД
     * и раcпрееляет их по объектам для каждого фильма из $Films
     * @param ArrayHelper $Films - коллекция фильмов, для которой выбираются рейтинги
     * @param date $Date - дата, если не задана, то вернутся все рейтинги для коллекции фильмов
     */
    private function getFilmsRatings(ArrayHelper $Films, $Date = null)
    {
        $IdArr = [];
        foreach($Films->Value as $Film)
        {
            $IdArr[] = $Film->getId();
        }
        $Query = "SELECT * FROM `ratings` WHERE `id` IN (" . implode($IdArr, ",") . ")";
        if($Date) { $Query .= " AND `date`='" . addcslashes($Date, "'") . "'"; }

        $Res = $this->MySQL->query($Query);
        if(!$Res || !$Res->num_rows) { return; }
        
        while( $Row = $Res->fetch_assoc() )
        {
            $Rating = null;
            $CurrentRatingId = $Row[Rating::getIdFieldName()];

            // ищем фильм из переданного ассива с нужным ID
            foreach($Films->Value as $Film)
            {
                // в данной реализации фильм и рейтинг связаны по ID
                if( $Film->getId() == $CurrentRatingId)
                {
                    $Rating = new Rating($Film);
                    $Rating->initFromArray($Row);
                }
            }
        }

    }

    private function getFilmsCollection($Query)
    {
        $Res = $this->MySQL->query($Query);
        if(!$Res || !$Res->num_rows) { return null; }
        
        $Films = new ArrayHelper();

        while( $Row = $Res->fetch_assoc() )
        {
            $Films->Value[] = (new Film())->initFromArray($Row);
        }
        return $Films;
    }
    
    private function responseJson(ArrayHelper $Data = null)
    {
        if($Data)
        {
            echo json_encode($Data->Value);
        }
        exit;        
    }
}