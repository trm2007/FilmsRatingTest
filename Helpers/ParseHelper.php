<?php

namespace Helpers;

class ParseHelper
{
    /** @var string */
    protected $Template = "";
    /** @var string - ограничитель начала и конца шаблона, по умолчанию = "/" */
    protected $CurrentDelimiters = "/";
    /** @var array - массив с модификаторами, по умолчанию = ['i', 's', 'U'] */
    protected $CurrentModificators = ['i', 's', 'U'];

    /**
     * @param \Helpers\StringHelper $String
     * @return array - массив совпадений полученный с установленной опцией PREG_SET_ORDER
     */
    public function parse(StringHelper $String)
    {
        $matches = [];
        preg_match_all($this->Template, $String->Value, $matches, PREG_SET_ORDER);
        if(empty($matches)) { return null; }
        foreach( $matches as $i => $match ){ array_shift($match); $matches[$i] = $match; }
        return $matches;
    }

    /**
     * 
     * @param string $Template - строка с шаблоном для парсинга без оганичителей
     * @return $this
     */
    public function setTemplate(string $Template)
    {
        $this->Template = $this->CurrentDelimiters . $Template . $this->CurrentDelimiters . implode($this->CurrentModificators);
        return $this;
    }

}
