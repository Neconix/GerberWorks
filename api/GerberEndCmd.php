<?php
/**
 * @author Neconix (prostoe@gmail.com)
 */

namespace gerberworks;

class GerberEndCmd extends GerberCommand
{
    public function __toString()
    {
        return "Команда останова: {$this->SourceString}";
    }
}