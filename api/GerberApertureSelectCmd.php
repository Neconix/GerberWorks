<?php
/**
 * @author Neconix (prostoe@gmail.com)
 */

namespace gerberworks;

class GerberApertureSelectCmd extends GerberCommand
{
    /**
     * @var GerberApertureCmd Selected aperture
     */
    public $Aperture;

    /**
     * @var int Aperture code number
     */
    public $ApertureCode;

    /**
     * GerberApertureSelectCmd constructor.
     * @param string $line
     * @throws ParseException
     */
    public function __construct($line)
    {
        parent::__construct($line);

        $this->ApertureCode = self::_findApertureCode($line);
    }

    private static function _findApertureCode($line)
    {
        preg_match('/(?<=D)\d{1,}/', $line, $matches);
        if (count($matches) > 0)
            return intval($matches[0]);
        else
            throw new ParseException('Invalid aperutre D-code', 0, null, $line);
    }

}