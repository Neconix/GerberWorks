<?php
/**
 * @author Neconix (prostoe@gmail.com)
 */

namespace gerberworks;


class
GerberCommand
{
    /**
     * @var int Command line number
     */
    public $Line;

    /**
     * @var GerberCommand Previous command
     */
    public $PrevCommand;
    /**
     * @var GerberCommand Next command
     */
    public $NextCommand;

    /**
     * @var string Source string of gerber command
     */
    public $SourceString;

    public function __construct($line = '') {
        $this->SourceString = $line;
    }

    public function __toString()
    {
        return "Неизвестная команда: {$this->SourceString}";
    }

    /**
     * Возвращает строку команды в формате Gerber, пригодной для записи в файл
     * @return string
     */
    public function ToGerberString() {
        return $this->SourceString;
    }
}