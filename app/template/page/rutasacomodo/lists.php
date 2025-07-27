<?php 
include_once $_SERVER['DOCUMENT_ROOT']."/app/host.php";
$listaProv = new \Proveedores\Proveedores();
?>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<link href="/css/plugins/dataTables/dataTables1.min.css" rel="stylesheet"/>
<link href="/css/plugins/dataTables/buttons.dataTables.min.css" rel="stylesheet"/>
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/switchery.min.css" rel="stylesheet">


<!-- Mainly scripts -->
<script src="/js/jquery-2.1.4.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>

<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.min.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="/js/switchery.min.js"></script>
<!-- Select -->
<script src="/js/select2.js"></script>
<!-- Data picker -->
<script src="/js/plugins/dataTables/dataTables.min.js"></script>
<script src="/js/plugins/dataTables/dataTables_bootstrap.min.js"></script>
<script src="/js/plugins/chosen/chosen.jquery.js"></script>
<script src="/js/utils.js"></script>

<style>
    #list {
        width: 100%;
        height: 100%;
        position: absolute;
        left: 0px;
        z-index: 999;
    }

    #FORM,#search {
        width: 100%;
        /*height: 100%;*/
        position: absolute;
        left: 0px;
        z-index: 999;
    }
    .checkbox-2{
        position: relative;
        display: block;
        margin-top: 10px;
        margin-bottom: 10px;
    }

    .blink {
  
      animation-name: blink;
      animation-duration: 4s;
      animation-timing-function: linear;
      animation-iteration-count: infinite;

      -webkit-animation-name:blink;
      -webkit-animation-duration: 4s;
      -webkit-animation-timing-function: linear;
      -webkit-animation-iteration-count: infinite;
    }

    @-moz-keyframes blink{  
      0% { opacity: 1.0; }
      50% { opacity: 0.0; }
      100% { opacity: 1.0; }
    }

    @-webkit-keyframes blink {  
      0% { opacity: 1.0; }
      50% { opacity: 0.0; }
       100% { opacity: 1.0; }
    }

    @keyframes blink {  
      0% { opacity: 1.0; }
       50% { opacity: 0.0; }
      100% { opacity: 1.0; }
    }
</style>

<style type="text/css">

</style>

<?php 
$cve_proveedor = "";
if(isset($_SESSION['id_proveedor']))
{
    $cve_proveedor = $_SESSION['id_proveedor'];
}
?>
<input type="hidden" id="cve_proveedor" value="<?php echo $cve_proveedor; ?>">


<div class="wrapper wrapper-content  animated " id="form-prin">

    <h3>Rutas de Acomodo</h3>

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Almacen:</label>
                                <select id="select-almacen-1" class="chosen-select form-control">
                                    <option value="">Seleccione el Almacen</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Zona de Almacenaje</label>
                                <select id="select-zonaA-1" class="chosen-select form-control">
                                </select>
                            </div>
                        </div>

                        <?php 
                        /*
                        ?>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="email">Tipo de Ubicación</label>
                                <select id="select-tipoU" class="chosen-select form-control">
                                    <option value="">Seleccione el Tipo de Ubicación</option>
                                    <option value="L">Libre</option>
                                    <option value="R">Reservada</option>
                                    <option value="Q">Cuarentena</option>
                                    <option value="Picking">Picking</option>
                                    <option value="PTL">PTL</option>
                                    <option value="Mixto">Acomodo Mixto</option>
                                    <option value="Produccion">Área de Producción</option>
                                </select>
                            </div>
                        </div>
                        <?php 
                        */
                        ?>
                        <div class="col-sm-3">
                            <div class="form-group">
                               <label>BL</label>
                               <input id="slect-BL" class="form-control" placeholder="BL">
                               <?php 
                               /*
                                ?>
                                <select id="slect-BL" class="chosen-select form-control">
                                   <option value="">Seleccione un BL</option>
                              </select>
                              <?php 
                              */
                              ?>
                            </div>
                        </div>
                        <?php 
                        /*
                        ?>
                        <div class="col-sm-3">
                            <div class="form-group">
                               <label>Rack</label>
                               <select id="select-rack" class="chosen-select form-control">
                                   <option value="">Seleccione un Rack</option>
                              </select>
                            </div>
                        </div>
                        <?php 
                        */
                        ?>

                            <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>Empresa | Proveedor </label>
                                        <select class="form-control" id="cboProveedor_busq">
                                        <?php if($cve_proveedor == ""){ ?>
                                        <option value="">Seleccione</option>
                                        <?php } ?>
                                        <?php 
                                            foreach( $listaProv->getAll("AND es_cliente = 1") AS $p ): 
                                                if($p->ID_Proveedor == $cve_proveedor && $cve_proveedor != '')
                                                {
                                        ?>
                                        <option value="<?php echo $p->ID_Proveedor; ?>"><?php echo $p->Nombre; ?></option>
                                        <?php 
                                                }
                                                else if($cve_proveedor == "")
                                                {
                                        ?>
                                        <option value="<?php echo $p->ID_Proveedor; ?>"><?php echo $p->Nombre; ?></option>
                                        <?php 
                                                }
                                        ?>
                                        <?php endforeach; ?>
                                    </select>
                                    </div>
                            </div>

                    </div>

                    <div class="row">
                        <div class="col-sm-2">
                            <div class="form-group">
                               <label>Buscar</label>
                               <input type="text" class="form-control" name="txtCriterio" id="input-criterio" placeholder="Buscar...">
                            </div>
                        </div>

                        <?php 
                        /*
                        ?>
                        <div class="col-sm-2">
                            <div class="form-group">
                               <label>Pallet|Contenedor</label>
                               <input type="text" class="form-control" name="txtPallet" id="input-pallet-contenedor" placeholder="Pallet|Contenedor...">
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                               <label>License Plate (LP)</label>
                               <input type="text" class="form-control" name="txtLP" id="input-lp" placeholder="License Plate (LP)...">
                            </div>
                        </div>

                        <div class="col-sm-4">
                              <!--<input type="checkbox" name="ch_box_vacio" id="ch_box_vacio"><label>Ubicaciones vacías</label>-->
                            <div class="form-group">
                               <label>Ubicaciones</label>
                                <select id="select-existencia" class="chosen-select form-control">
                                    <option value="0">Todas las Ubicaciones</option>
                                    <option value="1" selected>Ubicaciones Con Existencia</option>
                                    <option value="2">Ubicaciones Sin Existencia</option>
                                </select>
                            </div>

                        </div>
                        <?php 
                        */
                        ?>
                        <div class="col-sm-6">
                            <button id="button-buscar" class="btn btn-primary"><span class="fa fa-search"></span> Buscar</button>
                            <button id="button-generar" class="btn btn-primary permiso_registrar"><span class="fa fa-list-ol"></span> Generar Secuencia</button>
                            <!--<button id="button-nuevo" class="btn btn-primary" onclick="initAgregar()"><span class="fa fa-plus"></span> Nuevo</button>-->
                            <!--button id="button-import" class="btn btn-primary" onclick="actionButtonCerrar()" ><span class="fa fa-download"></span> I Importar</button
                          <button class="btn btn-primary" data-toggle="modal" data-target="#importar"><span class="fa fa-download"></span> Importar</button>
                            <a href="/api/v2/ubicacion-de-almacenaje/exportar" target="_blank" class="btn btn-primary"><span class="fa fa-upload"></span> Exportar</a>-->
                        </div>

                        <div class="col-sm-2" style="float: right;">
                            <button id="button-ubi-ina" class="btn btn-primary" onclick="seachTableRecu()">Ubicaciones inactivas</button>
                        </div>
                    </div>
                </div>
                <div class="ibox-title">
                  <b>Ubicaciones: <span id="num_ubicaciones"></span></b>
                </div>
                <div class="ibox-content">
                    <div class="jqGrid_wrapper">
                        <label id="codigo_BL_name"></label>
                        <table id="grid-table"></table>
                        <div id="grid-pager"></div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>


