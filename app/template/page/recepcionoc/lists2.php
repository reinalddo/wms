<?php
$listaZR = new \CortinaEntrada\CortinaEntrada();
$listaProvee = new \Proveedores\Proveedores();
$listaAlma = new \Almacen\Almacen();
//$listaArtic = new \Articulos\Articulos();
$listaUser = new \Usuarios\Usuarios();
$listaOC = new \OrdenCompra\OrdenCompra();
$listaAP = new \AlmacenP\AlmacenP();
$listaProto= new \Protocolos\Protocolos();
?>
 
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet" />
<link href="/css/plugins/clockpicker/clockpicker.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
  
<div id="app_recepcion">
  <div class="wrapper wrapper-content  animated fadeInRight" id="FORM">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4" id="_title">
                            <h3>{{titulo}}</h3>
                        </div>
                    </div>
                </div>
                <form id="myform">
                    <div class="ibox-content">
                        <div class="row">
                            <label>Tipo</label>
                            <div class="col-lg-12 text-center">
                                <div class="form-group">
                                    <label class="col-md-4" for="checkboxes-0">
                                        <input type="checkbox" v-model="check_OC.box" id="checkOC" v-on:click="cambiar">
                                        <label>{{check_OC.name}}</label>
                                    </label>
                                    <label class="col-md-4" for="checkboxes-1" >
                                        <input type="checkbox" v-model="check_RL.box" id="checkRL" v-on:click="cambiar">
                                        <label>{{check_RL.name}}</label>
                                    </label>
                                    <label class="col-md-4" for="checkboxes-2" >
                                        <input type="checkbox" v-model="check_CD.box" id="checkCD" v-on:click="cambiar">
                                        <label>{{check_CD.name}}</label>
                                    </label>
                                </div>
                              <br>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6 b-r b-b">
                                <div class="form-group" id="data_2" style="display:none">
                                    <label>Fecha de Recepción</label>
                                    <input disabled id="fechaentrada" type="text" class="form-control" value="">
                                    <input id="fechainicio" type="text" class="form-control" value="" style="display: none">
                                </div>
                                <div class="form-group" id="almacenf" style="display:none">
                                    <label>Almacén*</label>
                                    <select class="form-control" id="almacen">
                                        <option value="">Seleccione</option>
                                        <?php foreach( $listaAP->getAll() AS $a ): ?>
                                            <option value="<?php echo $a->clave; ?>"><?php echo "($a->clave) $a->nombre"; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group" id="almacenfoc" style="display:none">
                                    <label>Almacén*</label>
                                    <select class="form-control" id="almacenoc" class="chosen-select form-control">
                                        <option value="">Seleccione</option>
                                        <?php foreach( $listaAP->getAll() AS $a ): ?>
                                            <option value="<?php echo $a->clave; ?>"><?php echo "($a->clave) $a->nombre"; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Número de Orden de Compra*</label>
                                    <select class="form-control" id="numeroOC">
                                    <option value="">Seleccione Almacén Primero</option>
                                    <?php if(isset($listaOC) && !empty($listaOC)): ?>
                                        <?php foreach( $listaOC->getAllProv() AS $a ):?>
                                            <option value="<?php echo $a->num_pedimento; ?>">[<?php echo $a->num_pedimento."] - ".$a->Nombre; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                </div>
                                <div class="form-group" >
                                    <label>Folio Recepción Libre</label> 
                                    <input id="foliorl" type="text"  placeholder="Folio Recepción Libre" class="form-control">
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
                                <div class="form-group">
                                    <label>Folio Crossdocking</label> 
                                    <input id="foliocd" type="text" placeholder="Folio Crossdocking" class="form-control">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Proveedor</label>
                                    <select class="form-control" id="proveedor">
                                        <option value="">Seleccione</option>
                                        <?php foreach( $listaProvee->getAll() AS $a ): ?>
                                            <option value="<?php echo $a->ID_Proveedor; ?>"><?php echo $a->Nombre; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Factura / Remisión</label> 
                                    <input id="facprove" type="text" placeholder="Factura" class="form-control" >
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
                                <div class="form-group" id="erpOC">
                                    <label>Numero de Orden (ERP)</label>
                                    <select id="select-nerp" class="chosen-select form-control">
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6 b-t">
                                <div class="form-group">
                                    <label>Zona de Recepción*</label>
                                    <select class="form-control" id="zonaRecepción">
                                    <option value="">Seleccione</option>
                                    <?php foreach( $listaZR->getAll() AS $a ): ?>
                                        <option value="<?php echo $a->cve_ubicacion; ?>"><?php echo "$a->cve_ubicacion $a->desc_ubicacion"; ?></option>
                                    <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Articulo*</label>
                                    <select class="form-control" id="articulo">
                                    <option value="">Seleccione OC Primero</option>
                                    <?php if(isset($listaArtic) && !empty($listaArtic)): ?>
                                        <?php foreach( $listaArtic->getAll() AS $a ): ?>
                                            <option value="<?php echo $a->id; ?>"><?php echo "(".$a->cve_articulo.") ".$a->des_articulo; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    </select>
                                    <input type="hidden" id="cve_articulo" />
                                </div>
                                <div class="form-group" id="nlotef" style="display:none">
                                    <label>Nuevo Lote*</label> 
                                    <input id="nlote" type="text" placeholder="Lote" class="form-control"> 
                                    <label id="CodeMessage" style="color:red; display:none;"></label>
                                </div>
                                <div class="form-group" id="lotef" style="display:none">
                                    <label>Lote*</label>
                                    <select class="form-control" id="lote">
                                                        </select>
                                </div>
                                <div class="form-group" id="nserief" style="display:none">
                                    <label>Numero de Serie</label> 
                                    <input id="nserie" type="text" placeholder="Numero de Serie" class="form-control">
                                </div>
                                <div class="form-group" id="cantidadRecibidaOC">
                                    <label id="label-cant-max">Cantidad Recibida*</label> 
                                    <input disabled id="cantrecib" oninput="this.value = this.value.replace(/[^0-9]/g, '');" type="text" placeholder="Cantidad Recibida" class="form-control">
                                </div>

                                <div class="form-group" id="cantidadRecibidaLibre" style="display:none">
                                    <label>Cantidad Recibida*</label> 
                                    <input id="cantidadLibre" type="text" placeholder="Cantidad Recibida" class="form-control">
                                </div>

                                <input id="xpieza" type="hidden" placeholder="Cantidad Recibida" class="form-control">
                                <input id="xcaja" type="hidden" placeholder="Cantidad Recibida" class="form-control">
                                <input id="xpallet" type="hidden" placeholder="Cantidad Recibida" class="form-control">

                            </div>
                            <div class="col-lg-6 b-t">
                                <div class="form-group">
                                    <label>Usuario</label>
                                    <select class="form-control" id="usuario" disabled>
                                        <?php foreach( $listaUser->getAll() AS $a ): ?>
                                        <option value="<?php echo $a->cve_usuario; ?>"><?php echo "(".$a->cve_usuario.") ".$a->nombre_completo; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Unidad de Medida</label>
                                    <select class="form-control" id="umedida">
                                        <option value="1">Por Pieza</option>
                                        <option value="2">Por Caja</option>
                                        <option value="3">Por Pallet</option>
                                    </select>
                                </div>
                                <div class="form-group" id="data_1" style="display:none">
                                    <label>Caducidad*</label>
                                    <div class="input-group date">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="caducidad" type="text" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group" id="cantidadTotalOC">
                                    <label>Cantidad Total</label> 
                                    <input disabled id="ctotal" type="text" placeholder="Cantidad Recibida" class="form-control">
                                </div>
                                <div class="form-group" id="costoEnLibre" style="display:none">
                                    <label>Costo</label> 
                                    <input id="costoEntradaLibre" type="text" placeholder="$ 0.00" class="form-control">
                                </div>
                                <div class="form-group" id="costoEnOC" >
                                    <label>Costo</label> 
                                    <input id="costoOrdenCompra" type="text" placeholder="$ 0.00" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-lg-12" id="botonesOC">
                                <center>
                                    <!--a href="#" id="addrow"><button type="button" class="btn btn-primary" id="btnCancel">Agregar Producto</button></a-->&nbsp;&nbsp;
                                    <button type="button" class="btn btn-success" onclick="receiveOC()">Recibir toda la OC</button></a>&nbsp;&nbsp;
                                    <div id="recibir_articulo"> <!-- VUE EDG-->
                                      <button type="button" class="btn btn-success t-10" v-on:click="f_recibir">{{titulo}}</button></a>&nbsp;&nbsp;
                                    </div>
                                </center>
                            </div>
                            <div class="col-lg-12" id="botonesLibre" style="display:none">
                                <center>
                                    <!--button type="button" class="btn btn-primary" id="addrowLibre">Agregar Producto</button></a-->&nbsp;&nbsp;
                                    <button type="button" class="btn btn-success" onclick="receiveOC()" id="botonRecibirLibre">Recibir Entrada Libre</button></a>&nbsp;&nbsp;
                                </center>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <br>
                                <label id="label-table-1">Productos recibidos</label>
                                <div class="ibox-content"  >
                                    <div class="jqGrid_wrapper" >
                                        <table id="grid-tabla"></table>
                                        <div id="grid-page"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label id="label-table-2">Productos esperados por la Orden de Compra</label>
                                <div class="ibox-content">
                                    <div class="jqGrid_wrapper">
                                        <table id="grid-tabla2"></table>
                                        <div id="grid-page2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="pull-right">
                                    <a href="#" onclick="cancelar()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="hiddenAction">
                    <input type="hidden" id="hiddenID_Aduana">
                    <input type="hidden" id="guardo">
                    <input type="hidden" id="lote_valor">
                </div>
            </form>
        </div>
    </div>
  </div>
</div>



<!-- Mainly scripts -->
<script src="/js/moment.js"></script>
<script src="/js/moment-with-locales.js"></script>
<script src="/js/jquery-2.1.1.js"></script>
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
    
<!-- Vue Js-->
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.10/dist/vue.js"></script>

<script src="/js/vueactions/recepcionoc_actions.js"></script>
