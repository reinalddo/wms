<?php
    $listaZR = new \CortinaEntrada\CortinaEntrada();
    $listaProvee = new \Proveedores\Proveedores();
    $listaAlma = new \Almacen\Almacen();
    $listaUser = new \Usuarios\Usuarios();
    $listaOC = new \OrdenCompra\OrdenCompra();
    $listaAP = new \AlmacenP\AlmacenP();
    $listaLotes = new \Lotes\Lotes();
    $listaProto = new \Protocolos\Protocolos();
    $listaMotivos = new \MotivoDevolucion\MotivoDevolucion();
    $rutasVP = new \Ruta\Ruta();
    $listaArticulos = new \Articulos\Articulos();

$cve_proveedor = "";
if(isset($_SESSION['id_proveedor']))
{
    $cve_proveedor = $_SESSION['id_proveedor'];
}

?>
<input type="hidden" id="cve_proveedor" value="<?php echo $cve_proveedor; ?>">

<input type="hidden" id="id_entrada" value="">
<input type="hidden" id="id_descarga" value="">
<input type="hidden" id="tiene_lote" value="">
<input type="hidden" id="tiene_caducidad" value="">
<input type="hidden" id="tiene_serie" value="">
<input type="hidden" id="cambiar_lote_val" value="">
<input type="hidden" id="articulo_cambiar" value="">
<input type="hidden" id="clave_zona" value="">
<input type="hidden" id="id_proveedor" value="">

<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-css/1.4.6/select2-bootstrap.min.css">
<link href="/css/plugins/clockpicker/clockpicker.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">

<input type="hidden" id="num_oc" value="<?php echo $listaOC->getMax_RL()->id + 1;?>">
<input type="hidden" id="ucaja_val" value="">
<input type="hidden" id="c_peso_val" value="">
<input type="hidden" id="cant_max" value="">

<input type="hidden" id="arr_check_cambiar_lotes" value="">
<input type="hidden" id="arr_check_cambiar_lotes_id_descarga" value="">


<div class="modal fade" id="cambiar_lote_modal" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Cambiar Lote|Serie</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label>Lote|Serie <span id="cve_articulo_cambio_lote_serie"></span></label>
                            <br><br>
                          <div class="col-xs-12" style="padding: 0;">
                            <div class="checkbox" id="chb_derivado">
                              <label for="btn-productoDerivado">
                                <input type="checkbox" name="productoDerivado" id="btn-productoDerivado"><b>Conversión a Producto Derivado</b>
                              </label>
                              <br>
                              <select class="form-control chosen-select" id="articulos_conversion" style="display: none;">
                                  <option value="">Seleccione Artículo</option>
                                  <?php 
                                  foreach($listaArticulos->getAllArticulosQ() as $a)
                                  {
                                  ?>
                                    <option value="<?php echo $a->cve_articulo; ?>"><?php echo " ( ".$a->cve_articulo." ) - ".$a->des_articulo; ?></option>
                                  <?php 
                                  }
                                  ?>
                              </select>
                              <br><br>
                              <label for="cantidad_derivado" id="cant_derivado" style="padding: 0;display: none;">
                                    <b>Cantidad: </b>
                                  <input type="number" class="form-control" name="cantidad_derivado" id="cantidad_derivado" min="0" value="0">
                              </label>
                            </div>
                          </div>
                            <br><br>
                            <label>Nuevo Lote|Serie</label>
                            <input type="text" class="form-control" name="nuevo_lote_serie" id="nuevo_lote_serie" placeholder="Nuevo Lote|Serie">
                            <span id="msj_lote_existente" style="color: red; font-weight: bolder;display: none;">Lote|Serie ya existente</span>
                            <br><br>
                            <div class="cambiar_caducidad_v" style="display: none;">
                            <label>Caducidad</label>
                            <input type="date" class="form-control" name="cambiar_caducidad_varios" id="cambiar_caducidad_varios" min="<?php echo $fecha_actual; ?>" value="">
                            <span style="font-weight: bolder;">El cambio de caducidad sólo aplicará a los artículos que tengan definida la respectiva Bandera</span>
                            <br><br>
                            </div>

                            <div id="check_cambiar_lotes">
                            <label>Lote|Serie Existente</label>
                            <select class="form-control chosen-select" name="select_lotes" id="select_lotes">
                                <option value="">Seleccione Lote</option>
                            </select>

                            <br><br>

                            <label>Caducidad</label>
                            <input type="date" class="form-control" name="cambiar_caducidad" id="cambiar_caducidad" min="<?php echo $fecha_actual; ?>" value="">
                            <br><br>
                            <select class="form-control chosen-select" name="select_serie" id="select_serie">
                                <option value="">Seleccione Serie</option>
                            </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            <button type="button" id="guardar_lote_serie_cambio" class="btn btn-sm btn-primary">Cambiar Lote|Serie</button>
            <button type="button" id="guardar_lote_serie_varios" class="btn btn-sm btn-primary permiso_registrar">Cambiar Lotes | Producto Derivado</button>
                </div>
          </div>
        </div>
    </div>
</div>

<div id="app_recibir_articulo">
    <div class="wrapper wrapper-content  animated fadeInRight" id="FORM">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <div class="row">
                            <div class="col-lg-4" id="_title">
                                <h3>Devolución de Clientes | Rutas</h3>
                            </div>
                        </div>
                    </div>
                    <form id="myform">
                        <div class="ibox-content">
                            <div class="row">
                                <label>Tipo </label>
                                <div class="col-lg-12 text-center">
                                    <div class="form-group">
                                        <label class="col-md-4" for="checkboxes-0">
                                            <label for="tipo_entrada1">
                                            <input type="radio" name="tipo_entrada" checked id="tipo_entrada1" value="1" onclick="">
                                            Pedido</label>
                                        </label>
                                        <label class="col-md-4" for="checkboxes-1" style="display: none;">
                                            <input type="radio" name="tipo_entrada" id="tipo_entrada2" value="2" onclick="traer_articulos();">
                                            <label>Devolución Libre</label>
                                        </label>
                                        <label class="col-md-4" for="checkboxes-3" >
                                            <label for="tipo_entrada3">
                                            <input type="radio" name="tipo_entrada" id="tipo_entrada3" value="3">
                                            Inventario en Ruta | SOBRANTE</label>
                                        </label>
                                        <label class="col-md-4" for="checkboxes-2" style="display: none;">
                                            <input type="radio" v-model="tipo_entrada" v-bind:value="4">
                                            <label>Cross Docking</label>
                                        </label>
                                    </div><br>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6 b-r b-b">
                                    <div class="form-group" id="data_2" style="display:none">
                                        <label>Fecha de Recepción</label>
                                        <input disabled id="fechaentrada" type="text" class="form-control" value="">
                                        <input id="fechainicio" type="text" class="form-control" value="" style="display: none">
                                    </div>
                                  
                                    <div class="form-group">
                                        <label>Almacén</label>
                                        <select class="form-control" id="almacen_select">
                                            <option value="">Seleccione</option>
                                            <?php foreach( $listaAP->getAll() AS $a ): ?>
                                                <option value="<?php echo $a->clave; ?>"><?php echo "( ".$a->clave." ) "; echo $a->nombre; ?></option>
                                            <?php endforeach; ?>

                                        </select>
                                    </div>
                                    <div class="form-group tipo_cliente">
                                        <label>Número de Pedido*</label>
                                        <select class="chosen-list form-control" id="pedidos_select">
                                            <option value="" >Seleccione un Pedido</option>
                                        </select>
                                    </div>
                                    <div class="form-group tipo_cliente">
                                        <label>Folio de Devolución</label> 
                                        <input  type="text" id="consecutivo_dev"  placeholder="Folio de Devolución" class="form-control" disabled>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group col-lg-6" style="display: none">
                                        <label>Hora Inicio</label>
                                        <div class="input-group clockpicker" disabled data-autoclose="true">
                                            <input type="text" disabled id="hora" class="form-control">
                                            <span class="input-group-addon" style="display: none;">
                                            <span class="fa fa-clock-o"></span>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group col-lg-6" style="display: none">
                                        <label>Hora Fin</label>
                                        <div class="input-group clockpicker" disabled data-autoclose="true">
                                            <input type="text" disabled id="horafin" class="form-control">
                                            <span class="input-group-addon" style="display: none;">
                                            <span class="fa fa-clock-o"></span>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6 tipo_cliente">
                                        <label>Cliente</label>
                                        <select class="form-control" id="clientes_pedidos">
                                            <option value="">Seleccione</option>
                                        </select>
                                        <div style="display: none;">
                                            <select class="form-control">
                                                <option>Proveedores</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6 tipo_cliente">
                                        <label>Factura / Remisión</label> 
                                        <!--<input id="facprove" type="text" placeholder="Factura" disabled class="form-control" >-->
                                        <select id="facprove" class="form-control">
                                            <option>Factura</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6" id="rutas_stock" style="display:none">
                                        <label>Ruta</label>
                                        <select class="form-control" id="rutas_select">
                                            <option value="">Seleccione</option>
                                            <?php /* foreach( $rutasVP->getAll($_SESSION["cve_almacen"], 1) AS $a ): ?>
                                                <option value="<?php echo $a->ID_Ruta; ?>"><?php echo "( ".$a->cve_ruta." ) "; echo $a->descripcion; ?></option>
                                            <?php endforeach; */ ?>

                                        </select>
                                    </div>


                                    <div class="form-group col-md-6" id="protocolof" style="display:none">
                                        <label>Protocolo</label>
                                        <select class="form-control" id="protocolo">
                                            <option value="">Seleccione</option>
                                            <?php foreach( $listaProto->getAll() AS $a ): ?>
                                                <option value="<?php echo $a->FOLIO; ?>"><?php echo $a->descripcion; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" id="protocolooc" />
                                    </div>


                                    <div class="form-group col-md-6" id="cprotocolof" style="display:none">
                                        <label>Consec Protocolo</label>
                                        <input id="cprotocolo" type="text" disabled placeholder="Consec Protocolo" class="form-control">
                                        <input type="hidden" id="cprotocolooc" />
                                    </div>


                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6 b-t">
                                    <?php 
                                    
                                    ?>
                                    <div class="form-group tipo_cliente">
                                        <label>Zona de Recepción*</label>
                                       <select class="form-control" id="zona_recepcion">
                                            <option value="">Seleccione una Zona de Recepcion</option>
                                        </select>
                                    </div>
                                    <?php 
                                    /*
                                    ?>
                                    <div class="form-group">
                                        <label>Zona de Recepción*</label>
                                        <select class="form-control" id="zona_recepcion_select">
                                            <option value="" >Seleccione una Zona de Recepcion</option>
                                        </select>
                                    </div>
                                    <?php 
                                    */
                                    ?>
                                    <div class="form-group tipo_cliente">
                                        <label>Pallet/Contenedor</label>
                                        <select class="form-control" id="pallet_contenedor">
                                            <option value="">Seleccione un Pallet/Contenedor</option>
                                        </select>
                                    </div>
                                    <div class="form-group tipo_cliente">
                                        <label>Articulo*</label>
                                        <select id="articulos_lista" class="form-control">
                                            <option value="" >Seleccione un Artículo</option>
                                        </select>
                                    </div>

                                    <div class="form-group tipo_cliente" id="cantidadRecibidaOC">
                                        <label>Cantidad a Recibir* MAX(<span></span>)</label>
                                        <input id="a_recibir" type="number" placeholder="Cantidad a Recibir" class="form-control" >
                                    </div>

                                    <div class="form-group tipo_cliente">
                                        <label>Motivo de Devolución</label>
                                        <select class="form-control" id="motivo_dev">
                                            <option value="">Seleccione Motivo</option>
                                            <?php foreach( $listaMotivos->getMotivos($_SESSION['id_almacen']) AS $a ): ?>
                                                <option value="<?php echo $a->MOT_ID; ?>"><?php echo "( ".$a->Clave_motivo." ) "; echo $a->MOT_DESC; ?></option>
                                            <?php endforeach; ?>

                                        </select>
                                    </div>

                                    <?php 
                                    /*
                                    ?>
                                    <div class="form-group" id="proveedor_devolucion">
                                        <label>Proveedor*</label>
                                        <select id="proveedor_dev" class="form-control">
                                            <option value="" >Seleccione un Proveedor</option>
                                        </select>
                                    </div>
                                    <?php  
                                    */
                                    ?>

                                    <div v-if="articulo.maneja_lotes == 1" class="form-group" id="nlotef" style="display: none;">
                                        <label>Lote*</label> 
                                        <input type="text" placeholder="Lote" class="form-control" disabled> 
                                    </div>
                                    <div v-if="articulo.maneja_series == 1" class="form-group" id="nserief" style="display: none;">
                                        <label>Numero de Serie</label> 
                                        <div id="series">
                                            <input type="text" placeholder="Numero de Serie" class="form-control" disabled>
                                        </div>
                                    </div>
                                    <?php 
                                    
                                    ?>

                                    <div class="form-group" id="cantidadRecibidaLibre" style="display:none">
                                        <label>Cantidad Devuelta</label> 
                                        <input :disabled="articulo.cve_articulo == ''"  type="text" placeholder="Cantidad Devuelta" class="form-control">
                                    </div>
                                    <input id="xpieza" type="hidden" placeholder="Cantidad Devuelta" class="form-control">
                                    <input id="xcaja" type="hidden" placeholder="Cantidad Devuelta" class="form-control">
                                    <input id="xpallet" type="hidden" placeholder="Cantidad Devuelta" class="form-control">
                                    <input id="xkg" type="hidden" placeholder="Cantidad Devuelta" class="form-control">
                                </div>
                                <div class="col-lg-6 b-t">
                                    <div class="form-group">
                                       <label>Usuario</label>
                                       <input class="form-control" type="text" id="user_dev" value="" disabled>
                                    </div>
                                     <div class="form-group tipo_cliente">
                                        <label>Unidad de Medida</label>
                                        <select class="form-control" id="umed">
                                        <option value="" >Seleccion Medida</option>
                                    </select>
                                    </div>
                                     <div class="form-group tipo_cliente">
                                        <label>Cantidad Devuelta (Piezas)</label>
                                        <input id="cantidad_recibida" type="text" placeholder="Cantidad Piezas" class="form-control" readonly="">
                                    </div>
                                    <div class="form-group tipo_cliente" id="costoUnitario">
                                        <label>Costo Unitario ($)</label>
                                        <input oninput="parseFloat($(this).val()).toFixed(2)"  type="number" disabled placeholder="Costo Unitario" class="form-control">
                                    </div>


                                    <div class="form-group" id="data_1" style="display: none;">
                                        <label>Caducidad*</label>
                                        <div class="input-group date">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="caducidad" type="date" class="form-control" disabled>
                                        </div>
                                    </div>

                                    <div class="form-group tipo_cliente" id="costoEnOC" >
                                        <label>Costo Total ($)</label> 
                                        <input class="form-control" type="text" disabled>
                                    </div>

                                </div>
                                <div class="col-lg-12">
                                    <div class="row">
                                        <div class="col-md-6 tipo_cliente">
                                            <center>
                                                <div id="recibir_articulo">
                                                    <div style="display: inline-flex; white-space: nowrap;width: 250px;">
                                                    <input type="checkbox" name="articulo_defectuoso" id="articulo_defectuoso" class="form-control" value="0" style="display: inline-block; width: 20px;">
                                                    <span style="display: inline-block;margin: 12px;font-weight: bold;">Marcar como Dañado</span>
                                                    </div>
                                                    <br>
                                                    <button type="button" class="btn btn-success t-10 permiso_registrar" id="btn-recibir">Recibir artículo</button>
                                                </div>
                                            </center>
                                        </div>
                                        <div class="col-md-6" style="display: none;">
                                            <center>
                                                <button type="button" class="btn btn-success" v-on:click="recibir_todo()">Recibir toda la OC</button>
                                            </center>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-lg-12" id="botonesLibre" style="display:none">
                                    <center>
                                        <button type="button" class="btn btn-primary" id="addrowLibre">Agregar Producto</button>
                                        <button type="button" class="btn btn-success" onclick="receiveOC()" id="botonRecibirLibre">Recibir Entrada Libre</button>
                                    </center>
                                </div>
                            </div>
                            <div class="row tipo_cliente">
                                <label id="label-table-1">Productos Devueltos</label>
                                <div class="col-lg-12 table-responsive"><br>    
                                    <table class="table table-bordered ">
                                        <thead>
                                            <tr>
                                                <td><label>Clave</label></td>
                                                <td><label>Decripción</label></td>
                                                <td><label>Proveedor</label></td>
                                                <td><label>Serie</label></td>
                                                <td><label>Lote</label></td>
                                                <td><label>Caducidad</label></td>
                                                <td><label>Cantidad (Pz)</label></td>
                                                <td><label>Pallet/Contenedor</label></td>
                                                <td><label>LP</label></td>
                                                <td><label>Zona de Recepción</label></td>
                                                <td><label>Usuario</label></td>
                                                <td><label>Costo</label></td>
                                                <td><label>Dañado</label></td>
                                                <td><label>Hora de recepcion</label></td>
                                                <td><label>Acciones</label></td>
                                            </tr>
                                        </thead>
                                        <tbody id="articulos_recibidos">
