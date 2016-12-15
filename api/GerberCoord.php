<?php
/**
 * @author Neconix (prostoe@gmail.com)
 */

namespace gerberworks;

/**
 * Class GerberCoord
 * @package gerberworks
 * @property bool InGrid true - если обе координаты совпадают с шагом сетки
 * @property bool IsPath true, если команда является экземпляром D01, D02 команды отрисовки пути
 * @property bool IsFlash true, если команда является экземпляром D03 команды отрисовки флеша
 */
class GerberCoord extends GerberCommand
{
    const INCHES_COEF = 25.4;

    /**
     * @var bool true, если источник координат был в дюймах
     */
    public $SourceInInches;

    /**
     * @var float Значение X координаты в мм
     */
    public $X;

    /**
     * @var float Значение Y координаты в мм
     */
    public $Y;

    /**
     * @var float Значение X координаты в дюймах
     */
    public $Xinch;

    /**
     * @var float Значение Y координаты в дюймах
     */
    public $Yinch;

    public $I;
    public $Iinch;
    public $J;
    public $Jinch;

    /**
     * @var string Строка кода действия
     */
    public $Action;

    /**
     * @var string Код интерполяции, либо null
     */
    public $GCode;
    /**
     * @var GerberGraphicState Graphic state on coordinate
     */
    public $GraphicState;

    /**
     * @var bool true, если точка находится в заданной сетки
     */
    public $XinGrid = true;

    /**
     * @var bool true, если точка находится в заданной сетки
     */
    public $YinGrid = true;

    /**
     * @var bool true, если точка находится в заданной сетки
     */
    public $XGridDelta;

    /**
     * @var bool true, если точка находится в заданной сетки
     */
    public $YGridDelta;

    /**
     * @var true - если координаты были изменены относительно первоначального значения источника.
     * Принимает значение true после вызова одной из функций setX, setY, setI, setJ
     */
    public $Changed;

    /**
     * @var GerberCoord Previous coordinate
     */
    public $PrevCoordinate;

    public function __get($name)
    {
        if ($name == 'InGrid') {
            return $this->XinGrid && $this->YinGrid;
        } else if ($name == 'IsPath') {
            return $this->Action == 'D01' || $this->Action == 'D02';
        } else if ($name == 'IsFlash') {
            return $this->Action == 'D03';
        } else {
            throw new \InvalidArgumentException("$name не определен в данном классе");
        }
    }

    public function __construct($line, $inInches) {
        parent::__construct($line);
        $this->SourceInInches = $inInches;
    }

    public function setX($value, $inInches) {
        if ($inInches) {
            $this->Xinch = $value;
            $this->X = $this->ConvertToMM($value);
        } else {
            $this->Xinch = $this->ConvertToInch($value);
            $this->X = $value;
        }
        $this->Changed = true;
    }

    public function setY($value, $inInches) {
        if ($inInches) {
            $this->Yinch = $value;
            $this->Y = $this->ConvertToMM($value);
        } else {
            $this->Yinch = $this->ConvertToInch($value);
            $this->Y = $value;
        }
        $this->Changed = true;
    }

    public function setI($value, $inInches) {
        if ($this->SourceInInches) {
            $this->Iinch = $value;
            $this->I = $this->ConvertToMM($value);
        } else {
            $this->Iinch = $this->ConvertToInch($value);
            $this->I = $value;
        }
        $this->Changed = true;
    }

    public function setJ($value, $inInches) {
        if ($this->SourceInInches) {
            $this->Jinch = $value;
            $this->J = $this->ConvertToMM($value);
        } else {
            $this->Jinch = $this->ConvertToInch($value);
            $this->J = $value;
        }
        $this->Changed = true;
    }

    /**
     * Конвертирует координату из дюймов в мм
     * @param $value
     * @return float
     */
    private function ConvertToMM($value) {
        return round($value * GerberCoord::INCHES_COEF, 6);
    }

