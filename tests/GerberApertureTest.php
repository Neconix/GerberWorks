<?php

/**
 * @author Neconix (prostoe@gmail.com)
 */

namespace gerberworks\tests;

use gerberworks\GerberApertureCmd;
use gerberworks\GerberEngine;
use PHPUnit\Framework\TestCase;

class GerberApertureTest extends TestCase
{
    public function testCircleApertures() {
        $a = new GerberApertureCmd('%ADD14C,0.62499*%');
        $this->assertEquals(0.62499, $a->Params['D']);

        $a = new GerberApertureCmd('%ADD15C,3.12499*%');
        $this->assertEquals(3.12499, $a->Params['D']);

        $a = new GerberApertureCmd('%ADD15C,3.12499X0.124*%');
        $this->assertEquals(3.12499, $a->Params['D']);
        $this->assertEquals(0.124, $a->Params['H']);

        $a = new GerberApertureCmd('%ADD15C,.12X.03*%');
        $this->assertEquals(0.12, $a->Params['D']);
        $this->assertEquals(0.03, $a->Params['H']);

        $a = new GerberApertureCmd('%ADD2524C,1.49548*%');
        $this->assertEquals(1.49548, $a->Params['D']);
        $this->assertEquals('C', $a->Type);

        echo '[ OK ] Circle aperture test'.PHP_EOL;
    }

    public function testRectApertures() {
        $a = new GerberApertureCmd('%ADD18R,2.50000*%');
        $this->assertEquals(2.5, $a->Params['X']);

        $a = new GerberApertureCmd('%ADD18R,2.50000X2.50000*%');
        $this->assertEquals(2.5, $a->Params['X']);
        $this->assertEquals(2.5, $a->Params['Y']);

        $a = new GerberApertureCmd('%ADD18R,2.50000X2.50000X0.3*%');
        $this->assertEquals(2.5, $a->Params['X']);
        $this->assertEquals(2.5, $a->Params['Y']);
        $this->assertEquals(0.3, $a->Params['H']);
        $this->assertEquals('R', $a->Type);
        $this->assertEquals('18', $a->Code);
        echo '[ OK ] Rect aperture test'.PHP_EOL;
    }

    public function testObroundApertures() {
        $a = new GerberApertureCmd('%ADD22O,0.046*%');
        $this->assertEquals(0.046, $a->Params['X']);

        $a = new GerberApertureCmd('%ADD22O,0.046X0.026*%');
        $this->assertEquals(0.046, $a->Params['X']);
        $this->assertEquals(0.026, $a->Params['Y']);

        $a = new GerberApertureCmd('%ADD22O,0.046X0.026X0.019*%');
        $this->assertEquals(0.046, $a->Params['X']);
        $this->assertEquals(0.026, $a->Params['Y']);
        $this->assertEquals(0.019, $a->Params['H']);
        $this->assertEquals('O', $a->Type);
        $this->assertEquals('22', $a->Code);
        echo '[ OK ] Obround aperture test'.PHP_EOL;
    }

    public function testUnknownAperture() {
        $a = new GerberApertureCmd('%ADD15P,3.12499*%');
        $this->assertEquals('U', $a->Type);

        $a = new GerberApertureCmd('%ADD71rect1x1*%');
        $this->assertEquals('U', $a->Type);

        echo '[ OK ] Unknown aperture test'.PHP_EOL;
    }

}