<?php 
/*
?>
                                            <tr>
                                                <td>{{art.contenedor}}</td>
                                                <td>{{art.pallet}}</td>
                                                <td>{{art.cve_articulo}}</td>
                                                <td>{{art.descripcion}}</td>
                                                <td>{{art.serie}}</td>
                                                <td>{{art.lote}}</td>
                                                <td>{{art.caducidad}}</td>
                                                <td>{{art.peso}}</td>
                                                <td>{{art.cantidad_por_recibir}}</td>
                                                <td>{{art.zona_recepcion}}</td>
                                                <td>{{orden.clave_usuario}}</td>
                                                <td>{{art.costo_total}}</td>
                                                <td>{{art.hora_de_recepcion}}</td>
                                                <td><a href="#" v-on:click="borrar_articulo_recibido(art.cve_articulo)"><i class="fa fa-eraser" alt="Borrar"></i></a></td>
                                            </tr>
<?php 
*/
?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="row tipo_ruta" style="display: none;">
                                <div class="form-control" style="display: table;">

                                      <div class="col-xs-2">
                                        <div class="checkbox" id="chb_asignar">
                                          <label for="btn-asignarTodo">
                                            <input type="checkbox" name="asignarTodo" id="btn-asignarTodo"><b>Cambiar Todos los Lotes</b>
                                          </label>
                                        </div>
                                      </div>

                                    <div class="col-xs-2">
                                    <label for="fecha_ini"> Fecha Inicio:
                                    <input type="date" class="form-control" name="fecha_ini" id="fecha_ini">
                                    </label>
                                    </div>

                                    <div class="col-xs-2">
                                    <label for="fecha_fin"> Fecha Fin:
                                    <input type="date" class="form-control" name="fecha_fin" id="fecha_fin">
                                    </label>
                                    </div>

                                    <div class="col-xs-2">
                                    <label for="fecha_fin"> Dia Operativo:
                                    <select class="form-control" name="diao" id="diao"></select>
                                    </label>
                                    </div>

                                </div>
                                    <div class="ibox-content">
                                        <div class="jqGrid_wrapper">
                                            <table id="grid-table"></table>
                                            <div id="grid-pager"></div>
                                        </div>

                                    </div>
                            </div>

                            <?php 
                            /*
                            ?>
                            <div class="row">
                                <label >Productos esperados por la Devolución ({{articulos.length}})</label>
                                <div class="col-lg-12 table-responsive"> 
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <td><label>Clave</label></td>
                                                <td><label>Decripción</label></td>
                                                <td><label>Cantidad</label></td>
                                                <td><label>Costo</label></td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="art in articulos" v-bind:style= "[art.color_1 == 1 ? art.color_2 == 1 ?  {'background':'green','color':'black'} : {'background':'yellow', 'color':'black'} : {'background':'red', 'color':'black'}]">
                                                <td>{{art.cve_articulo}}</td>
                                                <td>{{art.nombre_articulo}}</td>
                                                <td>{{art.cantidad}}</td>
                                                <td>{{art.costo}}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php 
                            */
                            ?>

                            <div class="row">

                                <div class="col-md-3 tipo_ruta" style="display: none;">
                                    <button type="button" class="btn btn-success tipo_ruta" id="cambiar_lotes">Cambiar Lotes | Producto Derivado</button>
                                </div>

                                <div class="col-md-3">
                                    <button type="button" class="btn btn-success tipo_cliente permiso_registrar" id="guardar_devolucion" disabled>Guardar</button>
                            <a href="#" id="generarExcelDetalle" class="btn btn-primary tipo_ruta" style="display: none;">
                                <span class="fa fa-file-excel-o"></span> Reporte de Devolución
                            </a>

                                </div>

                            </div>
                            <?php 
                            /*
                            ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="pull-right">
                                        <a href="#" onclick="cancelar()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                                    </div>
                                </div>
                            </div>
                            <?php 
                            */
                            ?>
                        </div>
                        <input type="hidden" id="hiddenAction">
                        <input type="hidden" id="hiddenID_Aduana">
                        <input type="hidden" id="guardo">
                        <input type="hidden" id="lote_valor">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-form2" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-md">
                <div class="modal-content">
                  <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                      <h4>Nuevo Lote</h4>
                  </div>
                  <div class="modal-body">
                    <div class="row">
                        <div class="form-group" id="lotex">
                           <label>Lote*</label>
                           <select class="form-control" id="lote1"></select>
                        </div>
                        <div class="form-group" id="cadu" >
                            <label>Caducidad*</label>
                            <div class="input-group date">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="caduci" type="text" class="form-control">
                            </div>
                        </div>
                      </div>
                  </div>
              </div>
          </div>
        </div>
    </div>
</div>

<!-- Mainly scripts -->
<script src="/js/moment.js"></script>
<script src="/js/moment-with-locales.js"></script>
<script src="/js/jquery-2.1.1.js"></script>
    <!--
<script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>-->
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

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
<script src="/js/select2.js"></script>

<!-- Data picker -->
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script src="/js/plugins/clockpicker/clockpicker.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="/js/plugins/chosen/chosen.jquery.js"></script>

<!-- Vue Js
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.10/dist/vue.js"></script>-->

<style>
  .t-10{
    margin-top: 10px;
  }
</style>

