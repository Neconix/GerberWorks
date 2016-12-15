<?php

/**
 * @author Neconix (prostoe@gmail.com)
 */

namespace gerberworks\tests;

use gerberworks\GerberEngine;
use PHPUnit\Framework\TestCase;

class GerberFileloadTest extends TestCase
{
    public function testLoadTestFile1() {
        $testfile = __DIR__.'/_test-files/plate1.gbr';
        $engine = new GerberEngine();
        $engine->Parse($testfile);

        echo '[ OK ] Parse test file: '.$testfile.PHP_EOL;
    }

}