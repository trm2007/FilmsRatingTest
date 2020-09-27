<?php

use DataBase\DB;

require_once "./Helpers/StringHelper.php";
require_once "./Helpers/ArrayHelper.php";
require_once "./Helpers/URLTemplateHelper.php";
require_once "./Helpers/CURLHelper.php";
require_once "./Helpers/ImageHelper.php";
require_once "./Helpers/ParseHelper.php";
require_once "./DataBase/DataBase.php";
require_once "./Models/Model.php";
require_once "./Models/Film.php";
require_once "./Models/Rating.php";
require_once "./Storages/Storage.php";
require_once "./Storages/FilmStorage.php";
require_once "./Storages/RatingStorage.php";
require_once "./Storages/Exceptions/StorageException.php";

define("WEB", "./Web");

$ConfigArray = require_once "./Config/config.php";

DB::setOptions(
        $ConfigArray["DB"]["Host"], 
        $ConfigArray["DB"]["User"], 
        $ConfigArray["DB"]["Password"], 
        $ConfigArray["DB"]["DBName"], 
        $ConfigArray["DB"]["Port"]
        );
$DB = DB::instance();
