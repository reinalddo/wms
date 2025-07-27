<?php
include_once  $_SERVER['DOCUMENT_ROOT']."/app/host.php";
use \AlmacenP\AlmacenP as AlmacenP;
$AlmacenP = new AlmacenP();
$listaTAlmacen = new \TipoAlmacen\TipoAlmacen();
$listaZona = new \Zona\Zona();
$listacia = new \Companias\Companias();

if(!isNikken()){
    $codDaneSql = \db()->prepare("SELECT *  FROM c_dane");
    $codDaneSql->execute();
    $codDane = $codDaneSql->fetchAll(PDO::FETCH_ASSOC);
}

$vere = \db()->prepare("select * from t_profiles as a where id_menu=10 and id_submenu=17 and id_role='".$_SESSION["perfil_usuario"]."'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);

$agre = \db()->prepare("select * from t_profiles as a where id_menu=10 and id_submenu=18 and id_role='".$_SESSION["perfil_usuario"]."'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);

$edita = \db()->prepare("select * from t_profiles as a where id_menu=10 and id_submenu=19 and id_role='".$_SESSION["perfil_usuario"]."'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);


$borra = \db()->prepare("select * from t_profiles as a where id_menu=10 and id_submenu=20 and id_role='".$_SESSION["perfil_usuario"]."'");
$borra->execute();
$borrar = $borra->fetchAll(PDO::FETCH_ASSOC);



// MOD 10

// VER 17
// AGREGAR 18
// EDITAR 19
// BORRAR 20

?>
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
</style>

<div class="wrapper wrapper-content  animated " id="list">

    <h3>Almacén</h3>

    <div class="row">
        <div class="col-md-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                <div class="input-group-btn">
                                    <a href="#" onclick="window.utils.reloadGrip(window.idTable, {criterio: $('#txtCriterio').val()});">
                                        <button type="submit" class="btn btn-primary" id="buscarA">
                                        <span class="fa fa-search"></span> Buscar
                                        </button>
                                    </a>
                                </div>
                            </div>

                        </div>
                        <div class="col-md-8">
 

                            <a href="#" onclick="agregar()"><button class="btn btn-primary permiso_registrar" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>
                            <button class="btn btn-primary pull-right" type="button" id="inactivos"><i class="fa fa-search"></i>&nbsp;&nbsp;Almacenes inactivos</button>
                            <?php if(isSCTP()): ?>
                                <button class="btn btn-primary pull-right" onclick="obtenerAlmacenesSCTP()" style="margin-right: 20px">Obtener Almacenes de Rutas</button>
                            <?php endif; ?>
                            <?php if(isLaCentral()): ?>
                                <button class="btn btn-primary pull-right" onclick="obtenerAlmacenesLaCentral()" style="margin-right: 20px">Obtener Almacenes de Rutas</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="ibox-content">

                    <div class="jqGrid_wrapper">
                        <table id="grid-table"></table>
                        <div id="grid-pager"></div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="wrapper wrapper-content  animated fadeInRight" id="FORM" style="display: none">
  <div class="row">
    <div class="col-md-12">
      <div class="ibox ">
        <div class="ibox-title">
          <div class="row">
            <div class="col-md-4" id="_title">
              <h3>Agregar Almacén</h3>
            </div>
          </div>
        </div>
        <form id="myform">
          <input type="hidden" id="hiddenAction" name="hiddenAction">
          <div class="ibox-content">
            <div class="row">
              <div class="col-md-6 b-r">
                <div class="form-group">
                  <label>Empresa *</label>
                    <select  required="true" id="cve_cia" class="chosen-select form-control">
                      <option value="">Seleccione</option>
                      <?php foreach( $listacia->getAll() AS $p ): ?>
                      <option value="<?php echo $p["cve_cia"];?>"><?php echo $p["des_cia"]; ?></option>
                      <?php endforeach; ?>
                    </select >
                </div>
                <div class="form-group">
                  <label>Clave del Almacén</label> 
                  <input id="clave" type="text" placeholder="Clave del Almacén" maxlength="20" class="form-control" required="true">
                  <label id="CodeMessage" style="color:red;"></label>
                </div>
                <div class="form-group">
                  <label>Nombre del Almacén</label> 
                  <input id="nombre" type="text" placeholder="Nombre del Almacén" class="form-control" required="true">
                </div>
                <div class="form-group">
                  <label>Tipo de Almacén</label>
                    <select  id="cve_talmacen" class="form-control" required="true">
                      <option value="">Tipo de Almacén</option>
                      <?php foreach( $listaTAlmacen->getAll() AS $p ): ?>
                      <option value="<?php echo $p["id"];?>"><?php echo $p["desc_tipo_almacen"]; ?></option>
                      <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                  <label>RUT</label> 
                  <input id="rut" type="text" placeholder="RUT" class="form-control" >
                </div>
                <div class="form-group">
                  <label>Distrito</label> 
                  <input id="distrito" type="text" placeholder="Distrito" class="form-control" >
                </div>
                <div class="form-group">
                  <label>Código Dane / Código Postal</label>
                </div>
                <div class="form-group">
                  <?php if(isset($codDane) && !empty($codDane)): ?>
                    <select id="codigopostal" class="form-control chosen-select" required="true">
                        <option value="">Código</option>
                        <?php foreach( $codDane AS $p ): ?>
                            <option value="<?php echo $p["cod_municipio"];?>"><?php echo $p["cod_municipio"]." ".$p["des_municipio"]; ?></option>
                        <?php endforeach; ?>

                    </select>
                  <?php else: ?>
                    <input type="text" name="codigopostal" id="codigopostal" class="form-control" required="true">
                  <?php endif; ?>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Departamento</label> 
                  <input disabled id="txtDepart" type="text" placeholder="Departamento" class="form-control" >
                </div>

                <div class="form-group"><label>Municipio</label> <input disabled id="txtMunicipio" type="text" placeholder="Municipio" class="form-control" ></div>
                <div class="form-group"><label>Dirección</label> <input id="direccion" type="text" placeholder="Dirección" class="form-control" required="true"></div>
                <div class="form-group"><label>Teléfono</label> <input id="telefono" type="text" maxlength="14" oninput="this.value = this.value.replace(/[^0-9]/g, '');" placeholder="Teléfono" class="form-control" required="true"></div>
                <div class="form-group"><label>Contácto</label> <input id="contacto" type="text" placeholder="Contácto" class="form-control" required="true"></div>
                <div class="form-group"><label>Correo Electrónico</label> <input id="correo" type="email" placeholder="Correo Electrónico" class="form-control"  required="true"><!--<label id="emailMessage" style="color:red;"></label>--></div>

                <div class="form-group"><label>Latitud</label> <input id="latitud" type="text" placeholder="Latitud" class="form-control"></div>
                <div class="form-group"><label>Longitud</label> <input id="longitud" type="text" placeholder="Longitud" class="form-control"  required="true"></div>

                <div class="form-group"><label>Comentarios</label> <input id="comentarios" type="text" placeholder="Comentarios" class="form-control"></div>

<br>
                  <div class="checkbox-2">
                      <label>
                          <input type="checkbox" id="almacen-local" value="0" title="Los Almacenes locales son los que están cercanos en la misma Ciudad">
                          Almacén Local
                      </label>
                  </div>
<br>
                    <div class="row">
                        <div class="col-md-3">
                    <label>Tipo de LP en Traslado:</label> 
                        </div>
                    
                        <div class="col-md-2">
                    <label style="cursor: pointer;">
                    <input type="radio" name="tipo_lp_traslado" checked id="tipo_pallet" value="Pallet" style="cursor: pointer;"> Pallet</label>
                        </div>

                        <div class="col-md-2">
                    <label style="cursor: pointer;">
                    <input type="radio" name="tipo_lp_traslado" id="tipo_contenedor" value="Contenedor" style="cursor: pointer;"> Contenedor</label>
                        </div>
                    </div>
<br>
                <div class="col-sm-6" id="div-codigo_bl">

                  <label>Cofiguración del Código BL</label>
                  <div class="row">
                      <div class="col-sm-6">
                          <div class="checkbox-2">
                              <label>
                                  <input type="checkbox" id="check-pasillo" value="Pasillo">
                                  Pasillo
                              </label>
                          </div>
                          <div class="checkbox-2">
                              <label>
                                  <input type="checkbox" id="check-rack" value="Rack">
                                  Rack
                              </label>
                          </div>
                          <div class="checkbox-2" id="cve_nivel">
                              <label>
                                  <input type="checkbox" id="check-nivel" value="Nivel">
                                  Nivel
                              </label>
                          </div>
                      </div>
                      <div class="col-sm-6">
                          <div class="checkbox-2">
                              <label>
                                  <input type="checkbox" id="check-seccion" value="Seccion">
                                  Sección
                              </label>
                          </div>
                          <div class="checkbox-2">
                              <label>
                                  <input type="checkbox" id="check-posicion" value="Posicion">
                                  Posición
                              </label>
                          </div>                            
                      </div>
                      <label>Vista preliminar del Código BL</label>
                      <input type="text" id="input-codigo-bl" readonly class="form-control" required>
                  </div>
                </div>
                
                
                <input type="hidden" id="hiddenIDAlmacenP">

                <div class="pull-right">
                    <a href="#" onclick="cancelar()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                    <button type="submit" class="btn btn-primary" data-style="contract" id="btnSave">Guardar</button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="coModal" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Advertencia</h4>
                </div>
                <div class="modal-body">
                    <p>Verificar que no hayan campos vacíos</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="alModal" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 1200px!important;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Recuperar Almacén</h4>
                </div>
                <div class="modal-body">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio1" placeholder="Buscar por Almacen...">
                            <div class="input-group-btn">
                                <a href="#" onclick=" window.utils.reloadGrip(window.idTable1, {criterio: $("#txtCriterio1").val()});">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        Buscar
                                    </button>
                                </a>
                            </div>
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


<?php if(isSCTP()): ?>
<div class="modal fade" id="modal_sctp" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-sm">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Obteniendo Almacenes de SCTP</h4>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
                        <div class="success">
                            <h3>
                                <i class="fa fa-check" style="color: #1ab394"></i>
                                ¡Almacenes cargados exitosamente!
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="button_modal_sctp" disabled="disabled" data-dismiss="modal">Aceptar</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php if(isLaCentral()): ?>
<div class="modal fade" id="modal_lacentral" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-sm">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Obteniendo Almacenes de La Central</h4>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
                        <div class="success">
                            <h3>
                                <i class="fa fa-check" style="color: #1ab394"></i>
                                ¡Almacenes cargados exitosamente!
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="button_modal_lacentral" disabled="disabled" data-dismiss="modal">Aceptar</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Mainly scripts -->
<script src="/js/jquery-2.1.4.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="/js/plugins/chosen/chosen.jquery.js"></script>
<!-- Peity -->
<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.min.js"></script>

<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>

<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>


<!-- Select -->

<script src="/js/utils.js"></script>

<script type="text/javascript">

    var colNames = ["Acciones", "ID",'Clave','Almacén',"Tipo de Almacén","Dirección","Empresa","Responsable", "Email", "Teléfono","Codigo BL", "Latitud", "Longitud", "Local", "Tipo LP Traslado"],
        colModel = [
                    {name:'myac',index:'myac', width:80, fixed:true, sortable:false, resize:false, formatter:buttonAction},
                    {name:'id',index:'id', width: 100, editable:false, hidden:true, sortable:false},
                    {name:'clave',index:'clave', width: 50, editable:false, sortable:false},
                    {name:'nombre',index:'nombre', width: 200, editable:false, sortable:false},
                    {name:'desc_tipo_almacen',index:'desc_tipo_almacen', width: 200, editable:false, sortable:false},
                    {name:'direccion',index:'direccion', width: 300, editable:false, sortable:false},
                    {name:'empresa',index:'empresa', width: 250, editable:false, sortable:false, resizable: false},
                    {name:'contacto',index:'contacto', width: 130, editable:false, sortable:false, resizable: false},
                    {name:'correo',index:'correo', width: 200, editable:false, sortable:false, resizable: false},
                    {name:'telefono',index:'telefono', editable:false, sortable:false, resizable: false},
                    {name:'BL',index:'BL', editable:false, sortable:false, resizable: false},
                    {name:'Latitud',index:'Latitud', editable:false, sortable:false, resizable: false, align: 'center'},
                    {name:'Longitud',index:'Longitud', editable:false, sortable:false, resizable: false, align: 'center'},
                    {name:'Local',index:'Local', editable:false, sortable:false, resizable: false, align: 'center'},
                    {name:'tipo_lp_traslado',index:'tipo_lp_traslado', editable:false, sortable:false, resizable: false, align: 'center'}
                    ],

        idTable = "#grid-table",
        idPage = "#grid-pager",
        url = "/api/almacenp/lista/index.php",
        data = { criterio: $("#txtCriterio").val(),
                cve_usuario: '<?php echo $_SESSION["cve_usuario"]?>',
                 id: '<?php echo $_SESSION["id_user"]?>'},
        dataReload = {criterio: $("#txtCriterio").val()},
        colNames1 = ["ID",'Clave','Almacén', "Dirección","Responsable", "Recuperar"],
        colModel1 = [
                    {name:'id',index:'id', width: 100, editable:false, hidden:true, sortable:false},
                    {name:'clave',index:'clave', width: 150, editable:false, sortable:false},
                    {name:'nombre',index:'nombre', width: 200, editable:false, sortable:false},
                    {name:'direccion',index:'direccion', width: 500, editable:false, sortable:false},
                    {name:'contacto',index:'contacto', width: 180, editable:false, sortable:false, resizable: false},
                    {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:buttonAction1},
            ],

        idTable1 = "#grid-table2",
        idPage1 = "#grid-pager2",
        url1 = "/api/almacenp/lista/index_i.php",
        data1 = {criterio: $("#txtCriterio1").val()},
        dataReload1 = {criterio: $("#txtCriterio").val()};

    window.utils.crearGrip(idTable, idPage, url, colNames, data, colModel);
    window.utils.crearGrip(idTable1, idPage1, url1, colNames1, data1, colModel1);

    function buttonAction( cellvalue, options, rowObject ){

        var proDefault = rowObject[10],
            serie = rowObject[1],
            correl = rowObject[5];

        var url = "x/?serie="+serie+"&correl="+correl;
        var url2 = "v/?serie="+serie+"&correl="+correl;
        $("#hiddenIDAlmacenP").val(serie);

        var html = '';
        html += '<a href="#" onclick="actualizarAlmaPre(\''+rowObject[2]+'\')"><i title="Asignar como Predeterminado" class="glyphicon glyphicon-'+proDefault+'" alt="Almacen Predeterminado"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

        if($("#permiso_editar").val() == 1)
        {
            html += '<a href="#" onclick="editar(\''+serie+'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
        }

        if($("#permiso_eliminar").val() == 1)
        {
            html += '<a href="#" onclick="borrar(\''+serie+'\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
        }

        return html;
    }

    function buttonAction1( cellvalue, options, rowObject ){

            var id_almacenp = rowObject[0];

            $("#hiddenIDAlmacenP").val(id_almacenp);

            var html = '';

            html += '<a href="#" onclick="recovery(\''+id_almacenp+'\')"><i class="fa fa-check" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

            return html;
    }

    function actualizarAlmaPre(almacen){


        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                alma: almacen,
                idUser: '<?php echo $_SESSION["id_user"]?>',
                action: 'cambiarAlmaPre'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/almacenp/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    window.utils.reloadGrip(window.idTable, {criterio: $("#txtCriterio").val()});
                }
            },
            error:function(res){
                window.console.log(res);
            }

        });
    }

    $('#txtCriterio').on('keyup',function () {
        window.utils.reloadGrip(window.idTable, {criterio: $("#txtCriterio").val()});
    });

    $('#txtCriterio1').on('keyup',function () {
        window.utils.reloadGrip(window.idTable1, {criterio: $("#txtCriterio1").val()});
    });

    function borrar(_codigo) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                id : _codigo,
                action : "isInUse"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/almacenp/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    swal({
                        title: "¡Alerta!",
                        text: "El almacen esta siendo usado en este momento",
                        type: "warning",
                        showCancelButton: false,
                    });
                }
                else{
                    swal({
                            title: "¿Está seguro que desea borrar el almacen?",
                            text: "Está a punto de borrar un almacen y esta acción no se puede deshacer",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "Borrar",
                            cancelButtonText: "Cancelar",
                            closeOnConfirm: false
                        },
                        function(){
                            $.ajax({
                                url: '/api/almacenp/update/index.php',
                                type: "POST",
                                dataType: "json",
                                data: {
                                    id : _codigo,
                                    action : "delete"
                                },
                                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                },
                                dataType: 'json'
                            }).done(function(data){
                                if(data.success){
                                     window.utils.reloadGrip(window.idTable, {criterio: $("#txtCriterio").val()});
                                    swal("Borrado", "El almacen ha sido borrado exitosamente", "success");
                                }else{
                                    swal("Error", "Ocurrió un error al eliminar el almacen", "error");
                                }
                        });
                    });
                }
            }
        });
    }

    function editar(_codigo) {
        $("#hiddenIDAlmacenP").val(_codigo);
        $("#_title").html('<h3>Editar Almacén</h3>');
        $("#emailMessage").html("");
        $("#CodeMessage").html("");
		$("#clave").prop('disabled', true);
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                codigo : _codigo,
                action : "load"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/almacenp/update/index.php',
            success: function(data) {
                console.log(data);
                if (data.success == true) {
                    $.each( data, function( key, value ) {
                        $('#'+key).val(value);
                    });
                    $("#cve_cia").trigger("chosen:updated");
                    $("#codigopostal").val(data.codigopostal);
                  
                    $("#input-codigo-bl").val(data.BL);
                    if (data.BL_Pasillo == 1){$("#check-pasillo").prop('checked', true);}
                    if (data.BL_Rack == 1){$("#check-rack").prop('checked', true);}
                    if (data.BL_Nivel == 1){$("#check-nivel").prop('checked', true);}
                    if (data.BL_Seccion == 1){$("#check-seccion").prop('checked', true);}
                    if (data.BL_Posicion == 1){$("#check-posicion").prop('checked', true);}
                    if (data.interno == 1){$("#almacen-local").prop('checked', true);}
                    if (data.tipolp_traslado == 'Contenedor'){$("#tipo_contenedor").prop('checked', true);}
                  
                    $("#check-pasillo").attr("disabled", true);
                    $("#check-rack").attr("disabled", true);
                    $("#check-nivel").attr("disabled", true);
                    $("#check-seccion").attr("disabled", true);
                    $("#check-posicion").attr("disabled", true);
                  
                    $( "#codigopostal" ).change();
                    $( "#hiddenIDAlmacenP" ).val(data.id);

                    //l.ladda('stop');
                    $("#btnCancel").show();

                    $('#list').removeAttr('class').attr('class', '');
                    $('#list').addClass('animated');
                    $('#list').addClass("fadeOutRight");
                    $('#list').hide();

                    $('#FORM').show();
                    $('#FORM').removeAttr('class').attr('class', '');
                    $('#FORM').addClass('animated');
                    $('#FORM').addClass("fadeInRight");

                    $("#hiddenAction").val("edit");
                }
            }
        });
    }

    function cancelar() {
        $(':input','#myform')
            .removeAttr('checked')
            .removeAttr('selected')
            .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
            .val('');
        $('#FORM').removeAttr('class').attr('class', '');
        $('#FORM').addClass('animated');
        $('#FORM').addClass("fadeOutRight");
        $('#FORM').hide();
        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeInRight");
        $('#list').addClass("wrapper");
        $('#list').addClass("wrapper-content");
        $('#list').show();  
    }

    function agregar() {
        $("#_title").html('<h3>' +' Almacén</h3>');
        $('#txtClaveCliente').prop('disabled', false);
		$("#clave").prop('disabled', false);
        $("#txtDepart").val("");
        $("#txtMunicipio").val("");
        $("#codigopostal").val("");
           $("#cve_cia").val("");
        $("#emailMessage").html("");
		$("#clave").html("");
        $("#CodeMessage").html("");
        $(':input','#myform')
            .removeAttr('checked')
            .removeAttr('selected')
            .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
            .val('');
        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeOutRight");
        $('#list').hide();
      
        $("#check-pasillo").attr("disabled", false);
        $("#check-rack").attr("disabled", false);
        $("#check-nivel").attr("disabled", false);
        $("#check-seccion").attr("disabled", false);
        $("#check-posicion").attr("disabled", false);

        $('#FORM').show();
        $('#FORM').removeAttr('class').attr('class', '');
        $('#FORM').addClass('animated');
        $('#FORM').addClass("fadeInRight");
        $("#hiddenAction").val("add");
    }

    var l = $( '#myform' );
    l.submit(function(e) {
      e.preventDefault();
		if ($("#cve_talmacen").val()=="") return;
        if ($("#cve_cia").val()=="") return;
        $("#btnCancel").hide();

        //l.ladda( 'start' );
      
        var code = '',pasillo ='',rack='',nivel='',seccion='',posicion='';

        code = fillCodigo();
      
        if ($("#check-pasillo").is(':checked'))
        {
          pasillo = 1;
        }
        else
        {
          pasillo = 0;
        }
        
        if ($("#check-rack").is(':checked'))
        {
          rack = 1;
        }
        else
        {
          rack = 0;
        }
      
        if ($("#check-nivel").is(':checked'))
        {
          nivel = 1;
        }
        else
        {
          nivel = 0;
        }
      
        if ($("#check-seccion").is(':checked'))
        {
          seccion = 1;
        }
        else
        {
          seccion = 0;
        }
      
        if ($("#check-posicion").is(':checked'))
        {
          posicion = 1;
        }
        else
        {
          posicion = 0;
        }
      

        if ($("#hiddenAction").val() == "add") {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    clave: $("#txtClaveCliente").val(),
                    action: "exists"
                },
                beforeSend: function (x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/almacenp/update/index.php',
                success: function (data) {
                  console.log("data list add almacen = ", data);
                    if (data.success == false) {
                        $.post('/api/almacenp/update/index.php',
						            {
                                clave: $('#clave').val(),
                                nombre: $('#nombre').val(),
                                rut: $('#rut').val(),
                                codigopostal: $('#codigopostal').val(),
                                direccion: $('#direccion').val(),
                                telefono: $('#telefono').val(),
                                contacto: $('#contacto').val(),
                                correo: $('#correo').val(),
                                distrito: $('#distrito').val(),
                                comentarios: $('#comentarios').val(),
                                cve_talmacen :$('#cve_talmacen').val(),
                                latitud :$('#latitud').val(),
                                longitud :$('#longitud').val(),
                                tipolp_traslado: $("input[name=tipo_lp_traslado]:checked").val(),

                                code : code,
                                pasillo: pasillo,
                                rack: rack,
                                nivel: nivel,
                                seccion: seccion,
                                posicion: posicion,

                                almacen_local: ($("#almacen-local").is(":checked"))?(1):(0),

                                cve_cia : window.utils.clearVar($('#cve_cia').val()),
                                action: "add"
                            },
                            function (response) {
                                console.log("almacen = ", response);
                            }, "json")
                            .always(function (response) {
                                //l.ladda('stop');
                                console.log("always = ", response);
                                $("#btnCancel").show();
                                cancelar();
                                 window.utils.reloadGrip(window.idTable, {criterio: $("#txtCriterio").val()});
                                 window.location.reload();
                            });
                    } else {
                        window.alert("Código ya Existe...");
                        $("#btnCancel").show();
                        //l.ladda('stop');
                    }
                }
            });
          
            
          
        } else {

            console.log("EDIT = ", "tipolp_traslado:", $("input[name=tipo_lp_traslado]:checked").val());
            if($("input[name=tipo_lp_traslado]:checked").val() == 'Pallet')
            {
                $("#tipo_pallet").prop('checked', true);
                $("#tipo_contenedor").prop('checked', false);
            }
            else
            {
                $("#tipo_pallet").prop('checked', false);
                $("#tipo_contenedor").prop('checked', true);
            }
                

            $.post('/api/almacenp/update/index.php',
                {
                    id: window.utils.clearVar($("#hiddenIDAlmacenP").val()),
                    clave: window.utils.clearVar($('#clave').val()),
                    nombre: window.utils.clearVar($('#nombre').val()),
                    rut: window.utils.clearVar($('#rut').val()),
                    codigopostal: window.utils.clearVar($('#codigopostal').val()),
                    direccion: window.utils.clearVar($('#direccion').val()),
                    telefono: window.utils.clearVar($('#telefono').val()),
                    contacto: window.utils.clearVar($('#contacto').val()),
                    correo: window.utils.clearVar($('#correo').val()),
                    distrito: window.utils.clearVar($('#distrito').val()),
                    comentarios: window.utils.clearVar($('#comentarios').val()),
                    cve_talmacen : window.utils.clearVar($('#cve_talmacen').val()),
                    latitud :$('#latitud').val(),
                    longitud :$('#longitud').val(),
                    tipolp_traslado: $("input[name=tipo_lp_traslado]:checked").val(),

                    code : code,
                    pasillo: pasillo,
                    rack: rack,
                    nivel: nivel,
                    seccion: seccion,
                    posicion: posicion,
              
                    almacen_local: ($("#almacen-local").is(":checked"))?(1):(0),

                    cve_cia : window.utils.clearVar($('#cve_cia').val()),
					action: "edit"
                },
                function (response) {
                    console.log(response);
                }, "json")
                .always(function (data) {
                    console.log("DATA = ", data);
                    $(':input', '#myform')
                        .removeAttr('checked')
                        .removeAttr('selected')
                        .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
                        .val('');
                    //l.ladda('stop');
                    $("#btnCancel").show();
                    cancelar()
                    window.utils.reloadGrip(window.idTable, {criterio: $("#txtCriterio").val()});
                });
        }

    });
  
    function fillCodigo(){
        var code = "",
        array = input_codigo_bl.value.split("-"),
        newArray = [],
        i = 0;

        for(i = 0; i < array.length; i++){

            var value = array[i];

            if(value === "Pasillo")
            newArray.push("Pasillo");
            else if(value === "Rack")
            newArray.push("Rack");
            else if(value === "Nivel")
            newArray.push("Nivel");
            else if(value === "Seccion")
            newArray.push("Seccion");
            else if(value === "Posicion")
            newArray.push("Posicion");
        }

        code = newArray.toString().replace(",", "-");

        for(i = 0; i < 3; i++)
        code = code.toString().replace(",", "-");

        return code;
      
    }

    $(document).ready(function(){

 	 $(function() {
        $('.chosen-select').chosen();
        $('.chosen-select-deselect').chosen({ allow_single_deselect: true });
      });
      $("#inactivos").on("click", function(){
            $modal0 = $("#alModal");
            $modal0.modal('show');
            window.utils.reloadGrip(window.idTable1, {criterio: $("#txtCriterio1").val()});
        });

        $( "#codigopostal" ).change(function() {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    codigo : $("#codigopostal").val(),
                    action : "getDane"
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                url: "/api/clientes/update/index.php",
                success: function(data) {
                    if (data.success == true) {
                        $("#txtDepart").val(data.departamento);
                        $("#txtMunicipio").val(data.municipio);
                    }
                }
            });
        });

    });


    $("#clave").keyup(function(e) {

        var claveCode = $(this).val();
        var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,20}$");

        if (claveCodeRegexp.test(claveCode)) {
            $("#CodeMessage").html("");
            $("#btnSave").prop('disabled', false);

			 var clave = $(this).val();
        
		$.ajax({
			type: "POST",
			dataType: "json",
			data: {
                clave : clave,
				action : "exists"
			},
		
			url: '/api/almacenp/update/index.php',
			success: function(data) {
                if (data.success == false) {
                   $("#CodeMessage").html("");
            $("#btnSave").prop('disabled', false);
             }else{
            $("#CodeMessage").html("Clave de almacén ya existe");
            $("#btnSave").prop('disabled', true);
        }
				}
			
		});
			
        }else{
            $("#CodeMessage").html("Por favor, ingresar una Clave de Almacén válida");
            $("#btnSave").prop('disabled', true);
        }
    });

	
	$("#txtCriterio").keyup(function(event){
        if(event.keyCode == 13){
            $("#buscarA").click();
        }
    });

    function recovery(_codigo) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                id : _codigo,
                action : "recovery"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/almacenp/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    window.utils.reloadGrip(window.idTable, {criterio: $("#txtCriterio").val()});
                    window.utils.reloadGrip(window.idTable1, {criterio: $("#txtCriterio1").val()});
                }
            }
        });
    }
  
    var check_pasillo = document.getElementById('check-pasillo'),
        check_rack = document.getElementById('check-rack'),
        check_nivel = document.getElementById('check-nivel'),
        check_seccion = document.getElementById('check-seccion'),
        check_posicion = document.getElementById('check-posicion'),
        input_codigo_bl = document.getElementById('input-codigo-bl'),
        div_ubi_rp = document.getElementById('div-ubi-rp'),
        div_codigo_bl = document.getElementById('div-codigo_bl'),
        btnSave = document.getElementById('btnSave');
  
    check_rack.onclick = check_nivel.onclick = check_seccion.onclick = check_posicion.onclick = check_pasillo.onclick = function(){

        if(this.checked){
            addCodBl(this.value);
        }
        else{
            removeCodBl(this.value);
        }
    };
  
    function addCodBl(newValue){
      
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
          codigo+="Seccion-";
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
  
    function saveAdd(){
      console.log("funcion save add");

        var code = '';

        code = fillCodigo();
        
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    action: 'save',
                    code : code
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                url: '/api/ubicacionalmacenaje/index.php',
                success: function(data) {
                    if(data.msj === "success"){
                        swal({
                            title: "¡Excelente!",
                            text: "Ubicación guardada.",
                            type: "warning",
                            showCancelButton: false
                        });
                        cerrarAgregar();
                        serchTableInfo();
                    }
                    else{
                        swal({
                            title: "¡Alerta!",
                            text: "Ubicación Existente",
                            type: "warning",
                            showCancelButton: false
                        });
                    }

                },
                error: function(res){
                    window.console.log(res);
                }
            });
        
       

        
    }

    <?php if(isSCTP()): ?>
        function obtenerAlmacenesSCTP(){
            $("#modal_sctp .fa-spinner").show();
            $("#modal_sctp .success").hide();
            $("#modal_sctp #button_modal_sctp").attr('disabled', 'disabled');
            $("#modal_sctp").modal('show');
            $.ajax({
                url: '/api/synchronize/sctp.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'almacenesSCTP'
                }
            }).done(function(data){
                if(data.success){
                    window.utils.reloadGrip(window.idTable, {criterio: $("#txtCriterio").val()});
                    $("#modal_sctp .fa-spinner").hide();
                    $("#modal_sctp .success").show();
                    $("#modal_sctp #button_modal_sctp").removeAttr('disabled');

                }else{
                    $("#modal_sctp").modal('hide');
                    swal("Error", data.error, "error");
                }
            });
        }
    <?php endif; ?>
    <?php if(isLaCentral()): ?>
        function obtenerAlmacenesLaCentral(){
            $("#modal_lacentral .fa-spinner").show();
            $("#modal_lacentral .success").hide();
            $("#modal_lacentral #button_modal_lacentral").attr('disabled', 'disabled');
            $("#modal_lacentral").modal('show');
            $.ajax({
                url: '/api/synchronize/lacentral.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'almacenesLaCentral'
                }
            }).done(function(data){
                if(data.success){
                    window.utils.reloadGrip(window.idTable, {criterio: $("#txtCriterio").val()});
                    $("#modal_lacentral .fa-spinner").hide();
                    $("#modal_lacentral .success").show();
                    $("#modal_lacentral #button_modal_lacentral").removeAttr('disabled');

                }else{
                    $("#modal_lacentral").modal('hide');
                    swal("Error", data.error, "error");
                }
            });
        }
    <?php endif; ?>
</script>

                           <style>

<?php if($edit[0]['Activo']==0){?>

.fa-edit{

    display: none;

}

<?php }?>


<?php if($borrar[0]['Activo']==0){?>

.fa-eraser{

    display: none;

}

<?php }?>

</style>