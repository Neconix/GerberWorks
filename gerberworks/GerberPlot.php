<?php

/**
 * @author Neconix (prostoe@gmail.com)
 */

namespace gerberworks;


class GerberPlot
{
    public $DPMM = 20;

    public $ImageWidth;
    public $ImageHeight;

    public $ZeroX;
    public $ZeroY;

    public $Xmax;
    public $Xmin;

    public $Ymax;
    public $Ymin;


    /**
     * @var bool true - отображать координаты некорректных точек
     */
    public $PrintBadCoord = true;

    private $_image;

    /**
     * @var GerberEngine
     */
    private $_gerber;

    /**
     * @var string Путь до файла чертежа
     */
    private $_plotFileName;

    /**
     * @var GerberCoord Предыдушая команда
     */
    private $_prevCmd = null;

    public function __construct(GerberEngine $engine, $fileName) {
        $this->_gerber = $engine;
        $this->_plotFileName = $fileName;
    }

    /**
     * Отрисовывает дорожки по Gerber-координатом с индикацией точек, не попадающих в сетку
     */
    public function DrawTracks() {
        $this->CalcImagesize();
        $this->_image = imagecreatetruecolor($this->ImageWidth+2, $this->ImageHeight+2);

        if ($this->_image === false)
            throw new PlotException("Не удалось создать чертеж размером: $this->ImageWidth x $this->ImageHeight");

        //$this->DrawZeroPoint();
        $this->DrawBadPoints();
        $this->DrawLines();

        if ($this->PrintBadCoord)
            $this->DrawCoordinateInfo();

        imagepng($this->_image, $this->_plotFileName, 1);

        imagedestroy($this->_image);
    }

    /**
     * Отмечает абсолютный центр координат
     */
    public function DrawZeroPoint() {
        $green = imagecolorallocate($this->_image, 0, 255, 0);
        $p = $this->ToPoint(0,0);
        $size = 10;
        //imageline($this->_image, $p['x'] - $size, $p['y'], $p['x'] + $size, $p['y'], $green);
        //imageline($this->_image, $p['x'], $p['y'] - $size, $p['x'], $p['y'] + $size, $green);
        imageline($this->_image, 0 - $size, $this->ImageHeight-1, 0 + $size, $this->ImageHeight-1, $green);
        imageline($this->_image, 0, $this->ImageHeight-1 - $size, 0, $this->ImageHeight-1 + $size, $green);
    }

    /**
     * Отображает точки, не попадающие в сетку окружностями
     */
    private function DrawBadPoints() {
        $red = imagecolorallocate($this->_image, 255, 0, 0);
        foreach($this->_gerber->Commands as $cmd) {
            if (($p = $this->IsBadPoint($cmd)) !== false) {
                $p = $this->ToPoint($cmd->X, $cmd->Y);
                $diam = $this->DPMM * 1;
                imagefilledellipse($this->_image, $p['x'], $p['y'], $diam, $diam, $red);
            }
        }
    }

    /**
     * Отображает текст координат для проблемных точек
     */
    private function DrawCoordinateInfo() {
        $fontColor = imagecolorallocate($this->_image, 94, 232, 230);

        foreach($this->_gerber->Commands as $cmd) {
            if (($p = $this->IsBadPoint($cmd)) !== false) {
                //Отображение координат на чертеже
                $xsign = !$cmd->XinGrid ? '!' : '';
                $ysign = !$cmd->YinGrid ? '!' : '';
                imagettftext($this->_image, 15, 25, $p['x'], $p['y'], $fontColor,
                    '/usr/share/fonts/truetype/ubuntu-font-family/Ubuntu-B.ttf', "($cmd->X{$xsign}, $cmd->Y{$ysign}) мм");
            }
        }
    }

    private function IsBadPoint($cmd) {
        if ($cmd instanceof GerberCoord
            && $cmd->GraphicState->InterpolationMode == GerberGraphicState::LINEAR_INTERPOLATION
            && $cmd->GraphicState->RegionMode == GerberGraphicState::REGION_MODE_OFF
            && $cmd->IsPath && !$cmd->InGrid) {
            return $this->ToPoint($cmd->X, $cmd->Y);
        } else {
            return false;
        }
    }

    private function DrawLines() {
        $white = imagecolorallocate($this->_image, 255,255,255);


        foreach($this->_gerber->Commands as $cmd) {
            if ($cmd instanceof GerberCoord) {
                if ($cmd->Action == 'D01') {
                    if ($this->_prevCmd != null) {
                        //Перемещение с засветкой
                        //Вычисляем начальную и конечную точку линии
                        $p1 = $this->ToPoint($this->_prevCmd->X, $this->_prevCmd->Y);
                        $p2 = $this->ToPoint($cmd->X, $cmd->Y);

                        //Толщина линии не менее 1 пикселя
                        $boldWidth = $this->DPMM / 10;
                        if ($boldWidth < 1)
                            $boldWidth = 1;

                        $this->ImageBoldLine($this->_image, $p1['x'], $p1['y'], $p2['x'], $p2['y'], $white, $boldWidth);
                        $this->_prevCmd = $cmd;
                    } else {
                        throw new \UnexpectedValueException('Нет данных по предыдущей команде');
                    }
                } else if ($cmd->Action == 'D02' || $cmd->Action == 'D03') {
                    //Перемещение без засветки, либо флеш
                    $this->_prevCmd = $cmd;
                }

            }
        }

    }

    function ImageBoldLine($resource, $x1, $y1, $x2, $y2, $Color, $BoldNess=2)
    {
        $center = round($BoldNess/2);
        for($i=0;$i<$BoldNess;$i++)
        {
            $a = $center-$i; if($a<0){$a -= $a;}
            for($j=0;$j<$BoldNess;$j++)
            {
                $b = $center-$j; if($b<0){$b -= $b;}
                $c = sqrt($a*$a + $b*$b);
                if($c<=$BoldNess)
                {
                    imageline($resource, $x1 +$i, $y1+$j, $x2 +$i, $y2+$j, $Color);
                }
            }
        }
    }

    private function ToPoint($x, $y) {
        $screenX = ($x - $this->Xmin) * $this->DPMM;
        $dy = $y - $this->Ymin;
        $screenY = $this->ImageHeight - $dy  * $this->DPMM;
        return ['x' => $screenX, 'y' => $screenY];
    }

    /**
     * Вычисляет размер изображения
     */
    public function CalcImagesize() {
        //Находим минимальные и максимальные значения координат
        $coordName = 'X';

        $filterFunc = function($c) use (&$coordName) {
            if ($c instanceof GerberCoord && in_array($c->Action, ['D01', 'D02', 'D03'])) {
                return $c->$coordName;
            }
            return null;
        };

        $filterNullsFunc = function ($a) { return !is_null($a); };

        $xlist = array_map($filterFunc, $this->_gerber->Commands);
        $xlist = array_filter($xlist, $filterNullsFunc);

        $coordName = 'Y';
        $ylist = array_map($filterFunc, $this->_gerber->Commands);
        $ylist = array_filter($ylist, $filterNullsFunc);

        $this->Xmin = min($xlist);
        $this->Xmax = max($xlist);
        $this->Ymin = min($ylist);
        $this->Ymax = max($ylist);

        $this->ImageWidth = ceil(($this->Xmax - $this->Xmin) * $this->DPMM);
        $this->ImageHeight = ceil(($this->Ymax - $this->Ymin) * $this->DPMM);
        $this->ZeroX = round ($this->ImageWidth / 2);
        $this->ZeroY = round ($this->ImageHeight / 2);
    }
}