<?php

$listaProvee = new \Proveedores\Proveedores();
$listaAlma = new \AlmacenP\AlmacenP();
$listaProto = new \Protocolos\Protocolos();
$listaRecursos = new \TipoDeRecursos\TipoDeRecursos();
$listaPresupuestos = new \Presupuestos\Presupuestos();
$listaProcedimientos = new \TipoDeProcedimientos\TipoDeProcedimientos();
$listaOC = new \OrdenCompra\OrdenCompra();
$listaUser = new \Usuarios\Usuarios();
$tools = new \Tools\Tools();
$listaPresupuestosNEW  = new \AdminEntrada\AdminEntrada();
$listaClientes = new \Clientes\Clientes();
$listaOC->updateFiles();

$permisos = $tools->permisos(46,array(
  "ver"=>288,
  "agregar"=>289,
  "editar"=>290,
  "borrar"=>291,
));

?>
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">

<style>
    #list {
        width: 100%;
        height: 100%;
        position: absolute;
        left: 0px;
        z-index: 999;
    }

    #FORM {
        width: 100%;
        /*height: 100%;*/
        position: absolute;
        left: 0px;
        z-index: 999;
    }
    .ui-jqgrid, .ui-jqgrid-view, .ui-jqgrid-hdiv, .ui-jqgrid-bdiv, #grid-pager_articulos{
        width: 100%;
        max-width: 100%;
    }
    .navtable 
    {
        display: none;
    }
</style>

<div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <i class="fa fa-laptop modal-icon"></i>
                <h4 class="modal-title">Editar Producto</h4>
            </div>
            <div class="modal-body">
                <input id="row_prod" type="hidden" value="" disabled class="form-control">
                <div class="form-group"><label>Clave del Producto</label> <input id="clave_prod" type="text" value="" disabled class="form-control"></div>
                <div class="form-group"><label>Nombre del Producto</label> <input id="nombre_prod" type="text" disabled value="" class="form-control"></div>
                <div class="form-group"><label>Cantidad</label> <input id="cant_prod" type="text" value="" class="form-control" maxlength="8"></div>
                <div class="form-group"><label>Peso</label> <input id="peso_prod_total" type="number" value="" disabled class="form-control"></div>
                <div class="form-group"><label>Precio Unitario</label> <input id="precio_unitario" type="text" value="" class="form-control"></div>
                <div class="form-group"><label>Importe Total</label> <input id="importe_total" type="number" value="" disabled class="form-control"></div>
                <input id="peso_prod"  type="hidden" disabled value="" class="form-control">
                <input type="hidden" id="hiddenAction">
                <input type="hidden" id="hiddencve_tipcia">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal" id="btnCancel">Cerrar</button>
                <button type="button" class="btn btn-primary ladda-button3" data-style="contract" id="btnSave">Guardar</button>
            </div>
        </div>
    </div>
</div>

<div class="wrapper wrapper-content  animated " id="list">
    <h3>Administración de Listas de Descuentos</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Seleccione un almacen</label>
                            <select class="form-control" id="almacen" >
                                <option value="">Almacen</option>
                                <?php foreach( $listaAlma->getAll() AS $a ): ?>
                                <option value="<?php echo $a->clave; ?>"><?php echo "($a->clave) $a->nombre"; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-4" style="display: none;">
                            <label>Fecha Inicio</label>
                            <div class="input-group date"  id="data_3">
                                <input id="fechai" type="text" class="form-control" style="text-align: center;" ><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            </div>
                        </div>
                        <div class="form-group col-md-4" style="display: none;">
                            <label>Fecha Fin</label>
                            <div class="input-group date"  id="data_4">
                                <input id="fechaf" type="text" class="form-control" style="text-align: center;" ><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="row" class="col-md-12">
                        <div class="form-group col-md-4" style="display: none;">
                            <label>Buscar por</label>
                            <input id="txtCriterio" type="text" placeholder="" class="form-control">
                        </div>
                        <div class="form-group col-md-4" style="display: none;">
                            <label for="email">En</label>
                            <select name="filtro" id="filtro" class="chosen-select form-control">
                                <option value="">Seleccione filtro</option>
                                <option value="th_aduana.num_pedimento">Folio</option>
                                <option value="c_proveedores.Nombre">Proveedor</option>
                                <option value="th_aduana.status">Status</option>
                            </select>
                        </div>
                        <div class="form-group col-lg-4" style="top: 25px; left: -30px;">
                            <div class="col-lg-12">
                                <a href="#" onclick="ReloadGrid()">
                                    <button type="submit" class="btn btn-sm btn-primary" id="buscarA">Buscar</button>
                                </a>
                            <?php //if($permisos["agregar"]==1){?>
                              <button onclick="agregar()" class="btn btn-sm btn-primary permiso_registrar" type="button">
                                <i class="fa fa-plus"></i> Nuevo
                              </button>
                            <?php //}?>
                        <?php 
                        /*
                        ?>
                        <button id="importExcelOC" type="button"  class="btn btn-primary">
                            <i class="fa fa-file-excel-o"></i>
                            Importar OC
                        </button>
                        <?php 
                        */
                        ?>

                            </div>
                            <style>
                                <?php /* if($permisos["editar"]==0){?>
                                  .fa-edit{display: none;}
                                <?php }?>
                                <?php if($permisos["borrar"]==0){?>
                                .fa-eraser{display: none;}
                                <?php } */ ?>
                            </style>
                          
                    <div style="width: 100%; text-align: center; margin-bottom: 50px;">
                    </div>
                        </div>
                      <?php /* ?>
                      <div class="row col-md-12">
                          <div class="form-group col-md-6">
                              <label for="email">Presupuesto</label>
                              <div class="input-group">
                                  <select name="presupuestoSelect" id="presupuestoSelect" class="chosen-select form-control">
                                      <option value="">Seleccione Presupuesto</option>
                                      <?php foreach( $listaPresupuestosNEW->getPresupuestos() AS $p ): ?>
                                      <option value="<?php echo $p['id']; ?>"><?php echo $p['claveDePartida'].' - '.$p['nombreDePresupuesto']; ?></option>
                                      <?php endforeach; ?>
                                  </select>
                                  <div class="input-group-addon" style="padding: 0px;">
                                      <button onclick="calcularPresupuesto()" type="submit" class="btn btn-primary" id="buscarA">
                                          <i class="fa fa-bar-chart"></i> Calcular
                                      </button>
                                  </div>
                              </div>
                          </div>
                          <div class="form-group col-md-2">
                              <label for="email">Presupuesto Asignado</label>
                              <div class="input-group date"  id="data_3">
                                  <input id="valorDePresupuesto" type="text" class="form-control" placeholder="$ 0.00" style= "background-color: white" readonly>
                              </div>
                          </div>
                          <div class="form-group col-md-2">
                              <label for="email">Valor de Compras</label>
                              <div class="input-group date"  id="data_3">
                                  <input id="importeTotalPresupuesto" type="text" class="form-control" placeholder="$ 0.00" style= "background-color: white" readonly>
                              </div>
                          </div>
                          <div class="form-group col-md-2">
                              <label for="email">Remanente</label><!--El resultado de restar el presupuesto por su importe-->
                              <div class="input-group date"  id="data_3">
                                  <input id="remanenteDePresupuesto" type="text" class="form-control" placeholder="$ 0.00" style= "background-color: white" readonly>
                              </div>
                          </div>
                      </div>
                      <?php */ ?>
                    </div>
                </div>
                <div class="ibox-content">
<?php 
/*
?>
                    <div style="width: 100%; text-align: center; margin-bottom: 50px;">
                        <button id="importExcelOC" type="button"  class="pull-left btn btn-primary">
                            <i class="fa fa-file-excel-o"></i>
                            Importar OC
                        </button>
                    </div>
<?php 
*/
?>
                    <div class="jqGrid_wrapper">
                        <table id="grid-table"></table>
                        <div id="grid-pager"></div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="modalImportOC" role="dialog">
    <div class="vertical-alignment-helper">
          <div class="modal-dialog vertical-align-center">
            <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Importar Ordenes de Compra</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Seleccione tipo de importación</label>
                              <select class="form-control" name="tipo" id="tipo" required>
                                  <option value="">Seleccione</option>
                                  <option value="">Orden de Compra</option>
                                  <option value="asl">Importación especial</option>
                              </select>
                        </div>
                        <form id="form-import" action="import" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Seleccionar archivo excel para importar</label>
                                <input type="file" name="file" id="file" class="form-control" accept=".xlsx" required>
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
                    </div>
                    <div class="modal-footer">
                        <div class="col-md-6" style="text-align: left">
                            <button id="btn-layoutOC" type="button" class="btn btn-primary">Descargar Layout</button><!--cambiar layout-->
                        </div>
                        <div class="col-md-6" style="text-align: right">
                            <button id="btn-importOC" type="button" class="btn btn-primary">Importar</button><!--funcion de import-->
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
          </div>
    </div>
