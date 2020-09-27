<?php

namespace Storages;

use DataBase\DB;
use Models\Model;
use Storages\Exceptions\StorageException;

abstract class Storage
{
    /** @var DB - экземпляр объекта для работы с БД */
    protected $DB = null;
    /** @var \mysqli */
    protected $MySQL = null;
//    /** @var Model - объект модели, с которой работает хранилище */
//    private $Model;
    /** @var string - тип-класс моделей, с которой работает хранилище */
    private $ModelClassName;
//    /**
//     * @var array - массив строк с операциями INSERT
//     */
//    protected $InsertQueryArray = [];
    /** @var string - имя таблицы, в которй сохраняются данные модели */
    protected $TableName;

    /**
     * @param DB $db
     * @param string $ModelClassName - тип-класс моделей, с которой работает хранилище
     */
    public function __construct(DB $db, $ModelClassName, $TableName)
    {
        $this->DB = $db;
        $this->MySQL = $db->getSQLInstance();
        $this->ModelClassName = $ModelClassName;
//        $this->Model = $this->getModel();
        $this->TableName = $TableName;
    }
    /**
     * @return Model - возвращает новый экземпляр модели (clone)
     */
    public function getModel()
    {
        return new $this->ModelClassName;
        //return clone $this->Model;
    }
    
    public function getBy(array $Params)
    {
        $Collection = new \Helpers\ArrayHelper();
        
        return $Collection;
    }

    /**
     * @param Model $Model
     * @return string - генерирует строку INSERT INTO .... данных в модели
     * @param bool $ODKUFlag - если флаг установлен, 
     * то будет применяться вставка INSERT ... ON DUPLICATE KEY UPDATE
     */
    protected function generateInsertString(Model $Model, $ODKUFlag = false)
    {
        $QueryField = [];
        $QueryValue = [];
        foreach($Model::getFields() as $FieldName)
        {
            $QueryField[] = "`" . $FieldName . "`";
            $QueryValue[$FieldName] = "'" . 
                    (isset($Model[$FieldName]) ? $this->MySQL->escape_string($Model[$FieldName]) : "")
                . "'";
        }
        $QueryString = "INSERT INTO `{$this->TableName}` "
        . "(" . implode(",", $QueryField) . ") "
        . "VALUES (" . implode(",", $QueryValue) . ")";
        // Если передан флаг для выполнения запроса INERT ... ON DUPLICATE KEY UPDATE,
        // дополняем строку
        if($ODKUFlag)
        {
            $QueryString .= " ON DUPLICATE KEY UPDATE ";
            foreach($QueryValue as $FieldName => $FieldValue)
            {
                $QueryString .= $FieldName . "=" . $FieldValue . ",";
            }
        }
        return rtrim($QueryString, ",");
    }
    /**
     * устанавливает поле ID модели в значение ID для только что записанной строки в БД
     * @param Model $Model
     * @param sring $IdFieldName - имя поля, содержащего ID
     */
    protected function setLastId(Model $Model, $IdFieldName = "id")
    {
        if(in_array($IdFieldName, $Model::getFields()) && $this->MySQL->insert_id)
        {
            $Model->$IdFieldName = $this->MySQL->insert_id;
        }
    }
    /**
     * Пытается совершить вставку данных модели в БД через INSERT,
     * если вставка не удалась, например, если запись уже есть с повторяющимся уникальным полем,
     * то выбрасывается исключение
     * 
     * @param Model $Model
     * @param bool $ODKUFlag - если флаг установлен, 
     * то будет применяться вставка INSERT ... ON DUPLICATE KEY UPDATE
     * @throws StorageException
     */
    public function insert(Model $Model, $ODKUFlag = false)
    {
        $InsertQuery = $this->generateInsertString($Model, $ODKUFlag);
        if(!$this->MySQL->query($InsertQuery))
        {
            throw new StorageException($this->MySQL->errno, $InsertQuery);
        }
        $this->setLastId($Model);
    }
    public function insertODKU(\Models\Model $Model)
    {
        $this->insert($Model, true);
    }
//    
//    public function doInsert()
//    {
//echo "<pre>" . PHP_EOL;
//print_r($this->InsertQueryArray);
//echo "</pre>" . PHP_EOL;exit;        
//        if(empty($this->InsertQueryArray) ) { return false; }
//        $QueryString = implode(";", $this->InsertQueryArray);
//        $this->DB->getMySQLInstance()->multi_query($QueryString);
//    }
}