<?php

$contenedorAlmacen = new \AlmacenP\AlmacenP();

$almacenes = $contenedorAlmacen->getAll();

$listaProvee = new \Proveedores\Proveedores();

$rutas = new \Ruta\Ruta();

$rutas_all = $rutas->getAll();

$confSql = \db()->prepare("SELECT IFNULL(Valor, '0') AS Valor FROM t_configuraciongeneral WHERE cve_conf = 'SAP' LIMIT 1 ");
$confSql->execute();
$ValorSAP = $confSql->fetch()['Valor'];

$confSql = \db()->prepare("SELECT DATE_FORMAT(CURDATE(), '%d-%m-%Y') AS fecha_actual FROM DUAL");
$confSql->execute();
$fecha_actual = $confSql->fetch()['fecha_actual'];

//$clientes = new \Clientes\Clientes();
//$colonias = new \Clientes\Clientes();
//$cpostales = new \Clientes\Clientes();
//$clientes = $clientes->getAll();
//$colonias = $colonias->getAll();
//$cpostales = $cpostales->getAll();

$confSql = \db()->prepare("SELECT IFNULL(Valor, '') AS instancia FROM t_configuraciongeneral WHERE cve_conf = 'instancia' LIMIT 1");
$confSql->execute();
$instancia = $confSql->fetch()['instancia'];

include_once 'config.php';

//TODO Alberto aqui

?>

<input type="hidden" id="instancia" value="<?php echo $instancia; ?>">

<!-- Mainly scripts -->

<!--<script src="/js/jquery-2.1.1.js"></script>-->

<script src="/js/jquery.min.js"></script>

<script src="/js/bootstrap.min.js"></script>

<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>

<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>



<!-- Peity -->

<script src="/js/plugins/peity/jquery.peity.min.js"></script>



<!-- jqGrid -->

<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>

<script src="/js/plugins/jqGrid/jquery.jqGrid.min.js"></script>



<!-- Custom and plugin javascript -->

<script src="/js/plugins/toastr/toastr.min.js"></script>

<script src="/js/inspinia.js"></script>

<script src="/js/plugins/pace/pace.min.js"></script>

<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>

<script src="/js/plugins/ladda/spin.min.js"></script>

<script src="/js/plugins/ladda/ladda.min.js"></script>

<script src="/js/plugins/ladda/ladda.jquery.min.js"></script>

<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>

<script src="/js/plugins/chosen/chosen.jquery.js"></script>

<script src="/js/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>

<script src="/js/plugins/bootstrap-wizard/form-wizard.min.js"></script>

<script src="/app/template/static/global/plugins/jquery.pulsate.min.js"></script>



<!--<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAAKrvy7BqPz9GOwH-Ss3yNwmBeykitNhI"></script>

<script src="/js/googlemap.js"></script>-->



<!-- DataTables -->

<link rel="stylesheet" href="/app/template/static/global/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css">

<link rel="stylesheet" href="/app/template/static/global/plugins/datatables.net-bs/css/responsive.bootstrap.min.css">

<link rel="stylesheet" href="/app/template/static/global/plugins/datatables.net-bs/css/buttons.dataTables.min.css">

<link rel="stylesheet" href="/app/template/static/global/plugins/datatables.net-bs/css/select.dataTables.css">


<!-- DataTables -->

<script src="/app/template/static/global/plugins/datatables.net/js/jquery.dataTables.min.js"></script>

<script src="/app/template/static/global/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>

<script src="/app/template/static/global/plugins/datatables.net-bs/js/dataTables.responsive.min.js"></script>

<script src="/app/template/static/global/plugins/datatables.net-bs/js/responsive.bootstrap.min.js"></script>

<script src="/app/template/static/global/plugins/datatables.net-bs/js/dataTables.buttons.min.js"></script>

<script src="/app/template/static/global/plugins/datatables.net-bs/js/jszip.min.js"></script>

<script src="/app/template/static/global/plugins/datatables.net-bs/js/buttons.print.min.js"></script>

<script src="/app/template/static/global/plugins/datatables.net-bs/js/buttons.html5.min.js"></script>

<script src="/app/template/static/global/plugins/datatables.net-bs/js/pdfmake.min.js"></script>

<script src="/app/template/static/global/plugins/datatables.net-bs/js/vfs_fonts.js"></script>

<script src="/app/template/static/global/plugins/datatables.net-bs/js/buttons.colVis.min.js"></script>

<script src="/app/template/static/global/plugins/datatables.net-bs/js/dataTables.select.min.js"></script>



<!-- Jquery Validate -->



<script src="/js/plugins/validate/jquery.validate.min.js"></script>

<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">

<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">

<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">

<!-- Switch -->

<link href="/app/template/static/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet"

      type="text/css" media="screen"/>

<script src="/app/template/static/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"

        type="text/javascript"></script>

<style>

    .products-list .product-info, .products-list-under .product-info {
        display: inline-block;
        width: 70%;
        margin-left: 30px;
    }

    .products-list-under
    {
        margin-top: 30px;
    }

    .hide {
        display: none;
    }

    .switch {

        position: relative;

        display: inline-block;

        width: 60px;

        height: 34px;

        float: right;

    }



    /* Hide default HTML checkbox */

    .switch input {

        display: none;

    }


    /* The slider */

    .slider {

        position: absolute;

        cursor: pointer;

        top: 0;

        left: 0;

        right: 0;

        bottom: 0;

        background-color: #ccc;

        -webkit-transition: .4s;

        transition: .4s;

    }



    .slider:before {

        position: absolute;

        content: "";

        height: 26px;

        width: 26px;

        left: 4px;

        bottom: 4px;

        background-color: white;

        -webkit-transition: .4s;

        transition: .4s;

    }



    input.default:checked + .slider {

        background-color: #444;

    }



    input.primary:checked + .slider {

        background-color: #2196F3;

    }



    input.success:checked + .slider {

        background-color: #8bc34a;

    }



    input.info:checked + .slider {

        background-color: #3de0f5;

    }



    input.warning:checked + .slider {

        background-color: #FFC107;

    }



    input.danger:checked + .slider {

        background-color: #f44336;

    }



    input:focus + .slider {

        box-shadow: 0 0 1px #2196F3;

    }



    input:checked + .slider:before {

        -webkit-transform: translateX(26px);

        -ms-transform: translateX(26px);

        transform: translateX(26px);

    }



    /* Rounded sliders */

    .slider.round {

        border-radius: 34px;

    }



    .slider.round:before {

        border-radius: 50%;

    }

    .desc
    {
        font-size: 20px;
    }

</style>

<!-- Mainly scripts -->
    <input type="hidden" id="num_pallets" value="">
    <input type="hidden" id="ruta_selected" value="">

<div class="wrapper wrapper-content  animated" id="list">


<div class="modal fade" id="fotos_oc_th" role="dialog">
    <div class="vertical-alignment-helper">
          <div class="modal-dialog vertical-align-center">
            <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Fotos del Embarque <span id="n_folio"></span></h4>
                    </div>
                    <div class="modal-body">
                        <form id="form-subir-fotos-th" action="import" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Seleccione las fotos a documentar</label>
                                <input type="hidden" name="folio_foto_th" id="folio_foto_th" value="">
                                <input type="file" name="image_file_th" id="file_th" class="form-control" accept=".png, .PNG, .jpg, .JPG, .JPEG, .jpeg" required>
                                <br>
                                <input type="text" class="form-control" name="descripcion_foto" id="descripcion_foto" placeholder="Descripción de la foto" required>
                                <br><br>
                                <button id="btn-fotos-th" type="button" class="btn btn-primary">Subir Foto</button>
                            </div>
                        </form>
                        <div class="col-md-12">
                            <div class="progress" style="display:none">
                                <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar"
                                     aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                                    <div class="percent">0%</div >
                                </div>
                            </div>
                        </div>
                        <br>
                        <div id="fotos_th">
                            
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div style="text-align: right">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
          </div>
    </div>
</div>

    <div class="row">

        <div class="col-md-12">



            <div class="portlet light bordered" id="form_wizard_1">



                <div class="portlet-title">

<?php 
/*
?>
                    <div class="caption">

                        <i class=" icon-layers font-red"></i>

                                            <span class="caption-subject font-red bold uppercase"> Planeación Embarque -

                                                <span class="step-title"> Paso 1 de 3 </span>

                                            </span>

                    </div>
                    <?php 
                    */
                    /*
                    ?>
                    <div class="actions">

                        <a class="btn btn-circle btn-icon-only btn-default" href="javascript:;">

                            <i class="icon-cloud-upload"></i>

                        </a>

                        <a class="btn btn-circle btn-icon-only btn-default" href="javascript:;">

                            <i class="icon-wrench"></i>

                        </a>

                        <a class="btn btn-circle btn-icon-only btn-default" href="javascript:;">

                            <i class="icon-trash"></i>

                        </a>

                    </div>
                    <?php
                    */ 
                    ?>

                </div>

                <div class="portlet-body form">

                    <form class="form-horizontal" action="#" id="submit_form" method="POST">

                        <div class="form-wizard">

                            <div class="form-body">

                                <ul class="nav nav-pills nav-justified steps">

                                    <li>

                                        <a href="#tab1" data-toggle="tab" class="step" style="background-color: #2f4050;">

                                            <!--<span class="number"> 1 </span>-->

                                                                <span class="desc">

                                                                    <!--<i class="fa fa-check"></i>--> Planeación de Embarques </span>

                                        </a>

                                    </li>

<?php 
/*
?>
                                    <li>

                                        <a href="#tab2" data-toggle="tab" class="step">

                                            <span class="number"> 2 </span>

                                                                <span class="desc">

                                                                    <i class="fa fa-check"></i> Contenedores </span>

                                        </a>

                                    </li>

                                    <li>

                                        <a href="#tab3" data-toggle="tab" class="step active">

                                            <span class="number"> 3 </span>

                                                                <span class="desc">

                                                                    <i class="fa fa-check"></i> Rutas </span>

                                        </a>

                                    </li>

                                    <li>

                                        <a href="#tab4" data-toggle="tab" class="step">

                                            <span class="number"> 4 </span>

                                                                <span class="desc">

                                                                    <i class="fa fa-check"></i> Confirmar </span>

                                        </a>

                                    </li>
<?php 
*/
?>
                                </ul>

                                <!--<div id="bar" class="progress progress-striped" role="progressbar">

                                    <div class="progress-bar progress-bar-success"> </div>

                                </div>-->

                                <div class="tab-content">

                                    <div class="alert alert-danger display-none hidden">

                                        <button class="close" data-dismiss="alert"></button>

                                        You have some form errors. Please check below.

                                    </div>

                                    <div class="alert alert-success display-none" hidden>

                                        <button class="close" data-dismiss="alert"></button>

                                        Your form validation is successful!

                                    </div>

                                    <div class="tab-pane active" id="tab1">

                                        <?php /* ?><h3>Visor Áreas de Embarque</h3><?php */ ?>



                                        <div class="row">

                                            <div class="ibox ">

                                                <div class="ibox-title">

                                                    <div class="row">

                                                        <div class="col-md-4">

                                                            <div class="form-group">

                                                                <label>Almacén</label>

                                                                <select class="form-control chosen-select"

                                                                        name="almacen" id="almacen">

                                                                    <option value="">Seleccione</option>

                                                                    <?php if (isset($almacenes) && !empty($almacenes)): ?>

                                                                        <?php foreach ($almacenes as $almacen): ?>

                                                                            <option

                                                                                value="<?php echo $almacen->clave ?>"><?php echo "(".$almacen->clave.") - ".$almacen->nombre ?></option>

                                                                        <?php endforeach; ?>

                                                                    <?php endif; ?>

                                                                </select>

                                                            </div>

                                                        </div>


                                                        <div class="col-md-4">

                                                            <div class="form-group">

                                                                <label>Área de embarque</label>

                                                                <select class="form-control chosen-select" name="isla"

                                                                        id="isla" onchange="cargarDatosGridPrincipal()">

                                                                    <!--<option value="">Seleccione</option>-->

                                                                </select>

                                                            </div>

                                                        </div>

                                                        <div class="col-md-4">

                                                            <div class="form-group">

                                                                <label>Ruta</label>

                                                                <select class="form-control chosen-select" name="ruta"

                                                                        id="ruta" onchange="/*cargarIslas();*/ cargarDatosGridPrincipal();">

                                                                    <!--<option value="">Seleccione</option>-->

                                                                    <?php /* if (isset($rutas_all) && !empty($rutas_all)): ?>

                                                                        <?php foreach ($rutas_all as $ruta): ?>

                                                                            <option

                                                                            value="<?php echo $ruta->cve_ruta; ?>"><?php echo $ruta->descripcion; ?></option>

                                                                        <?php endforeach; ?>

                                                                    <?php endif; */ ?>

                                                                </select>

                                                            </div>

                                                        </div>


                                                    </div>

                                                    <div class="row">

                                                        <div class="col-md-4">

                                                            <div class="form-group">

                                                                <label>Cliente</label>
                                                                <input type="text" class="form-control" name="cliente" id="cliente"  placeholder="Cliente...">
                                                                <?php 
                                                                /*
                                                                ?>
                                                                <select class="form-control chosen-select"

                                                                        name="cliente" id="cliente" onchange="cargarDatosGridPrincipal()">

                                                                    <option value="">Seleccione</option>

                                                                    <?php if (!empty($clientes)): ?>

                                                                        <?php foreach ($clientes as $cliente): ?>

                                                                            <option value="<?php echo $cliente->Cve_Clte ?>"><?php echo $cliente->Cve_Clte . " - " . $cliente->RazonSocial ?></option>

                                                                        <?php endforeach; ?>

                                                                    <?php endif; ?>

                                                                </select>
                                                                <?php  
                                                                */
                                                                ?>
                                                            </div>

                                                        </div>

                                                        <div class="col-md-4">

                                                            <div class="form-group">

                                                                <label>Colonia</label>

                                                                <input type="text" class="form-control" name="colonia" id="colonia"  placeholder="Colonia...">
                                                                <?php 
                                                                /*
                                                                ?>
                                                                <select class="form-control chosen-select"

                                                                        name="colonia" id="colonia" onchange="cargarDatosGridPrincipal()">

                                                                    <option value="">Seleccione</option>

                                                                    <?php if (!empty($colonias)): ?>

                                                                        <?php foreach ($colonias as $colonia): ?>

                                                                            <?php if ($colonia->Colonia != null): ?>

                                                                                <option

                                                                                    value="<?php echo utf8_decode($colonia->Colonia); ?>"><?php echo utf8_decode($colonia->Colonia); ?></option>

                                                                            <?php endif ?>

                                                                        <?php endforeach; ?>

                                                                    <?php endif; ?>

                                                                </select>
                                                                <?php  
                                                                */
                                                                ?>


                                                            </div>

                                                        </div>

                                                        <div class="col-md-4">

                                                            <div class="form-group">

                                                                <label>Código Postal</label>

                                                                <input type="text" class="form-control" name="cpostal" id="cpostal"  placeholder="Código Postal">
                                                                <?php 
                                                                /*
                                                                ?>
                                                                <select class="form-control chosen-select"

                                                                        name="cpostal" id="cpostal" onchange="cargarDatosGridPrincipal()">

                                                                    <option value="">Seleccione</option>

                                                                    <?php if (!empty($cpostales)): ?>

                                                                        <?php foreach ($cpostales as $cpostal): ?>

                                                                            <?php if ($cpostal->CodigoPostal != null): ?>

                                                                                <option

                                                                                    value="<?php echo $cpostal->CodigoPostal; ?>"><?php echo $cpostal->CodigoPostal; ?></option>

                                                                            <?php endif ?>

                                                                        <?php endforeach; ?>

                                                                    <?php endif; ?>

                                                                </select>
                                                                <?php 
                                                                */
                                                                ?>

                                                            </div>

                                                        </div>




<?php 
/*
?>
                                                        <div class="col-md-4"

                                                             style="margin-bottom: 20px; text-align:right">

                                                            <label for="email">&#160;&#160;</label>

                                                            <div class="input-group">

                                                                <input type="text" class="form-control" name="buscar"

                                                                       id="buscar"

                                                                       placeholder="Ingrese el número de pedido">

                                                                <div class="input-group-btn">

                                                                    <button onclick="cargarDatosGridPrincipal()"

                                                                            type="button" class="btn btn-primary"

                                                                            id="buscarP">

                                                                        <span class="fa fa-search"></span> Buscar

                                                                    </button>

                                                                </div>

                                                            </div>

                                                        </div>
<?php 
*/
?>
                                                        <div class="col-md-12">

                                                            <h4 id="folio-embarque"></h4>

                                                        </div>

                                                    </div>

                                                </div>

                                                <div class="ibox-content">

                                                    <div class="row">


                                                        <div class="col-md-2" style="width: 110px;">

                                                            <div class="form-group">

                                                                <label style="background-color:#2f4050; color: #fff;width: 100%;padding: 5px;text-align: center;">No. de Pedidos</label>

                                                                <input class="form-control" id="nro_de_pedidos"

                                                                       type="text" disabled style="text-align: center; font-size: 18px; font-weight: bolder;">

                                                            </div>

                                                        </div>

                                                        <div class="col-md-2" style="width: 100px;">

                                                            <div class="form-group">

                                                                <label style="background-color: #2f4050; color: #fff;width: 100%;padding: 5px;text-align: center;">No. Entregas</label>

                                                                <input class="form-control" id="nro_de_entregas"

                                                                       type="text" disabled style="text-align: center; font-size: 18px; font-weight: bolder;">

                                                            </div>

                                                        </div>

                                                        <div class="col-md-2" style="width: 110px;">

                                                            <div class="form-group">

                                                                <label style="background-color: #2f4050; color: #fff;width: 100%;padding: 5px;text-align: center;">Peso Total KG</label>

                                                                <input class="form-control" id="txt_tot_peso"

                                                                       type="text" disabled style="text-align: center; font-size: 18px; font-weight: bolder;">

                                                            </div>

                                                        </div>

                                                        <div class="col-md-2" style="width: 130px;">

                                                            <div class="form-group">

                                                                <label style="background-color: #2f4050; color: #fff;width: 100%;padding: 5px;text-align: center;">Volumen Total m3</label>

                                                                <input class="form-control" id="txt_tot_volumen"

                                                                       type="text" disabled style="text-align: center; font-size: 18px; font-weight: bolder;">

                                                            </div>

                                                        </div>


                                                        <div class="col-md-2" style="width: 110px;">

                                                            <div class="form-group">

                                                                <label style="background-color: #2f4050; color: #fff;width: 100%;padding: 5px;text-align: center;">Total de Piezas</label>

                                                                <input class="form-control" id="txt_tot_piezas"

                                                                       type="text" disabled style="text-align: center; font-size: 18px; font-weight: bolder;">

                                                            </div>

                                                        </div>

                                                        <div class="col-md-2" style="width: 110px;">

                                                            <div class="form-group">

                                                                <label style="background-color: #2f4050; color: #fff;width: 100%;padding: 5px;text-align: center;">Total de Guias</label>

                                                                <input class="form-control" id="txt_tot_guias"

                                                                       type="text" disabled style="text-align: center; font-size: 18px; font-weight: bolder;">

                                                            </div>

                                                        </div>

                                                        <div class="col-md-2" style="width: 110px;">

                                                            <div class="form-group">

                                                                <label style="background-color: #2f4050; color: #fff;width: 100%;padding: 5px;text-align: center;">Total de Pallets</label>

                                                                <input class="form-control" id="nro_pallets"

                                                                       type="text" disabled style="text-align: center; font-size: 18px; font-weight: bolder;">

                                                            </div>

                                                        </div>

                                                        <div class="row">
                                                        <div class="col-md-5" >

                                    <div class="" style="margin-left: 0px;margin-bottom: 0;">

                                        <input type="text" class="form-control" name="buscar"

                                               id="buscar"

                                               placeholder="Ingrese el número de pedido">

                                        <div class="input-group-btn" style="top: 35px;right: 232px;">



                                        </div>

                                    </div>
                                                        </div>
                                                        <div class="col-md-2" >
                                            <button onclick="cargarDatosGridPrincipal()"

                                                    type="button" class="btn btn-primary"

                                                    id="buscarP">

                                                <span class="fa fa-search"></span> Buscar

                                            </button>
                                                        </div>
                                                    </div>
                                                        <div class="text-right col-md-4">