</div>
<?php 
//**************************************************************************************************
//**************************************************************************************************
//**************************************************************************************************
?>
<input id="id_lista" type="hidden" value="">
<div class="wrapper wrapper-content  animated fadeInRight" id="FORM" style="display: none">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4" id="_title">
                            <h3>Agregar Listas de Descuentos</h3>
                        </div>
                    </div>
                </div>
                <form id="myform">
                  <!--<input id="FolioOrden"  type="hidden" placeholder="Número de Folio" class="form-control">-->

                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group col-md-4">
                                    <label>Almacen*</label>
                                    <select class="form-control" id="Almcen" required>
                                        <option value="">Seleccione</option>
                                        <?php foreach( $listaAlma->getAll() AS $a ): ?>
                                        <option value="<?php echo $a->clave; ?>"><?php echo $a->nombre; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-4"><label>Nombre de la Lista</label> 
                                    <input id="nombre_lista" type="text" placeholder="Nombre de la Lista" class="form-control" required>
                                      <br>
                                        <input type="checkbox" style="display: inline;width: auto;height: auto;" class="form-control" id="caduca" value="0">
                                        <label for="caduca" style="display: inline;">Caduca</label>
                                </div>
                                <div class="form-group col-md-3"><label>Tipo de Lista</label> 
                                  <br>
                                    <label>
                                    <input type="radio" name="tipo_lista" checked id="tipo_lista1" value="1">
                                    Listas de Descuentos Normal</label>
                                    <br>
                                    <label>
                                    <input type="radio" name="tipo_lista" id="tipo_lista2" value="2">
                                    Listas de Descuentos por Rango de Descuentos</label>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="form-group col-md-4">
                                    <label>Fecha de Inicio:</label>
                                    <div class="input-group date" id="data_5">
                                        <input id="fechaini" type="text" class="form-control" style="text-align: center;" required value=""><?php //echo $listaOC->fecha_actual(); ?>
                                        <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </div>
                                </div>
                                <div class="form-group col-md-4" id="fecha_fin" style="display: none;">
                                    <label>Fecha de Fin: </label>
                                    <div class="input-group date" id="data_6">
                                        <input id="fechafin" type="text" required class="form-control" style="text-align: center;">
                                        <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </div>
                                </div>
                            </div>

                            <div>
                              <div class="col-lg-12">
                                <div class="form-group col-lg-12" style="padding: 30px;  border: 2.5px solid; border-color: #dedede; border-radius: 15px"><!--#1ab394-->
                                  <div class="form-group col-md-4" id="Articul" >
                                      <label>Artículo*</label>
                                      <select name="country" id="basic" class="chosen-select form-control">
                                          <option value="">Seleccione Artículo</option>
                                      </select>
                                      <input id="ancho" type="hidden" class="form-control">
                                      <input id="alto" type="hidden" class="form-control">
                                      <input id="fondo" type="hidden" class="form-control">
                                  </div>

                                  <?php 
                                  /*
                                  ?>
                                  <div class="form-group col-md-4" id="precio_min">
                                      <label>Precio</label> 
                                      <input id="PrecioMinimo" type="number" placeholder="Precio" class="form-control" style="text-align: right;">
                                  </div>
                                  <div class="form-group col-md-4" id="precio_max">
                                      <label style="display: none;">Precio Máximo</label> 
                                      <input id="PrecioMaximo" type="number" placeholder="Precio Máximo" class="form-control" style="display: none;text-align: right;">
                                  </div>
                                  <?php 
                                  */
                                  ?>
                                    <div class="form-group col-md-4" id="descuento_min">
                                        <label>Descuento $</label>
                                        <input id="DescuentoMin" type="number" placeholder="Descuento $ " class="form-control" 
                                        style="width: 95%;display: inline;text-align: right;"><b> $</b>
                                    </div>

                                    <div class="form-group col-md-4" id="descuento_max">
                                        <label style="display: none;">Descuento Max $</label>
                                        <input id="DescuentoMax" type="number" placeholder="Descuento Max $ " class="form-control" 
                                        style="width: 95%;display: none;text-align: right;"><b style="display: none;"> $</b>
                                    </div>


                                  <div class="col-lg-12">
                                    <div class="form-group col-md-4">
                                        <label>Costo</label>
                                        <input type="hidden" id="importeHidden" value="0"/>
                                        <input id="importeTotal" disabled type="text" placeholder="$ 00.00 " dir="rtl" class="form-control">
                                    </div>

                                    <div class="form-group col-md-4" id="descuento_min_porc">
                                        <label>Descuento %</label> 
                                        <input id="comisionPorc" type="number" placeholder="Descuento %" class="form-control" 
                                        style="width: 95%;display: inline;text-align: right;"><b> %</b>
                                    </div>

                                    <div class="form-group col-md-4" id="descuento_max_porc">
                                        <label style="display: none;">Descuento Max %</label>
                                        <input id="DescuentoMax_porc" type="number" placeholder="Descuento Max % " class="form-control" 
                                        style="width: 95%;display: none;text-align: right;"><b style="display: none;"> %</b>
                                    </div>

                                    <div class="form-group col-md-8"></div>
                                    <div class="form-group col-md-4">
                                    <br><br>
                                    <label>Aplica a: </label> 
                                    <br>
                                    <label style="margin-right: 15px;">
                                    <input type="radio" name="aplica_a" checked id="aplica_a1" value="1">
                                    Crédito</label>
                                    <label style="margin-right: 15px;">
                                    <input type="radio" name="aplica_a" id="aplica_a2" value="2">
                                    Contado</label>
                                    <label>
                                    <input type="radio" name="aplica_a" id="aplica_a3" value="3">
                                    Crédito y Contado</label>

                                    </div>

                                  </div>
                                </div>
                              </div>
                            </div>

                            <div class="col-lg-12">
                            </div>
                                  
                            <div class="col-lg-12">
                                <input type="hidden" id="articulo" value="0"/>
                                <center><button type="button" class="btn btn-primary" id="addrow">Agregar Producto</button></center>
                            </div>
                            <div class="col-lg-12">
                                <br>
                                <div class="ibox-content">
                                    <div class="jqGrid_wrapper">
                                        <table id="grid-tabla"></table>
                                        <div id="grid-page"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="pull-right">
                                    <a href="#" onclick="cancelar()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                                    <button type="button" class="btn btn-primary ladda-button2" data-style="contract">Guardar</button><!-- id="btnSave"-->
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="hiddenAction">
                        <input type="hidden" id="hiddenID_Aduana">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php 
//**************************************************************************************************
//**************************************************************************************************
//**************************************************************************************************
?>
<div class="modal fade" id="articulos" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body" style="text-align: center;">
                <h3 class="modal-title">Artículos del almacén</h3>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="input-group">
                          <input type="text" id="searchArticle" class="form-control" placeholder="Buscar">
                          <span class="input-group-btn">
                            <button class="btn btn-primary" type="button" onclick="loadArticulos()">Buscar</button>
                          </span>
                        </div><!-- /input-group -->
                    </div>
                </div>
                <br>
                <div class="jqGrid_wrapper">
                    <table id="grid-table_articulos"></table>
                    <div id="grid-pager_articulos"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
  #grid-pager_detalles
  {
    width: auto !important;
  }
</style>
<div class="modal fade" id="ver_detalles" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="width: 1200px">
        <div class="modal-content">
            <div class="modal-body" style="text-align: center;">
                <h3 class="modal-title">Detalles de la Listas de Descuentos: <span id="num_entrada"></span></h3>
                <br>
                <div class="jqGrid_wrapper">
                    <table id="grid-table_detalles"></table>
                    <div id="grid-pager_detalles" style="width: auto;"></div>
                </div>

                <br><br>
                <h3 class="modal-title">Clientes Asignados a la lista</h3>
                <br>
                <div class="jqGrid_wrapper">
                    <table id="grid-table_detalles2"></table>
                    <div id="grid-pager_detalles2"></div>
                </div>

            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="agregar_cliente" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body" style="text-align: left;">
                <h3 class="modal-title">Agregar Cliente a la Lista de Descuentos: <span id="nombre_lista_asignar"></span></h3>
                <br><br>
                <div class="row">
                  <div class="form-group col-md-12">
                    <label>Cliente*</label>
                    <input class="form-control" name="cliente_buscar" id="cliente_buscar" placeholder="Código del Cliente">
                    <input class="form-control" name="cliente" id="cliente" placeholder="Código del Cliente" style="display: none;">
                  </div>
                </div>

                <div class="row">
                  <div class="form-group col-md-12">
                  <label>Clave y Nombre Cliente</label>
                       <select id="desc_cliente" name="desc_cliente" class="form-control">
                       </select>
                  </div>
                </div>

                <div class="row">
                <div class="form-group col-md-12">
                    <label>Destinatario *</label> 
                        <select class="form-control chosen-select" name="destinatario" id="destinatario" disabled></select>
                        <br><br>
                            <button type="button" disabled id="agregar_destinatario" class="btn btn-success" data-toggle="modal" data-target="#modal_destinatario">
                                Asignar Destinatario
                            </button>
                    <br><br>
                    <textarea id="txt-direc" name="observacion" class="form-control" disabled></textarea>
                </div>
              </div>
                <br><br>
            

            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

</span></div></div></span></div></div></div></div></div></form></div></div></div></div>
        <!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="/js/moment.js"></script>
<script src="/js/moment-with-locales.js"></script>

<!-- Peity -->
<script src="/js/plugins/peity/jquery.peity.min.js"></script>

<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.min.js"></script>

<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>

<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>

<script src="/js/plugins/ladda/spin.min.js"></script>
<script src="/js/plugins/ladda/ladda.min.js"></script>
<script src="/js/plugins/ladda/ladda.jquery.min.js"></script>

