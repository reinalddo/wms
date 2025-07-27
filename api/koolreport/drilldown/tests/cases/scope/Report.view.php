<?php
    use \koolreport\drilldown\DrillDown;
    use \koolreport\widgets\google\ColumnChart;
    use \koolreport\widgets\koolphp\Table;
    use \koolreport\inputs\TextBox;
?>
<html>
    <head>
        <title>Advanced DrillDown</title>
    </head>
    <body>

    <form method="post" style="margin-top:30px;">
        <?php
            TextBox::create(array(
                "name"=>"textBox"
            ));
        ?>
        <button type="submit" class="btn btn-success">Submit</button>
    </form>
    <?php
    DrillDown::create(array(
        "name"=>"mydrilldown",
        "title"=>"Sale By Location",
        "scope"=>$this->params,
        "levels"=>array(
            array(
                "title"=>function($params,$scope)
                {
                    return "All Countries";
                },
                "widget"=>array(\koolreport\d3\ColumnChart::class,array(
                    "dataSource"=>function($params,$scope)
                    {
                        return $this->src("automaker")->query("
                            SELECT country, sum(amount) as sale_amount
                            FROM
                                payments
                            JOIN
                                customers
                            ON
                                customers.customerNumber = payments.customerNumber
                            GROUP BY country
                        ");
                    },                
                )),
                // "widget"=>array(ColumnChart::class,array(
                    // "dataSource"=>function($params,$scope)
                    // {
                    //     return $this->src("automaker")->query("
                    //         SELECT country, sum(amount) as sale_amount
                    //         FROM
                    //             payments
                    //         JOIN
                    //             customers
                    //         ON
                    //             customers.customerNumber = payments.customerNumber
                    //         GROUP BY country
                    //     ");
                    // },
                // )),
                // "content"=>function($params,$scope)
                // {
                //     ColumnChart::create(array(
                //         "dataSource"=>$this->src("automaker")->query("
                //             SELECT country, sum(amount) as sale_amount
                //             FROM
                //                 payments
                //             JOIN
                //                 customers
                //             ON
                //                 customers.customerNumber = payments.customerNumber
                //             GROUP BY country
                //         "),
                //         "clientEvents"=>array(
                //             "itemSelect"=>"function(params){
                //                 mydrilldown.next({country:params.selectedRow[0]});
                //             }"
                //         )
                //     ));
                // }
            ),
            array(
                "title"=>function($params,$scope)
                {
                    return $params["country"].$scope["textBox"];
                },
                "widget"=>array(Table::class,array(
                    "dataSource"=>function($params,$scope)
                    {
                        return $this->src("automaker")->query("
                            SELECT city, sum(amount) as sale_amount
                            FROM
                                payments
                            JOIN
                                customers
                            ON
                                customers.customerNumber = payments.customerNumber
                                AND
                                country=:country
                            GROUP BY 
                                city                        
                        ")->params(array(
                            ":country"=>$params["country"]
                        ));
                    }
                )),
                // "content"=>function($params,$scope)
                // {
                //     Table::create(array(
                //         "dataSource"=>$this->src("automaker")->query("
                //             SELECT city, sum(amount) as sale_amount
                //             FROM
                //                 payments
                //             JOIN
                //                 customers
                //             ON
                //                 customers.customerNumber = payments.customerNumber
                //                 AND
                //                 country=:country
                //             GROUP BY 
                //                 city                        
                //         ")->params(array(
                //             ":country"=>$params["country"]
                //         ))
                //     ));
                // },
            )
        )
    ))
    ?>
    </body>
</html>