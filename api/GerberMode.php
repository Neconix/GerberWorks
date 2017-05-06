<?php
/**
 * @author Neconix (prostoe@gmail.com)
 */

namespace gerberworks;


class GerberMode extends GerberCommand
{
    /**
     * @var int Mode type code
     */
    public $Code = 0;

    public function __construct($line)
    {
        parent::__construct($line);
        $this->Code = static::FindModeCode($line);
    }

    /**
     * @param GerberGraphicState $graphicState
     * @param $line
     * @throws ParseException
     */
    public static function UpdateGraphicState(GerberGraphicState &$graphicState, $line)
    {
        $mode = static::FindModeCode($line);
        switch ($mode) {
            //Linear interpolation
            case GerberGraphicState::LINEAR_INTERPOLATION:
                $graphicState->InterpolationMode = $mode;
                break;
            //Circular interpolation
            case GerberGraphicState::CIRCULAR_INTERPOLATION_CLOCKWISE:
                $graphicState->InterpolationMode = $mode;
                break;
            //Circular interpolation
            case GerberGraphicState::CIRCULAR_INTERPOLATION_COUNTERCLOCKWISE:
                $graphicState->InterpolationMode = $mode;
                break;
            case GerberGraphicState::IGNORE_DATA_BLOCK:
                //Comment
                //TODO: Think about comments
                $code = GerberGraphicState::IGNORE_DATA_BLOCK;
//                if (preg_match('/G04.*[^\*]/', $line, $m))
//                    $this->Comment = $m[0];
                break;
            case GerberGraphicState::REGION_MODE_ON:
                $graphicState->RegionMode = $mode;
                break;
            case GerberGraphicState::REGION_MODE_OFF:
                $graphicState->RegionMode = $mode;
                break;
            case GerberGraphicState::SINGLE_QUADRANT_MODE:
                $graphicState->QuadrantMode = $mode;
                break;
            case GerberGraphicState::MULTI_QUADRANT_MODE:
                $graphicState->QuadrantMode = $mode;
                break;
            //Aperture command
            case 'G54':
                //Aperture code
                if (preg_match('/(?<=D)\d*/', $line, $matches) === 1) {
                    $apertureCode = $matches[0];
                    $graphicState->CurrentAperture = $graphicState->Apertures[$apertureCode];
                } else
                    throw new ParseException('Invalid aperture select command', 0, null, $line);
                break;
            default:
                ;
        }
    }

    public static function FindModeCode($line)
    {
        preg_match('/(?<=G)\d{2}/', $line, $matches);
        if (count($matches) > 0)
            return 'G'.$matches[0];
            //return $matches[0];
        else
            throw new ParseException('Invalid mode G-code specification', 0, null, $line);
    }
}