<?php

namespace Storages\Exceptions;

class StorageException extends \Exception
{
    /** строка уже есть по уникальному ключу */
    const INSERT_ALREADY_EXISTS_INDEX = 1062;
    
    const Messages = [
        self::INSERT_ALREADY_EXISTS_INDEX => "Cтрока уже есть по уникальному ключу!"
    ];
    
    public $CurrentError;
    
    public function __construct($ErrorCode, $Msg)
    {
        $this->CurrentError = $ErrorCode;
        parent::__construct( $ErrorCode . ": " .
                isset(self::Messages[$ErrorCode]) ? self::Messages[$ErrorCode] . " " : ""
                . $Msg, 500);
    }
}