# Visual Query

`koolreport/visualquery` is package to build query using UI.

## Installation

1. Download and unzip the zipped file.
2. Copy the folder `visualquery` into `koolreport` folder
3. Reference to the Bindable trait by the trait name`\koolreport\visualquery\Bindable`
4. Reference to the VisualQuery input widget by the classname`\koolreport\visualquery\VisualQuery`

## Requirement

Since version 2.0.0, VisualQuery no longer requires the Inputs package to be installed as well.

## Usage

### VisualQuery

#### Bindable

To use `VisualQuery` widget in a report view, you need to use its `Bindable` trait in the report class. For example:

```
//MyReport.php
class Report extends \koolreport\KoolReport
{
    use \koolreport\visualquery\Bindable;
    ...
```

#### defineSchemas (version >= 2.0.0)

Beside the `Bindable` trait you also need implement method defineSchemas in your report class. The returned schemas are to be used by VisualQuery widgets in the report view:

```
//Myreport.php
class Report extends \koolreport\KoolReport
{
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
                    ),
                ],
                "relations" => [
                    ["orders.customerNumber", "leftjoin", "customers.customerNumber"]
                ],
            ),
            "separator" => "."
        ];
    }
```
A schema can has two main properties which are `tables` and `relations`. `Tables` is used for defining tables and their metadata, fields and the fields' information like type, alias, expression, prefix, suffix. `relations` is used to for defining join relations between tables.

When defining schemas you could set a `separator` property which acts as a separator string between tables, views and fields. Its default value is a period mark ".".

After that you could add VisualQuery widget in the report view using one of the defined schemas:

```
//MyReport.view.php
<?php
    \koolreport\visualquery\VisualQuery::create(array(
        "name" => "visualquery1",
        "schema" => "salesSchema",
        ...
```

#### defaultValue

You could set default values for `VisualQuery` widget via `defaultValue` property, i.e which tables, fields, filters, groups, sorts, limit, offset users will see upon `VisualQuery` loads for the first time:

```
\koolreport\visualquery\VisualQuery::create(array(
    "name" => "visualquery1",
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
            "(",
            [
                "field" => "orders.orderDay", 
                "operator" => ">", 
                "value1" => "2001-01-01", 
                "value2" => "", 
                "logic" => "and",
                "toggle" => true,
            ],
            ")",
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
                "value1" => "100", 
                "value2" => "", 
                "logic" => "and",
                "toggle" => true,
            ],
            ")",
        ],
        "sorts" => [
            [
                "field" => "sum(orderdetails.cost)", 
                "direction" => "desc", 
                "toggle" => true
            ],
        ],
        "limit" => [
            "offset" => 5,
            "limit" => 15,
            "toggle" => false,
        ]
    ],
    ...
```

`selectDistinct` is a boolean value indicating whether the query is select distinct or select.

`selectTables` is an array of table names. 

`selectFields` is an array of table fields. 

`filters` is an array of filter considitons. Each filter is an array including a field, a filter operator, filter value 1, filter value 2 and a logical operator ("and", "or"). The list of filter operators is:

|Filter operator   |Meaning   |
|---|---|
| =  | equals to  |
|  <> |  not quals to |
|  > | greater than  |
|  >= | greater than or equals to  |
|  < | less than   |
| <=>  | less than or equals to  |
| btw  |  between |
| nbtw  |  not between |
|  ctn | contains  |
| nctn  | not contains  |
|  null | is null  |
| nnull  |  is not null |
| in  | in  |
|  nin |  not in |

`groups` is an array of groups. Each group is an array including a field and an aggregated operator: "sum", "count", "avg", "min" or "max" like a database aggregate.

`havings` is an array of having conditions. Each having is similar to filter except that the having field must be existed in the selected fields.

Since version 2.0.0, `filters`, `groups`, and `havings` can contain brackets. Remember to open and close brackets orderly and correctly.

`sorts` is an array of sorts. Each sort is an array including a field and a direction: "asc" or "desc".

`offset` is a number indicating the offset of the first row to be retrieved. `limit` is the total number of expected returned rows.

#### activeTab (version >= 2.0.0)

You could set the default active tab when initially loading VisualQuery with the property `activeTab`:

```
\koolreport\visualquery\VisualQuery::create(array(
    ...
    "activeTab" => "filters", //"tables", "filters", "groups", "sorts", "limit"

```

#### `queryParams` and `paramsToQueryBuilder`

When using `Bindable` together with `VisualQuery` widget, your report has access to a property called `queryParams` which contains an array value of the VisualQuery widget. 

With that value you could create a QueryBuilder object using `paramsToQueryBuilder` method. The query builder in turn could return a sql query. 

Or with the array and its defined format you could convert it directly to a sql query if you want.

Here's an example of using `VisualQuery` with `QueryBuilder` package to produce a sql query:

```
//MyReport.php
class Report extends \koolreport\KoolReport
{
    use \koolreport\visualquery\Bindable;
    ...
    protected function setup()
    {
        if (isset($this->queryParams['visualquery1'])) {
            $vqParams =  $this->queryParams['visualquery1'];
            $queryBuilder = $this->paramsToQueryBuilder($vqParams);
            $queryStr = $queryBuilder->toMySQL();

            //For security reasons, it's better to get a parameterized query and its params,
            //then use those to retrieve data
            $queryStr = $qb->toMySQL(['useSQLParams' => "name"]) ;
            $sqlParams = $qb->getSQLParams();
        } else {
            $queryStr = "select * from myTable where 1=0";
            $sqlParams = [];
        }
        
        $this
        ->src('myDataSource')
        ->query($queryStr)
        ->params($sqlParams)
        ->pipe($this->dataStore('myDataStore'));
    ...

//MyReport.view.php
<?php
    \koolreport\visualquery\VisualQuery::create(array(
        "name" => "visualquery1",
        ...
```

## Support

Please use our forum if you need support, by this way other people can benefit as well. If the support request need privacy, you may send email to us at __support@koolreport.com__.