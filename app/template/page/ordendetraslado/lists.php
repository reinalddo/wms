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
    <h3>Ordenes de Traslado</h3>
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
                        <div class="form-group col-md-4">
                            <label>Fecha Inicio</label>
                            <div class="input-group date"  id="data_3">
                                <input id="fechai" type="text" class="form-control" ><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            </div>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Fecha Fin</label>
                            <div class="input-group date"  id="data_4">
                                <input id="fechaf" type="text" class="form-control" ><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="row" class="col-md-12">
                        <div class="form-group col-md-4">
                            <label>Buscar por</label>
                            <input id="txtCriterio" type="text" placeholder="" class="form-control">
                        </div>
                        <div class="form-group col-md-4">
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
                            <?php if($permisos["agregar"]==1){?>
                              <button onclick="agregar()" class="btn btn-sm btn-primary" type="button">
                                <i class="fa fa-plus"></i> Nuevo
                              </button>
                            <?php }?>
                        <button id="importExcelOC" type="button"  class="btn btn-primary">
                            <i class="fa fa-file-excel-o"></i>
                            Importar OC
                        </button>

                            </div>
                            <style>
                                <?php if($permisos["editar"]==0){?>
                                  .fa-edit{display: none;}
                                <?php }?>
                                <?php if($permisos["borrar"]==0){?>
                                .fa-eraser{display: none;}
                                <?php }?>
                            </style>
                          
                    <div style="width: 100%; text-align: center; margin-bottom: 50px;">
                    </div>
                        </div>
                      
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
                              <label for="email">Valor de Traslados</label>
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
                        <h4 class="modal-title">Importar Ordenes de Traslado</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Seleccione tipo de importación</label>
                              <select class="form-control" name="tipo" id="tipo" required>
                                  <option value="">Seleccione</option>
                                  <option value="">Orden de Traslado</option>
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

