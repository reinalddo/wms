<?php

/**
 * This file contains BarCode widget class 
 *
 * @author KoolPHP Inc (support@koolphp.net)
 * @link https://www.koolphp.net
 * @copyright KoolPHP Inc
 * @license https://www.koolreport.com/license
 */

/*
  Usage

  BarCode::create(array(
    "format"=>"html", //"svg", "png", "jpg"
    "value"=>"081231723897",
    "type"=>"TYPE_CODE_128",
    "widthFactor"=>2,
    "height"=>30,
    "color"=>"black", //"black" for html and svg, array(0, 0, 0) for jpg and png
  ));
*/



namespace koolreport\barcode;

use \koolreport\core\Widget;
use \koolreport\core\Utility as Util;
use \Picqer\Barcode\BarcodeGeneratorHTML;
use \Picqer\Barcode\BarcodeGeneratorSVG;
use \Picqer\Barcode\BarcodeGeneratorPNG;
use \Picqer\Barcode\BarcodeGeneratorJPG;


class BarCode extends Widget
{
    protected $format;
    protected $value;
    protected $type;
    protected $widthFactor;
    protected $height;
    protected $color;

    public function version()
    {
        return "2.0.0";
    }

    protected function onInit()
    {
    }

    public static function getBarCode($params)
    {
        $format = strtoupper(Util::get($params, "format", "jpg"));
        $value = Util::get($params, "value", "");
        $type = Util::get($params, "type", "TYPE_CODE_128");
        $widthFactor = Util::get($params, "widthFactor", 2);
        $height = Util::get($params, "height", 30);
        $color = Util::get(
            $params,
            "color",
            $format === "HTML" || $format === "SVG" ? "black" : array(0, 0, 0)
        );
        $barcodeClass = "\Picqer\Barcode\BarcodeGenerator$format";
        $generator = new $barcodeClass();
        $barcode = $generator->getBarcode(
            $value,
            constant("\Picqer\Barcode\BarcodeGenerator::" . $type),
            $widthFactor,
            $height,
            $color
        );
        return $barcode;
    }

    public static function writeFile($params)
    {
        $barcode = self::getBarCode($params);
        $format = strtolower(Util::get($params, "format", "png"));
        $path = Util::get($params, "path", __DIR__ . "/barcode.$format");
        file_put_contents($path, $barcode);
    }

    protected function onRender()
    {
        $barcode = self::getBarCode($this->params);
        $format = strtoupper(Util::get($this->params, "format", "jpg"));
        if ($format === "HTML" || $format === "SVG")
            echo $barcode;
        else if ($format === "PNG" || $format === "JPG")
            echo "<img src='data:image/$format;base64," . base64_encode($barcode) . "'>";
    }
}
