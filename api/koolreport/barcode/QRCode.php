<?php

/**
 * This file contains QRCode widget class 
 *
 * @author KoolPHP Inc (support@koolphp.net)
 * @link https://www.koolphp.net
 * @copyright KoolPHP Inc
 * @license https://www.koolreport.com/license
 */


/*
  Usage
  
    QRCode::create(array(
        "format" => "svg", //"png", "jpg"
        "value"=>"Test QRCode",
        "size"=>150,
        "foregroundColor"=>array(0, 0, 0),
        "backgroundColor"=>array(255, 255, 255),
    ));

    QRCode::writeFile(array(
        "format" => "png", //png, svg, eps
        "value"=>"http://koolreport.com",
        "size"=>150,
        "foregroundColor"=>array(0, 0, 0),
        "backgroundColor"=>array(255, 255, 255),
        "path" => "myQRCode.png",
    ));
*/

namespace koolreport\barcode;

use koolreport\core\Widget;
use koolreport\core\Utility as Util;

class QRCode extends Widget
{
    protected $format;
    protected $value;
    protected $size;
    protected $foregroundColor;
    protected $backgroundColor;
    public function version()
    {
        return "2.0.0";
    }
    
    protected function onInit()
    {
    }

    public static function getQRCode($params)
    {
        $value = Util::get($params, "value", "");
        $size = Util::get($params, "size", 150);
        $foregroundColor = Util::get($params, "foregroundColor", array(0, 0, 0));
        $backgroundColor = Util::get($params, "backgroundColor", array(255, 255, 255));
        $qrCode = new \Endroid\QrCode\QrCode($value);
        $qrCode->setSize($size);
        $f = $foregroundColor;
        $b = $backgroundColor;
        $qrCode->setForegroundColor(['r' => $f[0], 'g' => $f[1], 'b' => $f[2]]);
        $qrCode->setBackgroundColor(['r' => $b[0], 'g' => $b[1], 'b' => $b[2]]);
        return $qrCode;
    }

    public static function writeFile($params)
    {
        $qrCode = self::getQRCode(($params));    
        $format = strtolower(Util::get($params, "format", "png"));
        $qrCode->setWriterByName($format);
        $path = Util::get($params, "path", __DIR__ . "/qrcode.png");
        $qrCode->writeFile($path);
    }

    protected function onRender()
    {
        $qrCode = self::getQRCode($this->params);
        $format = strtoupper(Util::get($this->params, "format", "png"));
        if ($format === "SVG") {
            $svg = new \Endroid\QrCode\Writer\SvgWriter();
            echo $svg->writeString($qrCode);
        } else if ($format === "PNG" || $format === "JPG")
            echo "<img src='data:image/$format;base64," . base64_encode($qrCode->writeString()) . "'>";;
    }
}
