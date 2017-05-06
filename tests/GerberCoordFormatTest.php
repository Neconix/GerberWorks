<?php

namespace gerberworks;

use \InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class GerberCoordFormatTest extends TestCase
{
    public function testOmitCoordinateZeros() {
        $coord = new GerberCoordFormat('%FSDAX44Y44*%');
        $this->assertEquals(false, $coord->OmitTrailZeros);
        $this->assertEquals(false, $coord->OmitLeadZeros);

        $coord = new GerberCoordFormat('%FSLAX44Y44*%');
        $this->assertEquals(false, $coord->OmitTrailZeros);
        $this->assertEquals(true, $coord->OmitLeadZeros);

        $coord = new GerberCoordFormat('%FSTAX44Y44*%');
        $this->assertEquals(true, $coord->OmitTrailZeros);
        $this->assertEquals(false, $coord->OmitLeadZeros);

        $this->expectException(InvalidArgumentException::class);
        new GerberCoordFormat('%FSXAX44Y44*%');

    }

    public function testCoordinateFormat() {
        $coord = new GerberCoordFormat('%FSDAX44Y44*%');
        $this->assertEquals(4, $coord->XIntCount);
        $this->assertEquals(4, $coord->XDecimalCount);
        $this->assertEquals(4, $coord->YIntCount);
        $this->assertEquals(4, $coord->YDecimalCount);
    }
}