<div class="wrapper wrapper-content  animated fadeInRight" id="FORM" style="display: none">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4" id="_title">
                            <h3>Agregar Orden de Traslado</h3>
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
                                <div class="form-group col-md-4"><label>Numero de Orden | Cross Docking (ERP)</label> 
                                    <input id="NumOrden" type="text" placeholder="Numero de Orden" class="form-control" maxlength="10">
                                </div>
                                <div class="form-group col-md-2"><label>Folio de la Orden de Traslado</label> 
                                    <input disabled id="FolioOrden" type="text" placeholder="Número de Folio" class="form-control" value="<?php echo $listaOC->getMax()->id + 1;?>">
                                </div>
                                <div class="form-group col-md-2"><label>Consecutivo de Protocolo</label> 
                                    <input disabled id="Consecut" type="text" placeholder="Consecutivo de Protocolo"  class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group col-md-4">
                                    <label>Proveedor*</label>
                                    <select class="form-control" id="Proveedr">
                                        <option value="">Seleccione</option>
                                        <?php foreach( $listaProvee->getAll() AS $a ): ?>
                                        <option value="<?php echo $a->ID_Proveedor; ?>">[<?php echo $a->ID_Proveedor; ?>] - <?php echo $a->Nombre; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Tipo de Orden de Traslado (Protocolo)*</label>
                                    <select class="form-control" id="Protocol">
                                        <option value="">Seleccione</option>
                                        <?php foreach( $listaProto->getAll() AS $a ): ?>
                                        <option value="<?php echo $a->ID_Protocolo; ?>"><?php echo $a->descripcion; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Usuario*</label>
                                    <select class="form-control" id="usuario" disabled>
                                        <?php foreach( $listaUser->getAll() AS $a ): ?>
                                          <option value="<?php echo $a->id_user; ?>"><?php echo "(".$a->cve_usuario.") ".$a->nombre_completo; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group col-md-4">
                                    <label>Fecha de Entrega Estimada</label>
                                    <div class="input-group date" id="data_5">
                                        <input id="fechaestimada" type="text" class="form-control" value="<?php echo $listaOC->fecha_actual(); ?>">
                                        <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </div>
                                </div>
                                <div class="form-group col-md-4" >
                                    <label>Fecha de Orden de Traslado*</label>
                                    <div class="input-group date" id="data_6">
                                        <input id="fechaentrada" type="text" class="form-control" >
                                        <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </div>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Presupuesto</label> <!--Precio Unitario-->
                                        <select class="form-control" id="presupuestoActivo" onchange="mostrarPartida(this)">
                                            <option value="">Seleccione Presupuesto</option>
                                            <?php foreach( $listaPresupuestos->getAll() AS $a ): ?> <!--llama la funcion get all() en Protocolos/Protocolos-->
                                            <option value="<?php echo $a->id; ?>"><?php echo $a->claveDePartida.' - '.$a->nombreDePresupuesto; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                </div>
                            </div>
                            <div class="col-lg-12"><!--nueva fila para catalogos-->
                                <div class="form-group col-md-4">
                                      <label>Tipo de Recurso</label> <!--Precio Unitario-->
                                      <select class="form-control" id="tipoDeRecurso">
                                        <option value="">Seleccione Recurso</option>
                                        <?php foreach( $listaRecursos->getAll() AS $a ): ?> <!--llama la funcion get all() en Protocolos/Protocolos-->
                                        <option value="<?php echo $a->nombreDeRecurso; ?>"><?php echo $a->nombreDeRecurso; ?></option>
                                        <?php endforeach; ?>
                                      </select>
                                </div>
                                <div class="form-group col-md-4">
                                      <label>Tipo de Procedimiento</label> 
                                      <select class="form-control" id="tipoDeProcedimiento">
                                        <option value="">Seleccione Procedimiento</option>
                                        <?php foreach( $listaProcedimientos->getAll() AS $a ): ?> <!--llama la funcion get all() en Protocolos/Protocolos-->
                                        <option value="<?php echo $a->nombreDeProcedimiento; ?>"><?php echo $a->nombreDeProcedimiento; ?></option>
                                        <?php endforeach; ?>
                                      </select>
                                </div>
                                <div class="form-group col-md-4">
                                      <label>Partida Presupuestaria</label>
                                      <input  id="partidaPresupuesto" type="text" placeholder="Partida Presupuestaria" class="form-control">
                                      
                                </div>
                            </div>
                                  
                            <div class="col-lg-12"><!--nueva fila para catalogos-->
                                <div class="form-group col-md-4">
                                      <label>No. de Suficiencia</label>
                                      <input id="numeroDeSuficiencia" type="text" placeholder="No. de Suficiencia" class="form-control">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Fecha de Suficiencia</label>
                                    <div class="input-group date" id="data_new2">
                                        <input id="fechaSuficiencia" type="text" class="form-control" >
                                        <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </div>
                                </div>
                                <div class="form-group col-md-4">
                                      <label>Area Solicitante</label>
                                      <!--input id="areaSolicitante" type="text" placeholder="Area Solicitante" class="form-control"-->
                                      <select class="form-control" id="areaSolicitante">
                                        <option value="">Seleccione Cliente</option>
                                        <?php foreach( $listaClientes->getAll() AS $a ): ?> <!--llama la funcion get all() en Protocolos/Protocolos-->
                                        <option value="<?php echo $a->Cve_CteProv; ?>"><?php echo $a->RazonSocial; ?></option>
                                        <?php endforeach; ?>
                                      </select>
                                </div>
                                
                            </div>
                              
                            <div class="col-lg-12"><!--nueva fila para catalogos-->
                                <div class="form-group col-md-4">
                                      <label>No. de Contrato</label>
                                      <input id="numeroContrato" type="text" placeholder="No. de Contrato" class="form-control">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Fecha de Contrato</label>
                                    <div class="input-group date" id="data_new3">
                                        <input id="fechaContrato" type="text" class="form-control" >
                                        <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </div>
                                </div>
                                <div class="form-group col-md-4">
                                      <label>Monto de Suficiencia</label>
                                      <input id="montoSuficiencia" type="text" placeholder="Monto de Suficiencia" class="form-control">
                                </div>
                            </div>
                                  
                            <div class="col-lg-12"><!--fila pra campos fantantes   TODOHOY-->
                                <div class="form-group col-md-4">
                                      <label>Fecha de Fallo</label> <!--Precio Unitario-->
                                      <div class="input-group date" id="data_new">
                                        <input id="fechaDeFallo" type="text" class="form-control" >
                                        <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                      </div>
                                </div>
                                <div class="form-group col-md-4">
                                      <label>Plazo de Entrega</label>
                                      <input id="plazoDeEntrega" type="text" placeholder="Plazo de Entrega" class="form-control">
                                </div>
                                <div class="form-group col-md-4">
                                      <label>Condiciones de Pago</label>
                                      <input id="condicionesDePago" type="text" placeholder="Condiciones de Pago" class="form-control">
                                </div>
                            </div>
                                  
                             <div class="col-lg-12"><!--fila pra campos fantantes   TODOHOY-->
                                <div class="form-group col-md-4">
                                      <label>Lugar de Entrega</label> <!--Precio Unitario-->
                                      <input id="lugarDeEntrega" type="text" placeholder="Lugar de Entrega" class="form-control">
                                </div>
                                <div class="form-group col-md-4">
                                      <label>Numero de Expediente</label>
                                      <input id="numeroDeExpediente" type="text" placeholder="Numero de Expediente" class="form-control">
                                </div>
                                <div class="form-group col-md-2">
                                      <label>Dictamen</label> 
                                      <select class="form-control" id="dictamenActivo" onchange="habilitar(this)"> 
                                          <option value="Si">Si</option> 
                                          <option value="No">No</option> 
                                      </select> 
                                </div>
                                <div class="form-group col-md-2">
                                  <label>No. de dictamen</label>
                                    <input class="form-control" id="numeroDictamen"  type="text" name="dictamen" value=""/>
                                </div>
                            </div>      
                            <div style="pading: 30px; marging: 30px;">
                              &nbsp;
                            </div>
                            <div>
                              <div class="col-lg-12" style="pading: 30px; marging: 30px;">
                                <div class="form-group col-lg-12" style="pading: 30px; marging: 30px;  border: 2.5px solid; border-color: #dedede; border-radius: 15px"><!--#1ab394-->
                                  <div class="form-group col-md-4" id="Articul" >
                                      <label>Artículo*</label>
                                      <select name="country" id="basic" class="chosen-select form-control" readonly>
                                          <option value="">Seleccione Artículo</option>
                                      </select>
                                      <input id="ancho" type="hidden" class="form-control">
                                      <input id="alto" type="hidden" class="form-control">
                                      <input id="fondo" type="hidden" class="form-control">
                                  </div>
                                  <div class="form-group col-md-4">
                                      <label>Cantidad de Piezas</label> 
                                      <input type="hidden" id="modo_peso" value="1">
                                      <input id="CantPiezas" type="text" placeholder="Cantidad de Piezas" class="form-control" maxlength="8">
                                      <input id="CantPiezasPeso" type="text" placeholder="Cantidad de Piezas Kg" class="form-control" maxlength="8" style="display: none;">
                                  </div>
                                  <div class="form-group col-md-4">
                                      <label>Peso (Kgs)</label> 
                                      <input type="hidden" id="pesohidden" value="0"/>
                                      <input id="peso" disabled type="text" placeholder="Peso" class="form-control">
                                  </div>
                                  <input id="status" type="hidden" placeholder="Cantidad de Piezas" class="form-control">
                              
                                  <div class="col-lg-12">
                                    <div class="form-group col-md-4">
                                        <label>Precio Unitario</label> 
                                        <input type="hidden" id="PUhidden" value="0"/>
                                        <input id="precioUnitario" type="text" placeholder="$ 00.00 " class="form-control">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Importe Total (Articulo)</label> 
                                        <input type="hidden" id="importeHidden" value="0"/>
                                        <input id="importeTotal" disabled type="text" placeholder="$ 00.00 " class="form-control">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Importe Total de la Orden de Traslado</label> 
                                        <input id="importeOrden" disabled type="text" placeholder="$ 00.00 " class="form-control">
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

