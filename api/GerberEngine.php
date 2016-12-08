<?php
/**
 * @author Neconix (prostoe@gmail.com)
 */

namespace gerberworks;


class GerberEngine
{
    /**
     * @var GerberUnitsCmd Величины измерения
     */
    public $CurrentUnits;

    /**
     * @var array Список команд Gerber-файла
     */
    public $Commands;

    /**
     * @var GerberCoordFormat Формат координат
     */
    public $CoordFormat;

    /**
     * @var bool Режим работы с абсолютными или относительными координатами
     */
    public $IsAbsoluteCoord = true;

    /**
     * @var GerberCoord Последняя обработанная координата
     */
    public $LastCoordinate;

    /**
     * @var string Последнее действие
     */
    public $LastAction = '';

    /**
     * @var GerberGraphicState Текущее графическое состояние при парсинге файла
     */
    public $_graphicState;

    /**
     * @var float Шаг сетки для дорожек
     */
    public $TrackGridCoef = 0.625;

    /**
     * @var float Величина отклонения дорожки от сетки, которое считается ошибочным
     */
    public $TrackDeviationDelta = 0.001;


    public function __construct() {
        $this->_graphicState = new GerberGraphicState();
    }

    public function Parse($fileName) {
        //Чтение конфигурации
        $lines = file($fileName, FILE_IGNORE_NEW_LINES);
        $lineNo = 0;
        //Составляем список примитивов
        foreach ( $lines as $line ) {
            try {
                $cmd = $this->ParseLine($line);
                $cmd->Line = ++$lineNo;
            } catch (\Exception $e) {
                throw new \InvalidArgumentException("Ошибка разбора строки: \"$line\"", 1, $e);
            }

            $this->Commands[] = $cmd;
            if ($cmd instanceof GerberEndCmd)
                break;
        }
    }

    /**
     * Разбирает значение строки файла
     * @param $line
     * @return GerberCommand
     */
    private function ParseLine($line) {

        //Функциональные команды
        if (preg_match('/G(\d){2}/', $line)) {
            return $this->ParseMode($line);
        }

        if ( preg_match('/^%/', $line) ) {
            //Строка конфигурации
            return $this->ParseConfig($line);
        } else if ( preg_match('/^G04/', $line) ) {
            //Строка комментария
            $cmd = new GerberCommand();
            $cmd->SourceString = $line;
            return $cmd;
        } else if (preg_match('/(X{1}-?\d+)/', $line)
            || preg_match('/(Y{1}-?\d+)/', $line)) {
            //Либо Х, либо Y координата
            return $this->ParseCoord($line);
        } else if (preg_match('/^M02*/', $line)) {
            //Конец скрипта
            return new GerberEndCmd($line);
        } else {
            //Неизвестная команда или пустая строка
            return new GerberCommand($line);
        }
    }

    /**
     * Разбирает строку конфигурации
     * @param $line
     * @return GerberCommand|GerberCoordFormat|GerberUnitsCmd
     */
    private function ParseConfig($line) {
        if (preg_match('/^%MO/', $line)) {
            //Настрока единицы измерения - мм
            return $this->ParseUnits($line);
        } else if (preg_match('/^%FS/', $line)) {
            //Настройки формата координат
            $this->CoordFormat = new GerberCoordFormat($line);
            return $this->CoordFormat;
        } else if (preg_match('/^%AD/', $line)) {
            $a = new GerberApertureCmd($line);
            $this->_graphicState->Apertures[$a->Code] = $a;
            //Назначает апертуру по умолчанию - первую по списку,
            //если не задана ранее
            if ($this->_graphicState->CurrentAperture == null) {
                $this->_graphicState->CurrentAperture = $a;
            }
            return $a;
        } else {
            return new GerberCommand($line);
        }
    }

    /**
     * Определяет значение единиц измерения в дюймах или миллиметрах
     * @param $line
     * @return GerberUnitsCmd
     */
    private function ParseUnits($line) {
        $cmd = new GerberUnitsCmd($line);
        $this->CurrentUnits = $cmd;
        return $cmd;
    }

    /**
     * Разбирает строку с командой изменения режима
     * @param $line
     * @return GerberMode
     */
    private function ParseMode($line) {
        GerberMode::UpdateGraphicState($this->_graphicState, $line);
        return new GerberMode($line);
    }

