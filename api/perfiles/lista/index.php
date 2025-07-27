<?php
include '../../../config.php';
session_start();
error_reporting(0);

if (isset($_POST) && !empty($_POST)) {
    $id_role = $_POST['rol']; // get the requested page

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sql = "SELECT * FROM t_menu WHERE orden = '0';";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $arr = array();
    $i = 0;
?>

    <div class="ibox float-e-margins">
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-6">
                    <h5>Dispositivo Móvil</h5> 
                </div>  
            </div>
        </div>
    </div>

    <?php
    $sqls = "SELECT * FROM s_permisos_modulo where ID_PERMISO in (1,2,3,4,9,51,52,53,54,57,58,60,61,62,63,68,121);";
    if (!($ress = mysqli_query($conn, $sqls))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

   while ($rows = mysqli_fetch_array($ress)) {
       
        $sqlRRR = "SELECT * FROM t_permisos_perfil WHERE ID_PERFIL = '{$id_role}' AND ID_PERMISO = '".$rows['ID_PERMISO']."';";
        $resRRR = mysqli_query($conn, $sqlRRR);
        $mainsR = mysqli_fetch_object($resRRR);

    ?>

    <div class="ibox float-e-margins">
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-6">
                    <h5><?=$rows['DESCRIPCION']?></h5>&nbsp;&nbsp;

                </div>                    
                <div class="col-md-6">
                    <table class="table table-stripped">
                        <thead>
                            <tr>
                                <th>Hablitar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <input type="checkbox" name="<?php echo $rows['ID_PERMISO'].'-movil' ?>[]" <?php echo !empty($mainsR->Activo>0) ? 'checked' : '' ?>  >
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php
    }
       
    while ($row = mysqli_fetch_array($res)) {
        $sqlR = "SELECT * FROM t_profiles WHERE id_role = '".$id_role."' AND id_menu = '".$row['id_menu']."';";
        $resR = mysqli_query($conn, $sqlR);
        $main = mysqli_fetch_object($resR);

        $sqlRv = "SELECT * FROM t_profiles as a inner join t_submenu as b WHERE a.id_role = '".$id_role."' and a.id_menu = '".$row['id_menu']."' and a.id_submenu=b.id_submenu and id_opciones=1;";
        $resRv = mysqli_query($conn, $sqlRv);
        $mainv = mysqli_num_rows($resRv);

        $sqlRa = "SELECT * FROM t_profiles as a inner join t_submenu as b WHERE a.id_role = '".$id_role."' and a.id_menu = '".$row['id_menu']."' and a.id_submenu=b.id_submenu and id_opciones=2;";
        $resRa = mysqli_query($conn, $sqlRa);
        $maina = mysqli_num_rows($resRa);

        $sqlRe = "SELECT * FROM t_profiles as a inner join t_submenu as b WHERE a.id_role = '".$id_role."' and a.id_menu = '".$row['id_menu']."' and a.id_submenu=b.id_submenu and id_opciones=3;";
        $resRe = mysqli_query($conn, $sqlRe);
        $maine = mysqli_num_rows($resRe);

        $sqlRb = "SELECT * FROM t_profiles as a inner join t_submenu as b WHERE a.id_role = '".$id_role."' and a.id_menu = '".$row['id_menu']."' and a.id_submenu=b.id_submenu and id_opciones=4;";
        $resRb = mysqli_query($conn, $sqlRb);
        $mainb = mysqli_num_rows($resRb);

?>
<div class="ibox float-e-margins">
    <div class="row">
        <div class="col-md-12">
            <div class="col-md-6">
                <h5><?php echo utf8_encode($row['modulo']) ?></h5> 
            </div>                    
            <div class="col-md-6">
                <table class="table table-stripped">
                    <thead>
                        <tr>

                    <?php 
                        $sqlS = "SELECT * FROM t_submenu WHERE id_menu = '".$row['id_menu']."';";
                        $resS = mysqli_query($conn, $sqlS);
                        while ($rowS = mysqli_fetch_array($resS)) {                    
                            if($rowS['id_opciones']==1){ echo '<th>Ver</th>'; }                          
                            if($rowS['id_opciones']==2){ echo '<th>Agregar</th>'; }
                            if($rowS['id_opciones']==3){ echo '<th>Editar</th>'; }
                            if($rowS['id_opciones']==4){ echo '<th>Borrar</th>'; }
                        }
                    ?>

                        </tr>
                    </thead>
                    <tbody>
                        <tr>




                            <?php 


        $sqlS = "SELECT * FROM t_submenu WHERE id_menu = '".$row['id_menu']."';";
        $resS = mysqli_query($conn, $sqlS);
        while ($rowS = mysqli_fetch_array($resS)) {

                            ?>

                            <?php if($rowS['id_opciones']==1){?>

                            <td>
                                <input type="checkbox" name="<?php echo $row['id_menu'].'-'.$rowS['id_submenu'] ?>[]" <?php echo !empty($mainv>0) ? 'checked' : '' ?>>
                            </td>

                            <?php }?>

                            <?php if($rowS['id_opciones']==2){?>

                            <td>
                                <input type="checkbox" name="<?php echo $row['id_menu'].'-'.$rowS['id_submenu'] ?>[]" <?php echo !empty($maina>0) ? 'checked' : '' ?>>
                            </td>

                            <?php }?>

                            <?php if($rowS['id_opciones']==3){?>

                            <td>
                                <input type="checkbox" name="<?php echo $row['id_menu'].'-'.$rowS['id_submenu'] ?>[]" <?php echo !empty($maine>0) ? 'checked' : '' ?>>
                            </td>

                            <?php }?>

                            <?php if($rowS['id_opciones']==4){?>

                            <td>
                                <input type="checkbox" name="<?php echo $row['id_menu'].'-'.$rowS['id_submenu'] ?>[]" <?php echo !empty($mainb>0) ? 'checked' : '' ?>>
                            </td>

                            <?php }?>

                            <?php }?>

                        </tr>
                    </tbody>
                </table>      
            </div>            
        </div>
    </div>

    <?php
        $sql = "Select * from t_menu where orden = '1' and id_menu_padre='".$row['id_menu']."';";
        if (!($res1 = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }

        $arr = array();
        $i = 0;
        while ($row1 = mysqli_fetch_array($res1)) {
            $sqlR = "SELECT * FROM t_profiles WHERE id_role = '".$id_role."' AND id_menu = '".$row1['id_menu']."';";
            $resR = mysqli_query($conn, $sqlR);
            $main = mysqli_fetch_array($resR);
            $Activo = !empty($main["Activo"]) ? 'checked' : '';


            $sqlRv = "SELECT * FROM t_profiles as a inner join t_submenu as b WHERE a.id_role = '".$id_role."' and a.id_menu = '".$row1['id_menu']."' and a.id_submenu=b.id_submenu and id_opciones=1;";
            $resRv = mysqli_query($conn, $sqlRv);
            $mainv = mysqli_num_rows($resRv);

            $sqlRa = "SELECT * FROM t_profiles as a inner join t_submenu as b WHERE a.id_role = '".$id_role."' and a.id_menu = '".$row1['id_menu']."' and a.id_submenu=b.id_submenu and id_opciones=2;";
            $resRa = mysqli_query($conn, $sqlRa);
            $maina = mysqli_num_rows($resRa);

            $sqlRe = "SELECT * FROM t_profiles as a inner join t_submenu as b WHERE a.id_role = '".$id_role."' and a.id_menu = '".$row1['id_menu']."' and a.id_submenu=b.id_submenu and id_opciones=3;";
            $resRe = mysqli_query($conn, $sqlRe);
            $maine = mysqli_num_rows($resRe);

            $sqlRb = "SELECT * FROM t_profiles as a inner join t_submenu as b WHERE a.id_role = '".$id_role."' and a.id_menu = '".$row1['id_menu']."' and a.id_submenu=b.id_submenu and id_opciones=4;";
            $resRb = mysqli_query($conn, $sqlRb);
            $mainb = mysqli_num_rows($resRb);

    ?>
    <hr>
    <div class="row">
        <div class="col-md-12" style="padding-top: 0px; padding-right: 5px">
            <div class="col-md-6">
                <strong><?php echo utf8_encode($row1['modulo']) ?></strong>&nbsp;&nbsp;

            </div>
            <div class="col-md-6">
                <table class="table table-stripped">
                    <thead>
                        <tr>
                        <?php 

                            $sqlS = "SELECT * FROM t_submenu WHERE id_menu = '".$row1['id_menu']."';";
                            $resS = mysqli_query($conn, $sqlS);
                            while ($rowS = mysqli_fetch_array($resS)) {
                                if($rowS['id_opciones']==1){ echo '<th>Ver</th>';}
                                if($rowS['id_opciones']==2){ echo '<th>Agregar</th>'; }                            
                                if($rowS['id_opciones']==3){ echo '<th>Editar</th>'; }
                                if($rowS['id_opciones']==4){ echo '<th>Borrar</th>'; }
                            } 
                            ?>

                        </tr>
                    </thead>
                    <tbody>
                        <tr>

                            <?php 


            $sqlS = "SELECT * FROM t_submenu WHERE id_menu = '".$row1['id_menu']."';";
            $resS = mysqli_query($conn, $sqlS);
            while ($rowS = mysqli_fetch_array($resS)) {

                            ?>

                            <?php if($rowS['id_opciones']==1){?>

                            <td>
                                <input type="checkbox" name="<?php echo $row1['id_menu'].'-'.$rowS['id_submenu'] ?>[]" <?php echo !empty($mainv>0) ? 'checked' : '' ?>>
                            </td>

                            <?php }?>

                            <?php if($rowS['id_opciones']==2){?>

                            <td>
                                <input type="checkbox" name="<?php echo $row1['id_menu'].'-'.$rowS['id_submenu'] ?>[]" <?php echo !empty($maina>0) ? 'checked' : '' ?>>
                            </td>

                            <?php }?>

                            <?php if($rowS['id_opciones']==3){?>

                            <td>
                                <input type="checkbox" name="<?php echo $row1['id_menu'].'-'.$rowS['id_submenu'] ?>[]" <?php echo !empty($maine>0) ? 'checked' : '' ?>>
                            </td>

                            <?php }?>

                            <?php if($rowS['id_opciones']==4){?>

                            <td>
                                <input type="checkbox" name="<?php echo $row1['id_menu'].'-'.$rowS['id_submenu'] ?>[]" <?php echo !empty($mainb>0) ? 'checked' : '' ?>>
                            </td>

                            <?php }?>

                            <?php }?>

                        </tr>
                    </tbody>
                </table>    
            </div>                        
        </div>
    </div>

    <?php
            //$sql2 = 'SELECT * FROM profiles WHERE id_role = '.$id_role.' AND id_menu = '.$row['id_menu'].';';
            $sql2 = "Select * from t_menu where orden = '2' and id_menu_padre='".$row1['id_menu']."';";
            if (!($res2 = mysqli_query($conn, $sql2))) {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }

            while ($row2 = mysqli_fetch_array($res2)) {
                $sqlR = "SELECT * FROM t_profiles WHERE id_role = '".$id_role."' AND id_menu = '".$row2['id_menu']."';";
                $resR = mysqli_query($conn, $sqlR);
                $main = mysqli_fetch_array($resR);
                $Activo = !empty($main["Activo"]) ? 'checked' : '';

                $sqlRv = "SELECT * FROM t_profiles as a inner join t_submenu as b WHERE a.id_role = '".$id_role."' and a.id_menu = '".$row2['id_menu']."' and a.id_submenu=b.id_submenu and id_opciones=1;";
                $resRv = mysqli_query($conn, $sqlRv);
                $mainv = mysqli_num_rows($resRv);

                $sqlRa = "SELECT * FROM t_profiles as a inner join t_submenu as b WHERE a.id_role = '".$id_role."' and a.id_menu = '".$row2['id_menu']."' and a.id_submenu=b.id_submenu and id_opciones=2;";
                $resRa = mysqli_query($conn, $sqlRa);
                $maina = mysqli_num_rows($resRa);

                $sqlRe = "SELECT * FROM t_profiles as a inner join t_submenu as b WHERE a.id_role = '".$id_role."' and a.id_menu = '".$row2['id_menu']."' and a.id_submenu=b.id_submenu and id_opciones=3;";
                $resRe = mysqli_query($conn, $sqlRe);
                $maine = mysqli_num_rows($resRe);

                $sqlRb = "SELECT * FROM t_profiles as a inner join t_submenu as b WHERE a.id_role = '".$id_role."' and a.id_menu = '".$row2['id_menu']."' and a.id_submenu=b.id_submenu and id_opciones=4;";
                $resRb = mysqli_query($conn, $sqlRb);
                $mainb = mysqli_num_rows($resRb);
    ?>
    <div class="row">
        <div class="col-md-12">
            <div class="col-md-6">
                <span style="margin-left: 40px"><b>*</b> <?php echo utf8_encode($row2['modulo']) ?></span>
            </div>
            <div class="col-md-6">

                <table class="table table-stripped">
                    <thead>
                        <tr>

                        <?php 
                            $sqlS = "SELECT * FROM t_submenu WHERE id_menu = '".$row2['id_menu']."';";
                            $resS = mysqli_query($conn, $sqlS);
                            while ($rowS = mysqli_fetch_array($resS)) {                                
                                if($rowS['id_opciones']==1){ echo '<th>Ver</th>';}
                                if($rowS['id_opciones']==2){ echo '<th>Agregar</th>'; }                            
                                if($rowS['id_opciones']==3){ echo '<th>Editar</th>'; }
                                if($rowS['id_opciones']==4){ echo '<th>Borrar</th>'; }
                            }
                        ?>


                        </tr>
                    </thead>
                    <tbody>
                        <tr>


                            <?php 


                $sqlS = "SELECT * FROM t_submenu WHERE id_menu = '".$row2['id_menu']."';";
                $resS = mysqli_query($conn, $sqlS);
                while ($rowS = mysqli_fetch_array($resS)) {

                            ?>

                            <?php if($rowS['id_opciones']==1){?>

                            <td>
                                <input type="checkbox" name="<?php echo $row2['id_menu'].'-'.$rowS['id_submenu'] ?>[]" <?php echo !empty($mainv>0) ? 'checked' : '' ?>>
                            </td>

                            <?php }?>

                            <?php if($rowS['id_opciones']==2){?>

                            <td>
                                <input type="checkbox" name="<?php echo $row2['id_menu'].'-'.$rowS['id_submenu'] ?>[]" <?php echo !empty($maina>0) ? 'checked' : '' ?>>
                            </td>

                            <?php }?>

                            <?php if($rowS['id_opciones']==3){?>

                            <td>
                                <input type="checkbox" name="<?php echo $row2['id_menu'].'-'.$rowS['id_submenu'] ?>[]" <?php echo !empty($maine>0) ? 'checked' : '' ?>>
                            </td>

                            <?php }?>

                            <?php if($rowS['id_opciones']==4){?>

                            <td>
                                <input type="checkbox" name="<?php echo $row2['id_menu'].'-'.$rowS['id_submenu'] ?>[]" <?php echo !empty($mainb>0) ? 'checked' : '' ?>>
                            </td>

                            <?php }?>

                            <?php }?>

                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php } ?>

    <?php } ?>
</div>
<?php

    }
    mysqli_close();
}