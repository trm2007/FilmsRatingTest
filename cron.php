<?php

use App\Cron;
use Helpers\CURLHelper;
use Helpers\ParseHelper;

require_once "./bootstrap.php";
require_once "./App/Cron.php";

$Cron = new Cron( $DB, new CURLHelper(), new ParseHelper() );
$Cron->ImagesFolder = $ConfigArray["ImagesFolder"];
$Cron->ImagesURLPrefix = $ConfigArray["ImagesURLPrefix"];

$Cron->setCURLHeaders($ConfigArray["CURLHeaders"]);
$Cron->setParseOptions($ConfigArray["ParserOptions"]);
$Cron->setTmpFileName($ConfigArray["TmpFileName"]);
$Cron->start(Cron::SAVE_TO_DB);
echo "The End!";
exit;
