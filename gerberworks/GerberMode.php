<?php
/**
 * @author Neconix (prostoe@gmail.com)
 */

namespace gerberworks;


class GerberMode extends GerberCommand
{
    public function __construct($line)
    {
        parent::__construct($line);
    }

    /**
     * @param GerberGraphicState $graphicState
     * @param $line
     */
    public static function UpdateGraphicState(GerberGraphicState &$graphicState, $line) {
        preg_match('/G(\d){2}/', $line, $matches);
        $mode = $matches[0];
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
                //Команда выбора апертуры
                if (preg_match('/(?<=D)\d{2}/', $line, $matches) === 1) {
                    $apertureCode = $matches[0];
                    $graphicState->CurrentAperture = $graphicState->Apertures[$apertureCode];
                } else
                    throw new \InvalidArgumentException('Неверная команда выбора апертуры');
                break;
            default:
                ;
                //$this->Code = $mode;
        }
    }
}