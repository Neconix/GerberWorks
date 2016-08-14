<?php
/**
 * @author Neconix (prostoe@gmail.com)
 */

namespace gerberworks;


class FunctionCodeCmd extends GerberCommand
{
    public $Code;

    public function __toString()
    {
        return "Функциональная команда: $this->Code";
    }
}