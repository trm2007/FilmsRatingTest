<?php

namespace DataBase;

/**
 * Синглтон,
 * создает экземпляр подключения к MySQL
 */
class DB
{
/**
 *
 * @var \mysqli 
 */
private static $MySQLInstance;

protected static $Host = "localhost";
protected static $User = "root";
protected static $Password = "";
protected static $DBName = "cinema_rating";
protected static $Port = 3306;
protected static $CharSet = "utf8";

private static $Instance = null;

private function __construct()
{
    self::$MySQLInstance = new \mysqli();

    self::$MySQLInstance->connect(self::$Host, self::$User, self::$Password, self::$DBName, self::$Port);
    self::$MySQLInstance->set_charset(self::$CharSet);
}
/**
 * 
 * @return DB
 */
public static function instance()
{
    if(!self::$Instance) { self::$Instance = new self; }
    return self::$Instance;
}
/**
 * 
 * @return \mysqli
 */
public static function getSQLInstance()
{
    if(!self::$Instance) { self::$Instance = new self; }
    return self::$MySQLInstance;
}

/**
 * 
 * @param string $Host
 * @param string $User
 * @param string $Password
 * @param string $DBName
 * @param int $Port
 */
public static function setOptions($Host, $User, $Password, $DBName, $Port = 0)
{
    self::$Host = $Host;
    self::$User = $User;
    self::$Password = $Password;
    self::$DBName = $DBName;
    self::$Port = $Port;
}

} // DB