<!--                                                            <label class="text-right"><input type="checkbox"-->

<!--                                                                                             id="btn-asignar-todo"/>-->

<!--                                                                Asignar todo</label>-->

                                                        </div>
                                            <div class="col-sm-12">

                                                <form class="form-inline margin-bottom-20">

                                                    <div class="form-group">

                                                        <label class="sr-only" for="exampleInputAmount"></label>

                                                            <label style="font-size: 14px;"><b>Transporte</b></label>

                                                        <div

                                                            class="input-group select2-bootstrap-append select2-bootstrap-prepend input-large" style="display: block;">

                                                            <input type="hidden" id="folio_orden" value="">
                                                            <input type="hidden" id="folio_xpartes" value="">
                                                            <input type="hidden" id="foliosStorage" value="">
                                                            <select class="form-control select" name="select_transporte"

                                                                    id="select_transporte">

                                                                <option></option>

                                                            </select>
<!--
                                                            <div class="input-group-addon" id="camion_alto"><i

                                                                    class="fa fa-arrow-up" title="Alto del Transporte">

                                                                    &nbsp;</i></div>

                                                            <div class="input-group-addon" id="camion_fondo"><i

                                                                    class="fa fa-arrow-circle-o-down"

                                                                    title="Fondo del Transporte">&nbsp;</i></div>

                                                            <div class="input-group-addon" id="camion_ancho"><i

                                                                    class="fa fa-text-width" aria-hidden="true"

                                                                    title="Ancho del Transporte">&nbsp;</i></div>

                                                            <div class="input-group-addon" id="camion_carga"><i

                                                                    class="fa fa-truck" aria-hidden="true"

                                                                    title="Carga en Toneladas">&nbsp;</i></div>

                                                            <div class="input-group-addon" id="camion_difpeso"><i

                                                                    class="fa fa-minus" aria-hidden="true"

                                                                    title="Diferencia Peso">&nbsp;</i></div>

                                                            <div class="input-group-addon" id="camion_difcap"><i

                                                                    class="fa fa-minus-square" aria-hidden="true"

                                                                    title="Diferencia Capacidad">&nbsp;</i></div>
-->
                    <br><br><br>
                    <style>
                        .titulo_transporte
                        {
                            background-color:#2f4050;
                            color:#FFF;
                            padding: 5px;
                            font-weight: bold;
                        }

                        .titulo_head
                        {
                            font-size: 20px;
                            text-align: center;
                        }
                        .titulo_body
                        {
                            font-size: 13px;
                            font-weight: 600;
                            text-align: center;
                        }

                        .padding_titulo
                        {
                            padding-bottom: 0 !important;
                        }

                        .padding_celda
                        {
                            padding-top: 0 !important;
                        }

                        .valor_celda
                        {
                            padding: 5px;
                            border: 1px solid #ccc;
                        }
                    </style>
                    <div class="titulo_transporte titulo_head">Transporte</div>
                    <div class="row padding_titulo" style="padding: 14px;">
                        <div class="titulo_transporte titulo_body col-md-2">Capacidad Carga KG</div>
                        <div class="titulo_transporte titulo_body col-md-2">Capacidad Volumétrica m3</div>
                        <div class="col-md-2"></div>
                        <div class="titulo_transporte titulo_body col-md-6">Dimensiones del Vehículo</div>
                    </div>





                    <div class="row padding_celda" style="padding: 14px;">
                        <div class="titulo_body valor_celda col-md-2">
                            <div class="" id="camion_carga"><i class="" aria-hidden="true" title="Carga en Toneladas">&nbsp;</i></div>
                            <!-- fa fa-truck input-group-addon -->
                        </div>
                        <div class="titulo_body valor_celda col-md-2">
                            <div class="" id="capacidad_volumen">&nbsp;</div>
                        </div>
                        <div class="col-md-2"></div>
                        <div class="titulo_body col-md-6"></div>
                    </div>
                    <div class="row padding_titulo" style="padding: 14px;">
                        <div class="titulo_transporte titulo_body col-md-2">Capacidad Utilizada KG</div>
                        <div class="titulo_transporte titulo_body col-md-2">Volumen Ocupado m3</div>
                        <div class="col-md-2"></div>
                        <div class="titulo_transporte titulo_body col-md-2">Altura Mts</div>
                        <div class="titulo_transporte titulo_body col-md-2">Ancho Mts</div>
                        <div class="titulo_transporte titulo_body col-md-2">Fondo Mts</div>
                    </div>
                    <div class="row padding_celda" style="padding: 14px;">
                        <div class="titulo_body valor_celda col-md-2">
                            <span id="capacidad_utilizada">&nbsp;</span>
                        </div>
                        <div class="titulo_body valor_celda col-md-2">
                            <span id="volumen_ocupado">&nbsp;</span>
                        </div>
                        <div class="col-md-2"></div>
                        <div class="titulo_body valor_celda col-md-2">
                            <div class="" id="camion_alto"><i class="" title="Alto del Transporte">&nbsp;</i></div>
                            <!--fa fa-arrow-up input-group-addon-->
                        </div>
                        <div class="titulo_body valor_celda col-md-2">
                            <div class="" id="camion_ancho"><i class="" aria-hidden="true" title="Ancho del 
                                <!--fa fa-text-width-->Transporte">&nbsp;</i></div>
                        </div>
                        <div class="titulo_body valor_celda col-md-2">
                            <div class="" id="camion_fondo"><i class="" title="Fondo del Transporte">&nbsp;</i></div>
                            <!--fa fa-arrow-circle-o-down input-group-addon-->
                        </div>
                    </div>

                                                        </div>

                                                    </div>

                                                </form>

                                            </div>



                                                    </div>


                                                </div>

                                            </div>






                                            <div class="col-md-12" style="margin-bottom: 30px;">



                                                <div class="row" style="text-align: center;display: none;">
                                                    <span><strong style="color:red;">LOS PEDIDOS QUE TENGAN FECHA DE ENTREGA MAYOR A <?php echo $fecha_actual; ?> NO APARECERÁN EN ESTE LISTADO</strong></span>
                                                </div>
                             <div class="row">
                              <div class="col-lg-2">
                                <div class="checkbox" id="chb_asignar">
                                  <label for="btn-asignarTodo">
                                    <input type="checkbox" name="asignarTodo" id="btn-asignarTodo"><b>Generar Todos los Reportes</b>
                                  </label>
                                </div>
                              </div>
                              <div class="col-lg-2">
                                <div class="checkbox" id="chb_asignarCliente">
                                  <label for="btn-EntregarClientes">
                                    <input type="checkbox" title="Los pedidos Embarcados No se cargaran en el Stock de Rutas" name="EntregarClientes" id="btn-EntregarClientes"><b>Entregar a Cliente</b>
                                  </label>
                                </div>
                              </div>
                            </div> 

                                                <table id="grid-table"></table>

                                                <div id="grid-pager"></div>

                                            <br><br>
                                            <input type="button" id="boton_reportes" class="btn btn-primary" value="Generar Reportes Etiquetas" />
                                            </div>

                                            <div class="col-md-12">

                                                <div class="form-group row nuevoListadoPedido">

                                                    <div class="cola" style="height: 530px;">

                                                        <div class="box-header with-border">

                                                            <h3 class="box-title">Pedidos Agregados</h3>

                                                        </div>

                                                        <ul class="products-list product-list-in-box">



                                                        </ul>

                                                        <ul class="products-list-under product-list-in-box">



                                                        </ul>
                        <div class="row">
                            <br><br><br>
                            <div class="col-md-4"></div>
                            <div class="col-md-4">
                                <label for="guia_transporte" style="width:100%;">
                                    <b>Guía de Transporte:</b>
                                <input type="text" id="guia_transporte" class="form-control" placeholder="Guía de Transporte..." style="text-transform: uppercase;" maxlength="20">
                                </label>
                            </div>
                        </div>
                        <div class="row">
                            <br><br><br>
                            <div class="col-md-4"></div>
                            <div class="col-md-4">
                                <label for="contacto_embarque" style="width:100%;">
                                    <b>Contacto:</b>
                                <input type="text" id="contacto_embarque" class="form-control" placeholder="Contacto...">
                                </label>
                            </div>
                        </div>

                        <div class="row">
                            <br><br><br>
                            <div class="col-md-4"></div>

                            <?php 
                            if($instancia == 'repremundo' || $instancia == 'dev')
                            {
                            ?>
                            <div class="col-md-4">
                                <label for="idchofer" style="width:100%;">
                                    <b>ID Chofer:</b>
                                <input type="text" id="idchofer" class="form-control" placeholder="ID Chofer...">
                                </label>
                            </div>
                            <?php 
                            }
                            ?>
                            <div class="col-md-4">
                                <label for="chofer" style="width:100%;">
                                    <b>Chofer:</b>
                                <input type="text" id="chofer" class="form-control" placeholder="Chofer...">
                                </label>
                            </div>
                        </div>
                        <?php 
                        if($instancia == 'repremundo' || $instancia == 'dev')
                        {
                        ?>
                        <div class="row">
                            <br><br><br>
                            <div class="col-md-4"></div>
                            <div class="col-md-4">
                                <label for="n_unidad" style="width:100%;">
                                    <b>Número de Unidad:</b>
                                <input type="text" id="n_unidad" class="form-control" placeholder="Número de Unidad...">
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label for="cve_transportadora" style="width:100%;">
                                    <b>Clave Transportadora:</b>
                                <select class="chosen-select form-control" id="cve_transportadora" name="cve_transportadora">
                                    <option value="">Seleccione una Transportadora</option>  
                                    <?php foreach( $listaProvee->getAll(" AND es_transportista = 1 ") AS $p ): //AND es_cliente = 1 ?>    
                                        <option value="<?php echo $p->cve_proveedor; ?>"><?php echo "( ".$p->cve_proveedor." ) - ".$p->Nombre; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                </label>
                            </div>
                        </div>
                        <?php 
                        }
                        ?>
                        <?php 
                        if($instancia == 'repremundo' || $instancia == 'dev' || $instancia == 'welldex')
                        {
                        ?>
                        <div class="row">
                            <br><br><br>
                            <div class="col-md-4"></div>
                            <div class="col-md-4">
                                <label for="placa" style="width:100%;">
                                    <b>Placa:</b>
                                <input type="text" id="placa" class="form-control" placeholder="Placa...">
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label for="sello_precinto" style="width:100%;">
                                    <b>Sello/Precinto:</b>
                                <input type="text" id="sello_precinto" class="form-control" placeholder="Sello/Precinto...">
                                </label>
                            </div>
                        </div>

                        <?php 
                        }
                        ?>

                        <div class="row">
                            <br><br>
                            <div class="col-md-4"></div>
                            <div class="col-md-2">
                                <label for="seguro">
                                    <b>Seguro de la Carga:</b>
                                <input type="text" id="seguro" class="form-control" placeholder="Seguro...">
                                </label>
                            </div>
                            <div class="col-md-2">
                                <label for="flete">
                                    <b>Flete Pagadero en:</b>
                                <input type="text" id="flete" class="form-control" placeholder="Flete...">
                                </label>
                            </div>
                        </div>
                        <?php 
                        if(strpos($_SERVER['HTTP_HOST'], 'avavex') !== false || strpos($_SERVER['HTTP_HOST'], 'dev') !== false)
                        {
                        ?>
                        <input type="hidden" class="valor_origen" value="1">
                        <div class="row">
                            <br><br><br>
                            <div class="col-md-4" style="padding-left: 80px;">
                                <div class="control_pallets_contenedores" style="display: none;">
                                    <label for="n_control_pallets">
                                        <b>Pallets:</b>
                                    <input type="number" id="n_control_pallets" class="form-control" placeholder="Pallets..." min="0" value="0">
                                    </label>
                                    <br><br>
                                    <label for="n_control_contenedores">
                                        <b>Contenedores:</b>
                                    <input type="number" id="n_control_contenedores" class="form-control" placeholder="Contenedores..." min="0" value="0">
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>
                                    <b>Origen: </b></label>
                                <textarea id="origen" class="form-control" placeholder="Origen..." rows="10">Remitente: 
RFC: 
Domicilio: 
Ciudad: 
Se Recogerá En: 
                                </textarea>
                                
                            </div>
                        </div>
                        <?php 
                        }
                        else
                        {
                        ?>
                        <input type="hidden" class="valor_origen" value="1">
                        <div class="row">
                            <br><br><br>
                            <div class="col-md-4" style="padding-left: 80px;">
                                <div class="control_pallets_contenedores" style="display: none;">
                                    <label for="n_control_pallets">
                                        <b>Pallets:</b>
                                    <input type="number" id="n_control_pallets" class="form-control" placeholder="Pallets..." min="0" value="0">
                                    </label>
                                    <br><br>
                                    <label for="n_control_contenedores">
                                        <b>Contenedores:</b>
                                    <input type="number" id="n_control_contenedores" class="form-control" placeholder="Contenedores..." min="0" value="0">
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4"></div>
                        </div>
                        <?php 
                        }
                        ?>

                                                    </div>





                                                </div>

                                                <input type="hidden" id="listaPedidos" name="listaPedidos">

                                                <button type="button"

                                                        class="btn btn-default hidden-lg btnAgregarPedido">Agregar

                                                    Pedido

                                                </button>



                                                <hr>

                                            </div>

                                        </div>

                                        <br/>



                                        <!--<div class="text-right">

                                            <button onclick="embarcar()" type="button" class="btn btn-primary"

                                                    id="buscarP">

                                                <span class="fa fa-search"></span> Embarcar

                                            </button>

                                        </div>-->

                                    </div>

                                    <div class="tab-pane" id="tab2">

                                        <h3 class="block">Escoja los Pallets /Contenedores</h3>

                                        <div class="row">

                                            <div class="col-lg-6">

                                                <select name="contenedoreselect" id="contenedorselect"

                                                        class="form-control chosen-select">

                                                    <option value="-1">Seleccione el Contenedor</option>

                                                </select>

                                            </div>

                                            <div class="col-lg-6">

                                            </div>

                                        </div>

                                        <div class="table-responsive" style="margin-top: 10px;">

                                            <table class="table" id="tablacajas">

                                                <thead>

                                                <tr>

                                                    <th>Nro Caja</th>

                                                    <th>Clave Caja</th>

                                                    <th>Guia</th>

                                                    <th>Folio</th>

                                                    <th>Volumen(m<sup>3</sup>)</th>

                                                    <th>Peso(Kg)</th>

                                                    <th>Accion</th>

                                                </tr>

                                                </thead>

                                                <tbody>

                                                <tr>

                                                    <td scope="row"></td>

                                                    <td></td>

                                                    <td></td>

                                                    <td></td>

                                                    <td></td>

                                                    <td></td>

                                                    <td></td>

                                                </tr>

                                                <tr>

                                                    <td scope="row"></td>

                                                    <td></td>

                                                    <td></td>

                                                </tr>

                                                </tbody>

                                            </table>

                                        </div>



                                    </div>

                                    <div class="tab-pane" id="tab3">

                                        <h3 class="block">Revise la Ruta a Seguir</h3>

                                        <div class="form-group">

                                            <form id="form1">

                                                <div id="mapa" style="height: 600px; width: 80%;">

                                                </div>

                                                <br />

                                                Velocidad Promedio (km/hr) :

                                                <input type="text" id="txtAvgSpeed">

                                                <br />

                                                <br />

                                                <div align="left" style="width: 100%;">

                                                    <table id="HtmlTable1" width="80%" border="1" style="border-color: #275D18; text-align: center;"

                                                           cellpadding="3" cellspacing="0">

                                                        <tr style="background-color: #D1E8B0;">

                                                            <td style="width: 80px; color: #275D18; font-weight: bold;">

                                                                No.

                                                            </td>

                                                            <td style="width: 80px; color: #275D18; font-weight: bold;">

                                                                Localizacion

                                                            </td>

                                                            <td style="width: 100px; color: #275D18; font-weight: bold;">

                                                                Latitud

                                                            </td>

                                                            <td style="width: 100px; color: #275D18; font-weight: bold;">

                                                                Longitud

                                                            </td>

                                                            <td style="width: 100px; color: #275D18; font-weight: bold;">

                                                                Distancia (Meters)

                                                            </td>

                                                            <td style="width: 70px; color: #275D18; font-weight: bold;">

                                                                Hora (Minutes)

                                                            </td>

                                                            <td style="width: 60px; color: #275D18; font-weight: bold;">

                                                                Eliminar

                                                            </td>

                                                        </tr>

                                                    </table>

                                                </div>

                                                <div align="left" style="width: 100%;">

                                                    <table id="HtmlTable" width="80%" border="1" style="border-color: #275D18; text-align: center;"

                                                           cellpadding="3" cellspacing="0">

                                                    </table>

                                                </div>



                                            </form>

                                        </div>



                                    </div>

                                    <div class="tab-pane" id="tab4">

                                        <h3 class="block">Confirme su Embarque</h3>



                                    </div>

                                </div>

                            </div>

                            <div class="form-actions">

                                <div class="row">

                                    <div class="col-md-offset-3 col-md-9">
                                        <a href="#" class="btn btn-outline green button-next permiso_registrar" id="embarcar-btn" <?php /* ?>onclick="EmbarcarPedidos()" <?php */ ?>>Embarcar</a>
