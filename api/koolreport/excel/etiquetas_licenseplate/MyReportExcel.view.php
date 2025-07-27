<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';

    $sheet1 = "License Plate";
?>
<meta charset="UTF-8">
<meta name="description" content="Free Web tutorials">
<meta name="keywords" content="Excel,HTML,CSS,XML,JavaScript">
<meta name="creator" content="John Doe">
<meta name="subject" content="subject1">
<meta name="title" content="title1">
<meta name="category" content="category1">

<div sheet-name="<?php echo $sheet1; ?>">

?>

    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>LP</div>
<?php 
    $array_lps = $_GET['LP'];

    $c = 2;
$lps_array = explode(",", $array_lps);
for($i = 0; $i < count($lps_array); $i++)
{
?>

        ?>
        <div cell="A<?php echo $c; ?>"><?php echo $lps_array[$i]; ?></div>
        <?php 
        $c++;

    }
  ?>

    
</div>