<!-- Select -->
<script src="/js/plugins/chosen/chosen.jquery.js"></script>
<!-- Data picker -->
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="/js/utils.js"></script> 
<script type="text/javascript">


    var importeOrden_tabla = 0;
    function habilitar(obj) {
        var hab;
        var neeew;
        frm=obj.form; 
        num=obj.selectedIndex; 
        if (num==1) {
          hab=true;
          neeew="";
          console.log("1");
        }
        else {
          hab=false;
          neeew="";
          console.log("2");
        }
        console.log(neeew);
        frm.dictamen.disabled=hab;
        frm.dictamen.value=neeew;
        
    }
  

  
    function mostrarPartida(){
      $.ajax({
          type: "POST",
          dataType: "json",
          data: 
          {
              presupuesto: $("#presupuestoActivo").val(),
              action : "partida"
          },
          beforeSend: function(x)
          { 
              if(x && x.overrideMimeType) 
              { 
                x.overrideMimeType("application/json;charset=UTF-8"); 
              }
          },
          url: '/api/ordendecompra/update/index.php',
          success: function(data) 
          {
              //console.log(data.monto);
             /* if (data.success == true)
              {
                  if($('#presupuestoActivo').val()!="")
                  {
                      $('#partidaPresupuesto').val((data.clave)+" "+"-"+" "+(data.concepto)); 
                  }
                  else
                  {
                      $('#partidaPresupuesto').val(""); 
                  }
              }*/
          }
      });
    }

  
    
    var basic = document.getElementById('basic'),
        //select_user = document.getElementById('usuario'),
        input_cant = document.getElementById('CantPiezas'),
        input_cantM = document.getElementById('cant_prod'),
        input_precioU = document.getElementById('precio_unitario'),
        input_importeT = document.getElementById('importe_total');

    /**
     * @author Ricardo Delgado.
     * Busca y selecciona el almacen predeterminado para el usuario.
     */

    //input_cant.addEventListener("keypress", validateNumber, false);
    //input_cantM.addEventListener("keypress", validateNumber, false);
    //input_precioU.addEventListener("keypress", validateNumber, false);
    //input_importeT.addEventListener("keypress", validateNumber, false);
  
    function selectUser(){
        var user = '<?php echo $_SESSION["id_user"]?>';
        //select_user.value = user;
    } 

    function almacenPrede(swict){ 
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                idUser: '<?php echo $_SESSION["id_user"]?>',
                action: 'search_almacen_pre'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/almacenPredeterminado/index.php',
            success: function(data) {
                if (data.success == true) {
                    if(swict){
                        document.getElementById('Almcen').value = data.codigo.clave;
                        fillSelectArti();
                    }
                    else{
                        document.getElementById('almacen').value = data.codigo.clave;
                        setTimeout(function() {
                            ReloadGrid();
                        }, 1000);
                    }
                }
            },
            error: function(res){
                window.console.log(res);
            }
        });
    }
    
    $(function($) {
        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";

        //resize to fit page size
        $(window).on('resize.jqGrid', function () {
            $(grid_selector).jqGrid( 'setGridWidth', $(".page-content").width() - 60 );
        })
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) {
            if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $(grid_selector).jqGrid( 'setGridWidth', parent_column.width() );
                }, 0);
            }
        })

        $(grid_selector).jqGrid({
            url:'/api/listadescuentos/lista/index.php',
            datatype: "json",
            shrinkToFit: false,
            height:'auto',
            postData: {
                criterio: $("#txtCriterio").val(),
                filtro: $("#filtro").val(),
                almacen: $("#almacen").val(),
                fechai: $("#fechai").val(),
                fechaf: $("#fechaf").val(),
                presupuesto: $("#presupuestoSelect").val()
            },
            mtype: 'POST',
            colNames:["Acciones", 'Status Res', 'Status','ID','Lista','Tipo', 'Fecha Inicio', 'Fecha Fin', 'Total de Productos', 'Total de Clientes', 'Almacén'], 
            colModel:[
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat},
                {name:'status_res',index:'status_res',width:50, editable:false, sortable:false, hidden:true, align: 'center'},
                {name:'status_fecha',index:'status_fecha',width:50, editable:false, sortable:false, hidden:false, align: 'center'},
                {name:'id',index:'id',width:80, editable:false, sortable:false, align: 'right'},
                {name:'lista',index:'lista',width:250, editable:false, sortable:false, hidden:false},
                {name:'tipo',index:'tipo',width:150, editable:false, sortable:false},
                {name:'fechaini',index:'fechaini',width:100, editable:false, sortable:false},
                {name:'fechafin',index:'fechafin',width:100, editable:false, sortable:false},
                {name:'total_productos',index:'total_productos',width:140, editable:false, sortable:false, align: 'right'},
                {name:'total_clientes',index:'total_clientes',width:120, editable:false, sortable:false},
                {name:'almacen',index:'almacen',width:200, editable:false, sortable:false}
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            viewrecords: true,
            loadComplete: almacenPrede()

        });

        // Setup buttons
        $("#grid-table").jqGrid('navGrid', '#grid-pager',
                                {edit: false, add: false, del: false, search: false},
                                {height: 200, reloadAfterSubmit: true}
                               );

        $(window).triggerHandler('resize.jqGrid');

        function imageFormat( cellvalue, options, rowObject ){

            var status_res = rowObject[1];
            var id     = rowObject[3];
            var nombre = rowObject[4];
            $("#hiddenID_Aduana").val(id);
            //console.log(id);
            var html = '<a href="#" onclick="ver(\''+id+'\',\''+nombre+'\')"><i class="fa fa-search" title="Ver Detalles"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            //if(status_res == 1)
            if($("#permiso_editar").val() == 1)
            {
            html += '<a href="#" onclick="editarLista(\''+id+'\')"><i class="fa fa-edit" title="Editar Lista"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            //if(status_res == 1)
            if($("#permiso_registrar").val() == 1)
            {
            html += '<a href="#" onclick="AgregarCliente(\''+id+'\',\''+nombre+'\')"><i class="fa fa-user-plus" title="Agregar Clientes"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            }
              //html += '<a href="#" onclick="exportarPDF(\''+serie+'\')"><i class="fa fa-file-pdf-o" title="PDF"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
          
            return html;
        }

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });
        //////////////////////////////////////////////////////////Aqui se contruye el Grid 2 //////////////////////////////////////////////////////////
    $(function($) {
        var grid_selector = "#grid-tabla";
        var pager_selector = "#grid-page";

        //resize to fit page size
        $(window).on('resize.jqGrid', function () {
            $(grid_selector).jqGrid( 'setGridWidth', $(".page-content").width() - 60 );
        })
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) {
            if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $(grid_selector).jqGrid( 'setGridWidth', parent_column.width() );
                }, 0);
            }
        })

        $(grid_selector).jqGrid({
            //url:'',
            datatype: "json",
            shrinkToFit: false,
            height:'auto',
            mtype: 'POST',
            colNames:['Acciones', 'Clave','Nombre','Costo $','Descuento Mín $','Descuento Máx $','Descuento Min %', 'Descuento Max %', 'Aplica Val', 'Aplica a'],
            colModel:[
                {name:'myac',index:'', width:130, fixed:true, sortable:false, resize:false, formatter:imageFormat2},
                {name:'codigo',index:'codigo',width:150, editable:false, sortable:false, align: 'right'},
                {name:'descripcion',index:'descripcion',width:250, editable:false, sortable:false},
                {name:'costo',index:'costo',width:150, editable:false, hidden:false, sortable:false, align: 'right'},
                {name:'preciomin',index:'preciomin',width:150, sortable:false, align: 'right'},
                {name:'preciomax',index:'preciomax',width:150, editable:false, sortable:false, align: 'right'},
                {name:'comisionporc',index:'comisionporc',width:150, editable:false, hidden:false, sortable:false, align: 'right'},
                {name:'descuentomax',index:'descuentomax',width:150, editable:false, hidden:false, sortable:false, align: 'right'},
                {name:'aplica_a_val',index:'aplica_a_val',width:100, editable:false, hidden:true, sortable:false, align: 'center'},
                {name:'aplica_a',index:'aplica_a',width:100, editable:false, hidden:false, sortable:false, align: 'center'}
            ],
            rowNum:30,
            rowList:[30,40,50],
            //pager: pager_selector,
            sortname: 'cve_gpoart',
            viewrecords: true,
            sortorder: "desc",
            ondblClickRow: function (rowid, iRow,iCol) {
                var row_id = $(grid_selector).getGridParam('selrow');
                $(grid_selector).editRow(row_id, true);
            }
        });

        $("#DescuentoMin, #DescuentoMax").keyup(function()
        {
            $("#comisionPorc, #DescuentoMax_porc").val("");
        });

        $("#comisionPorc, #DescuentoMax_porc").keyup(function()
        {
            $("#DescuentoMin, #DescuentoMax").val("");
        });


        $("#addrow").click( function() {
            if (basic.value == "")
            {
                swal(
                    'Error',
                    'Por favor seleccione un articulo',
                    'error'
                );
                return;
            }



            if (($('#DescuentoMin').val() == "" || ($('#DescuentoMax').val() == "" && $("input[name=tipo_lista]:checked").val() == 2)) && $("#comisionPorc").val() == "")
            {
              var msj = 'Debe establecer un descuento en precio';
              if($("input[name=tipo_lista]:checked").val() == 2)
                  msj = 'Debe establecer un rango de descuentos en precios';
                swal(
                    'Error',
                    msj,
                    'error'
                );
                return;
            }


            if (($('#comisionPorc').val() == "" || ($('#DescuentoMax_porc').val() == "" && $("input[name=tipo_lista]:checked").val() == 2)) && $("#DescuentoMin").val() == "")
            {
              var msj = 'Debe establecer un descuento en porcentaje';
              if($("input[name=tipo_lista]:checked").val() == 2)
                  msj = 'Debe establecer un rango de descuentos en porcentaje';
                swal(
                    'Error',
                    msj,
                    'error'
                );
                return;
            }

            if($("input[name=tipo_lista]:checked").val() == 2 && parseFloat($('#DescuentoMin').val()) >= parseFloat($('#DescuentoMax').val()))
            {
                swal(
                    'Error',
                    'El Precio Mínimo No puede ser Mayor o Igual al Precio Máximo',
                    'error'
                );
                return;
            }

            if($("input[name=tipo_lista]:checked").val() == 2 && parseFloat($('#comisionPorc').val()) >= parseFloat($('#DescuentoMax_porc').val()))
            {
                swal(
                    'Error',
                    'El Descuento Mínimo No puede ser Mayor o Igual al Descuento Máximo',
                    'error'
                );
                return;
            }

/*
            if(parseFloat($('#DescuentoMin').val()) < parseFloat($('#comisionPrec').val()))
            {
                swal(
                    'Error',
                    'El Descuento $ No puede ser Mayor o Igual al Precio Mínimo',
                    'error'
                );
                return;
            }

            if(parseFloat($('#DescuentoMin').val()) < parseFloat($('#importeTotal').val()))
            {
                swal(
                    'Error',
                    'El Precio de Venta no puede ser inferior al costo',
                    'error'
                );
                return;
            }
*/
            var ids = $("#grid-tabla").jqGrid('getDataIDs');

            console.log("ids= ", ids);
            for (var i = 0; i < ids.length; i++)
            {
                var rowId = ids[i];
                var rowData = $('#grid-tabla').jqGrid ('getRowData', rowId);

                console.log("rowData = ", rowData);
                console.log("rowData.codigo = ", rowData.codigo);

                if (rowData.codigo==basic.value) 
                {
                    window.alert("Este Artículo ya fue incluido");
                    return;
                }
            }

            if($("input[name=tipo_lista]:checked").val() == 1)
               $('#DescuentoMax').val($('#DescuentoMin').val());

             if($('#comisionPorc').val() == "") $('#comisionPorc').val(0);
             if($('#comisionPrec').val() == "") $('#comisionPrec').val(0);

            var aplica_a = "";
            if($("input[name=aplica_a]:checked").val() == 1)
              aplica_a = "Crédito";
            else if($("input[name=aplica_a]:checked").val() == 2)
              aplica_a = "Contado";
            else 
              aplica_a = "Crédito y Contado";

            emptyItem=[{
                codigo       : basic.value,
                descripcion  : $('#basic :selected').text(),
                preciomin    : ($('#DescuentoMin').val()=="")?0:parseFloat($('#DescuentoMin').val()).toFixed(2),
                preciomax    : ($('#DescuentoMax').val()=="")?0:parseFloat($('#DescuentoMax').val()).toFixed(2),
                comisionporc : ($('#comisionPorc').val()=="")?0:parseFloat($('#comisionPorc').val()).toFixed(2),
                descuentomax : ($('#DescuentoMax_porc').val()=="")?0:parseFloat($('#DescuentoMax_porc').val()).toFixed(2),
                costo        : $('#importeTotal').val(),
                aplica_a_val : $("input[name=aplica_a]:checked").val(),
                aplica_a     : aplica_a
                //utilidad     : (parseFloat($('#DescuentoMin').val()).toFixed(2)-parseFloat($('#importeTotal').val())-parseFloat($('#comisionPrec').val()).toFixed(2))
            }];
            $("#grid-tabla").jqGrid('addRowData',0,emptyItem);

            $("#tipo_lista1, #tipo_lista2").prop("disabled", true);
            //$("#grid-tabla").jqGrid('editRow', 0,true);

            basic.value = '';
            $('.chosen-select').trigger("chosen:updated");
            $("#DescuentoMin").val("");
            $("#DescuentoMax").val("");
            $("#DescuentoMax_porc").val("");
            $("#comisionPorc").val("");
            $("#comisionPrec").val("");
            $('#importeTotal').val("");

            //calcularTotal();

        });

        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size

        //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
        function imageFormat2( cellvalue, options, rowObject ){
            var correl = options.rowId;
            var	html = ''; //html = '<a href="#" onclick="editarAdd(\''+correl+'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="borrarAdd(\''+correl+'\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

            return html;
        }

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });

    function borrarAdd(_codigo) {
        $("#grid-tabla").jqGrid('delRowData', _codigo);
        calcularTotal();
    }

    function editarAdd(_codigo){
        var data=$("#grid-tabla").jqGrid('getRowData', _codigo);
        $("#modaltitle").html('<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><i class="fa fa-laptop modal-icon"></i><h4 class="modal-title">Editar Producto</h4>');
        $modal0 = $("#myModal");
        $("#clave_prod").val(data.codigo);
        $("#row_prod").val(_codigo);
        $("#cant_prod").val(data.CantPiezas);
        $("#peso_prod_total").val(data.peso);
        $("#precio_unitario").val(data.precioUnitario);
        $("#importe_total").val(data.importeTotal);//Detalle del producto
        $("#peso_prod").val(data.pesohidden);
        $("#nombre_prod").val(data.descripcion);
        $modal0.modal('show');
        l.ladda('stop');
        calcularTotal();
    }

    //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////
    function ReloadGrid() {
        console.log($("#filtro").val());
        $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                criterio: $("#txtCriterio").val(),
                filtro: $("#filtro").val(),
                almacen: $("#almacen").val(),
                fechai: $("#fechai").val(),
                fechaf: $("#fechaf").val(),
                presupuesto: $("#presupuestoSelect").val()
            }, datatype: 'json', page : 1})
            .trigger('reloadGrid',[{current:true}]);
      
            $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                criterio: $("#txtCriterio").val(),
                fechaInicio: $("#fechai").val(),
                fechaFin: $("#fechaf").val(),
                almacen: $("#almacen").val(),
                status: $("#status").val(),
                presupuesto: $("#presupuestoSelect").val(),
                action : "totalesPedido"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ordendecompra/update/index.php',
            success: function(data) {
                if (data.success == true) {
					if (data.total_pedido){

				$("#totales").empty();
				$("#totales").append("Total Pedido: "+data.total_pedido);
					} else{
				$("#totales").empty();
				$("#totales").append("Total Pedido: 0");

					}

                }
            }
        });
    }

    $modal0 = null;

    function borrar(_codigo) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                ID_Aduana : _codigo,
                action : "delete"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/ordendecompra/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    //$('#codigo').prop('disabled', true);
                    ReloadGrid();
                }
            }
        });
    }

    function editar(_codigo) {
        $("#_title").html('<h3>Editar Listas de Descuentos</h3>');
        $.ajax({
            type: "POST",
            dataType: "json",

            data: {
                codigo : _codigo,
                action : "load"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/ordendecompra/update/index.php',
            success: function(data) {
                if (data.success == true) {

                    $("#FolioOrden").val(data.num_pedimento);
                    $("#fechaestimada").val(data.fech_llegPed);
                    $("#fechaentrada").val(data.fech_pedimento);
                    $("#fechaentrada").attr('disabled', true);
                    $("#Almcen").val(data.Cve_Almac);
                    $("#Proveedr").val(data.ID_Proveedor);
                    $("#Protocol").val(data.ID_Protocolo);
                    $("#Consecut").val(data.Consec_protocolo);
                    $("#NumOrden").val(data.factura);
                    $("#status").val(data.status);
                    $("#usuario").val(data.cve_usuario);
                    $("#tipoDeRecurso").val(data.recurso);
                    $("#tipoDeProcedimiento").val(data.procedimiento);
                    $("#areaSolicitante").val(data.areaSolicitante);
                    $("#numeroDeSuficiencia").val(data.numSuficiencia);
                    $("#fechaSuficiencia").val(data.fechaSuficiencia);
                    $("#montoSuficiencia").val(data.montoSuficiencia);
                    $("#numeroContrato").val(data.numeroContrato);
                    $("#fechaContrato").val(data.fechaContrato);
                    $("#numeroDictamen").val(data.dictamen);
                    $("#presupuestoActivo").val(data.presupuesto);
                    $("#condicionesDePago").val(data.condicionesDePago);
                    $("#lugarDeEntrega").val(data.lugarDeEntrega);
                    $("#fechaDeFallo").val(data.fechaDeFallo);
                    $("#plazoDeEntrega").val(data.plazoDeEntrega);
                    $("#numeroDeExpediente").val(data.numeroDeExpediente);
                    
                    $("#importeOrden").val(data.importe);


                    $("#grid-tabla").jqGrid("clearGridData");

                    if (data.detalle) {
                        for (var i = 0; i < data.detalle.length; i++) {
                            emptyItem=[{
                                        codigo:data.detalle[i].cve_articulo,
                                        descripcion:data.detalle[i].des_articulo,
                                        CantPiezas:data.detalle[i].cantidad,
                                        peso:data.detalle[i].cantidad*data.detalle[i].peso,pesohidden:data.detalle[i].peso,
                                        volumen:data.detalle[i].volumen,
                                        precioUnitario:data.detalle[i].precioUnitario,
                                        importeTotal:data.detalle[i].importeTotal,
                                        recurso:data.detalle[i].recurso,
                                        procedimiento:data.detalle[i].procedimiento,
                                        areaSolicitante:data.detalle[i].areaSolicitante,
                                        numSuficiencia:data.detalle[i].numSuficiencia,
                                        fechaSuficiencia:data.detalle[i].fechaSuficiencia,
                                        montoSuficiencia:data.detalle[i].montoSuficiencia,
                                        numeroContrato:data.detalle[i].numeroContrato,
                                        fechaContrato:data.detalle[i].fechaContrato,
                                        dictamen:data.detalle[i].dictamen,
                                        presupuesto:data.detalle[i].presupuesto,
                                        condicionesDepago:data.detalle[i].condicionesDepago,
                                        lugarDeEntrega:data.detalle[i].lugarDeEntrega,
                                        fechaDeFallo:data.detalle[i].fechaDeFallo,
                                        plazoDeEntrega:data.detalle[i].plazoDeEntrega,
                                        numeroDeExpediente:data.detalle[i].numeroDeExpediente
                                       
                                   
                                      }];
                          $("#grid-tabla").jqGrid('addRowData',0,emptyItem);
                        }
                    }
                    fillSelectArti();
                    l.ladda('stop');
                    $("#btnCancel").show();

                    $('#list').removeAttr('class').attr('class', '');
                    $('#list').addClass('animated');
                    $('#list').addClass("fadeOutRight");
                    $('#list').hide();

                    $('#FORM').show();
                    $('#FORM').removeAttr('class').attr('class', '');
                    $('#FORM').addClass('animated');
                    $('#FORM').addClass("fadeInRight");

                    $('#FolioOrden').prop('disabled', true);

                    $("#hiddenAction").val("edit");

                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        data: {
                            codigo : data.num_pedimento,
                            status : "A",
                            id_user: '<?php echo $_SESSION['id_user']; ?>',
                            action : "editando"
                        },
                        beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                                },
                        url: '/api/ordendecompra/update/index.php',
                        success: function(data) {
                        }
                    });

                }
            }
        });
    }

    function cancelar() {

        window.location.reload();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                codigo : $("#FolioOrden").val(),
                status : "C",
                action : "editando",
                id_user: '<?php echo $_SESSION['id_user']; ?>',
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/ordendecompra/update/index.php',
            success: function(data) {
                ReloadGrid();
            }
        });

        $(':input','#myform')
            .removeAttr('checked')
            .removeAttr('selected')
            .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
            .val('');
        $('#FORM').removeAttr('class').attr('class', '');
        $('#FORM').addClass('animated');
        $('#FORM').addClass("fadeOutRight");
        $('#FORM').hide();

        $('#list').show();
        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeInRight");
        $('#list').addClass("wrapper");
        $('#list').addClass("wrapper-content");

    }

    $('#data_3').datetimepicker({
        locale: 'es',
        format: 'DD-MM-YYYY',
        useCurrent: false
    });

    $('#data_4').datetimepicker({
        locale: 'es',
        format: 'DD-MM-YYYY',
        useCurrent: false
    });

    $('#data_5').datetimepicker({
        locale: 'es',
        format: 'DD-MM-YYYY',
        useCurrent: true
    });

    $('#data_6').datetimepicker({
        locale: 'es',
        format: 'DD-MM-YYYY',
        useCurrent: false
    });

    $('#data_new').datetimepicker({
        locale: 'es',
        format: 'DD-MM-YYYY',
        useCurrent: false
    });
  
    $('#data_new2').datetimepicker({
        locale: 'es',
        format: 'DD-MM-YYYY',
        useCurrent: false
    });
  
    $('#data_new3').datetimepicker({
        locale: 'es',
        format: 'DD-MM-YYYY',
        useCurrent: false
    });

    $('#data_6').datetimepicker({
        locale: 'es',
        format: 'DD-MM-YYYY HH:mm:ss',
        useCurrent: false
    });
  
    $('#data_5').data("DateTimePicker").minDate(new Date());
    $('#data_6').data("DateTimePicker").minDate(new Date());
    //$('#data_new').data("DateTimePicker").minDate(new Date());
    $('#data_new2').data("DateTimePicker");
    $('#data_new3').data("DateTimePicker");

    $('#data_3 .input-group.date').datepicker({
        todayBtn: "linked",
        keyboardNavigation: false,
        forceParse: false,
        calendarWeeks: true,
        autoclose: true,
        format: 'dd-mm-yyyy',
        'default': 'now',
    });

    function agregar() {
        almacenPrede(true);
        //selectUser();
        
        $('input, #basic').each(function(){
            $(this).html("");
        });
        //$("#FolioOrden").val("<?php echo $listaOC->getMax()->id + 1;?>")
        $('.chosen-select').trigger('chosen:updated');
        //$("#Consecut").val("<?php echo $consec+1; ?>");
        $('#CodeMessage').html("");
        $("#_title").html('<h3>Agregar Lista de Descuentos</h3>');
        //$('#data_6').data("DateTimePicker").date(new Date());
        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeOutRight");
        $('#list').hide();
        

        $('#FORM').show();
        $('#FORM').removeAttr('class').attr('class', '');
        $('#FORM').addClass('animated');
        $('#FORM').addClass("fadeInRight");
        $("#basic").val("");

        $('#grid-tabla').jqGrid('clearGridData');
        $('#grid-tabla').trigger('reloadGrid');
        $("#hiddenAction").val("add");
      
        $("#dictamenActivo").val("No");
        $("#numeroDictamen").prop("disabled",true);
        
        //$('#FolioOrden').prop('disabled', false);
        //calcularTotal();
    }

    function editarLista(id) {
        almacenPrede(true);
        //selectUser();

        $("#tipo_lista1, #tipo_lista2, #caduca").prop("disabled", true);
        $("#id_lista").val(id);
        $('input, #basic').each(function(){
            $(this).html("");
        });
        $('.chosen-select').trigger('chosen:updated');
        $('#CodeMessage').html("");
        $("#_title").html('<h3>Editar Lista de Descuentos</h3>');
        //$('#data_6').data("DateTimePicker").date(new Date());
        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeOutRight");
        $('#list').hide();

        $('#FORM').show();
        $('#FORM').removeAttr('class').attr('class', '');
        $('#FORM').addClass('animated');
        $('#FORM').addClass("fadeInRight");
        $("#basic").val("");

        //$('#grid-tabla').jqGrid('clearGridData');
        //$('#grid-tabla').trigger('reloadGrid');
        //$("#hiddenAction").val("add");
      
        //$("#dictamenActivo").val("No");
        //$("#numeroDictamen").prop("disabled",true);

        //$('#FolioOrden').prop('disabled', false);
        //calcularTotal();

        //console.log("EDITAR ID = ", id);

        $.ajax({
            type: "POST",
            dataType: "json",

            data: {
                codigo : id,
                action : "load_lista"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/listadescuentos/update/index.php',
            success: function(data) {
              console.log("EDITAR = ", data);
                if (data.success == true) {

                    $("#Almcen").val(data.cab_lista[0].clave);
                    $("#nombre_lista").val(data.cab_lista[0].Lista);
                    $("#fechaini").val(data.cab_lista[0].FechaIni);
                    $("#fechafin").val(data.cab_lista[0].FechaFin);

                    if(data.cab_lista[0].Caduca == 1)
                    {
                      $("#caduca").prop('checked', true);
                      $("#caduca").val(1);
                      $("#fecha_fin").show();
                    }
                    else
                      $("#fechafin").hide();

                    if(data.cab_lista[0].Tipo == 1)
                    {
                      $("#tipo_lista1").prop('checked', true);
                      $("#descuento_max label, #descuento_max input, #descuento_max b").hide();
                      $("#descuento_max_porc label, #descuento_max_porc input, #descuento_max_porc b").hide();
                    }
                    else
                    {
                      $("#tipo_lista2").prop('checked', true);
                      $("#descuento_max label, #descuento_max input, #descuento_max b").show();
                      $("#descuento_max_porc label, #descuento_max_porc input, #descuento_max_porc b").show();
                      $("#descuento_max input").css("display", "inline");
                      $("#descuento_max_porc input").css("display", "inline");
                    }

                    for (var i = 0; i < data.det_lista.length; i++) {
                      var tipo_aplica = "Crédito y Contado";
                        if(data.det_lista[i].Tipo == 1) tipo_aplica = "Crédito";
                        else if(data.det_lista[i].Tipo == 2) tipo_aplica = "Contado";

                        emptyItem=[{
                                      codigo       : data.det_lista[i].Cve_Articulo,
                                      descripcion  : data.det_lista[i].des_articulo,
                                      preciomin    : data.det_lista[i].PrecioMin,
                                      preciomax    : data.det_lista[i].PrecioMax,
                                      comisionporc : data.det_lista[i].ComisionPor,
                                      descuentomax : data.det_lista[i].FactorMax,
                                      aplica_a_val : data.det_lista[i].Tipo,
                                      aplica_a     : tipo_aplica
                                  }];
                      $("#grid-tabla").jqGrid('addRowData',0,emptyItem);
                    }

                    $("#hiddenAction").val("edit");
                }
            }, error: function(data){
              console.log("ERROR EDITAR = ", data);
            }
        });


    }

    function calcularTotal()
    {
        var ids = $("#grid-tabla").jqGrid('getDataIDs');

        var total = 0;
        
        for (var i = 0; i < ids.length; i++)
        {
            var rowId = ids[i];
            var rowData = $('#grid-tabla').jqGrid ('getRowData', rowId);
            //console.log(rowData);
            total+=parseFloat(rowData.importeTotal);
        }
        //return total;
        $('#importeOrden').val(total);
    }

    $("#agregar_destinatario").click(function()
    {
        $.ajax({
            type: "POST",
            dataType: "json",
            url: '/api/listadescuentos/update/index.php',
            data: {
                id_lista: $("#id_lista").val(),
                id_destinatario: $("#destinatario").val(),
                action : 'asignar_destinatario_descuento'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); } console.log("before", x);},
            success: function(data) {
              console.log("success", data);
                  swal('Excelente','Cliente Asignado con Éxito','success');
                  window.location.reload();
            }, error: function(data)
            {
                console.log("error", data);
            }
        });
    });

    var l = $( '.ladda-button2' ).ladda();
    l.click(function() 
    {
        var inicio = $("#fechaini").val();
        var final  = $("#fechafin").val();

        var arrDetalle = [];

        var ids = $("#grid-tabla").jqGrid('getDataIDs');

        if (ids.length == 0){
            swal(
                'Error',
                'Por favor seleccione productos',
                'error'
            );
            return;
        }
        if($("#nombre_lista").val() == "")
        {
            swal(
                'Error',
                'Por favor coloque un nombre a la Lista de Descuentos',
                'warning'
            );
            return;
        }

        if($("#fechaini").val() == "")
        {
            swal(
                'Error',
                'Por favor coloque una Fecha Inicial',
                'warning'
            );
            return;
        }

        if($("#fechafin").val() == "" && $("#caduca").val() == 1)
        {
            swal(
                'Error',
                'Por favor coloque una Fecha Final',
                'warning'
            );
            return;
        }


        console.log("fecha inicio = ", inicio);
        console.log("fecha fin = ", final);
        if(final < inicio && $("#caduca").val() == 1)
        {
            swal(
                'Error',
                'La Fecha Final No puede ser menor que la fecha inicial',
                'warning'
            );
            return;
        }

        for (var i = 0; i < ids.length; i++)
        {
            var rowId = ids[i];
            var rowData = $('#grid-tabla').jqGrid ('getRowData', rowId);
            
            var inputPU = document.getElementById('precioUnitario');

          //TODO: Envia items de la orden - precioNew
            arrDetalle.push({
                codigo: rowData.codigo,
                descripcion: rowData.descripcion,
                preciomin: rowData.preciomin,
                preciomax: rowData.preciomax,
                comisionporc: rowData.comisionporc,
                descuentomax: rowData.descuentomax,
                costo: rowData.costo,
                aplica_a_val: rowData.aplica_a_val,
                aplica_a: rowData.aplica_a
            });
        }
      
      
        //return;

        l.ladda( 'start' );
        $.ajax({
            type: "POST",
            dataType: "json",
            url: '/api/listadescuentos/update/index.php',
            data: {
                id_lista: $("#id_lista").val(),
                action : $("#hiddenAction").val(),
                nombre_lista: $("#nombre_lista").val(),
                Almcen: $("#Almcen").val(),
                caduca: $("#caduca").val(),
                fechaini: $("#fechaini").val(),
                fechafin: $("#fechafin").val(),
                tipo_lista: $("input[name=tipo_lista]:checked").val(),
                arrDetalle: arrDetalle
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); } console.log("before", x);},
            success: function(data) {
              console.log("success", data);
                console.log($("#hiddenAction").val());
                  if($("#hiddenAction").val() === "add") {
                      swal('Excelente','Lista de Descuentos Creada con exito.','success');
                      location.reload();
                  }
                  else {
                    swal('Excelente','Lista de Descuentos Modificada con exito.','success');
                    location.reload();
                  }
                  
              l.ladda('stop');
            }, error: function(data)
            {
                console.log("error", data);
            }
        });
    });

    var l2 = $( '.ladda-button3' ).ladda();
    l2.click(function() {
        if ($("#clave_prod").val()=="") return;
        $("#grid-tabla").jqGrid('setCell',$("#row_prod").val(),0,$("#clave_prod").val());
        $("#grid-tabla").jqGrid('setCell',$("#row_prod").val(),1,$("#nombre_prod").val());
        $("#grid-tabla").jqGrid('setCell',$("#row_prod").val(),2,$("#cant_prod").val());
        $("#grid-tabla").jqGrid('setCell',$("#row_prod").val(),3,$("#peso_prod_total").val());
        $("#grid-tabla").jqGrid('setCell',$("#row_prod").val(),4,$("#peso_prod").val());
        $("#grid-tabla").jqGrid('setCell',$("#row_prod").val(),6,$("#precio_unitario").val());
        $("#grid-tabla").jqGrid('setCell',$("#row_prod").val(),7,parseFloat($("#cant_prod").val())*parseFloat($("#precio_unitario").val()));
        $modal0 = $("#myModal");
        $modal0.modal('hide');
        l.ladda('stop');
        
        calcularTotal();
    });

    var inputPU = document.getElementById('precioUnitario');
    var inputCP = document.getElementById('CantPiezas');
    var importeTotal = document.getElementById('importeTotal');
    var importeOrden = document.getElementById('importeOrden');

    var updateInputs = function () {

        var cant_piezas = 0;
        if($("#modo_peso").val() == 1)
        {
            cant_piezas = $("#CantPiezasPeso").val();
            inputCP = document.getElementById('CantPiezasPeso');
        }
        else 
        {
            cant_piezas = parseInt($("#CantPiezas").val());
            inputCP = document.getElementById('CantPiezas');
        }

        importeTotal.value = inputPU.value*cant_piezas;
        //importeOrden.value = inputPU.value*$("#CantPiezas").val();
        //console.log("importeOrden_tabla", importeOrden_tabla);

        importeOrden.value = parseFloat(importeOrden_tabla) + parseFloat(inputPU.value*cant_piezas);

        //calcularTotal();
        //$('#importeOrden').val(parseFloat($('#importeOrden').val())+parseFloat(importeTotal.value));
    }

    //if (inputPU.addEventListener) 
    //{
    //  inputPU.addEventListener('keyup', function () {
    //      updateInputs();
    //  });
    //}
  
    //if (inputCP.addEventListener) 
    //{
    //  inputCP.addEventListener('keyup', function () {
    //      updateInputs();
    //  });
    //}


    $("#caduca").change(function(){

        if($(this).val() == 0)
        {
            $(this).val("1");
            $("#fecha_fin").show();
        }
        else
        {
            $(this).val("0");
            $("#fechafin").val('');
            $("#fecha_fin").hide();
        }
    });


    $("#tipo_lista1, #tipo_lista2").change(function(){

      var tipo_lista = $("input[name=tipo_lista]:checked").val();

      if(tipo_lista == 1)
      {
          $("#descuento_min label").text("Descuento $");
          $("#descuento_min input").attr("placeholder", "Descuento $");

          $("#descuento_min_porc label").text("Descuento %");
          $("#descuento_min_porc input").attr("placeholder", "Descuento %");

          $("#descuento_max label, #descuento_max input, #descuento_max b").hide();
          $("#descuento_max_porc label, #descuento_max_porc input, #descuento_max_porc b").hide();
      }
      else
      {
        $("#descuento_min label").text("Descuento Min $");
        $("#descuento_min input").attr("placeholder", "Descuento Min $");
        $("#descuento_min_porc label").text("Descuento Min %");
        $("#descuento_min_porc input").attr("placeholder", "Descuento Min %");

        $("#descuento_max label, #descuento_max input, #descuento_max b").show();
        $("#descuento_max input").css("display", "inline");

        $("#descuento_max_porc label, #descuento_max_porc input, #descuento_max_porc b").show();
        $("#descuento_max_porc input").css("display", "inline");

      }

      $("#comisionPorc, #comisionPrec").val("");

    });

    $('#basic').change(function(e) {
        var cve_articulo= $(this).val(),
            option = $(this).find('option:selected'),
            id = option.data('id');

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_articulo : id,
                action : "load"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/articulos/update/index.php',
            success: function(data) {
                
                //$('#pesohidden').val(data.peso);
//                //$('#peso').val(data.peso);
//                //$('#PUhidden').val(data.peso);
//                //
//                //if($("#articulo").val()!=data.cve_articulo)
//                //{
//                //  $('#precioUnitario').val(data.costo);
//                //}
//                //$('#importeHidden').val(data.costo);
//
//                //if($('#precioUnitario').val()==data.costo)
//                //{
//                //    $('#importeTotal').val(data.costo);
//                //}
//                //else if($('#precioUnitario').val()!=data.costo)
//                //{
//                //    $('#importeTotal').val(importeTotal);
//                //}
//
//                //importeOrden.value = parseFloat(importeOrden_tabla) + parseFloat($('#importeTotal').val());
//
//                //$("#CantPiezas").val("1");
//                //$("#CantPiezasPeso").val("1");
//
                //console.log("control_peso = ", data.control_peso);
                //if(data.control_peso == 1)
                //{
                //  $("#CantPiezas").hide();
                //  $("#CantPiezasPeso").show();
                //}
                //else
                //{
                //  $("#CantPiezas").show();
                //  $("#CantPiezasPeso").hide();
                //}
                //$("#modo_peso").val(data.control_peso);

                $("#articulo").val(data.cve_articulo);
                $('#importeTotal').val(data.costo);

                //$("#alto").val(data.alto);
                //$("#ancho").val(data.ancho);
                //$("#fondo").val(data.fondo);
              
               
            }
        });
    });

    $('#Protocol').change(function(e) {
        var ID_Protocolo= $(this).val();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                ID_Protocolo : ID_Protocolo,
                action : "getConsecutivo"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/ordendecompra/update/index.php',
            success: function(data) {
                $('#Consecut').val(data.consecutivo);
            }

        });

    });

