<?php

/**
 * This file contains class the handle to file generated
 *
 * @author KoolPHP Inc (support@koolphp.net)
 * @link https://www.koolphp.net
 * @copyright KoolPHP Inc
 * @license https://www.koolreport.com/license#regular-license
 * @license https://www.koolreport.com/license#extended-license
 */

namespace koolreport\excel;

use \koolreport\core\Utility as Util;
use \PhpOffice\PhpSpreadsheet as ps;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

class ExportHandler
{
    protected $report;
    protected $setting = [];
    protected $widgetParams = [];
    public $sheetInfo = [
        'tablePositions' => [],
        'tableSheet' => [],
        'tableAutoId' => 0,
        'chartAutoId' => 0,
    ];

    public function __construct($report, $dataStores)
    {
        $this->report = $report;
        $this->dataStores = $dataStores;
    }

    public function setting($setting)
    {
        $this->setting = array_merge($this->setting, $setting);
    }

    public function setWidgetParams($name, $params)
    {
        $this->widgetParams[$name] = $params;
    }

    public function getWidgetParams($name)
    {
        return Util::get($this->widgetParams, $name, null);
    }

    public function getWidgetBuilder($type)
    {
        switch ($type) {
            case 'table':
                if (!isset($this->tableBuilder))
                    $this->tableBuilder = new TableBuilder();
                $Builder = $this->tableBuilder;
                break;
            case 'chart':
                if (!isset($this->chartBuilder))
                    $this->chartBuilder = new ChartBuilder();
                $Builder = $this->chartBuilder;
                break;
            case 'pivottable':
                if (!isset($this->pivottableBuilder))
                    $this->pivottableBuilder = new PivotTableBuilder();
                $Builder = $this->pivottableBuilder;
                break;
            case 'text':
            default:
                if (!isset($this->textBuilder))
                    $this->textBuilder = new TextBuilder();
                $Builder = $this->textBuilder;
        }
        $Builder->exportHandler = $this;
        return $Builder;
    }

