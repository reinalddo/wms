<?php
include "MyReport.php";
$report = new MyReport;
$report->run();
//$report->exportToExcel('MyReportExcel')->toBrowser("Reporte Kardex.xlsx");

$report->run()
->exportToXLSX(
    'MyReportSpreadsheet', ['useLocalTempFolder' => true]
)
->toBrowser("Reporte Kardex.xlsx");

/*
    array(
    "dataStores" => array(
            'MyReportSpreadsheet' => array(
                "columns"=>array(
                    'Fecha', 'Clave', 'Articulo', 'Lote|Serie', 'Caducidad', 'Pallet|Contenedor', 'License Plate (LP)', 'Movimiento', 'Origen', 'Destino', 'BL', 'Cantidad', 'Usuario'),
            )
        )
    )

$report->exportToCSV(
    array(
        "dataStores" => array(
            "ordersExport" => [
                "separator" => ";", // default separator = "," i.e. comma
                "enclosure" => "\"", // default general enclosure = "" i.e. empty string
                "enclosure" => ["(", ")"], // all enclosure property could be a 2 element array
                "typeEnclosures" => [
                    "string" => "\"", // default string enclosure is general enclosure
                    "date" => "\"", // default date enclosure is general enclosure
                    "datetime" => "\"", // default datetime enclosure is general enclosure
                    "number" => "", // default number enclosure = "" i.e. empty string
                    "boolean" => "", // default boolean enclosure = "" i.e. empty string
                ],
                'nullEnclosure' => "", // default = "" i.e empty string
                'nullString' => "NULL", // default = false i.e empty string for null value
                'useColumnFormat' => 1, // default = 1, set = 0 to increase export speed
                'useEnclosureEscape' => 1, // default = 1, set = 0 to increase export speed
                'useTypeEnclosure' => 1, // default = 1, set = 0 to increase export speed     
                "escape" => "\\", // if escape is empty/undefined, double enclosures will be used
                "eol" => "\n", // define End of line character, default eol is "\n"
                "columns"=>array(
					"customerName",
					"productName",
					"productLine",
					"orderDate",
					"orderMonth",
					"orderYear",
					"orderQuarter",
					"dollar_sales" => [
                        "type" => "number",
                        "enclosure" => ["<", ">"], // to apply custom column enclosure "useCustomColumnEnclosure" must be true
                        "headerEnclosure" => "\"",
                        "nullEnclosure" => "",
                        "nullString" => "nULL",
                        "enclosureEscape" => "\"",
                    ]
				),  
                'useCustomColumnEnclosure' => 1, // default = 0
                'useCustomColumnNullString' => 1, // default = 0
                'useCustomColumnEnclosureEscape' => 1, // default = 0             
            ],
        ),

        "useLocalTempFolder" => true,
        "BOM" => false, // default bom = false
        "buffer" => 1000 // unit: KB ~ 1000 bytes. Default buffer = 1000 KB
    )
)
->toBrowser("Reporte Kardex.csv");
*/
/*
$report->exportToODS(
    'MyReportSpreadsheet', ['useLocalTempFolder' => true]
)
->toBrowser("Reporte Kardex.xlsx");
*/