//******************************************************************************************************
//******************************************************************************************************
        $("#cliente_buscar").on('keyup', function(e)
        {
            var cliente = e.target.value;
            //console.log("cliente.length = ", cliente.length);
            if(cliente.length > 2)
                BuscarCliente(cliente);
            else
                BuscarCliente("Borrar_La_Lista_de_Clientes");
        });

        function BuscarCliente(cliente)
        {
            console.log("Cliente = ", cliente);
            console.log("id_almacen = ", <?php echo $_SESSION['id_almacen']; ?>);
            document.getElementById("agregar_destinatario").removeAttribute("disabled");
            $.ajax({
                url: '/api/nuevospedidos/lista/index.php',
                data: {
                    action: 'getClientesSelect',
                    listaD: 1,
                    cliente: cliente,
                    id_almacen: <?php echo $_SESSION['id_almacen']; ?>
                },
                dataType: 'json',
                method: 'GET',
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                success: function(data) 
                {
                    console.log("SUCCESS: ", data);
                    //var combo = data.combo;
                    //if(combo.length > 36 || data.direccion.length > 2){
                        var desc_cliente = document.getElementById("desc_cliente");
                        if(data.combo !== '' && data.find == true){
                            desc_cliente.innerHTML = data.combo;
                            var text = desc_cliente.options[desc_cliente.selectedIndex].text;
                            //document.getElementById("txt-direc").value = text;
                            desc_cliente.removeAttribute('disabled');
                            //$("#cliente").val(data.clave_cliente);
                            //$("#desc_cliente").val("["+data.clave_cliente+"] - "+data.nombre_cliente);
                            $("#cliente").val(data.firsTValue);
                            BuscarDestinatario(data.firsTValue);
                            console.log("#cliente = ", $("#cliente").val());
                        }
                        else
                        {
                            $("#destinatario, #agregar_destinatario").prop("disabled", true);
                            $("#cliente").val("");
                            $("#desc_cliente").val("");
                            console.log("#cliente = ", $("#cliente").val());
                        }

                        $(".chosen-select").trigger("chosen:updated");
                }, error: function(data){
                    console.log("ERROR: ", data);
                }

            });
        }

        function BuscarDestinatario(cliente)
        {
            document.getElementById("agregar_destinatario").removeAttribute("disabled");
            $.ajax({
                url: '/api/nuevospedidos/lista/index.php',
                data: {
                    action: 'getDestinatario',
                    descuento: 1,
                    cliente: cliente
                },
                dataType: 'json',
                method: 'GET',
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                success: function(data) 
                {
                    console.log("SUCCESS: ", data);
                    var destinatario = document.getElementById("destinatario");
                    if(data.combo !== '' && data.find == true){
                        destinatario.innerHTML = data.combo;
                        var text = destinatario.options[destinatario.selectedIndex].text;
                        document.getElementById("txt-direc").value = text;
                        destinatario.removeAttribute('disabled');
                    }
                    else
                    {
                        $("#destinatario, #agregar_destinatario").prop("disabled", true);
                    }

                    $(".chosen-select").trigger("chosen:updated");
                }, error: function(data){
                    console.log("ERROR: ", data);
                }

            });
        }

        $("#desc_cliente").on("change", function(e){
            var cliente = $(this).val();
            BuscarDestinatario(cliente);
            //console.log("Cliente = ", cliente);
        });

        $("#destinatario").on("change", function(e){
            var destinatario = document.getElementById("destinatario");
            var text = destinatario.options[destinatario.selectedIndex].text;
            document.getElementById("txt-direc").value = text;
        });
