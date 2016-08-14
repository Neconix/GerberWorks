<?php
/**
 * @author Neconix (prostoe@gmail.com)
 */

namespace gerberworks;


class GerberGraphicState
{
    const LINEAR_INTERPOLATION = 'G01';
    const CIRCULAR_INTERPOLATION_CLOCKWISE = 'G02';
    const CIRCULAR_INTERPOLATION_COUNTERCLOCKWISE = 'G03';
    const IGNORE_DATA_BLOCK = 'G04';
    const REGION_MODE_ON = 'G36';
    const REGION_MODE_OFF = 'G37';
    const SINGLE_QUADRANT_MODE = 'G74';
    const MULTI_QUADRANT_MODE = 'G75';
    const POLARITY_CLEAR = 'C';
    const POLARITY_DARK = 'D';
    /**
     * @var string Holds interpolation mode.
     * Null by default (see 2.8 Gerber format specification)
     */
    public $InterpolationMode;
    public $QuadrantMode;
    /**
     * @var GerberApertureCmd Current aperture
     */
    public $CurrentAperture;
    /**
     * @var string Polarity mode, default is [[POLARITY_DARK]] (see 2.8 Gerber format specification)
     */
    public $Polarity = GerberGraphicState::POLARITY_DARK;
    /**
     * @var string Region mode, default is [[REGION_MODE_OFF]] (see 2.8 Gerber format specification)
     */
    public $RegionMode = GerberGraphicState::REGION_MODE_OFF;

    /**
     * @var array Available apertures
     */
    public $Apertures;
}