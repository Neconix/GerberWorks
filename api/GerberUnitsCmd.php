<?php
/**
 * @author Neconix (prostoe@gmail.com)
 */

namespace gerberworks;


class GerberUnitsCmd extends GerberCommand
{
    const INCHES_CMD = '%MOIN*%';
    const MM_CMD = '%MOMM*%';

    public $CurrentUnits;

    /**
     * @var bool Координаты задаются в дюймах, если true, иначе в мм
     */
    public $InInches;

    public function __construct($line)
    {
        parent::__construct($line);
        if ( preg_match('/^%MOIN/', $line) )
            $this->CurrentUnits = 'IN';
        else if ( preg_match('/^%MOMM/', $line) )
            $this->CurrentUnits = 'MM';
        else
            throw new \UnexpectedValueException('Указанная единица измерения не поддерживается: '.$line);

        $this->InInches = $this->CurrentUnits == 'IN';
    }
}