    /**
     * Разбирает строку с координатами
     * @param $line
     * @return GerberCoord
     */
    private function ParseCoord($line) {
        $inInches = $this->CurrentUnits->InInches;
        $cmd = new GerberCoord($line, $this->CurrentUnits->InInches);

        //Получение G-кода
        if (preg_match('/G\d\d/', $line, $matches)) {
            $cmd->GCode = $matches[0];
        }

        //Получение X составляющей координаты
        if (preg_match('/X[\d,-]*/', $line, $matches)) {
            $rawvalue = substr($matches[0], 1);
            $value = $this->ParseValue($rawvalue, $this->CoordFormat->XIntCount, $this->CoordFormat->XDecimalCount);
            $cmd->setX($value, $inInches);
        } else {
            //X-координата отсутствует, получаем ее значение из предыдущей согласно Gerber-спецификации
            if ($this->LastCoordinate != null) {
                $cmd->X = $this->LastCoordinate->X;
                $cmd->Xinch = $this->LastCoordinate->Xinch;
            } else
                throw new \InvalidArgumentException('Ошибка разбора координат: не найдена предыдущаяя X-координата');
        }

        //Получение Y составляющей координаты
        if (preg_match('/Y[\d,-]*/', $line, $matches)) {
            $rawvalue = substr($matches[0], 1);
            $value = $this->ParseValue($rawvalue, $this->CoordFormat->YIntCount, $this->CoordFormat->YDecimalCount);
            $cmd->setY($value, $inInches);
        } else {
            //Y-координата отсутствует, получаем ее значение из предыдущей согласно Gerber-спецификации
            if ($this->LastCoordinate != null) {
                $cmd->Y = $this->LastCoordinate->Y;
                $cmd->Yinch = $this->LastCoordinate->Yinch;
            } else
                throw new \InvalidArgumentException('Ошибка разбора координат: не найдена предыдущаяя Y-координата');
        }

        //При наличии I составляющей координаты обрабатываем
        if (preg_match('/I-?\d+/', $line, $matches)) {
            $rawvalue = substr($matches[0], 1);
            $value = $this->ParseValue($rawvalue, $this->CoordFormat->XIntCount, $this->CoordFormat->XDecimalCount);
            $cmd->setI($value, $inInches);
        }

        //При наличии J составляющей координаты обрабатываем
        if (preg_match('/J-?\d+/', $line, $matches)) {
            $rawvalue = substr($matches[0], 1);
            $value = $this->ParseValue($rawvalue, $this->CoordFormat->YIntCount, $this->CoordFormat->YDecimalCount);
            $cmd->setJ($value, $inInches);
        }

        //Определение действия
        if (preg_match('/D\d{2}/', $line, $matches)) {
            $cmd->Action = $matches[0];
        } else {
            if ($this->LastAction != '')
                $cmd->Action = $this->LastAction;
            else
                throw new \InvalidArgumentException('Ошибка разбора координат: действие для координаты не найдено');
        }

        //Saves graphic state on coordinate
        $cmd->GraphicState = clone $this->_graphicState;

        //Выполняем анализ отклонения
        $this->AnalyzeGridDelta($cmd);

        //Сохраняем текущую координату как последнюю для последующих циклов обработки
        $this->LastCoordinate = $cmd;
        $this->LastAction = $cmd->Action;

        return $cmd;
    }

    /**
     * Получает числовое значение координаты из строки
     * @param $coord
     * @return float
     */
    private function ParseValue($coord, $intCount, $decimalCount) {

        //Частный случай - 0
        if ($coord === '0')
            return 0;

        //Первый символ может быть знаком минус
        $isPositive  = $coord[0] != '-';

        $numbers = $isPositive ? $coord : substr($coord, 1);

        if ($this->CoordFormat->OmitTrailZeros) {
            $numbers = str_pad($numbers, $intCount + $decimalCount, '0', STR_PAD_RIGHT);
        } else {
            $numbers = str_pad($numbers, $intCount + $decimalCount, '0', STR_PAD_LEFT);
        }

        $value = substr_replace($numbers, '.', $intCount, 0);
        if ($isPositive == false)
            $value = '-'.$value;

//        if ($this->CoordFormat->OmitLeadZeros) {
//            //Подавление ведущих нулей, читаем строку числа с конца
//            $decimalPart = substr($numbers, -$decimalCount);
//            $integerPart = substr($numbers, 0, strlen($numbers)-$decimalCount);
//        } else {
//            //Подавление завершающих нулей, либо полный формат, читаем строку числа с начала и до конца
//            $integerPart = substr($numbers, 0, $intCount);
//            $decimalPart = substr($numbers, $intCount-1);
//        }
//
//        //Cообщаем об ошибке, если не удалось получить координаты
//        if ($decimalPart === false || $integerPart === false)
//            throw new \InvalidArgumentException('Не удалось определить значение координаты');
//
//        $value = "$integerPart.$decimalPart";
//        if ($isPositive == false)
//            $value = '-'.$value;
        $value = (double)$value;
        return $value;
    }

    private function AnalyzeGridDelta(GerberCoord &$cmd) {
        if ($cmd->IsPath) {
            $gridStep = $this->TrackGridCoef;
            $eps = $this->TrackDeviationDelta;

            $cmd->XGridDelta = $this->CalcDelta($cmd->X, $gridStep);
            $cmd->YGridDelta = $this->CalcDelta($cmd->Y, $gridStep);

            if (abs($cmd->XGridDelta) >= $eps)
                $cmd->XinGrid = false;

            if (abs($cmd->YGridDelta) >= $eps)
                $cmd->YinGrid = false;
        }
    }

    private function CalcDelta($a, $gridStep) {
        $multiply = $a / $gridStep;
        $gridLine = round($multiply) * $gridStep;
        $delta = $a - $gridLine;
        return round($delta, 6);
    }
}