//******************************************************************************************************
//******************************************************************************************************
    $("#CantPiezas").keyup(function(e) {

        var cantidad = $(this).val();
        var peso =$("#pesohidden").val();
        var pu =$("#precioUnitario").val();
        
        
        $("#peso").val(cantidad*peso);
        $("#importeTotal").val((cantidad*pu).toFixed(2));
      
                               
        
    });
   
    $("#CantPiezasPeso").keyup(function(e) {

        var cantidad = $(this).val();
        var peso =$("#pesohidden").val();
        var pu =$("#precioUnitario").val();
        
        
        $("#peso").val(cantidad*peso);
        $("#importeTotal").val((cantidad*pu).toFixed(2));
      
                               
        
    });

    $("#FolioOrden").keyup(function(e) {

        var claveCode = $(this).val();
        var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,20}$");

        if (claveCodeRegexp.test(claveCode)) {
            $("#CodeMessage").html("");
            $("#btnSave").prop('disabled', false);

            var num_pedimento = $(this).val();

            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    num_pedimento : num_pedimento,
                    action : "exists"
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                        },
                url: '/api/ordendecompra/update/index.php',
                success: function(data) {
                    if (data.success == false) {
                        $("#CodeMessage").html("");
                        $("#btnSave").prop('disabled', false);
                    }else{
                        $("#CodeMessage").html(" Numero de Folio ya existe");
                        $("#btnSave").prop('disabled', true);
                    }
                }

            });

        }else{
            $("#CodeMessage").html("Por favor, ingresar una Numero de Folio válida");
            $("#btnSave").prop('disabled', true);
        }
    });

    $("#txtCriterio").keyup(function(event){
        if(event.keyCode == 13){
            $("#buscarA").click();
        }
    });

    $(document).ready(function(){
        $("div.ui-jqgrid-bdiv").css("max-height",$("#list").height()-80);
        $(function() {
            $('.chosen-select').chosen();
            $('.chosen-select-deselect').chosen({ allow_single_deselect: true });
        });

        $("#Almcen").on("change", function(e){
            fillSelectArti();
            document.getElementById('CantPiezas').value = '';
            document.getElementById('CantPiezasPeso').value = '';
            document.getElementById('precioUnitario').value = '';
            document.getElementById('importeTotal').value = '';
            document.getElementById('peso').value = '';
            document.getElementById('pesohidden').value = 0;

        });
    });

    $(function($) {
        var grid_selector_detalles = "#grid-table_detalles";
        var pager_selector_detalles = "#grid-pager_detalles";

        //resize to fit page size
        $(window).on('resize.jqGrid', function () {
            $(grid_selector_detalles).jqGrid( 'setGridWidth', $(".page-content").width() - 60 );
        })
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector_detalles).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) {
            if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $(grid_selector_detalles).jqGrid( 'setGridWidth', parent_column.width() );
                }, 0);
            }
        })

        $(grid_selector_detalles).jqGrid({
            datatype: "local",
            mtype: 'GET',
            shrinkToFit: false,
            autowidth: true,
            height:'auto',
            mtype: 'GET',
            colNames:['Clave','Descripción', 'Descuento Mínimo', 'Descuento Máximo', 'Descuento Min %', 'Descuento Max %'],
            colModel:[
                {name:'clave',index:'clave',width: 100, editable:false, sortable:false},
                {name:'descripcion',index:'descripcion',width:300, editable:false, sortable:false},
                {name:'preciomin',index:'preciomin',width:150, editable:false, sortable:false, align: 'right'},
                {name:'preciomax',index:'preciomax',width:150, editable:false, sortable:false, align: 'right'},
                {name:'comisionporc',index:'comisionporc',width:150, editable:false, sortable:false, align: 'right'},
                {name:'descuentomax',index:'descuentomax',width:150, editable:false, sortable:false, align: 'right'}
            ],
            rowNum:10,
            rowList:[10,20,30],
            pager: pager_selector_detalles,
            viewrecords: true
        });

        // Setup buttons
        $(grid_selector_detalles).jqGrid('navGrid', '#grid-pager',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );

        $(window).triggerHandler('resize.jqGrid');
        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector_detalles).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });


        var grid_selector_detalles2 = "#grid-table_detalles2";
        var pager_selector_detalles2 = "#grid-pager_detalles2";


        //resize to fit page size
        $(window).on('resize.jqGrid', function () {
            $(grid_selector_detalles2).jqGrid( 'setGridWidth', $(".page-content").width() - 60 );
        })
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector_detalles2).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) {
            if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $(grid_selector_detalles2).jqGrid( 'setGridWidth', parent_column.width() );
                }, 0);
            }
        })

        $(grid_selector_detalles2).jqGrid({
            datatype: "local",
            mtype: 'GET',
            shrinkToFit: false,
            autowidth: true,
            height:'auto',
            mtype: 'GET',
            colNames:['Acciones', 'Clave Cliente', 'ID Destinatario', 'Destinatario', 'Dirección', 'Colonia', 'Postal', 'Ciudad', 'Estado', 'Teléfono'],
            colModel:[
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat},
                {name:'clave_cliente',index:'clave_cliente',width: 100, editable:false, sortable:false, align: 'left'},
                {name:'id_destinatario',index:'id_destinatario',width:120, editable:false, sortable:false, align: 'right'},
                {name:'destinatario',index:'destinatario',width:250, editable:false, sortable:false, align: 'left'},
                {name:'direccion',index:'direccion',width:250, editable:false, sortable:false, align: 'left'},
                {name:'colonia',index:'colonia',width:150, editable:false, sortable:false, align: 'left'},
                {name:'postal',index:'postal',width:100, editable:false, sortable:false, align: 'left'},
                {name:'ciudad',index:'ciudad',width:150, editable:false, sortable:false, align: 'left'},
                {name:'estado',index:'estado',width:150, editable:false, sortable:false, align: 'left'},
                {name:'telefono',index:'telefono',width:100, editable:false, sortable:false, align: 'right'}
            ],
            rowNum:10,
            rowList:[10,20,30],
            pager: pager_selector_detalles2,
            viewrecords: true
        });

        // Setup buttons
        $(grid_selector_detalles2).jqGrid('navGrid', '#grid-pager',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );

        $(window).triggerHandler('resize.jqGrid');

        function imageFormat( cellvalue, options, rowObject ){

            var id     = rowObject[2];
            var correl = options.rowId;
            //console.log(id);
            var html = '<a href="#" onclick="eliminar(\''+id+'\', \''+correl+'\')"><i class="fa fa-eraser" title="Eliminar Destinatario"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
          
            return html;
        }
        $("#grid-pager_detalles2").css("width", "auto");

    });

    function ver(id, nombre){
        $("#id_lista").val(id);
        $("#ver_detalles #num_entrada").text(nombre);
        $("#ver_detalles").modal("show");
        loadDetalles(id);
    }
    function loadDetalles(id) {
        $('#grid-table_detalles').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                id: id,
                action: 'getDetallesFolio'
            }, datatype: 'json', page : 1, mtype: 'GET', url:'/api/listadescuentos/lista/index.php'})
            .trigger('reloadGrid',[{current:true}]);


        $('#grid-table_detalles2').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                id: id,
                action: 'getListadoClientes'
            }, datatype: 'json', page : 1, mtype: 'GET', url:'/api/listadescuentos/lista/index.php'})
            .trigger('reloadGrid',[{current:true}]);

    }

    function AgregarCliente(id, nombre)
    {
        $("#id_lista").val(id);
        $("#agregar_cliente #nombre_lista_asignar").text(nombre);
        $("#agregar_cliente").modal("show");
        //loadDetalles(id);
    }

    function loadArticulos() {
        $('#grid-table_articulos').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                almacen: document.getElementById("Almcen").value,
                search: document.getElementById("searchArticle").value
            }, datatype: 'json', page : 1, mtype: 'GET', url: '/api/articulos/lista/index.php'})
            .trigger('reloadGrid',[{current:true}]);
    }

    function eliminar(id_destinatario, idrow)
    {
          swal({
              title: "¿Eliminar Cliente?",
              text: "",
              type: "warning",

              showCancelButton: true,
              cancelButtonText: "No",
              cancelButtonColor: "#14960a",

              confirmButtonColor: "#55b9dd",
              confirmButtonText: "Si",
              closeOnConfirm: true
          },
          function(e) {
              if (e == true) 
              {
                  //Si
                  console.log("SI Eliminar");

                  $.ajax({
                    url: '/api/listadescuentos/update/index.php',
                    data: {
                        almacen:$('#almacen').val(),
                        id_lista:  $("#id_lista").val(),
                        id_destinatario: id_destinatario,
                        action: 'eliminar_destinatario'
                    },
                    type: 'POST',
                    dataType: 'json'
                  })
                  .done(function(data) 
                  {
                      $("#grid-table_detalles2").jqGrid('delRowData', idrow);
                      ReloadGrid();
                  });

              } else {
                  //No
                  console.log("NO Eliminar");
                  /*
                  */
              }
          });
    }

    function fillSelectArti()
    {  
      var almacen = document.getElementById("Almcen").value;
      $.ajax({
        url: "/api/articulos/lista/index.php",
        type: "GET",
        data: {
          almacen: almacen
        },
        beforeSend: function(x){if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
        },
        success: function(res){
          console.log(res);
          fillSelect(res.arr);
            var ids = $("#grid-tabla").jqGrid('getDataIDs');
            //console.log("ids= ", ids);
            for (var i = 0; i < ids.length; i++)
            {
                var rowId = ids[i];
                var rowData = $('#grid-tabla').jqGrid ('getRowData', rowId);

                //console.log("rowData = ", rowData);
                console.log("rowData.codigo = ", rowData.codigo);
                
                $("#basic option[value='"+rowData.codigo+"']").hide();
            }
            $('.chosen-select').trigger("chosen:updated");
        },
        error : function(res){
          window.console.log(res);
        },
        cache: false
      });

      function fillSelect(node)
      {
          console.log("node = ", node);
        var option = "<option value =''>Seleccione un Articulo </option>";//("+node.length+")
        if(node.length > 0)
        {
          for(var i = 0; i < node.length; i++)
          {
            option += "<option value = "+htmlEntities(node[i].cve_articulo)+" data-id="+htmlEntities(node[i].id)+">"+""+htmlEntities(node[i].cve_articulo)+" - "+htmlEntities(node[i].des_articulo)+"</option>";
          }
        }
        basic.innerHTML = option;
        $(basic).trigger("chosen:updated");
      }
    }

    function validateNumber(e){

        var key = window.event ? e.which : e.keyCode;

        if (key < 48 || key > 57) {
            e.preventDefault();
        }
    }
  
  
    function calcularPresupuesto(){
      $.ajax({
          type: "POST",
          dataType: "json",
          data: 
          {
              presupuesto: $("#presupuestoSelect").val(),
              action : "cargarMonto"
          },
          beforeSend: function(x)
          { 
              if(x && x.overrideMimeType) 
              { 
                x.overrideMimeType("application/json;charset=UTF-8"); 
              }
          },
          url: '/api/ordendecompra/update/index.php',
          success: function(data) 
          {
              console.log(data.monto);
              if (data.success == true)
              {
                  if($('#presupuestoSelect').val()!="")
                  {
                      $('#valorDePresupuesto').val("$"+" "+(data.monto)); 
                      $('#importeTotalPresupuesto').val("$"+" "+(data.importeTotal));
                      $('#remanenteDePresupuesto').val("$"+" "+((data.monto)-(data.importeTotal)).toFixed(2));
                  }
                  else
                  {
                      swal("Error", "Selecione un Presupuesto", "error");
                  }
              }
          }
      });

      ReloadGrid();
      /*
      var valor=2;
      var valor2=1;
      if($('#presupuestoSelect').val()!="")
      {
          //$('#valorDePresupuesto').val(valor) 
          //$('#importeTotalPresupuesto').val(valor2)  
          //$('#remanenteDePresupuesto').val(valor-valor2)
      }*/
    }
  
    function exportarPDF($folio) 
    {
      console.log($folio);
      
              var folio = $folio,
                  form = document.createElement('form'),
                  input_nofooter = document.createElement('input'),
                  input_folio = document.createElement('input'),
                  input_tipo = document.createElement('input');
      
              form.setAttribute('method', 'post');
              form.setAttribute('action', '/entradas/pdf/exportar');
              form.setAttribute('target', '_blank');

              input_nofooter.setAttribute('name', 'nofooternoheader');
              input_nofooter.setAttribute('value', '1');

              input_folio.setAttribute('name', 'folio');
              input_folio.setAttribute('value', folio);
              input_tipo.setAttribute('name', 'tipo');
              input_tipo.setAttribute('value', 1);

              form.appendChild(input_nofooter);
              form.appendChild(input_folio);
              form.appendChild(input_tipo);

              document.getElementsByTagName('body')[0].appendChild(form);
              form.submit();
    }
  
  
    /////////////////////////////////////////Mostrar Modal Importar OC/////////////////////////////////////////////////////
    $("#importExcelOC").on("click", function(){
        console.log("mostrar modal de importador");
        $moda200 = $("#modalImportOC");
        $moda200.modal('show');

    });
    ///////////////////////////////////////////////Funcion de Importar///////////////////////////////////////////////
    $('#btn-importOC').on('click', function(){

        $('#btn-importOC').prop('disable', true);
        var bar = $('.progress-bar');
        var percent = $('.percent');
        //var status = $('#status');

        var formData = new FormData();
        formData.append("clave", "valor");

        $.ajax({
            // Your server script to process the upload
            url: '/ordenescompra/importar'+$("#tipo").val(),
            type: 'POST',

            // Form data
            data: new FormData($('#form-import')[0]),

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
                setTimeout(
                    function(){if (data.status == 200) {
                        swal("Exito", data.statusText, "success");
                        $('#importar').modal('hide');
                        ReloadGrid();
                    }
                    else {
                        swal("Error", data.statusText, "error");
                    }
                },1000)
            },
        });
    });
  
    ///////////////////////////////////////////////Funcion Descargar Layout///////////////////////////////////////////////
    $('#btn-layoutOC').on('click', function(e) {
      e.preventDefault();  //stop the browser from following
      window.location.href = 'http://www.tinegocios.com/proyectos/wms/LAYOUT_OC_01.xlsx';//cambiar href por nuevo layout
    }); 
  
      
</script>