<div class="wrapper wrapper-content  animated fadeInRight" id="form-agregar" style="display: none">
  <div class="row">
    <div class="col-lg-12">
      <div class="ibox ">
        <div class="ibox-title">
          <div class="row">
            <div class="col-lg-4" id="_title">
                <h3>Agregar Posición de Zona de Almacenaje</h3>
            </div>
          </div>
        </div>
        <form id="myform">
          <div class="ibox-content">
            <div class="row">
              <div class="col-lg-6 b-r">
                <div class="row">
                  <div class="col-sm-12">
                    <div class="form-group">
                      <label for="almacenes">Almacen: *</label>
                      <select class="form-control" id="select-almacen-2" required>
                      </select>
                    </div>
                    <div class="form-group">
                      <label for="Almacen">Zona de Almacenaje: *</label>
                      <select class="form-control" id="select-zonaA-2" required>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="row" id="div_de_pasillo">
                  <div class="col-sm-12">
                    <div class="form-group" >
                      <label>Pasillo:</label> 
                      <input id="input-pasillo" type="text" class="form-control">
                    </div>
                  </div>
                </div>
                <div class="row" id="div_de_rack">
                  <div class="col-sm-12">
                    <div class="form-group">
                      <label>Rack:</label>
                      <input id="input-rack" class="form-control" type="text" value="0">
                    </div>
                  </div>
                </div>

                <div class="row" id="div-nivels-1">
                  <div class="col-sm-5">
                    <div class="form-group">
                      <input type="radio" id="radio-nivels" name="nivels" checked>
                      <label>Niveles: *</label>
                    </div>
                  </div>
                  <div class="col-sm-7">
                    <div class="form-group">
                      <input id="input-nivel" class="form-control" type="text" value="0">
                    </div>
                  </div>
                </div>

                <div class="row" id="div-nivels-2">
                  <div class="col-sm-5">
                    <div class="form-group">
                      <input type="radio" id="radio-nivelsR" name="nivels" >
                      <label>Rango de Nivel: *</label>
                      <label class="pull-right">Del</label>
                    </div>
                  </div>
                  <div class="col-sm-3">
                    <div class="form-group">
                      <input id="input-nivelsR-D" class="form-control" type="text" value="0">
                    </div>
                  </div>
                  <div class="col-sm-1">
                    <div class="form-group">
                      <label>Al</label>
                    </div>
                  </div>
                  <div class="col-sm-3">
                    <div class="form-group">
                      <input id="input-nivelsR-A" class="form-control" type="text" value="0">
                    </div>
                  </div>
                </div>

                <div class="row" id="div-seccions-1">
                  <div class="col-sm-5">
                    <div class="form-group">
                      <input type="radio" id="radio-secc" name="seccs" checked>
                      <label>Secciones: *</label>
                    </div>
                  </div>
                  <div class="col-sm-7">
                    <div class="form-group">
                      <input id="input-secc" class="form-control" type="text" value="0">
                    </div>
                  </div>
                </div>

                <div class="row" id="div-seccions-2">
                  <div class="col-sm-5">
                    <div class="form-group">
                      <input type="radio" id="radio-seccR" value="1" name="seccs">
                      <label>Rango de Secciones: *</label>
                      <label class="pull-right">Del</label>
                    </div>
                  </div>
                  <div class="col-sm-3">
                    <div class="form-group">
                      <input id="input-seccR-D" class="form-control" type="text" value="0">
                    </div>
                  </div>
                  <div class="col-sm-1">
                    <div class="form-group">
                      <label>Al</label>
                    </div>
                  </div>
                  <div class="col-sm-3">
                    <div class="form-group">
                      <input id="input-seccR-A" class="form-control" type="text" value="0">
                    </div>
                  </div>
                </div>
                <div class="row" id="div-nivel" style="display: none;">
                  <div class="col-sm-12">
                    <div class="form-group">
                      <label>Nivel: *</label>
                      <input id="input-hiden-nivel" class="form-control" type="text" value="0" name="ubicacion2" disabled>
                    </div>
                  </div>
                </div>
                <div class="row" id="div-seccion" style="display: none;">
                  <div class="col-sm-12">
                    <div class="form-group">
                      <label>Seccion: *</label>
                      <input id="input-hiden-seccion" class="form-control" type="text" value="0" name="ubicacion2" disabled>
                    </div>
                  </div>
                </div>
                <div class="row" id="ubicacionseccion2">
                  <div class="col-sm-12">
                    <div class="form-group">
                      <label>Posiciones por Sección: *</label>
                      <input id="input-seccU" class="form-control" type="text" value="0" name="ubicacion2" disabled>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-lg-6">
                <div class="row">
                  <div class="col-sm-12">
                    <div class="form-group">
                      <label>Alto (mm) *</label>
                      <input id="input-alto" class="form-control" type="text" maxlength="6" value="0" name="demo1" required>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-sm-12">
                    <div class="form-group">
                      <label>Ancho (mm) *</label> 
                      <input id="input-ancho" maxlength="6" type="text" class="form-control" value="0" required>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-sm-12">
                    <div class="form-group">
                      <label>Fondo(mm) *</label>
                      <input id="input-fondo" class="form-control" maxlength="6" type="text" value="0" required>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-sm-12">
                    <div class="form-group">
                      <label>Peso Máximo (kgs) *</label>
                      <input id="input-pesoM"  maxlength="12" type="text" class="form-control" value="0" required>
                    </div>
                  </div>
                </div>
                <div class="row" id="div-ubi-rp">
                  <div class="col-sm-12">
                    <div class="form-group">
                      <div class="form-group">
                        <input type="radio" name="ubica_rack" id="radio-ubi-rack">
                        <label for="radio5">Ubicación de Rack: *</label>
                      </div>
                      <div class="form-group">
                        <input type="radio" name="ubica_rack" id="radio-ubi-piso">
                        <label for="radio6">Ubicación de Piso: *</label>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-sm-2">
                    <div class="form-group">
                      <label>Status: *</label>
                    </div>
                  </div>
                  <div class="col-sm-2">
                    <div class="form-group">
                      <input type="radio" name="status" id="radio-status-lib" value="L">
                      <label>Libre</label>
                    </div>
                  </div>
                  <div class="col-sm-2">
                    <div class="form-group">
                      <input type="radio" name="status" id="radio-status-res" value="R">
                      <label>Reservada</label>
                    </div>
                  </div>
                  <div class="col-sm-3">
                    <div class="form-group">
                      <input type="radio" name="status" id="radio-status-cua" value="Q">
                      <label>Cuarentena</label>
                    </div>
                  </div>
                  <div class="col-sm-3">
                    <div class="form-group">
                      <input type="radio" name="status" id="radio-status-bloq" value="B">
                      <label>Bloqueada</label>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group">
                      <div class="form-group">
                        <input type="checkbox" name="Pick" id="check-pick">
                        <label >Picking</label>
                      </div>
                      <div class="form-group">
                        <input type="checkbox" name="ubicaptl1" id="check-ubi">
                        <label >Ubicación de PTL</label>
                      </div>
                      <div class="form-group">
                        <input type="checkbox" name="ubicaptl" id="check-acoM">
                        <label >Acomodo Mixto</label>
                      </div>
                      <div class="form-group">
                        <input type="checkbox" name="areaproduccion" id="check-area">
                        <label for="areaproduccion">Área de Producción</label>
                      </div>

                    <div class="form-group">
                        <label for="control_abc">
                        <input type="checkbox" name="control_abc" id="control_abc" value="1"> Control ABC</label>
                    </div>
                    <div class="form-group tipo_ABC" style="display: none;margin-left: 20px;margin-bottom: 0;">
                        <label for="control_tipo_A">
                        <input type="checkbox" name="control_tipo_A" id="control_tipo_A" value="1"> A</label>
                    </div>
                    <div class="form-group tipo_ABC" style="display: none;margin-left: 20px;margin-bottom: 0;">
                        <label for="control_tipo_B">
                        <input type="checkbox" name="control_tipo_B" id="control_tipo_B" value="1"> B</label>
                    </div>
                    <div class="form-group tipo_ABC" style="display: none;margin-left: 20px;margin-bottom: 0;">
                        <label for="control_tipo_C">
                        <input type="checkbox" name="control_tipo_C" id="control_tipo_C" value="1"> C</label>
                    </div>


                    </div>
                  </div>

                  <div class="col-sm-6" id="div-codigo_bl">
                    <div class="row">
                      <label>Código BL del Almacen</label>
                      <div id ="Parte1" class="col-sm-6">
                          <div class="checkbox-2">
                              <label>
                                  <input type="checkbox" id="check-pasillo" value="Pasillo" disabled>
                                  Pasillo
                              </label>
                          </div>
                          <div class="checkbox-2">
                              <label>
                                  <input type="checkbox" id="check-rack" value="Rack" disabled>
                                  Rack
                              </label>
                          </div>
                          <div class="checkbox-2" id="cve_nivel">
                              <label>
                                  <input type="checkbox" id="check-nivel" value="Nivel" disabled>
                                  Nivel
                              </label>
                          </div>
                      </div>
                      <div id ="Parte2" class="col-sm-6">
                        <div class="checkbox-2">
                          <label>
                            <input type="checkbox" id="check-seccion" value="Seccion" disabled>
                            Sección
                          </label>
                        </div>
                        <div class="checkbox-2">
                          <label>
                            <input type="checkbox" id="check-posicion" value="Posicion" disabled>
                            Posición
                          </label>
                        </div>                            
                      </div>
                      <!--label>Código BL del Almacen</label-->
                      <input type="text" id="input-codigo-bl" readonly class="form-control" required>
                    </div>
                  </div>

                </div>
                <div class="row">
                  <div class="col-sm-12">
                    <div class="form-group">
                      <div class="pull-right"><br>
                        <a href="#" onclick="cerrarAgregar()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                        <button type="submit" class="btn btn-primary ladda-button" data-style="contract" id="btnSave">Guardar</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="modal-zona" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 1200px!important;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Productos de la Zona de Almacenaje <span id="title_almacen"></span></h4>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="table-zona"  class="table table-hover table-striped no-margin display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Clave</th>
                                    <th>Articulo</th>
                                    <th>Ubicacion</th>
                                    <th>Lote</th>
                                    <th>Existencia</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-zone">
                          </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-recu" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 1200px!important;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Recuperar Ubicacion de Almacenaje</h4>
                </div>
                <div class="modal-body">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="almacenes">Almacen:</label>
                            <select class="form-control" id="select-alma-re" >
                                <option value=" ">Seleccione el Almacen</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <button id="button-buscar-re" class="btn btn-primary btn-sm" style="margin-top: 25px;" >Buscar</button>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div class="jqGrid_wrapper">
                            <table id="grid-table2"></table>
                            <div id="grid-pager2"></div>
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



    <div class="modal fade" id="importar" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Importar ubicaciones de almacenaje</h4>
                    </div>
                    <div class="modal-body">
                        <form id="form-import" action="import" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Seleccionar archivo excel para importar</label>
                                <input type="file" name="file" id="file" class="form-control"  required>
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
                        <button id="btn-layout" type="button" class="btn btn-primary">Descargar Layout</button>
                      </div>
                        <div class="col-md-6" style="text-align: right">
                            <button id="btn-import" type="button" class="btn btn-primary">Importar</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
<!-- EDG -->

  <div class="modal fade" id="ver_detalles" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="width:91%;">
<!--       style="width:100%;" -->
      <div class="modal-content">
        <div class="modal-body" style="text-align: center;">
          <h3 class="modal-title">Productos Ubicados</h3>
          <h4><label id="detalle_ubicacion" style="text-align: center;"></label></h4>
          <div class="row">
            <div class="col-md-12">
              <div class="col-md-3">
                <label>Peso Maximo (Kg)</label>
                <input type="text" class="form-control" id="pes_max" style="text-align:right;"disabled>
              </div>
              <div class="col-md-3">
                <label>Peso Total Ocupado (Kg)</label>
                <input type="text" class="form-control" id="pes_total" style="text-align:right;"disabled>
              </div>
              <div class="col-md-3">
                <label>Peso Disponible (Kg)</label>
                <input type="text" class="form-control" id="pes_dispo" style="text-align:right;"disabled>
              </div>
              <div class="col-md-3">
                <label>% Ocupación Peso</label>
                <input type="text" class="form-control" id="pes_porcentaje" style="text-align:right;" disabled>
              </div>
            </div>
          </div>
          <br>
          <div class="row">
            <div class="col-md-12">
              <div class="col-md-3">
                <label>Volumen Maximo (m3)</label>
                <input type="text" class="form-control" id="vol_max" style="text-align:right;"disabled>
              </div>
              <div class="col-md-3">
                <label>Volumen Total Ocupado (m3)</label>
                <input type="text" class="form-control" id="vol_total" style="text-align:right;"disabled>
              </div>
              <div class="col-md-3">
                <label>Volumen Disponible (m3)</label>
                <input type="text" class="form-control" id="vol_dispo" style="text-align:right;"disabled>
              </div>
              <div class="col-md-3">
                <label>% Ocupacion Volumen</label>
                <input type="text" class="form-control" id="vol_porcentaje" style="text-align:right;"disabled>
              </div>
            </div>
          </div>
          <br>
          
           <div class="row">
               <div class="ibox-content">
                 <div class="table-responsive">
                  <div class="col-md-12" style="padding-left: 0px;">
                      <table id="grid-table_detalles"></table>
                      <div id="grid-pager_detalles"></div>
                  </div>
                </div>
              </div>
          </div>
          <br>
          <div class="row">
            <div class="col-md-12">
              <h4><labe id="tabla_vacia" style="display: none;">No existen productos en esta ubicación</labe></h4>
            </div>
          </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-default" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

<script>

$('#btn-layout').on('click', function(e) {
  //e.preventDefault();  //stop the browser from following
  //window.location.href = 'http://www.tinegocios.com/proyectos/wms/Layout_Pedidos.xlsx';
  //window.location.href = '../../../../Layout/Layout_OC.xlsx';
  window.location.href = '/Layout/Layout_Ubicaciones.xlsx';
});
  
$('#btn-import').on('click', function() {

    $('#btn-import').prop('disable', true);
    var bar = $('.progress-bar');
    var percent = $('.percent');
    //var status = $('#status');

    var formData = new FormData();
    formData.append("clave", "valor");

    $.ajax({
        // Your server script to process the upload
        url: '/ubicacion-de-almacenaje/importar',
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
            console.log("SUCCESS: ", data);
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
        }, error: function(data){
            console.log("ERROR: ", data);
        }
    });
});
</script>