    private function ConvertToInch($value) {
        return round($value / GerberCoord::INCHES_COEF, 6);
    }

    private function GetActionName() {
        switch ($this->Action) {
            case 'D01': return 'DO1: Перемещение с засветкой';
            case 'D02': return 'DO2: Перемещение без засветки';
            case 'D03': return 'DO3: Вспышка (Flash)';
            default: return 'Неизвестное действие';
        }
    }

    /**
     * @param GerberCoordFormat|null $coordFormat
     * @param bool $inInches
     * @return string
     */
    public function ToGerberString(GerberCoordFormat $coordFormat = null, $inInches = false, $fullFormat = false)
    {
        if ($coordFormat == null)
            throw new \InvalidArgumentException('Не задан формат координат');

        list($x, $y) = $inInches ? [$this->Xinch, $this->Yinch] : [$this->X, $this->Y];

        $xstr = 'X'.$this->MakeStrCoord($x, $coordFormat->XIntCount, $coordFormat->XDecimalCount, $coordFormat);
        $ystr = 'Y'.$this->MakeStrCoord($y, $coordFormat->YIntCount, $coordFormat->YDecimalCount, $coordFormat);

        //Важно: если координата наследуюется от предыдущей, то неправильное ее значение уже было исправлено
        //на предыдущем этапе, следовательно, проверка на изменение не нужно
        if ($fullFormat) {
            $str = "{$this->GCode}{$xstr}{$ystr}{$this->Action}*";
        } else {
            $str = preg_replace('/X[\d,-]*/', $xstr, $this->SourceString);
            $str = preg_replace('/Y[\d,-]*/', $ystr, $str);

            //I и J составляющие координат могут быть не заданы, проверяем на null
            if ($this->I != null) {
                $coordname = $inInches ? 'Iinch' : 'I';
                $istr = 'I'.$this->MakeStrCoord($this->$coordname,
                        $coordFormat->XIntCount, $coordFormat->XDecimalCount, $coordFormat);
                $str = preg_replace('/I-?\d+/', $istr, $str);
            }

            if ($this->J != null) {
                $coordname = $inInches ? 'Jinch' : 'J';
                $jstr = 'J'.$this->MakeStrCoord($this->$coordname,
                        $coordFormat->YIntCount, $coordFormat->YDecimalCount, $coordFormat);
                $str = preg_replace('/J-?\d+/', $jstr, $str);
            }
        }

        return $str;
    }

    private function MakeStrCoord($val, $padIntCount, $padDecCount, GerberCoordFormat $coordFormat) {
        $a = explode('.', $val);
        //TODO: Приведение к размеру, проверка на ошибку

        //Целая часть
        if ($coordFormat->OmitLeadZeros) {
            $s = $a[0];
        } else {
            if ($val > 0) {
                $s = str_pad($a[0], $padIntCount, '0', STR_PAD_LEFT);
            } else {
                //Переносим знак - в начало числа
                $s = str_pad(abs($a[0]), $padIntCount, '0', STR_PAD_LEFT);
                $s = "-$s";
            }
        }

        //Дробная часть
        if (count($a) > 1) {
            //Если длина строки уже соответствует требуемуему размеру,
            // то вызов str_pad() сдвинет ее влево, что испортит значение
            if (strlen($a[1]) < $padDecCount)
                $s .= str_pad($a[1], $padDecCount, '0', STR_PAD_RIGHT);
            else
                $s .= $a[1];
        } else
            $s .= str_pad('', $padDecCount, '0', STR_PAD_RIGHT);
        return $s;
    }

    public function __toString()
    {
        $s = "Координаты: {$this->SourceString}, мм: ({$this->X}, {$this->Y})";
        $s .= "; дюймы: ($this->Xinch, $this->Yinch)";
        $s .= "; Действие: ".$this->GetActionName();
        return $s;
    }
}