<?php

namespace App;

use DataBase\DB;
use Helpers\ArrayHelper;
use Helpers\CURLHelper;
use Helpers\ImageHelper;
use Helpers\ParseHelper;
use Helpers\URLTemplateHelper;
use Models\Film;
use Models\Rating;
use Storages\Exceptions\StorageException;
use Storages\FilmStorage;
use Storages\RatingStorage;

final class Cron
{
    /** кодировка приложения по умолчанию */
    const DEFAULT_CODEPAGE = "UTF-8";

    const SAVE_TO_DB = 2^0;
    const SAVE_TO_FILE = 2^1;

    /** @var DB экземпляр объекта DataBase */
    protected $db = null;
    protected $Curl = null;
    protected $Parser = null;
    
    /** @var array */
    protected $CURLHeaders = [];
    /** @var array */
    protected $ParseOptions = [];
    /** @var string */
    protected $TmpFileName = "";
    /** @var string - кодировка страницы сайта и БД */
    protected $CurrentCodePage = self::DEFAULT_CODEPAGE;
    /** @var string - папка для сохранения картинок, относительно корня сайта */
    public $ImagesFolder = "./";
    /** @var string - префикс к адресу изображения, полученного через парсинг страницы, 
     * обычно это url-домена */
    public $ImagesURLPrefix = "";

    /**
     * @param DB $db - объект базы данных
     */
    public function __construct(DB $db, CURLHelper $Curl, ParseHelper $Parser)
    {
        $this->db = $db;
        $this->Curl = $Curl;
        $this->Parser = $Parser;
    }

    /**
     * формирует валидные опции (параметры парсинга и записи в БД)
     * из переданного массива настроек
     * 
     * @param array $CurrentOption
     * @return ArrayHelper
     */
    private function generateOptions(array $CurrentOption)
    {
        $ResultOptions = new ArrayHelper();
        $ResultOptions->Value["Url"] = $CurrentOption["url"];
        if(isset($CurrentOption["template"]))
        {
            $ResultOptions->Value["Template"] = $CurrentOption["template"];
        }
        else
        {
            $ResultOptions->Value["Template"] = $this->ParseOptions["template"];
        }
        if(isset($CurrentOption["groups"]))
        {
            $ResultOptions->Value["Groups"] = $CurrentOption["groups"];
        }
        else
        {
            $ResultOptions->Value["Groups"] = $this->ParseOptions["groups"];
        }
        if(isset($CurrentOption["code_page"]))
        {
            $ResultOptions->Value["CodePage"] = $CurrentOption["code_page"];
        }
        else
        {
            $ResultOptions->Value["CodePage"] = $this->ParseOptions["code_page"];
        }
        if(isset($CurrentOption["data"]))
        {
            $ResultOptions->Value["Data"] = $CurrentOption["data"];
        }
        else
        {
            $ResultOptions->Value["Data"] = $this->ParseOptions["data"];
        }
        $ResultOptions->Value["Category"] = $CurrentOption["category_id"];

        return $ResultOptions;
    }

    /**
     * запускает получение данных из удаленных источников,
     * парсинг на основе заданных параметров,
     * и запись результата в БД или файл
     */
    public function start($param = self::SAVE_TO_DB)
    {
        $ArrayObject = new ArrayHelper();

        $this->Curl->setHeaderArray($this->CURLHeaders);

        if($param & self::SAVE_TO_FILE && is_file($this->TmpFileName))
        {
            unlink($this->TmpFileName);
        }
        foreach( $this->ParseOptions["urls"] as $CurrentURLOptions)
        {
            $ValidOptions = $this->generateOptions($CurrentURLOptions);

            $HTMLString = $this->Curl->getHTTPStringFrom($ValidOptions->Value["Url"]);
            $this->Parser->setTemplate($ValidOptions->Value["Template"]);
            $ArrayObject->Value = $this->Parser->parse($HTMLString);

            if($param & self::SAVE_TO_DB)
            {
                $this->saveToDB($ArrayObject, $ValidOptions);
            }
            if($param & self::SAVE_TO_FILE)
            {
                file_put_contents($this->TmpFileName, json_encode($ArrayObject->Value), FILE_APPEND);
            }
        }
        
    }

    /**
     * Создает объект фильма под данным из $FilmRating
     * @param array $FilmRating - массив данных, полученных из парсера для очередного фильма
     * @param ArrayHelper $Options - опции для текущего объекта парсинга (опции для текущго URL)
     * @param array $GroupKeys - массив с названиями ключей из БД, идут по порядку в результатах парсинга
     * @param URLTemplateHelper $URLTemp - объект лдя работы с шаблонами URL 
     * ************************************************************
     * (URLTemplateHelper - нужна инъекция в конструктор!!!)
     * ************************************************************
     * 
     * @return Film
     */
    private function createFilm(array $FilmRating, ArrayHelper $Options, array $GroupKeys, URLTemplateHelper $URLTemp)
    {
        $Film = new Film();

        $Film->setId( $FilmRating[array_keys($GroupKeys, "id")[0]] );
        if(!$Film->getId()) { return null; }

        $Film->title = iconv($Options->Value["CodePage"], $this->CurrentCodePage, $FilmRating[array_keys($GroupKeys, "title")[0]] );
        $Film->year = $FilmRating[array_keys($GroupKeys, "year")[0]];
        $Film->category_id = $Options->Value["Category"];
        
        $this->getAdditionData($Film, $Options, $URLTemp);

        return $Film;
    }

