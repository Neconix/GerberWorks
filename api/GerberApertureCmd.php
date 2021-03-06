<?php
/**
 * @author Neconix (prostoe@gmail.com)
 */

namespace gerberworks;


class GerberApertureCmd extends GerberCommand
{
    /**
     * @var int Aperture code
     */
    public $Code;
    /**
     * @var string Aperture type - C, R, O, U (Unknown)
     */
    public $Type;
    /**
     * @var array Aperture params dependents to type
     */
    public $Params;

    public function __construct($line) {
        parent::__construct($line);
        $this->Parse($line);
    }

    public function Parse($line)
    {
        //Finding aperture code
        $c = preg_match('/((?<=ADD)\d{2,})([CRO])/', $line, $matches);

        if ($c > 0 && count($matches) === 3) {
            $this->Code = $matches[1]; //Ex.: 10
            $this->Type = $matches[2]; //Ex.: D
            //Finding aperture params
            $pc = preg_match('/(?<=,).*(?=\*)/', $line, $paramsMatches);
            if ($pc > 0)
                switch ($this->Type) {
                    case 'C': $this->ParseCircleParams($paramsMatches[0]);
                        break;
                    case 'R': $this->ParseRectOrObroundParams($paramsMatches[0]);
                        break;
                    case 'O': $this->ParseRectOrObroundParams($paramsMatches[0]);
                        break;
                }
        } else {
            $pc = preg_match('/((?<=ADD)\d{2,})/', $line, $matches);
            if ($pc > 0) {
                $this->Code = $matches[1];
                $this->Type = 'U';
            } else {
                throw new ParseException("Aperture code, type is not found in \"$line\"");
            }
        }
    }

    private function ParseCircleParams($paramsString) {
        $this->Params = [];
        $params = explode('X', $paramsString);
        $pc = count($params);
        if ($pc <=2) {
            $this->Params['D'] = doubleval($params[0]);
            if ($pc > 1)
                $this->Params['H'] = doubleval($params[1]);
        } else {
            throw new ParseException('Circle aperture param count is greater than 2');
        }
    }

    private function ParseRectOrObroundParams($paramsString) {
        $this->Params = [];
        $params = explode('X', $paramsString);
        $pc = count($params);
        if ($pc <= 3) {
            $this->Params['X'] = doubleval($params[0]);
            if ($pc > 1)
                $this->Params['Y'] = doubleval($params[1]);
            if ($pc > 2)
                $this->Params['H'] = doubleval($params[2]);
        } else {
            throw new ParseException('Rect/Obround aperture param count is greater than 4');
        }
    }

    public function __toString()
    {
        return "Aperture: $this->SourceString";
    }
}