    protected function getTemplateHtml($view)
    {
        $currentDir = dirname(Util::getClassPath($this->report));
        $excelTplFile = $currentDir . "/" . $view . ".excel.php";
        $viewTplFile = $currentDir . "/" . $view . ".view.php";
        if (is_file($excelTplFile)) {
            $oldActiveReport = (isset($GLOBALS["__ACTIVE_KOOLREPORT__"]))
                ? $GLOBALS["__ACTIVE_KOOLREPORT__"] : null;
            $GLOBALS["__ACTIVE_KOOLREPORT__"] = $this->report;
            ob_start();
            include($excelTplFile);
            $templateHtml = ob_get_clean();
            if ($oldActiveReport === null) {
                unset($GLOBALS["__ACTIVE_KOOLREPORT__"]);
            } else {
                $GLOBALS["__ACTIVE_KOOLREPORT__"] = $oldActiveReport;
            }
        } elseif (is_file($viewTplFile)) {
            $templateHtml = $this->report->render($view, true);
        } else {
            throw new \Exception("Could not found excel export template 
                file $viewTplFile or $excelTplFile");
        }
        return $templateHtml;
    }

    protected function setExcelMeta($spreadsheet, $properties)
    {
        $spreadsheet->getProperties()
            ->setCreator(Util::get($properties, "creator", "KoolReport"))
            ->setTitle(Util::get($properties, "title", ""))
            ->setDescription(Util::get($properties, "description", ""))
            ->setSubject(Util::get($properties, "subject", ""))
            ->setKeywords(Util::get($properties, "keywords", ""))
            ->setCategory(Util::get($properties, "category", ""));
    }

    protected function isJson($string)
    {
        $firstChar = mb_substr($string, 0, 1);
        $lastChar = mb_substr($string, -1);
        if (($firstChar !== "{" && $firstChar !== "[") ||
            ($lastChar !== "}" && $lastChar !== "]")
        ) {
            return false;
        }
        json_decode($string);
        $isJson = json_last_error() == JSON_ERROR_NONE;
        return $isJson;
    }

    protected function contentXmlToConfig($contentXml)
    {
        $contentStr = trim($contentXml->textContent);
        $content = $this->isJson($contentStr) ?
            json_decode($contentStr, true) : [
                'type' => 'text',
                'text' => $contentStr
            ];

        if (isset($content['name'])) {
            $content = $this->getWidgetParams($content['name']);
        }

        if (isset($content['dataSource']) && is_string($content['dataSource'])) {
            $content['dataSource'] =
                $this->report->dataStore($content['dataSource']);
        } elseif (isset($content['excelDataSource'])) {
            $content['dataSource'] = $content['excelDataSource'];
        }

        $contentAttrs = [];
        $attrs = $contentXml->attributes;
        foreach ($attrs as $attr) {
            $contentAttrs[$attr->nodeName] = $attr->nodeValue;
        }
        $content['attributes'] = $contentAttrs;

        return $content;
    }

    protected function sheetXmlToConfig($sheetXml)
    {
        $sheetConfig = [];
        $sheetConfig['name'] = $sheetXml->getAttribute('sheet-name');

        $xpath = $this->xpath;
        $contentXmls = $xpath->query("div", $sheetXml);
        $sheetConfig['contents'] = [];
        foreach ($contentXmls as $contentXml) {
            $sheetConfig['contents'][] = $this->contentXmltoConfig($contentXml);
        }
        return $sheetConfig;
    }

    protected function viewToConfig($view)
    {
        $config = [];
        $templateHtml = $this->getTemplateHtml($view);
        // $templateHtml = str_replace('<', '&lt;', $templateHtml);

        libxml_use_internal_errors(true);
        $doc = new \DomDocument();
        $doc->loadHTML($templateHtml);

        $properties = [];
        $metas = $doc->getElementsByTagName("meta");
        foreach ($metas as $meta) {
            $name = $meta->getAttribute('name');
            $value = $meta->getAttribute('content');
            $properties[$name] = $value;
        }
        $config['properties'] = $properties;

        $xpath = $this->xpath = new \DomXPath($doc);
        $sheetXmls = $xpath->query("*/div");
        $config['sheets'] = [];
        foreach ($sheetXmls as $i => $sheetXml) {
            $config['sheets'][] = $this->sheetXmlToConfig($sheetXml);
        }

        return $config;
    }

    protected function paramsToConfig($params)
    {
        $config = $params;

        $options = array();
        $dataStoreNames = Util::get($params, "dataStores", null);
        if (!isset($dataStoreNames) || !is_array($dataStoreNames))
            $exportDataStores = $this->dataStores;
        else {
            $options = array();
            $exportDataStores = array();
            foreach ($dataStoreNames as $k => $v) {
                if (isset($this->dataStores[$k])) {
                    $exportDataStores[$k] = $this->dataStores[$k];
                    $options[$k] = $v;
                } else if (isset($this->dataStores[$v]))
                    $exportDataStores[$v] = $this->dataStores[$v];
            }
        }
        $config['sheets'] = [];
        foreach ($exportDataStores as $name => $dataStore) {
            $type = isset($dataStore->meta()['pivotId']) ? 'pivottable' : 'table';
            $config['sheets'][] = [
                'name' => $name,
                'contents' => [
                    [
                        'type' => $type,
                        'dataSource' => $dataStore
                    ]
                ]
            ];
        }
        // Util::prettyPrint($config); exit;
        return $config;
    }

    protected function buildConfig($paramsOrView = [], $setting = [])
    {
        $this->setting($setting);
        if (is_string($paramsOrView)) {
            $view = $paramsOrView;
            $config = $this->viewToConfig($view);
        } elseif (is_array($paramsOrView)) {
            $params = $paramsOrView;
            $config = $this->paramsToConfig($params);
        }
        $this->config = array_merge($this->setting, $config);
    }

    protected function configToExcel()
    {
        $config = $this->config;
        $spreadsheet = $this->spreadsheet = new ps\Spreadsheet();
        $this->setExcelMeta($spreadsheet, Util::get($config, 'properties', []));

        $chartDataSheet = new ps\Worksheet\Worksheet($spreadsheet, 'chart_data');
        $spreadsheet->addSheet($chartDataSheet);
        if (Util::get($config, 'hideChartDataSheet', true)) {
            $chartDataSheet->setSheetState(ps\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
        }

        $rtl = Util::get($config, 'rtl');
        foreach ($config['sheets'] as $i => $sheetConfig) {
            if ($i === 0) {
                $sheet = $spreadsheet->getSheet(0);
                if ($rtl) $sheet->setRightToLeft(true);
            } else {
                $sheet = new ps\Worksheet\Worksheet($spreadsheet);
                if ($rtl) $sheet->setRightToLeft(true);
                $spreadsheet->addSheet($sheet, $i);
            }
            $sheetName = $sheetConfig['name'];
            if (empty($sheetName)) {
                $sheetName = "Sheet" . ($i + 1);
            }
            $sheet->setTitle($sheetName);
            foreach ($sheetConfig['contents'] as $contentConfig) {
                $type = Util::get($contentConfig, 'type', 'text');
                $widgetBuilder = $this->getWidgetBuilder($type);
                $widgetBuilder->saveContentToSheet($contentConfig, $sheet);
            }
        }

        //The last added sheet is active, we set it to the first one
        $spreadsheet->setActiveSheetIndex(0);

        $tmpFilePath = $this->getTempFolder() . "/" . Util::getUniqueId() . ".xlsx";
        $objWriter = ps\IOFactory::createWriter($spreadsheet, "Xlsx");
        $objWriter->setPreCalculateFormulas(false);
        $objWriter->setIncludeCharts(TRUE);
        $objWriter->save($tmpFilePath);

        return $tmpFilePath;
    }

    protected function configToBigSpreadsheet($fileType)
    {
        $config = $this->config;
        $writer = WriterEntityFactory::createWriter($fileType);
        $tmpFilePath = $this->getTempFolder() . "/" . Util::getUniqueId() . $fileType;
        $writer->openToFile($tmpFilePath);

        if ($fileType === 'csv') {
            $bom = Util::get($config, 'BOM', false);
            $writer->setShouldAddBOM($bom);
            $fieldDelimiter = Util::get($config, 'fieldSeparator', ',');
            $fieldDelimiter = Util::get($config, 'delimiter', $fieldDelimiter);
            $fieldDelimiter = Util::get($config, 'fieldDelimiter', $fieldDelimiter);
            $writer->setFieldDelimiter($fieldDelimiter);
        }

        foreach ($config['sheets'] as $i => $sheetConfig) {
            if (method_exists($writer, 'getCurrentSheet')) {
                $sheet = $i === 0 ?
                    $writer->getCurrentSheet() : $writer->addNewSheetAndMakeItCurrent();
                $sheetName = $sheetConfig['name'];
                if (empty($sheetName)) {
                    $sheetName = "Sheet" . ($i + 1);
                }
                $sheet->setName($sheetName);
            }

            foreach ($sheetConfig['contents'] as $contentConfig) {
                $type = Util::get($contentConfig, 'type', 'text');
                $widgetBuilder = $this->getWidgetBuilder($type);
                $widgetBuilder->saveContentToBigSpreadsheet($contentConfig, $writer);
            }
        }
        $writer->close();
        return $tmpFilePath;
    }

    public function exportToExcel($paramsOrView = [], $setting = [])
    {
        $this->buildConfig($paramsOrView, $setting);
        $tmpFilePath = $this->configToExcel();
        return new FileHandler($tmpFilePath);
    }

    public function exportToXLSX($paramsOrView = [], $setting = [])
    {
        $this->buildConfig($paramsOrView, $setting);
        $tmpFilePath = $this->configToBigSpreadsheet('xlsx');
        return new FileHandler($tmpFilePath);
    }

    public function exportToCSVWithSpout($paramsOrView = [], $setting = [])
    {
        $this->buildConfig($paramsOrView, $setting);
        $tmpFilePath = $this->configToBigSpreadsheet('csv');
        return new FileHandler($tmpFilePath);
    }

    public function exportToODS($paramsOrView = [], $setting = [])
    {
        $this->buildConfig($paramsOrView, $setting);
        $tmpFilePath = $this->configToBigSpreadsheet('ods');
        return new FileHandler($tmpFilePath);
    }

    public function exportToCSV($params = [], $setting = [])
    {
        $content = "";
        $options = array();
        if (is_string($params)) {
            $dsName = $params;
            $this->setting($setting);
            $exportDataStores = [$dsName => $this->report->datastore($dsName)];
            $options = [$dsName => $setting];
            $bom = Util::get($setting, "BOM", false);
        } elseif (is_array($params)) {
            $this->setting($params);
            $bom = Util::get($params, "BOM", false);
            $dataStoreNames = Util::get($params, "dataStores", null);
            if (is_string($dataStoreNames))
                $dataStoreNames = array_map('trim', explode(',', $dataStoreNames));
            if (!is_array($dataStoreNames))
                $exportDataStores = $this->dataStores;
            else {
                $options = array();
                $exportDataStores = array();
                foreach ($dataStoreNames as $k => $v) {
                    if (isset($this->dataStores[$k])) {
                        $exportDataStores[$k] = $this->dataStores[$k];
                        $options[$k] = $v;
                    } else if (is_string($v) && isset($this->dataStores[$v]))
                        $exportDataStores[$v] = $this->dataStores[$v];
                }
            }
        }
        foreach ($exportDataStores as $name => $ds) {
            $option = Util::get($options, $name, []);
            $colMetas = $ds->meta()['columns'];
            $optCols = Util::get($option, 'columns', array_keys($colMetas));
            $expColKeys = [];
            $expColLabels = [];
            $i = 0;
            foreach ($colMetas as $colKey => $colMeta) {
                $label = Util::get($colMeta, 'label', $colKey);
                foreach ($optCols as $col)
                    if ($col === $i || $col === $colKey || $col === $label) {
                        $expColKeys[] = $colKey;
                        $expColLabels[] = $label;
                    }
                $i++;
            }

            $delimiter = Util::get($option, 'fieldSeparator', ',');
            $delimiter = Util::get($option, 'delimiter', $delimiter);
            $delimiter = Util::get($option, 'fieldDelimiter', $delimiter);
            $showHeader = Util::get($option, "showHeader", true);
            if ($showHeader) $content .= implode($delimiter, $expColLabels) . "\n";

            $ds->popStart();
            while ($row = $ds->pop()) {
                foreach ($expColKeys as $colKey) {
                    $content .= Util::format($row[$colKey], $colMetas[$colKey])
                        . $delimiter;
                }
                $content = substr($content, 0, -1) . "\n";
            }
        }

        $tmpFilePath = $this->getTempFolder() . "/" . Util::getUniqueId() . ".csv";
        $file = fopen($tmpFilePath, 'w') or die('Cannot open file:  ' . $tmpFilePath);
        fwrite($file, ($bom) ? (chr(239) . chr(187) . chr(191) . $content) : ($content));
        fclose($file);

        return new FileHandler($tmpFilePath);
    }

    protected function getTempFolder()
    {
        $this->useLocalTempFolder = Util::get($this->setting, "useLocalTempFolder", false);
        if ($this->useLocalTempFolder) {
            // $path = dirname(__FILE__);
            $path = dirname($_SERVER['SCRIPT_FILENAME']);
            if (!is_dir(realpath($path) . "/tmp")) {
                mkdir(realpath($path) . "/tmp");
            }
            return realpath($path) . "/tmp";
        }
        return sys_get_temp_dir();
    }
}
