<?php

namespace Helpers;

class URLTemplateHelper
{
    /** @var string */
    public $Template = "";
    /** @var array - переменные из щаблона URL */
    public $Variables = [];
    
    public function setTemplate(string $Template)
    {
        $this->Template = $Template;
        return $this;
    }

    public function getVariables()
    {
        return $this->Variables;
    }

    /**
     * получает и возвращает все переменные, присутсвующие в шаблоне URL,
     * которые необходимо предать в generateURL( [...] )
     * @param string $Template
     * @return array
     */
    public function generateVariables($Template = "")
    {
        if( empty($Template) )
        {
            if(empty($this->Template)) { return null; }
            $Template = $this->Template;
        }
        else
        {
            $this->Template = $Template;
        }
        
        $matches = [];
        preg_match_all("/{([^}]+)}/i", $Template, $matches, PREG_SET_ORDER);
        if(empty($matches)) { return null; }
        $this->Variables = [];
        foreach( $matches as $i => $match ){ array_shift($match); $this->Variables[] = $match[0]; }
        return $this->Variables;
    }
    /**
     * генерирует URL из шаблона по заданным параметрам
     * @param array $Params - массив параметров и их значени [ "VarName" => "VarValue", ... ]
     * @return string
     */
    public function generateURL(array $Params)
    {
        $URLString = $this->Template;
        foreach($Params as $VarName => $VarVal)
        {
            $URLString = str_replace("{" . $VarName . "}", $VarVal, $URLString);
        }
        return $URLString;
    }
}
