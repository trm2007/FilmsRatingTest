<?php

namespace Models;

abstract class Model implements \ArrayAccess, \Iterator, \JsonSerializable
{
    /** 
     * @var string - текущее поле (точнее его индекс в массиве $Fields) при переборе объекта как итерирумого
     */
    protected $CurrentFieldIndex;
    /** 
     * @var array - массив доступных полей у объекта
     */
    protected static $Fields = [];
    /** @var string - имя поля с ID по умолчанию */
    protected static $IdFieldName = "id";
    /**
     * @var array - массив с данными модели, индекс - имя свойства-(поля) => значения свойства 
     */
    protected $data = [];

    /**
     * 
     * @param array $Fields - массив полей(свойств) у создаваемой модели
     */
    public function __construct(array $Fields)
    {
        /**
         * позднее статическое связывание static::, 
         * массив $Fields будет свой у каждого наследнуемого класса
         */
        static::$Fields = $Fields;
    }

    /**
     * @return array - массив свойств(полей) для данного типа моделей
     */
    public static function getFields()
    {
        return static::$Fields;
    }

    /**
     * возвращает значение свойства модели с именем $name
     * @param string $name
     * @return mixed
     */
    protected function getValue($name)
    {
        if( in_array($name, static::$Fields) && isset($this->data[$name]))
        {
            return $this->data[$name];
        }
        return null;
    }
    /**
     * устанавливает свойство модели с именем $name в значение $value
     * @param string $name
     * @param mixed $value
     */
    protected function setValue($name, $value)
    {
        if( in_array($name, static::$Fields) )
        {
            return $this->data[$name] = $value;
        }
    }

    /**
     * возвращает значение свойства модели с именем $name
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getValue($name);
    }
    
    /**
     * устанавливает свойство модели с именем $name в значение $value
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->setValue($name, $value);
    }

    /**
     * @return mixed - значение поля ID, по умолчанию индекс поля "id"
     */
    public function getId()
    {
        return isset($this->data[static::$IdFieldName]) ?
                    $this->data[static::$IdFieldName] :
                    null;
    }

    /**
     * @param mixed $id - значение поля ID, по умолчанию индекс поля "id"
     * @return $this
     */
    public function setId($id)
    {
        $this->data[static::$IdFieldName] = $id;
        return $this;
    }
    
    /**
     * @return string - возвращает имя поля содержащего ID, по умолчанию - "id"
     */
    public static function getIdFieldName()
    {
        return static::$IdFieldName;
    }

    /**
     * Задает имя поля содержащего ID
     * @param string $IdFieldName
     * @return $this
     */
    public static function setIdFieldName($IdFieldName)
    {
        static::$IdFieldName = $IdFieldName;
        return $this;
    }

    /**
     * инициализирует данные модели из массива
     * @param array $Data - массив с данными, должен иметь такие же индексы элементов массива, 
     * как соответствующие имена полей self::$Fields в данной модели
     * @return $this
     */
    public function initFromArray(array $Data)
    {
        foreach( static::$Fields as $FieldName )
        {
            $this->data[$FieldName] = isset($Data[$FieldName]) ? $Data[$FieldName] : null;
        }
        return $this;
    }
// ******************* Iterator ***************************

    public function current()
    {
        return isset($this->data[static::$Fields[$this->CurrentFieldIndex]]) ? 
                $this->data[static::$Fields[$this->CurrentFieldIndex]] :
                null;
    }

    public function key() {
        return static::$Fields[$this->CurrentFieldIndex];
    }

    public function next(): void {
        ++$this->CurrentFieldIndex;
    }

    public function rewind(): void {
        $this->CurrentFieldIndex = 0;
    }

    public function valid(): bool {
        return isset(static::$Fields[$this->CurrentFieldIndex]);
    }

// ******************* ArrayAccess ***************************

    public function offsetExists($offset): bool {
        return in_array($offset, static::$Fields);
    }

    public function offsetGet($offset) {
        return $this->getValue($offset);
    }

    public function offsetSet($offset, $value): void {
        $this->setValue($offset, $value);
    }

    public function offsetUnset($offset): void {
        unset($this->data[$offset]);
    }

// ******************* JsonSerializable ***************************

    public function jsonSerialize() {
        return $this->data;
    }

}