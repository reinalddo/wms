<?php
$listaAlm = new \AlmacenP\AlmacenP();
$pedidos = new \Pedidos\Pedidos();
$almacenes = $listaAlm->getAll();
$listaRutas = new \Ruta\Ruta();

$mod=60;
$var1=187;
$var2=188;
$var3=189;
$var4=190;

$vere = \db()->prepare("select * from t_profiles as a where id_menu='".$mod."' and id_submenu='".$var1."' and id_role='".$_SESSION["perfil_usuario"]."'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);

$agre = \db()->prepare("select * from t_profiles as a where id_menu='".$mod."' and id_submenu='".$var2."' and id_role='".$_SESSION["perfil_usuario"]."'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);

$edita = \db()->prepare("select * from t_profiles as a where id_menu='".$mod."' and id_submenu='".$var3."' and id_role='".$_SESSION["perfil_usuario"]."'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);

$borra = \db()->prepare("select * from t_profiles as a where id_menu='".$mod."' and id_submenu='".$var4."' and id_role='".$_SESSION["perfil_usuario"]."'");
$borra->execute();
$borrar = $borra->fetchAll(PDO::FETCH_ASSOC);


    $id_almacen = "";
    $usuario = $_SESSION["cve_usuario"];
    if($_SESSION['es_cliente'] == 1) 
    {
      $cliente_almacen_style = "style='display: none;'";
      $cve_cliente = $_SESSION['cve_cliente'];
      $alm_cliente = \db()->prepare("SELECT a.id as id_almacen 
                                     FROM c_usuario u 
                                     LEFT JOIN c_almacenp a ON a.clave = u.cve_almacen
                                     WHERE u.cve_usuario = '$usuario' AND u.cve_cliente = '$cve_cliente'");
      $alm_cliente->execute();
      $almacen_cliente = $alm_cliente->fetch()['id_almacen'];
      $id_almacen = $almacen_cliente;
    }
    else 
    {
      $id_almacen = $_SESSION['id_almacen'];

      $reportes_embarques = \db()->prepare("SELECT cve_conf, Valor FROM t_configuraciongeneral WHERE cve_conf = 'lista_empaque' AND id_almacen = $id_almacen");
      $reportes_embarques->execute();
      $lista_empaque = $reportes_embarques->fetch()["Valor"];

      $reportes_embarques = \db()->prepare("SELECT cve_conf, Valor FROM t_configuraciongeneral WHERE cve_conf = 'entrega_programada' AND id_almacen = $id_almacen");
      $reportes_embarques->execute();
      $entrega_programada = $reportes_embarques->fetch()["Valor"];

      $reportes_embarques = \db()->prepare("SELECT cve_conf, Valor FROM t_configuraciongeneral WHERE cve_conf = 'salida_inventario' AND id_almacen = $id_almacen");
      $reportes_embarques->execute();
      $salida_inventario = $reportes_embarques->fetch()["Valor"];

      $reportes_embarques = \db()->prepare("SELECT cve_conf, Valor FROM t_configuraciongeneral WHERE cve_conf = 'lista_embarque' AND id_almacen = $id_almacen");
      $reportes_embarques->execute();
      $lista_embarque = $reportes_embarques->fetch()["Valor"];

      $reportes_embarques = \db()->prepare("SELECT cve_conf, Valor FROM t_configuraciongeneral WHERE cve_conf = 'auditoria_embarque' AND id_almacen = $id_almacen");
      $reportes_embarques->execute();
      $auditoria_embarque = $reportes_embarques->fetch()["Valor"];

      $reportes_embarques = \db()->prepare("SELECT cve_conf, Valor FROM t_configuraciongeneral WHERE cve_conf = 'discrepancia' AND id_almacen = $id_almacen");
      $reportes_embarques->execute();
      $discrepancia = $reportes_embarques->fetch()["Valor"];

      $reportes_embarques = \db()->prepare("SELECT cve_conf, Valor FROM t_configuraciongeneral WHERE cve_conf = 'imprimir_asn' AND id_almacen = $id_almacen");
      $reportes_embarques->execute();
      $imprimir_asn = $reportes_embarques->fetch()["Valor"];

      $reportes_embarques = \db()->prepare("SELECT cve_conf, Valor FROM t_configuraciongeneral WHERE cve_conf = 'imp_archivo_despacho' AND id_almacen = $id_almacen");
      $reportes_embarques->execute();
      $imp_archivo_despacho = $reportes_embarques->fetch()["Valor"];

      $reportes_embarques = \db()->prepare("SELECT cve_conf, Valor FROM t_configuraciongeneral WHERE cve_conf = 'des_aviso_despacho' AND id_almacen = $id_almacen");
      $reportes_embarques->execute();
      $des_aviso_despacho = $reportes_embarques->fetch()["Valor"];

      $reportes_embarques = \db()->prepare("SELECT cve_conf, Valor FROM t_configuraciongeneral WHERE cve_conf = 'des_archivo_despacho' AND id_almacen = $id_almacen");
      $reportes_embarques->execute();
      $des_archivo_despacho = $reportes_embarques->fetch()["Valor"];
    }
//$confSql = \db()->prepare("SELECT IFNULL(Valor, '') AS api_key_google_maps FROM t_configuraciongeneral WHERE cve_conf = 'api_key_google_maps' LIMIT 1");
//$confSql->execute();
//$api_key_google_maps = $confSql->fetch()['api_key_google_maps'];

$api_key_google_maps = 'AIzaSyC5xF7JtKzw9cTRRXcDAqTThbYnMCiYOVM';

?>
<input type="hidden" id="cve_usuario" value="<?php echo $usuario; ?>">
<input type="hidden" id="id_almacen" value="<?php echo $id_almacen; ?>">
<input type="hidden" id="codigo" value="">

<input type="hidden" id="lista_empaque" value="<?php if($lista_empaque == "") $lista_empaque = "1"; echo $lista_empaque; ?>">
<input type="hidden" id="entrega_programada" value="<?php if($entrega_programada == "") $entrega_programada = "1"; echo $entrega_programada; ?>">
<input type="hidden" id="salida_inventario" value="<?php if($salida_inventario == "") $salida_inventario = "1"; echo $salida_inventario; ?>">
<input type="hidden" id="lista_embarque" value="<?php if($lista_embarque == "") $lista_embarque = "1"; echo $lista_embarque; ?>">
<input type="hidden" id="auditoria_embarque" value="<?php if($auditoria_embarque == "") $auditoria_embarque = "1"; echo $auditoria_embarque; ?>">
<input type="hidden" id="discrepancia" value="<?php if($discrepancia == "") $discrepancia = "1"; echo $discrepancia; ?>">
<input type="hidden" id="imprimir_asn" value="<?php if($imprimir_asn == "") $imprimir_asn = "1"; echo $imprimir_asn; ?>">
<input type="hidden" id="imp_archivo_despacho" value="<?php if($imp_archivo_despacho == "") $imp_archivo_despacho = "1"; echo $imp_archivo_despacho; ?>">
<input type="hidden" id="des_aviso_despacho" value="<?php if($des_aviso_despacho == "") $des_aviso_despacho = "1"; echo $des_aviso_despacho; ?>">
<input type="hidden" id="des_archivo_despacho" value="<?php if($des_archivo_despacho == "") $des_archivo_despacho = "1"; echo $des_archivo_despacho; ?>">


<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet">
<link href="/css/plugins/selectize/selectize.css" rel="stylesheet"/>
<link href="/css/plugins/selectize/selectize.bootstrap3.css" rel="stylesheet"/>
<link href="/css/plugins/iCheck/custom.css" rel="stylesheet"/>
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">


<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">

<!-- Peity -->
<script src="/js/plugins/peity/jquery.peity.min.js"></script>

<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.js"></script>

<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="/js/plugins/ladda/spin.min.js"></script>
<script src="/js/plugins/ladda/ladda.min.js"></script>
<script src="/js/plugins/ladda/ladda.jquery.min.js"></script>

<!-- Select -->
<script src="/js/select2.js"></script>
<script src="/js/plugins/selectize/standalone/selectize.min.js"></script>
<script src="/js/plugins/dataTables/jquery.dataTables.min.js"></script>
<script src="/js/plugins/dataTables/dataTables.buttons.min.js"></script>
<script src="/js/plugins/dataTables/buttons.flash.min.js"></script>
<script src="/js/plugins/dataTables/vfs_fonts.js"></script>
<script src="/js/plugins/dataTables/buttons.html5.min.js"></script>
<script src="/js/plugins/dataTables/buttons.print.min.js"></script>
<script src="https://momentjs.com/downloads/moment.js"></script>
<script src="https://momentjs.com/downloads/moment-with-locales.js"></script>
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<!-- Mainly scripts -->
<script src="/js/utils.js"></script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo $api_key_google_maps; ?>"></script>

<!--
  <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCc_ej3xfMCxtyW7oYhgUIw8Rk_NK_ASR0"></script>
  <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAAKrvy7BqPz9GOwH-Ss3yNwmBeykitNhI"></script>-->
<!-- &callback=initMap -->

<style>
        .row_descargar:hover
        {
            background: #f2f2f2;
            cursor: pointer;
        }
        .row_descargar
        {
            border-top: 1px solid #ccc;
        }

    .row_descargar div, .row_descargar div i
    {
        margin: 5px 0 0 0 !important;
        font-size: 20px !important;
    }
</style>

<script type="text/javascript">
var markers = [], lat = "", lon = "", info_map = [];

/*
function renderDirections(result, map) {
  var directionsRenderer1 = new google.maps.DirectionsRenderer({
    directions: result,
    routeIndex: 0,
    map: map,
    polylineOptions: {
      strokeColor: "green"
    }
  });
  console.log("routeindex1 = ", directionsRenderer1.getRouteIndex());

  var directionsRenderer2 = new google.maps.DirectionsRenderer({
    directions: result,
    routeIndex: 1,
    map: map,
    polylineOptions: {
      strokeColor: "blue"
    }
  });
  console.log("routeindex2 = ", directionsRenderer2.getRouteIndex()); //line 17
}

function calculateAndDisplayRoute(origin, destination, directionsService, directionsDisplay, map) {
  directionsService.route({
    origin: origin,
    destination: destination,
    travelMode: google.maps.TravelMode.DRIVING,
    provideRouteAlternatives: true
  }, function(response, status) {
    if (status === google.maps.DirectionsStatus.OK) {
      renderDirections(response, map);
    } else {
      console.log('Directions request failed due to ' + status);
    }
  });
}
*/
function calculateAndDisplayRoute(map, location_ini, location_fin) {

        var directionsServiceTmp = new google.maps.DirectionsService;
        var directionsDisplayTmp = new google.maps.DirectionsRenderer;
        //var km = "";
        //location_ini = 14.595188 + ',' + -90.5166266;
        //location_fin = 14.641828 + ',' + -90.5152771;


        directionsServiceTmp.route({
            origin: location_ini,
            destination: location_fin,
            optimizeWaypoints: true,
            travelMode: google.maps.DirectionsTravelMode['DRIVING'],
            unitSystem: google.maps.DirectionsUnitSystem['METRIC'],
            travelMode: 'DRIVING'
        }, function(response, status) {

            console.log("ROUTE = ", response);
            if (status === 'OK') {
                // Aqui con el response podemos acceder a la distancia como texto 
                console.log("Origen: ",response.routes[0].legs[0].start_address, "| Destino: ",response.routes[0].legs[0].end_address, " | Distancia: ", response.routes[0].legs[0].distance.text);
                //km = response.routes[0].legs[0].distance.text;
                // Obtenemos la distancia como valor numerico en metros 
               //console.log(response.routes[0].legs[0].distance.value);

                directionsDisplayTmp.setDirections(response);
            }
        });
        directionsDisplayTmp.setMap(map);
        //return km;
}


  function initMap() {
          var map;
          var bounds = new google.maps.LatLngBounds();
          var lt = parseFloat(lat);
          var ln = parseFloat(lon);
          console.log("lat initMap = ", lt, "lon initMap = ", ln);

          var mapOptions = {
              center: {lat: lt, lng: ln},
              zoom: 14,
              mapTypeId: 'roadmap'
          };

          map = new google.maps.Map(document.getElementById('mapa'), {
              center: {lat: lt, lng: ln},
              zoom: 14,
              mapTypeId: 'roadmap'
          });

          map.setTilt(50);
          console.log("markers initMap = ", markers);
          // Crear múltiples marcadores desde la Base de Datos 
          //['', 19.326174, -99.0949096],
          var marcadores = 
              markers
          ;

          console.log("marcadores initMap = ", marcadores);
          // Creamos la ventana de información para cada Marcador
/*
          var ventanaInfo = [
              <?php // include('php/info_marcadores.php'); ?>
          ];
*/
          // Creamos la ventana de información con los marcadores 
          var mostrarMarcadores = new google.maps.InfoWindow(),
              marcadores, i, center_almacen;

          // Colocamos los marcadores en el Mapa de Google 
          var color_point = "";
          for (i = 0; i < marcadores.length; i++) {
              var position = new google.maps.LatLng(marcadores[i][1], marcadores[i][2]);
              bounds.extend(position);

              if(i == 0) center_almacen = position;

              color_point = "#ff3300";
              if(info_map[i][0] == 'Almacen-Data')
              {
                  color_point = "#0066ff";
              }
              else 
              {
                    if(info_map[i][1] == 'F')
                    {
                        color_point = "#00ff00";
                    }
              }

              marker = new google.maps.Marker({
                  position: position,
                  map: map,
                  icon: {
                      path: 'M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z',
                      scale: 1, //tamaño
                      strokeColor: '#000', //color del borde
                      strokeWeight: 1, //grosor del borde
                      fillColor: color_point, //color de relleno
                      fillOpacity:1// opacidad del relleno
                  },
                  title: marcadores[i][0]
              });


              // Colocamos la ventana de información a cada Marcador del Mapa de Google 
              //var km = "66";
              if(i > 0)
              {
                  //var directionsService = new google.maps.DirectionsService();
                  //var directionsDisplay = new google.maps.DirectionsRenderer();
                  //directionsDisplay.setMap(map);
                  //calculateAndDisplayRoute(position, center_almacen, directionsService, directionsDisplay, map);
                  calculateAndDisplayRoute(map, position, center_almacen);
              }

              google.maps.event.addListener(marker, 'click', (function(marker, i) {
                  return function() {
                      //mostrarMarcadores.setContent();
                      mostrarMarcadores.open(map, marker);
                  }
              })(marker, i));

              // Centramos el Mapa de Google para que todos los marcadores se puedan ver 
              //map.fitBounds(bounds);
          }

          // Aplicamos el evento 'bounds_changed' que detecta cambios en la ventana del Mapa de Google, también le configramos un zoom de 14 
          map.setCenter(center_almacen);
          var boundsListener = google.maps.event.addListener((map), 'bounds_changed', function(event) {
              this.setZoom(14);
              google.maps.event.removeListener(boundsListener);
          });

      }
</script>

<style>
  #mapa {
    height: 50vh;
  }

    .bt{
        margin-right: 10px;
    }
    .btn-blue{
        background-color: blue !important;
        border-color: blue !important;
        color: white !important;
    }
    .ui-jqgrid tr.jqgrow td[aria-describedby="grid-table_accion"]{
        display: table;
        width: 100%;
        text-align: center;
    }
    .ui-jqgrid tr.jqgrow td[aria-describedby="grid-table_accion"] a{
        margin: 0 5px;
    }
</style>
<div class="wrapper wrapper-content animated fadeInRight" id="verEmpresa">
    <h3>Embarques</h3>
    <div class="row">
        <div class="col-md-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
    <?php
    $cliente_almacen_style = ""; $cve_cliente = ""; $almacen_cliente = "";
    if($_SESSION['es_cliente'] == 1) 
    {
      $cliente_almacen_style = "style='display: none;'";
      $cve_cliente = $_SESSION['cve_cliente'];
      $alm_cliente = \db()->prepare("SELECT a.id as id_almacen 
                                     FROM c_usuario u 
                                     LEFT JOIN c_almacenp a ON a.clave = u.cve_almacen
                                     WHERE u.cve_usuario = '$usuario' AND u.cve_cliente = '$cve_cliente'");
      $alm_cliente->execute();
      $almacen_cliente = $alm_cliente->fetch()['id_almacen'];

    }

    $cve_proveedor = "";
    if($_SESSION['es_cliente'] == 2) 
    {
        $cliente_almacen_style = "style='display: none;'";
        $cve_proveedor = $_SESSION['cve_proveedor'];
    }

    if(isset($_SESSION['id_proveedor']))
    {
        $cve_proveedor = $_SESSION['id_proveedor'];
    }

    ?>
  <input type="hidden" id="cve_proveedor" value="<?php echo $cve_proveedor; ?>">
  <input type="hidden" id="cve_cliente" value="<?php echo $cve_cliente; ?>">
  <input type="hidden" id="almacen_cliente" value="<?php echo $almacen_cliente; ?>">




<div class="modal fade" id="fotos_oc_th" role="dialog">
    <div class="vertical-alignment-helper">
          <div class="modal-dialog vertical-align-center">
            <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Folio Embarque <span id="n_folio"></span></h4>
                    </div>
                    <div class="modal-body">
                        <form id="form-subir-fotos-th" action="import" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Seleccione uno o más archivos para documentar (Límite 5) </label>
                                <input type="hidden" name="folio_documento" id="folio_documento" value="">

                                <?php //accept=".png, .PNG, .jpg, .JPG, .JPEG, .jpeg" ?>
                                <input type="file" name="image_file_th" id="file_th" class="form-control"  required>
                                <br>
                                <button id="btn-fotos-th" type="button" class="btn btn-primary">Subir Archivo</button>
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


<div class="modal fade" id="fotos_oc_th_emb" role="dialog">
    <div class="vertical-alignment-helper">
          <div class="modal-dialog vertical-align-center" style="width:80%;">
            <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Fotos del Embarque <span id="n_folio_emb"></span></h4>
                    </div>
                    <div class="modal-body">

                        <div id="fotos_th_emb">
                            
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

                        <div class="col-md-3" <?php echo $cliente_almacen_style; ?>>
                            <label>Almacén</label>
                            <select id="almacen_filter" class="form-control chosen-select">
                                <option value="">Seleccione</option>
                                <?php foreach($almacenes as $almacen): ?>
                                    <option value="<?php echo $almacen->id?>"><?php echo "({$almacen->clave}) $almacen->nombre" ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <div class="input-group" style="margin-top: 22px">
                            <select name="ruta_busqueda" id="ruta_busqueda" style="width:100%;" class="form-control">
                                <option value="">Seleccione Ruta</option>
                                <?php //foreach( $listaRutas->getAllInventario($_SESSION['id_almacen'], '1') AS $p ): ?>
                                <?php foreach( $listaRutas->getAllRutasPedidos($_SESSION['id_almacen']) AS $p ): ?>
                                <option value="<?php echo $p->cve_ruta; ?>"><?php echo "( ".$p->cve_ruta." ) - ".$p->descripcion; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Fecha Inicial:</label>
                                <div class="input-group date" id="data_1">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input id="fechaini" type="text" class="form-control" value=""><?php // echo $fecha_semana; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Fecha Final:</label>
                                <div class="input-group date" id="data_2">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input id="fechafin" type="text" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="input-group" style="margin-top: 22px">
                            <select name="status_embarque" id="status_embarque" style="width:100%;" class="form-control">
                                <option value="">Status</option>
                                <option selected value="T">En Ruta</option>
                                <!--<option value="E">Entregando</option>-->
                                <option value="F">Entregado</option>
                            </select>
                        </div>
                        </div>

                        <div class="col-md-3">
                            <div class="input-group" style="margin-top: 22px">
                                <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                <div class="input-group-btn">
                                    <button type="button" class="btn btn-primary" id="buscarA">
                                        <span class="fa fa-search"></span >Buscar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <?php /*if($ag[0]['Activo']==1){?>
                                <a href="#" onclick="agregar()" style="margin-top: 22px"><button class="btn btn-primary" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>
                            <?php //} */ ?>
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

<div class="modal fade" id="detalleModal" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 1200px!important;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Detalle de Embarque <span id="n_inventario">0</span></h4>

                    <div id="guia_transporte_div" style="display: none;text-align: center;">
                    <br>
                    <b>Guía de Transporte: <span></span></b>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="table-responsive" style="overflow-x: scroll;">
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

<div class="modal fade" id="UsuariosRecibir" role="dialog" style="overflow: scroll;">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 1200px!important;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Usuarios que Recibieron los pedidos del Embarque <span id="f_embarque">0</span></h4>
                </div>
                <div class="modal-body">
                    <table class="table table-responsive table-bordered">
                        <thead>
                            <th style="text-align: center;"><input style="cursor:pointer;" type="checkbox" id='seleccionar_todos' title="Seleccionar Todos" />
                                <input type="hidden" name="entregar_embarque" id="entregar_embarque" value=""></th>
                            <th>Pedido</th>
                            <th>Destinatario</th>
                            <th>Registro del Nombre de quién Recibe</th>
                            <th>Fecha Entrega</th>
                        </thead>
                        <tbody id="usuarios_receptores_embarque">
                        </tbody>
                    </table>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button id="btn-enviar" type="button" class="btn btn-primary">Marcar Como Entregado</button><!--funcion de import-->
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="detalleGps" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 1200px!important;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Planeación de Entregas <span id="n_inventario_gps">0</span></h4>
                </div>
                <div class="modal-body">
                    <div id="mapa" style="overflow-x: scroll;">
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
                                <div id="grid-pager-pedidos"></div>
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

      <textarea id="text_dw" style="display: none;">1|2|3|</textarea>
      <input type="button" id="btn_dw" value="Download" style="display:none;" />


<script type="text/javascript">
    /**
     * @author Ricardo Delgado.
     * Busca y selecciona el almacen predeterminado para el usuario.
     */ 

    function almacenPrede()
    { 
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                idUser: '<?php echo $_SESSION["id_user"]?>',
                action: 'search_almacen_pre'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url: '/api/almacenPredeterminado/index.php',
            success: function(data) {
                if (data.success == true && $("#almacen_cliente").val() == '') 
                {
                    document.getElementById('almacen_filter').value = data.codigo.id;
                }
                else
                    document.getElementById('almacen_filter').value = $("#almacen_cliente").val();

                setTimeout(function() {
                    ReloadGrid();
                }, 1000);
            },
            error: function(res){
                window.console.log(res);
            }
        });
    }
almacenPrede();
      $("#txtCriterio").keyup(function(event){
        if(event.keyCode == 13)
        {
            console.log("search = ", $("#txtCriterio").val());
            $("#buscarA").click();
        }
    });

      $("#ruta_busqueda, #status_embarque").change(function(){
      //  console.log("search = ", $("#txtCriterio").val());
        ReloadGrid();
      });


      $("#buscarA").click(function(){
      //  console.log("search = ", $("#txtCriterio").val());
        ReloadGrid();
      });

    $(function($) {
        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";

        //resize to fit page size
        $(window).on('resize.jqGrid', function () {
            $(grid_selector).jqGrid( 'setGridWidth', $(".page-content").width() - 60 );
        });

        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) {
            if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) 
            {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $(grid_selector).jqGrid( 'setGridWidth', parent_column.width() );
                }, 0);
            }
        })
        
        $(grid_selector).jqGrid({
            url:'/api/embarques/lista/index.php',
            datatype: 'local',
            shrinkToFit: false,
            height:'auto',
            mtype: 'POST',
            colNames:[ 'Acción', 'Folio', 'Folios Pedidos', 'Ruta', 'Stops', 'Pedidos', 'Pallets', 'Cajas','Fecha de Embarque','Fecha Entrega', 'Status', 'Transporte Externo', 'Transporte', 'Clave','Placas',/*,'Entregas'*/'Cap. Volumen','Volumen Utilizado','Peso Maximo','Peso Utilizado', 'Tiene Foto'],
            colModel:[
                {name:'accion',index:'accion', align:'center', width:<?php if($cve_cliente != "") echo "150"; else echo "300"; ?>, fixed:true, sortable:false, resize:false, formatter: getActions},
                {name:'folio',index:'folio', align:'center', editable:false, sortable:false,width:80},
                {name:'folios_pedidos',index:'folios_pedidos', editable:false, sortable:false,width:100, align: "center", hidden: true},
                {name:'ruta',index:'ruta', align:'center', editable:false, sortable:false,width:120, hidden: <?php if($cve_cliente != "") echo "true"; else echo "false"; ?>},
                {name:'stops',index:'stops', editable:false, sortable:false,width:100, align: "center"},
                {name:'pedidos',index:'pedidos', editable:false, sortable:false,width:100, align: "center"},
                {name:'total_pallets',index:'total_pallets', editable:false, sortable:false,width:80,align:"center", hidden: true},
                {name:'total_cajas',index:'total_cajas', editable:false, sortable:false,width:80,align:"center", hidden: true},
                //{name:'total_piezas',index:'total_piezas', editable:false, sortable:false,width:100, align:"right"},
                //{name:'total_cajas',index:'total_cajas', editable:false, sortable:false,width:100,align:"right"},
                //{name:'chofer',index:'chofer',  sortable:false,width:150, align:"right"},
                {name:'fecha_embarque',index:'fecha_embarque', editable:false, sortable:false,width:150,align:"center"},
                {name:'fecha_entrega',index:'fecha_entrega', editable:false, sortable:false,width:100, align:"center"},
                {name:'status',index:'status', editable:false, sortable:false,width:100},
                {name:'transporte_externo',index:'transporte_externo', editable:false, sortable:false,width:100, align:"right", hidden: true},
                {name:'transporte',index:'chofer',  sortable:false,width:100, align:"center", hidden: <?php if($cve_cliente != "") echo "true"; else echo "false"; ?>},
                {name:'clave',index:'clave',  sortable:false,width:100, align:"center", hidden: <?php if($cve_cliente != "") echo "true"; else echo "false"; ?>},
                {name:'placas',index:'placas',  sortable:false,width:100, align:"center", hidden: <?php if($cve_cliente != "") echo "true"; else echo "false"; ?>},
                //{name:'entregas',index:'entregas', editable:false, sortable:false,width:100,align:"center", hidden: true <?php //if($cve_cliente != "") echo "true"; else echo "false"; ?>},
                {name:'volmax',index:'volmax', editable:false, sortable:false,width:100,align:"right", hidden: <?php if($cve_cliente != "") echo "true"; else echo "false"; ?>},
                {name:'volumen',index:'volumen', editable:false, sortable:false,width:120,align:"right", hidden: <?php if($cve_cliente != "") echo "true"; else echo "false"; ?>},
                {name:'pesomax',index:'pesomax', editable:false, sortable:false,width:100,align:"right", hidden: <?php if($cve_cliente != "") echo "true"; else echo "false"; ?>},
                {name:'peso',index:'peso', editable:false, sortable:false,width:100,align:"right", hidden: <?php if($cve_cliente != "") echo "true"; else echo "false"; ?>},
                {name:'tiene_foto',index:'tiene_foto', editable:false, sortable:false,width:100, hidden: true},
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'folio',
            viewrecords: true,
            sortorder: "desc",
            loadComplete: function(data){
                console.log("SUCESS: ", data);
                //almacenPrede();
            },
            loadError: function(data){
                console.log("ERROR: ", data);
            }
            //loadonce: true,
            //loadComplete: almacenPrede()
        });

        $(grid_selector).jqGrid('navGrid',  "#grid-pager",{edit: false, add: false, del: false, search: false},{height: 200, reloadAfterSubmit: true});
      
        function getActions(cell, options, row)
        {
            var id = row[1];
            var folio = row[2];
            var status = row[10];
            var transporte_externo = row[11];
            var tiene_foto = row[19];
            var cve_cia = <?php echo $_SESSION['cve_cia']; ?>;
            //onclick="printExcel(${id},'${folio}')"printPDFDiscrepancias
            //<a href="/embarques/exportar?id=${id}&folios=${folio}" title="Imprimir ASN"><i class="fa fa-file-excel-o"></i></a>
            //<a href="#" onclick="exportarPDFLista(${id},'${folio}')" title="Lista de Embarque con Precios"><i class="fa fa-file-pdf-o"></i></a>
            var html = "";

                //html += '<a href="#" onclick="ver_fotos(\''+ folio +'\')"><i class="fa fa-camera" title="Fotos de embarque"></i></a>&nbsp;';
            var mostrar_foto = "";
            if(tiene_foto == 'N')
                mostrar_foto = "style='display:none;'";

            if($("#cve_cliente").val() == "")
            {
                html = `<a href="#" onclick="verDetalle(${id},'${folio}')" title="Ver Detalle"><i class="fa fa-search"></i></a>
                    <a href="#" ${mostrar_foto} onclick="ver_fotos('${id}')"><i class="fa fa-camera" title="Fotos de embarque"></i></a>`;
                if($("#lista_empaque").val() == "1")
                html += `<a href="#" onclick="printPDFEmpaque(${id},'${folio}')" title="Lista de Empaque"><i class="fa fa-file-pdf-o"></i></a>`;
                if($("#entrega_programada").val() == "1")
                html += `<a href="/api/koolreport/export/reportes/embarques/reporte_embarques?id=${id}&cve_cia=${cve_cia}" target="_blank" title="Entregas Programadas"><i class="fa fa-file-pdf-o"></i></a>`;
                if($("#salida_inventario").val() == "1")
                html +=  `<a href="/api/koolreport/export/reportes/embarques/salida_de_inventario?id=${id}&cve_cia=${cve_cia}" target="_blank" title="SALIDA DE INVENTARIO"><i class="fa fa-file-pdf-o"></i></a>`;
                if($("#lista_embarque").val() == "1")
                html += `<a href="#" onclick="printPDF(${id},'${folio}')" title="Lista de Embarque"><i class="fa fa-file-pdf-o"></i></a>`;
                if($("#auditoria_embarque").val() == "1")
                html += `<a href="#" onclick="printPDFAuditoria(${id},'${folio}')" title="Auditoría de Embarque"><i class="fa fa-file-pdf-o"></i></a>`;
                if($("#discrepancia").val() == "1")
                html += `<a href="#" onclick="printPDFDiscrepancias(${id},'${folio}')" title="Discrepancias Embarque"><i class="fa fa-file-pdf-o"></i></a>`;
                if($("#imprimir_asn").val() == "1")
                html += `<a href="#" onclick="printExcelASN(${id})" title="Imprimir ASN"><i class="fa fa-file-excel-o"></i></a>`;
                if($("#imp_archivo_despacho").val() == "1")
                html += `<a href="#" onclick="printExcelDespacho(${id})" title="Imprimir Archivo de Despacho"><i class="fa fa-file-excel-o"></i></a>`;
                html += `<a href="#" onclick="verGps(${id})" title="GPS"><i class="fa fa-map-marker"></i></a>`;
        //                <a href="/api/koolreport/export/reportes/auditoria/etiquetas?folio=`+id+`&cve_cia=`+cve_cia+`" target="_blank" title="Código de Barras"><i class="fa fa-barcode"></i></a>

                    //&& $("#cve_proveedor").val() == ""
                    if(transporte_externo == 0 && status != 'ENTREGADO' && $("#permiso_editar").val() == 1)
                      html += `<a href="#" onclick="MarcarEntregadoUsuarios(${id}, 1)" title="Realizar Entrega"><i class="fa fa-check"></i></a>`;
                    else if(transporte_externo == 0 && status == 'ENTREGADO' /*&& $("#permiso_editar").val() == 1*/)
                        html += `<a href="#" onclick="MarcarEntregadoUsuarios(${id}, 0)" title="Ver Entregados"><i class="fa fa-check"></i></a>`;

                      if($("#des_aviso_despacho").val() == "1")
                      html += `<a href="#" onclick="GenerarTxt(${id})" title="Descargar Aviso Despacho"><i class="fa fa-download" aria-hidden="true"></i></a>`;
                      if($("#des_archivo_despacho").val() == "1")
                      html += `<a href="#" onclick="GenerarTxtDespacho(${id})" title="Descargar Archivo Despacho"><i class="fa fa-download" aria-hidden="true"></i></a>`;
            }
            else
            {
                html = `<a href="#" onclick="verDetalle(${id},'${folio}')" title="Ver Detalle"><i class="fa fa-search"></i></a>
                    <a href="#" ${mostrar_foto} onclick="ver_fotos('${id}')"><i class="fa fa-camera" title="Fotos de embarque"></i></a>
                    <a href="#" onclick="printPDFEmpaque(${id},'${folio}')" title="Lista de Empaque"><i class="fa fa-file-pdf-o"></i></a>
                    <a href="/api/koolreport/export/reportes/embarques/reporte_embarques?id=${id}&cve_cia=${cve_cia}" target="_blank" title="Entregas Programadas"><i class="fa fa-file-pdf-o"></i></a>
                    `;
            }
            html += `<a href="#" onclick="ver_documentos('${id}')"><i class="fa fa-upload" title="Subir Documentos de Embarque"></i></a>`;
            html += `<a href="#" onclick="descargar_documentos('${id}')" title="Descargar Documentos de Embarque"><i class="fa fa-download" ></i></a>`;
            return html;
        }

        $(window).triggerHandler('resize.jqGrid');
        $(document).one('ajaxloadstart.page', function(e) {
            $.jgrid.gridUnload(pedidos_grid_selector);
            $.jgrid.gridUnload(grid_selector);
            $('.ui-jqdialog').remove();
        });
    });
  
    $(function($) {


        var grid_selector = "#grid-detalles-pedidos";
        var pager_selector = "#grid-pager-pedidos";

        //resize to fit page size
        $(window).on('resize.jqGrid', function() {
            $("#grid-detalles-pedidos").jqGrid('setGridWidth', $("#modal-detalles-pedido .modal-body").width() - 2);
        });
          
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) {
            if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) 
            {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $(grid_selector).jqGrid( 'setGridWidth', $("#modal-detalles-pedido .modal-body").width() - 2);
                }, 0);
            }
        })

        $(grid_selector).jqGrid({
            url: '/api/administracionembarque/lista/index_nikken.php',
            shrinkToFit: false,
            height: 250,
            mtype: 'POST',
            colNames: ["Clave", "Artículo", "Cantidad", "Peso(Kg)"],
            colModel: [
                {name: 'clave',index: 'clave',width: 88,editable: false,sortable: false},
                {name: 'articulo',index: 'articulo',width: 350,editable: false,sortable: false},
                {name: 'cantidad',index: 'cantidad',width: 80,editable: false,align: 'right',sortable: false,},
                {name: 'peso',index: 'peso',width: 120,editable: false,align: 'right',sortable: false}
            ],
            rowNum: 10,
            rowList: [10, 20, 30],
            pager: pager_selector,
            viewrecords: true,
            loadComplete :  function(){
                $('#pedido_cantidad').text($("#grid-detalles-pedidos").getGridParam("reccount")); 
            }
        });
        $(grid_selector).jqGrid('navGrid',  "#grid-pager-pedidos",{edit: false, add: false, del: false, search: false},{height: 200, reloadAfterSubmit: true});

        $(window).triggerHandler('resize.jqGrid'); //trigger window resize to make the grid get the correct size
        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });
  
    function cargarGridDetallesPedidos(nro, guia, volumen) 
    {
        $('#grid-detalles-pedidos').jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    folio: infoTemporal.folio,
                    partida:nro,
                    action: 'cargarDetallePedido'
                },
                datatype: 'json'
            })
            .trigger('reloadGrid', [{
                current: true
            }]);
        console.log("volumen",volumen);
        console.log("peso",infoTemporal.peso); 
        $('#pedido_nro').text(nro); 
        $('#pedido_folio').text(infoTemporal.folio);
        $('#pedido_cliente').text(infoTemporal.cliente);
        $('#pedido_clave').text(infoTemporal.clave);
        $('#pedido_guias').text(guia); 
        $('#pedido_volumen').text(volumen.toFixed(4)); 
        $('#pedido_peso').text(infoTemporal.peso.toFixed(4));
        $("#modal-detalles-pedido").modal('show');
    }
  
    $(document).ready(function() {
        $('#modal-detalles-pedido').on('shown.bs.modal', function (e) {
            $("#grid-detalles-pedidos").jqGrid('setGridWidth', $("#modal-detalles-pedido .modal-body").width());
        });

        $(window).on('resize.jqGrid', function() {
            $("#grid-detalles-pedidos").jqGrid('setGridWidth', $("#modal-detalles-pedido .modal-body").width());
        })

        $("#grid-table").jqGrid('setGridWidth', $("#grid-table").parent().width() );
    });

    //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////
    function ReloadGrid() 
    {
        console.log("cve_cliente = ", $("#cve_cliente").val());
        console.log("cve_proveedor = ", $("#cve_proveedor").val());
        var almacen = $("#id_almacen").val();
        //($("#almacen_filter").val() == '' || $("#almacen_filter").val() == null)?($("#id_almacen").val()):($("#almacen_filter").val());
        console.log("almacen_filter = ", almacen);
        if(almacen != '')
        {
          console.log("search5 = ", $("#txtCriterio").val());
            $('#grid-table').jqGrid('clearGridData')
                .jqGrid('setGridParam', {postData: {
                    search: $("#txtCriterio").val(),
                    ruta: $("#ruta_busqueda").val(),
                    status_embarque: $("#status_embarque").val(),
                    fechaini: $("#fechaini").val(),
                    fechafin: $("#fechafin").val(),
                    almacen: almacen,
                    cve_cliente: $("#cve_cliente").val(),
                    cve_proveedor: $("#cve_proveedor").val(),
                    action: 'load'
                }, datatype: 'json', page : 1, success: function(data){console.log("SUCCESS: ", data);}, 
                    always: function(data){console.log("ERROR: ", data);}})
                .trigger('reloadGrid',[{current:true}]);
        }
        else
        {
            swal("Error", "Por favor seleccione un almacén", "error");
        }
    }
  
    var infoTemporal = {};
    function ReloadDetalle(id, folios) 
    {
        console.log("Dcve_cliente = ", $("#cve_cliente").val());
        console.log("Dcve_proveedor = ", $("#cve_proveedor").val());
      console.log("folios = ", folios, " id = ", id);
        $('#grid-table2').jqGrid('clearGridData')
        .jqGrid('setGridParam', {postData: {
            action: 'detalle',
            cve_cliente: $("#cve_cliente").val(),
            cve_proveedor: $("#cve_proveedor").val(),
            folio: folios,
            id: id
        }, datatype: 'json', page : 1})
        .trigger('reloadGrid',[{current:true}]);
    }


    $(function($) {
        var grid_selector = "#grid-table2";
        var pager_selector = "#grid-pager2";

        //resize to fit page size
        $(window).on('resize.jqGrid', function () {
            $(grid_selector).jqGrid( 'setGridWidth', $(".page-content").width() - 60 );
        });

        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) {
            if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) 
            {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $(grid_selector).jqGrid( 'setGridWidth', parent_column.width() );
                }, 0);
            }
        })

        $(grid_selector).jqGrid({
            url:'/api/embarques/lista/index.php',
            datatype: 'local',
            shrinkToFit: false,
            height:'auto',
            mtype: 'POST',
            colNames:['Acción', 'Ruta','Pedido','OC Cliente', 'Cliente', 'Nombre', 'Clave Dest.', 'Destinatario', 'Clave', 'Descripción', 'Cant. Enviada', 'Lote', 'Caducidad', 'Serie', 'Pallet', 'Caja', 'Piezas|Kg', 'Volumen (m3)', 'Peso(Kg)', 'Caja Empaque', 'Guia Embarque', 'Peso Artículo', 'Partida'],
            colModel:[
                {name:'accion',index:'accion', align:'center', width:80, fixed:true, sortable:false, resize:false, formatter: getActions, hidden: true},
                {name:'ruta',index:'ruta', editable:false, sortable:false,width:120, align:'left'},
                {name:'folio',index:'folio', editable:false, sortable:false,width:120, align:'center'},
                {name:'oc_cliente',index:'oc_cliente', editable:false, sortable:false,width:120, align:'center'},
                {name:'cve_cliente',index:'cve_cliente', editable:false, sortable:false,width:110},
                {name:'cliente',index:'cliente', editable:false, sortable:false,width:300},
                {name:'id_destinatario',index:'id_destinatario', editable:false, sortable:false,width:100},
                {name:'nombre_destinatario',index:'nombre_destinatario', editable:false, sortable:false,width:300},
                {name:'clave',index:'clave', editable:false, sortable:false,width:100},
                {name:'descripcion_producto',index:'descripcion_producto', editable:false, sortable:false,width:300},
                {name:'cantidad',index:'cantidad', editable:false, sortable:false,width:120, align: 'right'},
                {name:'lote',index:'lote', editable:false, sortable:false,width:200},
                {name:'caducidad',index:'caducidad', editable:false, sortable:false,width:100, align:'center'},
                {name:'serie',index:'serie', editable:false, sortable:false,width:100},
                {name:'pallet',index:'pallet', editable:false, sortable:false,width:100, align:'center'},
                {name:'caja',index:'caja', editable:false, sortable:false,width:100, align:'center'},
                {name:'piezas',index:'piezas', editable:false, sortable:false,width:100, align:'center'},
                {name:'volumen',index:'volumen', editable:false, sortable:false,width:150, align: 'right'},
                {name:'peso',index:'peso', editable:false, sortable:false,width:100, align: 'right'},
                {name:'tipo_caja',index:'tipo_caja', editable:false, sortable:false,width:150},
                {name:'guia',index:'guia', editable:false, sortable:false,width:250, align:'center'},

                {name:'peso_producto',index:'peso_producto', editable:false, sortable:false,width:100, hidden: true},
                {name:'partida',index:'partida', editable:false, sortable:false,width:80, align:'center', hidden: true},
            ],
            rowNum:30,
            rowList:[10,30,40,50],
            pager: pager_selector,
            sortname: 'folio',
            viewrecords: true,
            sortorder: "desc",
            loadComplete: function(data)
            {
                console.log("Detalle = ", data); 
                $("#guia_transporte_div").hide(); 
                if(data.guia_transporte != "") 
                {
                  $("#guia_transporte_div span").text(data.guia_transporte);
                  $("#guia_transporte_div").show();
                }
            },
            loadError: function(data){console.log("Detalle ERROR = ", data);},
            loadonce: true
        });
        $(grid_selector).jqGrid('navGrid',  "#grid-pager2",{edit: false, add: false, del: false, search: false},{height: 200, reloadAfterSubmit: true});
        
        function getActions(cell, options, row)
        {
            var folio = row[1];
            var cliente = row[8];
            var clave = row[9];
            var guias = row[5];
            var peso = row[7];
            var nro = row[10];
            var volumen =row[6]
            var html = '<a href="#" onclick="verDetallePedido(\''+folio+'\',\''+cliente+'\',\''+clave+'\',\''+guias+'\','+peso+','+nro+','+volumen+')" title="Ver Detalle"><i class="fa fa-search"></i></a>'
            return html;
        }

        $(window).triggerHandler('resize.jqGrid');
        $(document).one('ajaxloadstart.page', function(e) {
            $.jgrid.gridUnload(pedidos_grid_selector);
            $.jgrid.gridUnload(grid_selector);
            $('.ui-jqdialog').remove();
        });
    });
  
    function verDetallePedido(folio,cliente,clave,guias,peso,nro,volumen)
    {
        infoTemporal.folio = folio;
        infoTemporal.cliente = cliente;
        infoTemporal.clave = clave;
        infoTemporal.guias = guias;
        infoTemporal.peso = peso;
        infoTemporal.nro = nro;
        infoTemporal.volumen = volumen;
        console.log("peso"+peso+"  volumen"+volumen);
        cargarGridDetallesPedidos(nro,guias,volumen);
    }

    function verDetalle(id, folios)
    {
      $("#n_inventario").text(id);
      $("#detalleModal").modal('show');
      ReloadDetalle(id, folios);
    }


    function MarcarEntregado(folio)
    {

            swal({
                title: "Cambiar Status a Entregado el folio #"+folio+"?",
                text: "¿Está Seguro de Proceder a Cambiar el estado? Esto habilitará los transportes ocupados en este folio",
                type: "info",

                cancelButtonText: "No",
                cancelButtonColor: "#14960a",
                showCancelButton: true,

                confirmButtonColor: "#55b9dd",
                confirmButtonText: "Si",
                closeOnConfirm: true
            }, function() {

                MarcarEntregadoUsuarios(folio, 1);
                //MarcarEntregadoOK(folio);

            });

    }

    function MarcarEntregadoUsuarios(folio, mostrar_boton) 
    {
        $("#f_embarque").text(folio);
        $("#entregar_embarque").val(folio);
        $("#UsuariosRecibir").modal('show');
        $("#usuarios_receptores_embarque").empty();
        if(mostrar_boton == 0) $("#btn-enviar, #seleccionar_todos").hide(); else $("#btn-enviar, #seleccionar_todos").show();


        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                folio: folio,
                action: 'traer_destinatarios_pedidos'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url:'/api/embarques/lista/index.php',
            success: function(data) {
                //console.log("SUCCESS DESTINATARIOS", data);
                $("#usuarios_receptores_embarque").append(data.table);
                //console.log(data);
                //swal("Éxito", "Folio #"+folio+" marcado como Entregado", "success");
                //ReloadGrid();

            },
            error: function(data){
                console.log("ERROR");
                console.log(data);
            }
        });

    }

    $("#seleccionar_todos").click(function()
    {
        if($(this).prop("checked") == true)
            $(".folios_dest").prop("checked", true);
        else
            $(".folios_dest").prop("checked", false);
    });

    $("#btn-enviar").click(function(){

        var array_receptores = [], array_valores = [], array_folios = [], acepto = true;
        $(".dest_receptores").each(function(i, e){

            if($(this).data("status") == 'T')
            {
                array_receptores.push($(this).data("id"));
                array_valores.push($(this).val());
            }

            //if($(this).val() == '') acepto = false;
        });

        $(".folios_dest").each(function(i, e){

            if($(this).prop("checked") == true)
            {
                array_folios.push($(this).data('fol'));
            }

            //if($(this).val() == '') acepto = false;
        });

        console.log("array_folios", array_folios);
        console.log("array_receptores", array_receptores);
        console.log("array_valores", array_valores);
        //return;

        if(array_folios.length == 0) 
            swal("Error", "Debe Seleccionar 1 o más Folios para entregar", "error");
        else 
            MarcarEntregadoOK($("#f_embarque").text(), array_folios, array_receptores, array_valores);

    });

    function MarcarEntregadoOK(folio_embarque, array_folios, array_receptores, array_valores)
    {
        console.log("MarcarEntregado = ", folio_embarque);
        console.log("array_folios", array_folios);
        console.log("array_receptores", array_receptores);
        console.log("array_valores", array_valores);
        console.log("cve_usuario", $("#cve_usuario").val());

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                folio_embarque: folio_embarque,
                array_folios: array_folios,
                usuario: $("#cve_usuario").val(),
                array_receptores: array_receptores,
                array_valores: array_valores,
                action: 'marcar_entregado'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url:'/api/embarques/lista/index.php',
            success: function(data) {
                //console.log("SUCCESS");
                //console.log(data);
                swal("Éxito", "Folios marcado como Entregado", "success");
                $("#UsuariosRecibir").modal('hide');
                ReloadGrid();

            },
            error: function(data){
                console.log("ERROR");
                console.log(data);
            }
        });
    }
    function download(filename, textInput) {

        console.log(filename, textInput);
          var element = document.createElement('a');
          element.setAttribute('href','data:text/plain;charset=utf-8, ' + encodeURIComponent(textInput));
          element.setAttribute('download', filename);
          document.body.appendChild(element);
          element.click();
          //document.body.removeChild(element);
    }


    function ImprimirFotos_th(folio)
    {
        console.log("ImprimirFotos_th", folio);
          $.ajax({
              type: "POST",
              //dataType: "json",
              data: 
              {
                  folio: folio,
                  action : "cargarFotosTHEmbarque"
              },/*
              beforeSend: function(x)
              {
                  if(x && x.overrideMimeType) 
                  { 
                    x.overrideMimeType("application/json;charset=UTF-8"); 
                  }
              },*/
              url: '/api/embarques/lista/index.php',
              success: function(data) 
              {
                //console.log("SUCCESS IMAGENES = ", data);
                $("#fotos_th_emb").html(data);

              }, error: function(data) 
              {
                  console.log("ERROR Fotos Datos = ", data);
              }
          });
    }

    function ver_fotos(_codigo)
    {
        //$("#folio_foto_th_emb").val(_codigo);
        $("#n_folio_emb").text(_codigo);
        $modal0 = $("#fotos_oc_th_emb");
        $modal0.modal('show');
        ImprimirFotos_th(_codigo);
    }

    function GenerarTxt(folio)
    {
    console.log("GenerarTxt = ", folio);

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                folio: folio,
                action: 'aviso_despacho'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url:'/api/embarques/lista/index.php',
            success: function(data) {
                console.log("SUCCESS aviso_despacho");
                console.log(data);
                //window.location.href = '../../../uploads/archivo.txt';
                //window.open('../../../uploads/archivo.txt', 'download');
/*
                document.getElementById("btn_dw")
                      .addEventListener("click", function () {
                      }, false);
*/
                //var text = document.getElementById("text_dw").value;
                var filename = "AvisoDespacho_"+folio+".txt";
                download(filename, data.text);
            },
            error: function(data){
                console.log("ERROR aviso_despacho");
                console.log(data);
            }
        });
    }

    function GenerarTxtDespacho(folio)
    {
    console.log("GenerarTxtDespacho = ", folio);

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                folio: folio,
                action: 'archivo_despacho'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url:'/api/embarques/lista/index.php',
            success: function(data) {
                console.log("SUCCESS aviso_despacho");
                console.log(data);
                //window.location.href = '../../../uploads/archivo.txt';
                //window.open('../../../uploads/archivo.txt', 'download');
/*
                document.getElementById("btn_dw")
                      .addEventListener("click", function () {
                      }, false);
*/
                //var text = document.getElementById("text_dw").value;
                var filename = "ArchivoDespacho_"+folio+".txt";
                download(filename, data.text);
            },
            error: function(data){
                console.log("ERROR aviso_despacho");
                console.log(data);
            }
        });
    }

    function verGps(id)
    {
    console.log("verGps = ", id);
      $("#n_inventario_gps").text(id);
      $("#detalleGps").modal('show');
      
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                folio: id,
                action: 'get_gps'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url:'/api/embarques/lista/index.php',
            success: function(data) {
                console.log("SUCCESS GPS", data);
                //console.log(data);
                var datos = data.rows;
                console.log("DATOS = ", datos);
                if(datos == undefined)
                {
                    swal("Error", "Faltan las coordenadas de Destinatario", "error");
                    $("#detalleGps").modal('hide');
                    return;
                }
                markers = [];
                info_map = [];
                datos.forEach(function(destinatario, i)
                {
                    console.log(i, destinatario[0], destinatario[1], destinatario[2], destinatario[3], destinatario[4], destinatario[5], destinatario[6], destinatario[7]);
                    //'["' . $row['direccion'] . '", ' . $row['lat'] . ', ' . $row['lng'] . '],'
                    //markers += '[ "", '+destinatario[2]+','+destinatario[3]+'],';
                    var tooltip = "Entrega "+id+"\n"+
                                  destinatario[0]+" | "+destinatario[1]+"\n"+
                                  "Pedido "+destinatario[4]+"\n"+
                                  "Fecha Entrega "+destinatario[6]+"\n"+
                                  "Horario Entrega "+destinatario[7]+"\n";

                    if(destinatario[4] == 'Almacen-Data')
                    tooltip = "Cedis "+destinatario[0]+"\n"+
                               destinatario[1];
                    markers[i] = [tooltip, parseFloat(destinatario[2]), parseFloat(destinatario[3])];
                    info_map[i] = [destinatario[4], destinatario[5]];
                    lat = destinatario[2];
                    lon = destinatario[3];
                    console.log(markers);
                });
                //google.maps.event.addDomListener(window, 'load', initMap);
                initMap();

            },
            error: function(data){
                console.log("ERROR");
                console.log(data);
            }
        });
    }


    function printExcelASN(consecutivo){
        var form = document.createElement("form"),
            input1 = document.createElement("input"),
            input2 = document.createElement("input"),
            input3 = document.createElement("input");
            //input4 = document.createElement("input");
        input1.setAttribute('name', 'nofooternoheader');
        input1.setAttribute('value', 'true');
        input2.setAttribute('name', 'id');
        input2.setAttribute('value', consecutivo);
        input3.setAttribute('name', 'action');
        input3.setAttribute('value', 'exportExcelinvfidet');
        //input4.setAttribute('name', 'consolidado');
        //input4.setAttribute('value', consolidado);

        form.setAttribute('action', '/api/reportes/lista/asn.php');
        form.setAttribute('method', 'post');
        form.setAttribute('target', '_blank');
        form.appendChild(input1);
        form.appendChild(input2);
        form.appendChild(input3);
        document.body.appendChild(form);
        form.submit();
    }

    function printExcelDespacho(consecutivo){
        var form = document.createElement("form"),
            input1 = document.createElement("input"),
            input2 = document.createElement("input"),
            input3 = document.createElement("input");
            //input4 = document.createElement("input");
        input1.setAttribute('name', 'nofooternoheader');
        input1.setAttribute('value', 'true');
        input2.setAttribute('name', 'id');
        input2.setAttribute('value', consecutivo);
        input3.setAttribute('name', 'action');
        input3.setAttribute('value', 'exportExcelinvfidetDespacho');
        //input4.setAttribute('name', 'consolidado');
        //input4.setAttribute('value', consolidado);

        form.setAttribute('action', '/api/reportes/lista/asn.php');
        form.setAttribute('method', 'post');
        form.setAttribute('target', '_blank');
        form.appendChild(input1);
        form.appendChild(input2);
        form.appendChild(input3);
        document.body.appendChild(form);
        form.submit();
    }

    function printExcel(id, folios)
    {
/*
        var form = document.createElement("form"),
            input1 = document.createElement("input"),
            input2 = document.createElement("input"),
            input3 = document.createElement("input");
        input1.setAttribute('name', 'nofooternoheader');
        input1.setAttribute('value', 'true');
        input2.setAttribute('name', 'id');
        input2.setAttribute('value', id);
        input3.setAttribute('name', 'action');
        input3.setAttribute('value', 'exportExcelEmbarque');
        form.setAttribute('action', '/api/embarques/lista/index.php');
        form.setAttribute('method', 'post');
        form.setAttribute('target', '_blank');
        form.setAttribute('style', 'display:none');
        form.appendChild(input1);
        form.appendChild(input2);
        form.appendChild(input3);
        document.body.appendChild(form);
        form.submit();
*/
    $.ajax({
        // Your server script to process the upload
        url: '/embarques/exportar',
        type: 'GET',
        data: 
        {
            id: id,
            folio: folios
        },
        //cache: false,
          success: function(data) {
            console.log("success data = ", data);
            //console.log("success id = ", data.id);
          }, error: function(data)
          {
                console.log("error = ", data);
          }
      });

    }
  
    function printPDF(id, folio_pedido)
    {
      
      console.log(id);
      console.log("folio_pedido = ", folio_pedido);
      
              var folio = id,
                  cia = <?php echo $_SESSION['cve_cia'] ?>,
                  form = document.createElement('form'),
                  input_nofooter = document.createElement('input'),
                  input_folio = document.createElement('input'),
                  input_folio_pedido = document.createElement('input'),
                  input_cia = document.createElement('input');
      
              form.setAttribute('method', 'post');
              form.setAttribute('action', '/embarque/pdf/exportar');
              form.setAttribute('target', '_blank');
              form.setAttribute('style', 'display:none');

              console.log("cia = ", cia);
              input_nofooter.setAttribute('name', 'nofooternoheader');
              input_nofooter.setAttribute('value', '1');

              input_folio.setAttribute('name', 'folio');
              input_folio.setAttribute('value', folio);
              input_folio_pedido.setAttribute('name', 'folio_pedido');
              input_folio_pedido.setAttribute('value', folio_pedido);
              input_cia.setAttribute('name', 'cia');
              input_cia.setAttribute('value', <?php echo $_SESSION['cve_cia'] ?>);
              

              form.appendChild(input_nofooter);
              form.appendChild(input_folio);
              form.appendChild(input_folio_pedido);
              form.appendChild(input_cia);

              document.getElementsByTagName('body')[0].appendChild(form);
              form.submit();
    }


    function printPDFEmpaque(id, folio_pedidos)
    {
      
      console.log("id = ", id, " folio_pedidos = ", folio_pedidos);
      
              var folio = id,
                  cia = <?php echo $_SESSION['cve_cia'] ?>,
                  form = document.createElement('form'),
                  input_nofooter = document.createElement('input'),
                  input_folio = document.createElement('input'),
                  input_pedidos = document.createElement('input'),
                  input_cia = document.createElement('input');
      console.log("id = ", id, " cia = ", cia, " folio_pedidos = ", folio_pedidos);
              form.setAttribute('method', 'post');
              form.setAttribute('action', '/empaque/pdf/exportar');
              form.setAttribute('target', '_blank');
              form.setAttribute('style', 'display:none');

              input_nofooter.setAttribute('name', 'nofooternoheader');
              input_nofooter.setAttribute('value', '1');

              input_folio.setAttribute('name', 'folio');
              input_folio.setAttribute('value', folio);
              input_pedidos.setAttribute('name', 'folio_pedidos');
              input_pedidos.setAttribute('value', folio_pedidos);
              input_cia.setAttribute('name', 'cia');
              input_cia.setAttribute('value', <?php echo $_SESSION['cve_cia'] ?>);
              

              form.appendChild(input_nofooter);
              form.appendChild(input_folio);
              form.appendChild(input_pedidos);
              form.appendChild(input_cia);

              document.getElementsByTagName('body')[0].appendChild(form);
              form.submit();
    }

    function exportarPDFLista(id, folio_pedidos)
    {
      
      console.log("id = ", id, " folio_pedidos = ", folio_pedidos);
      
              var folio = id,
                  cia = <?php echo $_SESSION['cve_cia'] ?>,
                  form = document.createElement('form'),
                  input_nofooter = document.createElement('input'),
                  input_folio = document.createElement('input'),
                  input_pedidos = document.createElement('input'),
                  input_cia = document.createElement('input');
      console.log("id = ", id, " cia = ", cia, " folio_pedidos = ", folio_pedidos);
              form.setAttribute('method', 'post');
              form.setAttribute('action', '/embarque/pdf/exportarprecios');
              form.setAttribute('target', '_blank');
              form.setAttribute('style', 'display:none');

              input_nofooter.setAttribute('name', 'nofooternoheader');
              input_nofooter.setAttribute('value', '1');

              input_folio.setAttribute('name', 'folio');
              input_folio.setAttribute('value', folio);
              input_pedidos.setAttribute('name', 'folio_pedidos');
              input_pedidos.setAttribute('value', folio_pedidos);
              input_cia.setAttribute('name', 'cia');
              input_cia.setAttribute('value', <?php echo $_SESSION['cve_cia'] ?>);
              

              form.appendChild(input_nofooter);
              form.appendChild(input_folio);
              form.appendChild(input_pedidos);
              form.appendChild(input_cia);

              document.getElementsByTagName('body')[0].appendChild(form);
              form.submit();
    }


    function printPDFAuditoria(id, folio_pedidos)
    {
      
      console.log("id = ", id, " folio_pedidos = ", folio_pedidos);
      
              var folio = id,
                  cia = <?php echo $_SESSION['cve_cia'] ?>,
                  form = document.createElement('form'),
                  input_nofooter = document.createElement('input'),
                  input_folio = document.createElement('input'),
                  input_pedidos = document.createElement('input'),
                  input_cia = document.createElement('input');
      console.log("id = ", id, " cia = ", cia, " folio_pedidos = ", folio_pedidos);
              form.setAttribute('method', 'post');
              form.setAttribute('action', '/auditoria/pdf/exportar');
              form.setAttribute('target', '_blank');
              form.setAttribute('style', 'display:none');

              input_nofooter.setAttribute('name', 'nofooternoheader');
              input_nofooter.setAttribute('value', '1');

              input_folio.setAttribute('name', 'folio');
              input_folio.setAttribute('value', folio);
              input_pedidos.setAttribute('name', 'folio_pedidos');
              input_pedidos.setAttribute('value', folio_pedidos);
              input_cia.setAttribute('name', 'cia');
              input_cia.setAttribute('value', <?php echo $_SESSION['cve_cia'] ?>);
              

              form.appendChild(input_nofooter);
              form.appendChild(input_folio);
              form.appendChild(input_pedidos);
              form.appendChild(input_cia);

              document.getElementsByTagName('body')[0].appendChild(form);
              form.submit();
    }

    function printPDFDiscrepancias(id, folio_pedidos)
    {
      
      console.log("id = ", id, " folio_pedidos = ", folio_pedidos);
      
              var folio = id,
                  cia = <?php echo $_SESSION['cve_cia'] ?>,
                  form = document.createElement('form'),
                  input_nofooter = document.createElement('input'),
                  input_folio = document.createElement('input'),
                  input_pedidos = document.createElement('input'),
                  input_cia = document.createElement('input');
      console.log("id = ", id, " cia = ", cia, " folio_pedidos = ", folio_pedidos);
              form.setAttribute('method', 'post');
              form.setAttribute('action', '/discrepancias/pdf/exportar');
              form.setAttribute('target', '_blank');
              form.setAttribute('style', 'display:none');

              input_nofooter.setAttribute('name', 'nofooternoheader');
              input_nofooter.setAttribute('value', '1');

              input_folio.setAttribute('name', 'folio');
              input_folio.setAttribute('value', folio);
              input_pedidos.setAttribute('name', 'folio_pedidos');
              input_pedidos.setAttribute('value', folio_pedidos);
              input_cia.setAttribute('name', 'cia');
              input_cia.setAttribute('value', <?php echo $_SESSION['cve_cia'] ?>);
              

              form.appendChild(input_nofooter);
              form.appendChild(input_folio);
              form.appendChild(input_pedidos);
              form.appendChild(input_cia);

              document.getElementsByTagName('body')[0].appendChild(form);
              form.submit();
    }

    function printPDFEmpaqueCajas(id, folio_pedidos)
    {
      
      console.log("id = ", id, " folio_pedidos = ", folio_pedidos);
      
              var folio = id,
                  cia = <?php echo $_SESSION['cve_cia'] ?>,
                  form = document.createElement('form'),
                  input_nofooter = document.createElement('input'),
                  input_folio = document.createElement('input'),
                  input_pedidos = document.createElement('input'),
                  input_cia = document.createElement('input');
      console.log("id = ", id, " cia = ", cia, " folio_pedidos = ", folio_pedidos);
              form.setAttribute('method', 'post');
              form.setAttribute('action', '/empaque/pdf/exportarCajas');
              form.setAttribute('target', '_blank');
              form.setAttribute('style', 'display:none');

              input_nofooter.setAttribute('name', 'nofooternoheader');
              input_nofooter.setAttribute('value', '1');

              input_folio.setAttribute('name', 'folio');
              input_folio.setAttribute('value', folio);
              input_pedidos.setAttribute('name', 'folio_pedidos');
              input_pedidos.setAttribute('value', folio_pedidos);
              input_cia.setAttribute('name', 'cia');
              input_cia.setAttribute('value', <?php echo $_SESSION['cve_cia'] ?>);
              

              form.appendChild(input_nofooter);
              form.appendChild(input_folio);
              form.appendChild(input_pedidos);
              form.appendChild(input_cia);

              document.getElementsByTagName('body')[0].appendChild(form);
              form.submit();
    }



    function DescargarDocumentos(folio)
    {
        console.log("ImprimirFotos_th_documentos", folio);
          $.ajax({
              type: "POST",
              //dataType: "json",
              data: 
              {
                  folio: folio,
                  action : "DescargarDocumentos"
              },/*
              beforeSend: function(x)
              {
                  if(x && x.overrideMimeType) 
                  { 
                    x.overrideMimeType("application/json;charset=UTF-8"); 
                  }
              },*/
              url: '/api/embarques/update/index.php',
              success: function(data) 
              {

                $("#fotos_th").html(data);

              }, error: function(data) 
              {
                  console.log("ERROR Fotos Datos = ", data);
              }
          });
    }

    function descargar_documentos(folio)
    {
        //$("#folio_foto_th").val(cve_articulo);
        $("#n_folio").text(folio);
        $("#form-subir-fotos-th").hide();
        $modal0 = $("#fotos_oc_th");
        $modal0.modal('show');
        DescargarDocumentos(folio);
    }

    function ver_documentos(folio)
    {
        $("#codigo").val(folio);
        $("#form-subir-fotos-th").show();
        //$("#folio_foto_th").val(folio);
        $("#n_folio").text(folio);
        $modal0 = $("#fotos_oc_th");
        $modal0.modal('show');
        ImprimirFotos_th_documentos(folio);
    }

  ///////////////////////////////////////////////SUBIR FOTOS TH///////////////////////////////////////////////
    $('#btn-fotos-th').on('click', function() {

        $("#folio_documento").val($("#codigo").val());

        $('#btn-fotos-th').prop('disable', true);
        var bar = $('.progress-bar');
        var percent = $('.percent');
        //var status = $('#status');
        var formData = new FormData();
        formData.append("clave", "valor");

        if(!$("#file_th").val())
        {
            swal("Advertencia!", "Debe enviar un archivo", "warning");
            return false;
        }

        $.ajax({
            // Your server script to process the upload
            url: '/embarques/archivosdocumentos',
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
                        ImprimirFotos_th_documentos($("#folio_documento").val());
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

    function ImprimirFotos_th_documentos(folio)
    {
        console.log("ImprimirFotos_th_documentos", folio);
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
              url: '/api/embarques/update/index.php',
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
              url: '/api/embarques/update/index.php',
              success: function(data) 
              {

                ImprimirFotos_th_documentos($("#codigo").val());

              }, error: function(data) 
              {
                  console.log("ERROR Fotos Datos = ", data);
              }
          });
    }




        $('#data_1').datetimepicker({
            locale: 'es',
            format: 'DD-MM-YYYY',
            useCurrent: false
        });

        $('#data_2').datetimepicker({
            locale: 'es',
            format: 'DD-MM-YYYY',
            useCurrent: false
        });

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