<?php 
/*
?>
                                        <a href="javascript:;" class="btn default button-previous">

                                            <i class="fa fa-angle-left"></i> Atras </a>

                                        <a href="javascript:;" class="btn btn-outline green button-next"> Embarcar

                                            <!--<i class="fa fa-angle-right"></i>-->

                                        </a>
                                        <a href="javascript:;" class="btn green button-submit"> Embarcar

                                            <i class="fa fa-check"></i>

                                        </a>
<?php 
*/
?>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </form>

                </div>

            </div>



        </div>

    </div>

</div>



<div class="modal fade" id="modal-detalles-cajas" role="dialog">

    <div class="vertical-alignment-helper">

        <div class="modal-dialog vertical-align-center modal-lg" style="width:90% !important; max-width:90%">

            <!-- Modal content-->

            <div class="modal-content">

                <div class="modal-header">

                    <button type="button" class="close" data-dismiss="modal">&times;</button>

                    <h4>Lista de Empaque</h4>

                </div>

                <div class="modal-body">

                    <div class="row">

                        <div class="table-responsive">

                            <div class="col-md-12">

                                <table class="table table-bordered">

                                    <thead>

                                    <tr>

                                        <th>Folio</th>

                                        <th>Clave</th>

                                        <th>Cliente</th>

                                        <th>Clave Destinatario</th>

                                        <th>Destinatario</th>

                                        <th>Ruta</th>

                                        <th>Total Cajas</th>

                                        <th>Total Pallets</th>

                                        <th>Total guías</th>

                                        <th>Volumen (m<sup>3</sup>)</th>

                                        <th>Peso (Kg)</th>

                                    </tr>

                                    </thead>

                                    <tbody>

                                    <tr>

                                        <td id="caja_folio"></td>

                                        <td id="caja_clave"></td>

                                        <td id="caja_cliente"></td>

                                        <td id="caja_clave_destinatario"></td>

                                        <td id="caja_destinatario"></td>

                                        <td id="caja_ruta"></td>

                                        <td class="text-right" id="total_cajas"></td>

                                        <td class="text-right" id="total_pallets"></td>

                                        <td class="text-right" id="caja_guias"></td>

                                        <td class="text-right" id="caja_volumen"></td>

                                        <td class="text-right" id="caja_peso"></td>

                                    </tr>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                    <div class="row">

                        <div class="ibox-content">

                            <div class="jqGrid_wrapper" id="detalle_wrapper">

                                <table id="grid-detalles-caja"></table>

                                <div id="grid-detalles-caja-pager"></div>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>

                </div>

            </div>

        </div>

    </div>

</div>



<div class="modal fade" id="modal-detalles-pedido" role="dialog">

    <div class="vertical-alignment-helper">

        <div class="modal-dialog vertical-align-center modal-lg" style="max-width:100%">

            <!-- Modal content-->

            <div class="modal-content">

                <div class="modal-header">

                    <button type="button" class="close" data-dismiss="modal">&times;</button>

                    <h4>Detalles de la guía</h4>

                </div>

                <div class="modal-body">

                    <div class="row">

                        <div class="table-responsive">

                            <div class="col-md-12" style="padding-left: 0px;">

                                <table class="table table-bordered">

                                    <thead>

                                    <tr>

                                        <th>Folio</th>

                                        <th>Cliente</th>

                                        <th>Clave</th>

                                        <th>No. caja</th>

                                        <th>Guía</th>

                                        <th>No. de productos</th>

                                        <th>Volumen (m<sup>3</sup>)</th>

                                        <th>Peso (Kg)</th>

                                    </tr>

                                    </thead>

                                    <tbody>

                                    <tr>

                                        <td id="pedido_folio"></td>

                                        <td id="pedido_cliente"></td>

                                        <td id="pedido_clave"></td>

                                        <td id="pedido_nro"></td>

                                        <td id="pedido_guias"></td>

                                        <td class="text-right" id="pedido_cantidad"></td>

                                        <td class="text-right" id="pedido_volumen"></td>

                                        <td class="text-right" id="pedido_peso"></td>

                                    </tr>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                    <div class="row">

                        <div class="table-responsive">

                            <div class="col-md-12" style="padding-left: 0px;">

                                <table id="grid-detalles-pedidos"></table>

                                <div id="grid-pager2"></div>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>

                </div>

            </div>

        </div>

    </div>

</div>



<div class="modal fade" id="modal-transporte" role="dialog">

    <div class="vertical-alignment-helper">

        <div class="modal-dialog vertical-align-center modal-lg" style="max-width:100%">

            <!-- Modal content-->

            <div class="modal-content">

                <div class="modal-header">

                    <button type="button" class="close" data-dismiss="modal">&times;</button>

                    <h4>Transportes</h4>

                </div>

                <div class="modal-body">

                    <div class="row">

                        <select class="form-control chosen-select" name="almacen" id="transporte">

                            <option value="">Seleccione</option>

                        </select>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>

                    <button onclick="guardarTransporte()" type="button" class="btn btn-primary" id="buscarP"><span

                            class="fa fa-search"></span> Guardar

                    </button>

                </div>

            </div>

        </div>

    </div>

</div>



<div class="modal fade" id="modal-contenedores" role="dialog">

    <div class="vertical-alignment-helper">

        <div class="modal-dialog vertical-align-center modal-lg" style="max-width:100%">

            <!-- Modal content-->

            <div class="modal-content">

                <div class="modal-header">

                    <button type="button" class="close" data-dismiss="modal">&times;</button>

                    <h4>Pallet | Contenedores</h4>

                </div>

                <div class="modal-body">

                    <div class="row">

                        <select class="form-control chosen-select" name="almacen" id="contenedor">

                            <option value="">Seleccione</option>

                        </select>

                    </div>

                    <br>

                    <br>

                    <div class="row">

                        <div class="table-responsive">

                            <div class="col-md-12">

                                <table id="detalles" class="table table-bordered">

                                    <thead>

                                    <tr>

                                        <th>Acciones</th>

                                        <th>N°. Caja</th>

                                        <th>Clave Caja</th>

                                        <th>Tipo caja</th>

                                        <th>Guia</th>

                                        <th>Folio</th>

                                        <th>Volumen (m<sup>3</sup>)</th>

                                        <th>Peso (Kg)</th>

                                    </tr>

                                    </thead>

                                    <tbody></tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>

                    <button onclick="guardarPallet()" type="button" class="btn btn-primary" id="buscarP"><span

                            class="fa fa-search"></span> Guardar

                    </button>

                </div>

            </div>

        </div>

    </div>

</div>





<div class="modal fade" id="modal_fotos" role="dialog">

    <div class="vertical-alignment-helper">

        <div class="modal-dialog vertical-align-center modal-lg" style="width: 95% !important;">

            <div class="modal-content">

                <div class="modal-header">

                    <button type="button" class="close" data-dismiss="modal">&times;</button>

                    <h4 align="center">Foto de Empaque</h4>

                    <div class="modal-body">

                        <div class="row">

                            <div class="col-md-12">

                                <!--contenido-->

                                <div class="row" align="center">

                                    <form id="elegir1" class="form_img">

                                        <input type="hidden" name="action" value="subirImagen"/>

                                        <input type="hidden" name="idPedido" value="" class="idPedido"/>

                                        <input type="hidden" name="numeroImagen" value="1"/>

                                        <input type="hidden" name="foto_to_up_1" id="foto_to_up_1" value="">

                                        <input id="up_1" name="file" type="file" class="file_img"/><br/>

                                        <!--input type="submit" class="ui blue inline button" value="Subir archivo" /-->

                                    </form>

                                    <img class="fotos" src="" width="33%" id="foto1">

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <!-- Modal Contenido de Fotos Embarque-->

                <div class="modal-header align-center">

                    <h4 align="center">Fotos de Embarque</h4>

                </div>

                <div class="modal-body">

                    <div class="row">

                        <div class="col-md-4">

                            <div class="row col-md-12" align="center">

                                <form id="elegir1" class="form_img">

                                    <input type="hidden" name="action" value="subirImagen"/>

                                    <input type="hidden" name="idPedido" value="" class="idPedido"/>

                                    <input type="hidden" name="numeroImagen" value="2"/>

                                    <input type="hidden" name="foto_to_up_2" id="foto_to_up_2" value="">

                                    <input id="up_1" name="file" type="file" class="file_img"/><br/>

                                    <!--input type="submit" class="ui blue inline button" value="Subir archivo" /-->

                                </form>

                                <img class="fotos" src="" width="100%" id="foto2">

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="row col-md-12" align="center">

                                <form id="elegir1" class="form_img">

                                    <input type="hidden" name="action" value="subirImagen"/>

                                    <input type="hidden" name="idPedido" value="" class="idPedido"/>

                                    <input type="hidden" name="numeroImagen" value="3"/>

                                    <input type="hidden" name="foto_to_up_3" id="foto_to_up_3" value="">

                                    <input id="up_1" name="file" type="file" class="file_img"/><br/>

                                    <!--input type="submit" class="ui blue inline button" value="Subir archivo" /-->

                                </form>

                                <img class="fotos" src="" width="100%" id="foto3">

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="row col-md-12" align="center">

                                <form id="elegir1" class="form_img">

                                    <input type="hidden" name="action" value="subirImagen"/>

                                    <input type="hidden" name="idPedido" value="" class="idPedido"/>

                                    <input type="hidden" name="numeroImagen" value="4"/>

                                    <input type="hidden" name="foto_to_up_4" id="foto_to_up_4" value="">

                                    <input id="up_1" name="file" type="file" class="file_img"/><br/>

                                    <!--input type="submit" class="ui blue inline button" value="Subir archivo" /-->

                                </form>

                                <img class="fotos" src="" width="100%" id="foto4">

                            </div>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>

                </div>

            </div>

        </div>

    </div>

</div>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB3w-JXwq-Qjcsec-dP03a46kSd71c6pKQ">

</script>

<script type="text/javascript">

var init_modulo = 1;
    /**

     * @author Ricardo Delgado.

     * Busca y selecciona el almacen predeterminado para el usuario.

     */

    function almacenPrede() {

        

        $.ajax({

            type: "POST",

            dataType: "json",

            data: {

                idUser: '<?php echo $_SESSION["id_user"]?>',

                action: 'search_almacen_pre'

            },

            beforeSend: function (x) {

                if (x && x.overrideMimeType) {

                    x.overrideMimeType("application/json;charset=UTF-8");

                }

            },

            url: '/api/almacenPredeterminado/index.php',

            success: function (data) {

                if (data.success == true) {

                    $('#almacen').val(data.codigo.clave).trigger("chosen:updated");

                    cargarIslas();

                    cargarRutas();

                    console.log("cargar transporte1");

                    cargarTransportes();

                    //cargarcontenedores();

                }

            },

            error: function (res) {

                window.console.log(res);

            }

        });

    }


