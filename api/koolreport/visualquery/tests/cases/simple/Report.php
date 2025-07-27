<?php

require_once "../../../../core/autoload.php";

use \koolreport\core\Utility as Util;

class Report extends \koolreport\KoolReport
{
    use \koolreport\bootstrap4\Theme;
    // use \koolreport\clients\Bootstrap;
    use \koolreport\visualquery\Bindable;

    public function defineSchemas()
    {
        return [
            "salesSchema" => array(
                "tables" => [
                    "customers" => array(
                        "{meta}" => [
                            "alias" => "Table Customers"
                        ],
                        "customerNumber" => array(
                            "alias" => "Customer Number",
                        ),
                        "customerName" => array(
                            "alias" => "Customer Name",
                        ),
                    ),
                    "orders" => array(
                        "{meta}" => [
                            "alias" => "Table Orders"
                        ],
                        "orderNumber" => array(
                            "alias" => "Order Number"
                        ),
                        "orderDay" => array(
                            "alias" => "Order Day",
                            "expression" => "date(orderDate)",
                            "type" => "date",
                        ),
                        "orderDate" => array(
                            "alias" => "Order Date",
                            "type" => "datetime",
                        ),
                        "orderMonth" => [
                            "expression" => "month(orderDate)",
                            "type" => "number",
                        ]
                        // "customerNumber"=>array(
                        //    "alias"=>"Customer Number"
                        // )
                    ),
                    "orderdetails" => array(
                        "{meta}" => [
                            "alias" => "Order Details"
                        ],
                        // "orderNumber"=>array(
                        //     "alias"=>"Order Number"
                        // ),
                        "quantityOrdered" => array(
                            "alias" => "Quantity",
                            "type" => "number",
                        ),
                        "priceEach" => array(
                            "alias" => "Price Each",
                            "type" => "number",
                            "decimal" => 2,
                            "prefix" => "$",
                        ),
                        // "productCode"=>array(
                        //     "alias"=>"Product Code"
                        // ),
                        "cost" => [
                            // "expression" => "orderdetails.quantityOrdered * orderdetails.priceEach",
                            "expression" => "quantityOrdered * priceEach",
                            "alias" => "Order Cost",
                            "type" => "number",
                            "decimal" => 2,
                            "prefix" => "$",
                        ]
                    ),
                    "products" => array(
                        "{meta}" => [
                            "alias" => "Table Products"
                        ],
                        "productCode" => array(
                            "alias" => "Product Code"
                        ),
                        "productName" => array(
                            // "alias" => "Product Name"
                        ),
                    )
                ],
                "relations" => [
                    ["orders.customerNumber", "leftjoin", "customers.customerNumber"],
                    ["orders.orderNumber", "join", "orderdetails.orderNumber"],
                    ["orderdetails.productCode", "leftjoin", "products.productCode"],
                ],
            ),
            "separator" => "."
        ];
    }

    function settings()
    {
        return array(
            "dataSources" => array(
                "automaker" => array(
                    "connectionString" => "mysql:host=localhost;dbname=automaker",
                    "username" => "root",
                    "password" => "",
                    "charset" => "utf8"
                ),
                'mysql' => [
                    'host' => 'localhost',
                    'username' => 'root',
                    'password' => '',
                    'dbname' => 'automaker',
                    'class' => '\koolreport\datasources\MySQLDataSource',
                ],
            )
        );
    }

    protected function setup()
    {
        $params = Util::get($this->queryParams, 'visualquery1');
        // echo "params = "; Util::prettyPrint($params);
        $qb = $this->paramsToQueryBuilder($params);
        // $arr = $qb->toArray();
        // echo "qb array="; Util::prettyPrint($arr);
        $this->query = $params ? 
            $qb->toMySQL() : "select * from customers where 1=0";
        $this->paramQuery = $params ? 
        // $qb->toMySQL(['useSQLParams' => "question"]) : "select * from customers where 1=0";
            $qb->toMySQL(['useSQLParams' => "name"]) : "select * from customers where 1=0";
        $this->sqlParams = $qb->getSQLParams();
        // echo "paramQuery="; echo $this->paramQuery; echo "<br>";
        // Util::prettyPrint($this->sqlParams);

        $this
            ->src('automaker')
            // ->query($this->query)
            ->query($this->paramQuery)
            ->params($this->sqlParams)
            ->pipe(new \koolreport\processes\ColumnMeta([
                "Order Number" => [
                    "type" => "string"
                ],
                "orderMonth" => [
                    "type" => "string"
                ],
            ]))
            ->pipe($this->dataStore('vqDS'));
    }
}
