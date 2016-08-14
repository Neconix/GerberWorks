<?php
/**
 * @author Neconix (prostoe@gmail.com)
 */

namespace gerberworks;


class GerberCoordFormat extends GerberCommand
{
    public $OmitLeadZeros = false;
    public $OmitTrailZeros = false;

    public $XIntCount = 0;
    public $XDecimalCount = 0;

    public $YIntCount = 0;
    public $YDecimalCount = 0;

    public function __construct($line)
    {
        parent::__construct($line);
        $this->Parse($line);
    }

    /**
     * Задает состояние объекта по описанию формата в строке $line
     * @param $line
     */
    private function Parse($line) {
        //Определяем режим подавления нулей
        if (strpos($line, 'L') !== false )
            $this->OmitLeadZeros = true;
        else if (strpos($line, 'T') !== false)
            $this->OmitTrailZeros = true;
        else if (strpos($line, 'D') !== false) {
            $this->OmitLeadZeros = false;
            $this->OmitTrailZeros = false;
        }
        else
            throw new \InvalidArgumentException("Неверный формат описания координат, строка: $line");

        //Определяем формат десятичной точки для координаты Х
        if (preg_match('/X\d{2}/', $line, $matches)) {
            $this->XIntCount = $matches[0][1];
            $this->XDecimalCount = $matches[0][2];
        } else {
            throw new \InvalidArgumentException("Не удалось определить формат запятой X-координаты: $line");
        }

        //Определяем формат десятичной точки для координаты Y
        if (preg_match('/Y\d{2}/', $line, $matches)) {
            $this->YIntCount = $matches[0][1];
            $this->YDecimalCount = $matches[0][2];
        } else {
            throw new \InvalidArgumentException('Не удалось определить формат запятой Y-координаты', $line);
        }
    }

    public function __toString()
    {
        $s = "Команда описания координат: {$this->SourceString} ";
        $s .= "; Подавлять начальные нули: " . ($this->OmitLeadZeros ? 'Да' : 'Нет');
        $s .= "; Подавлять замыкающие нули: " . ($this->OmitTrailZeros ? 'Да' : 'Нет');
        $s .= "; Формат X-координаты: \"{$this->XIntCount},{$this->XDecimalCount}\"";
        $s .= "; Формат Y-координаты: \"{$this->YIntCount},{$this->YDecimalCount}\"";
        return $s;
    }
}