<?php

use App\Api\Api;
use App\Cron;
use Helpers\CURLHelper;
use Helpers\ParseHelper;

require_once "./bootstrap.php";

// разбираем адрес REQUEST_URI
$api_uri_template = "#^" . $ConfigArray["StartCatalog"] . "/api/?#";
$cron_uri_template = "#^" . $ConfigArray["StartCatalog"] . "/cron/?#";
$default_uri_template = "#^" . $ConfigArray["StartCatalog"] . "/?#";

if(preg_match($api_uri_template, $_SERVER["REQUEST_URI"]))
{
    require_once "./App/Api/Api.php";
    
    $Api = new Api($DB);
    if(strpos($_SERVER["REQUEST_URI"], "all-categories"))
    {
        $Api->actionAllCategories();
    }
    if(strpos($_SERVER["REQUEST_URI"], "all-films"))
    {
        $Api->actionAllFilms();
    }
    if(strpos($_SERVER["REQUEST_URI"], "films-for-categories"))
    {
        //$Api->actionFilmsFromCategory();
        $Api->actionFilmsWithRatingFromCategory();
    }
}
else if(preg_match($cron_uri_template, $_SERVER["REQUEST_URI"]))
{
    require_once "./App/Cron.php";
    $Cron = new Cron( $DB, new CURLHelper(), new ParseHelper() );
    $Cron->ImagesFolder = $ConfigArray["ImagesFolder"];
    $Cron->ImagesURLPrefix = $ConfigArray["ImagesURLPrefix"];

    $Cron->setCURLHeaders($ConfigArray["CURLHeaders"]);
    $Cron->setParseOptions($ConfigArray["ParserOptions"]);
    $Cron->setTmpFileName($ConfigArray["TmpFileName"]);
    $Cron->start(Cron::SAVE_TO_DB | Cron::SAVE_TO_FILE);
}
else
{
    ob_start();
    require WEB . "/Views/main.php";
    // сохраняем вывод
    // $Content - используется как основной контент внутри layouts.default
    $Content = ob_get_clean();

    ob_start();
    // сюда уже передается $Content
    require WEB . "/Layouts/default.php";
    $FullContent = ob_get_clean();

    echo $FullContent;
}

exit;