<script type="text/javascript">
  
    

    var select_almacen_1 = document.getElementById('select-almacen-1'),
        select_zonaA_1 = document.getElementById('select-zonaA-1'),
        //select_tipoU = document.getElementById('select-tipoU'),
        select_BL = document.getElementById('slect-BL'),
        //select_rack = document.getElementById('select-rack'),
        button_nuevo = document.getElementById('button-nuevo'),
        button_import = document.getElementById('button-import'),
        button_buscar = document.getElementById('button-buscar'),
        button_buscar_re = document.getElementById('button-buscar-re'),
        button_ubi_ina = document.getElementById('button-ubi-ina'),
        form_agregar = document.getElementById('form-agregar'),
        form_prin = document.getElementById('form-prin'),
        input_criterio = document.getElementById('input-criterio'),
        input_pallet_contenedor = document.getElementById('input-pallet-contenedor'),
        input_lp = document.getElementById('input-lp'),
        //check_vacio = document.getElementById('ch_box_vacio'),
        tableDataInfo = new TableDataRest(),
        tableDataZone = new TableDataRest(),
        tableDataRecu = new TableDataRest(),
        buttonSave = document.getElementById('myform'),
        DATA = null,
        DATA_R = null,
        TABLE = null,
        PREDE_ALMACEN = "",
        ID_MODI = null;

    var select_alma_re = document.getElementById('select-alma-re');


    /* formulario */
    //* izquierdo *//
    var select_almacen_2 = document.getElementById('select-almacen-2'),
        select_zonaA_2 = document.getElementById('select-zonaA-2'),
        input_pasillo = document.getElementById('input-pasillo'),
        input_rack = document.getElementById('input-rack'),

        radio_nivels = document.getElementById('radio-nivels'),
        input_nivel = document.getElementById('input-nivel'),
        radio_nivelsR = document.getElementById('radio-nivelsR'),
        input_nivelsR_D = document.getElementById('input-nivelsR-D'),
        input_nivelsR_A = document.getElementById('input-nivelsR-A'),

        radio_secc = document.getElementById('radio-secc'),
        input_secc = document.getElementById('input-secc'),
        radio_seccR = document.getElementById('radio-seccR'),
        input_seccR_D = document.getElementById('input-seccR-D'),
        input_seccR_A = document.getElementById('input-seccR-A'),
        input_seccU = document.getElementById('input-seccU'),
        input_hiden_nivel = document.getElementById('input-hiden-nivel'),
        input_hiden_seccion = document.getElementById('input-hiden-seccion'),

        div_nivel = document.getElementById('div-nivel'),
        div_seccion = document.getElementById('div-seccion'),
        div_nivels_1 = document.getElementById('div-nivels-1'),
        div_nivels_2 = document.getElementById('div-nivels-2'),
        div_seccions_1 = document.getElementById('div-seccions-1'),
        div_seccions_2 = document.getElementById('div-seccions-2');

    //* derecha *//
    var input_alto = document.getElementById('input-alto'),
        input_ancho = document.getElementById('input-ancho'),
        input_fondo = document.getElementById('input-fondo'),
        input_pesoM = document.getElementById('input-pesoM'),
        radio_ubi_rack = document.getElementById('radio-ubi-rack'),
        radio_ubi_piso = document.getElementById('radio-ubi-piso'),
        radio_status_lib = document.getElementById('radio-status-lib'),
        radio_status_res = document.getElementById('radio-status-res'),
        radio_status_cua = document.getElementById('radio-status-cua'),
        radio_status_bloq = document.getElementById('radio-status-bloq'),
        check_pick = document.getElementById('check-pick'),
        check_ubi = document.getElementById('check-ubi'),
        check_acoM = document.getElementById('check-acoM'),
        check_area = document.getElementById('check-area'),
        check_pasillo = document.getElementById('check-pasillo'),
        check_rack = document.getElementById('check-rack'),
        check_nivel = document.getElementById('check-nivel'),
        check_seccion = document.getElementById('check-seccion'),
        check_posicion = document.getElementById('check-posicion'),
        input_codigo_bl = document.getElementById('input-codigo-bl'),
        div_ubi_rp = document.getElementById('div-ubi-rp'),
        div_codigo_bl = document.getElementById('div-codigo_bl'),
        control_abc = document.getElementById('control_abc'),
        control_tipo_A = document.getElementById('control_tipo_A'),
        control_tipo_B = document.getElementById('control_tipo_B'),
        control_tipo_C = document.getElementById('control_tipo_C'),
        btnSave = document.getElementById('btnSave');


    buttonSave.addEventListener('submit', function(e){e.preventDefault();});
    /*check_pick.onclick=function(){
      check_ubi.checked=false;
    };
  
    check_ubi.onclick=function(){
      check_pick.checked=false;
    };*/

    select_almacen_1.onchange = function(){changeAlmacens(select_almacen_1, select_zonaA_1,);};
    select_almacen_2.onchange = function(){changeAlmacens(select_almacen_2, select_zonaA_2);};
    

    button_buscar.onclick = function(){
      buscar();
    };
  
    $("#select-almacen-1").change(function(){
      buscar();
    });
  
    function buscar()
    {
      var zone = select_zonaA_1.value,
      //tipo = select_tipoU.value,
      BL = select_BL.value,
      //RACK = select_rack.value,
      data = {
        criterio: input_criterio.value, 
        //pallet_contenedor: input_pallet_contenedor.value,
        //lp: input_lp.value,
        almacen : select_almacen_1.value, 
        BL: select_BL.value,
        //RACK: select_rack.value,
        id_proveedor: $("#cboProveedor_busq").val(),
        cve_proveedor: $("#cve_proveedor").val()
        //vacio: $("#select-existencia").val()
        //vacio: $("#ch_box_vacio").prop('checked') 
      };
console.log("zone = ", zone);
      if(zone)
      {
        data.zone = zone;
      }
      else
      {
        data.zone = "";
      }
/*
      if(tipo)
      {
        data.tipo = tipo;
      }
      else
      {
        data.tipo = "";
      }
*/
      $("#grid-table").jqGrid('clearGridData')
      .jqGrid('setGridParam', {postData: data, datatype: 'json', page : 1})
      .trigger('reloadGrid',[{current:true}]);
    };

    function exportarPDFCodigoBarras(lp)
    {
            //console.log("folio = ", $("#lista_lp").data("folio"));
            //console.log("proveedor = ", $("#lista_lp").data("proveedor"));
            //console.log("oc = ", $("#lista_lp").data("oc"));


            //var lista_lp = $("#lista_lp").val();
            //if(lista_lp === null) lista_lp = "";
            //console.log("lista_lp = ", lista_lp);
            //console.log("folio = ", folio);
            //console.log("proveedor = ", proveedor);
            //console.log("oc = ", oc);

            var folio = '';
            var proveedor = '';
            var oc = '';
            console.log("LP = ", lp);


                var 
                        form = document.createElement('form'),
                        input_nofooter = document.createElement('input'),
                        input_folio = document.createElement('input'),
                        input_oc = document.createElement('input'),
                        input_proveedor = document.createElement('input'),
                        input_lp =  document.createElement('input');

                form.setAttribute('method', 'post');
                form.setAttribute('action', '/entradas/pdf/codigobarras');//Class: ReportePDF/EntradasPDF.php->generarReporteEntradas($folio);
                form.setAttribute('target', '_blank');

                input_nofooter.setAttribute('name', 'nofooternoheader');
                input_nofooter.setAttribute('value', '1');

                input_folio.setAttribute('name', 'folio');
                input_folio.setAttribute('value', folio);
                input_oc.setAttribute('name', 'oc');
                input_oc.setAttribute('value', oc);//$("#lista_lp").data("oc")
                input_proveedor.setAttribute('name', 'proveedor');
                input_proveedor.setAttribute('value', proveedor);//$("#lista_lp").data("proveedor")
                input_lp.setAttribute('name', 'lp');
                input_lp.setAttribute('value', lp);

                form.appendChild(input_nofooter);
                form.appendChild(input_folio);
                form.appendChild(input_oc);
                form.appendChild(input_proveedor);
                form.appendChild(input_lp);

                document.getElementsByTagName('body')[0].appendChild(form);
                form.submit();
              
    }

    button_buscar_re.onclick = function(){

        var data = {almacen : select_alma_re.value };

        $("#grid-table2").jqGrid('clearGridData')
        .jqGrid('setGridParam', {postData: data, datatype: 'json', page : 1})
        .trigger('reloadGrid',[{current:true}]);
    };

    //select_zonaA_1.onchange = function(){serchTableInfo();};
    //select_tipoU.onchange = function(){serchTableInfo();};

    input_alto.addEventListener("keypress", validateNumber, false);
    input_ancho.addEventListener("keypress", validateNumber, false);
    input_fondo.addEventListener("keypress", validateNumber, false);
    input_pesoM.addEventListener("keypress", validateNumber, false);
    //input_rack.addEventListener("keypress", validateNumber, false);
    input_nivel.addEventListener("keypress", validateNumber, false);
    input_nivelsR_D.addEventListener("keypress", validateNumber, false);
    input_nivelsR_A.addEventListener("keypress", validateNumber, false);
    input_secc.addEventListener("keypress", validateNumber, false);
    input_seccR_D.addEventListener("keypress", validateNumber, false);
    input_seccR_A.addEventListener("keypress", validateNumber, false);
    input_seccU.addEventListener("keypress", validateNumber, false);

    radio_nivelsR.onchange = radio_nivels.onchange =  function(){

        input_nivel.value = "0";
        input_nivelsR_D.value = "0";
        input_nivelsR_A.value = "0";

        if(radio_nivels.checked){
            input_nivel.disabled = false;
            input_nivelsR_D.disabled = true;
            input_nivelsR_A.disabled = true;
        }
        else{
            input_nivel.disabled = true;
            input_nivelsR_D.disabled = false;
            input_nivelsR_A.disabled = false;
        }
    };

    radio_secc.onchange = radio_seccR.onchange =  function(){

        input_secc.value = "0";
        input_seccR_D.value = "0";
        input_seccR_A.value = "0";

        if(radio_secc.checked){
            input_secc.disabled = false;
            input_seccR_D.disabled = true;
            input_seccR_A.disabled = true;
        }
        else{
            input_secc.disabled = true;
            input_seccR_D.disabled = false;
            input_seccR_A.disabled = false;
        }
    };

    radio_ubi_rack.onchange = radio_ubi_piso.onchange =  function(){

        if(radio_ubi_piso.checked){

            radio_nivelsR.disabled = true;
            radio_nivels.disabled = true;
            input_nivel.disabled = true;
            input_nivelsR_D.disabled = true;
            input_nivelsR_A.disabled = true;
        }
        else{
            
            radio_nivelsR.disabled = false;
            radio_nivels.disabled = false;

            if(radio_nivels.checked){
                input_nivel.disabled = false;
            }
            else{
                input_nivelsR_D.disabled = false;
                input_nivelsR_A.disabled = false;
            }
        }
    };

    radio_status_bloq.onchange = radio_status_lib.onchange = radio_status_res.onchange = radio_status_cua.onchange = function(){

        if(radio_status_cua.checked){
            check_pick.disabled = true;
            check_ubi.disabled = true;
            check_pick.checked = false;
            check_ubi.checked = false;
        }
        else{
            check_pick.disabled = false;
        }
    };

    check_pick.onchange =  function(){

        if(check_pick.checked){
            check_ubi.disabled = false;
        }
        else{
            check_ubi.disabled = true;
            check_ubi.checked = false;
        }
    };
    check_ubi.onchange =  function(){

        if(check_ubi.checked){
            check_acoM.disabled = true;
            check_acoM.checked = false;
        }
        else{
            check_acoM.disabled = false;
            check_acoM.checked = false;
        }
    };

    check_rack.onclick = check_nivel.onclick = check_seccion.onclick = check_posicion.onclick = check_pasillo.onclick = function(){

        if(this.checked){
            addCodBl(this.value);
        }
        else{
            removeCodBl(this.value);
        }
    };

    buttonSave.onsubmit = function(){
      save();
    };
    
    function initAgregar()
    {
      ID_MODI = null;
      select_almacen_2.value = PREDE_ALMACEN;
      changeAlmacens(select_almacen_2, select_zonaA_2);
      habilitateButons(false);
      configCodiBL();
      input_pasillo.value = "";
      input_rack.value = "";
      radio_ubi_rack.checked = true;
      radio_nivels.checked = true;
      input_nivel.value = "0";
      input_nivelsR_D.value = "0";
      input_nivelsR_A.value = "0";
      input_nivelsR_D.disabled = true;
      input_nivelsR_A.disabled = true;
      radio_secc.checked = true;
      input_secc.value = "0";
      input_seccR_D.value = "0";
      input_seccR_A.value = "0";
      input_seccR_D.disabled = true;
      input_seccR_A.disabled = true;
      input_seccU.value = "";
      input_alto.value = "";
      input_ancho.value = "";
      input_fondo.value = "";
      input_pesoM.value = "";
      radio_status_lib.checked = true;
      check_pick.checked = false;
      check_ubi.checked = false;
      check_ubi.disabled = true;
      check_acoM.checked = false;
      check_area.checked = false;

      control_abc.checked = false;
      control_tipo_A.checked = false;
      control_tipo_B.checked = false;
      control_tipo_C.checked = false;

      form_prin.style.display = "none";
      form_agregar.style.display = "block";

      $("#Parte1").hide();
      $("#Parte2").hide();

      traerBL();

    }
  
    $("#select-almacen-2").change(function (){
       traerBL();
        //console.log("hola mundo");
        $("#input-pasillo").val("");
        $("#input-rack").val("");
        $("#input-nivel").val("");
        $("#input-seccU").val("");

        $("#input-alto").val("");
        $("#input-ancho").val("");
        $("#input-fondo").val("");
        $("#input-pesoM").val("");
      
        $("#input-nivelsR-D").val("0");
        $("#input-nivelsR-A").val("0");
      
      
    });
  
    $("#button-generar").click(function(){

            swal({
                title: "Orden de Secuencia",
                text: "Generar Orden de Secuencia para la Zona de Almacenaje: "+$("#select-zonaA-1 option:selected").text()+"?",
                type: "info",

                cancelButtonText: "No",
                cancelButtonColor: "#14960a",
                showCancelButton: true,

                confirmButtonColor: "#55b9dd",
                confirmButtonText: "Si",
                closeOnConfirm: true
            }, function() {

                console.log("Generar");
                console.log("zona: ", $("#select-zonaA-1").val());
                console.log("proveedor: ", $("#cboProveedor_busq").val());


                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        zona: $("#select-zonaA-1").val(),
                        proveedor: $("#cboProveedor_busq").val(),
                        action: 'generar-secuencia'
                    },
                    beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                    },
                    url: '/api/rutasacomodo/index.php',
                    success: function(data) {
                        console.log("Generar data = ", data);
                        swal("Éxito", "Se ha generado las ordenes de secuencia", "success");
                        buscar();
                    },
                    error:function(res){
                        console.log("Error Generar = ",res);
                    }

                });

            });

    });

    $('#control_abc').change(function() 
    {
        if($("#control_abc").is(':checked'))
            $('.tipo_ABC').show();
        else
            $('.tipo_ABC').hide();

        $('#control_tipo_A').prop('checked', false);
        $('#control_tipo_B').prop('checked', false);
        $('#control_tipo_C').prop('checked', false);
    });

    $('#control_tipo_A').change(function() 
    {
        $('#control_tipo_B').prop('checked', false);
        $('#control_tipo_C').prop('checked', false);
    });

    $('#control_tipo_B').change(function() 
    {
        $('#control_tipo_A').prop('checked', false);
        $('#control_tipo_C').prop('checked', false);
    });

    $('#control_tipo_C').change(function() 
    {
        $('#control_tipo_A').prop('checked', false);
        $('#control_tipo_B').prop('checked', false);
    });

  
    function traerBL()
    {
       var clave = $("#select-almacen-2").val();
       $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                clave: clave,
                action: "traerBL"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/rutasacomodo/index.php',
            success: function(data) 
            {
              //console.log("input-codigo-bl = ", data["res"][0]["BL"]);
              //$("#input-codigo-bl").val(data["res"][0]["BL"]);
              
              //if (data["res"][0]["BL_Pasillo"] == 1){$("#check-pasillo").prop('checked',true);}
              //if (data["res"][0]["BL_Rack"] == 1){$("#check-rack").prop('checked',true);}
              //if (data["res"][0]["BL_Nivel"] == 1){$("#check-nivel").prop('checked',true);}
              //if (data["res"][0]["BL_Seccion"] == 1){$("#check-seccion").prop('checked',true);}
              //if (data["res"][0]["BL_Posicion"] == 1){$("#check-posicion").prop('checked',true);}
              /*
              if ($("#select-almacen-2").val() !="")
              {
                  if (data["res"][0]["BL_Pasillo"] == 0)
                  {
                    $("#div_de_pasillo").hide();
                  }
                  else
                  {
                    $("#div_de_pasillo").show();
                    $("#div_de_pasillo").attr("required", true);
                  }
                
                  if (data["res"][0]["BL_Rack"] == 0)
                  {
                    $("#div_de_rack").hide();
                  }
                  else
                  {
                    $("#div_de_rack").show();
                    $("#div_de_rack").attr("required", true);
                  }
                
                  if (data["res"][0]["BL_Nivel"] == 0)
                  {
                    $("#div-nivels-1").hide();
                    
                    $("#div-nivels-2").hide();
                  }
                  else
                  {
                    $("#div-nivels-1").show();
                    $("#div-nivels-1").attr("required", true);
                    
                    $("#div-nivels-2").show();
                    $("#div-nivels-2").attr("required", true);
                  }
                
                  if (data["res"][0]["BL_Seccion"] == 0)
                  {
                    $("#div-seccions-1").hide();
                    $("#div-seccions-2").hide();
                  }
                  else
                  {
                    $("#div-seccions-1").show();
                    $("#div-seccions-1").attr("required", true);
                    
                    $("#div-seccions-2").show();
                    $("#div-seccions-2").attr("required", true);
                  }
                
                  if (data["res"][0]["BL_Posicion"] == 0)
                  {
                    $("#ubicacionseccion2").hide();
                  }
                  else
                  {
                    $("#ubicacionseccion2").show();
                    $("#ubicacionseccion2").attr("required", true);
                  }
              }
              */
            }
       });
    }

    function cerrarAgregar(){
        form_agregar.style.display = "none";
        form_prin.style.display = "block"; 
    }

    function editUbi(id)
    {

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                idy_ubica: id,
                action: "search-ubica"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/rutasacomodo/index.php',
            success: function(data) {
                var node = data.res[0];
                ID_MODI = node.idy_ubica;
                select_almacen_2.value = node.alma;
                changeAlmacens(select_almacen_2, select_zonaA_2, node.almacen);
                habilitateButons(true);
                input_pasillo.value = node.cve_pasillo;
                input_rack.value = 0+node.cve_rack;
                input_hiden_nivel.value = node.cve_nivel;
                input_hiden_seccion.value = node.Seccion;
                form_prin.style.display = "none";
                form_agregar.style.display = "block";
                input_seccU.value = node.Ubicacion;
                input_alto.value = node.num_alto;
                input_ancho.value = node.num_ancho;
                input_fondo.value = node.num_largo;
                input_pesoM.value = node.PesoMaximo;

                if(node.picking === "S")
                    check_pick.checked = true;
                if(node.TECNOLOGIA === "PTL")
                    check_ubi.checked = true;
                if(node.AcomodoMixto === "S")
                    check_acoM.checked = true;
                if(node.AreaProduccion === "S")
                    check_area.checked = true;

                if(node.Tipo === "L")
                    radio_status_lib.checked = true;
                else if(node.Tipo === "R")
                    radio_status_res.checked = true;
                else if(node.Tipo === "B")
                    radio_status_bloq.checked = true;
                else
                    radio_status_cua.checked = true;

                control_abc.checked = false;
                control_tipo_A.checked = false;
                control_tipo_B.checked = false;
                control_tipo_C.checked = false;
                  if(node.clasif_abc != '')
                  {
                      control_abc.checked = true;
                      if(node.clasif_abc == 'A') control_tipo_A.checked = true;
                      else if(node.clasif_abc == 'B') control_tipo_B.checked = true;
                      else if(node.clasif_abc == 'C') control_tipo_C.checked = true;
                  }

            },
            error:function(res){
                window.console.log(res);
            }

        });
    }
  
    function ver(id, csd, peso_max, vol_max, pes_porcentaje, vol_porcentaje, cvelp)
    {//EDG
      console.log(id, csd, peso_max, vol_max, pes_porcentaje, vol_porcentaje, cvelp);
      $("#tabla_vacia").hide();
      $("#pes_max").val("");
      $("#pes_total").val("");
      $("#pes_dispo").val("");
      $("#pes_porcentaje").val("");
      $("#vol_max").val("");
      $("#vol_total").val("");
      $("#vol_dispo").val("");
      $("#vol_porcentaje").val("");

      $("#pes_max").val(peso_max);
      $("#vol_max").val(vol_max);
       $("#pes_porcentaje").val(pes_porcentaje);
       $("#vol_porcentaje").val(vol_porcentaje);
      
      $("#detalle_ubicacion").html("Ubicacion "+csd);
      $("#ver_detalles").modal("show");
      //console.log(id);
      loadDetalles(id, cvelp);
    }
  
    function loadDetalles(id, cvelp) 
    {
          console.log("LOAD = ",id);
          $('#grid-table_detalles').jqGrid('clearGridData')
          .jqGrid('setGridParam', {postData: {
            action: 'getDetallesFolio',
            id: id,
            //CveLP: cvelp,
            almacen: $("#select-almacen-1").val(),
            rows: 10
          }, datatype: 'json', 
          error: function(data){
            console.log("ERROR", data);
          },
          page : 1, mtype: 'GET', url:'/api/rutasacomodo/lista/index.php'
        }).trigger('reloadGrid',[{current:true}]);
          console.log("LOAD2 = ",$("#select-almacen-1").val());
    }

    function habilitateButons(disabled)
    {
      select_almacen_2.disabled = disabled;
      //select_zonaA_2.disabled = disabled;
      input_pasillo.disabled = disabled;
      input_rack.disabled = disabled;
      radio_ubi_rack.disabled = disabled;
      radio_ubi_piso.disabled = disabled;
      input_seccU.disabled = disabled;

      if(disabled)
      {
        div_nivels_1.style.display = 'none';
        div_nivels_2.style.display = 'none';
        div_seccions_1.style.display = 'none';
        div_seccions_2.style.display = 'none';
        div_ubi_rp.style.display = 'none';
        div_codigo_bl.style.display = 'none';
        div_nivel.style.display = 'block';
        div_seccion.style.display = 'block';
      }
      else
      {
        div_nivels_1.style.display = 'block';
        div_nivels_2.style.display = 'block';
        div_seccions_1.style.display = 'block';
        div_seccions_2.style.display = 'block';
        div_ubi_rp.style.display = 'block';
        div_codigo_bl.style.display = 'block';
        div_nivel.style.display = 'none';
        div_seccion.style.display = 'none';
      }
    }

    init();

    function addCodBl(newValue){

/*
        var value = input_codigo_bl.value;

        if(value === '')
            input_codigo_bl.value = newValue;
        else
            input_codigo_bl.value += "-"+newValue;
*/
  
        codigo="";
        if(check_pasillo.checked){
          codigo+="Pasillo-";
        }
        if(check_rack.checked){
          codigo+="Rack-";
        }
        if(check_nivel.checked){
          codigo+="Nivel-";
        }
        if(check_seccion.checked){
          codigo+="Sección-";
        }
        if(check_posicion.checked){
          codigo+="Posicion";
        }
        input_codigo_bl.value=codigo;

    }

    function removeCodBl(newValue){
        /*
        var array = input_codigo_bl.value.split("-"),
            value = "";

        var index = array.indexOf(newValue);
        if (index > -1) {
            array.splice(index, 1);
        }

        value = array.toString().replace(",", "-");

        for(var i = 0; i < 3; i++)
            value = value.toString().replace(",", "-");

        input_codigo_bl.value = value;*/
      addCodBl(newValue);
    }

    function configCodiBL(){

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                action: 'enter-codiBL'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/rutasacomodo/index.php',
            success: function(data) {
                console.log("enter-codiBL = ",data);
                config(data.codigoBL[0].codigo);
            },
            error:function(res){
                console.log("Error BL = ",res);
            }

        });

        function config(node){

            check_pasillo.checked = false;
            check_rack.checked = false;
            check_nivel.checked = false;
            check_seccion.checked = false;
            check_posicion.checked = false;
            input_codigo_bl.value = "";

            var array = node.split("-");

            if(array.length > 0){
                for(var i = 0; i < array.length; i++){
                    if(array[i] === "cve_pasillo"){
                        check_pasillo.checked = true;
                        addCodBl(check_pasillo.value);
                    }
                    else if(array[i] === "cve_rack"){
                        check_rack.checked = true;
                        addCodBl(check_rack.value);
                    }
                    else if(array[i] === "cve_nivel"){
                        check_nivel.checked = true;
                        addCodBl(check_nivel.value);
                    }
                    else if(array[i] === "Seccion"){
                        check_seccion.checked = true;
                        addCodBl(check_seccion.value);
                    }
                    else if(array[i] === "Ubicacion"){
                        check_posicion.checked = true;
                        addCodBl(check_posicion.value);
                    }
                }
            }
        }
    }

    function init(){
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                id_user: <?php echo $_SESSION['id_user']; ?>,
                action: 'enter-view'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/rutasacomodo/index.php',
            success: function(data) {
                fillsAlmacens(data.almacens);
                almacenPrede();
            },
            error:function(res){
                window.console.log(res);
            }

        });
    }

    function changeAlmacens(element, select, valor){
        
        var value = element.value;

        if(value){

            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    action: 'search-alma',
                    'id': value
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                url: '/api/rutasacomodo/index.php',
                success: function(data) {
                    //console.log(data.zona[0]["BL"]);
                    //$("#codigo_BL_name").text(data.BL);
                    fillZona(data.zona, select, valor);
                    //serchTableInfo();
                },
                error:function(res){
                    window.console.log(res);
                }

            });
        }
        else{
            fillZona(null, select);
        }

        function fillZona(node, element, valor){

            var options = "";//"<option value = ''>Seleccione una zona de almacenaje</option>";

            if(node){
                var selected = "selected";
                for(var i = 0; i < node.length; i++){console.log(node[i].des_almac)
                    options += "<option value = "+node[i].cve_almac+">"+htmlEntities(node[i].des_almac)+"</option>";
                    selected = "";
                }
            }

            element.innerHTML = options;

            if(valor)
                element.value = valor;
        }
      //this.changeBL();
      //buscar();
    }
  
    //$("#select-zonaA-1").change(function(){changeBL();});

    function changeBL()
    {
      console.log("changeBL = ", $("#select-zonaA-1").val());
        $.ajax({
           type:"POST",
           dataType:"json",
           data: {
             action:"traer_BL",
             zona: $("#select-zonaA-1").val(),
             almacen: select_almacen_1.value
           },
           beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
           url:"/api/rutasacomodo/index.php",
           success: function(data){
              console.log(data);
              var options = "<option value = ''>Seleccione BL</option>";
              for(var i = 0; i < data.length; i++){
                  options += "<option value = "+data[i].CodigoCSD+">"+htmlEntities(data[i].CodigoCSD)+"</option>";
              }
              select_BL.innerHTML = options;
          },
          error: function(res){
            window.console.log(res);
          }
        });

        $.ajax({
           type:"POST",
           dataType:"json",
           data: {
             action:"traer_racks",
             almacen: select_almacen_1.value
           },
           beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
           url:"/api/rutasacomodo/index.php",
           success: function(data){
              console.log(data);
              var options_rack = "<option value = ''>Seleccione Rack</option>";
              for(var i = 0; i < data.length; i++){
                  options_rack += "<option value = "+data[i].cve_rack+">"+htmlEntities(data[i].cve_rack)+"</option>";
              }
              select_rack.innerHTML = options_rack;
          },
          error: function(res){
            window.console.log(res);
          }
        });

    }

    function fillsAlmacens(node)
    {

        var options = "";

        if(node){
          
          
            for(var i = 0; i < node.length; i++)
            {
                options += "<option value = "+node[i].clave+">"+htmlEntities(node[i].clave)+" - "+htmlEntities(node[i].nombre)+"</option>";
            }
        }

        select_almacen_1.innerHTML += options;
        select_almacen_2.innerHTML += options;
        select_alma_re.innerHTML += options;
    }

    function fillTableInfo(node){

        tableDataInfo.destroy();

        var data = [];

        DATA = node;

        for(var i = 0; i < node.length ; i++){

            var volumen = (node[i].num_ancho / 1000) * (node[i].num_alto / 1000) * (node[i].num_largo / 1000);
            var dimension = (node[i].num_alto)+" X "+(node[i].num_ancho)+" X "+(node[i].num_largo);
                volumen = volumen.toFixed(2); 

            var Picking = (node[i].picking === "S")  ? 'check' : 'close',
                Ptl = (node[i].Ptl === "S")  ? 'check' : 'close',
                libre = (node[i].Tipo === "L")  ? 'check' : 'close',
                reser = (node[i].Tipo === "R")  ? 'check' : 'close',
                cuaren = (node[i].Tipo === "Q")  ? 'check' : 'close',
                mixt = (node[i].AcomodoMixto === "S")  ? 'check' : 'close';

            var PickingC = (node[i].picking === "S")  ? 'success' : 'danger',
                PtlC = (node[i].Ptl === "S")  ? 'success' : 'danger',
                libreC = (node[i].Tipo === "L")  ? 'success' : 'danger',
                reserC = (node[i].Tipo === "R")  ? 'success' : 'danger',
                cuarenC = (node[i].Tipo === "Q")  ? 'success' : 'danger',
                mixtC = (node[i].AcomodoMixto === "S")  ? 'success' : 'danger';

            data.push([
                node[i].CodigoCSD,
                node[i].zona,
                node[i].cve_pasillo,
                node[i].cve_rack,
                node[i].cve_nivel,
                node[i].Seccion,
                node[i].Ubicacion,
                node[i].PesoMaximo,
                volumen,dimension,
                node[i].pes_porcentaje,
                node[i].vol_porcentaje,
                '<i class="text-'+PickingC+' fa fa-'+Picking+'"></i>',
                '<i class="text-'+PtlC+' fa fa-'+Ptl+'"></i>',
                '<i class="text-'+libreC+' fa fa-'+libre+'"></i>',
                '<i class="text-'+reserC+' fa fa-'+reser+'"></i>',
                '<i class="text-'+cuarenC+' fa fa-'+cuaren+'"></i>',
                '<i class="text-'+mixtC+' fa fa-'+mixt+'"></i>',
                '<a href="##" class="pointer-events: none;cursor: default;">'+
                  '<i id="button-table-'+i+'" class="glyphicon glyphicon-pencil btnEdit" onclick="editUbi('+i+')"></i>'+
                '</a>',
                '<a href="##" class="pointer-events: none;cursor: default;">'+
                  '<i id="button-table-'+i+'" class="glyphicon glyphicon-remove btnRemo" onclick="remove('+i+')"></i>'+
                '</a>',
                '<a href="##" class="pointer-events: none;cursor: default;">'+
                  '<i id="button-table-'+i+'" class="glyphicon glyphicon-search btnSearch" onclick="seachTableZona('+i+')"></i>'+
                '</a>']);
        }

        tableDataInfo.init("table-info",false, true, data);
    }