<script>
    var select_nerp = document.getElementById('select-nerp'),
        select_lote = document.getElementById('lote');
    var productos_recibidos = [];

    function almacenPrede() 
    {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                idUser: '<?php echo $_SESSION["id_user"]?>',
                action: 'search_almacen_pre'
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) 
                {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/almacenPredeterminado/index.php',
            success: function(data) {
                console.log("Predeterminado = ", data);
                if (data.success == true) 
                {
                    console.log("data.codigo.clave = ",data.codigo.clave);
                    $("#almacen_select").val(data.codigo.clave);
                    searchERP(data.codigo.clave);
                }
            },
            error: function(res) {
                window.console.log(res);
            }
        });
    }
    almacenPrede();


    $("#almacen_select").change(function(){
        searchERP($(this).val());
    });

    function searchERP(almacen_clave)
    {
        console.log("ALMACEN pedidos = ", almacen_clave);
         $.ajax({
            type: "POST",
            dataType: "json",
            cache: false,
            data: {
                almacen: almacen_clave,
                id_user: <?php echo $_SESSION['id_user']; ?>,
                tipo_pedido: $('input:radio[name=tipo_entrada]:checked').val(),
                cve_proveedor: $("#cve_proveedor").val(),
                action: 'pedidos'
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) 
                {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/devclientes/update/index.php',
            success: function(data) {
                console.log("data fillSelect = ", data);
                //console.log("data Consecutivo = ", data.Consecutivo[0].Consecutivo);
                $("#consecutivo_dev").val(data.Consecutivo[0].Consecutivo);
                $("#user_dev").val("("+data.res_usuario[0].cve_usuario+") "+data.res_usuario[0].nombre_completo);
               fillSelect(data.res, data.res_clientes, data.res_factura, data.res_recepcion, data.res_pallets, data.res_unidad_medida, data.res_rutas);
            },
            error: function(res) {
                window.console.log(res);
            }
        });
    }
        function fillSelect(node, node_clientes, node_factura, node_recepcion, node_pallets, node_unidad_medida, node_rutas)
        {
            console.log("node fillSelect = ", node);

            $("#facprove").empty();
            $("#pedidos_select").empty();
            $("#clientes_pedidos").empty();
            $("#zona_recepcion").empty();
            $("#pallet_contenedor").empty();
            $("#rutas_select").empty();
            $("#umed").empty();

            var option = "<option value =''>Pedidos...</option>", 
                option_clientes  = "<option value =''>Cliente...</option>", 
                option_recepcion = "<option value =''>Zona Recepción...</option>", 
                option_pallets = "<option value ='' data-cvelp=''>Pallet/Contenedor...</option>", 
                option_umed = "<option value =''>Unidad de Medida...</option>", 
                option_rutas = "<option value =''>Rutas...</option>", 
                option_factura   = "<option value =''>Factura...</option>";

            if($('input:radio[name=tipo_entrada]:checked').val() != 3) //tipo ruta
            {
                if(node.length > 0)
                {
                    for(var i = 0; i < node.length; i++)
                    {
                        option += "<option value = '"+node[i].Fol_folio+"'>"+node[i].Fol_folio+"</option>";
                    }
                }
            }
            //clientes_pedidos
            $("#pedidos_select").append(option);
            $("#pedidos_select").trigger("chosen:updated");
            if(node_clientes.length > 0)
            {
                for(var i = 0; i < node_clientes.length; i++)
                {
                    option_clientes += "<option value = '"+node_clientes[i].Cve_clte+"'>("+node_clientes[i].Cve_clte+") - "+node_clientes[i].RazonSocial+"</option>";
                }
            }
            //clientes_pedidos
            $("#clientes_pedidos").append(option_clientes);
            $("#clientes_pedidos").trigger("chosen:updated");


            if(node_factura.length > 0)
            {
                for(var i = 0; i < node_factura.length; i++)
                {
                    if(node_factura[i].Factura)
                       option_factura += "<option value = '"+node_factura[i].Factura+"'>"+node_factura[i].Factura+"</option>";
                }
            }
            //clientes_pedidos
            $("#facprove").append(option_factura);
            $("#facprove").trigger("chosen:updated");


            if(node_recepcion.length > 0)
            {
                for(var i = 0; i < node_recepcion.length; i++)
                {
                    option_recepcion += "<option value = '"+node_recepcion[i].cve_ubicacion+"'>("+node_recepcion[i].cve_ubicacion+") - "+node_recepcion[i].desc_ubicacion+"</option>";
                }
            }
            //clientes_pedidos
            $("#zona_recepcion").append(option_recepcion);
            $("#zona_recepcion").trigger("chosen:updated");

            if(node_pallets.length > 0)
            {
                for(var i = 0; i < node_pallets.length; i++)
                {
                    option_pallets += "<option value = '"+node_pallets[i].clave_contenedor+"' data-cvelp='"+node_pallets[i].CveLP+"'>("+node_pallets[i].clave_contenedor+") - "+node_pallets[i].descripcion+"</option>";
                }
            }
            //clientes_pedidos
            $("#pallet_contenedor").append(option_pallets);
            $("#pallet_contenedor").trigger("chosen:updated");

            if(node_unidad_medida.length > 0)
            {
                for(var i = 0; i < node_unidad_medida.length; i++)
                {
                    option_umed += "<option value = '"+node_unidad_medida[i].des_umed+"'>("+node_unidad_medida[i].cve_umed+") - "+node_unidad_medida[i].des_umed+"</option>";
                }
            }
            //clientes_pedidos
            $("#umed").append(option_umed);
            $("#umed").trigger("chosen:updated");

            if(node_rutas.length > 0)
            {
                for(var i = 0; i < node_rutas.length; i++)
                {
                    option_rutas += "<option value = '"+node_rutas[i].ID_Ruta+"'>("+node_rutas[i].cve_ruta+") - "+node_rutas[i].descripcion+"</option>";
                }
            }
            //clientes_pedidos
            $("#rutas_select").append(option_rutas);
            $("#rutas_select").trigger("chosen:updated");

        } 

        $("#pedidos_select").change(function()
        {
            //$("#diao").empty();
            console.log("tipo_entrada = ", $('input:radio[name=tipo_entrada]:checked').val());
             $.ajax({
                type: "POST",
                dataType: "json",
                cache: false,
                data: {
                    pedido: $(this).val(),
                    cve_proveedor: $("#cve_proveedor").val(),
                    tipo_pedido: $('input:radio[name=tipo_entrada]:checked').val(),
                    id_ruta: $("#rutas_select").val(),
                    action: 'pedidos_cliente'
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) 
                    {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/devclientes/update/index.php',
                success: function(data) {

                    if($("input[name=tipo_entrada]:checked").val() == 3)
                        ReloadGrid();
                    else
                    {
                        $("#clientes_pedidos").empty();
                        var option_clientes = "<option value =''>Cliente...</option>";

                        var node_clientes = data.res_clientes;

                        if(node_clientes.length > 0)
                        {
                            for(var i = 0; i < node_clientes.length; i++)
                            {
                                option_clientes += "<option selected value = '"+node_clientes[i].Cve_clte+"'>("+node_clientes[i].Cve_clte+") - "+node_clientes[i].RazonSocial+"</option>";
                            }
                        }

                        $("#clientes_pedidos").append(option_clientes);
                        $("#clientes_pedidos").trigger("chosen:updated");
                        //$("#clientes_pedidos").change();


                        $("#articulos_lista").empty();
                        var option_articulos = "<option value =''>Artículos...</option>";

                        var node_articulos = data.res_articulos;

                        if(node_articulos.length > 0)
                        {
                            for(var i = 0; i < node_articulos.length; i++)
                            {
                                option_articulos += "<option value = '"+node_articulos[i].cve_articulo+"' data-lote='"+node_articulos[i].lote+"' data-serie='"+node_articulos[i].serie+"' data-caducidad='"+node_articulos[i].Caducidad+"' data-cantidad='"+node_articulos[i].Cantidad+"' data-num_multiplo='"+node_articulos[i].num_multiplo+"' data-precio='"+node_articulos[i].precio+"' data-id_proveedor='"+node_articulos[i].id_proveedor+"' data-nombre_proveedor='"+node_articulos[i].nombre_proveedor+"'>("+node_articulos[i].cve_articulo+") - "+node_articulos[i].Descripcion+"</option>";
                            }
                        }

                        $("#articulos_lista").append(option_articulos);
                        $("#articulos_lista").trigger("chosen:updated");
                    }
                },
                error: function(res) {
                    window.console.log(res);
                }
            });

        });

        $("#tipo_entrada1, #tipo_entrada2, #tipo_entrada3").change(function()
        {
            console.log("tipo_entrada = ", $("input[name=tipo_entrada]:checked").val());
            searchERP($("#almacen_select").val());
            $("#articulos_lista").empty();
            var option_articulos = "<option value =''>Artículos...</option>";
            $("#articulos_lista").append(option_articulos);
            productos_recibidos = [];
            listar_articulos_recibidos();
            $("#guardar_devolucion").prop("disabled", true);
            $("#pedidos_select").prop("disabled", false);
            $("#zona_recepcion").prop("disabled", false);
            $("#articulo_defectuoso").prop('checked',false);
            $("#articulo_defectuoso").val("0");

            //$("#proveedor_dev").empty();
            //var option_proveedores = "<option value =''>Seleccione Proveedor...</option>";
            //$("#proveedor_dev").append(option_proveedores);

            if($(this).val() == 1) //tipo pedido
            {
                $("#rutas_stock, .tipo_ruta").hide();
                $(".tipo_cliente").show();
                if($("#permiso_registrar").val() == 1) $("#guardar_devolucion").show();
                else $("#guardar_devolucion").hide();
            }

            if($(this).val() == 3) //tipo ruta
            {
                $("#rutas_stock, .tipo_ruta").show();
                $(".tipo_cliente").hide();
                if($("#permiso_registrar").val() == 1) $("#guardar_lote_serie_varios").show();
                else $("#guardar_lote_serie_varios").hide();

                if($("#permiso_registrar").val() == 1) $("#cambiar_lotes").show();
                else $("#cambiar_lotes").hide();
            }

        });

        function modo_pedidos()
        {
            searchERP($("#almacen_select").val());
            $("#articulos_lista").empty();
            var option_articulos = "<option value =''>Artículos...</option>";
            $("#articulos_lista").append(option_articulos);
            productos_recibidos = [];
            listar_articulos_recibidos();
            $("#guardar_devolucion").prop("disabled", true);
        }

        function traer_articulos()
        {
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/devclientes/update/index.php',
                data: {
                    cliente: $("#clientes_pedidos").val(),
                    action: 'traer_todos_los_articulos'
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    $("#articulos_lista").empty();
                    var option_articulos = "<option value =''>Artículos...</option>";

                    var node_articulos = data.res_articulos;

                    if(node_articulos.length > 0)
                    {
                        for(var i = 0; i < node_articulos.length; i++)
                        {
                            option_articulos += "<option value = '"+node_articulos[i].cve_articulo+"' data-lote='"+node_articulos[i].lote+"' data-serie='"+node_articulos[i].serie+"' data-caducidad='"+node_articulos[i].Caducidad+"' data-cantidad='"+node_articulos[i].Cantidad+"' data-num_multiplo='"+node_articulos[i].num_multiplo+"' data-precio='"+node_articulos[i].precio+"'>("+node_articulos[i].cve_articulo+") - "+node_articulos[i].Descripcion+"</option>";
                        }
                    }

                    $("#articulos_lista").append(option_articulos);
                    $("#articulos_lista").trigger("chosen:updated");
                },
                error: function(data) 
                {
                    window.console.log(data);
                }
            });
        }

        $("#umed").change(function(){

            if($("#umed").val() == 'Caja' && $("#cantidadRecibidaOC span").text())
            {
                $("#cantidadRecibidaOC span").text(parseInt($("#cantidadRecibidaOC span").text()/$("option:selected","#articulos_lista").data("num_multiplo")));
                $("#cantidadRecibidaOC  #a_recibir").val($("#cantidadRecibidaOC span").text());
            }
            else
            {
                $("#cantidadRecibidaOC span").text($("option:selected","#articulos_lista").data("cantidad"));
                $("#cantidadRecibidaOC  #a_recibir").val($("#cantidadRecibidaOC span").text());
            }
        });

        $("#articulos_lista").change(function(){
            //console.log("lote      = ",$("option:selected",this).data("lote"));
            //console.log("serie     = ",$("option:selected",this).data("serie"));
            //console.log("caducidad = ",$("option:selected",this).data("caducidad"));
            //console.log("cantidad  = ",$("option:selected",this).data("cantidad"));

            $("#nlotef").hide(); $("#nserief").hide(); $("#data_1").hide(); 
            $("#nlotef input").val("");$("#nserief input").val(""); 
            $("#data_1 #caducidad").val("");$("#cantidadRecibidaOC #a_recibir").val("");
            $("#cantidadRecibidaOC span").text("");

            if($("option:selected",this).data("lote"))
            {
                $("#nlotef input").val($("option:selected",this).data("lote"));
                $("#nlotef").show();
            }
            if($("option:selected",this).data("serie"))
            {
                $("#nserief input").val($("option:selected",this).data("serie"));
                $("#nserief").show();
            }
            if($("option:selected",this).data("caducidad"))
            {
                $("#data_1 #caducidad").val($("option:selected",this).data("caducidad"));
                $("#data_1").show();
            }
            if($("option:selected",this).data("cantidad"))
            {
                var cant_reg = 0;
                for(var i = 0; i < productos_recibidos.length; i++)
                {
                    if(productos_recibidos[i].pedido == $("#pedidos_select").val() && productos_recibidos[i].cve_articulo == $("#articulos_lista").val() && productos_recibidos[i].lote == $("#nlotef input").val() && productos_recibidos[i].serie == $("#nserief input").val() && productos_recibidos[i].defectuoso == $("#articulo_defectuoso").val() /*&& productos_recibidos[i].proveedor == $("#proveedor_dev").val()*/)
                    {
                        cant_reg = productos_recibidos[i].cantidad;
                        break;
                    }
                }

                console.log("cant_reg = ", cant_reg);

                $("#cantidadRecibidaOC  #a_recibir").val($("option:selected",this).data("cantidad")-cant_reg);
                $("#cantidadRecibidaOC span").text($("option:selected",this).data("cantidad")-cant_reg);
                $("#cantidad_recibida").val($("option:selected",this).data("cantidad")-cant_reg);

                if($("#umed").val() == 'Caja' && $("#cantidadRecibidaOC span").text())
                {
                    $("#cantidadRecibidaOC span").text(parseInt(($("#cantidadRecibidaOC span").text())/$("option:selected","#articulos_lista").data("num_multiplo")));
                    $("#cantidadRecibidaOC  #a_recibir").val($("#cantidadRecibidaOC span").text());
                }
                $("#costoUnitario input").val($("option:selected",this).data("precio"));
                $("#costoEnOC input").val(parseFloat($("option:selected",this).data("precio"))*parseFloat($("#cantidadRecibidaOC  #a_recibir").val()));
            }
//proveedor_devolucion
//proveedor_dev
            /*
             $.ajax({
                type: "POST",
                dataType: "json",
                cache: false,
                data: {
                    cve_articulo: $(this).val(),
                    action: 'proveedores_articulos'
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) 
                    {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/devclientes/update/index.php',
                success: function(data) {

                    $("#proveedor_dev").empty();
                    var option_proveedores = "<option value =''>Seleccione Proveedor...</option>";

                    var node_proveedores = data.res_proveedores;

                    if(node_proveedores.length > 0)
                    {
                        for(var i = 0; i < node_proveedores.length; i++)
                        {
                            option_proveedores += "<option value = '"+node_proveedores[i].ID_Proveedor+"'>("+node_proveedores[i].cve_proveedor+") - "+node_proveedores[i].Nombre+"</option>";
                        }
                    }

                    $("#proveedor_dev").append(option_proveedores);
                    $("#proveedor_dev").trigger("chosen:updated");
                    //$("#clientes_pedidos").change();
                },
                error: function(res) {
                    window.console.log(res);
                }
            });
             */

        });

        $("#a_recibir").keyup(function()
        {
            if($("#umed").val() == "Caja")
            {
                $("#cantidad_recibida").val($(this).val()*$("option:selected","#articulos_lista").data("num_multiplo"));

                //if(parseFloat($("#cantidad_recibida").val()) > parseFloat($("#cantidad_recibida").val()))
                //   $("#cantidad_recibida").val($("#cantidad_recibida").val());
            }
            else //if($("#umed").val() == 1)
                    $("#cantidad_recibida").val($(this).val());

            $("#costoEnOC input").val(parseFloat($("option:selected","#articulos_lista").data("precio"))*parseFloat($(this).val()));
            //if($(this).val() == "")
            //   $(this).val("0");
        });

        function listar_articulos_recibidos()
        {
            var art_recibidos = "", pallet_c = "", cve_lp = "", cve_articulo = "", descripcion = "", serie = "", lote = "", 
                caducidad = "", peso = "", cantidad_por_recibir = "", zona_recepcion = "", clave_usuario = "", costo_total = "",
                hora_de_recepcion = "", defectuoso = "", proveedor = "";

            $("#articulos_recibidos").empty();
            for(var i = 0; i < productos_recibidos.length; i++)
            {
                pallet_c = productos_recibidos[i].pallet_cont; cve_lp = productos_recibidos[i].cveLP; 
                cve_articulo = productos_recibidos[i].cve_articulo; descripcion = productos_recibidos[i].des_articulo; 
                serie = productos_recibidos[i].serie; lote = productos_recibidos[i].lote; caducidad = productos_recibidos[i].caducidad; 
                peso = ""; cantidad_por_recibir = productos_recibidos[i].cantidad; 
                proveedor = productos_recibidos[i].proveedor_desc;

                zona_recepcion = productos_recibidos[i].z_recepcion_desc; 
                clave_usuario = $("#user_dev").val(); costo_total = productos_recibidos[i].costo;
                hora_de_recepcion = "";
                defectuoso = 'No';
                if(productos_recibidos[i].defectuoso == '1') defectuoso = 'Si';
                //console.log("productos_recibidos",i,"->",productos_recibidos);
                art_recibidos += "<tr>"+
                                    "<td>"+cve_articulo+"</td>"+
                                    "<td>"+descripcion+"</td>"+
                                    "<td>"+proveedor+"</td>"+
                                    "<td>"+serie+"</td>"+
                                    "<td>"+lote+"</td>"+
                                    "<td>"+caducidad+"</td>"+
                                    "<td>"+cantidad_por_recibir+"</td>"+
                                    "<td>"+pallet_c+"</td>"+
                                    "<td>"+cve_lp+"</td>"+
                                    "<td>"+zona_recepcion+"</td>"+
                                    "<td>"+clave_usuario+"</td>"+
                                    "<td>"+costo_total+"</td>"+
                                    "<td>"+defectuoso+"</td>"+
                                    "<td>"+hora_de_recepcion+"</td>"+
                                    "<td><i class='fa fa-eraser borrar_articulo' style='cursor:pointer;' data-reg='"+i+"' title='Borrar'></i></td>"+
                                "</tr>";
            }
            $("#articulos_recibidos").append(art_recibidos);

            $(".borrar_articulo").click(function(){

                console.log("borrar_articulo = ", $(this).data("reg"));
                productos_recibidos.splice($(this).data("reg"),1);
                listar_articulos_recibidos();
            });

            if(productos_recibidos.length == 0) $("#guardar_devolucion").prop("disabled", true);
        }

        $("#btn-recibir").click(function(){
            

            if($("input[name=tipo_entrada]:checked").val() == 2 && $("#clientes_pedidos").val() == "")
            {
                swal('Error','Debe Seleccionar un Cliente','warning');
                return;
            }

            if($("#zona_recepcion").val() == "" && $("input[name=tipo_entrada]:checked").val() != "3")
            {
                swal('Error','Debe Seleccionar una Zona de Recepción','warning');
                return;
            }

            if($("#articulos_lista").val() == "")
            {
                swal('Error','Debe Seleccionar un Artículo','warning');
                return;
            }
/*
            if($("#proveedor_dev").val() == "")
            {
                swal('Error','Debe Seleccionar un Proveedor','warning');
                return;
            }
*/
            //console.log("cantidad_recibida = ", $("#cantidad_recibida").val());
            //console.log("cantidadRecibidaOC = ", $("#cantidadRecibidaOC span").text());
            if(parseFloat($("#cantidad_recibida").val()) > parseFloat($("#cantidadRecibidaOC span").text()))
            {
                swal('Error','Cantidad a Recibir no puede ser mayor del máximo disponible','warning');
                return;
            }

            if($("#cantidadRecibidaOC  #a_recibir").val() == "" || $("#cantidadRecibidaOC  #a_recibir").val() <= 0)
            {
                swal('Error','Cantidad a Recibir debe tener un valor mayor a cero','warning');
                return;
            }

            var ok = true;
            for(var i = 0; i < productos_recibidos.length; i++)
            {
                if(productos_recibidos[i].pedido == $("#pedidos_select").val() && productos_recibidos[i].cve_articulo == $("#articulos_lista").val() && productos_recibidos[i].lote == $("#nlotef input").val() && productos_recibidos[i].serie == $("#nserief input").val() && productos_recibidos[i].defectuoso == $("#articulo_defectuoso").val() /*&& productos_recibidos[i].proveedor == $("#proveedor_dev").val()*/)
                {
                    ok = false;
                    break;
                }
            }

            if(!ok)
            {
                swal('Error','El artículo ya fué agregado para la devolución, debe borrarlo antes del listado','warning');
                return;
            }


            productos_recibidos.push({
                                        pedido : $("#pedidos_select").val(),
                                        cve_articulo : $("#articulos_lista").val(),
                                        des_articulo : $("#articulos_lista option:selected").text(),
                                        num_multiplo : $("option:selected", "#articulos_lista").data("num_multiplo"),
                                        lote : $("#nlotef input").val(), 
                                        serie : $("#nserief input").val(), 
                                        caducidad : $("#data_1 #caducidad").val(), 
                                        z_recepcion : $("#zona_recepcion").val(),
                                        z_recepcion_desc : $("#zona_recepcion option:selected").text(),
                                        pallet_cont : $("#pallet_contenedor").val(),
                                        cveLP : $("option:selected","#pallet_contenedor").data("cvelp"),
                                        defectuoso : $("#articulo_defectuoso").val(),
                                        costo : $("#costoEnOC input").val(),
                                        proveedor : $("option:selected", "#articulos_lista").data("id_proveedor"),
                                        proveedor_desc : $("option:selected", "#articulos_lista").data("nombre_proveedor"),
                                        cantidad : $("#cantidad_recibida").val()
                                    });

            listar_articulos_recibidos();

            $("#nlotef").hide(); $("#nserief").hide(); $("#data_1").hide(); 
            $("#nlotef input").val("");$("#nserief input").val(""); 
            $("#data_1 #caducidad").val("");$("#cantidadRecibidaOC #a_recibir").val("");
            $("#cantidadRecibidaOC span").text("");$("#articulos_lista").val("");

            $("#guardar_devolucion").prop("disabled", false);
            $("#pedidos_select").prop("disabled", true);
            $("#zona_recepcion").prop("disabled", true);
            $("#articulo_defectuoso").prop('checked',false);
            $("#articulo_defectuoso").val("0");

        });


        $("#clientes_pedidos").change(function()
        {
             $.ajax({
                type: "POST",
                dataType: "json",
                cache: false,
                data: {
                    almacen: $("#almacen_select").val(),
                    pedido: $("#pedidos_select").val(),
                    cliente: $(this).val(),
                    cve_proveedor: $("#cve_proveedor").val(),
                    action: 'cliente_pedido'
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) 
                    {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/devclientes/update/index.php',
                success: function(data) {

                    $("#pedidos_select").empty();
                    $("#facprove").empty();
                    var node = data.res_pedido, node_factura = data.res_factura;
                    var option = "<option value =''>Pedidos...</option>", option_factura = "<option value =''>Factura...</option>";
                    if(node.length > 0)
                    {
                        for(var i = 0; i < node.length; i++)
                        {
                            option += "<option value = '"+node[i].Fol_folio+"'>"+node[i].Fol_folio+"</option>";
                        }
                    }
                    //clientes_pedidos
                    $("#pedidos_select").append(option);
                    $("#pedidos_select").trigger("chosen:updated");

                    if(node_factura.length > 0)
                    {
                        for(var i = 0; i < node_factura.length; i++)
                        {
                            if(node_factura[i].Factura)
                               option_factura += "<option value = '"+node_factura[i].Factura+"'>"+node_factura[i].Factura+"</option>";
                        }
                    }
                    //clientes_pedidos
                    $("#facprove").append(option_factura);
                    $("#facprove").trigger("chosen:updated");
                },
                error: function(res) {
                    window.console.log(res);
                }
            });

        });



        $("#facprove").change(function()
        {
             $.ajax({
                type: "POST",
                dataType: "json",
                cache: false,
                data: {
                    almacen: $("#almacen_select").val(),
                    factura: $(this).val(),
                    cve_proveedor: $("#cve_proveedor").val(),
                    action: 'factura_pedido'
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) 
                    {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/devclientes/update/index.php',
                success: function(data) {

                    $("#pedidos_select").empty();
                    var node = data.res_pedido;
                    var option = "<option value =''>Pedidos...</option>", option_factura = "<option value =''>Factura...</option>";
                    if(node.length > 0)
                    {
                        for(var i = 0; i < node.length; i++)
                        {
                            option += "<option value = '"+node[i].Fol_folio+"'>"+node[i].Fol_folio+"</option>";
                        }
                    }
                    //clientes_pedidos
                    $("#pedidos_select").append(option);
                    $("#pedidos_select").trigger("chosen:updated");



                    $("#clientes_pedidos").empty();
                    var option_clientes = "<option value =''>Cliente...</option>";

                    var node_clientes = data.res_clientes;

                    if(node_clientes.length > 0)
                    {
                        for(var i = 0; i < node_clientes.length; i++)
                        {
                            option_clientes += "<option value = '"+node_clientes[i].Cve_clte+"'>("+node_clientes[i].Cve_clte+") - "+node_clientes[i].RazonSocial+"</option>";
                        }
                    }

                    $("#clientes_pedidos").append(option_clientes);
                    $("#clientes_pedidos").trigger("chosen:updated");
                },
                error: function(res) {
                    window.console.log(res);
                }
            });

        });

        $("#diao, #fecha_ini, #fecha_fin").change(function(){ ReloadGrid(); });
        

        $("#rutas_select").change(function()
        {
            $("#diao").empty();
             $.ajax({
                type: "POST",
                dataType: "json",
                cache: false,
                data: {
                    almacen: $("#almacen_select").val(),
                    pedido: $("#pedidos_select").val(),
                    cliente: $("#clientes_pedidos").val(),
                    id_ruta: $(this).val(),
                    cve_proveedor: $("#cve_proveedor").val(),
                    action: 'rutas_pedido'
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) 
                    {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/devclientes/update/index.php',
                success: function(data) {
                    console.log("Rutas SUCCESS: ", data);
                    $("#pedidos_select").empty();
                    //$("#facprove").empty();
                    var node = data.res_pedido;
                    //, node_factura = data.res_factura;
                    var option = "<option value =''>Pedidos...</option>";
                    //, option_factura = "<option value =''>Factura...</option>";
                    if(node.length > 0)
                    {
                        for(var i = 0; i < node.length; i++)
                        {
                            option += "<option value = '"+node[i].Fol_folio+"'>"+node[i].Fol_folio+"</option>";
                        }
                    }
                    //clientes_pedidos
                    $("#pedidos_select").append(option);
                    $("#pedidos_select").trigger("chosen:updated");
                    /*
                    if(node_factura.length > 0)
                    {
                        for(var i = 0; i < node_factura.length; i++)
                        {
                            if(node_factura[i].Factura)
                               option_factura += "<option value = '"+node_factura[i].Factura+"'>"+node_factura[i].Factura+"</option>";
                        }
                    }
                    //clientes_pedidos
                    $("#facprove").append(option_factura);
                    $("#facprove").trigger("chosen:updated");
                    */
                    ReloadGrid();
                },
                error: function(res) {
                    window.console.log(res);
                }
            });

        });


        $("#articulo_defectuoso").change(function(){

            if($(this).val() == 0)
                $(this).val("1");
            else
                $(this).val("0");
        });

        $("#guardar_devolucion").click(function(){

            if($("#motivo_dev").val() == "")
            {
                swal("Debe Registrar un Motivo", "Debe Seleccionar un Motivo de Devolución", "error");
                return;
            }


             $.ajax({
                type: "POST",
                dataType: "json",
                cache: false,
                data: {
                    motivo_dev: $("#motivo_dev").val(),
                    almacen: $("#almacen_select").val(),
                    folio_dev: $("#consecutivo_dev").val(),
                    pedido: $("#pedidos_select").val(),
                    usuario: <?php echo $_SESSION['id_user']; ?>,
                    productos_recibidos: productos_recibidos,
                    zona_recepcion : $("#zona_recepcion").val(),
                    tipo: $("input[name=tipo_entrada]:checked").val(),
                    action: 'guardar_devolucion'
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) 
                    {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/devclientes/update/index.php',
                success: function(data) {
                    //console.log(data);
                    swal("Devolución Registrada", "Devolución Registrada con Éxito", "success");
                    window.location.reload();
                },
                error: function(res) {
                    window.console.log(res);
                }
            });

        });

/*
    select_nerp.onchange = function(){
        if(this.value)
        {
            var value = this.value.split("-");
            document.getElementById('numeroOC').value = value[0];
        }
        else
        {
            document.getElementById('numeroOC').value = "";
        }
        $("#numeroOC").change();
    };
    */
/*
    $("#nlote").on('input', function(){//1
      $.ajax({
        //EDG117EDG
            type: "POST",
            dataType: "json",
            url: '/api/lotes/update/index.php',
            data: {
                LOTE: $(this).val(),
                cve_articulo: $("#articulo").val(),
                action: "load2"
            },
            beforeSend: function(x) {
              if (x && x.overrideMimeType) 
              {
                  x.overrideMimeType("application/json;charset=UTF-8");
              }
            },
            success: function(data) {
                if (data.LOTE != null)
                {
                  console.log("data",data);
                  $("#nlote").val(data.LOTE);
                  $("#caducidad").val(data.CADUCIDAD);
                  $("#CodeMessage").html("Lote existente");
                  window.crearLote = false;
                }
                else{
                  if($("#nlote").val() != "")
                  {
                    window.crearLote = true;
                    $("#CodeMessage").show();
                    $("#CodeMessage").html("Lote no existe, se creará como nuevo lote");
                    $("#caducidad").val("");
                  }
                  else{
                    window.crearLote = false;
                  }
                }
            }
        });
    });
*/


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
            url:'/api/devclientes/lista/index.php',
            datatype: "json",
            shrinkToFit: false,
            height:'auto',
            postData: {
                almacen: $("#almacen_select").val(),
                pedido: $("#pedidos_select").val(),
                cliente: $("#clientes_pedidos").val(),
                id_ruta: $("#rutas_select").val(),
                cve_proveedor: $("#cve_proveedor").val()
            },
            mtype: 'POST',
            colNames:['Acciones','Cliente|Ruta', 'Folio Entrada','Clave','Descripción','Lote|Serie','Caducidad','Cantidad','UM','LP','Status', 'DiaO', 'Fecha','Hora Recepción','Usuario','id_item','control_lotes','control_caduca','control_serie', 'clave_zona', 'id_proveedor','Zona de Recepción', 'id_descarga'],
            colModel:[
                {name:'myac',index:'', width:80, sortable:false, resize:false, formatter:imageFormat},
                {name:'cliente_ruta',index:'cliente_ruta',width:100, editable:false, sortable:false, hidden: true},
                {name:'Folio_Entrada',index:'Folio_Entrada',width:100, editable:false, sortable:false, align: 'right'},
                {name:'cve_articulo',index:'cve_articulo',width:120, editable:false, sortable:false},
                {name:'desc_articulo',index:'desc_articulo',width:200, editable:false, sortable:false},
                {name:'lote_serie',index:'lote_serie',width:120, editable:false, sortable:false},
                {name:'caducidad',index:'caducidad',width:100, editable:false, sortable:false, align: 'center'},
                {name:'cantidad',index:'cantidad',width:100, editable:false, sortable:false, align: 'right'},
                {name:'unidad_medida',index:'unidad_medida',width:60, editable:false, sortable:false},
                {name:'license_plate',index:'license_plate',width:120, editable:false, sortable:false},
                {name:'status',index:'status',width:120, editable:false, sortable:false},
                {name:'diao',index:'diao',width:60, editable:false, sortable:false, align: 'right'},
                {name:'fecha',index:'fecha',width:100, editable:false, sortable:false, align: 'center'},
                {name:'hora_recepcion',index:'hora_recepcion',width:120, editable:false, sortable:false, align: 'center'},
                {name:'usuario',index:'usuario',width:120, editable:false, sortable:false, hidden: true},
                {name:'id_item',index:'id_item',width:120, editable:false, sortable:false, hidden: true},
                {name:'control_lotes',index:'control_lotes',width:120, editable:false, sortable:false, hidden: true},
                {name:'control_caduca',index:'control_caduca',width:120, editable:false, sortable:false, hidden: true},
                {name:'control_serie',index:'control_serie',width:120, editable:false, sortable:false, hidden: true},
                {name:'clave_zona',index:'clave_zona',width:120, editable:false, sortable:false, hidden: true},
                {name:'id_proveedor',index:'id_proveedor',width:120, editable:false, sortable:false, hidden: true},
                {name:'zona_recepcion',index:'zona_recepcion',width:250, editable:false, sortable:false},
                {name:'id_descarga',index:'id_descarga',width:250, editable:false, sortable:false, hidden: true},
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'cve_gpoart',
            viewrecords: true,
            loadComplete: function(data){ console.log("SUCCESS: ", data); $("#diao").empty(); $("#diao").append(data.options_diao);},
            loadError: function(data){ console.log("ERROR: ", data);},
            sortorder: "desc"
        });

        // Setup buttons
        $("#grid-table").jqGrid('navGrid', '#grid-pager',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );


        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
        function imageFormat( cellvalue, options, rowObject ){
            //return '<img src="'+cellvalue+'" />&nbsp;&nbsp;&nbsp;&nbsp;<img src="'+cellvalue+'" />';
            var id = rowObject[15];
            var cve_articulo = rowObject[3];
            var control_lotes = rowObject[16];
            var Caduca = rowObject[17];
            var control_numero_series = rowObject[18];
            var lote = rowObject[5];
            var clave_zona = rowObject[19];
            var id_proveedor = rowObject[20];
            var cantidad = rowObject[7];
            var lp = rowObject[9];
            var status = rowObject[10];
            var id_descarga = rowObject[22];
            //var url = "x/?serie="+serie+"&correl="+correl;
            //var url2 = "v/?serie="+serie+"&correl="+correl;

            //console.log("cellvalue = ", cellvalue, " - options = ", options, " - rowObject = ", rowObject);

            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
/*
            html += '<a href="#" onclick="editar(\''+serie+'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="borrar(\''+serie+'\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
*/
            //return '<a href="#" onclick="downloadxml(\''+url+'\')"><i class="ace-icon fa fa-download" alt="Download"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onclick="viewPdf(\''+url2+'\')"><i class="ace-icon fa fa-file-pdf-o" alt="Ver PDF"></i></a>';
            if(lote == '' && status != 'Enviado a Merma')
            {
                if(control_lotes == 'S')
                {
                    html += '<input type="checkbox" aling="center" title="Cambiar Lote|Serie" class="checkbox-asignator" data-id_descarga="'+id_descarga+'" id="'+id+'" value="'+id+'" />&nbsp;&nbsp;&nbsp;&nbsp;';
                }

                if($("#permiso_editar").val() == 1)
                html += '<a href="#" onclick="cambiar_lote(\''+id+'\', \''+cve_articulo+'\', \''+control_lotes+'\', \''+Caduca+'\', \''+control_numero_series+'\', \''+clave_zona+'\', \''+id_proveedor+'\', \''+id_descarga+'\', \''+cantidad+'\')"><i class="fa fa-edit" title="Cambiar Lote|Serie"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            }

            if(status != 'Enviado a Merma' && status != 'Ubicado' && $("#permiso_editar").val() == 1)
            html += '<a href="#" onclick="marcar_como_merma(\''+id+'\', \''+cve_articulo+'\', \''+lote+'\', \''+cantidad+'\', \''+lp+'\', \''+clave_zona+'\', \''+id_proveedor+'\')"><i class="fa fa-trash-o" style="font-size:15px;" title="Marcar como Merma"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

            return html;
        }

        function aceSwitch( cellvalue, options, cell ) {
            setTimeout(function(){
                $(cell) .find('input[type=checkbox]')
                    .addClass('ace ace-switch ace-switch-5')
                    .after('<span class="lbl"></span>');
            }, 0);
        }
        //enable datepicker
        function pickDate( cellvalue, options, cell ) {
            setTimeout(function(){
                $(cell) .find('input[type=text]')
                    .datepicker({format:'yyyy-mm-dd' , autoclose:true});
            }, 0);
        }

        function beforeDeleteCallback(e) {
            var form = $(e[0]);
            if(form.data('styled')) return false;

            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_delete_form(form);

            form.data('styled', true);
        }

        function reloadPage() {
            var grid = $(grid_selector);
            $.ajax({
                url: "index.php",
                dataType: "json",
                success: function(data){
                    grid.trigger("reloadGrid",[{current:true}]);
                },
                error: function(){}
            });
        }

        function beforeEditCallback(e) {
            var form = $(e[0]);
            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_edit_form(form);
        }

        //it causes some flicker when reloading or navigating grid
        //it may be possible to have some custom formatter to do this as the grid is being created to prevent this
        //or go back to default browser checkbox styles for the grid
        function styleCheckbox(table) {
        }

        //unlike navButtons icons, action icons in rows seem to be hard-coded
        //you can change them like this in here if you want
        function updateActionIcons(table) {
        }

        //replace icons with FontAwesome icons like above

        function updatePagerIcons(table) {
        }

        function enableTooltips(table) {
            $('.navtable .ui-pg-button').tooltip({container:'body'});
            $(table).find('.ui-pg-div').tooltip({container:'body'});
        }

        //var selr = jQuery(grid_selector).jqGrid('getGridParam','selrow');

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });


    $(function($) {
        var grid_selector = "#grid-tabla2";
        var pager_selector = "#grid-page2";

        //resize to fit page size
        $(window).on('resize.jqGrid', function() {
                $(grid_selector).jqGrid('setGridWidth', $(".page-content").width() - 60);
            })
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
            if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') 
            {
                setTimeout(function() {
                    $(grid_selector).jqGrid('setGridWidth', parent_column.width());
                }, 0);
            }
        })

        $(grid_selector).jqGrid({
            url: '#',
            datatype: "json",
            height: "auto",
            shrinkToFit: false,
            mtype: 'POST',
            colNames: ['Clave', 'Descripcion', "Cantidad", "Costo"],
            colModel: [
              {name: 'clave',index: 'clave',width: 200,editable: false,sortable: false}, 
              {name: 'desc',index: 'desc',width: 550,editable: false,sortable: false}, 
              {name: 'cantidad',index: 'cantidad',width: 150,editable: false,sortable: false}, 
              {name: 'costo',index: 'costo',width: 150,editable: false,sortable: false}, 
            ],
            rowNum: 30,
            rowList: [30, 40, 50],
            pager: "#grid-page2",
            sortname: 'cve_gpoart',
            viewrecords: true,
            sortorder: "desc",
            ondblClickRow: function(rowid, iRow, iCol) {
                var row_id = $(grid_selector).getGridParam('selrow');
                $(grid_selector).editRow(row_id, true);
            }
        });

        $(window).triggerHandler('resize.jqGrid'); //trigger window resize to make the grid get the correct size

        function imageFormat2(cellvalue, options, rowObject) 
        {
            var correl = options.rowId;
            var html = '<a href="#" onclick="borrarAdd(\'' + correl + '\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            return html;
        }

        function aceSwitch(cellvalue, options, cell) 
        {
            setTimeout(function() {
                $(cell).find('input[type=checkbox]')
                    .addClass('ace ace-switch ace-switch-5')
                    .after('<span class="lbl"></span>');
            }, 0);
        }

        function beforeDeleteCallback(e) 
        {
            var form = $(e[0]);
            if (form.data('styled')) return false;
            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_delete_form(form);
            form.data('styled', true);
        }

        function reloadPage() 
        {
            var grid = $(grid_selector);
            $.ajax({
                url: "index.php",
                dataType: "json",
                success: function(data) {
                    grid.trigger("reloadGrid", [{
                        current: true
                    }]);
                },
                error: function() {}
            });
        }

        function beforeEditCallback(e) 
        {
            var form = $(e[0]);
            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_edit_form(form);
        }

        function enableTooltips(table) 
        {
            $('.navtable .ui-pg-button').tooltip({
                container: 'body'
            });
            $(table).find('.ui-pg-div').tooltip({
                container: 'body'
            });
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

        $(window).on('resize.jqGrid', function() {
            $(grid_selector).jqGrid('setGridWidth', $(".page-content").width() - 60);
        })
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
            if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') 
            {
                setTimeout(function() {
                    $(grid_selector).jqGrid( 'setGridWidth', parent_column.width() );
                }, 0);
            }
        })

        $(grid_selector).jqGrid({
            url: '#',
            datatype: "json",
            height: "auto",
            mtype: 'POST',
            colNames: ['ID', 'Clave', 'Descripcion', 'Serie', 'Lote', 'Caducidad', "Recibidas (Pzs)", "Cve Zona", "Zona de Recepción", "Cve Usuario","Costo", "Acciones"],
            colModel: [
                {name: 'id',index: 'id',width: 10,editable: false,sortable: false,hidden: true},
                {name: 'articulo',index: 'articulo',width: 100,editable: false,sortable: false},
                {name: 'desc',index: 'desc',width: 280,editable: false,sortable: false},
                {name: 'serie',index: 'serie',width: 100,editable: false,sortable: false},
                {name: 'lote',index: 'lote',width: 100,editable: false,sortable: false},
                {name: 'caducidad',index: 'caducidad',width: 100,sortable: false,editable: false,edittype: "text"},
                {name: 'recibida',index: 'recibida',width: 150,sortable: false,editable: false,edittype: "text"},
                {name: 'cve_ubicacion',index: 'cve_ubicacion',hidden: true,width: 100,sortable: false,editable: false,edittype: "text"},
                {name: 'almacen',index: 'almacen',width: 190,sortable: false,editable: false,edittype: "text"},
                {name: 'cve_usuario',index: 'cve_usuario',width: 130,sortable: false,editable: false,edittype: "text"},
                {name: 'costo',index: 'costo',width: 110,sortable: false,editable: false,edittype: "text"},
                {name: 'myac',index: '',width: 100,fixed: true,sortable: false,resize: false,formatter: imageFormat2}
           ],
            rowNum: 30,
            rowList: [30, 40, 50],
            pager: "#grid-page",
            sortname: 'cve_gpoart',
            viewrecords: true,
            sortorder: "desc",
            ondblClickRow: function(rowid, iRow, iCol) {
                var row_id = $(grid_selector).getGridParam('selrow');
                $(grid_selector).editRow(row_id, true);
            }
        });


        $(window).triggerHandler('resize.jqGrid');

        function comprobar_campos()
        {
            var result = false;
            $("#guardo").val("true");
            $("#usuario").prop("disabled", true);

            if ($("#checkOC").is(":checked"))
            {
                if ($("#numeroOC").val() == "") 
                {
                    swal('Error','Debes indicar un numero de orden','warning');
                    return result;
                }
            }
            if ($("#zonaRecepción").val() == "") 
            {
                swal('Error','Debes seleccionar una zona de recepción','warning');
                return result;
            }
            if ($("#articulo").val() == "") 
            {
                swal('Error','Por favor selecciona un articulo','error');
                return result;
            }
            if ($("#nlotef").is(":visible") == true && $("#nlote").val() == "") 
            {
                swal('Error','Debes seleccionar o crear un Lote','warning');
                return result;
            }
            if ($("#nserief").is(":visible") == true && $("#nserie").val() == "") 
            {
                swal('Error','Debes crear un numero de serie','warning');
                return result;
            }
            if ($("#checkOC").is(":checked"))
            {
                if ($("#ctotal").val() == "0" || $("#ctotal").val() == "") 
                {
                    swal('Error','Por favor eliga la cantidad','error');
                    return result;
                }
            }
            if ($("#data_1").is(":visible") == true && $("#nlote").val() != "" && $("#caducidad").val() == "" && !$("#caducidad").prop("disabled")) 
            {
                swal('Error','La fecha de caducidad no puede estar vacia','warning');
                return;
            }
            return true;
        }

        function validar_lotes()
        {
            var loteF;
            console.log("validar_lote", $("#nlote").val()+"-"+window.crearLote);
            
            if ($("#nlote").val() != "") 
            {
              if(window.crearLote)
              {
                loteF = $("#nlote").val();
                var date = new Date();
                
                //EDG117EDG
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    async: false,
                    cache: false,
                    url: '/api/lotes/update/index.php',
                    data: {
                        cve_articulo: $("#cve_articulo").val(),
                        LOTE: $("#nlote").val(),
                        CADUCIDAD: $("#caducidad").val(),
                        action: "add"
                    },
                    beforeSend: function(x) {
                        if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                    },
                    success: function(data) 
                    {
                        console.log("GUARDE LOTE NUEVO");
                    }
                });
              }

            }
            if ($("#nserie").val() != "") 
            {
                loteF = "";
                $("#lote_valor").val(loteF);
              console.log("loteF", loteF);
            }
        }
      
            
        function traerserie() {
            if($('#nserie').val()!= "")
            {
              $.ajax({
                  type: "POST",
                  dataType: "json",
                  url: '/api/devclientes/update/index.php',
                  data: {
                      action : "traerserie",
                      numero_serie:$('#nserie').val(),
                      articulo:$("#cve_articulo").val(),
                  },
                  beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
                  success: function(data) {
                    $("#nlotef").hide();
                    $("#nlote").hide();
                      console.log("Creando serie",data);
                  }
              });
            }
        }

        $(window).triggerHandler('resize.jqGrid'); 

        function imageFormat2(cellvalue, options, rowObject) 
        {
            var correl = options.rowId;
            var html = '<a href="#" onclick="borrarAdd(\'' + correl + '\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            return html;
        }

        function aceSwitch(cellvalue, options, cell) 
        {
            setTimeout(function() {
                $(cell).find('input[type=checkbox]')
                    .addClass('ace ace-switch ace-switch-5')
                    .after('<span class="lbl"></span>');
            }, 0);
        }

        function beforeDeleteCallback(e) {
            var form = $(e[0]);
            if (form.data('styled')) return false;
            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_delete_form(form);
            form.data('styled', true);
        }

        function reloadPage() {
            var grid = $(grid_selector);
            $.ajax({
                url: "index.php",
                dataType: "json",
                success: function(data) {
                    grid.trigger("reloadGrid", [{
                        current: true
                    }]);
                },
                error: function() {}
            });
        }

        function beforeEditCallback(e) 
        {
            var form = $(e[0]);
            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_edit_form(form);
        }

        function enableTooltips(table) 
        {
            $('.navtable .ui-pg-button').tooltip({container: 'body'});
            $(table).find('.ui-pg-div').tooltip({container: 'body'});
        }

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });

    $("#cantrecib").change( function (e){//lilo
      console.log("change");
      var i= 1;
      var html="";
      for(i = 1 ; i<= $("#cantrecib").val();i++) 
      {
         html += '<input id="serie_'+i+'" type="text" class="form-control" value="" ></a>&nbsp;&nbsp;';
         
      }
       $("#series").html(html);
    });

    $("#guardar_lote_serie_cambio").click(function(){

        console.log("tiene_lote = ", $("#tiene_lote").val());
        console.log("select_lotes = ", $("#select_lotes").val());
        console.log("select_lotes_chosen = ", $("#select_lotes_chosen").val());
        console.log("tiene_caducidad = ", $("#tiene_caducidad").val());
        console.log("tiene_serie = ", $("#tiene_serie").val());
        var productoDerivado = $("#btn-productoDerivado").is(":checked")?1:0;

        if($("#tiene_lote").val() == 'S' && $("#select_lotes").val() != "" && $("#nuevo_lote_serie").val() != "")
        {
            var mensaje = "Puede Agregar un nuevo lote o seleccionar un lote existente, debe borrar el lote en el campo de nuevo lote|serie o no seleccionar un lote en lotes existentes";
            swal("Error", mensaje, "error");
            return;
        }

        if($("#tiene_lote").val() == 'S' && $("#select_lotes").val() == "" && $("#nuevo_lote_serie").val() == "")
        {
            var mensaje = "Debe Ingresar un Lote";
            swal("Error", mensaje, "error");
            return;
        }

        if($("#tiene_caducidad").val() == 'S' && $("#cambiar_caducidad").val() == "")
        {
            var mensaje = "Debe Ingresar un Lote y Caducidad";
            swal("Error", mensaje, "error");
            return;
        }

        if($("#tiene_serie").val() == 'S' && $("#select_serie").val() != "" && $("#nuevo_lote_serie").val() != "" )
        {
            var mensaje = "Puede Agregar una nueva serie o seleccionar una serie existente, debe borrar la serie en el campo de nuevo lote|serie o no seleccionar una serie en series existentes";
            swal("Error", mensaje, "error");
            return;
        }

        if($("#tiene_serie").val() == 'S' && $("#select_serie").val() == "" && $("#nuevo_lote_serie").val() == "")
        {
            swal("Error", "Debe Seleccionar la Serie", "error");
            return;
        }

        if($("#articulos_conversion").val() == '' && productoDerivado == 1)
        {
            swal("Error", "Debe Selecionar un artículo para realizar la conversión", "error");
            return;
        }

        if(($("#cantidad_derivado").val() == '' || $("#cantidad_derivado").val() == '0') && productoDerivado == 1)
        {
            swal("Error", "Debe registrar una cantidad para realizar la conversión", "error");
            return;
        }

          console.log("producto_derivado:", ($("#btn-productoDerivado").is(":checked"))?1:0);
          console.log("articulos_conversion:", $("#articulos_conversion").val());
          console.log("cantidad_derivado:", $("#cantidad_derivado").val());

        $.ajax({
            url: '/api/devclientes/update/index.php',
            method: 'POST',
            dataType: 'json',
            data: {
              action: 'cambiar_lote',
              cve_articulo: $("#articulo_cambiar").val(),
              id_entrada: $("#id_entrada").val(),
              id_descarga: $("#id_descarga").val(),
              nuevo_lote_serie: $("#nuevo_lote_serie").val(),
              tiene_serie: $("#tiene_serie").val(),
              tiene_lote: $("#tiene_lote").val(),
              tiene_caducidad: $("#tiene_caducidad").val(),
              cambiar_caducidad: $("#cambiar_caducidad").val(),
              cambiar_lote: $("#cambiar_lote_val").val(),
              clave_zona: $("#clave_zona").val(),
              id_proveedor: $("#id_proveedor").val(),
              producto_derivado: ($("#btn-productoDerivado").is(":checked"))?1:0,
              articulos_conversion: $("#articulos_conversion").val(),
              cantidad_derivado: $("#cantidad_derivado").val(),
              cve_usuario: "<?php echo $_SESSION['cve_usuario'] ?>", 
              id_almacen: "<?php echo $_SESSION['id_almacen'] ?>", 
              lote_serie: ($("#select_lotes").val() != '')?($("#select_lotes").val()):($("#select_serie").val())
            }
        })
        .done(function(data){
            console.log("data lotes = ", data);
            $("#select_lotes").empty();
            $("#select_serie").empty();
            $("#select_lotes").append(data.options);
            $("#select_lotes").val('');
            $('.chosen-select').trigger("chosen:updated");
            $("#cambiar_lote_modal").modal("hide");
            ReloadGrid();
            if(productoDerivado == 1)
                swal("Éxito", "El Artículo se ha convertido Correctamente", "success");
            else
                swal("Éxito", "Lote|Serie Cambiado Correctamente, ya puede acomodar el artículo", "success");

        }).fail(function(data){
            console.log("ERROR data lotes 1 = ", data);
        });

    });

    $("#generarExcelDetalle").click(function(){

        var pedido = $("#pedidos_select").val(), id_ruta = $("#rutas_select").val(), almacen = $("#almacen_select").val(), diao = $("#diao").val(), fecha_inicio = $("#fecha_ini").val(), fecha_fin = $("#fecha_fin").val();

        $(this).attr("href", "/api/koolreport/excel/detalle_devoluciones/export.php?id_ruta="+id_ruta+"&pedido="+pedido+"&almacen="+almacen+"&diao="+diao+"&fecha_inicio="+fecha_inicio+"&fecha_fin="+fecha_fin+"");

    });


    $("#btn-productoDerivado").click(function()
    {
        if($(this).is(":checked"))
        {
            $("#articulos_conversion_chosen, #cant_derivado").show();
        }
        else
        {
            $("#articulos_conversion_chosen, #cant_derivado").hide();
        }
    });

    function marcar_como_merma(id, cve_articulo, lote_serie, cantidad, lp, clave_zona, id_proveedor)
    {
        console.log("marcar_como_merma");
            swal({
                title: "¿Marcar Artículo como merma?",
                text: "Al marcar como merma el producto ya no se podrá acomodar para entrar al almacén",
                type: "info",

                cancelButtonText: "No",
                cancelButtonColor: "#14960a",
                showCancelButton: true,

                confirmButtonColor: "#55b9dd",
                confirmButtonText: "Si",
                closeOnConfirm: true
            }, function() {

                    $.ajax({
                        url: '/api/devclientes/update/index.php',
                        method: 'POST',
                        dataType: 'json',
                        data: {
                          action: 'marcar_como_merma',
                          id_entrada: id,
                          cve_articulo: cve_articulo,
                          lote_serie: lote_serie,
                          cantidad: cantidad,
                          lp: lp,
                          clave_zona: clave_zona,
                          cve_usuario: "<?php echo $_SESSION['cve_usuario'] ?>", 
                          id_almacen: "<?php echo $_SESSION['id_almacen'] ?>", 
                          id_proveedor: id_proveedor
                        }
                    })
                    .done(function(data){
                        console.log("data merma = ", data);
                        ReloadGrid();

                    }).fail(function(data){
                        console.log("ERROR data lotes 2 = ", data);
                    });

            });

    }

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

    $("#cambiar_lotes").click(function()
    {
        $("#guardar_lote_serie_cambio").hide();
        if($("#permiso_registrar").val() == 1) $("#guardar_lote_serie_varios").show();
        else $("#guardar_lote_serie_varios").hide();

        if($("#permiso_registrar").val() == 1) $("#cambiar_lotes").show();
        else $("#cambiar_lotes").hide();

          var arr = [], arr2 = [];
          $("input[type=checkbox].checkbox-asignator").each(function(i, e){
            if($(this).prop("checked") == true)
            {
              arr.push($(this).attr('value'));
              arr2.push($(this).data('id_descarga'));

              console.log("asignar() id_entrada ->", $(this).attr('id'));
              console.log("asignar() id_descarga->", $(this).data('id_descarga'));
              
              console.log("***********************************");
            }
          });
         console.log("id_entrada  a asignar = ", arr);
         console.log("id_descarga a asignar = ", arr2);
         console.log("Asignar Todos = ", ($("#btn-asignarTodo").is(":checked"))?1:0);

         if(arr.length == 0)
         {
            swal("Error", "Debe Seleccionar un artículo para realizar la operación", "error");
            return;
         }

              console.log("**almacen:", $("#almacen_select").val());
              console.log("**pedido:", $("#pedidos_select").val());
              console.log("**diao:", $("#diao").val());
              console.log("**fecha_inicio:", $("#fecha_ini").val());
              console.log("**fecha_fin:", $("#fecha_fin").val());
              console.log("**cliente:", $("#clientes_pedidos").val());
              console.log("**id_ruta:", $("#rutas_select").val());
              console.log("**cve_proveedor:", $("#cve_proveedor").val());
              console.log("**asignar_todos:", ($("#btn-asignarTodo").is(":checked"))?1:0);
              console.log("**arr_entradas:", arr);

        $.ajax({
            url: '/api/devclientes/lista/index.php',
            method: 'GET',
            dataType: 'json',
            data: {
              action: 'verificar_caducidad_lote_varios',
              almacen: $("#almacen_select").val(),
              pedido: $("#pedidos_select").val(),
              diao: $("#diao").val(),
              fecha_inicio: $("#fecha_ini").val(),
              fecha_fin: $("#fecha_fin").val(),
              cliente: $("#clientes_pedidos").val(),
              id_ruta: $("#rutas_select").val(),
              cve_proveedor: $("#cve_proveedor").val(),
              asignar_todos: ($("#btn-asignarTodo").is(":checked"))?1:0,
              arr_entradas: arr
            }
        })
        .done(function(data){
            //cambiar_caducidad_varios
            console.log("verificar_caducidad_lote_varios = ", data);
            $(".cambiar_caducidad_v").hide();
            if(data.caducidad == 'S' || data.caducidad == 'SN' || data.caducidad == 'NS')
                $(".cambiar_caducidad_v").show();

        }).fail(function(data){
            console.log("ERROR verificar_caducidad_lote_varios = ", data);
        });


         $("#nuevo_lote_serie, #select_lotes, #cambiar_caducidad, #cambiar_caducidad_varios, #select_serie").val("");
         $("#check_cambiar_lotes").hide();
         $("#cambiar_lote_modal").modal("show");
         $("#arr_check_cambiar_lotes").val(arr);
         $("#arr_check_cambiar_lotes_id_descarga").val(arr2);

    });

    $("#guardar_lote_serie_varios").click(function()
    {
        cambiar_lote_varios();
    });
    

    function cambiar_lote_varios()
    {
        $("#guardar_lote_serie_cambio").hide();
        if($("#permiso_registrar").val() == 1) $("#guardar_lote_serie_varios").show();
        else $("#guardar_lote_serie_varios").hide();

        if($("#permiso_registrar").val() == 1) $("#cambiar_lotes").show();
        else $("#cambiar_lotes").hide();

        if($("#nuevo_lote_serie").val() == '')
        {
            swal("Error", "Debe Registrar un Lote", "error");
            return;
        }

        if($("#cambiar_caducidad_varios").val() == '')
        {
            swal("Error", "Debe Registrar una Caducidad", "error");
            return;
        }

        $.ajax({
            url: '/api/devclientes/update/index.php',
            method: 'POST',
            dataType: 'json',
            data: {
              action: 'cambiar_lote_varios',
              almacen: $("#almacen_select").val(),
              pedido: $("#pedidos_select").val(),
              diao: $("#diao").val(),
              fecha_inicio: $("#fecha_ini").val(),
              fecha_fin: $("#fecha_fin").val(),
              cliente: $("#clientes_pedidos").val(),
              id_ruta: $("#rutas_select").val(),
              nuevo_lote: $("#nuevo_lote_serie").val(),
              nueva_caducidad: $("#cambiar_caducidad_varios").val(),
              cve_proveedor: $("#cve_proveedor").val(),
              asignar_todos: ($("#btn-asignarTodo").is(":checked"))?1:0,
              producto_derivado: ($("#btn-productoDerivado").is(":checked"))?1:0,
              articulos_conversion: $("#articulos_conversion").val(),
              cantidad_derivado: $("#cantidad_derivado").val(),
              cve_usuario: "<?php echo $_SESSION['cve_usuario'] ?>", 
              id_almacen: "<?php echo $_SESSION['id_almacen'] ?>", 
              arr_entradas: $("#arr_check_cambiar_lotes").val(),
              arr_descargas: $("#arr_check_cambiar_lotes_id_descarga").val()
            }
        })
        .done(function(data){
            console.log("data lotes 2= ", data);

            $("#select_lotes").empty();
            $("#select_serie").empty();
            $("#select_lotes").append(data.options);
            $("#select_lotes").val('');
            $('.chosen-select').trigger("chosen:updated");
            $("#cambiar_lote_modal").modal("hide");
            ReloadGrid();
            swal("Éxito", "Lotes Cambiados Correctamente, ya puede acomodar los artículo", "success");

        }).fail(function(data){
            console.log("ERROR data lotes 2 = ", data);
        });
    }

    function cambiar_lote(id, cve_articulo, control_lotes, Caduca, control_numero_series, clave_zona, id_proveedor, id_descarga, cantidad)
    {
        $(".cambiar_caducidad_v").hide();
        $("#guardar_lote_serie_cambio").show();
        $("#guardar_lote_serie_varios").hide();
        
        
        $("#nuevo_lote_serie, #select_lotes, #cambiar_caducidad, #select_serie").val("");
        $("#check_cambiar_lotes").show();
        console.log("id = ",id, " cve_articulo = ",cve_articulo, " control_lote = ", control_lotes, " control_caducidad = ", Caduca, " control_serie = ", control_numero_series);
        $("#id_entrada").val(id);
        $("#id_descarga").val(id_descarga);
        $("#cantidad_derivado").val(cantidad);
        $("#articulo_cambiar").val(cve_articulo);
        $("#tiene_lote").val(control_lotes);
        $("#tiene_caducidad").val(Caduca);
        $("#tiene_serie").val(control_numero_series);
        $("#clave_zona").val(clave_zona);
        $("#id_proveedor").val(id_proveedor);


        $("#select_lotes, #select_lotes_chosen").hide();
        $("#cambiar_caducidad").hide();
        $("#select_serie, #select_serie_chosen").hide();

        if(control_lotes == 'S')
        {
            $("#select_lotes, #select_lotes_chosen").show();
            if(Caduca == 'S')
            {
                $("#cambiar_caducidad").show();
                //$("#cambiar_caducidad").val(caducidad);
            }

                $.ajax({
                    url: '/api/controlcuarentena/update/index.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                      action: 'cargarLotes',
                      cve_articulo: cve_articulo,
                      lote_a_cambiar: ''
                    }
                })
                .done(function(data){
                    console.log("data lotes = ", data);
                    $("#select_lotes").empty();
                    $("#select_serie").empty();
                    $("#select_lotes").append(data.options);
                    $("#select_lotes").val('');
                    $('.chosen-select').trigger("chosen:updated");
                }).fail(function(data){
                    console.log("ERROR data lotes 2 = ", data);
                });


            $("#select_serie, #select_serie_chosen").hide();
        }

        if(control_numero_series == 'S')
        {
            $("#select_lotes, #select_lotes_chosen").hide();
            $("#cambiar_caducidad").hide();
            $("#select_serie, #select_serie_chosen").show();

                $.ajax({
                    url: '/api/controlcuarentena/update/index.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                      action: 'cargarSeries',
                      cve_articulo: cve_articulo,
                      serie_a_cambiar: serie
                    }
                })
                .done(function(data){
                    console.log("data series = ", data);
                    $("#select_serie").empty();
                    $("#select_lotes").empty();
                    $("#select_serie").append(data.options);
                    $("#select_serie").val('');
                    $('.chosen-select').trigger("chosen:updated");
                }).fail(function(data){
                    console.log("ERROR data series = ", data);
                });

        }
        $("#cve_articulo_cambio_lote_serie").text(cve_articulo);
        $("#cambiar_lote_modal").modal("show");
        //$('.chosen-select').chosen();
        $('.chosen-select').trigger("chosen:updated");
    }

    //MENUS 
    $('#checkRL').change(function(e) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                action : "folioConsecutico"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url: '/api/devclientes/update/index.php',
            success: function(data) {
                $('#foliorl').val(data.folioConsecutivo);
                $("#foliorl").prop('disabled', true);
            }
        });
    });

    $('#checkCD').change(function(e) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                action : "folioConsecutico"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url: '/api/devclientes/update/index.php',
            success: function(data) {
                $('#foliocd').val(data.folioConsecutivo);
                $("#foliocd").prop('disabled', true);
            }
        });
    });



    $('#lote').change(function(e) {
        if ($('#lote').val() == "") 
        {
          $("#nlotef").show();
        } 
        else  
        {
          //$("#nlotef").hide().val("");
        }
      //EDG117EDG
        $.ajax({
            type: "POST",
            dataType: "json",
            url: '/api/lotes/update/index.php',
            data: {
                LOTE: $(this).val(),
                cve_articulo: $("#articulo").val(),
                action: "load2"
            },
            beforeSend: function(x) {
              if (x && x.overrideMimeType) 
              {
                  x.overrideMimeType("application/json;charset=UTF-8");
              }
            },
            success: function(data) {
                if (data.success == true)
                {
                  $("#caducidad").val(data.CADUCIDAD);
                  console.log("datacaducidad",data.CADUCIDAD);
                }
            }
        });
    });
  
    function cargarOC()
    {
      $.ajax({
        type: "POST",
        dataType: "json",
        data: {
            action: "load2",
            codigo: $('#numeroOC').val()
        },
        beforeSend: function(x) {
            if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
        },
        url: '/api/devclientes/update/index.php',
        success: function(data) {renderOC(data);}
      });
    }
  
    function renderOC(data)
    {
        $("#fechaentrada").prop("disabled", true);
        $("#hora").prop("disabled", true);
//         $("#almacenoc").val(data.Cve_Almac);
        $("#protocolooc").val(data.ID_Protocolo);
        $("#cprotocolooc").val(data.Consec_protocolo);
        select_nerp.value = $('#numeroOC').val()+"-"+data.factura;
        $(select_nerp).trigger("chosen:updated");
        $("#proveedor").val(data.ID_Proveedor).change().prop("disabled", true);
        cargarOrden($('#numeroOC').val());//inserta los productos esperados y limita la cantidad de productos que pueden entrar todavia
        cargarTodo($('#numeroOC').val());//inserta los productos que ya han sido recibidos y cambia el hiddenAction a edit, si no hay productos es add
        cargarZonas(data);//Carga el listado de las zonas respecto al almacen
    }
  
    function cargarZonas(data)
    {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_almacenp: data.Cve_Almac,
                action: "loadPorAlmacen"
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) 
                {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/cortinaentrada/update/index.php',
            success: function(data) {
                var options = $("#zonaRecepción");
                options.empty();
                options.append(new Option("Seleccione", ""));
                for (var i = 0; i < data.zonas.length; i++) 
                {
                    options.append(new Option(data.zonas[i].desc_ubicacion, data.zonas[i].cve_ubicacion));
                }
                changeColorTable();//Cambia el color de las tablas relacionanado lo que espera recibir y lo que ha recibido
            }
        });
    }

  
    //$('#numeroOC').change(cambioNumeroOC);

    function changeColorTable()
    {
        var ids2 = $("#grid-tabla2").jqGrid('getDataIDs'), totalRe = 0, totalEs = 0;
        for (var i = 0; i < ids2.length; i++) 
        {
            var rowId2 = ids2[i];
            var rowData2 = $('#grid-tabla2').jqGrid('getRowData', rowId2);
            var recibido = getArti(rowData2.clave), total = parseInt(rowData2.cantidad);

            totalRe = totalRe + recibido;
            totalEs = totalEs + total;

            if(recibido === 0)
            {
                changeColor(rowId2, "#F74949");
            }
            else if(recibido === total)
            {
                changeColor(rowId2, "#45F842"); 
            }
            else
            {
                changeColor(rowId2, "#FAF44C");
            }
        }
        document.getElementById('label-table-1').innerHTML = "Productos recibidos. ( TOTAL RECIBIDOS "+totalRe+" )";
        document.getElementById('label-table-2').innerHTML = "Productos esperados por la Devolución. ( TOTAL ESPERADOS "+totalEs+" )";
}
    $("#nlote").keyup(function(e) {
        var claveCode = $(this).val();
        var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,20}$");
        if (claveCodeRegexp.test(claveCode)) 
        {
            $("#CodeMessage").html("");
            $("#btnSave").prop('disabled', false);

        } 
        else 
        {
            $("#CodeMessage").show();
            $("#CodeMessage").html("Por favor, ingresar una Clave de lote válida");
            $("#btnSave").prop('disabled', true);
        }
    });

    $('#nlote').change(function(e) {
        $("#CodeMessage").hide();
    });  

    $('#nlote').focus(function(e) {
        $("#CodeMessage").hide();
        $("#nserief").hide();
        $("#lotef").hide();
    });  

    $('#lote').focus(function(e) {
        $("#CodeMessage").hide();
        $("#nserief").hide();
    });  

    $('#nserie').focus(function(e) {
        $("#nlotef").hide();
        $("#lotef").hide();
    });  

    $('#nlote').focusout(function(e) {
        if ($('#nlote').val()==""){
            $("#CodeMessage").show();
            //$("#nserief").show();
            $("#lotef").show();
        }
    });  

    $('#lote').focusout(function(e) {
        if ($('#lote').val()==""){
            $("#CodeMessage").show();
            //$("#nserief").show();
            $("#lotef").show();
        }
    }); 

    $('#nserie').focusout(function(e) {
        if ($('#nserie').val()==""){
            //$("#nlotef").show();
            //$("#lotef").show();
        }
    }); 

    $('#umedida').change(function(e) {
        $("#cantrecib").val(0);
        $("#ctotal").val(0);
    });


    function Solo_Numerico(variable) 
    {
        Numer = parseInt(variable);
        if (isNaN(Numer)) {return "";}
        return Numer;
    }

    function ValNumero(Control) 
    {
        Control.value = Solo_Numerico(Control.value);
    }

    function ReloadGrid() 
    {
        console.log("fecha_inicio:", $("#fecha_ini").val());
        console.log("fecha_fin:", $("#fecha_fin").val());
        $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    almacen: $("#almacen_select").val(),
                    pedido: $("#pedidos_select").val(),
                    diao: $("#diao").val(),
                    fecha_inicio: $("#fecha_ini").val(),
                    fecha_fin: $("#fecha_fin").val(),
                    cliente: $("#clientes_pedidos").val(),
                    id_ruta: $("#rutas_select").val(),
                    cve_proveedor: $("#cve_proveedor").val()
                },
                datatype: 'json',
                page: 1
            })
            .trigger('reloadGrid', [{
                current: true
            }]);
    }

    function ReloadGrid1() 
    {
        $('#grid-table2').jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    criterio: $("#txtCriterio2").val(),
                },
                datatype: 'json',
                page: 1
            })
            .trigger('reloadGrid', [{
                current: true
            }]);
    }

    function downloadxml(url) 
    {
        var win = window.open(url, '_blank');
        win.focus();
    }

    function viewPdf(url) 
    {
        var win = window.open(url, '_blank');
        win.focus();
    }

    $modal0 = null;

    function borrar(_codigo) 
    {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                ID_Aduana: _codigo,
                action: "delete"
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) 
                {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/devclientes/update/index.php',
            success: function(data) {
                if (data.success == true) 
                {
                    ReloadGrid();
                }
            }
        });
    }

    function selectarticulo() 
    {
        $("#modaltitle").html('<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><i class="fa fa-laptop modal-icon"></i><h4 class="modal-title">Agregar Proveedores</h4>');
        $modal0 = $("#myModal");
        $modal0.modal('show');
        l.ladda('stop');
        $("#hiddenAction").val("add");
        $("#btnCancel").show();
        $("#nombre_proveedor").val("");
        $("#contacto").val("");
    }


    function quehizo() 
    {
        borro = localStorage.getItem('borro');
        guardo = localStorage.getItem('guardo');
        var quehizo;
        if (guardo > 0 && borro > 0) 
        {
            quehizo = 'Agrego y borro producto(s)';
        } 
        else if (guardo > 0 && borro < 1) 
        {
            quehizo = 'Agrego producto(s)';
        } 
        else if (guardo < 1 && borro > 0) 
        {
            quehizo = 'Borro producto(s)';
        }
        return quehizo;
    }


    $('#data_2 .input-group.date').datepicker({
        todayBtn: "linked",
        keyboardNavigation: false,
        forceParse: false,
        calendarWeeks: true,
        autoclose: true,
        format: 'dd-mm-yyyy',
        'default': 'now',
        startDate: new Date()
    }).datepicker("setDate", new Date()).datepicker('option', 'minDate', new Date());

    $('.clockpicker').clockpicker({
        autoclose: true,
        'default': DisplayCurrentTime(),
        twelvehour: true,
    }).find('input').val(DisplayCurrentTime());

    function DisplayCurrentTime() 
    {
        var date = new Date();
        var hours = date.getHours() > 12 ? date.getHours() - 12 : date.getHours();
        var am_pm = date.getHours() >= 12 ? "PM" : "AM";
        hours = hours < 10 ? "0" + hours : hours;
        var minutes = date.getMinutes() < 10 ? "0" + date.getMinutes() : date.getMinutes();
        var seconds = date.getSeconds() < 10 ? "0" + date.getSeconds() : date.getSeconds();
        time = hours + ":" + minutes + ":" + am_pm;
        return time;
    };


    $('#foliorl').keyup(function(e) { // text written
        $("#fechaentrada").prop("disabled", true);
        $("#hora").prop("disabled", true);
    });

    $('#foliocd').keyup(function(e) { // text written
        $("#fechaentrada").prop("disabled", true);
        $("#hora").prop("disabled", true);
        cargarTodo($('#foliocd').val());
    });

    $('#protocolo').change(function(e) {
        var ID_Protocolo = $(this).val();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                ID_Protocolo: ID_Protocolo,
                action: "getConsecutivo"
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) 
                {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/devclientes/update/index.php',
            success: function(data) {
                $('#cprotocolo').val(data.consecutivo);
            }

        });
    });


    function clock() { // We create a new Date object and assign it to a variable called "time".
        var time = new Date(),
            hours = time.getHours(),
            minutes = time.getMinutes(),
            seconds = time.getSeconds();

        if (hours > 12) 
        {
            taime = "PM";
            hours = hours - 12;
        } 
        else
        {
            taime = "AM";
        }

        $('#horafin').val(harold(hours) + ":" + harold(minutes) + taime);

        function harold(standIn) 
        {
            if (standIn < 10) 
            {
                standIn = '0' + standIn
            }
            return standIn;
        }
    }
    setInterval(clock, 1000);

    var auto;
    //ESTO HACE UN FLOOP 

    $(document).ready(function() {
        localStorage.clear();

        var date;
        date = new Date();
        date = date.getFullYear() + '-' +
            ('00' + (date.getMonth() + 1)).slice(-2) + '-' +
            ('00' + date.getDate()).slice(-2) + ' ' +
            ('00' + date.getHours()).slice(-2) + ':' +
            ('00' + date.getMinutes()).slice(-2) + ':' +
            ('00' + date.getSeconds()).slice(-2);
        $("#fechaentrada").val(date);
        $("#fechainicio").val(date);
        $("#checkOC").prop('checked', true);
        $("#checkRL").prop('checked', false);
        $("#checkCD").prop('checked', false);

        $("#numeroOC").prop('disabled', false);
        $("#foliorl").prop('disabled', true);
        $("#foliocd").prop('disabled', true);
        $("#foliorl").val('');
        $("#foliocd").val('');
        $("#almacenf").hide();
        $("#almacenfoc").show();
        $("#cprotocolof").hide();
        $("#protocolof").hide();
        $("#proveedor").prop('disabled', true);
        $("#hiddenAction").val("add");

        $modal0 = $("#coModal");
        $modal0.modal('toggle');
        $("#_title").html('<h3>Devolución de Clientes | Rutas</h3>');

        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeOutRight");
        $('#list').hide();

        $('#FORM').show();
        $('#FORM').removeAttr('class').attr('class', '');
        $('#FORM').addClass('animated');
        $('#FORM').addClass("fadeInRight");
        $("#basic").select2("val", "");

        $("#hiddenAction").val("add");
        $("#fechaentrada").trigger("change");
    });

    $("#txtCriterio").keyup(function(event) {
        if (event.keyCode == 13) 
        {
            $("#buscarA").click();
        }
    });


    function cargarOrden(_codigo) 
    {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                action: "load",
                codigo: _codigo
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
            },
            url: '/api/devclientes/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    $("#grid-tabla2").jqGrid("clearGridData");
                    console.log("data.detalle.leght", data.detalle.length);
                    console.log("cant_max = ", window.app.articulo.cantidad_pedida);
                    console.log("ucaja_val = ", $("#ucaja_val").val());
                    console.log("c_peso_val = ", $("#c_peso_val").val());

                    for (var i = 0; i < data.detalle.length; i++) 
                    {
                        var cant = data.detalle[i].cantidad;

                        if($("#ucaja_val").val() > 0 && $("#c_peso_val").val() == 'N' && $("#umed").val() == 2)
                            cant = $("#cantidad_recibida").val();

                        emptyItem = [{
                            clave: data.detalle[i].cve_articulo,
                            desc: data.detalle[i].des_articulo,
                            cantidad: cant,
                            costo: data.detalle[i].costo
                        }];
                        $("#grid-tabla2").jqGrid('addRowData', 0, emptyItem);
                        console.log("Aqui Carga los articulos que se esperan de la Devolución");
                    }
                    var options = $("#articulo");
                    options.empty();
                    options.append(new Option("Seleccione", ""));
                    for (var i = 0; i < data.sinCompletar.length; i++) 
                    {
                        options.append(new Option("(" + data.sinCompletar[i].cve_articulo + ") " + data.sinCompletar[i].des_articulo, data.sinCompletar[i].id));
                    }
                }
            }
        });
    }