<div class="modal fade" id="ver_detalles" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="width: 1200px">
        <div class="modal-content">
            <div class="modal-body" style="text-align: center;">
                <h3 class="modal-title">Detalles de OC | Entrada <span id="num_entrada"></span></h3>
                <br>
                <div class="jqGrid_wrapper">
                    <table id="grid-table_detalles"></table>
                    <div id="grid-pager_detalles"></div>
                </div>
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
        select_user = document.getElementById('usuario'),
        input_cant = document.getElementById('CantPiezas'),
        input_cantM = document.getElementById('cant_prod'),
        input_precioU = document.getElementById('precio_unitario'),
        input_importeT = document.getElementById('importe_total');

    /**
     * @author Ricardo Delgado.
     * Busca y selecciona el almacen predeterminado para el usuario.
     */

    input_cant.addEventListener("keypress", validateNumber, false);
    input_cantM.addEventListener("keypress", validateNumber, false);
    input_precioU.addEventListener("keypress", validateNumber, false);
    input_importeT.addEventListener("keypress", validateNumber, false);
  
    function selectUser(){
        var user = '<?php echo $_SESSION["id_user"]?>';
        select_user.value = user;
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
            url:'/api/ordendecompra/lista/index.php',
            datatype: "json",
            shrinkToFit: false,
            height:'auto',
            postData: {
                criterio: $("#txtCriterio").val()
            },
            mtype: 'POST',
            colNames:["Acciones",'Clave','Folio','Status','OC | Entrada', 'Proveedor','Tipo OC | Entrada','Cons. Protocolo','Fecha Solicitud','Fecha Requerida','Status2','Almacen','Usuario','cve_usuario','Tipo de Recurso','Tipo de Procedimiento','Dictamen','Presupuesto','Condiciones de Pago','Lugar de Entrega','Fecha De Fallo','Plazo de Entrega','No. de Expediente','Importe de la Orden',"Area Solicitante","Numero de Suficiencia","Fecha de Suficiencia","Fecha de Contrato","Monto de Suficiencia","Numero de Contrato"],/*'Calle y Numero','Colonia','Ciudad','Estado','Pais','Codigo Postal','RFC','Telefono1','Telefono2'*/
            colModel:[
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat},
                {name:'ID_Aduana',index:'ID_Aduana',width:110, editable:false, hidden:true, sortable:false, align: 'right'},
                {name:'num_pedimento',index:'num_pedimento',width:50, editable:false, sortable:false, align: 'right'},
                {name:'status2',index:'status2',width:150, editable:false, sortable:false, hidden:false},
                {name:'ocentrada',index:'ocentrada',width:200, editable:false, sortable:false},
                {name:'Empresa',index:'Empresa',width:200, editable:false, sortable:false},
                {name:'Protocolo',index:'Protocolo',width:200, editable:false, sortable:false},
                {name:'Consec_protocolo',index:'Consec_protocolo',width:130, editable:false, sortable:false},
                {name:'fech_pedimento',index:'fech_pedimento',width:140, align:'center', editable:false, sortable:false},
                {name:'fech_llegPed',index:'fech_llegPed',width:140, align:'center', editable:false, sortable:false},
                {name:'status',index:'status',width:120, editable:false, sortable:false, hidden:true},

                {name:'Almacen',index:'Almacen',width:200, editable:false, sortable:false},

                {name:'cve_usuario',index:'cve_usuario',width:100, editable:false, sortable:false, hidden:false},
                {name:'cve_usuario2',index:'cve_usuario2',width:150, editable:false, sortable:false, hidden:true},
                {name:'recurso',index:'recurso',width:150, editable:false, sortable:false, hidden:false},
                {name:'procedimiento',index:'procedimiento',width:150, editable:false, sortable:false, hidden:false},
                {name:'dictamen',index:'dictamen',width:150, editable:false, sortable:false, hidden:false},
                {name:'nombreDePresupuesto',index:'nombreDePresupuesto',width:250, editable:false, sortable:false, hidden:false},
                {name:'condicionesDePago',index:'condicionesDePago',width:150, editable:false, sortable:false, hidden:false},
                {name:'lugarDeEntrega',index:'lugarDeEntrega',width:200, editable:false, sortable:false, hidden:false},
                {name:'fechaDeFallo',index:'fechaDeFallo',width:140, align:'center', editable:false, sortable:false, hidden:false},
                {name:'plazoDeEntrega',index:'plazoDeEntrega',width:140, editable:false, sortable:false, hidden:false},
                {name:'numeroDeExpediente',index:'numeroDeExpediente',width:150, editable:false, sortable:false, hidden:false, align: 'right'},
                {name:'importe',index:'importe',width:150, editable:false, sortable:false, hidden:false, align: 'right'},
              {name:'areaSolicitante',index:'areaSolicitante',width:150, editable:false, sortable:false, hidden:false},
              {name:'numSuficiencia',index:'numSuficiencia',width:150, editable:false, sortable:false, hidden:false, align: 'right'},
              {name:'fechaSuficiencia',index:'fechaSuficiencia',width:150, align:'center', editable:false, sortable:false, hidden:false},
              {name:'fechaContrato',index:'fechaContrato',width:150, align:'center', editable:false, sortable:false, hidden:false},
              {name:'montoSuficiencia',index:'montoSuficiencia',width:150, editable:false, sortable:false, hidden:false, align: 'right'},
              {name:'numeroContrato',index:'numeroContrato',width:150, editable:false, sortable:false, hidden:false, align: 'right'},
              
               
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

            var serie = rowObject[2];
            var oc    = rowObject[4];
            var status = rowObject[9];
            var usuario = rowObject[12];
            $("#hiddenID_Aduana").val(serie);

            var html = '<a href="#" onclick="ver(\''+serie+'\', \''+oc+'\')"><i class="fa fa-search" title="Ver Detalles"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            if (status === "C"){
                html += '<a href="#" onclick="editar(\''+serie+'\')"><i class="fa fa-edit" title="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            
              html += '<a href="#" onclick="exportarPDF(\''+serie+'\')"><i class="fa fa-file-pdf-o" title="PDF"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
          
            return html;
          console.log(serie);
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
            colNames:['Clave','Nombre','Cantidad','Peso (Kgs)','PesoHidden', 'Volumen (m3)','Precio Unitario','Importe Total', "Acciones"],/*'Calle y Numero','Colonia','Ciudad','Estado','Pais','Codigo Postal','RFC','Telefono1','Telefono2'*/
            colModel:[
                {name:'codigo',index:'codigo',width:150, editable:false, sortable:false, align: 'right'},
                {name:'descripcion',index:'descripcion',width:250, editable:false, sortable:false},
                {name:'CantPiezas',index:'CantPiezas',width:150, sortable:false, align: 'right'},
                {name:'peso',index:'peso',width:150, editable:false, sortable:false, align: 'right'},
                {name:'pesohidden',index:'pesohidden',width:100, editable:false, hidden:true, sortable:false},
                {name:'volumen',index:'volumen',width:200, editable:false, hidden:false, sortable:false, align: 'right'},
                {name:'precioUnitario',index:'precioUnitario',width:150, editable:false, hidden:false, sortable:false, align: 'right'},
                {name:'importeTotal',index:'importeTotal',width:150, editable:false, hidden:false, sortable:false, align: 'right'},
                {name:'myac',index:'', width:130, fixed:true, sortable:false, resize:false, formatter:imageFormat2}
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

            var ids = $("#grid-tabla").jqGrid('getDataIDs');

            for (var i = 0; i < ids.length; i++)
            {
                var rowId = ids[i];
                var rowData = $('#grid-tabla').jqGrid ('getRowData', rowId);
                if (rowData.codigo==basic.value) 
                {
                    window.alert("Este Artículo ya fue incluido");
                    return;
                }
            }
            option = $("#basic").val();
            var cant_piezas = 0;
            if($("#modo_peso").val() == 1)
            {
                cant_piezas = $("#CantPiezasPeso").val();
            }
            else 
            {
                cant_piezas = parseInt($("#CantPiezas").val());
            }
            volumen = parseInt($("#ancho").val()) * parseInt($("#alto").val()) * parseInt($("#fondo").val()) / 1000000000  * cant_piezas;

            emptyItem=[{
                codigo          : basic.value,
                descripcion     : $('#basic :selected').text(),
                CantPiezas      : cant_piezas,
                peso            : parseFloat($("#peso").val()).toFixed(3),
                pesohidden      : $("#pesohidden").val(),
                volumen         : volumen,
                precioUnitario  : $('#precioUnitario').val(),
                importeTotal    : $('#importeTotal').val()
            }];
            $("#grid-tabla").jqGrid('addRowData',0,emptyItem);

            //$("#grid-tabla").jqGrid('editRow', 0,true);

            basic.value = '';
            $('.chosen-select').trigger("chosen:updated");
            $("#CantPiezas").val(0);
            $("#CantPiezasPeso").val(0);
            $("#precioUnitario").val(0);
            $("#peso").val(0);

            importeOrden_tabla += parseFloat($('#importeTotal').val());
            $('#importeTotal').val(0);

            calcularTotal();

        });

        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size

        //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
        function imageFormat2( cellvalue, options, rowObject ){
            var correl = options.rowId;
            var	html = '<a href="#" onclick="editarAdd(\''+correl+'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
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
        $("#_title").html('<h3>Editar Orden de Compra</h3>');
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
    $('#data_6').data("DateTimePicker").date(new Date());
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
        selectUser();
        
        $('input, #basic').each(function(){
            $(this).html("");
        });
        $("#FolioOrden").val("<?php echo $listaOC->getMax()->id + 1;?>")
        $('.chosen-select').trigger('chosen:updated');
        //$("#Consecut").val("<?php echo $consec+1; ?>");
        $('#CodeMessage').html("");
        $("#_title").html('<h3>Agregar Orden de Traslado</h3>');
        $('#data_6').data("DateTimePicker").date(new Date());
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
        calcularTotal();
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
  
    var l = $( '.ladda-button2' ).ladda();
    l.click(function() {

        /************************ VALIDAR INPUTS DEL FORM ****************************/
        if ($("#FolioOrden").val()=="") {
            return;
        }
       
        if ($("#dictamenActivo").val()=="Si") {
            if ($("#numeroDictamen").val()=="") { 
                swal(
                    'Error',
                    'Por favor ingrese No. de Dictamen',
                    'error'
                );
                return;
            }
        }
      
        if ($("#Almcen").val()=="") {
            swal(
                'Error',
                'Por favor seleccione almacen',
                'error'
            );
            return;
        }
      
      
        if ($("#montoSuficiencia").val()!="") {
						var monto = $("#montoSuficiencia").val();
						var importe = $("#importeOrden").val();
            if (parseFloat(monto) < parseFloat(importe))
						{
                swal(
                    'Error',
                    'El Importe Total de la Orden es mayor al Monto de Suficiencia, Por favor seleccione menos articulos',
                    'error'
                );
                return;
            }
            
        }
        

        if ($("#Proveedr").val()=="") {
            swal(
                'Error',
                'Por favor seleccione proveedor',
                'error'
            );
            return;
        }

        if ($("#fechaestimada").val()=="") {
            document.getElementById('fechaestimada').value = $("#fechaentrada").val();
            //var date = new Date();
            //document.getElementById('fechaestimada').value = date.getDate() + "-" + (date.getMonth() +1) + "-" + date.getFullYear();
        }

        /*if ($("#fechaDeFallo").val()=="") {
            var date = new Date();
            document.getElementById('fechaDeFallo').value = date.getDate() + "-" + (date.getMonth() +1) + "-" + date.getFullYear();
        }*/
      
        if ($("#fechaentrada").val()=="") {
            swal(
                'Error',
                'Por favor seleccione fecha de orden de Traslado',
                'error'
            );
            return;
        }

        if ($("#usuario").val()=="" || !$("#usuario").val()) {
            swal(
                'Error',
                'Por favor seleccione un usuario',
                'error'
            );
            return;
        }

        if ($("#Protocol").val()=="") {
            swal(
                'Error',
                'Por favor seleccione un protocolo',
                'error'
            );
            return;
        }

        $('input').each(function(){
            if ($(this).attr("id")!="cantidad" && $(this).val()=="") return;
        });

        var startDate = new Date($('#fechaentrada').val());
        var endDate = new Date($('#fechaestimada').val());
/*
        if (startDate > endDate)
        {
          swal(
            'Error',
            'La fecha de orden de Traslado es mayor a la fecha estimada de recepcion',
            'error'
          );
          return;
        }
*/
        /************************ FIN VALIDAR INPUTS DEL FORM ************************/

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

        for (var i = 0; i < ids.length; i++)
        {
            var rowId = ids[i];
            var rowData = $('#grid-tabla').jqGrid ('getRowData', rowId);
            
            var inputPU = document.getElementById('precioUnitario');
          
          //TODO: Envia items de la orden - precioNew
            arrDetalle.push({
                codigo: rowData.codigo,
                descripcion: rowData.descripcion,
                CantPiezas: rowData.CantPiezas,
                precioNewOk: rowData.precioUnitario
            });
        }
      
      
        //return;
        l.ladda( 'start' );
        $.ajax({
            type: "POST",
            dataType: "json",
            url: '/api/ordendecompra/update/index.php',
            data: {
              
                action : $("#hiddenAction").val(),
                num_pedimento: $("#FolioOrden").val(),
                fechaentrada: $("#fechaentrada").val(),
                fechaestimada: $("#fechaestimada").val(),
                Cve_Almac: $("#Almcen").val(),
                ID_Proveedor: $("#Proveedr").val(),
                ID_Protocolo: $("#Protocol").val(),
                Consec_protocolo: $("#Consecut").val(),
                factura: $("#NumOrden").val(),
                status: "C",
                cve_usuario: $("#usuario").val(),
                arrDetalle: arrDetalle,
                precioNew: inputPU.value,
                precioNewOk: rowData.precioUnitario,
                recurso: $("#tipoDeRecurso").val(),
                procedimiento: $("#tipoDeProcedimiento").val(),
                areaSolicitante: $("#areaSolicitante").val(),
                numSuficiencia: $("#numeroDeSuficiencia").val(),
                fechaSuficiencia: $("#fechaSuficiencia").val(),
                montoSuficiencia: $("#montoSuficiencia").val(),
                numeroContrato: $("#numeroContrato").val(),
                fechaContrato: $("#fechaContrato").val(),
                dictamen: $("#numeroDictamen").val(),
                presupuesto: $("#presupuestoActivo").val(),
                condicionesDePago: $("#condicionesDePago").val(),
                lugarDeEntrega: $("#lugarDeEntrega").val(),
                fechaDeFallo: $("#fechaDeFallo").val(),
                plazoDeEntrega: $("#plazoDeEntrega").val(),
                numeroDeExpediente: $("#numeroDeExpediente").val()
           
                //importeOrden: $("#importeOrden").val(),
                
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); } console.log("before", x);},
            success: function(data) {
              console.log("success", data);
              if (data.success == true) 
              {
                console.log($("#hiddenAction").val());
                  if($("#hiddenAction").val() === "add") {
                      swal('Excelente','Orden Creada con exito.','success');
                      location.reload();
                  }
                  else {
                    swal('Excelente','Orden Modificada con exito.','success');
                  }
                  
              } 
              else 
              {
                swal('Error',data.err,'error');
                $("#btnCancel").show();
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

    if (inputPU.addEventListener) 
    {
      inputPU.addEventListener('keyup', function () {
          updateInputs();
      });
    }
  
    if (inputCP.addEventListener) 
    {
      inputCP.addEventListener('keyup', function () {
          updateInputs();
      });
    }

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
                
                $('#pesohidden').val(data.peso);
                $('#peso').val(data.peso);
                $('#PUhidden').val(data.peso);
                
                if($("#articulo").val()!=data.cve_articulo)
                {
                  $('#precioUnitario').val(data.costo);
                }
                $('#importeHidden').val(data.costo);

                if($('#precioUnitario').val()==data.costo)
                {
                    $('#importeTotal').val(data.costo);
                }
                else if($('#precioUnitario').val()!=data.costo)
                {
                    $('#importeTotal').val(importeTotal);
                }

                importeOrden.value = parseFloat(importeOrden_tabla) + parseFloat($('#importeTotal').val());

                $("#CantPiezas").val("1");
                $("#CantPiezasPeso").val("1");

                console.log("control_peso = ", data.control_peso);
                if(data.control_peso == 1)
                {
                  $("#CantPiezas").hide();
                  $("#CantPiezasPeso").show();
                }
                else
                {
                  $("#CantPiezas").show();
                  $("#CantPiezasPeso").hide();
                }
                $("#modo_peso").val(data.control_peso);

                $("#articulo").val(data.cve_articulo);
                $("#alto").val(data.alto);
                $("#ancho").val(data.ancho);
                $("#fondo").val(data.fondo);
              
               
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
            colNames:['Clave','Descripción', 'Lote', 'Caducidad|Fecha Entrada', 'Serie', 'Solicitadas', 'Recibidas', 'Peso', 'Volumen','Precio Unitario', 'Importe Total'],
            colModel:[
                {name:'clave',index:'clave',width: 100, editable:false, sortable:false, align: 'right'},
                {name:'descripcion',index:'descripcion',width:300, editable:false, sortable:false},

                {name:'lote',index:'lote',width:150, editable:false, sortable:false, align: 'right'},
                {name:'caducidad',index:'caducidad',width:180, editable:false, sortable:false, align: 'center'},
                {name:'serie',index:'serie',width:150, editable:false, sortable:false, align: 'right'},
                {name:'pedidas',index:'pedidas',width:100, editable:false, sortable:false, align: 'right'},
                {name:'surtidas',index:'surtidas',width:100, editable:false, sortable:false, align: 'right'},
                {name:'peso',index:'peso',width:100, editable:false, sortable:false, align: 'right'},
                {name:'volumen',index:'volumen',width:100, editable:false, sortable:false, align: 'right'},
                {name:'precioU',index:'precioU',width:100, editable:false, sortable:false, align: 'right'},
                {name:'importeTotal',index:'importeTotal',width:100, editable:false, sortable:false, align: 'right'}
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
    });

    function ver(folio, oc){
        $("#ver_detalles #num_entrada").text(oc);
        $("#ver_detalles").modal("show");
        loadDetalles(folio);
    }
    function loadDetalles(folio) {
        $('#grid-table_detalles').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                folio: folio,
                action: 'getDetallesFolio'
            }, datatype: 'json', page : 1, mtype: 'GET', url:'/api/ordendecompra/lista/index.php'})
            .trigger('reloadGrid',[{current:true}]);
    }

    function loadArticulos() {
        $('#grid-table_articulos').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                almacen: document.getElementById("Almcen").value,
                search: document.getElementById("searchArticle").value
            }, datatype: 'json', page : 1, mtype: 'GET', url: '/api/articulos/lista/index.php'})
            .trigger('reloadGrid',[{current:true}]);
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
          //console.log(res);
          fillSelect(res);
        },
        error : function(res){
          window.console.log(res);
        },
        cache: false
      });

      function fillSelect(node)
      {
        var option = "<option value =''>Seleccione un Articulo ("+node.length+")</option>";
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