function strPad(input, pad_length, pad_string, pad_type){
    var output = input.toString();
    if (pad_string === undefined) { pad_string = ' '; }
    if (pad_type === undefined) { pad_type = 'STR_PAD_RIGHT'; }
    if (pad_type == 'STR_PAD_RIGHT') {
        while (output.length < pad_length) {
            output = output + pad_string;
        }
    } else if (pad_type == 'STR_PAD_LEFT') {
        while (output.length < pad_length) {
            output = pad_string + output;
        }
    } else if (pad_type == 'STR_PAD_BOTH') {
        var j = 0;
        while (output.length < pad_length) {
            if (j % 2) {
                output = output + pad_string;
            } else {
                output = pad_string + output;
            }
            j++;
        }
    }
    //output = output.replace('*', ' ');
    return output;
}

    function cargarTransportes() {

        console.log("Cargando transportes");

        $.ajax({

            type: "POST",

            dataType: "json",

            data: {

                almacen: $('#almacen').val(),
                ruta: $('#ruta').val(),
                action: 'cargarTransportes'

            },

            beforeSend: function (x) {

                if (x && x.overrideMimeType) {

                    x.overrideMimeType("application/json;charset=UTF-8");

                }

            },

            url: '/api/administracionembarque/lista/index_nikken.php',

            success: function (data) {

                if (data.status == true) {

                    console.log("transportes cargar", data);
                    console.log("max = ", data.max);
                    console.log("control_pallets_cont = ", data.control_pallets_cont);

                    if(data.control_pallets_cont == 'S')
                    {
                        $(".control_pallets_contenedores").show();
                    }
                    else
                    {
                        $("#n_control_pallets, #n_control_contenedores").val("");
                        $(".control_pallets_contenedores").hide();
                    }

                    var max = data.max;

                    var html = '<option value="">Seleccione</option>';
                    if($('#ruta').val())
                        html = '';

                    $.each(data.data, function (key, value) {

                        //html += '<option value="' + value['id'] + '" data-carga="' + value['capacidad_carga'] + '" data-fondo="' + value['fondo'] + '" data-ancho="' + value['ancho'] + '" data-alto="' + value['alto'] + '" data-orden="'+value['ID_OEmbarque']+'">' + strPad(value['nombre'], max, '*', 'STR_PAD_RIGHT') + " " + value['descripcion']+ ' ('+value['Placas']+')' + '</option>';
                        html += '<option value="' + value['id'] + '" data-carga="' + value['capacidad_carga'] + '" data-fondo="' + value['fondo'] + '" data-ancho="' + value['ancho'] + '" data-alto="' + value['alto'] + '" data-orden="'+value['ID_OEmbarque']+'" data-peso_pendiente="' + value['peso_pendiente'] + '" data-volumen_pendiente="' + value['volumen_pendiente'] + '">' +' ('+value['ID_Transporte']+') - '+value['descripcion']+' | Placa: '+value['Placas'] + '</option>';

                    })

                    $('#transporte').html(html).trigger("chosen:updated");

                    $('#select_transporte').html(html).trigger("chosen:updated");

                    ;



                }

            },

            error: function (res) {

                window.console.log(res);

            }

        });

    }



    function cargarcontenedores() {

        $.ajax({

            type: "POST",

            dataType: "json",

            url: '/api/administracionembarque/lista/index_nikken.php',

            data: {

                action: 'traer_contenedores',

            },

            beforeSend: function (x) {

                if (x && x.overrideMimeType) {

                    x.overrideMimeType("application/json;charset=UTF-8");

                }

            },

            success: function (data) {

                if (data.status == true) {

                    console.log("entra contendores");

                    var html = '<option value="">Seleccione</option>'

                    $.each(data.data, function (key, value) {

                        //console.log("dataa-con", value);

                        html += '<option value="' + value['contenedor'] + '">' + value['descripcion'] + '</option>'

                    })

                    $('#contenedorselect').html(html).trigger("chosen:updated");

                    ;

                }

            },

            error: function (data) {

                console.log("error");

            }

        });

    }



    function cargarIslas() {

        console.log("cargarIslas() Almacen => ", $('#almacen').val());
        $.ajax({

            type: "POST",

            dataType: "json",

            url: '/api/administracionembarque/lista/index_nikken.php',

            data: {

                action: 'cargarIslas',

                almacen: $('#almacen').val(),

                ruta: $("#ruta").val()

            },

            beforeSend: function (x) {

                /*
                if (x && x.overrideMimeType) {

                    x.overrideMimeType("application/json;charset=UTF-8");

                }*/

            },

            success: function (data) {

                $("#isla").empty();
                if (data.status == true) {

                    console.log("cargar islas");

                    var html = '';//'<option value="">Seleccione</option>'

                    $.each(data.data, function (key, value) {

                        html += '<option value="' + value['id'] + '">' + value['descripcion'] + '</option>'

                    })

                    $('#isla').html(html).trigger("chosen:updated");

                    //;

                    //console.log(data.sql);
                }

            },

            error: function (res) {

                window.console.log("ERROR ISLAS", res);

            }

        });

    }



    function cargarRutas() {


        $.ajax({

            type: "POST",

            dataType: "json",

            data: {

                action: 'cargarRutas',

                transporte: $("#select_transporte").val(),

                almacen: $('#almacen').val()

            },

            beforeSend: function (x) {

                if (x && x.overrideMimeType) {

                    x.overrideMimeType("application/json;charset=UTF-8");

                }

            },

            url: '/api/administracionembarque/lista/index_nikken.php',

            success: function (data) {

                console.log("Rutas = ", data);
                $('#ruta').html("").trigger("chosen:updated");
                if (data.status == true) {

                    var html = '<option value="">Seleccione</option>';

                    $.each(data.data, function (key, value) {

                        html += '<option value="' + value['clave'] + '">' + value['descripcion'] + '</option>';

                    })

                    $('#ruta').html(html).trigger("chosen:updated");

                }

            },

            error: function (res) {

                window.console.log("ERROR Rutas", res);

            }

        });

    }



    function sumadores()//EDG

    {
        console.log("->Sumadores()<-");
        var myData = $("#grid-table").jqGrid('getRowData');

        var j = 0;

        var guias = 0, pesos = 0.00, volumen = 0.00, piezas = 0, destinatarios = 0;

        $.each(myData, function (key, value) {

            if (myData[j].total_guias == "" || myData[j].total_guias == null) {

                var data_guias = "0";

            } else {

                var data_guias = myData[j].total_guias;

            }

            if (myData[j].id_destinatario == "" || myData[j].id_destinatario == null) {

                //var data_destinatarios = "0";

            } else {

                destinatarios++;

            }

            if (myData[j].total_peso == "" || myData[j].total_peso == null) {

                var data_peso = "0";

            } else {

                var data_peso = myData[j].total_peso;

            }

            if (myData[j].volumen == "" || myData[j].volumen == null) {

                var data_volumen = "0";

            } else {

                var data_volumen = myData[j].volumen;

            }

            if (myData[j].piezas == "" || myData[j].piezas == null) {

                var data_piezas = "0";

            } else {

                var data_piezas = myData[j].piezas;

            }

            guias = guias + parseFloat(data_guias);

            pesos = pesos + parseFloat(data_peso);

            volumen = volumen + parseFloat(data_volumen);

            piezas = piezas + parseFloat(data_piezas);

            j++;

        });

//nro_de_pedidos
//nro_de_entregas
//nro_pallets

        $("#nro_de_pedidos").val(j);

        $("#nro_de_entregas").val(destinatarios);

        $("#txt_tot_guias").val(guias);

        $("#txt_tot_peso").val(pesos.toFixed(2));

        $("#txt_tot_volumen").val(volumen.toFixed(5));

        $("#txt_tot_piezas").val(piezas);

    }



    $(function ($) {

        sessionStorage.folios = "";
        sessionStorage.sufijos = "";
        almacenPrede();



        var grid_selector = "#grid-table";

        var pager_selector = "#grid-pager";



        //resize to fit page size

        $(window).on('resize.jqGrid', function () {

            $(grid_selector).jqGrid('setGridWidth', $(".page-content").width() - 60);

        })

        //resize on sidebar collapse/expand

        var parent_column = $(grid_selector).closest('[class*="col-"]');

        $(document).on('settings.ace.jqGrid', function (ev, event_name, collapsed) {

            if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {

                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!

                setTimeout(function () {

                    $(grid_selector).jqGrid('setGridWidth', $(window).width() - 200);

                }, 0);

            }

        })

        folio = sessionStorage.folios;

        //laur='/api/administracionembarque/lista/index_nikken.php?folio='. sessionStorage.folios;

        $(grid_selector).jqGrid({

            url: '/api/administracionembarque/lista/index_nikken.php',

            datatype: "json",

            shrinkToFit: false,

            cache: false,

            height: 'auto',

            postData: {

                action: 'cargarGridPrincipal',

                folios: sessionStorage.folios

            },
            success: function(data) {

                   console.log("cargarGridPrincipal = ",data);
            },

            error: function(data) {

                   console.log("cargarGridPrincipal ERROR = ",data);
            },

            mtype: 'POST',

            //colNames: ["Acción", "Embarcar", "FolioHide", "Sufijo", "Folio", "Fecha Pedido", "Fecha Entrega","Pedido", "C Dest", "Destinatario", "C de SUC", "Direccion", "C. Postal", "Colonia", "Latitud", "Longitud", "Ruta","Horario Planeado", "Total guías", "Peso total", "Volumen", "Piezas", "Total Cajas", "Total Pallets", "Clave Cliente", "Cliente | Empresa", "Razon Social", "Zona Embarque (Isla)", "Listo Para Entrega"],
            colNames: ["Acción", "Embarcar", "FolioHide", "Sufijo", "Folio", "Fecha Pedido", "Fecha Entrega","Horario Planeado", "Ruta","Pedido", "Clave Cliente", "C Dest", "Destinatario", "C de SUC", "Direccion", "C. Postal", "Colonia", "Latitud", "Longitud", "Total Cajas", "Piezas", "Total guías", "Peso total", "Volumen", "Total Pallets", "Cliente | Empresa", "Razon Social", "Zona Embarque (Isla)", "Listo Para Entrega"],

            colModel: [

                {name: 'myac', index: 'myac', width: 100, align: "center", fixed: true, sortable: false, resize: false, formatter: imgFormatDetallePedido},

                {name: 'embarcar', index: 'embarcar', width: 80, fixed: true, sortable: false, resize: false, align: "center", formatter: "checkbox",formatoptions: {disabled: false}, edittype: "checkbox", editoptions: {value: "Yes:No", defaultValue: "Yes"}, stype: "select", searchoptions: {sopt: ["eq", "ne"], value: ":Any;true:Yes;false:No"}},

                {name: 'folio', index: 'folio', width: 100, editable: false, sortable: false, hidden: true},

                {name: 'sufijo', index: 'sufijo', width: 100, editable: false, sortable: false, hidden: true},

                {name: 'foliosuf', index: 'foliosuf', width: 100, editable: false, sortable: false},

                {name: 'fec_pedido', index: 'fec_pedido', width: 100, editable: false, sortable: false},

                {name: 'fec_entrega', index: 'fec_entrega', width: 100, editable: false, sortable: false},

                {name: 'rango_hora', index: 'rango_hora', width: 150, editable: false, sortable: false},

                {name: 'ruta', index: 'ruta', width: 100, editable: false, sortable: false},

                {name: 'pedido', index: 'pedido', width: 100, editable: false, sortable: false, hidden: true},

                {name: 'cliente', index: 'cliente', width: 100, editable: false, sortable: false},

                {name: 'id_destinatario', index: 'id_destinatario', width: 100, editable: false, sortable: false},

                {name: 'destinatario', index: 'destinatario', width: 150, editable: false, sortable: false},

                {name: 'clave_sucursal', index: 'clave_sucursal', width: 100, editable: false, sortable: false, hidden: true},

                {name: 'Direccion_Cliente', index: 'Direccion_Cliente', width: 280, editable: false, sortable: false},

                {name: 'cpostal', index: 'cpostal', width: 150, editable: false, sortable: false},

                {name: 'colonia', index: 'colonia', width: 150, editable: false, sortable: false},

                {name: 'latitud', index: 'latitud', width: 100, editable: false, sortable: false},

                {name: 'longitud', index: 'longitud', width: 100, editable: false, sortable: false},

                {name: 'total_cajas', index: 'total_cajas', width: 100, editable: false, sortable: false, align: 'right'},

                {name: 'piezas', index: 'piezas', width: 100, editable: false, align: 'right', sortable: false},

                {name: 'total_guias', index: 'total_pedidos', width: 100, editable: false, align: 'right', sortable: false},

                {name: 'total_peso', index: 'total_productos', width: 100, editable: false, align: 'right', sortable: false},

                {name: 'volumen', index: 'volumen', width: 100, editable: false, align: 'right', sortable: false},

                {name: 'total_pallets', index: 'total_pallets', width: 100, editable: false, sortable: false, hidden: true},

                {name: 'razon_social', index: 'razon_social', width: 300, editable: false, sortable: false},
                {name: 'razon_social2', index: 'razon_social2', width: 300, editable: false, sortable: false, hidden: true},
                {name: 'isla', index: 'isla', width: 160, editable: false, sortable: false},
                {name: 'puedo_entregar', index: 'puedo_entregar', width: 160, editable: false, sortable: false, hidden: true},

            ],

            beforeSelectRow: function (rowid, e) {

                var $self = $(this),

                    iCol = $.jgrid.getCellIndex($(e.target).closest("td")[0]),

                    cm = $self.jqGrid("getGridParam", "colModel"),

                    localData = $self.jqGrid("getLocalRow", rowid);

                if (cm[iCol].name === "closed") {

                    localData.closed = $(e.target).is(":checked");

                }

                return true; // allow selection

            },

            rowNum: 10,

            rowList: [10, 30, 40, 50, 70, 80, 90, 100],

            pager: pager_selector,

            viewrecords: true,

            sortorder: "desc",

            autowidth: true,

            //loadComplete: sumadores,

            loadComplete: function(data){
                console.log("SUCCESS grid-principal = ", data);
                sumadores();
            }, loadError: function(data){
                console.log("ERROR grid-principal = ", data);
            }


        });



        // Setup buttons

        $(grid_selector).jqGrid('navGrid', '#grid-pager', {

            edit: false,

            add: false,

            del: false,

            search: false

        }, {height: 200, reloadAfterSubmit: true});



        $(window).triggerHandler('resize.jqGrid'); //trigger window resize to make the grid get the correct size



        function imgFormatDetallePedido(cellvalue, options, rowObject) {

            var embarque = rowObject[2],

                folio = rowObject[2], //folio

                sufijo = rowObject[3], //sufijo

                clave = rowObject[10], //cliente

                //cliente = rowObject[24],
                cliente = rowObject[26], //razon_social2

                clave_destinatario = rowObject[11],//id_destinatario

                destinatario = rowObject[12],//destinatario

                ruta = rowObject[8],//ruta

                guias = rowObject[21],//total_guias

                peso = rowObject[22],//total_peso

                volumen = rowObject[23], //volumen

                total_cajas = rowObject[19], //total_cajas

                total_pallets = rowObject[24], //total_pallets
                puedo_entregar = rowObject[28];//puedo_entregar
//caja_guias
//caja_volumen
//caja_peso
            // console.log(rowObject); EDG

/*
                    <a href="#" onclick="printPDF('` + embarque + `')" title="Imprimir PDF">

                      <i class="fa fa-print"></i>PDF

                    </a>
*/
           var cve_cia = <?php echo $_SESSION['cve_cia']; ?>;
/*
            var html = `<div style="white-space:initial;">

                    <a href="#" onclick="verDetalleDeCajas('` + embarque + `','` + sufijo + `','` + cliente + `','` + clave + `','` + guias + `','` + peso + `','` + volumen + `','` + clave_destinatario + `','` + destinatario + `','` + ruta + `','` + total_cajas + `','` + total_pallets + `')">

                      <i class="fa fa-search" alt="Detalle" title="Detalle"></i>

                    </a>
                    &nbsp;
                    <a href="#" onclick="embarqueFoto('` + folio + `')">

                      <i class="fa fa-camera" title="Fotos de embarque"></i>

                    </a>
                    &nbsp;
                    <a href="/api/koolreport/export/reportes/auditoria/etiquetas?folio=`+embarque+`&cve_cia=`+cve_cia+`" target="_blank" title="Etiquetas Cajas"><i class="fa fa-barcode"></i></a>

                    &nbsp;
                    <input type="checkbox" class="checkbox-asignator" id='`+'chk_'+folio+`' value='`+folio+`' />
                    

                </div>`;
*/
           var html = "";

                    html += '<a href="#" onclick="verDetalleDeCajas(\''+ embarque +'\',\''+ sufijo +'\',\''+ cliente +'\',\''+ clave +'\',\''+ guias +'\',\''+ peso +'\',\''+ volumen +'\',\''+ clave_destinatario +'\',\''+ destinatario +'\',\''+ ruta +'\',\''+ total_cajas +'\',\''+ total_pallets +'\',\''+ puedo_entregar +'\')"><i class="fa fa-search" alt="Detalle" title="Detalle"></i></a>&nbsp;';
                    

                    html += '<a href="#" onclick="ver_fotos(\''+ folio +'\')"><i class="fa fa-camera" title="Fotos de embarque"></i></a>&nbsp;';

                    html += '<a href="/api/koolreport/export/reportes/auditoria/etiquetas?folio='+embarque+'&cve_cia='+cve_cia+'" target="_blank" title="Etiquetas Cajas"><i class="fa fa-barcode"></i></a>&nbsp;';

                    html += '<input type="checkbox" class="checkbox-asignator" title="Generar Etiquetas Cajas" style="cursor:pointer;" id="'+folio+'" value="'+folio+'" />';

            return html;

        }


/*
        function checaembarque(cellvalue, options, rowObject) {

            var embarque = rowObject[2];

            // console.log(rowObject); EDG

            var html = `<label for="embarcao"><h4>>></h4><input type="checkbox"  name="embarcao" ></label>`;

            return html;

        }
*/




        $(document).one('ajaxloadstart.page', function (e) {

            $(grid_selector).jqGrid('GridUnload');

            $('.ui-jqdialog').remove();

        });

    });



    function fotosEmbarque() {

        console.log();

    }


