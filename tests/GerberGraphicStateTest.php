<?php

namespace gerberworks;


use PHPUnit\Framework\TestCase;

class GerberGraphicStateTest extends TestCase
{
    public function testGraphicState() {
        $state = new GerberGraphicState();

        //Invalid mode test
        GerberMode::UpdateGraphicState($state, 'G77*');
        $this->assertEquals(new GerberGraphicState(), $state);

        //Aperture select
        GerberMode::UpdateGraphicState($state, 'G54D75*');
        $this->assertEquals(new GerberGraphicState(), $state);

        //LinearInterpolation
        GerberMode::UpdateGraphicState($state, 'G01X31525D01*');
        $this->assertEquals(GerberGraphicState::LINEAR_INTERPOLATION, $state->InterpolationMode);

        //CircularInterpolation
        GerberMode::UpdateGraphicState($state, 'G02X31525D01*');
        $this->assertEquals(GerberGraphicState::CIRCULAR_INTERPOLATION_CLOCKWISE, $state->InterpolationMode);

        //CircularInterpolation
        GerberMode::UpdateGraphicState($state, 'G03X20650Y49700J-1500D01*');
        $this->assertEquals(GerberGraphicState::CIRCULAR_INTERPOLATION_COUNTERCLOCKWISE, $state->InterpolationMode);

        //RegionMode
        GerberMode::UpdateGraphicState($state, 'G36*');
        $this->assertEquals(GerberGraphicState::REGION_MODE_ON, $state->RegionMode);

        GerberMode::UpdateGraphicState($state, 'G37*');
        $this->assertEquals(GerberGraphicState::REGION_MODE_OFF, $state->RegionMode);

        //SingleQuadrantMode
        GerberMode::UpdateGraphicState($state, 'G74*');
        $this->assertEquals(GerberGraphicState::SINGLE_QUADRANT_MODE, $state->QuadrantMode);

        //MultiQuadrantMode
        GerberMode::UpdateGraphicState($state, 'G75*');
        $this->assertEquals(GerberGraphicState::MULTI_QUADRANT_MODE, $state->QuadrantMode);

        //Comment block
        $state = new GerberGraphicState();
        GerberMode::UpdateGraphicState($state, 'G04 CAM350/DFMSTREAM V10.7 (Build 560) Date:  Thu May 12 11:43:28 2016 *');
        $this->assertEquals(new GerberGraphicState(), $state);
    }
}