    /**
     * Получает для фильма дополнительные данные, перечисленные в $Options->Value["Data"]["fields"],
     * в текущей рализации (2020-09-25) - это картинка и описание по шаблонному URL
     * 
     * @param Film $Film - объект фильма, для которого получаются данные
     * @param ArrayHelper $Options - опции для текущего объекта парсинга (опции для текущго URL)
     * @param URLTemplateHelper $URLTemp - объект лдя работы с шаблонами URL 
     */
    private function getAdditionData(Film $Film, ArrayHelper $Options, URLTemplateHelper $URLTemp)
    {
        $URLTemp->setTemplate($Options->Value["Data"]["url"]);

        $CurrentURL = $URLTemp->generateURL(["id" => $Film->getId()]);

        $CurrentHtml = $this->Curl->getHTTPStringFrom($CurrentURL);

        foreach($Options->Value["Data"]["fields"] as $FieldName => $Template)
        {
            $this->Parser->setTemplate($Template);
            $CurrentData = $this->Parser->parse($CurrentHtml);
            if(!empty($CurrentData))
            {
                $Film->$FieldName = iconv($Options->Value["CodePage"], $this->CurrentCodePage, $CurrentData[0][0] );
            }
        }
        if($Film->picture)
        {
            ImageHelper::$IMGPath = $this->ImagesFolder;
            $Film->picture = ImageHelper::getAndSaveImage($this->ImagesURLPrefix . $Film->picture);
        }
    }

    /**
     * Создает объект рейтинг для фильма под данным из $FilmRating
     * @param Film $Film - объект фильма, для которого создается рейтинг
     * @param array $FilmRating - массив данных, полученных из парсера для очередного фильма
     * @param array $GroupKeys - массив с названиями ключей из БД, идут по порядку в результатах парсинга
     * @return Rating
     */
    private function createRating(Film $Film, array $FilmRating, array $GroupKeys)
    {
        $Rating = new Rating($Film);
        
        $Rating->setId( $Film->getId() );
        if(!$Rating->getId()) { return null; }

        $Rating->place = $FilmRating[array_keys($GroupKeys, "place")[0]];
        $Rating->grade = $FilmRating[array_keys($GroupKeys, "grade")[0]];
        $Rating->voites = $FilmRating[array_keys($GroupKeys, "voites")[0]];
        $Rating->average_grade = $FilmRating[array_keys($GroupKeys, "average_grade")[0]];
        $Rating->date = date("Y-m-d");
        
        return $Rating;
    }


    /**
     * сохраняет данные в БД
     * 
     * @param ArrayHelper $FilmRatings
     * @param ArrayHelper $Options
     * @throws StorageException
     */
    private function saveToDB(ArrayHelper $FilmRatings, ArrayHelper $Options)
    {
        $GroupKeys = array_keys($Options->Value["Groups"]);

        $FilmStorage = new FilmStorage($this->db);
        $RatingStorage = new RatingStorage($this->db);

        $URLTemp = new URLTemplateHelper();
        
        foreach( $FilmRatings->Value as $FilmRating )
        {
            $Film = $this->createFilm($FilmRating, $Options, $GroupKeys, $URLTemp); //$FilmStorage->getModel();
            if(!$Film) { continue; }

            try
            {
                $FilmStorage->insertODKU($Film);
            }
            catch (StorageException $ex)
            {
                if($ex->CurrentError == $ex::INSERT_ALREADY_EXISTS_INDEX)
                { continue; }
                else
                {
                    throw $ex;
                }
            }

            $Rating = $this->createRating($Film, $FilmRating, $GroupKeys); //new Rating($Film);

            $RatingStorage->insertODKU($Rating);
        }

    }

    /**
     * устанавливает заголовки запроса
     * @param array $CURLHeaders
     * @return $this
     */
    public function setCURLHeaders(array $CURLHeaders)
    {
        $this->CURLHeaders = $CURLHeaders;
        return $this;
    }
    /**
     * задает параметры парсинга и записи в БД
     * @param array $ParseOptions
     * @return $this
     */
    public function setParseOptions(array $ParseOptions)
    {
        $this->ParseOptions = $ParseOptions;
        return $this;
    }
    /**
     * устанавливает имя файла, в который будут сохранены результаты парсинга
     * @param string $TmpFileName
     * @return $this
     */
    public function setTmpFileName($TmpFileName)
    {
        $this->TmpFileName = $TmpFileName;
        return $this;
    }



}
