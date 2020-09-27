<?php

namespace Helpers;

class ImageHelper {

    /**
     * @var string - локальная папка с изображениями товаров
     */
    public static $IMGPath = "/";

    /**
     * получает изображение из URL, сохраняет его
     * и возвращает имя фала
     * @param string $URL
     * @return string - возвращает имя фала
     */
    public static function getAndSaveImage($URL)
    {
        if(empty($URL)) { return ""; }
        
        $image_file_info = pathinfo($URL);

        $fileext = $image_file_info['extension'];
        $originalfilename = $image_file_info['filename']; // начиная с PHP 5.2.0

        $filename = md5($URL) . "." . $fileext;

        $fullpath = static::$IMGPath . "/" . $filename;

        if( file_exists($fullpath) ) { return $filename; }
        
        $Curl = new \Helpers\CURLHelper();
        $Curl->setBinaryFlag(true);

        $Data = $Curl->getHTTPStringFrom($URL);
        if(file_exists($fullpath)){
            unlink($fullpath);
        }
        $fp = fopen( __DIR__ . "/.." . $fullpath, 'x');
        if(!$fp) { return $filename; }
        fwrite($fp, $Data->Value);
        fclose($fp);
        return $filename;
    }

}
