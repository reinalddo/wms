<?php

use \koolreport\core\Utility as Util;
?>
<h1ml>

    <head>
        <title>Test visual query</title>
        <style>

        </style>

    </head>

    <body>
        <form method='post' class2='container box-container'>
            <h1>Test visual query</h1>
            <div>
                <?php
                // Util::prettyPrint($_POST);
                \koolreport\visualquery\VisualQuery::create(array(
                    "name" => "visualquery1",
                    // "themeBase" => "bs4",
                    "schema" => "salesSchema",
                    "defaultValue" => [
                        "selectDistinct" => true,
                        "selectTables" => [
                            "orders",
                            "orderdetails",
                            "products",
                        ],
                        "selectFields" => [
                            "products.productName",
                        ],
                        "filters" => [
                            [
                                "field" => "orders.orderDay", 
                                "operator" => ">", 
                                "value1" => "2001-01-01", 
                                "value2" => "", 
                                "logic" => "and",
                                "toggle" => true,
                            ],
                            ["products.productCode", "btw", "2", "998", "and", "toggle" => false],
                            "(",
                            // ["products.productName", "nbtw", "1", "", "and"],
                            ["products.productName", "<>", "a", "", "and"],
                            ["products.productName", "nin", "a", "", "and"],
                            ")",
                            // ["products.productName", "null", "a", "", "or"],
                            ["products.productName", "nnull", "a", "", "and"],
                            ["products.productName", "ctn", "a", "", "and"],
                            ["orders.orderMonth", "btw", 0, 12, "and"],
                        ],
                        "groups" => [
                            [
                                "field" => "orderdetails.cost", 
                                "aggregate" => "sum", 
                                "toggle" => true
                            ]
                        ],
                        "havings" => [
                            "(",
                            [
                                "field" => "sum(orderdetails.cost)", 
                                "operator" => ">", 
                                "value1" => "10000", 
                                "value2" => "", 
                                "logic" => "and",
                                "toggle" => true,
                            ],
                            ")",
                            // ["products.productName", "<>", "a", "", "or"],
                        ],
                        "sorts" => [
                            [
                                "field" => "sum(orderdetails.cost)", 
                                "direction" => "desc", 
                                "toggle" => true
                            ],
                            ["products.productName", "desc", "toggle" => false]
                        ],
                        "limit" => [
                            "offset" => 5,
                            "limit" => 15,
                            "toggle" => false,
                        ]
                    ],
                    "activeTab" => "filters",
                ));
                ?>
            </div>
            <div style='padding: 15px;'>
                <!-- <input type='text' name='input1' value='value1' /> -->
                <button type='submit' class='btn btn-primary'>Submit</button>
            </div>
            <style>
                pre {
                    overflow-x: auto;
                    white-space: pre-wrap;
                    white-space: -moz-pre-wrap;
                    white-space: -pre-wrap;
                    white-space: -o-pre-wrap;
                    word-wrap: break-word;
                }
            </style>
            <div style="margin: 30px; width:800px">
                <pre style="width:800px"><?php echo $this->query; ?></pre>
            </div>
            <div>
                <?php
                // print_r($this->dataStore('vqDS')->meta());
                \koolreport\charttable\ChartTable::create(array(
                    "name" => "charttable1",
                    "dataSource" => $this->dataStore('vqDS'),
                    // "columns" => ["Quantity", "Product Name"],
                    "options" => [
                        // "order" => [[1, 'desc']],
                        // "ordering" => false,
                        // "buttons" => [],
                    ]
                ));
                ?>
            </div>
            <div style='padding: 15px;'>
                <?php
                // echo "queryParams = "; Util::prettyPrint($this->queryParams); echo "<br>"; 
                ?>
                <?php
                // Util::prettyPrint($_POST); echo "<br>";
                ?>
            </div>
        </form>
    </body>
</h1ml>