/*
    function printPDF(id) 
    {

        var title = "Guía de Embarque";

        var cia = <?php echo $_SESSION['cve_cia'] ?>;

        var content = '';

        console.log("PDF1");

        $.ajax({

            url: "/api/administracionembarque/lista/index_nikken.php",

            type: "POST",

            data: {

                "action": "getDataPDF",

                "id": id

            },

            success: function (data, textStatus, xhr) {

                console.log("PDF2");

                console.log(data);

                var data = JSON.parse(data);

                var content_wrapper = document.createElement('div');

                //Encabezado

                var table_header = document.createElement('table');

                table_header.style.width = "100%";

                table_header.style.borderSpacing = "0";

                table_header.style.borderCollapse = "collapse";

                var thead_header = document.createElement('thead');

                var tbody_header = document.createElement('tbody');



                var head_content_header = '<tr>' +

                    '<th style="border: 1px solid #ccc; font-size:12px;">Folio</th>' +

                    '<th style="border: 1px solid #ccc; font-size:12px;">Fecha Entrega</th>' +

                    '<th style="border: 1px solid #ccc; font-size:12px;">Destino</th>' +

                    '<th style="border: 1px solid #ccc; font-size:12px;">Comentarios</th>' +

                    '<th style="border: 1px solid #ccc; font-size:12px;">Status</th>' +

                    '<th style="border: 1px solid #ccc; font-size:12px;">Peso</th>' +

                    '<th style="border: 1px solid #ccc; font-size:12px;">Volumen</th>' +

                    '<th style="border: 1px solid #ccc; font-size:12px;">Total Cajas</th>' +

                    '<th style="border: 1px solid #ccc; font-size:12px;">Total Piezas</th>' +

                    '</tr>';



                var body_content_header = '<tr>' +

                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + data.header.id + '</td> ' +

                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + data.header.fecha_entrega + '</td> ' +

                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + data.header.destino + '</td> ' +

                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + data.header.comentarios + '</td> ' +

                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + data.header.status + '</td> ' +

                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + data.header.peso + '</td> ' +

                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + data.header.volumen + '</td> ' +

                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + data.header.total_cajas + '</td> ' +

                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + data.header.total_piezas + '</td> ' +

                    '</tr>';

                //Detalle

                var table = document.createElement('table');

                table.style.width = "100%";

                table.style.borderSpacing = "0";

                table.style.borderCollapse = "collapse";

                var thead = document.createElement('thead');

                var tbody = document.createElement('tbody');

                var head_content = '<th style="border: 1px solid #ccc; font-size:12px;">Clave</th>' +

                    '<th style="border:1px solid #ccc; font-size:12px;">Descripción</th>' +

                    '<th style="border:1px solid #ccc; font-size:12px;">Cantidad</th>' +

                    '<th style="border:1px solid #ccc; font-size:12px;">Lote</th>    ' +

                    '<th style="border:1px solid #ccc; font-size:12px;">Caducidad</th>' +

                    '<th style="border:1px solid #ccc; font-size:12px;">Serie</th>   ' +

                    '<th style="border:1px solid #ccc; font-size:12px; width:15%">Costo Promedio</th>   ' +

                    '<th style="border:1px solid #ccc; font-size:12px;">Subtotal</th>   ' +

                    '</tr>';

                var body_content = '';



                data.body.forEach(function (item, index) {

                    body_content += '<tr>' +

                        '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: left">' + item.clave + '</td> ' +

                        '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: left">' + item.descripcion + '</td> ' +

                        '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + item.cantidad + '</td> ' +

                        '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + item.lote + '</td> ' +

                        '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + item.caducidad + '</td> ' +

                        '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + item.serie + '</td> ' +

                        '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + item.costoPromedio + '</td> ' +

                        '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + item.subtotal + '</td> ' +

                        '</tr>  ';

                });



                var table_total = document.createElement('table');

                table_total.style.width = "100%";

                table_total.style.borderSpacing = "0";

                table_total.style.borderCollapse = "collapse";

                var tbody_total = document.createElement('tbody');

                var total = '<tr>' +

                    '<th style="border: 1px solid #ccc; font-size:12px;"></th>' +

                    '<th style="border:1px solid #ccc; font-size:12px;"></th>' +

                    '<th style="border:1px solid #ccc; font-size:12px;"></th>' +

                    '<th style="border:1px solid #ccc; font-size:12px;"></th>    ' +

                    '<th style="border:1px solid #ccc; font-size:12px;"></th>' +

                    '<th style="border:1px solid #ccc; font-size:12px;"></th>   ' +

                    '<th style="border:1px solid #ccc; font-size:12px; width:15%">Total</th>   ' +

                    '<th style="border:1px solid #ccc; font-size:12px;">' + data.total + '</th>   ' +

                    '</tr>';



                console.log("PDF3");

                tbody_header.innerHTML = body_content_header;

                thead_header.innerHTML = head_content_header;

                table_header.appendChild(thead_header);

                table_header.appendChild(tbody_header);

                tbody.innerHTML = body_content;

                thead.innerHTML = head_content;

                tbody_total.innerHTML = total;

                table.appendChild(thead);

                table.appendChild(tbody);

                table.appendChild(tbody_total);

                content_wrapper.appendChild(table_header);

                content_wrapper.appendChild(document.createElement('br'));

                content_wrapper.appendChild(table);

                content = content_wrapper.innerHTML;

                console.log("PDF4");

                //Creando formulario para ser enviado



                var form = document.createElement("form");

                form.setAttribute("method", "post");

                form.setAttribute("action", "/api/reportes/generar/pdf.php");

                form.setAttribute("target", "_blank");

                var input_content = document.createElement('input');

                var input_title = document.createElement('input');

                var input_cia = document.createElement('input');

                input_content.setAttribute('type', 'hidden');

                input_title.setAttribute('type', 'hidden');

                input_cia.setAttribute('type', 'hidden');

                input_content.setAttribute('name', 'content');

                input_title.setAttribute('name', 'title');

                input_cia.setAttribute('name', 'cia');

                input_content.setAttribute('value', content);

                input_title.setAttribute('value', title);

                input_cia.setAttribute('value', cia);

                form.appendChild(input_content);

                form.appendChild(input_title);

                form.appendChild(input_cia);

                document.body.appendChild(form);

                form.submit();

                console.log("PDFn");

            }

        });

    }
*/


    $(function ($) {

        var grid_selector = "#grid-detalles-caja";

        var pager_selector = "#grid-detalles-caja-pager";



        //resize on sidebar collapse/expand

        var parent_column = $(grid_selector).closest('[class*="col-"]');

        $(document).on('settings.ace.jqGrid', function (ev, event_name, collapsed) {

            if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {

                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!

                setTimeout(function () {

                    $("#grid-detalles-caja").jqGrid('setGridWidth', $("#modal-detalles-cajas .modal-body").width() - 60);

                }, 0);

            }

        })
        //********************************************************************************************************
        //********************************************************************************************************
        //********************************************************************************************************
        //AÚN FALTA REALIZAR QUE SI POR CASUALIDAD EL PEDIDO TIENE CAJAS Y PALLETS QUE CUANDO SE MANDE EL ÚLTIMO REGISTRO SEA QUE ENTRE LOS 2 TIPOS TENGA BAN_EMBARCADO = S
        //********************************************************************************************************
        //********************************************************************************************************
        //********************************************************************************************************

        $(grid_selector).jqGrid({

            url: '/api/administracionembarque/lista/index_nikken.php',

            datatype: "local",

            shrinkToFit: false,

            height: 'auto',

            mtype: 'POST',

            colNames: ["Acción", "# Empaque", "Descripción", "Clave", "Artículo", "Lote | Serie", "Caducidad", "Empacadas", "Pallet", "License Plate (LP)", "Embarcar", "id_embarque", "num_rows", "ban_embarcado", "tipo_embarque"],

            colModel: [
                {name: 'acciones', index: 'acciones', width: 60, editable: false, align: 'center', hidden: true, sortable: false,formatter: imageFormat},

                {name: 'nro', index: 'nro', align: 'right', width: 100, editable: false, sortable: false},

                {name: 'caja', index: 'caja', width: 180, editable: false, sortable: false},

                {name: 'cve_articulo', index: 'cve_articulo', width: 120, editable: false, sortable: false},

                {name: 'des_articulo', index: 'des_articulo', width: 180, editable: false, sortable: false},

                {name: 'cve_lote', index: 'cve_lote', width: 120, editable: false, sortable: false},

                {name: 'caducidad', index: 'caducidad', width: 100, editable: false, align: 'right', sortable: false},

                {name: 'empacadas', index: 'empacadas', width: 90, editable: false, align: 'right', sortable: false},

                {name: 'pallet', index: 'pallet', width: 120, editable: false, align: 'left', sortable: false, hidden: true},

                {name: 'CveLP', index: 'CveLP', width: 180, editable: false, align: 'left', sortable: false},

                {name: 'embarcarxpartes', index: 'embarcarxpartes', width: 120, editable: false, align: 'center', hidden: false, sortable: false,formatter: imageFormat2},

                {name: 'id_emb', index: 'id_emb', width: 130, editable: false, align: 'left', sortable: false, hidden: true},
                {name: 'num_rows', index: 'num_rows', width: 130, editable: false, align: 'left', sortable: false, hidden: true},
                {name: 'ban_embarcado', index: 'ban_embarcado', width: 130, editable: false, align: 'left', sortable: false, hidden: true},
                {name: 'tipo_embarque', index: 'tipo_embarque', width: 130, editable: false, align: 'left', sortable: false, hidden: true},
            ],
/*
            colNames: ["Partida", "Clave caja", "Tipo Caja", "Guía", "Volumen (m3)", "Peso(Kg)", "Acción"],

            colModel: [

                {name: 'nro', index: 'nro', width: 80, editable: false, sortable: false},

                {name: 'clave', index: 'clave', width: 100, editable: false, sortable: false},

                {name: 'tipo', index: 'tipo', width: 150, editable: false, sortable: false},

                {name: 'guia', index: 'guia', width: 248, editable: false, sortable: false},

                {name: 'volumen', index: 'volumen', width: 100, editable: false, align: 'right', sortable: false},

                {name: 'peso', index: 'peso', width: 100, editable: false, align: 'right', sortable: false},

                {

                    name: 'acciones',

                    index: 'acciones',

                    width: 100,

                    editable: false,

                    align: 'center',

                    sortable: false,

                    formatter: imageFormat

                }

            ],
*/
            //rowNum: 10,

            rowList: [10, 20, 30],

            pager: pager_selector,

            viewrecords: true,

            loadComplete: function(data){
                console.log("SUCCESS grid-detalles-caja = ", data);

                $("#num_pallets").val(data.num_pallets);
                console.log("++++++++++++++++++++++++++");
                console.log("num_pallets = ",$("#num_pallets").val());
                console.log("++++++++++++++++++++++++++");

                if(parseFloat($("#num_pallets").val()) > 0)
                {

                    $("#total_pallets").text($("#num_pallets").val());
                    $("#caja_guias").text($("#num_pallets").val());
                }

            }, loadError: function(data){
                console.log("ERROR grid-detalles-caja = ", data);
            }

        });



        // Setup buttons

        $(grid_selector).jqGrid('navGrid', pager_selector, {

            edit: false,

            add: false,

            del: false,

            search: false

        }, {height: 200, reloadAfterSubmit: true});



        function imageFormat(cellvalue, options, rowObject) {

            var nro = rowObject[0];

            var guia = rowObject[3];

            var volumen = rowObject[4];

            var val = "'" + nro + "', '" + guia + "'" + ", '" + volumen + "'";

            var html = "";

            html = '<a href="#" onclick="cargarGridDetallesPedidos(' + val + ')"><i class="fa fa-search" title="Detalles"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

            return html;

        }

        function imageFormat2(cellvalue, options, rowObject) {

            var id_emb = rowObject[11];
            var num_rows = rowObject[12];
            var ban_embarcado = rowObject[13];
            var tipo_embarque = rowObject[14];

            //var val = "'" + nro + "', '" + guia + "'" + ", '" + volumen + "'";

            var html = "";

            if(id_emb != "" && num_rows > 1 && ban_embarcado == 'N')
                html = '<input type="button" class="btn btn-primary btn-xs" value="Embarcar" onclick="embarcarxpartes('+id_emb+', \''+tipo_embarque+'\')" />' ;
            else if(ban_embarcado == 'S')
                html = "<b>Enviado</b>";


            return html;

        }


        $(window).triggerHandler('resize.jqGrid'); //trigger window resize to make the grid get the correct size

        $(document).one('ajaxloadstart.page', function (e) {

            $(grid_selector).jqGrid('GridUnload');

            $('.ui-jqdialog').remove();

        });


    });



    $(function ($) {

        var grid_selector = "#grid-detalles-pedidos";

        var pager_selector = "#grid-pager2";



        //resize to fit page size

        $(window).on('resize.jqGrid', function () {

            $("#grid-detalles-pedidos").jqGrid('setGridWidth', $("#modal-detalles-pedido .modal-body").width() - 2);

        })



        $(grid_selector).jqGrid({

            url: '/api/administracionembarque/lista/index_nikken.php',

            shrinkToFit: false,

            height: 250,

            mtype: 'POST',

            colNames: ["Clave", "Artículo", "Cantidad", "Peso(Kg)"],

            colModel: [

                {name: 'clave', index: 'clave', width: 88, editable: false, sortable: false},

                {name: 'articulo', index: 'articulo', width: 350, editable: false, sortable: false},

                {name: 'cantidad', index: 'cantidad', width: 80, editable: false, align: 'right', sortable: false,},

                {name: 'peso', index: 'peso', width: 120, editable: false, align: 'right', sortable: false}

            ],

            rowNum: 10,

            rowList: [10, 20, 30],

            pager: pager_selector,

            viewrecords: true,

            loadComplete: function () {

                $('#pedido_cantidad').text($("#grid-detalles-pedidos").getGridParam("reccount"));

            }

        });



        // Setup buttons

        $(grid_selector).jqGrid('navGrid', '#grid-pager', {

            edit: false,

            add: false,

            del: false,

            search: false

        }, {height: 200, reloadAfterSubmit: true});



        $(window).triggerHandler('resize.jqGrid'); //trigger window resize to make the grid get the correct size

        $(document).one('ajaxloadstart.page', function (e) {

            $(grid_selector).jqGrid('GridUnload');

            $('.ui-jqdialog').remove();

        });

    });

</script>

<script>

    var folio = "";

    $(document).ready(function () {

        //initmap();

        sessionStorage.clear();

        //sessionStorage.folios="";

        //var foliox = sessionStorage.folios;

        <?php session_start(); $_SESSION['folios'] = "";   ?>

        cargarDatosGridPrincipal();
        //TODO Alberto formatear checkboxs

        $(".marcados").hover(function () {

            $(this).css("background-color", "#1b7cf5");

        }, function () {

            $(this).css("background-color", "#ff9c00");

        });

        $("#almacen").on('change', function (e) {

            cargarIslas();
            cargarRutas();
            cargarTransportes();

            cargarDatosGridPrincipal();//e.target.value

            sessionStorage.almacen=$(this).val();

            $.ajax({

                type: "POST",

                dataType: "json",

                data: {

                    id: sessionStorage.almacen,

                    action: 'damecoordenadas'

                },

                url: '/api/administracionembarque/lista/index_nikken.php',

                success: function (data) {

                    sessionStorage.longitud=data.longitud;

                    sessionStorage.latitud=data.latitud;

                },

            });

        });

        $("#grid-table td[aria-describedby=grid-table_embarcar] input[type=checkbox]").on('change', function (e) {

            var status = $(this).checked;

            if (status) {

                sessionStorage.checado = "Yes";

            } else {

                sessionStorage.checado = "No";

            }

        });

        $(function () {

            $('.chosen-select').chosen();
            $('.chosen-select-deselect').chosen({
                allow_single_deselect: true
            });

        });



        $('#modal-detalles-pedido').on('shown.bs.modal', function (e) {

            $("#grid-detalles-pedidos").jqGrid('setGridWidth', $("#modal-detalles-pedido .modal-body").width());

        });



        $('#modal-detalles-cajas').on('shown.bs.modal', function (e) {

            $("#grid-detalles-caja").jqGrid('setGridWidth', $("#modal-detalles-cajas .modal-body").width() - 60);

        });



        $(window).on('resize.jqGrid', function () {

            $("#grid-detalles-pedidos").jqGrid('setGridWidth', $("#modal-detalles-pedido .modal-body").width());

            $("#grid-detalles-caja").jqGrid('setGridWidth', $("#modal-detalles-cajas .modal-body").width() - 60);

        })



        $("#grid-table").jqGrid('setGridWidth', $("#grid-table").parent().width());



        $('#contenedorselect').on('change', function (e) {

            alert('guardar contenedor');

        });

//        $(".cajaselect").on('change', function (e) {

//            //dime el valor

//        });





        //InitializeMap();
        $("#embarcar-btn").removeClass("disabled");
    });

</script>

