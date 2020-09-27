<?php

namespace Helpers;

class CURLHelper
{
    /** 
     * массив с заголовками, в формате:
     * array('Content-type: text/plain', 'Content-length: 100') 
     * @var array 
     */
    protected $Header = [];
    
    protected $BinaryFlag = false;

    /**
     * возвращает сожержимое HTML-страницы в виде строки
     * @param string $url
     * @return \StringHelper
     */
    public function getHTTPStringFrom($url)
    {
        // create a new cURL resource
        $Res = curl_init();

        // set URL and other appropriate options
        curl_setopt($Res, CURLOPT_URL, $url);
        curl_setopt($Res, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($Res, CURLOPT_SSL_VERIFYPEER, false);
        if($this->BinaryFlag)
        {
            curl_setopt($Res, CURLOPT_BINARYTRANSFER, true);
        }
        if(!empty($this->Header))
        {
            curl_setopt($Res, CURLOPT_HEADER, true);
            curl_setopt($Res, CURLOPT_HTTPHEADER, $this->Header);
        }
        else
        {
            curl_setopt($Res, CURLOPT_HEADER, false);
        }

        $StringObject = new StringHelper();
        // grab URL and pass it to the browser
        $StringObject->Value = curl_exec($Res);

        // close cURL resource, and free up system resources
        curl_close($Res);

        return $StringObject;
    }

    /**
     * добавляет заголовок запроса
     * @param string $Name - например, Content-type
     * @param string $Value - например, text/plain
     */
    public function addHeader($Name, $Value)
    {
        $this->Header[] = trim($Name) . ": " . $Value;
    }

    /**
     * добавляет заголовок запроса в виде строки
     * @param string $String - например, Content-type: text/plain
     */
    public function addHeaderString($String)
    {
        $this->Header[] = $String;
    }
    /**
     * устанавливает заголовки запрса
     * @param array $Arr - массив с заголовками, в формате:
     * array('Content-type: text/plain', 'Content-length: 100') 
     */
    public function setHeaderArray(array $Arr)
    {
        $this->Header = $Arr;
    }
    
    public function setBinaryFlag($BinaryFlag)
    {
        $this->BinaryFlag = $BinaryFlag;
        return $this;
    }


}