/*
    function seachTableRecu(){

        $("#modal-recu").modal();

        var alma = select_alma_re.value
            data = {action : 'search-table-recu'};

        if(alma)
            data.alma = alma;

        $.ajax({
            type: "POST",
            dataType: "json",
            data: data,
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/rutasacomodo/index.php',
            success: function(data) {
                setTimeout(function() {
                    fillTableRecu(data.table);
                }, 1000);
            },
            error:function(res){
                window.console.log(res);
            }

        });
    }
*/

    function seachTableRecu(){

        $("#modal-recu").modal();

        var grid_selector = "#grid-table2";
        var pager_selector = "#grid-pager2";

        setGridWidth(grid_selector);

        $(grid_selector).jqGrid({
            url:'/api/rutasacomodo/lista/index_i.php',
            datatype: "json",
            shrinkToFit: true,
            height:250,
            postData: {
                criterio: input_criterio.value,
                //pallet_contenedor: input_pallet_contenedor.value,
                //lp: input_lp.value,
                almacen: select_alma_re.value
            },
            mtype: 'POST',
            colNames:['','','BL','Zona de Almacenaje','Pasillo','Rack','Nivel','Sección','Posición','Peso Máx.','Dimensiones (Lar. X Anc. X Alt. )','Picking', 'Acciones'],
            colModel:[
                {name:'cve_almac',index:'cve_almac', width:80, editable:false, sortable:false, hidden:true},
                {name:'idy_ubica',index:'idy_ubica', width:150, editable:false, sortable:false, hidden:true},
                {name:'CodigoCSD',index:'CodigoCSD', width:100, fixed:true, sortable:false, resize:false, align:"center"},
                {name:'des_almac',index:'des_almac',width:250, editable:false, align:"justify", sorttype: "text"},
                {name:'cve_pasillo',index:'cve_pasillo', width:110, editable:false, align:"center", sorttype: "text"},
                {name:'cve_rack',index:'cve_rack', width:110, editable:false, align:"center"},
                {name:'cve_nivel',index:'cve_nivel',width:110, editable:false, align:"center", sorttype: "text"},
                {name:'Seccion',index:'Seccion', width:110, editable:false, align:"center", sorttype: "int"},
                {name:'Ubicacion',index:'Ubicacion', width:110, editable:false, align:"center", sorttype: "int"},
                {name:'PesoMaximo',index:'PesoMaximo', width:110, editable:false, sortable:false, align:"center"},
                {name:'dim',index:'dim',width:330, editable:false, sortable:false, align:"center"},
                {name:'picking',index:'', width:80, fixed:true, sortable:false, resize:false, align:"center",hidden:true},
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, align:"center", formatter:imageFormat},
            ],
            loadonce: false,
            rowNum:10,
            rowList:[10,20,30],
            pager: pager_selector,
            sortname: 'cve_almac',
            sortorder: "desc",
            viewrecords: true,
            loadComplete: function(data){
              console.log("-----------------------");
              console.log(data);
            }
        });

        // Setup buttons
        $("#grid-table2").jqGrid('navGrid', '#grid-pager2',
                                 {edit: false, add: false, del: false, search: false},
                                 {height: 200, reloadAfterSubmit: true}
                                );


        $(window).triggerHandler('resize.jqGrid');

        function imageFormat( cellvalue, options, rowObject ){

            var serie = rowObject[1];

            var html = '';

            html += '<a href="#" onclick="ubiRecu(\''+serie+'\')"><i class="fa fa-check" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

            return html;
        }


    }
  
    $(function($) {//EDG
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
            colNames:['id','Clave','Descripción','Pallet|Contenedor', 'License Plate (LP)','Lote','Caducidad','Serie','Existencia','Peso U(Kg)','Volumen U(m3)', 'Peso Total(kg)', 'Volumen Total(m3)'],
            colModel:[
                {name:'id',index:'id',width: 300, editable:false, sortable:false, hidden:true},
                {name:'cve_articulo',index:'cve_articulo',width: 100, editable:false, sortable:false},
                {name:'descripcion',index:'descripcion',width:400, editable:false, sortable:false},
                {name:'pallet_contenedor',index:'pallet_contenedor',width:100, editable:false, sortable:false},
                {name:'lp',index:'lp',width:100, editable:false, sortable:false},
                {name:'lote',index:'lote',width:100, editable:false, sortable:false},
                {name:'caducidad',index:'caducidad',width:100, editable:false, sortable:false},
                {name:'serie',index:'serie',width:100, editable:false, sortable:false},
                {name:'Existencia_Total',index:'Existencia_Total',width:80, editable:false, sortable:false,align:'right'},
                {name:'peso_unitario',index:'peso_unitario',width:90, editable:false, sortable:false,align:'right'},
                {name:'volumen_unitario',index:'volumen_unitario',width:132, editable:false, sortable:false,align:'right'},
                {name:'peso_total',index:'peso_total',width:100, editable:false, sortable:false,align:'right'},
                {name:'volumen_total',index:'volumen_total',width:132, editable:false, sortable:false,align:'right'},
            ],
            rowNum:10,
            rowList:[10,20,30],
            pager: pager_selector_detalles,
            viewrecords: true,
            loadComplete:function(data){
              setTimeout(function(){ 
                if(data.total != 0)
                {
                  var peso_total = "0";
                  var pesos = data.rows;
                  for(var i=0; i<pesos.length; i++)
                  {
                    peso_total = parseFloat(peso_total) + (parseFloat(pesos[i].cell[9])*pesos[i].cell[8]);
                  }
                  $("#pes_total").val(peso_total.toFixed(4));
                  $("#pes_dispo").val(($("#pes_max").val() - peso_total).toFixed(4));
                  var porcentaje_peso = (100*$("#pes_total").val())/$("#pes_max").val();
                  $("#pes_porcentaje").val(porcentaje_peso.toFixed(4));

                  var volumen_total = "0";
                  var pesos = data.rows;
                  for(var i=0; i<pesos.length; i++)
                  {
                    if(pesos[i].cell[10] != "" && pesos[i].cell[8] != "")
                    {
                      volumen_total = parseFloat(volumen_total) + (parseFloat(pesos[i].cell[10])*pesos[i].cell[8]);
                      console.log("volumen_total "+i+" = "+volumen_total);
                      console.log("pesos["+i+"].cell[10] = "+pesos[i].cell[10]);
                      console.log("pesos["+i+"].cell[8] = "+pesos[i].cell[8]);
                    }
                  }
                  $("#vol_total").val(volumen_total.toFixed(4));
                  $("#vol_dispo").val(($("#vol_max").val() - volumen_total).toFixed(4));

                  var porcentaje_vol = 0;
                  if($("#vol_max").val() != 0 && $("#vol_max").val() != null && $("#vol_max").val() != "0" && $("#vol_max").val() != "")
                    porcentaje_vol = (100*$("#vol_total").val())/$("#vol_max").val();
                  $("#vol_porcentaje").val(porcentaje_vol.toFixed(4));
                  console.log("#vol_porcentaje = "+$("#vol_porcentaje").val());
                  console.log("#vol_total = "+$("#vol_total").val());
                  console.log("#vol_max = "+$("#vol_max").val());

                  var input_peso = document.getElementById('pes_porcentaje');
                  var input_volumen = document.getElementById('vol_porcentaje');

                  if($("#pes_total").val() > $("#pes_max").val())
                  {
                    input_peso.style.borderColor = "red";
                  }
                  else
                  {
                    input_peso.style.borderColor = "green";
                  }

                  if($("#vol_total").val() > $("#vol_max").val())
                  {
                    input_volumen.style.borderColor = "red";
                  }
                  else
                  {
                    input_volumen.style.borderColor = "green";
                  }
                  $("#tabla_vacia").hide();
                }
                else
                {
                  $("#tabla_vacia").show();
                  console.log("deam");
                  $("#pes_total").val("0");
                  $("#pes_dispo").val($("#pes_max").val());
                  $("#pes_porcentaje").val("0");

                  $("#vol_total").val("0");
                  $("#vol_dispo").val($("#vol_max").val());
                  $("#vol_porcentaje").val("0");
                  var input_peso = document.getElementById('pes_porcentaje');
                  var input_volumen = document.getElementById('vol_porcentaje');
                  input_peso.style.borderColor = "green";
                  input_volumen.style.borderColor = "green";
                }
              }, 200);
              
            }
        });

        // Setup buttons
        $(grid_selector_detalles).jqGrid('navGrid', pager_selector_detalles,
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );

        $(window).triggerHandler('resize.jqGrid');
        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector_detalles).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });

    function fillTableRecu(node){

        tableDataRecu.destroy();

        var data = [];

        DATA_R = node;

        for(var i = 0; i < node.length ; i++){

            data.push([
                node[i].CodigoCSD,
                node[i].zona,
                node[i].cve_pasillo,
                node[i].cve_rack,
                '<a href="##" class="pointer-events: none;cursor: default;">'+
                  '<i id="button-tableR-'+i+'" class="glyphicon glyphicon-plus btnRecu" ></i>'+
                '</a>']);
        }

        tableDataRecu.init("table-recu",false, true, data);
        $(".btnRecu").click(function(){
            var array = this.id.split("-"),
                id = parseInt(array[2]);
            ubiRecu(DATA_R[id]);             
        });
    }

    function seachTableZona(id){

        var node = DATA[id];

        $("#modal-zona").modal();

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                action : 'search-table-zona',
                id : node.idy_ubica
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/rutasacomodo/index.php',
            success: function(data) {
                setTimeout(function() {
                    fillTableZona(data.table);
                }, 1000);
            },
            error:function(res){
                window.console.log(res);
            }
        });
    }

    function fillTableZona(node){

        tableDataZone.destroy();

        var data = [];

        for(var i = 0; i < node.length; i++){

            data.push([
                node[i].clave,
                node[i].arti,
                node[i].ubica,
                node[i].cve_lote,
                node[i].Existencia
                ]);
        }

        tableDataZone.init("table-zona",false, true, data);
    }

    function ubiRecu(idy_ubica){

        $.ajax({
            url: "/api/rutasacomodo/index.php",
            type: "POST",
            data: {
                "action" : "recuperar",
                "id": idy_ubica
            },
            beforeSend: function(x){
                if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
            },
            success: function(res){
                var data = {almacen : select_alma_re.value };

                $("#grid-table2").jqGrid('clearGridData')
                .jqGrid('setGridParam', {postData: data, datatype: 'json', page : 1})
                .trigger('reloadGrid',[{current:true}]);
            },
            error : function(res){
                window.console.log(res);
            }
        });
    }

    function save()
    {
      console.log("funcion save");
      console.log("ID_MODI = ", ID_MODI);
      if(ID_MODI)
      {
        saveEdit();
      }
      else
      {
        saveAdd();
      }
    }

    function saveEdit(){
      console.log("funcion save edite");
        var alto = input_alto.value,
            zone = select_zonaA_2.value,
            ancho = input_ancho.value,
            fondo = input_fondo.value,
            pesoM = input_pesoM.value,
            tipo = 'L',
            pick = 'N',
            ptl = 'N',
            acoMixt = 'N',
            arePro = 'N',
            controlABC = '',
            tecno = 'EDA';

        if(check_pick.checked)
            pick = 'S';
        console.log(check_ubi.checked);
        if(check_ubi.checked){
            ptl = 'S';
            tecno = 'PTL';
        }
      
          if(control_abc.checked)
          {
              if(control_tipo_A.checked) controlABC = 'A';
              else if(control_tipo_B.checked) controlABC = 'B';
              else if(control_tipo_C.checked) controlABC = 'C';
          }

        if(check_acoM.checked)
            acoMixt = 'S';

        if(check_area.checked)
            arePro = 'S';

        if(radio_status_res.checked)
            tipo = 'R';
        else if(radio_status_cua.checked)
            tipo = 'Q';
        else if(radio_status_bloq.checked)
            tipo = 'B';

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                action: 'edit',
                id : ID_MODI,
                alto : alto,
                zone : zone,
                ancho : ancho,
                fondo : fondo,
                pesoM : pesoM,
                tipo : tipo,
                pick : pick,
                ptl : ptl,
                controlABC : controlABC,
                acoMixt : acoMixt,
                arePro : arePro,
                tecno : tecno
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/rutasacomodo/index.php',
            success: function(data) { console.log(data);
                if(data.msj === "success"){
                    swal({
                        title: "¡Excelente!",
                        text: "Ubicación Modificada.",
                        type: "warning",
                        showCancelButton: false
                    });
                    cerrarAgregar();
                    buscar();
                }
            },
            error: function(res){
                window.console.log(res);
            }
        });
    }

    function saveAdd()
    {
      console.log("funcion save add");

      if( $('#radio-ubi-rack').prop('checked') && $('#input-rack').val() == '' )
      {
        swal({
          title: "Advertencia!",
          text: "Debe ingresar el Rack.",
          type: "warning",
          showCancelButton: false
        });
        return false
      }

      var almacen = select_almacen_2.value,
      zone = select_zonaA_2.value,
      pasillo = input_pasillo.value,
      rack = input_rack.value,
      alto = input_alto.value,
      ancho = input_ancho.value,
      fondo = input_fondo.value,
      pesoM = input_pesoM.value,
      ubSeccion = input_seccU.value,
      niveles = "",
      seccion = "",
      tipo = 'L',
      pick = 'N',
      ptl = 'N',
      acoMixt = 'N',
      arePro = 'N',
      tecno = 'EDA',
      code = '',
      tipoBL = '',
      controlABC = '',
      msj = false;

      if(check_pick.checked)
      {
        pick = 'S';
      }

      if(check_ubi.checked)
      {
        ptl = 'S';
        tecno = 'PTL';
      }

      if(check_acoM.checked)
      {
        acoMixt = 'S';
      }

      if(check_area.checked)
      {
        arePro = 'S';
      }

      if(radio_status_res.checked)
      {
        tipo = 'R';
      }
      else if(radio_status_cua.checked)
      {
        tipo = 'Q';
      }
      else if(radio_status_bloq.checked)
      {
        tipo = 'B';
      }

      if(radio_ubi_rack.checked)
      {
        if(radio_nivels.checked)
        {
          if(input_nivel.value === "")
          {
            msj = true;
          }
          else
          {
            niveles = input_nivel.value;
          }
        }
        else
        {
          if(input_nivelsR_D.value === "" || input_nivelsR_A.value === "")
          {
            msj = true;
          }
          else
          {
            niveles = input_nivelsR_A.value;
          }
        }
      }
      else
      {
        niveles = 0;
      }

      if(radio_secc.checked)
      {
        if(input_secc.value === "")
        {
          msj = true;
        }
        else
        {
          seccion = input_secc.value;
        }
      }
      else
      {
        if(input_seccR_D.value === "" || input_seccR_A.value === "")
        {
          msj = true;
        }
        else
        {
          seccion = input_seccR_A.value;
        }
      }

      if(control_abc.checked)
      {
          if(control_tipo_A.checked) controlABC = 'A';
          else if(control_tipo_B.checked) controlABC = 'B';
          else if(control_tipo_C.checked) controlABC = 'C';
      }

      //EDG117
      code = fillCodigo();
      
      var nivel_inicial = 0;
      var nivel_final = 0;
      var seccion_inicial = 0;
      var seccion_final = 0;
      
      if($("#input-nivelsR-D").val() != 0)
      {
         nivel_inicial = $("#input-nivelsR-D").val();
      }
      if($("#input-nivelsR-A").val() != 0)
      {
         nivel_final = $("#input-nivelsR-A").val();
      }
      else
      {
        nivel_final = $("#input-nivel").val();
      }
      
      if($("#input-seccR-D").val() != 0)
      {
        seccion_inicial = $("#input-seccR-D").val();
      }
      if($("#input-seccR-A").val() != 0)
      {
        seccion_final = $("#input-seccR-A").val();
      }
      else
      {
        seccion_final = $("#input-secc").val();
      }

console.log("*******************************************");
console.log("action         : ", 'save');
console.log("zone           : ",  zone);
console.log("pasillo        : ",  pasillo);
console.log("rack           : ",  rack);
console.log("nivel_inicial  : ",  nivel_inicial);
console.log("niveles        : ",  nivel_final);
console.log("alto           : ",  alto);
console.log("ancho          : ",  ancho);
console.log("fondo          : ",  fondo);
console.log("pesoM          : ",  pesoM);
console.log("ubSeccion      : ",  ubSeccion);
console.log("seccion_inicial: ",  seccion_inicial);
console.log("seccion        : ",  seccion_final);
console.log("tipo           : ",  tipo);
console.log("pick           : ",  pick);
console.log("ptl            : ",  ptl);
console.log("acoMixt        : ",  acoMixt);
console.log("arePro         : ",  arePro);
console.log("tecno          : ",  tecno);
console.log("code           : ",  code);
console.log("controlABC     : ",  controlABC);
console.log("*******************************************");
//return;

      if(!msj)
      {
        console.log("nivel_final = ", nivel_final);
        console.log("ubSeccion = ", ubSeccion);
        console.log("seccion_final = ", seccion_final);
        $.ajax({
          type: "POST",
          dataType: "json",
          url: '/api/ubicacionalmrutasacomodoacenaje/index.php',
          data: {
            action: 'save',
            almacen: $("#select-almacen-1").val(),
            zone : zone,
            pasillo : pasillo,
            rack : rack,
            nivel_inicial: nivel_inicial,
            niveles : nivel_final,
            alto : alto,
            ancho : ancho,
            fondo : fondo,
            pesoM : pesoM,
            ubSeccion : ubSeccion,
            seccion_inicial: seccion_inicial,
            seccion : seccion_final,
            tipo : tipo,
            pick : pick,
            ptl : ptl,
            acoMixt : acoMixt,
            arePro : arePro,
            tecno : tecno,
            controlABC: controlABC,
            code : code
          },
          beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
          },
          success: function(data) 
          {
            console.log("SUCCESS = ", data);
            if(data.msj === "success")
            {
              swal({
                title: "¡Excelente!",
                text: "Ubicación guardada.",
                type: "warning",
                showCancelButton: false
              });
              cerrarAgregar();
              buscar();
            }
            else
            {
              //text: "Estas ubicaciónes ("+data.codigos_existentes.join(',')+") ya existen",
              swal({
                title: "¡Alerta!",
                text: "Estas ubicaciónes ya existen",
                type: "warning",
                showCancelButton: false
              });
            }
          },
          error: function(res)
          {
            window.console.log(res);
          }
        });
      }
      else
      {
        swal({
          title: "¡Alerta!",
          text: "Faltan campos por llenar",
          type: "warning",
          showCancelButton: false
        });
      }

      function fillCodigo()
      {
        var code = "",
        array = input_codigo_bl.value.split("-"),
        newArray = [],
        i = 0;

        for(i = 0; i < array.length; i++)
        {
          var value = array[i];

          if(value === "Pasillo")
          newArray.push(pasillo);
          else if(value === "Rack")
          newArray.push(rack);
          else if(value === "Nivel")
          newArray.push(niveles);
          else if(value === "Seccion")
          newArray.push(seccion);
          else if(value === "Posicion")
          newArray.push(ubSeccion);
        }

        code = newArray.toString().replace(",", "-");

        for(i = 0; i < 3; i++)
        {
          code = code.toString().replace(",", "-");
        }
        return code;
      }
      
    }

    function remove(id){

        swal({
            title: "¿Está seguro que desea borrar esta ubicacion?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Borrar",
            cancelButtonText: "Cancelar"
        },
        function(){
            $.ajax({
                url: "/api/rutasacomodo/index.php",
                type: "POST",
                data: {
                    "action" : "remove",
                    "id": id
                },
                beforeSend: function(x){
                    if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
                },
                success: function(res){
                    /*swal({
                        title: "¡Alerta!",
                        text: "Ubicacion Borrada.",
                        type: "warning",
                        showCancelButton: false
                    });*/
                    buscar();
                },
                error : function(res){
                    window.console.log(res);
                }
            });
        });
    }

    function getAllUbi(){

        var JSON = {};

        $.ajax({
            url: "/api/rutasacomodo/index.php",
            type: "POST",
            data: {
                "action" : "search-all-ubica"
            },
            beforeSend: function(x){
                if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
            },
            success: function(res){
               JSON = res.res;
               console.log("Iniciando Registro");
               execute(0);
            },
            error : function(res){
                window.console.log(res);
            }
        });

        function execute(count){

            var next = count + 1;

            if(count < JSON.length){

                var node = JSON[count];
                
                var id = node["idy_ubica"],
                    cve_pasillo = node["cve_pasillo"],
                    cve_rack = node["cve_rack"],
                    cve_nivel = node["cve_nivel"],
                    Seccion = node["Seccion"],
                    Ubicacion = node["Ubicacion"];

                var code = cve_pasillo+'-'+cve_rack+'-'+cve_nivel+'-'+Seccion+'-'+Ubicacion;
                    
                $.ajax({
                    url: "/api/rutasacomodo/index.php",
                    type: "POST",
                    dataType: "json",
                     data: {
                        action: 'change-all-ubica',
                        id : id,  
                        code : code
                    },
                    beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                    },
                    success: function(res) {
                        var msj = "Registro (" + next +") de " + JSON.length;
                        window.console.log(msj);
                        execute(next);
                    },
                    error: function(res) {
                        window.console.log(res);
                    }
                });
            }
            else{
                console.log("Se guardaron todos los registros");
            }
        }
    }

    /**
     * @author Ricardo Delgado.
     * Busca y selecciona el almacen predeterminado para el usuario.
     */ 
    function almacenPrede(){
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
              console.log("data = ", data);
                if (data.success == true) {
                    select_almacen_1.value = data.codigo.clave;
                    select_alma_re.value = data.codigo.clave;
                    PREDE_ALMACEN = data.codigo.clave;
                    changeAlmacens(select_almacen_1, select_zonaA_1);
                    serchTableInfo();
                    //console.log("select_almacen_1 = ", select_almacen_1);
                    //console.log("select_zonaA_1 = ", select_zonaA_1);
                    //buscar();
                        }
                else{
                    serchTableInfo();
                }
            },
            error: function(res){
                window.console.log(res);
            }
        });
    }

    function validateNumber(e){

        var key = window.event ? e.which : e.keyCode;

        if (key < 48 || key > 57) {
            e.preventDefault();
        }
    }

    function setGridWidth(grid_selector){
        
        $(window).on('resize.jqGrid', function () {
            $(grid_selector).jqGrid( 'setGridWidth', $(".page-content").width() - 60 );
        });

        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) {
            if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $(grid_selector).jqGrid( 'setGridWidth', parent_column.width() );
                }, 0);
            }
        });
    }
  
    function traer_totales(id)
    {
      $.ajax({
        type: "POST",
        dataType: "json",
        data: {
          action: "traer_totales",//EDG
          id: id,
          almacen: $("#select-almacen-1").val(),
        },
        beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
        },
        url: '/api/rutasacomodo/lista/index.php',
        success: function(data) {
          console.log(data);
          $("#pes_"+id).val();
          $("#vol_"+id).val();
        },
      });
    }

    function serchTableInfo()
    { 

        console.log("id_proveedor:", $("#cboProveedor_busq").val());
        console.log("cve_proveedor:", $("#cve_proveedor").val());
        console.log("select-zonaA-1:", $("#select-zonaA-1").val());

        var nombre_BL = "BL";
        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";
        var criterio = "",
            almacen = select_almacen_1.value; 

        setGridWidth(grid_selector);

        $(grid_selector).jqGrid({
            
            url:'/api/rutasacomodo/lista/index.php',
            datatype: "json",
            shrinkToFit: false,
            height:'auto',
            postData: {
                criterio: criterio,
                id_proveedor: $("#cboProveedor_busq").val(),
                cve_proveedor: $("#cve_proveedor").val(),
                zone: $("#select-zonaA-1").val(),
                almacen: almacen
            },
            mtype: 'POST', //lilo
            colNames:['','','Acciones', 'Sec Acomodo',nombre_BL, 'ABC','Zona','Pallet|Contenedor', 'License Plate (LP)'/*, 'Productos Ubicados'*/,'Pasillo','Rack','Nivel','Sección','Posición','Peso Máx.' /*,'Volumen (m3)','Peso %','Volumen %','Dimensiones (Alt. X Anc. X Lar. )'*/, 'Tipo','Picking', 'PTL',/*'Maximo','Minimo',*/'Libre', 'Reservada', 'Cuarentena', 'Acomodo Mixto', 'Área de Producción' ],
            colModel:[
                {name:'cve_almac',index:'cve_almac', width:110, editable:false, sortable:false, hidden:true},
                {name:'idy_ubica',index:'idy_ubica', width:110, editable:false, sortable:false, hidden:true},
                {name:'myac',index:'', width:120, fixed:true, sortable:false, resize:false, align:"center", formatter:imageFormat},
                {name:'sec_surtido',index:'sec_surtido', width:110, fixed:true, sortable:false, resize:false, align:"center"},
                {name:'CodigoCSD',index:'CodigoCSD', width:110, fixed:true, sortable:false, resize:false, align:"center"},
                {name:'abc',index:'abc', width:80, fixed:true, sortable:false, resize:false, align:"center"},
                {name:'des_almac',index:'des_almac',width:250, editable:false, align:"justify", sorttype: "text"},
                {name:'clave_contenedor',index:'clave_contenedor',width:150,editable:false,align:"right", hidden:true},
                {name:'CveLP',index:'CveLP',width:150,editable:false,align:"right", hidden:true},
                /*{name:'ubicados',index:'ubicados',width:150,editable:false,align:"right", hidden:true},*/
                {name:'cve_pasillo',index:'cve_pasillo', width:110, editable:false, align:"center", sorttype: "text", hidden:true},
                {name:'cve_rack',index:'cve_rack', width:110, editable:false, align:"center", hidden:true},
                {name:'cve_nivel',index:'cve_nivel',width:110, editable:false, align:"center", sorttype: "text", hidden:true},
                {name:'Seccion',index:'Seccion', width:110, editable:false, align:"center", sorttype: "int", hidden:true},
                {name:'Ubicacion',index:'Ubicacion', width:110, editable:false, align:"center", sorttype: "int", hidden:true},
                {name:'PesoMaximo',index:'PesoMaximo', width:110, editable:false, sortable:false, align:"right", hidden:true},
                /*{name:'volumen',index:'volumen', width:110, editable:false, sortable:false, align:"right", hidden:true},
                {name:'pes_porcentaje',index:'pes_porcentaje', width:110, editable:false, sortable:false, align:"right", hidden:true},
                {name:'vol_porcentaje',index:'vol_porcentaje', width:110, editable:false, sortable:false, align:"right", hidden:true},
                {name:'dim',index:'dim',width:250, editable:false, sortable:false, align:"right", hidden:true},
                */
                {name:'tipo',index:'', width:80, fixed:true, sortable:false, resize:false, align:"center", formatter:imageCheck7, hidden:true},
                {name:'picking',index:'', width:80, fixed:true, sortable:false, resize:false, align:"center", formatter:imageCheck, hidden:true},
                {name:'ptl',index:'', width:80, fixed:true, sortable:false, resize:false, align:"center", formatter:imageCheck2, hidden:true},
                {name:'li',index:'', width:80, fixed:true, sortable:false, resize:false, align:"center", formatter:imageCheck3, hidden:true},
                {name:'re',index:'', width:80, fixed:true, sortable:false, resize:false, align:"center", formatter:imageCheck4, hidden:true},
                {name:'cu',index:'', width:80, fixed:true, sortable:false, resize:false, align:"center", formatter:imageCheck5, hidden:true},
                {name:'acomodomixto',index:'acomodomixto', width:110, fixed:true, sortable:false, resize:false, align:"center", formatter:acomodoMixto, hidden:true},
                {name:'areaproduccion',index:'areaproduccion', width:140, fixed:true, sortable:false, resize:false, align:"center", formatter:imageCheck6, hidden:true},
                
            ],
            loadonce: false,
            rowNum:30,
            rowList:[30,40,50],
            pager: "#grid-pager",
            sortname: 'cve_almac',
            sortorder: "desc",
            viewrecords: true,
            gridComplete: function(){
                //$("#grid-table").setGridParam({datatype: 'local'});
            },
            loadComplete:function(data){
              //var datos = data.rows;
              //for(var i=0; i<datos.length; i++)
              //{
              //  console.log("DEmo",datos[i].cell[1]);
              //  var id_ubicacion = datos[i].cell[1];
              //  traer_totales(id_ubicacion);
              //}
              console.log("*********************ABC");
              console.log(data);
              $("#num_ubicaciones").text(data.records);
              console.log("ZONA ", $("#select-zonaA-1").val());
             // $("#codigo_BL_name").html("BL: "+data.bl);
             //changeBL();
                
            }, 
              loadError: function(data)
            {
              console.log("*********************");
              console.log("ERROR", data);

            }
        });

             //setTimeout(function(){
             //   buscar();
             //}, 500);

        // Setup buttons

        $("#grid-table").jqGrid('navGrid', '#grid-pager',
                                {edit: false, add: false, del: false, search: false,reloadGridOptions: { fromServer: true }},
                                {height: 200, reloadAfterSubmit: true}
                               );

        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size

        function imageCheck7(cellvalue, options, rowObject){
            var tipo = rowObject[16];
            console.log("TIPO BR = ", tipo);
            if (tipo == '0'){
                return 'Bahía';
                //return '<input type="checkbox" checked="checked" disabled="disabled" />';
            }else{
                return 'Rack';
                //return '<input type="checkbox" disabled="disabled" />';
            }
        }

        function imageCheck(cellvalue, options, rowObject){
            var picking = rowObject[17];
            if (picking === "S"){
                return '<div class="text-success"><i class="fa fa-check"></i></div>';
                //return '<input type="checkbox" checked="checked" disabled="disabled" />';
            }else{
                return '<div class="text-danger"><i class="fa fa-close"></div</i>';
                //return '<input type="checkbox" disabled="disabled" />';
            }
        }
      
        function pes_total(cellvalue, options, rowObject){
          var id = rowObject[1];
          return '<div><input id="pes_'+id+'" disabled></div>';
        }
      
        function vol_total(cellvalue, options, rowObject){
          var id = rowObject[1];
          return '<div><input id="vol_'+id+'" disabled></div>';
        }

        function imageCheck2(cellvalue, options, rowObject){
            var picking = rowObject[18];
            if (picking === "S"){
                return '<div class="text-success"><i class="fa fa-check"></i></div>';
                //return '<input type="checkbox" checked="checked" disabled="disabled" />';
            }else{
                return '<div class="text-danger"><i class="fa fa-close"></div</i>';
                //return '<input type="checkbox" disabled="disabled" />';
            }
        }


        function imageCheck3(cellvalue, options, rowObject){
            var picking = rowObject[19];
            if (picking === "S"){
                return '<div class="text-success"><i class="fa fa-check"></i></div>';
                //return '<input type="checkbox" checked="checked" disabled="disabled" />';
            }else{
                return '<div class="text-danger"><i class="fa fa-close"></div</i>';
                //return '<input type="checkbox" disabled="disabled" />';
            }
        }

        function imageCheck4(cellvalue, options, rowObject){
            var picking = rowObject[20];
            if (picking === "S"){
                return '<div class="text-success"><i class="fa fa-check"></i></div>';
                //return '<input type="checkbox" checked="checked" disabled="disabled" />';
            }else{
                return '<div class="text-danger"><i class="fa fa-close"></div</i>';
                //return '<input type="checkbox" disabled="disabled" />';
            }
        }


        function imageCheck5(cellvalue, options, rowObject){
            var picking = rowObject[21];
            if (picking === "S"){
                return '<div class="text-success"><i class="fa fa-check"></i></div>';
                //return '<input type="checkbox" checked="checked" disabled="disabled" />';
            }else{
                return '<div class="text-danger"><i class="fa fa-close"></div</i>';
                //return '<input type="checkbox" disabled="disabled" />';
            }
        }

        function acomodoMixto(cellvalue, options, rowObject){
            var html;
            if (rowObject[22] === "S"){
                html =  '<div class="text-success"><i class="fa fa-check"></i></div>';
            }else{
                html =  '<div class="text-danger"><i class="fa fa-close"></div</i>';
            }
            return html;
        }

        function imageCheck6(cellvalue, options, rowObject){
            var html;
            if (rowObject[23] === "S"){
                html =  '<div class="text-success"><i class="fa fa-check"></i></div>';
            }else{
                html =  '<div class="text-danger"><i class="fa fa-close"></div</i>';
            }
            return html;
        }


        function imageFormat( cellvalue, options, rowObject ){
            //return '<img src="'+cellvalue+'" />&nbsp;&nbsp;&nbsp;&nbsp;<img src="'+cellvalue+'" />';
            var serie = rowObject[0];
            var idy_ubica = rowObject[1];
            var correl = rowObject[8];
            var codigo_CSD = rowObject[5];
            var peso_max = rowObject[16];
            var vol_max = rowObject[17];
            var pes_porcentaje = rowObject[18];
            var vol_porcentaje = rowObject[19];
            var url = "x/?serie="+serie+"&correl="+correl;
            var url2 = "v/?serie="+serie+"&correl="+correl;
            var clave_contenedor = rowObject[8];
            var CveLP = rowObject[9];
            var html = '';//EDG

            //console.log("serie", serie, "idy_ubica", idy_ubica, "correl", correl, "codigo_CSD", codigo_CSD, "peso_max", peso_max, "vol_max", vol_max, "pes_porcentaje", pes_porcentaje, "vol_porcentaje", vol_porcentaje, "clave_contenedor", clave_contenedor, "CveLP", CveLP);
            html += '<a href="#" onclick="ver(\'' + idy_ubica + '\', \'' + codigo_CSD + '\', \'' + peso_max + '\', \'' + vol_max + '\', \'' + pes_porcentaje + '\', \'' + vol_porcentaje + '\', \'' + clave_contenedor + '\')" alt="Ver Detalles"><i class="fa fa-eye" alt="Ver" title="Ver Detalle"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            if($("#permiso_editar").val() == 1)
            html += '<a href="#" onclick="editUbi(\''+idy_ubica+'\')" alt="Editar" title="Editar"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            if($("#permiso_liminar").val() == 1)
            html += '<a href="#" onclick="remove(\''+idy_ubica+'\')" alt="Eliminar" title="Eliminar"><i class="fa fa-eraser"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            //html += '<a href="#" onclick="exportarPDFCodigoBarras(\''+CveLP+'\')"><i class="fa fa-barcode" title="Código de barras"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';            /*html += '<a href="#" onclick="search_alm_produ('+serie+','+idy_ubica+')" alt="Buscar" title="Buscar"><i class="fa fa-search"></i></a>';*/

            return html;
        }
    
    }

$(document).ready(function() {
    buscar();
});

</script>