<script>

    function selectcaja(folio) {

        //TODO Alberto enviar folio como marcado

        alert(folio);

    }

    $("#btn-asignar-todo").on('click', function (e) {

        console.log("asignar");

        var $checkboxes = $('#embarcao');

        if (e.target.checked) {

            if ($checkboxes.length > 0) {

                $checkboxes.each(function (i, v) {

                    v.checked = true;

                });

            }

        }

        else {

            if ($checkboxes.length > 0) {

                $checkboxes.each(function (i, v) {

                    v.checked = false;

                });

            }

        }

        getCheck();

    });





    setTimeout(function () {

        cargarDatosGridPrincipal();

        $('#ruta').trigger('change');
        $('#ruta').trigger("chosen:updated");
        //cargarTransportes();

        $('#select_transporte').trigger('change');
        $('#select_transporte').trigger("chosen:updated");


    }, 2000);

    $("#cliente, #colonia, #cpostal, #buscar").keyup(function(){
        cargarDatosGridPrincipal();
    });



    function cargarDatosGridPrincipal() {

        //TODO Alberto , mandar sessionStorage.folios para filtrar exceptuando ellos

        console.log("Cargar Grid Principal");

        folio = sessionStorage.folios;

        console.log("ruta_send = ", $("#ruta").val());

        if(!$("#isla").val() && !$("#buscar").val() && !$("#ruta").val() && !$("#cliente").val() && !$("#colonia").val() && !$("#cpostal").val())
        {
            //swal("Error","Debe Seleccionar un Filtro", "error");
            return;
        }

        console.log("***************************************");
        console.log("almacen:", $("#almacen").val());
        console.log("isla:", $("#isla").val());
        console.log("texto:", $("#buscar").val());
        console.log("ruta:", $("#ruta").val());
        console.log("cliente:", $("#cliente").val());
        console.log("colonia:", $("#colonia").val());
        console.log("cpostal:", $("#cpostal").val());
        console.log("folios:", sessionStorage.folios);
        console.log("***************************************");



        $('#grid-table').jqGrid('clearGridData').jqGrid('setGridParam', {

            postData: {

                almacen: $("#almacen").val(),

                isla: $("#isla").val(),

                texto: $("#buscar").val(),

                ruta: $("#ruta").val(),

                cliente: $("#cliente").val(),

                colonia: $("#colonia").val(),

                cpostal: $("#cpostal").val(),

                folios: sessionStorage.folios,

                action: 'cargarGridPrincipal'

            },

            datatype: 'json',

            gridComplete: function (data) {

                // var input = $("#grid-table input[type=checkbox]").attr({id: 'embarcao', name: 'embarcao'});
                //console.log("DATA FOLIOS = ", data);

                $("#grid-table td[aria-describedby=grid-table_embarcar] input[type=checkbox]").wrap("<div class='marcados'><label style='margin-top: 0px;width:100%;height:100%;'><h4 style='margin-top: 8px;width:100%;height:100%;'>>></h4></label></div>");

                //input.appendTo(label);

                //$("#grid-table input[type=checkbox]").parent().append("<h4>>></h4>");

                // $("input[type=checkbox]").parent().append("<div id='lanzar'><h4>>></h4></div>");

                // $("#grid-table input[type=checkbox]").css({left: 0, display: 'block'});

                $("#grid-table td[aria-describedby=grid-table_embarcar] input[type=checkbox]").css('position', 'absolute');

                $("#grid-table td[aria-describedby=grid-table_embarcar] input[type=checkbox]").css('top', '-20px');

                $("#grid-table label").css('cursor', 'pointer');

                $(".marcados").css('cursor', 'pointer');

                $(".marcados").css('background-color', '#ff9c00');

                $(".marcados").css('height', '30px');

                $(".marcados").hover(function () {

                    $(this).css("background-color", "#1b7cf5");

                }, function () {

                    $(this).css("background-color", "#ff9c00");

                });



                //$("#lanzar").css('display','inline-block');

                //var $checkboxes = $('td[aria-describedby="grid-table_embarcar"] input[type="checkbox"]');





            }

        }).trigger('reloadGrid', [{

            current: true, folios: sessionStorage.folios

        }]);

        /* EDG117 */

//       $.ajax({

//         type: "POST",

//         dataType: "json",

//         url: '/api/administracionembarque/lista/index_nikken.php',

//         data: {

//           action: 'verificarOrdenDeEmbarque',

//           isla :  $("#isla").val(),

//           ruta: $("#ruta").val(),

//           almacen: $("#almacen").val(),



//         },

//         success: function(data) {

//           console.log("rsponce",data);

// //           return;

//           infoTemporal.ordenEmbarque = parseInt(data.orden)+1;

//           //$("#folio-embarque").text('Folio de embarque: '+infoTemporal.ordenEmbarque)



//           $("#txt_tot_guias").val(data.guias);

//           $("#txt_tot_peso").val(data.peso);

//           $("#txt_tot_volumen").val(data.volumen);

//           $("#txt_tot_piezas").val(data.piezas);

//         },

//       });

//       /* EDG117 */




        $('#grid-table').on('click', 'label input[type="checkbox"]', function (evt) {

            // $('.marcados').on('click', function() {

            // $('#grid-table label input[type="checkbox"]').on('click', function() {

            var EntregaCliente = 0;
            if($("#btn-EntregarClientes").is(":checked"))
                EntregaCliente = 1;

            if($("#select_transporte").val() != "" || EntregaCliente == 1)
            {
                console.log('best', 'click');

                evt.stopPropagation();

                evt.preventDefault();

                evt.stopImmediatePropagation();

                $("#select_transporte").prop("disabled", true);

                getCheck();

                if(EntregaCliente == 1) $("#btn-EntregarClientes").prop("disabled", true);

                // return;
            }
            else
            {
                swal({

                    title: "No ha seleccionado Transporte",

                    text: "Debe seleccionar un transporte para poder agregar un pedido",

                    type: "error"

                });

            }

            sumadores();

        });
                $("#ruta").val($("#ruta_selected").val()).trigger("chosen:updated");
                $('.chosen-select').chosen().trigger("chosen:updated");
                console.log("RUTA SELECCIONADA = ", $("#ruta_selected").val(), " | RUTA = ", $("#ruta").val());


    }
    var str = "";

    function getCheck() {



        var myGrid = $('#grid-table'), i, rowData, folios = [],

            rowIds = myGrid.jqGrid("getDataIDs"),

            n = rowIds.length;

        //folios="";

        //sessionStorage.folios="";

        // sessionStorage.clear();

        //sessionStorage.folios="";

        sessionStorage.cargadatablacajas = 0;

        //TODO Alberto esto es lo que es y listo al valor seleccionado entrarlo a sessionStorage.folio

        //para obtener el valor de un campo de localstorage almacenado como json

        //STUFFF= JSON.parse(sessionStorage.getItem('F10121774'))

        //STUFFF.Direccion_Cliente



        selRowId = myGrid.jqGrid('getGridParam', 'selrow');

        //seleccion = selRowId;

        //console.log("selRowId", selRowId);

        seleccion = "";

        // celValue = myGrid.jqGrid ('getCell', selRowId, 'folio');

        rowData = myGrid.jqGrid("getRowData", selRowId);


        console.log("rowData = ", rowData);
        console.log("rowData.total_peso = ", rowData.total_peso);
        console.log("rowData.volumen = ", rowData.volumen);
        console.log("capacidad camion = ", $("#camion_carga").text());
        console.log("capacidad volumen = ", $("#capacidad_volumen").text());
        console.log("porc_add camion = ", (parseFloat(rowData.total_peso)/((parseFloat($("#camion_carga").text()))?(parseFloat($("#camion_carga").text())):1))*100);
        console.log("porc_add volumen = ", (parseFloat(rowData.volumen)/((parseFloat($("#capacidad_volumen").text()))?(parseFloat($("#capacidad_volumen").text())):1))*100);

        var acum_peso = $("#capacidad_utilizada").text();
        var acum_volu = $("#volumen_ocupado").text();
        var porc_add_camion = (parseFloat(rowData.total_peso)/((parseFloat($("#camion_carga").text()))?(parseFloat($("#camion_carga").text())):1))*100;
        var porc_add_volumen = (parseFloat(rowData.volumen)/((parseFloat($("#capacidad_volumen").text()))?(parseFloat($("#capacidad_volumen").text())):1))*100;
        acum_peso = acum_peso.replace("%", "");
        acum_peso = acum_peso.replace(" ", "");
        if(acum_peso == "") 
           acum_peso = 0;

        acum_volu = acum_volu.replace("%", "");
        acum_volu = acum_volu.replace(" ", "");
        if(acum_volu == "") 
           acum_volu = 0;

       console.log("capacidad usada = ", acum_peso);
       console.log("capacidad usada volumen = ", acum_volu);
/*
        if((acum_peso+porc_add_camion) > 100)
        {
            swal("Peso Excedido", "El peso de este pedido sobrepasa la capacidad de peso del Vehículo", "error");
            return;
        }
        else if((acum_volu+porc_add_volumen) > 100)
        {
            swal("Volumen Excedido", "El volumen de este pedido sobrepasa la capacidad de Volumen del Vehículo", "error");
            return;
        }
        else if(((acum_peso+porc_add_camion) > 100) && ((acum_volu+porc_add_volumen) > 100))
        {
            swal("Peso y Volumen Excedidos", "El volumen y el peso de este pedido sobrepasan la capacidad en cuando a volumen y peso del Vehículo", "error");
            return;
        }
*/

        if (rowData.folio == 0 || isNaN(rowData.folio) || typeof rowData.folio === 'undefined') {

            seleccion = "";

        }

        else {

            seleccion = rowData.folio;

        }

        if (rowData.folio) {



            //if (seleccion!="") {


            sessionStorage.folios += "" + rowData.folio + ",";
            sessionStorage.sufijos += "" + rowData.sufijo + ",";
            //sessionStorage.stop += "" + rowData.rango_hora + ",";

            if (sessionStorage.folios.substring(0, 9) == 'undefined') {

                sessionStorage.folios = sessionStorage.folios.substring(9, sessionStorage.folios.length);
                sessionStorage.sufijos = sessionStorage.sufijos.substring(9, sessionStorage.sufijos.length);

            }



            // sessionStorage.folios += "'" + seleccion + "',";

            var folio_replace = rowData.folio;
            folio_replace = folio_replace.replace("/", "");
            //$("#" + rowData.folio).attr("id", folio_replace);
            //console.log("folio_replace = ", folio_replace);
            $("#" + folio_replace).hide();

        }

        else {



            return;

        }

        var data_folio = str.indexOf(rowData.folio);
        console.log("str = ", str);
        console.log("data_folio = ", data_folio);

        if(data_folio == -1)
        {
            str = sessionStorage.folios;
            sessionStorage.setItem(rowData.folio, JSON.stringify(rowData));

            //------------------------------------------------------------------------------------------------

            var res = sessionStorage.folios.substring(sessionStorage.folios.length - 1, sessionStorage.folios.length);

            if (res == ',') {



                // sessionStorage.folios = sessionStorage.folios.slice(0,-1);

            }

    //TODO Alberto agregar al listado derecho


            $(".products-list").append('<li class="item" id="elfolio' + rowData.folio + '">' +

                '<div class="product-img">' +

                '<button type="button" class="btn btn-danger btn-xs quitarfolio" folio="' + rowData.folio + '" onclick="deletefolio(\'' + rowData.folio + '\')"><i class="fa fa-times"></i></button>' +

                '</div>' +

                '<div class="product-info">' +

                '<a href="#" onclick="verDetalleDeCajas(\'' + rowData.folio + '\',\'' + rowData.sufijo + '\',\'' + rowData.cliente + '\',\'' + rowData.isla + '\',\'' + rowData.Direccion_Cliente + '\',\'' + rowData.total_peso + '\',\'' + rowData.volumen + '\',\'' + rowData.destinatario + '\',\'' + rowData.id_destinatario + '\',\'' + rowData.ruta + '\',\'' + rowData.total_cajas + '\',\'' + rowData.total_pallets + '\',\'' + rowData.puedo_entregar + '\')" class="product-title" style="' +

                '">' + rowData.folio+ '<span class="label label-warning pull-right" style="margin-right: 2px;">Volumen: ' + rowData.volumen + '</span></a><span class="label label-success pull-right">Peso: ' + rowData.total_peso + '</span>' +

                '<span class="product-description">Cliente: ' + rowData.cliente + '</span>' +

                '</div>' +

                '<div class="stop" style="display: inline-block; float: left; text-align: center; margin-left: 20px;">'+
                '<b>STOP</b>'+
                '<br>'+
                '<span>1</span>'+
                '</div>'+

                '<div class="rango_hora" style="display: inline-block; float: left; text-align: center; margin-left: 20px;">'+
                '<b>Horario Planeado</b>'+
                '<br>'+
                '<span>'+rowData.rango_hora+'</span>'+
                '</div>'+

                '</li>');

            console.log(sessionStorage.folios);

            $.ajax({

                type: "POST",

                dataType: "json",

                data: {

                    //folios :  folios,

                    folios: sessionStorage.folios,

                    action: 'totalesPesosGuias'

                },

                url: '/api/administracionembarque/lista/index_nikken.php',

                success: function (data) {

                    //TODO Alberto guardar en sessionStorage para que no se pierdan los datos al paginar



                    $("#txt_tot_guias").val(data.guias);

                    $("#txt_tot_peso").val(data.peso);

                    $("#txt_tot_volumen").val(data.volumen);

                    $("#txt_tot_piezas").val(data.piezas);

                },

            });

            sessionStorage.difpeso = 0;

            sessionStorage.difcapacidad = 0;

            sessionStorage.carga = 0;

            sessionStorage.capacidad = 0;

            $('#select_transporte').trigger('change');

            var stop_size = $(".stop").length;
            console.log("stop_size1 = ", stop_size);
            $(".stop").each(function(i){

                $("span", this).text((stop_size - i));

            });
/*
            $(".stop").each(function(i){

                $("span", this).text((i+1));

            });
*/
        }
        else
        {
            swal({

                title: "Pedido Existente",

                text: "El pedido ya fué agregado a la lista de embarque",

                type: "error"

            });
        }

//************************************************************************************************

        console.log("****************************************************************");
        console.log("****************************************************************");

        var pedidos = 0;
        var guias = rowData.total_guias;
        var destinatarios = rowData.id_destinatario;
        var pesos = rowData.total_peso;
        var volumen = rowData.volumen;
        var piezas = rowData.piezas;

        console.log("nro_de_pedidos = ", $("#nro_de_pedidos").val());
        console.log("nro_de_entregas = ", destinatarios);
        console.log("txt_tot_guias = ", guias);
        console.log("txt_tot_peso = ", pesos);
        console.log("txt_tot_volumen = ", volumen);
        console.log("txt_tot_piezas = ", piezas);

        if(guias == "") guias = 0;
        if(!destinatarios) destinatarios = 0; else destinatarios = 1;
        if(pesos == "") pesos = 0;
        if(volumen == "") volumen = 0;
        if(piezas == "") piezas = 0;

        pedidos = $("#nro_de_pedidos").val() -1;
        destinatarios = parseFloat($("#nro_de_entregas").val()) - parseFloat(destinatarios);
        guias = parseFloat($("#txt_tot_guias").val()) - parseFloat(guias);
        pesos = parseFloat($("#txt_tot_peso").val()) - parseFloat(pesos);
        volumen = parseFloat($("#txt_tot_volumen").val()) - parseFloat(volumen);
        piezas = parseFloat($("#txt_tot_piezas").val()) - parseFloat(piezas);

        if(destinatarios < 0 ) destinatarios = 0;
        if(guias < 0 ) guias = 0;
        if(pesos < 0 ) pesos = 0;
        if(volumen < 0 ) volumen = 0;
        if(piezas < 0 ) piezas = 0;

        console.log("--------------------------------------------------------------");
        console.log("nro_de_pedidos = ", pedidos);
        console.log("nro_de_entregas = ", destinatarios);
        console.log("txt_tot_guias = ", guias);
        console.log("txt_tot_peso = ", pesos);
        console.log("txt_tot_volumen = ", volumen);
        console.log("txt_tot_piezas = ", piezas);

        console.log("****************************************************************");
        console.log("****************************************************************");


        $("#nro_de_pedidos").val(pedidos);
        $("#nro_de_entregas").val(destinatarios);
        $("#txt_tot_guias").val(guias);
        $("#txt_tot_peso").val(pesos);
        $("#txt_tot_volumen").val(volumen);
        $("#txt_tot_piezas").val(piezas);

//************************************************************************************************


    }

    function deletefolio(elfolio) {

        //TODO eliminar del sessionStorage

        sessionStorage.cargadatablacajas = 0;

        if (sessionStorage.folios.substring(0, 9) == 'undefined') {

            sessionStorage.folios = sessionStorage.folios.substring(9, sessionStorage.folios.length);

            if (sessionStorage.folios.slice(-1) == ',') {

                sessionStorage.folios = sessionStorage.folios.substring(0, sessionStorage.folios.length - 1);

            }

        }

        else {

            if (sessionStorage.folios.slice(-1) == ',') {

                sessionStorage.folios = sessionStorage.folios.substring(0, sessionStorage.folios.length - 1);

            }

        }



        var arrayeliminar = sessionStorage.folios.split(',');

//TODO ELIMINAR DE SESSIONSTORAGE.FOLIOS

        //var values = list.split(separator);

        for (var i = 0; i < arrayeliminar.length; i++) {

            if (arrayeliminar[i] == "'" + elfolio + "'") {

                arrayeliminar.splice(i, 1);

                arrayeliminar.join(',');

            }

        }

        sessionStorage.folios = arrayeliminar;

        sessionStorage.removeItem(elfolio);

        $("#" + elfolio).show();

        $("#elfolio" + elfolio).remove();

        sessionStorage.difpeso = 0;

        sessionStorage.difcapacidad = 0;

        sessionStorage.carga = 0;

        sessionStorage.capacidad = 0;

        $('#select_transporte').trigger('change');

            var stop_size = $(".stop").length;
            console.log("stop_size = ", stop_size);
            $(".stop").each(function(i){

                $("span", this).text((stop_size - i));

            });

    }



    function verDetalleDeCajas(folio, sufijo, cliente, clave, guias, peso, volumen, clave_destinatario, destinatario, ruta, total_cajas, total_pallets, puedo_entregar) {

        infoTemporal.folio = folio;

        infoTemporal.sufijo = sufijo;

        infoTemporal.cliente = cliente;

        infoTemporal.clave = clave;

        infoTemporal.guias = guias;

        infoTemporal.peso = peso;

        infoTemporal.clave_destinatario = clave_destinatario;

        infoTemporal.destinatario = destinatario;

        infoTemporal.ruta = ruta;


        //caja_guias
        //caja_volumen
        //caja_peso
        $('#caja_volumen').text(volumen);
/*

        $.ajax({

            type: "POST",

            dataType: "json",

            url: '/api/administracionembarque/lista/index_nikken.php',

            data: {

                action: 'obtenerVolumenCajas',

                folio: folio,

            },

            success: function (data) {

                //$('#caja_volumen').text(data.volumen);

            },

        });
*/


        cargarGridDetallesCajas(folio, sufijo);


        $('#caja_folio').text(folio);

        $('#caja_cliente').text(cliente);

        $('#caja_clave').text(clave);

        $('#caja_guias').text(guias);

        $('#caja_peso').text(peso);

        $('#caja_clave_destinatario').text(clave_destinatario);

        $('#caja_destinatario').text(destinatario);

        $('#caja_ruta').text(ruta);

        $('#total_cajas').text(total_cajas);

        $('#total_pallets').text(total_pallets);

        $("#modal-detalles-cajas").modal('show');

    }



    function cargarGridDetallesCajas(folio, sufijo) {

        $('#grid-detalles-caja').jqGrid('clearGridData')

            .jqGrid('setGridParam', {

                postData: {

                    folio: folio,

                    sufijo: sufijo,

                    id_zona: $("#isla").val(),

                    action: 'cargarDetalleCajas'

                },

                datatype: 'json'

            })

            .trigger('reloadGrid', [{

                current: true

            }]);

    }



    function cargarGridDetallesPedidos(nro, guia, volumen) {

        $('#grid-detalles-pedidos').jqGrid('clearGridData')

            .jqGrid('setGridParam', {

                postData: {

                    folio: infoTemporal.folio,

                    partida: nro,

                    action: 'cargarDetallePedido'

                },

                datatype: 'json'

            })

            .trigger('reloadGrid', [{

                current: true

            }]);



        $('#pedido_nro').text(nro);

        $('#pedido_folio').text(infoTemporal.folio);

        $('#pedido_cliente').text(infoTemporal.cliente);

        $('#pedido_clave').text(infoTemporal.clave);

        $('#pedido_guias').text(guia);

        $('#pedido_volumen').text(volumen);

        $('#pedido_peso').text(infoTemporal.peso);

        $("#modal-detalles-pedido").modal('show');

    }



    $("#buscar").keyup(function (event) {

        if (event.keyCode == 13) {

            cargarDatosGridPrincipal()

        }

    });