</script>

<script type="text/javascript">

$(function($) 
{
    $("#orden_de_compra").change(function()
    {
        console.log("Valor orden_de_compra", $(this).val());


        $.ajax({
            type: "POST",
            //dataType: "json",
            url: '/api/devclientes/update/index.php',
            data: {
                action: 'getDetallesFolio',
                folio: $(this).val()
            },
            beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
            },
            success: function(data) 
            {
                //console.log("datos OC", data);
                //console.log("datos OC Select", data.rows[0]);
                window.app.almacenes = data.almacenes;
                //$("#articulos_lista").append(data.rows[0]);
            },
            error: function(data) 
            {
                console.log("error", data);
            }
        });

    });
/*
    $("#articulos_lista").change(function()
    {
        console.log("articulos_lista = ", $(this).val());

        $.ajax({
            type: "POST",
            //dataType: "json",
            url: '/api/devclientes/update/index.php',
            data: {
                action: 'getUnidadesCaja',
                cve_articulo: $(this).val()
            },
            beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
            },
            success: function(data) 
            {
                console.log("SUCCESS = ", data);

                if(data.control_peso == 'N')
                {
                    $("#umed").children('option[value="1"]').show();//attr('disabled', false);
                    $("#umed").children('option[value="2"]').show();//attr('disabled', false);

                    //if(data.num_multiplo > 0)
                    //    $("#umed").val("2");
                    //else 
                    //    $("#umed").val("");
                }
                else
                {
                    $("#umed").children('option[value="1"]').hide();//attr('disabled', true);
                    $("#umed").children('option[value="2"]').hide();//attr('disabled', true);
                }
                $("#ucaja_val").val(data.num_multiplo);
                $("#c_peso_val").val(data.control_peso);

                //this.articulo.cantidad_pedida = this.ordenes[this.numero_interno_orden].articulos[j].cantidad;

                //console.log("cant_max = ", $("#cant_max").val());
//                console.log("cant_max = ", window.app.articulo.cantidad_pedida);
//                console.log("ucaja_val = ", $("#ucaja_val").val());
//                console.log("c_peso_val = ", $("#c_peso_val").val());
//
//                if($("#ucaja_val").val() > 0 && $("#c_peso_val").val() == 'N')
//                var cant = data.detalle[i].cantidad
//                {
//                    var val_max = window.app.articulo.cantidad_pedida;
//                    val_max = parseFloat(val_max) / parseFloat($("#ucaja_val").val());
//                    val_max = parseInt(val_max);
//                    console.log("val_max = ", val_max);
//                    window.app.articulo.cantidad_pedida = val_max;
//                }
//
            },
            error: function(data) 
            {
                console.log("error", data);
            }
        });
*/
        /*
        var valores = $(this).val();
        var valores_array = valores.split("*-*");
        console.log("ID_Aduana", valores_array[0]);
        console.log("clave", valores_array[1]);
        console.log("pedidas", valores_array[2]);
        console.log("lote", valores_array[3]);
        console.log("serie", valores_array[4]);
        console.log("caducidad", valores_array[5]);

        //app.set_articulo("'"+valores_array[1]+"'");

        if(valores)
            $("#label-cant-max #cant_recOC").text(valores_array[2]);
        else 
            $("#label-cant-max #cant_recOC").text("");

        if(valores_array[4])//maneja serie
        {
            $("#cantidadRecibidaOC input").val(valores_array[2]);
            $("#cantidadRecibidaOC input").prop("disabled", true);
            $("#nlotef").hide();
            $("#nserief").show();
            $("#nserief input").val(valores_array[4]);
            $("#nserief input").prop("disabled", true);
            $("#data_1").hide();
            $("#data_1 #caducidad").val("");
            $("#data_1 #caducidad").prop("disabled", false);
        }
        else if(valores_array[3])//maneja lote
        {
            console.log("Lote ON");
            $("#cantidadRecibidaOC input").val("");
            $("#cantidadRecibidaOC input").prop("disabled", false);
            $("#nlotef").show();
            $("#nserief").hide();
            $("#nlotef input").val(valores_array[3]);
            $("#nlotef input").prop("disabled", true);
            if(valores_array[5])
            {
                console.log("caducidad ON");
                $("#data_1").show();
                var data_caducidad = valores_array[5].split("-");
                $("#data_1 #caducidad").val(data_caducidad[2]+"-"+data_caducidad[1]+"-"+data_caducidad[0]);
                $("#data_1 #caducidad").prop("disabled", true);
            }
            else
            {
                $("#data_1").hide();
                $("#data_1 #caducidad").val("");
                $("#data_1 #caducidad").prop("disabled", false);
            }
        }
        else
        {
            $("#cantidadRecibidaOC input").val("");
            $("#cantidadRecibidaOC input").prop("disabled", false);
            $("#nlotef").hide();
            $("#nserief").hide();
            $("#nserief input").val("");
            $("#nserief input").prop("disabled", false);
            $("#nlotef input").val("");
            $("#nlotef input").prop("disabled", false);
            $("#data_1 #caducidad").hide();
            $("#data_1 #caducidad").val("");
            $("#data_1 #caducidad").prop("disabled", false);
        }
        
    });
*/


    $("#almacen_select").change(function(){

        console.log("almacen_select = ", $(this).val());
        //zona_recepcion_select
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/devclientes/update/index.php',
                data: {
                    action: 'traer_zonas',
                    almacen: $(this).val()
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    if (data.success == true) 
                    {
                        console.log("ZONAS = ", data.zonas);
                        window.app.zonas = data.zonas;
                    }
                },
                error: function(data) 
                {
                    window.console.log(data);
                }
            });

    });

});

    $('#data_1 .input-group.date').datetimepicker({
        locale: 'es',
        format: 'DD-MM-YYYY',
        useCurrent: false
    });

    //VUE EDG
    /*
window.app = new Vue({
    el: '#app_recibir_articulo',
    data: 
    {
        almacen_principal: '',
        numero_interno_orden: '',
        almacenes: [],
        ordenes: [],
        zonas: [],
        contenedores: [],
        articulos: [],
        articulos_recibidos: [],
        articulos_todos: [],
        articulos_con_lotes: [],
        articulos_con_series: [],
        articulos_con_peso: [],
        proveedores: [],
        lotes: [],
        medidas: [],
        orden:{
            id:'',
            articulos: [],
            status:'',
            clave_usuario:'',
            nombre_usuario:'',
        },
        articulo:{
            cve_articulo:'',
            descripcion:'',
            serie:'',
            lote:'',
            caducidad:'',
            peso: '',
            costo:'',
            cantidad_pedida:'',
            cantidad_por_recibir:'',
            maneja_lotes: 0,
            maneja_caducidad: 0,
            maneja_series: 0,
            maneja_peso: 0,
            zona_recepcion: '',
            costo_total: 0,
            hora_de_recepcion:'',
            se_crea_lote: 0,
            lote_creado: 0,
            contenedor: '',
            maneja_contenedor: 0,
            multiplo: '',
            id_con: '',
            perma: '',
            pallet: '',
            pesomax:'',
            volumen:'',
            unidad_medida:'',
        },
        proveedor:{
            id:'',
            Nombre:''
        },
        tipo_entrada: 1,
        costo_calculado: '',
        lote_existente: '',
        hora_actual:'',
        zona:{
            cve_ubicacion: '',
            desc_ubicacion: '',
        },
        con:{
            permanente: '',
            id: '',
            clave_contenedor: '',
            descripcion: '',
            volu:'',
        },
        med:{
            id_umed:'',
            descripcion:'',
        },
        folio_recepcion:'',
    },
    methods: 
    {
        f_recibir: function(event) 
        {
            agregar_linea();
            console.log("agregar_linea");
        },
      
        mayusculas: function()
        {
            this.articulo.lote = this.articulo.lote.toUpperCase()
            this.articulo.serie = this.articulo.serie.toUpperCase()
        },
      
        traer_almacenes: function()
        {
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/devclientes/update/index.php',
                data: {
                    action: 'traer_almacenes',
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    if (data.success == true) 
                    {
                        console.log(data.almacenes);
                        window.app.almacenes = data.almacenes;

                    }
                },
                error: function(data) 
                {
                    window.console.log(data);
                }
            });
        },
      
        traer_zonas: function()
        {
            console.log("traer_zonas = ", this.almacen_principal);
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/devclientes/update/index.php',
                data: {
                    action: 'traer_zonas',
                    almacen: this.almacen_principal,
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    if (data.success == true) 
                    {
                        console.log(data.zonas);
                        window.app.zonas = data.zonas;
                    }
                },
                error: function(data) 
                {
                    window.console.log(data);
                }
            });
        },
      
        traer_contenedores: function()
        {
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/devclientes/update/index.php',
                data: {
                    action: 'traer_contenedores',
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    if (data.success == true) 
                    {
                      console.log(data.contenedores);
                      window.app.contenedores = data.contenedores;
                    }
                },
                error: function(data) 
                {
                    window.console.log(data);
                }
            });
        },
      
         traer_medidas: function()
        {
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/devclientes/update/index.php',
                data: {
                    action: 'traer_medidas',
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    if (data.success == true) 
                    {
                      console.log("medidas",data.medidas);
                      window.app.medidas = data.medidas;
                    }
                },
                error: function(data) 
                {
                    window.console.log(data);
                }
            });
        },
      
        traer_ordenes: function()
        {
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/devclientes/update/index.php',
                data: {
                    action: 'traer_ordenes',
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    if (data.success == true) 
                    {
                        console.log("ok",data);
                        window.app.ordenes = data.ordenes;
                    }
                },
                error: function(data) 
                {
                    console.log("no",data);
                    window.console.log(data);
                }
            });
        },
      
        traer_proveedores: function()
        {
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/devclientes/update/index.php',
                data: {
                    action: 'traer_proveedores',
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    if (data.success == true) 
                    {
                        console.log("ok",data);
                        window.app.proveedores = data.proveedores;
                    }
                },
                error: function(data) 
                {
                    console.log("no",data);
                    window.console.log(data);
                }
            });
        },

        traer_articulos: function()
        {
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/devclientes/update/index.php',
                data: {
                    action: 'traer_todos_los_articulos',
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    if (data.success == true) 
                    {
                        window.app.articulos_todos = data.todos_los_articulos;
                        window.app.articulos_con_lotes = data.articulos_con_lotes;
                        window.app.articulos_con_series = data.articulos_con_series;
                        window.app.articulos_con_peso = data.articulos_con_peso;

                    }
                },
                error: function(data) 
                {
                    window.console.log(data);
                }
            });
        },
      
        traer_lotes: function()
        {
            console.log("traer lotes");
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/devclientes/update/index.php',
                data: {
                    action: 'traer_lotes',
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    if (data.success == true) 
                    {
                        console.log("traer lotes2");
                        window.app.lotes = data.lotes;
                    }
                },
                error: function(data) 
                {
                    window.console.log(data);
                }
            });
        },
      
        set_articulo(id)
        {
            //console.log("Set Artículo", id);
            this.reset_articulo();
            var ucaja = '';
            if(this.tipo_entrada == 1)
            {
                for (var i = 0; i < this.articulos.length; i++) 
                {
                    if(this.articulos[i].cve_articulo == id)
                    {
                        this.articulo.cve_articulo = id;
                        this.articulo.descripcion = this.articulos[i].nombre_articulo;
                        this.articulo.costo = this.articulos[i].costo;
                        this.articulo.unidad_medida = this.articulos[i].unidadMedida;
                        this.articulo.multiplo = this.articulos_todos[i].num_multiplo;
                        this.articulo.volumen = (this.articulos_todos[i].ancho / 1000) * (this.articulos_todos[i].alto / 1000) * (this.articulos_todos[i].fondo / 1000);
                        ucaja = this.articulos_todos[i].num_multiplo;
                        this.comparativa();
                        //console.log("ucaja 1 = ", ucaja);
                    }
                }
                for (var j = 0; j < this.ordenes[this.numero_interno_orden].articulos.length; j++) 
                {
                    if(this.ordenes[this.numero_interno_orden].articulos[j].cve_articulo == this.articulo.cve_articulo)
                    {
                        this.articulo.cantidad_pedida = this.ordenes[this.numero_interno_orden].articulos[j].cantidad;
                    }
                }
                console.log("this.articulo.cantidad_pedida = ", this.articulo.cantidad_pedida);
                //this.articulo.cantidad_pedida = 2222;
                $("#cant_max").val(this.articulo.cantidad_pedida);
            }
            else
            {
                for (var i = 0; i < this.articulos_todos.length; i++) 
                {
                    if(this.articulos_todos[i].cve_articulo == id)
                    {
                        this.articulo.cve_articulo = id;
                        this.articulo.descripcion = this.articulos_todos[i].des_articulo;
                        this.articulo.costo = this.articulos_todos[i].costo;
                        this.articulo.unidad_medida = this.articulos_todos[i].unidadMedida;
                        this.articulo.multiplo = this.articulos_todos[i].num_multiplo;
                        this.articulo.volumen = (this.articulos_todos[i].ancho / 1000) * (this.articulos_todos[i].alto / 1000) * (this.articulos_todos[i].fondo / 1000);
                        console.log("vol",this.articulo.volumen);
                        ucaja = this.articulos_todos[i].num_multiplo;
                        this.comparativa();
                        console.log("ucaja 2= ", ucaja);
                    }
                }  
            }


            if(ucaja > 0)
                $("#umed").val("2");
            else
                $("#umed").val("");

            console.log("cant_max 0 = ", this.articulo.cantidad_pedida);
            console.log("ucaja_val 0 = ", $("#ucaja_val").val());
            console.log("c_peso_val 0 = ", $("#c_peso_val").val());

        },
      
        set_orden(id)
        {
            if(this.tipo_entrada == 1)
            { 
                for (var i = 0; i < this.ordenes.length; i++) 
                {
                    if(this.ordenes[i].Fol_folio == id)
                    {
                        this.articulos = this.ordenes[i].articulos;
                        this.proveedor.id = this.ordenes[i].ID_Proveedor;
                        this.proveedor.Nombre = this.ordenes[i].nombre_proveedor;
                        this.orden.status = this.ordenes[i].status;
                        this.numero_interno_orden = i;
                    }
                }
                this.colores_tabla();
            }
        },

        set_proveedores(id)
        {
            for (var i = 0; i < this.proveedores.length; i++) 
            {
                if(this.proveedores[i].ID_Proveedor == id)
                {
                    this.proveedor.id = this.proveedores[i].ID_Proveedor;
                    this.proveedor.Nombre = this.proveedores[i].Nombre;
                }
            }
        },

        set_zona(id)
        {
            for (var i = 0; i < this.zonas.length; i++) 
            {
                if(this.zonas[i].cve_ubicacion == id)
                {
                    this.zona.cve_ubicacion = id;
                    this.zona.desc_ubicacion = this.zonas[i].desc_ubicacion;
                    this.zona.clave = this.zonas[i].clave;
                    this.articulo.zona_recepcion = this.zona.desc_ubicacion;
                }
            }
        },

        set_con(id)
        {
             for (var i = 0; i < this.contenedores.length; i++) 
             {
                 if(this.contenedores[i].clave_contenedor == id)
                 {
                     this.con.clave_contenedor = id;
                     this.con.descripcion = this.contenedores[i].descripcion;
                     this.articulo.contenedor = this.con.clave_contenedor;
                     this.con.id = this.contenedores[i].IDContenedor;
                     this.articulo.id_con = this.con.id;
                     this.con.permanente = this.contenedores[i].tipo;
                     this.articulo.perma = this.con.permanente;
                     this.articulo.pesomax = this.contenedores[i].pesomax;
                     this.con.volu = this.contenedores[i].capavol;
                 }
             }
        },

        set_med(id)
        {
            for (var i = 0; i < this.medidas.length; i++) 
            {
                 if(this.medidas[i].id_umed == id)
                 {
                    this.med.id_umed = id;
                    this.med.descripcion = this.medidas[i].des_umed;
                   
                }
            }
        },
      
        clone: function(item)
        {
            return JSON.parse(JSON.stringify(item));
        },
      
        reset_articulo: function()
        {
            this.articulo.cve_articulo = "";
            this.articulo.descripcion = "";
            this.articulo.serie = "";
            this.articulo.lote = "";
            this.articulo.caducidad = "";
            this.articulo.costo = "";
            this.articulo.cantidad_pedida = "";
            this.costo_calculado = "";
            this.articulo.cantidad_por_recibir = "";
            this.articulo.maneja_lotes = "";
            this.articulo.maneja_caducidad = "";
            this.articulo.maneja_series = "";
            this.articulo.costo_total = "";
        },
      
        set_almacenprede: function()
        {
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/almacenPredeterminado/index.php',
                data: {
                    action: 'search_almacen_pre',
                    idUser: '<?php echo $_SESSION["id_user"]?>',
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    if (data.success == true) 
                    {
                        window.app.almacen_principal = data.codigo.clave;
                        window.app.almacenes = data.codigo;
                        window.app.traer_zonas();
                    }
                },
                error: function(data) 
                {
                    window.console.log(data);
                }
            });
        },
      
        set_usuario: function()
        {
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/almacenPredeterminado/index.php',
                data: {
                    action: 'search_usuario',
                    idUser: '<?php echo $_SESSION["id_user"]?>',
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    if (data.success == true) 
                    {
                        window.app.orden.clave_usuario = data.data.cve_usuario;
                        window.app.orden.nombre_usuario = data.data.nombre_completo;
                    }
                },
                error: function(data) 
                {
                  window.console.log(data);
                }
            });
        },
      
        calcular_costo: function()
        { 
            console.log("1",this.articulo.cantidad_por_recibir);
            console.log("2",this.articulo.costo);
            if(this.articulo.costo != 0.00)
            {
                this.costo_calculado =  parseFloat(this.articulo.cantidad_por_recibir) * parseFloat(this.articulo.costo);
                console.log("3",this.costo_calculado);
            }
            
                
        },

        recibir_un_articulo: function()
        {
            this.articulo.hora_de_recepcion = this.hora_actual;
            if(this.articulo.cve_articulo == "" )
            {
                swal("Error","Por favor Selecione Un Articulo", "error");
                return;
            }
          
            if(this.articulo.cantidad_por_recibir == "" || this.articulo.cantidad_por_recibir == 0)
            {
                swal("Error","Debe recibir por lo menos 1 articulo", "error");
                return;
            }
          
            if(parseFloat(this.articulo.cantidad_por_recibir) > parseFloat(this.articulo.cantidad_pedida))
            {
                swal("Error","La cantidad por recibir excede la cantidad solicitada", "error");
                return;
            }
          
            if(this.articulo.maneja_lotes == 1)
            {
                if(this.articulo.lote == "")
                {
                    swal("Error","Debe introducir un lote, ya que el articulo "+"("+this.articulo.cve_articulo+")"+" maneja lotes", "error");
                    return;
                }
            }
          
            if(this.articulo.maneja_caducidad == 1)
            {
                if(this.articulo.caducidad == "")
                {
                    swal("Error","Debe introducir una caducidad, ya que el articulo "+"("+this.articulo.cve_articulo+")"+" maneja caducidad", "error");
                    return;
                }
            }
          
            if(this.articulo.maneja_series == 1)
            {
                if(this.articulo.serie == "")
                {
                    swal("Error","Debe introducir un numero de serie, ya que el articulo "+"("+this.articulo.cve_articulo+")"+" maneja numero de serie", "error");
                    return;
                }
            }
          
            if(this.proveedor.id == "")
            {
                swal("Error","Debe seleccionar un Proveedor", "error");
                return;
            }
          
            if(this.articulo.zona_recepcion == "")
            {
                swal("Error","Debe seleccionar una zona de recepcion", "error");
                return;
            }
          
            if(this.articulo.se_crea_lote == 1)
            {
                if(window.app.crear_lote(this.articulo.cve_articulo, this.articulo.lote, this.articulo.caducidad))
                {
                    console.log("Se ha creado ese lote");
                    this.traer_lotes();
                }
            }
          
            if(this.articulo.maneja_peso == 1)
            {
                this.articulo.peso = this.articulo.cantidad_por_recibir;
            }
          
            if(this.articulo.maneja_peso == 0)
            {
                this.articulo.peso = "";
            }  
            if(this.articulo.contenedor != "")
            {
                if(this.articulo.perma != 'Contenedor')
                {  
                    var a =  new String (this.con.id);
                    var b = a.padStart(6,0);
                    this.articulo.pallet = "LP" + "" +b+ "" + "" +this.folio_recepcion+ "";
                    console.log("this.articulo.pallet",this.articulo.pallet);
                }
                else
                {
                    this.articulo.pallet = "";
                }
            }
          
            if(this.articulo.volumen > this.con.volu && this.articulo.contenedor != "")
            {
               swal("Error","El volumen es mayor al permitido", "error");
               return;
            }

            if(this.articulo.unidad_medida == "")
            {
                swal("Error","Debe seleccionar una Unidad de Medida", "error");
                return;
            }

            if($("#ucaja_val").val() > 0 && $("#c_peso_val").val() == 'N' && $("#umed").val() == 2)
            {
                this.articulo.cantidad_por_recibir = $("#cantidad_recibida").val();
                //swal("Error","Debe seleccionar una Unidad de Medida", "error");
                //return;
            }

            if(this.ya_recibido(this.articulo.cve_articulo))
            {
               swal("Exito","El articulo "+"("+this.articulo.cve_articulo+") ha sido actualizado correctamente", "success");
               this.actualizar_articulo(this.articulo.cve_articulo);
               this.reset_articulo();
               return;
            }
            console.log("voy a actualizar articulo");
           this.articulo.costo_total = this.costo_calculado;
           swal("Exito","El articulo "+"("+this.articulo.cve_articulo+") ha sido agregado correctamente", "success");
           this.articulos_recibidos.push(this.clone(this.articulo));
           this.actualizar_articulo(this.articulo.cve_articulo);
           this.reset_articulo();          
       },
      
       comparativa: function()
       {
           this.articulo.maneja_lotes = 0;
           this.articulo.maneja_caducidad = 0;
           this.articulo.maneja_series = 0;
           this.articulo.maneja_peso = 0;
           this.articulo.maneja_contenedor = 0;
           for (var i = 0; i < this.articulos_con_lotes.length; i++) 
           {
               if(this.articulos_con_lotes[i].cve_articulo == this.articulo.cve_articulo)
               {
                    this.articulo.maneja_lotes = 1;
                    if(this.articulos_con_lotes[i].Caduca == 'S')
                    {
                        this.articulo.maneja_caducidad = 1;
                    }
               }
          }
          for (var i = 0; i < this.articulos_con_series.length; i++) 
          {
              if(this.articulos_con_series[i].cve_articulo == this.articulo.cve_articulo)
              {
                  this.articulo.maneja_series = 1;
                  this.articulo.cantidad_por_recibir = 1;
              }
          }
          for (var i = 0; i < this.articulos_con_peso.length; i++) 
          {
              if(this.articulos_con_peso[i].cve_articulo == this.articulo.cve_articulo)
              {
                  this.articulo.maneja_peso = 1;
              }
          }
          for (var i = 0; i < this.contenedores.length; i++) 
          {
              if(this.contenedores[i].clave_contenedor == this.articulo.contenedor)
              {
                  this.articulo.maneja_contenedor = 1;
              }
          }
      },
      
      revisar_lote_existente(lote)
      {
          this.articulo.se_crea_lote = 0;
          this.lote_existente = '';
          this.articulo.caducidad  = '';
          for(var i = 0; i < this.lotes.length; i++)
          {
              if(lote == this.lotes[i].LOTE && this.lotes[i].cve_articulo == this.articulo.cve_articulo)
              {
                  if(this.articulo.maneja_lotes == 1)
                  {
                      this.lote_existente = "Este Lote ya existe, el producto "+"("+this.articulo.cve_articulo+")"+"'"+this.articulo.descripcion+"'"+" sera añadido a ese lote";
                  }
                  if(this.articulo.maneja_series == 1)
                  {
                      this.lote_existente = "Esta serie ya existe, es necesario colocar una serie diferente";
                  }
                  if(this.articulo.maneja_caducidad == 1)
                  {
                      this.articulo.caducidad = this.lotes[i].Caducidad;
                  }
              }
          }
          if(this.lote_existente =="")
          {
              this.articulo.se_crea_lote = 1;
              console.log("Se creara lote", this.articulo.se_crea_lote);
          }
      },
      
      borrar_articulo_recibido(cve_articulo)
      {
          for(var i = this.articulos_recibidos.length - 1; i >= 0; i--) 
          {
              if(this.articulos_recibidos[i].cve_articulo == cve_articulo) 
              {
                  this.articulos_recibidos.splice(i, 1);
              }
          }
      },
      
      traer_hora_actual: function()
      {
          $.ajax({
              type: "POST",
              dataType: "json",
              url: '/api/devclientes/update/index.php',
              data: {
                  action: 'hora_actual',
              },
              beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
              },
              success: function(data) 
              {
                  if(data.success == true) 
                  {
                      window.app.hora_actual = data.hora_actual;
                      window.app.recibir_un_articulo();
                  }
              },
              error: function(data) 
              {
                  window.console.log(data);
              }
          });
      },
      traer_hora_fin: function()
      {
          $.ajax({
              type: "POST",
              dataType: "json",
              url: '/api/devclientes/update/index.php',
              data: {
                  action: 'hora_actual',
              },
              beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
              },
              success: function(data) 
              {
                  if(data.success == true) 
                  {
                      window.app.hora_actual = data.hora_actual;
                      window.app.guardar_entrada();
                  }
              },
              error: function(data) 
              {
                  window.console.log(data);
              }
          });
      },

      traer_folio_R: function()
      {
          $.ajax({
              type: "POST",
              dataType: "json",
              url: '/api/devclientes/update/index.php',
              data: {
                  action: 'traer_folio_R',
              },
              beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
              },
              success: function(data) 
              {
                  if(data.success == true) 
                  {
                      console.log("folio libre", data.data)
                      window.app.folio_recepcion = data.data;
                  }
              },
              error: function(data) 
              {
                  window.console.log(data);
              }
          });
      },

      actualizar_articulo(cve_articulo)
      {
        console.log("Entró en actualizar articulo");
          for(var i = 0; i < this.articulos.length; i++)
          {
              if(this.articulos[i].cve_articulo == cve_articulo)
              {
                  this.articulos[i].cantidad = parseFloat(this.articulos[i].cantidad) - parseFloat(this.articulo.cantidad_por_recibir);
                  if(this.articulos[i].cantidad == 0)
                  {
                      this.articulos[i].color_1 = 1;
                      this.articulos[i].color_2 = 1;
                      //$("#articulos_lista option[value='"+cve_articulo+"']").remove();
                  }
                  else
                  {
                      this.articulos[i].color_1 = 1;
                      this.articulos[i].color_2 = 0;
                  }
              }
          }
          var j = 0;
          console.log("entrar");
          for(var i = 0; i < this.articulos.length; i++)
          { 
              console.log("articulos",this.articulos.length);
              j+= this.articulos[i].cantidad; 
          }
          console.log(j);
          if(j == 0 && this.tipo_entrada == 1)
          {
              console.log("guardar");
              this.guardar_entrada();
          }
      },
      
      ya_recibido(cve_articulo)
      {
          for(var i = 0; i < this.articulos_recibidos.length; i++)
          {
              if(this.articulos_recibidos[i].cve_articulo == cve_articulo && this.articulos_recibidos[i].contenedor == this.articulo.contenedor)
              {
                  if(this.articulos_recibidos[i].lote == this.articulo.lote && this.articulo.serie == "")
                  {
                      this.articulo.costo_total = this.costo_calculado;
                      this.articulos_recibidos[i].cantidad_por_recibir = parseFloat(this.articulos_recibidos[i].cantidad_por_recibir) + parseFloat(this.articulo.cantidad_por_recibir);
                      this.articulos_recibidos[i].costo_total = parseFloat(this.articulos_recibidos[i].costo_total) + parseFloat(this.articulo.costo_total);
                      return true;
                  }
              }
          }
          return false;
      },
      
      colores_tabla: function()
      {
          for(var i = 0; i < this.articulos.length; i++)
          {
              this.articulos[i].color_1 = 0;
              this.articulos[i].color_2 = 0;
          }
      },
      guardar_entrada: function()
      {
          var almacen = this.almacen_principal;
          var oc = this.orden.id;
          var tipo;
          var activos_fijos = [];
          if(this.tipo_entrada == 1) {tipo = "OC";}
          if(this.tipo_entrada == 2) {tipo = "RL";}
          if(this.tipo_entrada == 3) {tipo = "CD";}

          console.log("Tipo", tipo);
        
          var sum = 0;
          for(var i = 0; i < this.articulos_recibidos.length; i++)
          {
              if(this.articulos.tipo_producto === 'Activo Fijo')
              {
                  var total_activos = activos_fijos.push(this.articulos_recibidos[i]);
              }
              else
              {
                  console.log("peso1",this.articulos_recibidos[i].peso);
                  sum+= parseFloat(this.articulos_recibidos[i].peso);
                  console.log("sum",sum);
                  console.log("pesmax",this.articulos_recibidos[i].pesomax);
                  if(sum > parseFloat(this.articulos_recibidos[i].pesomax))
                  {
                      console.log("pes2");
                      swal("Error", "El peso es mayor al soportado por la caja, elimine articulos", "error");
                      return;
                  }
              }
              
          }
        
          if(this.articulos_recibidos.length >= 1)
          {
              if(tipo != "")
              {
                var arrDetalle;
                if (oc === '' && !this.tipo_entrada == 2) 
                {
                  swal("Error", "Seleccione una OC primero", "error");
                }
                else 
                {
                    if(this.tipo_entrada == 2 || this.tipo_entrada == 3) oc = $("#num_oc").val();
                    swal({
                      title: 'Advertencia',
                      text: '¿Está seguro que desea guardar la Recepcion? Ésta acción no se puede deshacer.',
                      type: 'warning',
                      showConfirmButton: true,
                      confirmButtonText: 'Sí',
                      showCancelButton: true,
                      cancelButtonText: 'No',
                      closeOnConfirm: true,
                      closeOnCancel: true
                    }, 
                    function(confirm) 
                    {
                        console.log("confirm = "+confirm);
                        if (confirm) 
                        {
                            console.log("Cve_Almac: "+almacen+" - oc: "+oc+" - tipo: "+tipo+" - arrDetalle: "+window.app.articulos_recibidos+" - Cve_Usuario: "+window.app.orden.clave_usuario+" - usuario: "+window.app.orden.clave_usuario+" - Cve_Proveedor: "+window.app.proveedor.id+" - fechafin: "+window.app.hora_actual+" - STATUS: "+"E"+" - Fact_Prov: "+$("#facprove").val());
                            console.log(window.app.articulos_recibidos);

                            window.app.articulos_recibidos.forEach(function(value, key){
                            if(window.app.articulos_recibidos[key].serie != "")
                            {
                              window.app.articulos_recibidos[key].lote = window.app.articulos_recibidos[key].serie;
                            }
                            if(window.app.articulos_recibidos[key].zona_recepcion != "")
                            {
                              window.app.articulos_recibidos[key].zona_recepcion = window.app.zona.cve_ubicacion;
                              console.log("zonaass",window.app.articulos_recibidos[key].zona_recepcion);
                            }
                         });


                         $.ajax({
                            method: 'POST',
                            dataType: 'json',
                            data: {
                                action: 'receiveOC',
                                Cve_Almac: almacen,
                                oc: oc,
                                tipo: tipo,
                                arrDetalle: window.app.articulos_recibidos,
                                Cve_Usuario: window.app.orden.clave_usuario,
                                usuario: window.app.orden.clave_usuario,
                                Cve_Proveedor: window.app.proveedor.id,
                                fechafin: window.app.hora_actual,
                                STATUS: "E",
                                Fact_Prov: $("#facprove").val()
                            },
                            beforeSend: function(x) {
                                if (x && x.overrideMimeType) 
                                {
                                    x.overrideMimeType("application/json;charset=UTF-8");
                                }
                            },
                            url: '/api/devclientes/update/index.php',
                            success: function(data) {
                                console.log("Entró en data.success out");
                                if (data.success == true) 
                                {
                                      console.log("Entró en data.success");
                                      swal({
                                        title: 'Éxito',
                                        text: `¡La Recepción #${oc} se ha recibido correctamente`,
                                        type: 'success',
                                        showConfirmButton: true,
                                        confirmButtonText: 'Aceptar',
                                        showCancelButton: false,
                                        closeOnConfirm: true,
                                        closeOnCancel: false
                                      }, 
                                      function() 
                                      {
                                          console.log("Reload...");
                                          window.location.reload();
                                      });
                                }
                                else 
                                {
                                    swal("Error", "Ocurrió un error al guardar los datos", "error");
                                }
                            },
                            error: function(res) {
                                window.console.log(res);
                            }/
                            }); ///////////////////.done(function(data) 
                            {
                                console.log("data.success = "+data.success);
                              if(data.success) 
                              {
                                console.log("Entró en data.success");
                              swal({
                                title: 'Éxito',
                                text: `¡La Recepción #${oc} se ha recibido correctamente`,
                                type: 'success',
                                showConfirmButton: true,
                                confirmButtonText: 'Aceptar',
                                showCancelButton: false,
                                closeOnConfirm: true,
                                closeOnCancel: false
                              }, 
                              function() 
                              {
                                  window.location.reload();
                              });
                              } 
                              else 
                              {
                                  swal("Error", "Ocurrió un error al guardar los datos", "error");
                              }
                      });//////////////////////////////
                    }
                  });
                }
              }
            }
            if(activos_fijos.length >= 1)
            {
                $.ajax({
                    method: 'POST',
                    dataType: 'json',
                    url: '/api/devclientes/update/index.php',
                    data: {
                        action: 'activos_fijos',
                        activos: activos_fijos,
                    }
                    });
            }
          },
      crear_lote(cve_articulo, lote, caducidad)
      {
          var lote = window.app.articulo.lote;
          if(lote == ""){lote = window.app.articulo.serie}
          console.log("Lote",lote);
          console.log("Lote - L",window.app.articulo.lote);
          console.log("Lote - S",window.app.articulo.serie);
            $.ajax({
                type: "POST",
                dataType: "json",
                async: false,
                cache: false,
                url: '/api/lotes/update/index.php',
                data: {
                    action: "add",
                    cve_articulo: window.app.articulo.cve_articulo,
                    LOTE:lote,
                    CADUCIDAD: window.app.articulo.caducidad,
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    console.log("GUARDE LOTE NUEVO");
                }
            });
            return true;
          },
          
      recibir_todo: function()
      {
          this.articulos_recibidos = this.articulos;
          for(var i = 0; i < this.articulos.length; i++)
          {
              this.articulos_recibidos[i].cve_articulo = this.articulos[i].cve_articulo;
              this.articulos_recibidos[i].descripcion = this.articulos[i].nombre_articulo;
              this.articulos_recibidos[i].serie = "";
              this.articulos_recibidos[i].lote = "";
              this.articulos_recibidos[i].caducidad = "";
              this.articulos_recibidos[i].cantidad_por_recibir = parseFloat(this.articulos[i].cantidad);
              this.articulos_recibidos[i].zona_recepcion = this.zona.desc_ubicacion;
              this.articulos_recibidos[i].contenedores = "";
              this.articulos_recibidos[i].clave_usuario = "";
              this.articulos_recibidos[i].costo_total = "";
              this.articulos_recibidos[i].hora_de_recepcion = "";
              this.articulos_recibidos[i].tipo = this.articulos[i].tipo_producto;
              if(this.articulos_recibidos[i].cantidad_por_recibir !="")
              {
                  console.log(parseFloat(this.articulos[i].cantidad));
                  this.actualizar_articulo(this.articulos[i].cve_articulo);
              }
          }
      }
  },
  beforeMount()
  {
      this.set_almacenprede();
      this.traer_almacenes();
      this.traer_ordenes();
      this.traer_folio_R();
      this.traer_proveedores();
      this.traer_contenedores();
      this.set_usuario();
      this.traer_articulos();
      this.traer_lotes();
      this.traer_medidas();
      this.orden_reset = this.clone(this.orden);
  }
}); 
*/
/*
    $('.chosen-list').select2({
        theme: "bootstrap4",
        selectOnClose: true
    });
*/
//$('.chosen-select').chosen();
//$('.chosen-select').trigger("chosen:updated");
$('#articulos_conversion').chosen();
$('#articulos_conversion').trigger("chosen:updated");
$('#articulos_conversion_chosen').hide();

</script>