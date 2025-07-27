<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<link href="/css/plugins/dataTables/dataTables1.min.css" rel="stylesheet"/>
<link href="/css/plugins/dataTables/buttons.dataTables.min.css" rel="stylesheet"/>
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/switchery.min.css" rel="stylesheet">

<style>
    .bt{

        margin-right: 10px;
    }

    .btn-blue{

        background-color: blue !important;
        border-color: blue !important;
        color: white !important;
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
<input type="hidden" id="session_name" value="<?php echo $_SESSION['id_user']; ?>">
<input type="hidden" id="terminar_auditoria_user" value="0">


<?php 
$sesion_area_revision = "";
if(isset($_SESSION['IDarea']))
    $sesion_area_revision = $_SESSION['IDarea'];
?>
<input type="hidden" id="sesion_area_revision" value="<?php echo $sesion_area_revision; ?>">

<?php 
    $listaMed = new \UnidadesMedida\UnidadesMedida();

    $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $id_user = $_SESSION['id_user'];
    $sql = "SELECT nombre_completo FROM c_usuario WHERE id_user = '$id_user'";
    if(!$result = mysqli_query($conexion, $sql)) 
        echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";
    $row = mysqli_fetch_assoc($result);
    $nombre_usuario = $row['nombre_completo'];
?>
<input type="hidden" id="user_name" value="<?php echo $nombre_usuario; ?>">
<input type="hidden" id="pedido_aux" value="">
<?php $sesion = $_SESSION['name']; ?>
<?php $id_cve_almac = $_SESSION['id_almacen']; ?>

<?php 
//**************************************************************************************
//PROCESO PARA SABER SI LA INSTANCIA DEBE LLEGAR LOS PEDIDOS A AUDITORÍA COMPLETOS O NO
// Si en Rel_ModuloTipo, ID_Permiso = 3, Id_Tipo = 1, entonces debe llegar completo
//**************************************************************************************
    $sql = "SELECT COUNT(*) as existe FROM Rel_ModuloTipo WHERE ID_Permiso = 3 AND Id_Tipo = 1 AND Cve_Almac = '{$id_cve_almac}'";
    if (!($res = mysqli_query($conexion, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conexion) . ") ".$sql;
       }
    $row = mysqli_fetch_array($res);
    $pedido_completo = $row['existe'];
//**************************************************************************************
//**************************************************************************************
?>

<input type="hidden" id="pedido_completo" value="<?php echo $pedido_completo; ?>">

<audio id="audioError" style="display: none;">
    <source src="assets/sound/ErrorSound.ogg" type="audio/ogg">
</audio>

<audio id="audioCorrecto" style="display: none;">
    <source src="assets/sound/correcto.mp3" type="audio/ogg">
</audio>

<audio id="audioFinal" style="display: none;">
    <source src="assets/sound/correcto.ogg" type="audio/ogg">
</audio>

<div class="wrapper wrapper-content  animated " id="list">
    <h3>Auditoria y Empaque <span id="folio_pedido"></span></h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
              
              
                <div class="ibox-title" id="encabezado">
                    <div class="row">
                        <div class="col-lg-2">
                            <div class="form-group">
                              <label>Almacén</label>
                               
                                <select name="lote" id="select-alma" class="chosen-select form-control">
                                    <option value="">Seleccione un Almacén</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Listos Para Auditar | Auditando</label>
                                <select name="producto" id="select-pedido" class="chosen-select form-control">
                                    <option value="">Seleccione un Pedido</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Áreas de Revisión</label>
                                <select name="almacen" id="select-area" class="chosen-select form-control">
                                    <option value="">Seleccione una Area</option>
                                </select>
                            </div>
                        </div>

                      <div class="col-lg-3" style="display: none;">
                            <div class="form-group">
                                <label>Responsable de la Revision</label>
                                <select name="lote" id="select-respon" class="chosen-select form-control">
                                    <option value="">Seleccione un Responsable</option>
                                </select>
                            </div>
                      </div>

                      <div class="col-lg-1">
                            <button id="button-auditar" style="margin-top: 25px;" class="btn btn-primary btn-sm navbar-minimalize permiso_registrar" onclick="action_auditar()">Auditar</button>
                        </div>  
                    </div>
                  
                  <br>
                  <br>
                     <!--<div class="row">     
                    </div>-->
                  
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="form-group">     
                                <label>Cliente</label>                      
                                <input id="input-client" type="text" placeholder="" value="" class="form-control" readonly />
                            </div>                   
                        </div>

                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Total Pzas Factura</label>
                                <input id="input-fa" type="text" placeholder="" value="" class="form-control" readonly />
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Total Pzas Revisadas</label>
                                <input id="input-re" type="text" placeholder="" value="" class="form-control" readonly />
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Porcentaje %</label>
                                <input id="input-porce" type="text" placeholder="" value="" class="form-control" readonly />
                            </div>
                        </div>

                            
                    </div>
                </div>
              
              <!--aqui comienza-->
               <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-1">
                            <div class="form-group">
                                <label><h4>Revision</h4></label>
                                <input id="checkbox-auto" type="checkbox" class="js-switch" title="Modo automatico"/>
                                <a href="##" class="pointer-events: none;cursor: default;">
                                    <i id="i-info" class = "glyphicon glyphicon-info-sign"></i>
                                </a>
                            </div>
                        </div>
                      <div class="col-lg-2">
                            <div class="form-group">
                                <label>Ingrese Codigo a Validar <span id="label-validate" style="float: right;"></span></label>
                                <input id="input-codigo" type="text" class="form-control" disabled/>
                            </div>
                        </div>

                      <div class="col-lg-2">
                        <div class="form-group">
                            <label>Unidad de Medida</label>
                            <select class="form-control" id="unidadMedida" name="unidadMedida">
                                <!--<option value="">Seleccione unidad</option>-->
                                <?php foreach( $listaMed->getAll() AS $p ): ?>
                                <option value="<?php echo $p->id_umed; ?>"><?php echo $p->des_umed; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                      </div>

                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Articulo</label>
                                <input id="input-arti" type="text" class="form-control" readonly/>
                            </div>
                        </div>

                        <div class="col-lg-2">
                            <div class="form-group">
                                <label id="label-max">Cantidad (MAX 0)</label>
                                <input id="input-cant" type="text" class="form-control" maxlength="6" disabled/>
                            </div>
                        </div>
                      
                      <div class="col-lg-2">
                            <div class="form-group">
                                <label>Lote / Serie</label>
                                <select id="select-lote-serie" class="chosen-select form-control" disabled>
                                    <option value="">Seleccione un Lote</option>
                                </select>
                            </div>
                        </div>
                      
                    </div>
                   <!-- <div class="row">
                        </div>
                    -->

<?php 
//******************************************************************************************************************************
//******************************************************************************************************************************
?>
<div class="modal fade" id="modal-asignar-supervisor" role="dialog" data-keyboard="false" data-backdrop="static">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-sm" style="max-width: 400px !important;width: 400px;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4>Usuario quien cierra la auditoría antes sin completar</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Usuario:</label>
                        <select id="txt-supervisor" class="form-control chosen-select"></select>
                        <!--<input id="txt-supervisor" type="text" class="form-control"/>-->
                    </div>
                    <div class="form-group">
                        <label>Contraseña: </label>
                        <input type="password" class="form-control" id="txt-supervisor-pass"/>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" id="btn-asignar-supervisor" onclick="asignarSupervisor()" class="btn btn-primary">Asignar</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php 
//******************************************************************************************************************************
//******************************************************************************************************************************
?>

                    <div class="row">

                    </div>
                    <div class="row">
                        <div class="col-lg-1">
                            <button id="button-revisar" class="btn btn-primary btn-sm" onclick="actionButtonEmpacar()" disabled>Revisar</button>
                        </div>
                    <!--
                        <div class="col-lg-1">
                            <button id="button-guias" class="btn btn-primary btn-sm" onclick="">Imp. Guias</button>
                        </div>
                    -->
                    <!--
                        <div class="col-lg-1">
                            <button id="button-cerrar" class="btn btn-primary btn-sm" onclick="actionButtonCerrar(0)" disabled>Cerrar Auditoria</button>
                        </div>
                    -->
                        <div class="col-lg-1">
                            <button id="button-empacar" class="btn btn-primary btn-sm" onclick="actionButtonCerrar(0)" disabled>Empacar | F5</button>
                        </div>

                        <div class="col-lg-1">
                            <button id="terminar-auditoria" class="btn btn-primary btn-sm" onclick="showModalSupervisores()">Terminar Auditoría</button>
                        </div>
                        <div class="col-lg-1">
                            <button id="reiniciar-auditoria" class="btn btn-primary btn-sm" style="margin-left: 25px;"><i class="fa fa-refresh"></i> &nbsp;&nbsp;Reiniciar Auditoría</button>
                        </div>
                    </div>
                </div>
              <!--Aqui termina-->
                <div class="ibox-content">

            <div class="row">
                <div class="col-lg-6">
                <table class="table table-bordered">
                    <tr>
                        <td><b>TOTAL:</b></td><td style="text-align: right;"> <span id="total_suma">0</span></td>
                        <td><b>PENDIENTES:</b></td><td style="text-align: right;"> <span id="total_pendientes">0</span></td>
                        <td><b>REVISADAS:</b></td><td style="text-align: right;"> <span id="total_revisadas">0</span></td>
                    </tr>
                </table>
            </div>
            </div>


                    <div class="table-responsive" style="height: 500px;overflow-y: scroll;"> 
                        <table id="table-info"  class="table table-hover table-striped no-margin display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                <!--
                                    <th>Clave</th>
                                    <th>Articulo</th>
                                    <th>Lote</th>
                                    <th>Serie</th>
                                    <th>Caducidad</th>
                                    <th>Pedidas</th>
                                    <th>Revisadas</th>
                                    <th>Status</th>
                                    <th></th>
                                -->
                                <!--
                                    <th>Clave</th>
                                    <th>Descripción</th>
                                    <th>Lote</th>
                                    <th>Serie</th>
                                    <th>Caducidad</th>
                                    <th>Auditando</th>
                                    <th>Revisadas</th>
                                    <th>Empacadas</th>
                                    <th>Pendientes</th>
                                    <th>Tipo Caja</th>
                                    <th>No. Caja</th>
                                    <th>Pallet</th>
                                -->
                                    <th>Clave</th>
                                    <th>Descripción</th>
                                    <th>Lote</th>
                                    <th>Serie</th>
                                    <th>Caducidad</th>
                                    <th>Surtidas</th>
                                    <th>Pend. auditar</th>
                                    <th>Revisadas</th>
                                    <th>A Empacar</th>
                                    <!--<th>Pendientes</th>-->
                                </tr>
                            </thead>
                            <tbody id = "tbody">
                          </tbody>
                        </table>
                    </div>
                </div>

                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="table-info2"  class="table table-hover table-striped no-margin display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                <!--
                                    <th>Clave</th>
                                    <th>Articulo</th>
                                    <th>Lote</th>
                                    <th>Serie</th>
                                    <th>Caducidad</th>
                                    <th>Pedidas</th>
                                    <th>Revisadas</th>
                                    <th>Status</th>
                                    <th></th>
                                -->
                                <!--
                                    <th>Clave</th>
                                    <th>Descripción</th>
                                    <th>Lote</th>
                                    <th>Serie</th>
                                    <th>Caducidad</th>
                                    <th>Auditando</th>
                                    <th>Revisadas</th>
                                    <th>Empacadas</th>
                                    <th>Pendientes</th>
                                    <th>Tipo Caja</th>
                                    <th>No. Caja</th>
                                    <th>Pallet</th>
                                -->
                                    <th>Clave</th>
                                    <th>Descripción</th>
                                    <th>Lote</th>
                                    <th>Serie</th>
                                    <th>Caducidad</th>
                                    <!--<th>Auditando</th>-->
                                    <th>Empacadas</th>
                                    <th>Tipo Caja</th>
                                    <th>No. Caja</th>
                                    <th>Pallet</th>
                                    <th>LP</th>
                                </tr>
                            </thead>
                            <tbody id = "tbody2">
                          </tbody>
                        </table>
                    </div>
                </div>

              <!--aqui comienza-->
             
              <!--aqui termina-->
            </div>
        </div>
        <div class="modal fade" id="myModal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" style="padding:35px 50px;">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h2>Empacando | Packing</h2>
                    </div>
                    <div class="modal-body" style="padding:40px 50px;">
                        <form id="demo-form2" role="form" method="post" autocomplete="off">

                            <div class="form-group">
                                <label><span class="fa fa-key"></span> Factura</label>
                                <input type="text" class="form-control" id="input-factura" disabled>
                            </div>

                            <div class="form-group">
                                <label><span class="fa fa-box-check"></span> Seleccione Caja de Empaque | Packing Box</label>
                                <!--<input type="text" class="form-control" id="input-cajas" maxlength="5" required>-->
                                <select class="chosen-select form-control" id="input-cajas">
                                    <option value="">Seleccione Caja de Empaque</option>
                                <?php 
                                    $res = "";
                                    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                                    $sqlCount = "SELECT * FROM c_tipocaja WHERE Activo = 1 AND Packing = 'S'";
                                    //AND Packing = 'S'
                                    if (!($res = mysqli_query($conn, $sqlCount))) {
                                        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
                                    }

                                    while($row = mysqli_fetch_array($res))
                                    {
                                ?>
                                        <option value="<?php echo $row['id_tipocaja']; ?>"><?php echo "[ ".$row['clave']." ] - ".$row['descripcion']; ?></option>
                                <?php 
                                    }
                                ?>
                                </select>

                            </div>

                            <div class="form-group">
                                <label><span class="fa fa-box-check"></span> # de Cajas: </label><input type="number" name="ncajas" id="ncajas" class="form-control" style="width: 100px;">

                            </div>

                            <div class="form-group">
                                <label><span class="fa fa-box-check"></span>Pallet | Contenedor</label>
                                <!--<input type="text" class="form-control" id="input-cajas" maxlength="5" required>-->
                                <select class="chosen-select form-control" id="select-pallets">
                                    <option value="">Seleccione Pallet | Contenedor</option>
                                </select>
                            </div>

                            <div class="form-group" id="zona-embarque" style="display: none;">
                                <label><span class="fa fa-box-check"></span> Seleccione Zona de Embarque </label>
                                <!--<input type="text" class="form-control" id="input-cajas" maxlength="5" required>-->
                                <select class="chosen-select form-control" id="input-zona-embarque">
                                    <option value="000X">Seleccione Zona de Embarque</option>
                                <?php 
                                    $res = "";
                                    $id_almacen = $_SESSION['id_almacen'];
                                    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                                    $sqlCount = "SELECT ID_Embarque, cve_ubicacion, descripcion FROM t_ubicacionembarque WHERE Activo = 1 AND cve_almac = {$id_almacen} AND AreaStagging = 'N'";
                                    if (!($res = mysqli_query($conn, $sqlCount))) {
                                        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
                                    }

                                    while($row = mysqli_fetch_array($res))
                                    {
                                ?>
                                        <option value="<?php echo $row['cve_ubicacion']; ?>"><?php echo "[".$row['cve_ubicacion']."] - ".$row['descripcion']; ?></option>
                                <?php 
                                    }
                                ?>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <div style="text-align: center;display: none;">
                            <a href="#" id="button-imp-packing-list" class="btn btn-success" onclick="printPDF()">Imprimir Packing List</a>
                            <button id="button-imp-guias" class="btn btn-success">Imprimir Guías</button>
                        </div>
                        <br>

                        <div class="row" id="loadgif" style="text-align: center;padding: 0; display: none;font-size: 16px; position: relative;">
                            <div style="width: 50px;height: 50px;background-image: url(/img/load.gif);background-size: 100%;background-position: center;background-repeat: no-repeat;display: inline-flex;"></div>
                        </div>


                        <button id="button-asignar" onclick="CerrarPallet()" class="btn btn-success btn-block"><span class="glyphicon glyphicon-check"></span> Continuar | Embarcar<span id="tag.Modify"></span></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-info" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" style="padding:35px 50px;">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h2>Modo automatico</h2>
                    </div>
                    <div class="modal-body" style="padding:40px 50px;">
                        <p>Este modo permite empacar articulos de forma automatica, una vez se haya llenado el campo 'Codigo a Validar'.</p>
                        <br>
                        <p>Tenga en cuenta que solo se puede empacar un articulo a la vez.</p>
                    </div>
                    <div class="modal-footer">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Mainly scripts -->
<script src="/js/jquery-2.1.4.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>

<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>

<script src="/js/plugins/ladda/spin.min.js"></script>
<script src="/js/plugins/ladda/ladda.min.js"></script>
<script src="/js/plugins/ladda/ladda.jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="/js/switchery.min.js"></script>
<!-- Select -->
<script src="/js/select2.js"></script>
<!-- Data picker -->
<script src="/js/plugins/dataTables/dataTables.min.js"></script>
<script src="/js/plugins/dataTables/dataTables_bootstrap.min.js"></script>
<!--
<script src="/js/plugins/dataTables/dataTables.buttons.min.js"></script>
<script src="/js/plugins/dataTables/buttons.flash.min.js"></script>
<script src="/js/plugins/dataTables/jszip.min.js"></script>
<script src="/js/plugins/dataTables/pdfmake.min.js"></script>
<script src="/js/plugins/dataTables/vfs_fonts.js"></script>
<script src="/js/plugins/dataTables/buttons.html5.min.js"></script>
<script src="/js/plugins/dataTables/buttons.print.min.js"></script> 
-->
<script src="/js/plugins/chosen/chosen.jquery.js"></script>
<script src="/js/utils.js"></script>   
<script>
/*$(document).ready(function(){
    var kardex = new Kardex(); 
});
function Kardex(){*/
$(document).ready(function(){


console.log("sesion_area_revision = ", $("#sesion_area_revision").val());

var folio = '', folio_selected = '';
function verificar_pedido()
{
    folio = $("#select_pedido_chosen a span").text();

    console.log("*****************************************");
    console.log("*****************************************");
    console.log("*****************************************");
    console.log("folio = ", folio);
    console.log("sufijo = ", select_pedido.value);
    console.log("area = ", select_area.value);
    console.log("*****************************************");
    console.log("*****************************************");
    console.log("*****************************************");
    console.log("Entró en verificar_pedido()");
    console.log("*****************************************");

    $.ajax({
        url: "/api/qaauditoria/index.php",
        type: "POST",
        data: {
            "action" : "verificar_pedido",
            "area": select_area.value,
            "folio": folio,
            "sufijo": select_pedido.value
        },
        beforeSend: function(x){
            if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
        },
        success: function(res)
        {
            console.log("ocupadoX", res.ocupado);
            console.log("res.id_mesaX", res.id_mesa);
            if(res.ocupado)
            {
                //$("#select_area_chosen a span").text(res.mesa);

                /*
                $("#select_area_chosen .chosen-drop .chosen-results li").each(function(i){

                    if($(this).hasClass("result-selected"))
                        $(this).removeClass("result-selected");

                    if($(this).text() == res.mesa)
                    {
                        $(this).addClass("result-selected highlighted");
                        $("#select_area_chosen a span").text(res.mesa);
                    }

                });
                */
                $("#select_area_chosen a span").text(res.mesa);
                select_area.value = res.id_mesa;
                action_auditar();
            }
        },
        error : function(res){
            window.console.log(res);
        }
    });
}

function verificar_pedido_app() //esta es l función que va a leer el celular ya que no reconoce chosen
{
    folio = $("#select-pedido option:selected").text();

    console.log("VVVVVVVVVVVVVVVVVVVVVVVVVVVVV");
    var pedido_select = folio.split(" ");
    console.log("folio app = ", folio);

    if(pedido_select[0] == 'Seleccione')
    {
        var select_area_text = $("#select-area option:selected").text();
        var pedido_area = select_area_text.split(" - ");
        var select_area_val = $("#select-area option:selected").val();
        folio = pedido_area[1];
        console.log("folio app val area = ", select_area_val);
        select_area.value = select_area_val;
        folio_selected = folio;
    }

    //folio = folio.split("-")[0];
    //*****************************************************************************************
    // Este proceso se hace para que en una instancia que tenga pedidos con un formato por 
    // ejemplo AA-BBBBB-Sufijo, entonces no se confunda el sistema por el guión (-) y 
    // asegurarse de que el sufijo se tome desde el último guión y no el primero.
    //*****************************************************************************************
    //folio = $("#pedido_aux").val();
    var folio_arr = folio.split(""), sufijo = "", hay_sufijo = false;
    for(var i = folio_arr.length; i > 0; i--)
    {
        if(folio_arr[i] == '-')
        {
           folio_arr[i] = folio_arr[i].replace('-', '***');
           hay_sufijo = true;
           break;
        }
    }

    console.log("folio_arr = ", folio_arr);
    if(hay_sufijo)
    {
        folio = folio_arr.join("");
        sufijo = folio.split("***")[1];
        folio = folio.split("***")[0];
    }
    //*****************************************************************************************
    //*****************************************************************************************
    //console.log("sufijo = ", select_pedido.value);
    console.log("sufijo = ", sufijo);
    console.log("folio app = ", folio);
    console.log("select_area.value = ", select_area.value);
    console.log("Entró en verificar_pedido_app");
    console.log("^^^^^^^^^^^^^^^^^^^^^^^^^^^");

    //folio += "-"+select_pedido.value;

    $.ajax({
        url: "/api/qaauditoria/index.php",
        type: "POST",
        data: {
            "action" : "verificar_pedido",
            "area": select_area.value,
            "folio": folio,
            "sufijo": sufijo
        },
        beforeSend: function(x){
            if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
        },
        success: function(res)
        {
            console.log("res.ocupado", res.ocupado);
            console.log("res.id_mesa", res.id_mesa);
            console.log("res.mesa", res.mesa);
            if(res.ocupado)
            {
                //$("#select_area_chosen a span").text(res.mesa);

//                
//                $("#select_area_chosen .chosen-drop .chosen-results li").each(function(i){
//
//                    if($(this).hasClass("result-selected"))
//                        $(this).removeClass("result-selected");
//
//                    if($(this).text() == res.mesa)
//                    {
//                        $(this).addClass("result-selected highlighted");
//                        $("#select_area_chosen a span").text(res.mesa);
//                    }
//
//                });
//
                //$("#select-area option:selected").text(res.mesa);
                $("#select_area_chosen a span").text(res.mesa);
                select_area.value = res.id_mesa;
                //console.log("BF action_auditar, select_area.value = ",select_area.value);
                action_auditar();
            }
        },
        error : function(res){
            console.log("ERROR app", res);
        }
    });

}

    //$('select').trigger('click');
    $("#select_pedido_chosen").trigger('mousedown');

    $("#select_pedido_chosen .chosen-drop").bind('keyup',function(e) {
        //console.log("OK tecla");
        if(e.which === 13) 
        {
            //console.log("OK Enter Next");
            console.log("ENTER 2");
            console.log("Entró en #select_pedido_chosen .chosen-drop).bind");
            $("#select_area_chosen").trigger('mousedown');
            console.log($("#select_pedido_chosen a span").text());
            verificar_pedido();
            //$("#folio_pedido").text(" | "+$("#select_pedido_chosen a span").text());
        }
    });

    $("#select-pedido, #select-area").change(function(){

        console.log("Entró en #select-pedido.change");
        verificar_pedido_app();

    });


    $("#select_area_chosen .chosen-drop").bind('keyup',function(e) {
        //console.log("OK tecla");
        if(e.which === 13) {

            //console.log("OK Enter Next");
            console.log("ENTER 3");
            action_auditar();
        }
    });

    //select_respon.value = 1;

     $(document).keydown(function(e){
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code == 116) {
       e.preventDefault();
       e.keyCode=0;
       console.log("OK F5");
       if(!button_empacar.disabled)
        actionButtonCerrar(0);
        return false;
      }
     });

    $("#select_pedido_chosen .chosen-results").click(function(){

        //console.log($("#select_pedido_chosen a span").text());
        console.log("Entró en #select_pedido_chosen .chosen-results");
        verificar_pedido();

    });
});

    var initSwich = '';
    var cod_articulos_empacados    = [];
    var cod_lotes_series_articulos = [];
    var val_articulos_empacados    = [];
    var val_articulos_revisados    = [];
    var articulos_tabla = [];
    var g_pos = 0;
    var valor_empacados = [];
    var array_init = false;
    
    if(typeof Switchery !== 'undefined')
        initSwich = new Switchery(document.querySelector('.js-switch'));

    var self = this;

    var select_almacen = document.getElementById('select-alma'),
        select_area = document.getElementById('select-area'),
        select_pedido = document.getElementById('select-pedido'),
        select_pallets = document.getElementById('select-pallets'),
        select_respon = document.getElementById('select-respon'),
        button_auditar = document.getElementById('button-auditar'),
        button_cerrar = document.getElementById('button-cerrar'),
        button_revisar = document.getElementById('button-revisar'),
        button_empacar = document.getElementById('button-empacar'),
        input_client = document.getElementById('input-client'),
        select_unidadmedida = document.getElementById('unidadMedida'),
        input_fa = document.getElementById('input-fa'),
        input_re = document.getElementById('input-re'),
        input_porce = document.getElementById('input-porce'),
        input_codigo = document.getElementById('input-codigo'),
        input_arti = document.getElementById('input-arti'),
        input_cant = document.getElementById('input-cant'),
        label_max = document.getElementById('label-max'),
        select_lote_serie = document.getElementById('select-lote-serie'),
        input_factura = document.getElementById('input-factura'),
        input_cajas = document.getElementById('input-cajas'),
        input_zona_embarque = document.getElementById('input-zona-embarque'),
        checkbox_auto = document.getElementById('checkbox-auto'),
        tableData = new TableData(),
        tableData2 = new TableData(),
        TABLE = null,
        TABLE2 = null,
        JSONA = {},
        JSONB = {},
        JSONC = {},
        ITEM_SELECT = null,
        RED = '#F5A9A9',
        YELLOW = '#F2F5A9',
        GREEN = '#A9F5A9'
        PURPLE = '#CC99FF';

    $('.chosen-select').chosen();

    $("#i-info").click(function(){
        $("#modal-info").modal();
    });

    select_almacen.onchange = function(){searchSelectArea();};
    select_unidadmedida.onchange = function(){changeLote("", 1);};
    select_lote_serie.onchange = function(){changeLote("", 1);};
    input_cant.addEventListener("keypress", validateNumber, false);
    //input_cajas.addEventListener("keypress", validateNumber, false);
    input_codigo.addEventListener("keyup", function(e){fillFieldAudi(false, e);input_cant.value = 1;});

    init();

    function init(){
        console.log("id_user", <?php echo $_SESSION['id_user']; ?>);
        $.ajax({
            url: "/api/kardex/index.php",
            type: "POST",
            data: {
                id_user: <?php echo $_SESSION['id_user']; ?>,
                "action" : "enter-view"
            },
            beforeSend: function(x){
                if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
            },
            success: function(res){
                console.log("SUCCESS Init = ", res);
                fillSelectAlmacen(res.almacen);
                $("#input-codigo").focus();
                almacenPrede(false);
            },
            error : function(res){
                console.log("ERROR Init = ", res);
            }
        });
    }

    function searchSelects(almacen, fill_solo_pallets, fill_pedidos){

        //var almacen = select_almacen.value;
        console.log("almacend = ", almacen);
        console.log("fill_solo_pallets = ", fill_solo_pallets);
        console.log("almacend2 = ", almacen);
        console.log("pedido_completo = ", $("#pedido_completo").val());
        $.ajax({
            url: "/api/qaauditoria/index.php",
            type: "POST",
            data: {
                "action" : "enter-view",
                pedido_completo : $("#pedido_completo").val(),
                alma : almacen
            },
            beforeSend: function(x){
                if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
            },
            success: function(res){
                console.log("res = ", res);
                console.log("************fill_solo_pallets = ", fill_solo_pallets);
                if(!fill_solo_pallets || fill_pedidos)
                {
                    fillSelectPedidos(res.pedidos);
                    console.log("*****QA***", res.pedidos);
                }
                console.log("*****res pallets***", res.pallets);

                fillSelectPallets(res.pallets);

                if(!fill_solo_pallets)
                    searchSelectArea();
            },
            error : function(res){
                window.console.log(res);
            }
        });
    }

    function searchSelectArea(){

        var almacen = select_almacen.value;

        console.log("Almacen = ", almacen);
        console.log("pedido_completo = ", $("#pedido_completo").val());
        if(almacen){

            $.ajax({
                url: "/api/qaauditoria/index.php",
                type: "POST",
                data: {
                    "action" : "search-area-alma",
                    "pedido_completo" : $("#pedido_completo").val(),
                    "alma" : almacen
                },
                beforeSend: function(x){
                    if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
                },
                success: function(res){
                    console.log("search-area-alma",res);
                    fillSelectArea(res.area);
                    fillSelectRespon(res.users);
                    if(res.pedidos.length > 0)
                        fillSelectPedidos(res.pedidos);
                    //console.log("*****QA2***", res.pedidos.length);
                    searchSelects(almacen, true, true);
                },
                error : function(res){
                    window.console.log(res);
                }
            });
        }
        else{
            fillSelectArea();
            fillSelectRespon();
        }
    }

    function fillSelectAlmacen(node){

        var options = "";
        console.log("fillSelectAlmacen = ", node, "node.length = ", node.length);
        if(node){

            for(var i = 0; i < node.length; i++){
                options += "<option value = "+node[i].clave+">"+htmlEntities("("+node[i].clave+") - "+node[i].nombre)+"</option>";
            }
        }

        select_almacen.innerHTML += options;
        $(select_almacen).trigger("chosen:updated");
    }

    function fillSelectArea(node){

        var options = "<option value = ''>Seleccione una Area (0)</option>";

        if(node){

            options = "<option value = ''>Seleccione una Area ("+node.length+")</option>";

            var folio_mesa = "", val_sufijo = "";
            for(var i = 0; i < node.length; i++){
                folio_mesa = "";
                val_sufijo = "-"+node[i].sufijo;
                if(node[i].sufijo == '') val_sufijo = "";
                if(htmlEntities(node[i].fol_folio)!="") folio_mesa = " - "+htmlEntities(node[i].fol_folio+val_sufijo);
                options += "<option value = "+node[i].ID_URevision+">"+htmlEntities(node[i].descripcion)+folio_mesa+"</option>";
            }
        }

        select_area.innerHTML = options;
        select_area.value = $("#sesion_area_revision").val();
        $(select_area).trigger("chosen:updated");

    }

    function fillSelectPedidos(node){

        var options = "<option value = ''>Seleccione un Pedido (0)</option>";

        if(node){

            options = "<option value = ''>Seleccione un Pedido ("+node.length+")</option>";
            var val_pedido = "";
            for(var i = 0; i < node.length; i++){
                val_pedido = "-"+node[i].id_pedido;
                if(node[i].id_pedido == node[i].Fol_folio)
                    val_pedido = "";
                options += "<option value = "+node[i].id_pedido+">"+htmlEntities(node[i].Fol_folio+val_pedido)+"</option>";
            }
        }

        select_pedido.innerHTML = options;
        $(select_pedido).trigger("chosen:updated");
    }

    function fillSelectPallets(node){

        var options = "<option value = ''>Seleccione un Pallet | Contenedor</option>";

        if(node){

            options = "<option value = ''>Seleccione un Pallet | Contenedor ("+node.length+")</option>";

            for(var i = 0; i < node.length; i++){
                options += "<option value = "+node[i].IDContenedor+">"+htmlEntities("( "+node[i].clave_contenedor+" ) "+node[i].descripcion)+"</option>";
                //options += "<option value = "+node[i].IdCont+">"+htmlEntities("( "+node[i].Cve_Cont+" ) "+node[i].DesCont)+"</option>";
            }
        }

        select_pallets.innerHTML = options;
        $(select_pallets).trigger("chosen:updated");
    }

    function fillSelectRespon(node){

        var options = "<option value = ''>Seleccione un Responsable (0)</option>";

        if(node){

            options = "<option value = ''>Seleccione un Responsable ("+node.length+")</option>";

            for(var i = 0; i < node.length; i++){
                options += "<option value = "+node[i].nombre_completo+">"+htmlEntities(node[i].nombre_completo)+"</option>";
            }
        }

        select_respon.innerHTML = options;
        $(select_respon).trigger("chosen:updated");
    }

    function changeLote(e, i){

        var lote, node, max, code;

        lote = select_lote_serie.value;
        code = input_codigo.value;
        input_cant.disabled = false;
        if(i == 1)
        input_cant.value = 1;
        changeClass(false);

        console.log("changelote() = code = ", code);
        console.log("changelote() = lote = ", lote);
        console.log("ITEM_SELECT = ", ITEM_SELECT);


        if(typeof JSONA[code] !== 'undefined')
        {
            node = JSONA[code][lote];
            ITEM_SELECT = node;
        }
        else if(typeof JSONB[code] !== 'undefined')
        {
            node = JSONB[code][lote];
            ITEM_SELECT = node;
        }
        else if(typeof JSONC[code] !== 'undefined')
        {
            node = JSONC[code][lote];
            ITEM_SELECT = node;
        }


        //console.log("NODE = ", node);
        //if(ITEM_SELECT.barras2 == code)
            //$("#unidadMedida").val(2);

        input_arti.value = ITEM_SELECT.des_articulo;

        max = parseInt(ITEM_SELECT.Num_cantidad) - parseInt(ITEM_SELECT.Num_revisadas);

        console.log("max = ", max);
        console.log("ITEM_SELECT.Num_cantidad = ", ITEM_SELECT.Num_cantidad);
        console.log("ITEM_SELECT.num_multiplo = ", ITEM_SELECT.num_multiplo);
        console.log("ITEM_SELECT.Num_revisadas = ", ITEM_SELECT.Num_revisadas);

var str = $("#unidadMedida option:selected").text();
var es_caja = false;
if(str.toLowerCase().indexOf("caja") >= 0)
{
    es_caja = true;
}
        console.log("str = ", str);
        console.log("es_caja = ", es_caja);

        if(ITEM_SELECT.num_multiplo != '' && ITEM_SELECT.num_multiplo > 0 && es_caja == true)// $("#unidadMedida").val() == 2
        {
        max = Math.trunc((ITEM_SELECT.Num_cantidad/ITEM_SELECT.num_multiplo) - (ITEM_SELECT.Num_revisadas/ITEM_SELECT.num_multiplo));
        }
        else 
        {
            //max = ITEM_SELECT.Num_cantidad;
        }
        label_max.innerHTML = "Cantidad (MAX "+max+")";

        if(checkbox_auto.checked){
            actionButtonEmpacar();
        }
        else if(e){
            if (e.keyCode === 13 && !e.shiftKey) {
                console.log("ENTER 1");
                actionButtonEmpacar();
            }
        }
    }

    $("#input-cant").keydown(function(e){

        if(e){
            if (e.keyCode === 13 && !e.shiftKey) {
                console.log("ENTER input-cant");
                actionButtonEmpacar();
            }
        }

    });

    function fillFieldAudi(lote, e){

        var code = input_codigo.value;
        code = code.toUpperCase();

        input_codigo.value = code;

        console.log("code = ", code);
        console.log("JSONA[code] = ", JSONA[code]);
        console.log("JSONB[code] = ", JSONB[code]);
        console.log("JSONC[code] = ", JSONC[code]);

        if(code != '')
        {
            if(typeof JSONA[code] !== 'undefined' || typeof JSONB[code] !== 'undefined' || typeof JSONC[code] !== 'undefined'){

                fillLotes(code);

                if(lote){

                    if(lote !== "null")
                        select_lote_serie.value = lote;
                    else
                        select_lote_serie.value = '';

                    $(select_lote_serie).trigger("chosen:updated");
                }
                changeLote(e, 0);
            }
            else{
                input_cant.disabled = true;
                input_cant.value = "";
                input_arti.value = "";
                if(code !== "")
                    changeClass(true);
                label_max.innerHTML = "Cantidad (MAX 0)";
                $('#select-lote-serie').prop('disabled', true).trigger("chosen:updated");
            }
        }
    }

    function fillLotes(code){

        var count = 0;
        select_lote_serie.innerHTML = '';

        for(var lote in JSONA[code]){
            console.log("xxxxxxxxxxxxxxxxxx");
            console.log("JSONA = ", lote);
            console.log("xxxxxxxxxxxxxxxxxx");
            if(lote !== 'descrip'){
                select_lote_serie.innerHTML += "<option value = '"+lote+"''>"+lote+"</option>";
                count++;
            }
        }

        for(var lote in JSONB[code]){
            console.log("xxxxxxxxxxxxxxxxxx");
            console.log("JSONB = ", lote);
            console.log("xxxxxxxxxxxxxxxxxx");
            if(lote !== 'descrip'){
                select_lote_serie.innerHTML += "<option value = '"+lote+"''>"+lote+"</option>";
                count++;
            }
        }

        for(var lote in JSONC[code]){
            console.log("xxxxxxxxxxxxxxxxxx");
            console.log("JSONC = ", lote);
            console.log("xxxxxxxxxxxxxxxxxx");
            if(lote !== 'descrip'){
                select_lote_serie.innerHTML += "<option value = '"+lote+"''>"+lote+"</option>";
                count++;
            }
        }

        $(select_lote_serie).trigger("chosen:updated");

        if(count > 0)
            $('#select-lote-serie').prop('disabled', false).trigger("chosen:updated");
        else
            $('#select-lote-serie').prop('disabled', true).trigger("chosen:updated");
    }

    function action_auditar()
    {
        if($("#permiso_registrar").val() != 1) return;

        var select_pedido_text = $("#select_pedido_chosen a span").text();
        if(!select_pedido_text) select_pedido_text = $("#select-pedido option:selected").text();

        var select_area_text = $("#select_area_chosen a span").text();
        if(!select_area_text) select_area_text = $("#select-area option:selected").text();

        var pedido = select_pedido.value,
            folio = select_pedido.options.item(select_pedido.selectedIndex).text,
            area = select_area.value,
            respon = $("#session_name").val(),//select_respon.value,
            alma = select_almacen.value;

            console.log("1_action_auditar folio = ", folio);
//            console.log("1_action_auditar folio_selected = ", folio_selected);
//            if(folio_selected) folio = folio_selected;
//            console.log("2_action_auditar folio = ", folio);
//            console.log("2_action_auditar folio_selected = ", folio_selected);

        var pedido_area = select_area_text.split(" - ");


        //folio = folio.split("-")[0];
        //*****************************************************************************************
        // Este proceso se hace para que en una instancia que tenga pedidos con un formato por 
        // ejemplo AA-BBBBB-Sufijo, entonces no se confunda el sistema por el guión (-) y 
        // asegurarse de que el sufijo se tome desde el último guión y no el primero.
        //*****************************************************************************************
        //folio = $("#pedido_aux").val();
        var folio_arr = folio.split(""), sufijo = "", hay_sufijo = false;
        for(var i = folio_arr.length; i > 0; i--)
        {
            if(folio_arr[i] == '-')
            {
                folio_arr[i] = folio_arr[i].replace('-', '***');
                hay_sufijo = true;
                break;
            }
        }
        if(hay_sufijo)
        {
            folio = folio_arr.join("");
            //pedido = folio.split("***")[1];
            folio = folio.split("***")[0];
            sufijo = pedido_area[1]
        }
        //*****************************************************************************************
        //*****************************************************************************************

        console.log("------------------------------------");
        console.log("pedido_area = ", pedido_area);
        console.log("pedido_area.length = ", pedido_area.length);
        console.log("pedido_area[1] = ", sufijo);
        console.log("pedido = ", pedido);
        console.log("folio = ", folio);
        console.log("area = ", area);
        console.log("respon = ", respon);
        console.log("alma = ", alma);
        console.log("------------------------------------");

        //if(pedido_area[1] != "" && pedido_area[1] != "undefined") 
        if(pedido_area.length > 1 || sufijo == "")
        {
            console.log("entró en split[1]");
            $("#select_pedido_chosen a span").text(pedido_area[1]);
            select_pedido_text = $("#select_pedido_chosen a span").text();
            //select_pedido = document.getElementById('select-pedido');
            folio = sufijo;
            pedido = "-";
        }

            //console.log("select_area2 = ", select_area.value);
            //console.log("area = ", area);


        if(pedido != "" && area != "" && respon != "" && alma !== ""){
            /*swal({
                title: "¿Está seguro que desea auditar el pedido '"+folio+"'?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Auditar",
                cancelButtonText: "Cancelar"
                
            },
            function(){
              */
                $("#folio_pedido").text(" | "+select_pedido_text+" | "+select_area_text+" | "+$("#user_name").val());
                $("#encabezado").hide();
                $("#pedido_aux").val(select_pedido_text);
                loadPedido();
          
           // });
        }
        else{
            swal({
                title: "¡Alerta!",
                text: "Debe seleccionar primero un 'Almacen', una 'Area de Revision', un 'Pedido' y un 'Responsable de la Revision'",
                type: "warning",
                showCancelButton: false
            });
        }
    }

    var folio_auto = "";
    function loadPedido(){

        var pedido = select_pedido.value,
            folio = select_pedido.options.item(select_pedido.selectedIndex).text,
            area = select_area.value,
            respon = $("#session_name").val();//select_respon.value
         //folio = $("#select_pedido_chosen a span").text();

        var select_area_text = $("#select_area_chosen a span").text();
        if(!select_area_text) select_area_text = $("#select-area option:selected").text();

        var pedido_area = select_area_text.split(" - ");

        //if(pedido_area[1] != "" && pedido_area[1] != "undefined") 
/*
        if(pedido_area.length > 1)
        {
            console.log("entró en split[1]");
            $("#select_pedido_chosen a span").text(pedido_area[1]);
            select_pedido_text = $("#select_pedido_chosen a span").text();
            //select_pedido = document.getElementById('select-pedido');
            folio = pedido_area[1];
            pedido = "-";
        }
*/
            //console.log("pedido = ", pedido);
            //console.log("folio = ", folio);
            //console.log("area = ", area);
        $('#select-area').prop('disabled', true).trigger("chosen:updated");
        $('#select-pedido').prop('disabled', true).trigger("chosen:updated");
        $('#select-respon').prop('disabled', true).trigger("chosen:updated");
        $('#select-lote-serie').prop('disabled', true).trigger("chosen:updated");

        input_cant.disabled = true;
        input_cant.value = "";
        input_arti.value = "";
        input_codigo.value = "";
        input_codigo.disabled = false;
        changeClass(false);
        label_max.innerHTML = "Cantidad (MAX 0)";

        //folio = folio.split("-")[0];
        folio = $("#pedido_aux").val();
        //*****************************************************************************************
        // Este proceso se hace para que en una instancia que tenga pedidos con un formato por 
        // ejemplo AA-BBBBB-Sufijo, entonces no se confunda el sistema por el guión (-) y 
        // asegurarse de que el sufijo se tome desde el último guión y no el primero.
        //*****************************************************************************************
        var folio_arr = folio.split(""), sufijo = "", hay_sufijo = false;
        for(var i = folio_arr.length; i > 0; i--)
        {
            if(folio_arr[i] == '-')
            {
               folio_arr[i] = folio_arr[i].replace('-', '***');
               hay_sufijo = true;
               break;
            }
        }
        if(hay_sufijo)
        {
            folio = folio_arr.join("");
            pedido = folio.split("***")[1];
            folio = folio.split("***")[0];
        }
        //*****************************************************************************************
        //*****************************************************************************************
        console.log("*****************AUDITAR*****************");
        console.log("Sufijo = ", pedido);
        console.log("folio = ", folio);
        console.log("area = ", area);
        console.log("respon = ", respon);
        console.log("**********************************");

        if(folio)
        {
        console.log("*****************AUDITAR2*****************");
        console.log("Sufijo = ", pedido);
        console.log("folio = ", folio);
        console.log("area = ", area);
        console.log("respon = ", respon);
        console.log("**********************************");

            $.ajax({
                url: "/api/qaauditoria/index.php",
                type: "POST",
                data: {
                    "action" : "auditar",
                    "pedido": pedido, 
                    "folio" : folio,
                    "area" : area,
                    "respon" : respon
                },
                beforeSend: function(x){
                    if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
                    console.log("res = ", x);
                },
                always: function(x){
                    console.log("res always = ", x);
                },
                success: function(res){
                    console.log("**********************************");
                    console.log("res = ", res);
                    console.log("res.continuar = ", res.continuar);
                    console.log("res.ocupado = ", res.ocupado);
                    console.log("**********************************");
                    //if(res.continuar == 0)
                    //{
                        fillInputCli(res.cliente);
                        fillcalculatePiezas(res.sum);
                        fillTable(res.table);
                        fillTable2(res.table2);
                        fillReview(res.table);
                        validateClosed(res.table);
                        button_auditar.disabled = true;
                        button_revisar.disabled = false;
                        //console.log(res);
                    //}
                    /*
                    else
                    {
                        swal({
                            title: "Error!",
                            text: "Esta mesa ya tiene su folio asignado",
                            type: "error",
                            showCancelButton: false
                        });
                        window.location.reload();
                    }
                    */
                },
                error : function(res){
                    window.console.log(res);
                }
            });
        }
        else
            console.log("Entró ELSE");

    }

    function validateClosed(node){

        var cantT = node.length,
            count = 0;

        //console.log("Total validateClosed() = ", cantT);

        if(node.length > 0){

            for(var i = 0; i < node.length; i++){

                var cant = parseInt(node[i].Num_cantidad),
                    revi = parseInt(node[i].Num_revisadas);

                if(revi === cant){
                    count++;
                }
            }

            if(cantT === count){

                //button_cerrar.disabled = false;
                //button_revisar.disabled = true;
                actionButtonCerrar(1);
            }
        }
    }

    function fillInputCli(node){

        if(node.length > 0)
            input_client.value = node[0].RazonSocial;
        else
            input_client.value = "Desconocido";
    }

    function fillReview(node){

        if(node.length > 0){

            input_codigo.disabled = false;

            JSONA = [];
            JSONB = [];
            JSONC = [];

            var items = {}, items2 = {}, items3 = {};

            for(var i = 0; i < node.length; i++){

                var id = node[i].cve_articulo,
                    cod_barras = node[i].cve_codprov,
                    cod_barras_cajas = node[i].barras2,
                    lote = node[i].cve_lote,
                    cant = parseInt(node[i].Num_cantidad),
                    revi = parseInt(node[i].Num_revisadas),
                    //loteId = 'SIN LOTE';
                    loteId = '';

                if(revi < cant){

                    if(typeof items[id] === 'undefined'){
                        items[id] = {};
                        items[id]['descrip'] = node[i].des_articulo;
                        items2[cod_barras] = {};
                        items2[cod_barras]['descrip'] = node[i].des_articulo;
                        items3[cod_barras_cajas] = {};
                        items3[cod_barras_cajas]['descrip'] = node[i].barras2;
                    }

                    if(lote !== null)
                        loteId = node[i].cve_lote;

                    if(typeof items[id][loteId] === 'undefined'){
                        items[id][loteId] = JSON.parse(JSON.stringify(node[i]));
                        items2[cod_barras][loteId] = JSON.parse(JSON.stringify(node[i]));
                        items3[cod_barras_cajas][loteId] = JSON.parse(JSON.stringify(node[i]));
                    }
                }
            }

            JSONA = JSON.parse(JSON.stringify(items));
            JSONB = JSON.parse(JSON.stringify(items2));
            JSONC = JSON.parse(JSON.stringify(items3));
        }
    }

    function fillcalculatePiezas(node){

        if(node.length > 0){

            var cant = node[0].cant,
                revi = node[0].revi;

            input_fa.value = cant;
            input_re.value = revi;
            input_porce.value = (revi * 100) / cant;
        }
        else{
            input_fa.value = 0;
            input_re.value = 0;
            input_porce.value = "100%";
        }
    }

    var clic_revisar = false, entrando = true;

    function actionButtonEmpacar()
    {

        button_revisar.disabled = true;
        if(input_cant.value /*&& clic_revisar == false*/){
        //clic_revisar = true;
        //if(parseInt(ITEM_SELECT.Num_cantidad)){
        

            var cant = parseFloat(ITEM_SELECT.Num_cantidad), 
                revi = parseFloat(ITEM_SELECT.Num_revisadas),
                reviT = cant - revi,
                cantT = input_cant.value,
                artiDescrip = ITEM_SELECT.des_articulo,
                unidadMedida = 1,//$("#unidadMedida").val(),
                total = revi + cantT,
                //lote = 'SL';
                lote = '';

console.log("***************************");
console.log("actionButtonEmpacar()");
console.log("***************************");
console.log("cant = ", parseFloat(ITEM_SELECT.Num_cantidad)); 
console.log("revi = ", parseFloat(ITEM_SELECT.Num_revisadas));
console.log("reviT = ", cant - revi);
console.log("cantT = ", input_cant.value);
console.log("artiDescrip = ", ITEM_SELECT.des_articulo);
console.log("total = ", revi + cantT);
console.log("***************************");

var str = $("#unidadMedida option:selected").text();
var es_caja = false;
if(str.toLowerCase().indexOf("caja") >= 0)
{
    es_caja = true;
    cantT = parseInt(input_cant.value);
    cant = parseInt(ITEM_SELECT.Num_cantidad); 
    revi = parseInt(ITEM_SELECT.Num_revisadas);
    reviT = cant - revi;
    total = revi + cantT;
    unidadMedida = 2;
}

                if(ITEM_SELECT.num_multiplo != '' && ITEM_SELECT.num_multiplo > 0 && es_caja == true)//$("#unidadMedida").val() == 2
                    reviT = parseInt(reviT/ITEM_SELECT.num_multiplo);

            if(reviT >= cantT){

                var status = 'A';

                if(total === cant)
                    status = 'C';

                if(ITEM_SELECT.cve_lote)
                    lote = ITEM_SELECT.cve_lote;

                console.log("xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");
                console.log("status = ", status);
                console.log("cantT = ", cantT);
                console.log("reviT = ",  reviT);
                console.log("total = ",  total);
                console.log("unidadMedida = ", unidadMedida);
                console.log("folio = ",  ITEM_SELECT.Fol_folio);
                console.log("articulo = ",  ITEM_SELECT.cve_articulo);
                console.log("lote = ",  lote);
                console.log("xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");

                $.ajax({
                    url: "/api/qaauditoria/index.php",
                    type: "POST",
                    data: 
                    {
                        "action" : "VerificarCaso",
                        "folio" : ITEM_SELECT.Fol_folio,
                        "articulo" : ITEM_SELECT.cve_articulo,
                        "lote" : ITEM_SELECT.cve_lote
                    },
                    beforeSend: function(x){
                        if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
                    },
                    success: function(res){
                        console.log("55555555555555555555555555555");
                        console.log("CASO 1 = ",res.caso1);
                        console.log("SQL CASO 1 = ",res.sql_caso1);
                        console.log("55555555555555555555555555555");

                        if(res.caso1 == 0)
                        {
                            $.ajax({
                                url: "/api/qaauditoria/index.php",
                                type: "POST",
                                data: 
                                {
                                    "action" : "empacar",
                                    "status": status,
                                    "cantT" : cantT,
                                    "reviT" : total,
                                    "unidadMedida": unidadMedida,
                                    "folio" : ITEM_SELECT.Fol_folio,
                                    "articulo" : ITEM_SELECT.cve_articulo,
                                    "lote" : ITEM_SELECT.cve_lote
                                },
                                beforeSend: function(x){
                                    if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
                                },
                                success: function(res){

                                    if(res.val_revisar == true)
                                    {
                                        if(!res.alcanza_cajas || !res.alcanza_pallets)
                                        {
                                            //$("#unidadMedida").val(1);
                                        }
                                        else
                                        {
                                            console.log("Empacados = ",res.empacados);
                                        }

                                        loadPedido();
                                        changeLote("", 1);
                                        console.log(res);
                                        button_revisar.disabled = false;
                                        clic_revisar = false;
                                    }
                                    else
                                    {
                                        swal({
                                                title: "Producto Ya Revisado",
                                                text: "Por Favor Seleccione Otro Lote/Serie o Revise el siguiente producto",
                                                type: "error",
                                                showCancelButton: false,
                                            });
                                        button_revisar.disabled = false;
                                    }
                                },
                                error : function(res){
                                    console.log("++++++++++++++++++++ERROR+++++++++++++++++++");
                                    window.console.log(res);
                                    console.log("++++++++++++++++++++++++++++++++++++++++++++");

                                }
                            });
                        }
                        else
                        {
                                swal({
                                    title: "¿Surtir este Artículo?",
                                    text: "Este artículo no se ha surtido aún. Desea Surtirlo en este pedido para poder auditarlo?",
                                    type: "warning",

                                    showCancelButton: true,
                                    cancelButtonText: "Cancelar",
                                    cancelButtonColor: "#14960a",

                                    confirmButtonColor: "#55b9dd",
                                    confirmButtonText: "Surtir y Auditar",
                                    closeOnConfirm: true
                                },
                                function(e) {
                                    if (e == true) 
                                    {
                                        console.log("SURTIR ARTICULO");   

                                        $.ajax({
                                            url: "/api/qaauditoria/index.php",
                                            type: "POST",
                                            data: 
                                            {
                                                "action" : "VerificarYSurtir",
                                                "folio" : ITEM_SELECT.Fol_folio,
                                                "articulo" : ITEM_SELECT.cve_articulo,
                                                "lote" : ITEM_SELECT.cve_lote
                                            },
                                            beforeSend: function(x){
                                                if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
                                            },
                                            success: function(res){
                                                console.log("VerificarYSurtir = ", res);
                                                if(res.surtir == true)
                                                {
                                                    $.ajax({
                                                        url: "/api/qaauditoria/index.php",
                                                        type: "POST",
                                                        data: 
                                                        {
                                                            "action" : "empacar",
                                                            "status": status,
                                                            "cantT" : cantT,
                                                            "reviT" : total,
                                                            "unidadMedida": unidadMedida,
                                                            "folio" : ITEM_SELECT.Fol_folio,
                                                            "articulo" : ITEM_SELECT.cve_articulo,
                                                            "lote" : ITEM_SELECT.cve_lote
                                                        },
                                                        beforeSend: function(x){
                                                            if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
                                                        },
                                                        success: function(res){

                                                            if(res.val_revisar == true)
                                                            {
                                                                if(!res.alcanza_cajas || !res.alcanza_pallets)
                                                                {
                                                                    //$("#unidadMedida").val(1);
                                                                }
                                                                else
                                                                {
                                                                    console.log("Empacados = ",res.empacados);
                                                                }

                                                                loadPedido();
                                                                changeLote("", 1);
                                                                console.log(res);
                                                                button_revisar.disabled = false;
                                                                clic_revisar = false;
                                                            }
                                                            else
                                                            {
                                                                swal({
                                                                        title: "Producto Ya Revisado",
                                                                        text: "Por Favor Seleccione Otro Lote/Serie o Revise el siguiente producto",
                                                                        type: "error",
                                                                        showCancelButton: false,
                                                                    });
                                                                button_revisar.disabled = false;
                                                            }
                                                        },
                                                        error : function(res){
                                                            console.log("++++++++++++++++++++ERROR+++++++++++++++++++");
                                                            window.console.log(res);
                                                            console.log("++++++++++++++++++++++++++++++++++++++++++++");

                                                        }
                                                    });
                                                }
                                                else
                                                {
                                                    swal({
                                                            title: "No Hay Suficiente Stock para surtir",
                                                            text: "No se puede surtir el artículo por falta de stock o por registro de ubicación en ruta de surtido",
                                                            type: "error",
                                                            showCancelButton: false,
                                                        });
                                                    button_revisar.disabled = false;
                                                }

                                            },
                                            error : function(res){
                                                console.log("++++++++++++++++++++ERROR+++++++++++++++++++");
                                                window.console.log(res);
                                                console.log("++++++++++++++++++++++++++++++++++++++++++++");
                                                button_revisar.disabled = false;

                                            }
                                        });

                                    } 
                                    else 
                                    {
                                        console.log("NO SURTIR");
                                        loadPedido();
                                        changeLote("", 1);
                                        console.log(res);
                                        button_revisar.disabled = false;
                                        clic_revisar = false;
                                    }

                                });

                        }

                    },
                    error : function(res){
                        console.log("++++++++++++++++++++ERROR+++++++++++++++++++");
                        window.console.log(res);
                        console.log("++++++++++++++++++++++++++++++++++++++++++++");

                    }
                });


            }
            else{
                swal({
                    title: "¡Alerta!",
                    text: "Esta introduciendo una cantidad mayor a la posible a revisar ("+reviT+")",
                    type: "warning",
                    showCancelButton: false,
                });
                button_revisar.disabled = false;
            }
        }
        else //if(entrando == false) 
        {
            swal({
                title: "¡Alerta!",
                text: "Producto ya Empacado",
                type: "warning",
                showCancelButton: false,
            });
            input_cant.focus();
            input_cant.select();
            button_revisar.disabled = false;
        }

        //if(entrando == true) entrando = false;
        //$("#input-codigo").focus();

        function checkComplete(){
            
        }


    }

    function showModalSupervisores() 
    {
        /*
        $.ajax({
            url: '/api/v2/usuarios/administradores',
            dataType: 'json',
            method: 'GET'
        }).done(function(data) {
            var html = '';
            //$.each(data.data, function(index, item){
            //    html += '<option value="'+item.cve_usuario+'">('+item.cve_usuario+') - '+item.nombre_completo+'</option>';
            //})                
        });
        */
            html = '<option value="<?php echo $_SESSION['cve_usuario']; ?>"><?php echo $_SESSION['cve_usuario']; ?></option>';
            $("#txt-supervisor").html(html).trigger("chosen:updated");
            $("#modal-asignar-supervisor").modal('show');

    }

    function asignarSupervisor()
    {
        $.ajax({
            url: "/api/qaauditoria/index.php",
            type: "POST",
            data: {
                "action" : "verificar_usuario",
                "usuario" : $("#txt-supervisor").val(),
                "passw" : $("#txt-supervisor-pass").val()
            },
            beforeSend: function(x){
                if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
                console.log("res = ", x);
            },
            always: function(x){
                console.log("res always = ", x);
            },
            success: function(res){
                console.log("**********************************");
                console.log("res = ", res.correcto);
                console.log("**********************************");
                if(res.correcto == 1)
                {
                    console.log("USUARIO CORRECTO");
                    $("#terminar_auditoria_user").val(1);
                    $("#modal-asignar-supervisor").modal('hide');
                    actionButtonCerrar(1);
                }
                else 
                {
                    console.log("USUARIO INCORRECTO");
                    swal("Error", "Contraseña Incorrecta", "error");
                }
                //swal("Éxito", "Auditoría Reiniciada con éxito", "success");
            },
            error : function(res){
                window.console.log(res);
            }
        });
        //actionButtonCerrar(0);
    }

    var zona_embarque_obligatorio = 0, valor_asignado = false;
    function actionButtonCerrar(zona_embarque_oblig){

        zona_embarque_obligatorio = zona_embarque_oblig;
        $("#myModal").modal();
        console.log("folio_auto = ", folio_auto);
        if(folio_auto)
        {
            $("#input-factura").val(folio_auto);
            console.log("folio_auto2 = ", folio_auto);
        }
        else if(valor_asignado == false)
        {
            input_factura.value = select_pedido.options.item(select_pedido.selectedIndex).text;
            //console.log("folio_auto3 = ", input_factura.value);
            valor_asignado = true;
        }

        var mostrar_zona_embarque = true;
        for(var i = 0; i < g_pos; i++)
        {
            if(val_articulos_empacados[i] == 0 || val_articulos_empacados[i] == 3)
            {
                mostrar_zona_embarque = false;
                break;
            }
        }

        if(mostrar_zona_embarque || $("#terminar_auditoria_user").val() == 1){ 
            $("#zona-embarque").show(); 
            console.log("input_zona_embarque 0", input_zona_embarque.value); 
        }
        $("#input_cajas_chosen").trigger('mousedown');
    }

    $("#reiniciar-auditoria").click(function(){
        console.log("Fol_folio = ", $("#select-pedido option:selected").text());
        console.log("Fol_folio VAL = ", $("#select-pedido").val());

        if($("#select-pedido").val())
        {
            //swal("Advertencia", "Esto Reiniciará toda la Auditoría del folio "+$("#select-pedido option:selected").text()+" para revisar nuevamente", "warning");

            swal({
                title: "Advertencia",
                text: "Esto Reiniciará toda la Auditoría del folio "+$("#select-pedido option:selected").text()+" para revisar nuevamente \n\n ¿Desea Reiniciar la Auditoría?",
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
                    console.log("SI REINICIAR AUDITORIA");
                    var folio_sufijo = $("#select-pedido option:selected").text();
                    var folio_sufijo_arr = folio_sufijo.split("-");
                    var folio_reiniciar = folio_sufijo_arr[0];
                    var sufijo_reiniciar = ($("#pedido_completo").val() != 0)?(folio_sufijo_arr[1]):("");

                    ReiniciarAuditoria(folio_reiniciar, sufijo_reiniciar);
                } else 
                {
                    console.log("NO REINICIAR AUDITORIA");
                }

            });


        }
    });

    function ReiniciarAuditoria(folio_reiniciar, sufijo_reiniciar)
    {
        console.log("folio_reiniciar = ", folio_reiniciar);
        console.log("sufijo_reiniciar = ", sufijo_reiniciar);
        //return;
        $.ajax({
            url: "/api/qaauditoria/index.php",
            type: "POST",
            data: {
                "action" : "reiniciar_auditoria",
                "folio" : folio_reiniciar,
                "sufijo" : sufijo_reiniciar
            },
            beforeSend: function(x){
                if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
                console.log("res = ", x);
            },
            always: function(x){
                console.log("res always = ", x);
            },
            success: function(res){
                console.log("**********************************");
                console.log("res = ", res);
                console.log("**********************************");
                swal("Éxito", "Auditoría Reiniciada con éxito", "success");
                setTimeout(function(){window.location.reload();}, 2000);
            },
            error : function(res){
                window.console.log(res);
            }
        });
    }

    function CerrarPallet()
    {
        var mensaje = 'Cerrar';
        if(select_pallets.value != '')
        {
            for(var i = 0; i < g_pos; i++)
            {
                if(val_articulos_empacados[i] == 0 || val_articulos_empacados[i] == 3)
                {
                    mensaje = 'Empacar';
                    break;
                }
            }

            if(mensaje == 'Empacar')
            {
                swal({
                    title: "Aviso",
                    text: "¿Desea cerrar la tarima?",
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
                        console.log("SI CERRAR TARIMA");
                        closedAuditoria(0);
                    } else 
                    {
                        console.log("NO CERRAR TARIMA");
                        closedAuditoria(1);
                    }

                });
            }
            else
                closedAuditoria(0); 
        }
        else
        {
            closedAuditoria(0);
        }

        console.log(":::::::::::::::::::::::::::::::::::");
        console.log("MENSAJE = ", mensaje);
        console.log(":::::::::::::::::::::::::::::::::::");
    }

    function closedAuditoria(pallet_abierto){

        var value = input_cajas.value,
            pallet = select_pallets.value,
            area  = select_area.value;
            console.log("caja = ",value);

        var mensaje = 'Cerrar', colorBoton = '#DD6B55';
        for(var i = 0; i < g_pos; i++)
        {
            if(val_articulos_empacados[i] == 0 || val_articulos_empacados[i] == 3)
            {
                mensaje = 'Empacar';
                colorBoton = "#0066cc";
                break;
            }
        }

        var zona_embarque = 0;
        if(mensaje == 'Cerrar')
        {
            zona_embarque = input_zona_embarque.value;
            console.log("input_zona_embarque 1", zona_embarque);
        }
        //if(value){
/*
            swal({
                title: "¿Cerrar Auditoria | Empaque?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: colorBoton,
                confirmButtonText: mensaje,
                cancelButtonText: "Cancelar"
            },
            function(){
*/
                var folio_send = input_factura.value, sufijo = "";
                if(folio_auto)
                   folio_send = folio_auto;


               //folio_send = folio_send.split("-");
               folio_send = $("#pedido_aux").val();
                //*****************************************************************************************
                // Este proceso se hace para que en una instancia que tenga pedidos con un formato por 
                // ejemplo AA-BBBBB-Sufijo, entonces no se confunda el sistema por el guión (-) y 
                // asegurarse de que el sufijo se tome desde el último guión y no el primero.
                //*****************************************************************************************
                var folio_arr = folio_send.split(""), hay_sufijo = false;
                for(var i = folio_arr.length; i > 0; i--)
                {
                    if(folio_arr[i] == '-')
                    {
                        folio_arr[i] = folio_arr[i].replace('-', '***');
                        hay_sufijo = true;
                        break;
                    }
                }
                if(hay_sufijo)
                {
                    folio_send = folio_arr.join("");
                    sufijo = folio_send.split("***")[1];
                    folio_send = folio_send.split("***")[0];
                }
                else sufijo = 0;
                //*****************************************************************************************
                //*****************************************************************************************

               //folio_send = folio_send[0];
                console.log("********************************************");
                //console.log("sufijo = ", select_pedido.value);
                console.log("sufijo = ", sufijo);
                console.log("folio_send = ", folio_send);
                console.log("cajas = ", value);
                console.log("pallet = ", pallet);
                console.log("zona_embarque = ", zona_embarque);
                console.log("almacen = ", select_almacen.value);
                console.log("cod_articulos_empacados = ", cod_articulos_empacados);
                console.log("cod_lotes_series_articulos = ", cod_lotes_series_articulos);
                console.log("val_articulos_empacados = ", val_articulos_empacados);
                console.log("zona_embarque_obligatorio = ", zona_embarque_obligatorio);
                console.log("total_pendientes = ", $("#total_pendientes").text());
                console.log("********************************************");

                $("#loadgif").show();

                //return;

            if(zona_embarque_obligatorio == 1 && input_zona_embarque.value == "000X")
            {
                swal({
                    title: "¡Alerta!",
                    text: "Debe Seleccionar una Zona de Embarque para poder cerrar",
                    type: "error",
                    showCancelButton: false
                });
            }
            else
            {
                $.ajax({
                    url: "/api/qaauditoria/index.php",
                    type: "POST",
                    data: {
                        "action" : "cerrar",
                        "cajas": value,
                        "pallet_abierto" : pallet_abierto,
                        "pallet": pallet,
                        "sufijo": sufijo,
                        "folio" : folio_send,
                        "ncajas": $("#ncajas").val(),
                        "total_pendientes":$("#total_pendientes").text(),
                        "terminar_auditoria_user": $("#terminar_auditoria_user").val(),
                        "cod_articulos_empacados" : cod_articulos_empacados,
                        "zona_embarque" : zona_embarque,
                        "almacen" : select_almacen.value,
                        "val_articulos_empacados" : val_articulos_empacados
                    },
                    beforeSend: function(x){
                        if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
                    },
                    always: function(res){
                        console.log("ALWAYS:", res);
                    },
                    success: function(res){
                        $("#loadgif").hide();
                        console.log("SUCCESS EMPACADO:", res);
                        if(mensaje == 'Cerrar' || $("#terminar_auditoria_user").val() == 1)
                        {
                            /*
                            swal({
                                title: "¡Alerta!",
                                text: "Auditoria Cerrada con éxito.",
                                type: "warning",
                                showCancelButton: false
                            });
                            */
                        }
                        else
                        {
                            swal({
                                title: "¡Éxito!",
                                text: "Producto(s) Empacado(s) con éxito",
                                type: "success",
                                showCancelButton: false
                            });
                            if(res.abrir_logistica == false) ImprimirEtiquetaEmpaque(folio_send);
                        }
                        console.log(res);

  /*
                        var restart = true;
                        for(var i = 0; i < g_pos; i++)
                        {
                            if(val_articulos_empacados[i] == 1)
                            {
                                val_articulos_empacados[i] = 2;
                                button_empacar.disabled = true;
                            }
                            
                            if(val_articulos_empacados[i] == 0)
                                restart = false;
                        }
*/
                        if(res.cerrar == true || res.abrir_logistica == true || $("#terminar_auditoria_user").val() == 1)
                        {
                            if(res.abrir_logistica == true)
                            {
                                Swal.fire(
                                    'Espere un momento',
                                    'Estamos analizando la información',
                                    'info'
                                );

                                Swal.showLoading();
                                $.ajax({
                                    url: "/api/qaauditoria/index.php",
                                    type: "POST",
                                    data: {
                                        "action" : "ImprimirEtiquetas",
                                        "folio" : folio_send,
                                        "clave_almacen_sp" : res.clave_almacen_sp
                                    },
                                    beforeSend: function(x){
                                        if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
                                    },
                                    always: function(res){
                                        console.log("ALWAYS:", res);
                                    },
                                    success: function(res){

                                        console.log("SUCCESS ETIQ:", res);

                                        setTimeout(function(){
                                            if(res.archivo != '')
                                            {
                                                Swal.fire({
                                                  icon: "success",
                                                  title: "Etiqueta Generada",
                                                  text: "",
                                                  footer: 'No se generó la etiqueta? <a href="api/qaauditoria/'+res.archivo+'" target="_blank"> Click Aquí</a>'
                                                });
                                                //window.open('api/qaauditoria/'+res.archivo);
                                            }
                                            else
                                            {
                                                setTimeout(function(){
                                                Swal.fire({
                                                  icon: "success",
                                                  title: "Etiqueta Generada",
                                                  text: "",
                                                  footer: 'No se generó la etiqueta? <a href="api/qaauditoria/Etiqueta_'+folio_send+'.pdf" target="_blank"> Click Aquí</a>'
                                                });
                                                //window.open('api/qaauditoria/Etiqueta_'+folio_send+'.pdf');
                                                }, 2000);
                                            }
                                        }, 2000);
                                        Swal.close();
                                        if(res.cerrar == true && res.mensaje_error == "")
                                        {
                                            setTimeout(function(){window.location.reload();}, 2000);
                                        }
                                        else if(res.mensaje_error != "")
                                            swal("Error", res.mensaje_error, "error");
                                    },
                                    error : function(res){
                                        console.log("ERROR ETIQ", res);
                                    }
                                });

                                
                            }
                            else 
                            {
                                ImprimirEtiquetaEmpaque(folio_send);
                                setTimeout(function(){window.location.reload();}, 2000);
                            }
                            

                            //console.log(res);
                        }
                        else
                        {
                            loadPedido();
                            almacenPrede(true);
                        }

                        $("#myModal").modal('hide');
                        /*
                        else
                        {
                            $("#myModal").modal('hide');
                            if(res.zona_embarque == "000X" && res.cerrar == false)
                            {
                                swal({
                                    title: "¡Alerta!",
                                    text: "Debe Seleccionar una Zona de Embarque para poder cerrar",
                                    type: "error",
                                    showCancelButton: false
                                });
                            }
                        }
*/

                        console.log("cod_articulos_empacados", cod_articulos_empacados);
                        console.log("val_articulos_empacados", val_articulos_empacados);
                    },
                    error : function(res){
                        console.log("ERROR", res);
                    }
                });
            }


//            });

        //}
        /*else{
            swal({
                title: "¡Alerta!",
                text: "Por favor llene el campo 'Cantidad de cajas'",
                type: "warning",
                showCancelButton: false,
            });
            input_cajas.focus();
        }*/
    }

    function ImprimirEtiquetaEmpaque(folio)
    {
        window.open("/api/koolreport/export/reportes/auditoria/etiquetas?folio="+folio+"&ncajas="+$("#ncajas").val(), "_blank");

    }

    /**
     * @author Ricardo Delgado.
     * Busca y llena la table con los articulos del pedido.
     */
    function fillTable(node){

        tableData.destroy();

        var body = document.getElementById('tbody'),
            color = RED,
            cant = 0,
            revi = 0,
            id = null,
            //lote = 'SL',
            lote = '',
            serie = '',
            lote_serie_revisar = '',
            num_empacados = 0,
            caducidad = '';


        body.innerHTML = "";
        var str = $("#unidadMedida option:selected").text();
        var total_suma = 0;
        var total_pendientes = 0;
        var total_revisadas = 0;

        for(var i = 0; i < node.length ; i++)
        {
            //console.log("i = ",i, " - node = ", node[i]);
            color = RED; cant = node[i].Num_cantidad; revi = node[i].Num_revisadas;
            //lote = 'SIN LOTE';
            if(node[i].caso == 'caso1') color = PURPLE;
            var es_caja = false;
            if(str.toLowerCase().indexOf("caja") >= 0)
            {
                es_caja = true;
                cant = parseInt(node[i].Num_cantidad); revi = parseInt(node[i].Num_revisadas)
                unidadMedida = 2;
            }
            //console.log("cant === ", cant);
            //console.log("revi === ", revi);
            cant = parseFloat(cant).toFixed(2);
            revi = parseFloat(revi).toFixed(2);

            lote = ''; 
            serie = ''; caducidad = '';

            if(!array_init)
            {
                cod_articulos_empacados[g_pos] = node[i].cve_articulo; 
                cod_lotes_series_articulos[g_pos] = node[i].cve_lote;

                //if(val_articulos_empacados[g_pos] != 2)
                   val_articulos_empacados[g_pos] = 0;
                //g_pos++;
            }
            lote_serie_revisar = '';
            if($("#select-lote-serie").val() != '') lote_serie_revisar = $("#select-lote-serie").val();

/*
            revi = 0; num_empacados = 0;
            //if(node[i].cve_lote == lote_serie_revisar)
            if($.inArray(node[i].cve_lote, cod_lotes_series_articulos) !== -1)
            {
               revi = parseInt(node[i].Num_revisadas);
               num_empacados = node[i].Num_Empacados;
            }
*/
            //console.log("cod_lotes_series_articulos = ", cod_lotes_series_articulos);
            //console.log("cant = ", cant, " - revi = ", revi);
            
            if(cant === revi)//&& node[i].Num_cantidad > 0
            {
                color = GREEN;
                console.log("GREEN");
                var reg = true;
                var pos;

                for(var g = 0; g < (g_pos+1); g++)
                {
                    //if(node[i].cve_lote == cod_lotes_series_articulos[g])
  /*
                    if($.inArray(node[i].cve_lote, cod_lotes_series_articulos) !== -1)
                    {
                        color = GREEN;
                        console.log("GREEN");
                    }
*/

                    console.log("node[",i,"].cve_articulo == cod_articulos_empacados[",g,"] (",node[i].cve_articulo,"==",cod_articulos_empacados[g],")");
                    console.log("node[",i,"].cve_lote == cod_lotes_series_articulos[",g,"] (",node[i].cve_lote,"==",cod_lotes_series_articulos[g],")");
                    if(node[i].cve_articulo == cod_articulos_empacados[g] && node[i].cve_lote == cod_lotes_series_articulos[g])
                    {
                        cod_articulos_empacados[g] = node[i].cve_articulo;
                        cod_lotes_series_articulos[g] = node[i].cve_lote;
                        //if(val_articulos_empacados[g] != 2)
                           val_articulos_empacados[g] = 1;
                        //g_pos++;
                       break;
                    }
                }
                button_empacar.disabled = false;

            }
            else if(revi > 0 )
            {
                color = YELLOW; 
                var is_in_array = false, pos_array = 0;
                for(var j = 0; j < g_pos; j++)
                {
  /*
                    //if(node[i].cve_lote == cod_lotes_series_articulos[g])
                    if($.inArray(node[i].cve_lote, cod_lotes_series_articulos) !== -1)
                    {
                        color = YELLOW;
                        console.log("YELLOW");
                    }
*/
                    if(node[i].cve_articulo == cod_articulos_empacados[j] && node[i].cve_lote == cod_lotes_series_articulos[j])
                    {
                        is_in_array = true;
                        pos_array = j;
                        break;
                    }
                }
                if(is_in_array)
                {
                    val_articulos_empacados[pos_array] = 3;//el articulo se empezó a empacar
                    cod_articulos_empacados[pos_array] = node[i].cve_articulo;
                    cod_lotes_series_articulos[pos_array] = node[i].cve_lote;
                }
                else
                {
                    val_articulos_empacados[g_pos] = 3;//el articulo se empezó a empacar
                    cod_articulos_empacados[g_pos] = node[i].cve_articulo;
                    cod_lotes_series_articulos[g_pos] = node[i].cve_lote;
                }
                button_empacar.disabled = false;
            }

            if(revi == 0)
            {
                //button_empacar.disabled = true;
            }

            console.log("Color Final = ", color);
            console.log("**********************************");

            if(!array_init)
               g_pos++;

            if(node[i].cve_lote !== null)
            {
                if(node[i].control_lotes == 'S')
                {
                    lote = node[i].cve_lote;
                    if(node[i].Caducidad != '' && node[i].Caducidad != '0000-00-00')
                       caducidad = node[i].Caducidad;
                }

                if(node[i].control_numero_series == 'S')
                    serie = node[i].cve_lote;
            }
/*
                                    <th>Clave</th>
                                    <th>Descripción</th>
                                    <th>Lote</th>
                                    <th>Serie</th>
                                    <th>Caducidad</th>
                                    <th>Auditando</th>
                                    <th>Revisadas</th>
                                    <th>Empacadas</th>
                                    <th>Pendientes</th>
                        '<td>'+htmlEntities(node[i].status)+'</td>'+
                        '<td width = "50">'+
                            '<a href="##" class="pointer-events: none;cursor: default;">'+
                              '<i id = "select-'+i+'" class = "glyphicon glyphicon-tag btnselect" ></i>'+
                            '</a>'+
                        '</td>'+

*/
            articulos_tabla [i] = node[i].cve_articulo;

            var surtidas = node[i].Num_cantidad;
            var revisadas = revi;//node[i].Num_revisadas;
            var empacados = node[i].Num_Empacados;
            if(node[i].control_peso == 'N') 
            {
                surtidas = parseInt(surtidas);
                revisadas = parseInt(revisadas);
                empacados = parseInt(empacados);
            }
            body.innerHTML += '<tr style="background-color:'+color+'; color:black">'+
                        '<td>'+htmlEntities(node[i].cve_articulo)+'</td>'+
                        '<td>'+htmlEntities(node[i].des_articulo)+'</td>'+
                        '<td>'+htmlEntities(lote)+'</td>'+
                        '<td>'+htmlEntities(serie)+'</td>'+
                        '<td>'+htmlEntities(caducidad)+'</td>'+
                        '<td align="center">'+htmlEntities(surtidas)+'</td>'+
                        '<td align="center">'+htmlEntities(surtidas-revisadas)+'</td>'+
                        '<td align="center">'+htmlEntities(revisadas)+'</td>'+
                        '<td align="center">'+htmlEntities(empacados)+'</td>'+
                        //'<td>'+htmlEntities(node[i].Num_cantidad-node[i].Num_revisadas)+'</td>'+
                     '</tr>';

                total_suma += parseFloat(surtidas);
                total_pendientes += parseFloat(surtidas-revisadas);
                total_revisadas += parseFloat(revisadas);
            /*

            if(cant === revi) 
                document.getElementById('select-'+i).dataset.id = null;
            else{
                document.getElementById('select-'+i).dataset.id = node[i].cve_articulo;
                document.getElementById('select-'+i).dataset.lote = node[i].cve_lote;
            }

            $(".btnselect").click(function(){
                if(this.dataset.id !== "null"){
                    input_codigo.value = this.dataset.id;
                    fillFieldAudi(this.dataset.lote);
                }
                else{
                    input_codigo.value = "";
                    fillFieldAudi();
                }
            });
            */
        }

        $("#total_suma").text(total_suma);
        $("#total_pendientes").text(total_pendientes);
        $("#total_revisadas").text(total_revisadas);


        array_init = true;
        console.log("cod_articulos_empacados", cod_articulos_empacados);
        console.log("val_articulos_empacados", val_articulos_empacados);
        console.log("articulos_tabla", articulos_tabla);

        //TABLE = tableData.init("table-info",false, false);

        $("#input-codigo").focus();
        if(total_pendientes <= 0)
        {
            $("#audioFinal")[0].play();

            swal({
                title: "Se ha Finalizado la Auditoría",
                type: "success",
                showCancelButton: false,
                //confirmButtonColor: "#DD6B55",
                confirmButtonText: "Continuar",
                cancelButtonText: "Cerrar y Continuar"
                
            },
            function(){
                //actionButtonEmpacar();
            });

        }

    }

    function fillTable2(node){

        tableData2.destroy();

        var body = document.getElementById('tbody2'),
            color = RED,
            cant = 0,
            revi = 0,
            id = null,
            //lote = 'SL',
            lote = '',
            serie = '',
            caducidad = '';

        body.innerHTML = "";

        var auditando_rest = 0;
        var n_caja = '', num_caja = 0;
        for(var i = 0; i < node.length ; i++){

            if(node[i].cve_lote !== null)
            {
                if(node[i].control_lotes == 'S')
                {
                    lote = node[i].cve_lote;
                    if(node[i].Caducidad != '' && node[i].Caducidad != '0000-00-00')
                       caducidad = node[i].Caducidad;
                }

                if(node[i].control_numero_series == 'S')
                    serie = node[i].cve_lote;
            }

/*
                                    <th>Clave</th>
                                    <th>Descripción</th>
                                    <th>Lote</th>
                                    <th>Serie</th>
                                    <th>Caducidad</th>
                                    <th>Auditando</th>
                                    <th>Empacadas</th>
                                    <th>Tipo Caja</th>
                                    <th>No. Caja</th>
                                    <th>Pallet</th>
*/
            //if(auditando_rest == 0) auditando_rest = node[i].Num_cantidad;
            //if(node[i].tipo_caja != '') {num_caja++; n_caja = node[i].NCaja+'';} else n_caja = '';
            body.innerHTML += '<tr>'+
                        '<td>'+htmlEntities(node[i].cve_articulo)+'</td>'+
                        '<td>'+htmlEntities(node[i].des_articulo)+'</td>'+
                        '<td>'+htmlEntities(lote)+'</td>'+
                        '<td>'+htmlEntities(serie)+'</td>'+
                        '<td>'+htmlEntities(caducidad)+'</td>'+
                        //'<td>'+htmlEntities(auditando_rest)+'</td>'+
                        '<td>'+htmlEntities(node[i].Num_Empacados)+'</td>'+
                        '<td>'+htmlEntities(node[i].tipo_caja)+'</td>'+
                        '<td>'+htmlEntities(node[i].NCaja)+'</td>'+
                        '<td>'+htmlEntities(node[i].pallet)+'</td>'+
                        '<td>'+htmlEntities(node[i].CveLP)+'</td>'+
                     '</tr>';
                    //auditando_rest -= node[i].Num_Empacados;
        }

        TABLE2 = tableData2.init("table-info2",false, false);
    }

    function validateNumber(e){

        var key = window.event ? e.which : e.keyCode;

    var str = $("#unidadMedida option:selected").text();

        if(str.toLowerCase().indexOf("caja") >= 0)
        {
            if (key < 48 || key > 57) {
                e.preventDefault();
            }
        }
    }

    function changeClass(state){

        var Class = "label label-danger";

        if(state){
            $("#label-validate").addClass(Class);
            $("#label-validate").text("Código no valido");
            $("#audioError")[0].play();
        }
        else{
            $("#label-validate").removeClass(Class);
            $("#label-validate").text("");
            $("#audioCorrecto")[0].play();
            input_cant.focus();
        }
    }

    function almacenPrede(fill_solo_pallets){ 
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
                console.log("SUCCESS almacenPrede = ", data);
                if (data.success == true) {
                    select_almacen.value = data.codigo.clave;
                    $(select_almacen).trigger("chosen:updated");
                    searchSelects(data.codigo.id, fill_solo_pallets, true);
                }
            },
            error: function(res){
                console.log("ERROR almacenPrede = ", res);
            }
        });
    }


    function printPDF() {

        var id = $("#input-factura").val();

        var title = "Lista de Empaque | Packing List";

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

                /*Encabezado*/

                var table_header = document.createElement('table');

                table_header.style.width = "100%";

                table_header.style.borderSpacing = "0";

                table_header.style.borderCollapse = "collapse";

                var thead_header = document.createElement('thead');

                var tbody_header = document.createElement('tbody');


/*
                    '<th style="border: 1px solid #ccc; font-size:12px;">Fecha Entrega</th>' +

                    '<th style="border: 1px solid #ccc; font-size:12px;">Comentarios</th>' +

                    '<th style="border: 1px solid #ccc; font-size:12px;">Status</th>' +
*/
                var head_content_header = 
                '<tr style="border: 0;"><th colspan="6" text-align:center; font-weight: bold; font-size: 12px;>'+data.header.cliente+'</th></tr>' + 
                '<tr>' +

                    '<th style="border: 1px solid #ccc; font-size:12px;">Pedido | PO</th>' +


                    '<th style="border: 1px solid #ccc; font-size:12px;">Destino</th>' +

                    '<th style="border: 1px solid #ccc; font-size:12px;">Peso</th>' +

                    '<th style="border: 1px solid #ccc; font-size:12px;">Volumen</th>' +

                    '<th style="border: 1px solid #ccc; font-size:12px;">Total Piezas</th>' +

                    '<th style="border: 1px solid #ccc; font-size:12px;">Caja</th>' +

                    '</tr>';


/*
                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + data.header.fecha_entrega + '</td> ' +

                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + data.header.comentarios + '</td> ' +

                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + data.header.status + '</td> ' +
*/
                var body_content_header = '<tr>' +

                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + data.header.id + '</td> ' +

                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + data.header.destino + '</td> ' +

                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + data.header.peso + '</td> ' +

                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + data.header.volumen + '</td> ' +

                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + data.header.total_piezas + '</td> ' +

                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + data.header.total_cajas + '</td> ' +

                    '</tr>';

                /*Detalle*/

                var table = document.createElement('table');

                table.style.width = "100%";

                table.style.borderSpacing = "0";

                table.style.borderCollapse = "collapse";

                var thead = document.createElement('thead');

                var tbody = document.createElement('tbody');

/*
                    '<th style="border:1px solid #ccc; font-size:12px; width:15%">Costo Promedio</th>   ' +

                    '<th style="border:1px solid #ccc; font-size:12px;">Subtotal</th>   ' +
*/

                var head_content = '<th style="border: 1px solid #ccc; font-size:12px;">Clave</th>' +

                    '<th style="border:1px solid #ccc; font-size:12px;">Descripción</th>' +

                    '<th style="border:1px solid #ccc; font-size:12px;">Cantidad</th>' +

                    '<th style="border:1px solid #ccc; font-size:12px;">Lote</th>    ' +

                    '<th style="border:1px solid #ccc; font-size:12px;">Caducidad</th>' +

                    '<th style="border:1px solid #ccc; font-size:12px;">Serie</th>   ' +


                    '</tr>';

                var body_content = '';


/*
                        '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + item.costoPromedio + '</td> ' +

                        '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + item.subtotal + '</td> ' +
*/
                data.body.forEach(function (item, index) {

                    body_content += '<tr>' +

                        '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: left">' + item.clave + '</td> ' +

                        '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: left">' + item.descripcion + '</td> ' +

                        '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + item.cantidad + '</td> ' +

                        '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + item.lote + '</td> ' +

                        '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + item.caducidad + '</td> ' +

                        '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">' + item.serie + '</td> ' +


                        '</tr>  ';

                });



                var table_total = document.createElement('table');

                table_total.style.width = "100%";

                table_total.style.borderSpacing = "0";

                table_total.style.borderCollapse = "collapse";

                var tbody_total = document.createElement('tbody');
/*
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
*/


                console.log("PDF3");

                tbody_header.innerHTML = body_content_header;

                thead_header.innerHTML = head_content_header;

                table_header.appendChild(thead_header);

                table_header.appendChild(tbody_header);

                tbody.innerHTML = body_content;

                thead.innerHTML = head_content;

                //tbody_total.innerHTML = total;

                table.appendChild(thead);

                table.appendChild(tbody);

                //table.appendChild(tbody_total);

                content_wrapper.appendChild(table_header);

                content_wrapper.appendChild(document.createElement('br'));

                content_wrapper.appendChild(table);

                content = content_wrapper.innerHTML;

                console.log("PDF4");

                /*Creando formulario para ser enviado*/



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
/*
        $("#table-info").dataTable({
          "pageLength": "All"//,
          //"pagingType": "simple"
        });
*/
</script>


<style>
<?php 
/*
?>
.dataTables_paginate, .dataTables_info, .dataTables_filter, .dataTables_length {
  display: none;
}
<?php 
*/
 ?>

</style>