$("#guia_transporte").keypress(function (event) {
    //console.log("guia_transporte", event);
  var regex = new RegExp("^[a-zA-Z0-9 ]+$");
  var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
  if (!regex.test(key)) {
    event.preventDefault();
    return false;
 }

});

    $("#boton_reportes").click(function(){


          var folios_r = [];
          var id_embarque = '';
          var id_ruta = '';
          var todos = 0;
          var cliente = '';
          var almacen = '';

          if(!$("#btn-asignarTodo").is(":checked"))
          {
              $("input[type=checkbox].checkbox-asignator").each(function(i, e){
                if($(this).prop("checked") == true)
                {
                  folios_r.push($(this).attr('value'));

                  //console.log("asignar() ->", $(this).attr('id'));
                  //console.log("asignar() ->", $(this).attr('value'));
                  //console.log("***********************************");
                }
              });
          }
          else
          {
            id_embarque = $("#isla").val();
            id_ruta     = $("#ruta").val();
            almacen     = $("#almacen").val();
            cliente     = $("#cliente").val();
            todos       = 1;
          }


          console.log("folios a asignar = ", folios_r);

          if (folios_r.length === 0 && todos == 0)
          {
             swal("Error", "No ha seleccionado ningún Folio", "error");
             return;
          }

          //$("#reportes_pdf_etiquetas").attr("href",'/api/koolreport/export/reportes/auditoria/etiquetas?folios='+folios_r);
            window.open('/api/koolreport/export/reportes/auditoria/etiquetas?folios='+folios_r+'&id_embarque='+id_embarque+'&id_ruta='+id_ruta+'&todos='+todos+'&cliente='+cliente+'&almacen='+almacen);


    });


    $("#embarcar-btn").click(function(){

        var folios = sessionStorage.folios;

        if (!folios && $("#folio_orden").val() == "") {

            swal({title: "Error", text: "Seleccionar al menos un pedido", type: "error"});

            return;
        }
/*
            swal({
                    title: "¿Que desea hacer con el embarque?",
                    text: "",
                    type: "success",

                    //showCancelButton: false,
                    //cancelButtonText: "Guardar y Continuar más Tarde",
                    //cancelButtonColor: "#55b9dd",

                    confirmButtonColor: "#55b9dd",
                    confirmButtonText: "Cerrar Embarque",
                    closeOnConfirm: false
                },
                function(e) {
                    if (e == true) {
*/
                        console.log("Cerrar Embarque");
                        EmbarcarPedidos($("#folio_orden").val(), 'T', '', '');

                        <?php 
                        if($ValorSAP == 1 && ($_SERVER['HTTP_HOST'] == 'dicoisa.assistprowms.com' || $_SERVER['HTTP_HOST'] != 'www.dicoisa.assistprowms.com'))
                        {
                        ?>
                        OVSap($("#folio_orden").val(), '');
                        <?php 
                        }
                        ?>
/*
                    } else {
                        console.log("Guardar Embarque");
                        EmbarcarPedidos($("#folio_orden").val(), 'E', '', '');
                    }
                });
            */

    });

    function OVSap(folio, sufijo){
        console.log("folio = ", folio);
        console.log("sufijo = ", sufijo);

        //return;
      $.ajax({
        type: "POST",
        dataType: "json",
        url: '/api/administradorpedidos/update/index.php',
        data: 
        {
          //action: 'ConectarSAP',
          //funcion: 'Login',
          action: 'EjecutarOVSap',
          funcion: 'DeliveryNotes',
          metodo: 'POST',
          folio: folio,
          sufijo: sufijo
        },
        beforeSend: function(x){if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
        },
        success: function(data) 
        {
            console.log("SUCCESS", data);
            return;
            if(data.SessionId)
            {
                console.log("SessionId OK:", data.SessionId);
                    //return;
                  $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: '/api/adminordentrabajo/update/index.php',
                    data: 
                    {
                      action: 'EjecutarOVSap',
                      funcion: 'DeliveryNotes',
                      //funcion: 'Drafts',
                      metodo: 'POST',
                      folio: folio,
                      sufijo: sufijo,
                      sesion_id: data.SessionId
                    },
                    beforeSend: function(x){if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                    },
                    success: function(data) 
                    {
                      console.log("SUCCESS EjecutarOVSap", data);
                      if(data.error)
                      {
                         swal("Error", data.error.message.value, "error");
                      }
                      else
                         swal("Envío Correcto", "Actualización a OT Correcta", "success");
                    }, error: function(data) 
                    {
                      console.log("ERROR EjecutarOVSap", data);
                    }
                  });
            }
            else
            {
                console.log("SessionId ERROR:", data);
                swal("Error", "No hay Conexión a SAP", "error");
            }


        }, error: function(data) 
        {
          console.log("ERROR", data);
        }
      });

    }

    function embarcarxpartes(id_embarque, tipo_embarque){

        console.log("OK embarcarxpartes ID = ", id_embarque, " Folio = ", $("#caja_folio").text());
        EmbarcarPedidos($("#caja_folio").text(), 'E', id_embarque, tipo_embarque);

    }

    function EmbarcarPedidos(folio_orden, status, id_embarque, tipo_embarque)
    {
        var myGrid = $('#grid-table'),

            i,

            rowData,

            rowIds = myGrid.jqGrid("getDataIDs"),

            n = rowIds.length;


        var folios = sessionStorage.folios;
        var sufijos = sessionStorage.sufijos;
        //console.log("folios = ", sessionStorage.folios);

        if (!folios && $("#folio_orden").val() == "" && id_embarque == "") {

            swal({title: "Error", text: "Seleccionar al menos un pedido", type: "error"});

            return;

        }
        var EntregaCliente = 0;
        if($("#btn-EntregarClientes").is(":checked"))
            EntregaCliente = 1;

        if ($("#select_transporte").val() == '' && EntregaCliente == 0) {

            swal({title: "Error", text: "Selecciona un transporte", type: "error"});

            return;

        }


            var origen_val = "";
            if($(".valor_origen").val() == 1)
                origen_val = $("#origen").val();



            console.log("***********************************");
            console.log("Embarcando...");
            console.log("EntregaCliente = ", EntregaCliente);
            console.log("folios = ", folios);
            console.log("sufijos = ", sufijos);
            console.log("folio_orden = ", folio_orden);
            console.log("folio_xpartes = ", $("#folio_xpartes").val());
            console.log("status = ", status);
            console.log("isla = ", $("#isla").val());
            console.log("ruta = ", $("#ruta_selected").val());
            console.log("n_control_pallets = ", $("#n_control_pallets").val());
            console.log("n_control_contenedores  = ", $("#n_control_contenedores" ).val());
            console.log("valor_origen = ", $(".valor_origen").val());
            console.log("guia_transporte = ", $("#guia_transporte").val());
            console.log("contacto_embarque = ", $("#contacto_embarque").val());
            console.log("chofer = ", $("#chofer").val());
            console.log("idchofer = ", $("#idchofer").val());
            console.log("n_unidad = ", $("#n_unidad").val());
            console.log("cve_transportadora = ", $("#cve_transportadora").val());
            console.log("placa = ", $("#placa").val());
            console.log("sello_precinto = ", $("#sello_precinto").val());
            console.log("seguro = ", $("#seguro").val());
            console.log("flete  = ", $("#flete" ).val());
            console.log("origen  = ", origen_val);
            console.log("transporte = ", $("#select_transporte").val());
            console.log("id_user = ", <?php echo $_SESSION['id_user']; ?>);
            console.log("almacen = ", $("#almacen").val());
            console.log("id_embarquexpartes = ", id_embarque);
            console.log("tipo_embarque = ", tipo_embarque);
            console.log("***********************************");

            //return;

            $.ajax({

                type: "POST",

                dataType: "json",

                url: '/api/administracionembarque/lista/index_nikken.php',

                data: {

                    action: 'embarcar',

                    EntregaCliente: EntregaCliente,

                    folio_orden: folio_orden,

                    folio_xpartes: $("#folio_xpartes").val(),

                    id_embarque: id_embarque,

                    tipo_embarque: tipo_embarque,

                    status: status,

                    guia_transporte: $("#guia_transporte").val(),

                    contacto_embarque: $("#contacto_embarque").val(),

                    chofer: $("#chofer").val(),

                    idchofer: $("#idchofer").val(),
                    n_unidad: $("#n_unidad").val(),
                    cve_transportadora: $("#cve_transportadora").val(),
                    placa: $("#placa").val(),
                    sello_precinto: $("#sello_precinto").val(),

                    seguro: $("#seguro").val(),

                    flete: $("#flete" ).val(),

                    ruta: $("#ruta_selected").val(),

                    n_control_pallets: $("#n_control_pallets" ).val(),

                    n_control_contenedores: $("#n_control_contenedores" ).val(),

                    origen: origen_val,

                    folios: folios,

                    sufijos: sufijos,

                    id_user: <?php echo $_SESSION['id_user']; ?>,

                    isla: $("#isla").val(),

                    transporte: $("#select_transporte").val(),

                    almacen: $("#almacen").val()

//             orden: infoTemporal.ordenEmbarque,

                },

                success: function (data) {

                    console.log("Finish = ", data);
                    if(data.folio_xpartes)
                    {
                        $("#folio_xpartes").val(data.folio_xpartes);
                        cargarGridDetallesCajas(data.folio_xpartes, 0);
                    }
                    else if (data.status == 200) 
                    {
                        cargarDatosGridPrincipal();

                        cargarIslas();
                        cargarRutas();

                        $("#modal-transporte").modal('hide');


        swal({

                title: "Completado",

                text: "Orden de embarque creada con éxito",

                type: "success",

                showCancelButton: false,

                cancelButtonText: "NO",

                cancelButtonColor: "#14960a",



                confirmButtonColor: "#55b9dd",

                confirmButtonText: "OK",

                closeOnConfirm: true

            },

            function (e) {
                location.reload();
            });

                        /*
                        swal({

                            title: "Completado",

                            text: "Orden de embarque creada con éxito",

                            type: "success"

                        });
                        location.reload();
                        */
                    }

                    if(data.embarque_folio_completo == 'S')
                    {
                        //$("#modal-detalles-cajas").modal('hide');
                        cargarDatosGridPrincipal();
                    }

                },

            });

    }

    function embarcar() {



        var myGrid = $('#grid-table'),

            i,

            rowData,

            folios = [],

            rowIds = myGrid.jqGrid("getDataIDs"),

            n = rowIds.length;

        for (i = 0; i < n; i++) {

            rowData = myGrid.jqGrid("getRowData", rowIds[i]);

            if (rowData.embarcar == 'Yes') {

                console.log("1234");

                folios.push(rowData.pedido);

            }

        }

        swal({

                title: "Embarcar",

                text: "¿Desea Utilizar Pallet | Contenedor?",

                type: "success",



                showCancelButton: true,

                cancelButtonText: "Si",

                cancelButtonColor: "#14960a",



                confirmButtonColor: "#55b9dd",

                confirmButtonText: "No",

                closeOnConfirm: true

            },

            function (e) {

                if (e == true) {

                    //NO

                    if (folios.length == 0) {

                        swal({title: "Error", text: "Seleccionar al menos un pedido", type: "error"});

                    }

                    else {

                        cargarIslas();
                        cargarRutas();

                        //$('#grid-table').jqGrid('clearGridData');

                        $("#modal-transporte").modal('show');

                        console.log("trasnporte");

                    }

                }

                else { //SI

                    console.log("fol1", folios);

                    $("#modal-contenedores").modal('show');

                    detalles_caja(folios);

                    console.log("contenedores");

                }

            });





    }



    function guardarTransporte() {

        var cod = document.getElementById("select_transporte").value;

        console.log("transporte", cod);



        if (cod == '') {

            swal({title: "Error", text: "Selecciona un transporte", type: "error"});

        }

        else {

            var myGrid = $('#grid-table'),

                i,

                rowData,

                folios = [],

                rowIds = myGrid.jqGrid("getDataIDs"),

                n = rowIds.length;

            console.log("isla", isla);

            for (i = 0; i < n; i++) {

                rowData = myGrid.jqGrid("getRowData", rowIds[i]);

                if (rowData.embarcar == 'Yes') {

                    folios.push(rowData.pedido);

                }

            }

            $.ajax({

                type: "POST",

                dataType: "json",

                url: '/api/administracionembarque/lista/index_nikken.php',

                data: {

                    action: 'embarcar',

                    folios: folios,

                    isla: $("#isla").val(),

                    almacen: $("#almacen").val(),

//             orden: infoTemporal.ordenEmbarque,

                },

                success: function (data) {

                    if (data.status == 200) {

                        $.ajax({

                            type: "POST",

                            dataType: "json",

                            url: '/api/administracionembarque/lista/index_nikken.php',

                            data: {

                                action: 'guardarTransporte',

                                transporte: $("#select_transporte").val(),

//                   orden: infoTemporal.ordenEmbarque,

                            },

                            success: function (data) {

                                if (data.status == 200) {

                                    //$('#grid-table').jqGrid('clearGridData');

                                    cargarDatosGridPrincipal();

                                    cargarIslas();
                                    cargarRutas();

                                    $("#modal-transporte").modal('hide');

                                    swal({

                                        title: "Completado",

                                        text: "Orden de embarque creada y transporte asignado con éxito",

                                        type: "success"

                                    });

                                    infoTemporal.ordenEmbarque = null

                                }

                            },

                        });

                    }

                },

            });

        }

    }



    function detalles_caja(folios) {

        console.log("foliocajas", folios);

        html = '';

        $.ajax({

            type: "POST",

            dataType: "json",

            url: "/api/administracionembarque/lista/index_nikken.php",

            data: {

                action: 'detalles',

                folio: folios

            },

            beforeSend: function (x) {

                if (x && x.overrideMimeType) {

                    x.overrideMimeType("application/json;charset=UTF-8");

                }

                $('#detalles > tbody').html('<tr><td colspan="8">Cargando datos...</td></tr>');

            },

            success: function (data) {



                console.log(data);

                $.each(data.rows, function (key, value) {

                    html += '<tr>' +

                        '<td align="center" class="acciones"><input type="checkbox"></td>' +

                        '<td align="right">' + value.cell[0] + '</td>' +

                        '<td>' + value.cell[1] + '</td>' +

                        '<td>' + value.cell[2] + '</td>' +

                        '<td>' + value.cell[3] + '</td>' +

                        '<td>' + value.cell[4] + '</td>' +

                        '<td>' + value.cell[5] + '</td>' +

                        '<td>' + value.cell[6] + '</td>' +

                        '</tr>';

                });

                $('#detalles > tbody').html(html);



            },

            error: function (res) {

                window.console.log(res);

            }

        });

        $('#modal_detalles').modal('show');

    }

    function muestra_contcajas() {

        $('#tablacajas').DataTable({

            "processing": true,

            "serverSide": false,

            "bDestroy": true,

            "deferRender": true,

            "ajax": {

                'type': 'POST',

                'url': '/api/ajax/listados.php',

                'data': {

                    formName: 'damecajas',

                    action: 'damecajas',

                    data: sessionStorage.folios



                }

            },

            "language": {



                "sProcessing": "Procesando...",

                "sLengthMenu": "Mostrar _MENU_ registros",

                "sZeroRecords": "No se encontraron resultados",

                "sEmptyTable": "Ningún dato disponible en esta tabla",

                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",

                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",

                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",

                "sInfoPostFix": "",

                "sSearch": "Buscar:",

                "sUrl": "",

                "sInfoThousands": ",",

                "sLoadingRecords": "Cargando...",

                "oPaginate": {

                    "sFirst": "Primero",

                    "sLast": "Último",

                    "sNext": "Siguiente",

                    "sPrevious": "Anterior"

                },

                "buttons": {

                    "copy": "Copiar",

                    "print": "Imprimir",

                    copyTitle: 'Copiar a la Clipboard',

                    copySuccess: {

                        _: 'Copiado %d filas',

                        1: 'Copiado 1 fila'

                    }

                },

                "oAria": {

                    "sSortAscending": ": Activar para ordenar la columna de manera ascendente",

                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"

                }



            },

            dom: 'Bfrtip',

            select: true,

            buttons: [

                {

                    extend: 'print',

                    exportOptions: {

                        columns: [0, 1, 2, 3, 4] //Your Colume value those you want

                    }

                },

                {

                    extend: 'excel',

                    exportOptions: {

                        columns: [0, 1, 2, 3, 4] //Your Colume value those you want

                    }

                },

            ],

            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],

            "initComplete": function (settings, json) {

                sessionStorage.cargadatablacajas = 1;

            },

        });

        if($(".dt-button.buttons-copy"))

            $(".dt-button.buttons-copy").attr('class','btn btn-default btn-sm bt');



        if($(".dt-button.buttons-csv"))

            $(".dt-button.buttons-csv").attr('class', 'btn btn-default btn-sm bt');

        if($(".dt-button.buttons-excel"))

            $(".dt-button.buttons-excel").attr('class', 'btn btn-warning btn-sm bt');

        if($(".dt-button.buttons-pdf"))

            $(".dt-button.buttons-pdf").attr('class', 'btn btn-primary btn-sm bt');

        if($(".dt-button.buttons-print"))

            $(".dt-button.buttons-print").attr('class', 'btn btn-default btn-sm bt');



        /* $('#tablacajas').DataTable( {

         "ajax": "/api/ajax/listados.php?damecajas=1",

         "deferRender": true,

         "retrieve": true,

         "processing": true,

         "language": {



         "sProcessing":     "Procesando...",

         "sLengthMenu":     "Mostrar _MENU_ registros",

         "sZeroRecords":    "No se encontraron resultados",

         "sEmptyTable":     "Ningún dato disponible en esta tabla",

         "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",

         "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0",

         "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",

         "sInfoPostFix":    "",

         "sSearch":         "Buscar:",

         "sUrl":            "",

         "sInfoThousands":  ",",

         "sLoadingRecords": "Cargando...",

         "oPaginate": {

         "sFirst":    "Primero",

         "sLast":     "Último",

         "sNext":     "Siguiente",

         "sPrevious": "Anterior"

         },

         "oAria": {

         "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",

         "sSortDescending": ": Activar para ordenar la columna de manera descendente"

         }



         },"initComplete": function (settings, json) {

         sessionStorage.cargadatablacajas=1;

         }



         } );*/

    }


  ///////////////////////////////////////////////SUBIR FOTOS TH///////////////////////////////////////////////
    $('#btn-fotos-th').on('click', function() {

        $('#btn-fotos-th').prop('disable', true);
        var bar = $('.progress-bar');
        var percent = $('.percent');
        //var status = $('#status');
        var formData = new FormData();
        formData.append("clave", "valor");

        if(!$("#descripcion_foto").val() || !$("#file_th").val())
        {
            swal("Advertencia!", "Todos los datos son requeridos", "warning");
            return false;
        }

        $.ajax({
            // Your server script to process the upload
            url: '/embarques/importarfotosth',
            type: 'POST',

            // Form data
            data: new FormData($('#form-subir-fotos-th')[0]),

            // Tell jQuery not to process data or worry about content-type
            // You *must* include these options!
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $('.progress').show();
                var percentVal = '0%';
                bar.width(percentVal);
                percent.html(percentVal);
            },
            // Custom XMLHttpRequest
            xhr: function() {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    // For handling the progress of the upload
                    myXhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            var percentComplete = e.loaded / e.total;
                            percentComplete = parseInt(percentComplete * 100);
                            bar.css("width", percentComplete + "%");
                            percent.html(percentComplete+'%');
                            if (percentComplete === 100) {
                                setTimeout(function(){$('.progress').hide();}, 2000);
                            }
                        }
                    } , false);
                }
                return myXhr;
            },
            success: function(data) {
                console.log('sucess', data);
                setTimeout(
                    function(){if (data.status == 200 && data.statusType == 1) {
                        swal("Exito", data.statusText, "success");
                        //$('#fotos_oc_th').modal('hide');
                        //ReloadGrid();
                        $("#file_th").val("");
                        $("#descripcion_foto").val("");
                        ImprimirFotos_th($("#folio_foto_th").val());
                    }
                    else {
                        $('#fotos_oc_th').modal('hide');
                        swal("Error", data.statusText, "error");
                    }
                },1000)
            },error: function(data) {
                console.log('error', data);
            }
        });
    });



    function ImprimirFotos_th(folio)
    {
        console.log("ImprimirFotos_th", folio);
          $.ajax({
              type: "POST",
              //dataType: "json",
              data: 
              {
                  folio: folio,
                  action : "cargarFotosTH"
              },/*
              beforeSend: function(x)
              {
                  if(x && x.overrideMimeType) 
                  { 
                    x.overrideMimeType("application/json;charset=UTF-8"); 
                  }
              },*/
              url: '/api/administracionembarque/lista/index_nikken.php',
              success: function(data) 
              {

                $("#fotos_th").html(data);

              }, error: function(data) 
              {
                  console.log("ERROR Fotos Datos = ", data);
              }
          });
    }

    function eliminar_foto_th(id)
    {
        console.log("eliminar_foto_th", id);
          $.ajax({
              type: "POST",
              //dataType: "json",
              data: 
              {
                  id: id,
                  action : "eliminarFotosTH"
              },/*
              beforeSend: function(x)
              {
                  if(x && x.overrideMimeType) 
                  { 
                    x.overrideMimeType("application/json;charset=UTF-8"); 
                  }
              },*/
              url: '/api/administracionembarque/lista/index_nikken.php',
              success: function(data) 
              {

                ImprimirFotos_th($("#folio_foto_th").val());

              }, error: function(data) 
              {
                  console.log("ERROR Fotos Datos = ", data);
              }
          });
    }

    var infoTemporal = {};



    function calculateAndDisplayRoute(directionsService, directionsRenderer) {

        var waypts = [];

        var checkboxArray = document.getElementById('waypoints');

        for (var i = 0; i < checkboxArray.length; i++) {

            if (checkboxArray.options[i].selected) {

                waypts.push({

                    location: checkboxArray[i].value,

                    stopover: true

                });

            }

        }



        directionsService.route({

            origin: document.getElementById('start').value,

            destination: document.getElementById('end').value,

            waypoints: waypts,

            optimizeWaypoints: true,

            travelMode: 'DRIVING'

        }, function(response, status) {

            if (status === 'OK') {

                directionsRenderer.setDirections(response);

                var route = response.routes[0];

                var summaryPanel = document.getElementById('directions-panel');

                summaryPanel.innerHTML = '';

                // For each route, display summary information.

                for (var i = 0; i < route.legs.length; i++) {

                    var routeSegment = i + 1;

                    summaryPanel.innerHTML += '<b>Route Segment: ' + routeSegment +

                        '</b><br>';

                    summaryPanel.innerHTML += route.legs[i].start_address + ' to ';

                    summaryPanel.innerHTML += route.legs[i].end_address + '<br>';

                    summaryPanel.innerHTML += route.legs[i].distance.text + '<br><br>';

                }

            } else {

                window.alert('Directions request failed due to ' + status);

            }

        });

    }

</script>

<script>

    $(document).ready(function (e) {
/*
        function initMap() {

            var directionsService = new google.maps.DirectionsService;

            var directionsRenderer = new google.maps.DirectionsRenderer;

            var map = new google.maps.Map(document.getElementById('mapa'), {

                zoom: 6,

                center: {lat: 41.85, lng: -87.65}

            });

            directionsRenderer.setMap(map);



            document.getElementById('submit').addEventListener('click', function() {

                calculateAndDisplayRoute(directionsService, directionsRenderer);

            });

        }
*/
        sessionStorage.cargadatablacajas = 0;

        $(".form_img").on('submit', function (e) {

            e.preventDefault();

            console.log("enviando archivo")

            $.ajax({

                type: 'POST',

                url: '/api/administradorpedidos/lista/index.php',

                data: new FormData(this),

                dataType: "json",

                contentType: false,

                cache: false,

                processData: false,

                success: function (msg) {

                    console.log(msg, "#foto_to_up_" + msg.numeroImagen);

                    $("#foto_to_up_" + msg.numeroImagen).val(msg.nameFile);

                    $("#foto" + msg.numeroImagen).attr("src", "../to_img.php?img=embarques/" + msg.nameFile);

                }

            });

        });


        $("#btn-asignarTodo").on("click", function(){
          $("input[type=checkbox].checkbox-asignator").each(function(i, e){
            if($("#btn-asignarTodo").prop("checked") == false)
            {
              $("input[type=checkbox].checkbox-asignator").prop("checked", true);
              //
              if($("input[type=checkbox].checkbox-asignator").prop("checked") == true){$("input[type=checkbox].checkbox-asignator").prop("checked", false);}
              else{$("input[type=checkbox].checkbox-asignator").prop("checked", true);}
            }
            else
            {
              $("input[type=checkbox].checkbox-asignator").prop("checked", false);
              //
              if($("input[type=checkbox].checkbox-asignator").prop("checked") == true){$("input[type=checkbox].checkbox-asignator").prop("checked", false);}
              else{$("input[type=checkbox].checkbox-asignator").prop("checked", true);}
            }
          });
        });

        //file type validation

        $(".file_img").change(function () {

            var file = this.files[0];

            var imagefile = file.type;

            var match = ["image/jpeg", "image/png", "image/jpg"];

            if (!((imagefile == match[0]) || (imagefile == match[1]) || (imagefile == match[2]))) {

                alert('Por favor seleccione una imagen del formato (JPEG/JPG/PNG).');

                $(this).val('');

                return false;

            }

            else {

                console.log(this.form);

                $(this.form).submit()

            }

        });


        $("#ruta").change(function(){

            $("#ruta_selected").val($(this).val());
            cargarTransportes();
            $("#select_transporte").change();
                setTimeout(function(){
            if($("#select_transporte").val() != '')
            {
                $("#capacidad_utilizada").text($("#select_transporte").find(':selected').data('peso_pendiente')+" %");
                $("#volumen_ocupado").text($("#select_transporte").find(':selected').data('volumen_pendiente')+" %");
            }
                $("#ruta").val($("#ruta_selected").val());
                $("#isla").change();
                }, 1000);
                console.log("RUTAS CHANGE");
                //$("#ruta").val($("#ruta_selected").val()).trigger("chosen:updated");
                $('.chosen-select').chosen().trigger("chosen:updated");
                //console.log("RUTA SELECCIONADA = ", $("#ruta_selected").val(), " | RUTA = ", $("#ruta").val());
        });

        var transporte_selected = "";
        $("#select_transporte").change(function () {
/*

//        setTimeout(
//    function()
//            {
            console.log("xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");
            var cap = 0, vol = 0;
            cap = (parseFloat(($(this).find(':selected').data('peso_pendiente')/parseFloat($("#select_transporte").find(':selected').data('carga')))*100)).toFixed(2);
            vol = (parseFloat(($(this).find(':selected').data('volumen_pendiente')/parseFloat($("#capacidad_volumen").text()))*100)).toFixed(2);

//            while(!isNaN(cap) && !isNaN(vol))
//            {
//                cap = (parseFloat(($(this).find(':selected').data('peso_pendiente')/parseFloat($("#select_transporte").find(':selected').data('carga')))*100)).toFixed(2);
//                vol = (parseFloat(($(this).find(':selected').data('volumen_pendiente')/parseFloat($("#capacidad_volumen").text()))*100)).toFixed(2);
//            }

            console.log("capacidad_utilizada = ", cap+" %");
            console.log("volumen_ocupado = ", vol+" %");
        setTimeout(
    function()
            {        
        $("#capacidad_utilizada").text(cap+" %");//+parseFloat(acum_peso)
        $("#volumen_ocupado").text(vol+" %");
        console.log("xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");
            //console.log("okTimeOut");
    }
        , 2000);
*/
/*
        var cap = "", vol = "", iteracion = 0;

        while(cap == "" && vol == "")
        {
            $("#capacidad_utilizada").text((parseFloat(($(this).find(':selected').data('peso_pendiente')/parseFloat($("#select_transporte").find(':selected').data('carga')))*100)).toFixed(2)+" %");//+parseFloat(acum_peso)
            $("#volumen_ocupado").text((parseFloat(($(this).find(':selected').data('volumen_pendiente')/parseFloat($("#capacidad_volumen").text()))*100)).toFixed(2)+" %");

            cap = $("#capacidad_utilizada").text();
            vol = $("#volumen_ocupado").text();

            if(isNaN(cap) || isNaN(vol) || (vol > 0 && cap == 0) || (vol == 0 && cap > 0))
            {
                cap = ""; vol = "";
            }
            else
            {
                $("#capacidad_utilizada").text($("#capacidad_utilizada").text()+" %");
                $("#volumen_ocupado").text($("#volumen_ocupado").text()+" %");
                cap = "OK"; vol = "OK";
                break;
            }


            console.log("i = ", iteracion, " cap = ", cap, " vol = ", vol);
            iteracion++;
        }
*/



                $("#ruta").val($("#ruta_selected").val()).trigger("chosen:updated");
                $('.chosen-select').chosen().trigger("chosen:updated");
                //console.log("RUTA SELECCIONADA = ", $("#ruta_selected").val(), " | RUTA = ", $("#ruta").val());

            sessionStorage.difpeso = 0;

            sessionStorage.difcapacidad = 0;

            sessionStorage.carga = 0;

            sessionStorage.capacidad = 0;

            //$("#folio_orden").val($(this).find(':selected').data('orden'));
            //console.log("folio_orden_transporte = ", $("#folio_orden").val());

            //if($("#folio_orden").val() != '' && transporte_selected != $(this).val())
            //{
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: '/api/administracionembarque/lista/index_nikken.php',
                    data: {
                        action: 'DatosTransporte',
                        folio_orden: $("#folio_orden").val()
                    },
                    success: function (data) {
                        console.log(data);
                        console.log("OK");
                        var carga_total = parseFloat($("#select_transporte").find(':selected').data('carga'));
                        console.log("carga_total = ",carga_total);

if($("#select_transporte").val() != '')
{
$("#camion_carga").text($("#select_transporte").find(':selected').data('carga'));// / 1000
$("#camion_alto").text($("#select_transporte").find(':selected').data('alto') / 1000);
$("#camion_fondo").text($("#select_transporte").find(':selected').data('fondo') / 1000);
$("#camion_ancho").text($("#select_transporte").find(':selected').data('ancho') / 1000);
}

                if($("#select_transporte").val() != '')
                {
                    $("#capacidad_volumen").text(parseFloat($("#select_transporte").find(':selected').data('alto') / 1000) * parseFloat( $("#select_transporte").find(':selected').data('ancho') / 1000) * parseFloat($("#select_transporte").find(':selected').data('fondo') / 1000));
                }
                        //$("#capacidad_utilizada").text(((parseFloat(data.peso)/parseFloat(carga_total))*100).toFixed(2)+" %");

                        var capacidad = parseFloat($("#select_transporte").find(':selected').data('alto') / 1000) * parseFloat( $("#select_transporte").find(':selected').data('ancho') / 1000) * parseFloat($("#select_transporte").find(':selected').data('fondo') / 1000);
                        /*
                        $("#volumen_ocupado").text((((parseFloat(data.volumen)/parseFloat(capacidad))*100).toFixed(2))+" %");
                        console.log("volumen_ocupado1 = ", $("#volumen_ocupado").text());
                        */
                        console.log("OK0");

/*
<a href="#" onclick="verDetalleDeCajas(\'' + rowData.folio + '\',\'' + rowData.sufijo + '\',\'' + rowData.cliente + '\',\'' + rowData.isla + '\',\'' + rowData.Direccion_Cliente + '\',\'' + rowData.total_peso + '\',\'' + rowData.volumen + '\',\'' + rowData.destinatario + '\',\'' + rowData.id_destinatario + '\',\'' + rowData.ruta + '\')" class="product-title" style="' +
    '">' + rowData.folio+'-'+rowData.sufijo+ '</a>
*/
var folio = '';
var volumen = '';
var total_peso = '';
var cliente = '';
var rango_hora = '';

cargarRutas();

console.log("OK1");

var folios_storage_v = data.folios_storage.split(",");
var clientes_storage_v = data.clientes_storage.split(",");
var rango_storage_v = data.rango_storage.split(",");
var volumen_v = data.peso_storage.split(",");
var peso_v = data.volumen_storage.split(",");

console.log("OK2");

/*
for(var i = 0; i < folios_storage_v.length - 1; i++)
{
    folio = folios_storage_v[i];
    cliente = clientes_storage_v[i];
    rango_hora = rango_storage_v[i];
    volumen = volumen_v[i];
    total_peso = peso_v[i];

    $(".products-list-under").append('<li class="item" id="elfolio' + folio + '">' +

        '<div class="product-img">' +

        '<button type="button" class="btn btn-danger btn-xs quitarfolio hide" folio="' + folio + '" onclick="deletefolio(\'' + folio + '\')"><i class="fa fa-times"></i></button>' +

        '</div>' +

        '<div class="product-info">' +

        '<div class="product-title">'+folio+'</div>'+
        '<span class="label label-warning pull-right" style="margin-right: 2px;">Volumen: ' + volumen + '</span> <span class="label label-success pull-right">Peso: ' + total_peso + '</span>' +

        '<span class="product-description">Cliente: ' + cliente + '</span>' +

        '</div>' +

        '<div class="stop" style="display: inline-block; float: left; text-align: center; margin-left: 20px;">'+
        '<b>STOP</b>'+
        '<br>'+
        '<span>1</span>'+
        '</div>'+

        '<div class="rango_hora" style="display: inline-block; float: left; text-align: center; margin-left: 20px;">'+
        '<b>Horario Planeado1</b>'+
        '<br>'+
        '<span>'+rango_hora+'</span>'+
        '</div>'+

        '</li>');
}
*/
                    var stop_size = $(".stop").length;
                    console.log("stop_size = ", stop_size);
                    $(".stop").each(function(i){

                        $("span", this).text((stop_size - i));

                    });


                    }
                });
            //}


            if(transporte_selected != $(this).val())
            {
                //$("#capacidad_utilizada").text('0');
                $(".products-list-under").empty();
                //$("#capacidad_utilizada").text("");
                //$("#volumen_ocupado").text("");
                //$("#capacidad_utilizada").text($("#select_transporte").find(':selected').data('peso_pendiente')+" %");
                //$("#volumen_ocupado").text($("#select_transporte").find(':selected').data('volumen_pendiente')+" %");
            }

            console.log(">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>");
            console.log("capacidad_utilizada = ", $("#capacidad_utilizada").text() , "volumen_ocupado = ", $("#volumen_ocupado").text());
/*
        if(!$("#capacidad_utilizada").text() && !$("#volumen_ocupado").text())
        {
            $("#capacidad_utilizada").text($("#select_transporte").find(':selected').data('peso_pendiente')+" %");
            $("#volumen_ocupado").text($("#select_transporte").find(':selected').data('volumen_pendiente')+" %");
            console.log("Cargó Capacidad y Volumen");
        }
*/
            if ($(this).val() != '') {

                if(transporte_selected == $(this).val())
                {

                    $("#camion_carga").children('i').text('\xa0' + $(this).find(':selected').data('carga'));// / 1000
                    $("#camion_alto").children('i').text('\xa0' + $(this).find(':selected').data('alto') / 1000);
                    $("#camion_fondo").children('i').text('\xa0' + $(this).find(':selected').data('fondo') / 1000);
                    $("#camion_ancho").children('i').text('\xa0' + $(this).find(':selected').data('ancho') / 1000);
                    
                    sessionStorage.carga = $(this).find(':selected').data('carga') ;/// 1000;

                    sessionStorage.capacidad = ($(this).find(':selected').data('alto') / 1000) * ( $(this).find(':selected').data('ancho') / 1000) * ($(this).find(':selected').data('fondo') / 1000);

                    //calcular la diferencia con la suma de los sessionStorage.folios cargas y tamannos y colocarlo en diferencia

                    //$("#camion_carga").val($(this).find(':selected').data('carga'));
                    sessionStorage.difpesoc = (parseFloat(sessionStorage.carga)) - (Math.round(sessionStorage.difpesoc));//*1000
                    $("#capacidad_volumen").text(sessionStorage.capacidad);
                    console.log("sessionStorage.capacidad == ", sessionStorage.capacidad);
                }
                if(sessionStorage.folios)
                {
                    calculatepeso();

                    var carga_total = parseFloat($(this).find(':selected').data('carga'));

                    var volumen_total = parseFloat($("#capacidad_volumen").text());

                    var difpeso = (Math.round(sessionStorage.difpesoc)).toFixed(2);

                    //var difcap = volumen_total - (Math.round(sessionStorage.difcapacidadc * 100) / 100).toFixed(2);
                    var difcap = (Math.round(sessionStorage.difcapacidadc)).toFixed(2);

                    console.log("VVVVVVVVVVVVVVVVVVVVVVV");
                    console.log("difcap = ", difcap, " volumen_total = ", volumen_total);
                    $("#camion_difpeso").children('i').text(difpeso);

                    $("#camion_difcap").children('i').text(difcap);

                    

                    //console.log("difcap = ", difcap);
                    //console.log("volumen_total = ", volumen_total);

                    console.log("volumen_ocupado2 = ", $("#volumen_ocupado").text());
                    var acum_peso = $("#capacidad_utilizada").text();
                    var acum_volu = $("#volumen_ocupado").text();
                    console.log("volumen_ocupado3 = ", $("#volumen_ocupado").text());
                    console.log("acum_peso = ", acum_peso);
                    console.log("difpeso = ", difpeso);
                    console.log("difpeso2 = ", (Math.round(sessionStorage.difpesoc)));
                    console.log("carga_total = ", carga_total);
                    console.log("acum_volu = ", acum_volu);

                    if(acum_peso == "") 
                       acum_peso = 0;
                    else
                    {
                        acum_peso = acum_peso.replace("%", "");
                        acum_peso = acum_peso.replace(" ", "");
                    }


                    acum_volu = acum_volu.replace("%", "");
                    acum_volu = acum_volu.replace(" ", "");

                    if(acum_volu == "") 
                       acum_volu = 0;
                    console.log("acum_volu2 = ", acum_volu);

                    console.log((parseFloat((difpeso/carga_total)*100)).toFixed(2), "+", acum_peso);
                    $("#capacidad_utilizada").text((parseFloat((difpeso/carga_total)*100)+parseFloat(acum_peso)).toFixed(2)+" %");//+parseFloat(acum_peso)
                    $("#volumen_ocupado").text((parseFloat((difcap/volumen_total)*100)+parseFloat(acum_volu)).toFixed(2)+" %");

                    console.log("*********************************************");
                    console.log("difcap = ", difcap);
                    console.log("volumen_total = ", volumen_total);
                    console.log("acum_volu = ", acum_volu);
                    console.log("volumen_ocupado4 = ", $("#volumen_ocupado").text());
                    console.log("*********************************************");

                }

                transporte_selected = $(this).val();
            }

            else {

                $("#camion_carga").children('i').text('\xa0');

                $("#camion_alto").children('i').text('\xa0');

                $("#camion_fondo").children('i').text('\xa0');

                $("#camion_ancho").children('i').text('\xa0');

                $("#camion_difpeso").children('i').text('\xa0');

                $("#camion_difcap").children('i').text('\xa0');

                $("#camion_difcap").pulsate({color: "#bf1c56", repeat: !1});

                $("#camion_difpeso").pulsate({color: "#bf1c56", repeat: !1});

            }


        });

    });

    function calculatepeso() {

        //Foreach sessionstorage pesos

        //console.log("sessionStorage.folios_calculatepeso = ", sessionStorage.folios);
        var arrayfolios = sessionStorage.folios.split(',');

//    STUFFF= JSON.parse(sessionStorage.getItem('F10121774'));

//    STUFFF.Direccion_Cliente;

        var pesototal = 0;

        var capacidadtotal = 0;

        for (var i = 0; i < arrayfolios.length-1; i++) {

            //Eliminar un caracter al inicio y uno al final

            STUFFF = JSON.parse(sessionStorage.getItem(arrayfolios[i]));//.slice(1, -1)

            //console.log("arrayfolios["+i+"] = ", arrayfolios[i]);
            //console.log("STUFFF = ", STUFFF);
            //console.log("arrayfolios[i].slice(1, -1) = ", arrayfolios[i].slice(1, -1));

            if (STUFFF != null) {



                pesototal += parseFloat(STUFFF.total_peso);

                capacidadtotal += parseFloat(STUFFF.volumen);

            }

            //console.log("pesototal = ", pesototal);
            //console.log("capacidadtotal = ", capacidadtotal);

        }


/*
        if (pesototal >= parseFloat(sessionStorage.carga)) {

            $("#camion_difpeso").pulsate({color: "#bf1c56"});

        }

        else {

            $("#camion_difpeso").pulsate({color: "#bf1c56", repeat: 1});

        }

        if (capacidadtotal >= parseFloat(sessionStorage.capacidad)) {

            $("#camion_difcap").pulsate({color: "#bf1c56"});

        }

        else {

            $("#camion_difcap").pulsate({color: "#bf1c56", repeat: !1});

        }
*/
        //sessionStorage.difpesoc = (parseFloat(sessionStorage.carga)) - pesototal;//*1000
        sessionStorage.difpesoc = pesototal;

        //sessionStorage.difcapacidadc = ((parseFloat(sessionStorage.capacidad)) -  capacidadtotal); // *1000 /1000
        sessionStorage.difcapacidadc = capacidadtotal;

        console.log("pesototal = ", pesototal);
        console.log("capacidadtotal = ", capacidadtotal);
        console.log("sessionStorage.carga = ", sessionStorage.carga);
        console.log("sessionStorage.difpesoc = ", sessionStorage.difpesoc);
        console.log("sessionStorage.difcapacidadc = ", sessionStorage.difcapacidadc);


    }

    function ver_fotos(_codigo)
    {
        $("#folio_foto_th").val(_codigo);
        $("#n_folio").text(_codigo);
        $modal0 = $("#fotos_oc_th");
        $modal0.modal('show');
        ImprimirFotos_th(_codigo);
    }

    function distanciapuntos(longitud1,latitud1,longitud2,latitud2){



        var distance = google.maps.geometry.spherical.computeDistanceBetween(new google.maps.LatLng(latitud1, longitud1), new google.maps.LatLng(latitud2, longitud2));

        //en metros

        return distance;

    }

</script>

<style>

    @media (min-width: 768px) {

        .modal-dialog {

            width: 700px !important;

        }

    }



    @media (max-width: 450px) {

        .modal-dialog {

            width: 400px !important;

        }

    }



    .fotos {

        width: 200px;

    }

</style>