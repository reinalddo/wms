<?php
include $_SERVER['DOCUMENT_ROOT']."/app/host.php";

$rutas = new \Ruta\Ruta();
$vendedores = new \Usuarios\Usuarios();
$almacenes = new \AlmacenP\AlmacenP();

//$confSql = \db()->prepare("SELECT IFNULL(Valor, '') AS api_key_google_maps FROM t_configuraciongeneral WHERE cve_conf = 'api_key_google_maps' LIMIT 1");
//$confSql->execute();
//$api_key_google_maps = $confSql->fetch()['api_key_google_maps'];

$api_key_google_maps = 'AIzaSyC5xF7JtKzw9cTRRXcDAqTThbYnMCiYOVM';

if(!isNikken()){
    //$codDaneSql = \db()->prepare("SELECT *  FROM c_dane ");
    //$codDaneSql = \db()->prepare("SELECT DISTINCT c_dane.cod_municipio, c_dane.des_municipio, c_dane.departamento FROM c_dane, c_destinatarios WHERE c_dane.cod_municipio = c_destinatarios.postal");
    //SELECT id_dane, IF(CHAR_LENGTH(cod_municipio) = 4, CONCAT('0', cod_municipio), cod_municipio) AS cod_municipio, departamento, des_municipio FROM c_dane
    //$codDaneSql->execute();
    //$codigos_dane = $codDaneSql->fetchAll(PDO::FETCH_ASSOC);

    //$agentesSql = \db()->prepare("SELECT *  FROM t_vendedores ");
    //$agentesSql = $agentesSql->execute();
    //$agentes_operadores = $agentesSql->fetchAll(PDO::FETCH_ASSOC);

}
?>
    <style type="text/css">
        .ui-jqgrid,
        .ui-jqgrid-view,
        .ui-jqgrid-hdiv,
        .ui-jqgrid-bdiv,
        .ui-jqgrid,
        .ui-jqgrid-htable,
        #grid-table,
        #grid-table2,
        #grid-table3,
        #grid-table4,
        #grid-pager,
        #grid-pager2,
        #grid-pager3,
        #grid-pager4 {
            width: 100% !important;
            max-width: 100% !important;
        }
        .modal-open .modal {
            overflow-x: scroll !important;
        }
        #agentes1_chosen {display: none;}
    </style>


    <link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
    <link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
    <link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">


    <!-- Mainly scripts -->
    <script src="/js/jquery-2.1.1.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <!-- Peity -->
    <script src="/js/plugins/peity/jquery.peity.min.js"></script>

    <!-- Custom and plugin javascript -->
    <script src="/js/inspinia.js"></script>
    <script src="/js/plugins/pace/pace.min.js"></script>

    <script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>

    <script src="/js/plugins/ladda/spin.min.js"></script>
    <script src="/js/plugins/ladda/ladda.min.js"></script>
    <script src="/js/plugins/ladda/ladda.jquery.min.js"></script>
    <script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
    <script src="/js/plugins/chosen/chosen.jquery.js"></script>

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo $api_key_google_maps; ?>"></script>



<!--<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCc_ej3xfMCxtyW7oYhgUIw8Rk_NK_ASR0"></script>-->
<script type="text/javascript">
var markers = [], lat = "", lon = "", info_map = [], credito_contado = [];
/*
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
          // Creamos la ventana de información con los marcadores 
          var mostrarMarcadores = new google.maps.InfoWindow(),
              marcadores, i;

          // Colocamos los marcadores en el Mapa de Google 
          var color_point = "";
          for (i = 0; i < marcadores.length; i++) {
              var position = new google.maps.LatLng(marcadores[i][1], marcadores[i][2]);
              bounds.extend(position);

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
              google.maps.event.addListener(marker, 'click', (function(marker, i) {
                  return function() {
                      //mostrarMarcadores.setContent(ventanaInfo[i][0]);
                      mostrarMarcadores.open(map, marker);
                  }
              })(marker, i));

              // Centramos el Mapa de Google para que todos los marcadores se puedan ver 
              map.fitBounds(bounds);
          }

          // Aplicamos el evento 'bounds_changed' que detecta cambios en la ventana del Mapa de Google, también le configramos un zoom de 14 
          var boundsListener = google.maps.event.addListener((map), 'bounds_changed', function(event) {
              this.setZoom(14);
              google.maps.event.removeListener(boundsListener);
          });

      }
*/
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

        //console.log("POINT PRUEBA 1", mostrarMarcadores);

          // Colocamos los marcadores en el Mapa de Google 
          var color_point = "";
          for (i = 0; i < marcadores.length; i++) {
              var position = new google.maps.LatLng(marcadores[i][1], marcadores[i][2]);
              bounds.extend(position);

              if(i == 0) center_almacen = position;

              color_point = "#ff3300";
              if(i == 0) color_point = "#0000ff";
              else if(credito_contado[i] == 1) color_point = "#009900";

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
              google.maps.event.addListener(marker, 'click', (function(marker, i) {
                  return function() {
                      //mostrarMarcadores.setContent(ventanaInfo[i][0]);
                      mostrarMarcadores.open(map, marker);
                  }
              })(marker, i));

              // Centramos el Mapa de Google para que todos los marcadores se puedan ver 

              //map.fitBounds(bounds);
              //map.setCenter(bounds);
              //console.log("POINT PRUEBA 2", i);
          }
          map.setCenter(center_almacen);
        //console.log("POINT PRUEBA 2");
          // Aplicamos el evento 'bounds_changed' que detecta cambios en la ventana del Mapa de Google, también le configramos un zoom de 14 

          var boundsListener = google.maps.event.addListener((map), 'bounds_changed', function(event) {
              this.setZoom(14);
              google.maps.event.removeListener(boundsListener);
          });

          //console.log("POINT PRUEBA 3", boundsListener);

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

<style>

    .table{
        margin-top: 2px;
        border: 1px solid #dddddd;
    }

    .table > thead > tr > th, 
    .table > tbody > tr > th,
    .table > tfoot > tr > th, 
    .table > thead > tr > td, 
    .table > tbody > tr > td, 
    .table > tfoot > tr > td {
        border: 1px solid #dddddd;
        line-height: 1.42857;
        padding: 4px 8px;
        vertical-align: top;
    }
    .table > thead > tr {
        background-color: #f7f7f7;
    }
    .table > thead > tr > th {
        font-size: 12px;
    }
    .table > tfoot > tr {
        background-color: #f7f7f7;
    }
    .table > tfoot > tr > th {
        font-weight: 400;
        font-size: 11px;
        font-family: Verdana,Arial,sans-serif;
    }
    .table > tfoot .sep{
        width: 1px;
        height: 15px;
        background-color: #dddddd;
        display: inline-block;
        margin: 0px 5px;
        position: relative;
        top:3px;
    }
    .table .page{
        width: 50px;
        height: 20px;
        border: 1px solid #dddddd;
    }
    .table a {
        color: #000;
    }

</style>



    <!-- Jquery Validate -->
    <script src="/js/plugins/validate/jquery.validate.min.js"></script>

    <!-- Mainly scripts -->
    <input type="hidden" id="clave_almacen" value="<?php echo $_SESSION['cve_almacen']; ?>">

    <div class="wrapper wrapper-content  animated fadeInRight" id="FORM" style="display: none">

        <div class="row">
            <div class="col-md-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <div class="row">
                            <div class="col-md-4" id="_title">
                                <h3>Agregar Destinatario</h3>
                            </div>
                        </div>
                    </div>
                    <form id="myform">
                        <input type="hidden" id="hiddenAction" name="hiddenAction">
                        <div class="ibox-content">
                            <div class="row">

                                <div class="col-md-6">
                                    <div class="form-group"><label>Consecutivo *</label> <input id="txtClaveDest" type="text" placeholder="Consecutivo" class="form-control" maxlength="20" readonly></div>
                                    <div class="form-group"><label>Clave de Destinatario*</label> <input id="claveDeDestinatario" type="text" placeholder="Clave" class="form-control" maxlength="20" required="true"></div>
                                    <div class="form-group">
                                        <label>Cliente *</label>
                                        <select class="form-control chosen-select" id="cboCliente" required="true">
                                        <option value="">Seleccione un Cliente</option>
                                        @foreach( $clientes as $value )
                                            <option value="{{ $value->Cve_Clte }}">{{ $value->Cve_Clte }} - {{ $value->RazonSocial }}</option>
                                        @endforeach
                                    </select>
                                    </div>
                                    <div class="form-group"><label>Razón Social *</label> <input id="txtRazonSocial" type="text" placeholder="Razón Social" style="text-transform:uppercase;" class="form-control" required="true"></div>
                                    <div class="form-group"><label>Dirección *</label> <input id="txtCalleNumero" type="text" placeholder="Calle y Numero" style="text-transform:uppercase;" class="form-control" required="true"></div>
                                    <div class="form-group"><label>Colonia </label> <input id="txtColonia" style="text-transform:uppercase;" type="text" placeholder="Colonia" class="form-control"></div>
                                </div>
                                <div class="col-md-6" style="border-left: 1px solid #e7eaec;">
                                    <div class="form-group"><label>CP | CD *</label> 
                                        <!--
                                        @if(isset($codigos_dane) && !empty($codigos_dane))

                                            <select id="txtCod" class="form-control" required="true" style="width: 100%">
                                                <option value="">Seleccione Código</option>
                                            </select>
                                        -->
                                            <input type="text" name="txtCod" id="txtCod" class="form-control" style="width: 100%">
                                            <div id="mensaje_dane" class="text-danger" style="font-size: 12px;height: 20px;"></div>
                                        <!--
                                        @else
                                            <input type="text" name="txtCod" id="txtCod" class="form-control" required="true" style="width: 100%">
                                        @endif
                                        -->
                                    </div>
                                    <div class="form-group"><label>Alcaldía | Municipio</label> <input id="txtMunicipio" type="text" placeholder="Municipio" class="form-control"></div>
                                    <div class="form-group"><label>Ciudad | Departamento</label> <input id="txtDepart" type="text" placeholder="Departamento" class="form-control"></div>
                                    <div class="form-group"><label>Contacto</label> <input id="txtContacto" type="text" placeholder="Contacto" class="form-control"></div>
                                    <div class="form-group"><label>Teléfono</label> <input id="txtTelefono" maxlength="14" oninput="this.value = this.value.replace(/[^0-9]/g, '');" type="text" maxlength="15" placeholder="Teléfono" class="form-control"></div>

                                    <div class="form-group"><label>Email</label> <input id="txtEmail" type="email" placeholder="Email" class="form-control"></div>
                                    <div class="form-group"><label>Latitud</label> <input id="txtLatitud" type="number" placeholder="Latitud" class="form-control"></div>
                                    <div class="form-group"><label>Longitud</label> <input id="txtLongitud" type="number" placeholder="Longitud" class="form-control"></div>

                                    <div class="pull-right">
                                        <a href="#" onclick="cancelar()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                                        <button type="submit" class="btn btn-primary" id="btnSave">Guardar</button>
                                    </div>

                                </div>
                              
                                
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="SecuenciaMayor" value="">
    <input type="hidden" id="dir_principal" value="0">
    <div class="modal fade" id="modal_asignacion_ruta" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Asignar Ruta</h4>
                    </div>
                    <div class="modal-body">
                      <div class="col-md-6">
                        <div class="form-group">
<!--EDG -->
                            <label>Asignar Ruta</label>
                            <select class="form-control" id="ruta_asignada">
                              <option value="">Seleccione</option>
                              <?php foreach( $rutas->getAll($_SESSION['cve_almacen']) AS $p ): ?>
                              <option value="<?php echo $p->cve_ruta; ?>"><?php echo "(".$p->cve_ruta.") - ".$p->descripcion; ?></option>
                              <?php endforeach; ?>
                            </select>
                        </div>
                      </div>
                        <div class="ibox-content">
                          <table class="table table-bordered" id="tabla_usuarios_select">
                            <thead>
                              <tr>
                                <th>Clave Destinatario</th>
                                <th>Destinatario</th>
                                <th>Clave Cliente</th>
                                <th>Cliente</th>
                                <!--<th>Acciones</th>-->
                              </tr>
                            </thead>
                            <tbody>
                            </tbody>
                          </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-m btn-primary" id="guardar_ruta">Guardar Ruta</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="modal fade" id="detalleGpsRutas" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 1200px!important;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Planeación de Rutas | Mostrando (<span id="show_points">0</span>) Clientes</h4>
                </div>
                <div class="modal-body">
                    <div id="mapa" style="overflow-x: scroll;">
                    </div>
                </div>
                <div class="modal-footer">

<span style="float:left;position: relative;">
<br>
    <div style="background-color:#0000ff;width:20px; height: 20px;display: inline-block;margin-left: 15px;"></div>
    <span style="margin-top: 3px;position: absolute;display: inline-block;width: 250px;text-align: left;margin-left: 10px;">Almacén</span>    
<br>
    <div style="background-color:#ff3300;width:20px; height: 20px;display: inline-block;margin-left: 15px;"></div>
    <span style="margin-top: 3px;position: absolute;display: inline-block;width: 250px;text-align: left;margin-left: 10px;">Clientes Contado</span>    
<br>
    <div style="background-color:#009900;width:20px; height: 20px;display: inline-block;margin-left: 15px;"></div>
    <span style="margin-top: 3px;position: absolute;display: inline-block;width: 250px;text-align: left;margin-left: 10px;">Clientes Crédito</span>    
</span>

                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php 
/*
?>
<div class="modal fade" id="detalleGps" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 1200px!important;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Destinatario <span id="n_inventario_gps">0</span></h4>
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
<?php 
*/
?>
    <div class="wrapper wrapper-content  animated fadeInRight" id="list">

        <h3>Planeación de Rutas | Destinatarios</h3>

        <div class="row">
            <div class="col-md-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <div class="row">

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="almacenes">Almacen:</label>
                                    <select class="form-control" id="almacenes" name="almacenes">
                                    <option value="">Seleccione el Almacen</option>
                                    <?php 
                                    foreach( $almacenes->getAll() as $value )
                                    {
                                    ?>
                                        <option value="<?php echo $value->id; ?>"> <?php echo $value->clave." - ".$value->nombre; ?></option>
                                    <?php 
                                    }
                                    ?>
                                </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Rutas:</label>
                                    <select class="form-control chosen-select" id="rutas">
                                      <option value="">Seleccione</option>
                                      <?php 
                                      /*
                                      foreach( $rutas->getAll() AS $p ): ?>
                                      <option value="<?php echo $p->cve_ruta; ?>"><?php echo "(".$p->cve_ruta.") - ".$p->descripcion; ?></option>
                                      <?php endforeach; 
                                      */
                                      ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="agentes">Agente | Operador:</label> 
                                    <?php 
                                    
                                    ?>
                                    <select class="form-control chosen-select" style="display: none;" id="agentes1" name="agentes1">
                                        <option value="">Seleccione Agente | Operador</option>
                                        <?php 
                                        foreach( $vendedores->getAllVendedorPlaneacion($_SESSION['id_almacen']) AS $ag ): 
                                        ?>
                                        <option value="<?php echo $ag->cve_Vendedor; ?>"><?php echo "( ".$ag->cve_Vendedor." ) - ".$ag->Nombre; ?></option>
                                        <?php 
                                            endforeach; 
                                        ?>

                                    </select>
                                    <?php 
                                    
                                    ?>
                                    <select class="form-control chosen-select" id="agentes" name="agentes">
                                        <option value="">Seleccione Agente | Operador</option>
                                        <?php 
                                        foreach( $vendedores->getAllVendedorPlaneacion($_SESSION['id_almacen']) AS $ag ): 
                                        ?>
                                        <option value="<?php echo $ag->cve_Vendedor; ?>"><?php echo "( ".$ag->cve_Vendedor." ) - ".$ag->Nombre; ?></option>
                                        <?php 
                                            endforeach; 
                                        ?>

                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="dias">Día:</label> 
                                    <select class="form-control" id="dias" name="dias">
                                        <option value="''">Seleccione el Día</option>
                                        <option value="IFNULL(RelDayCli.Lu, 20000)">Lunes</option>
                                        <option value="IFNULL(RelDayCli.Ma, 20000)">Martes</option>
                                        <option value="IFNULL(RelDayCli.Mi, 20000)">Miércoles</option>
                                        <option value="IFNULL(RelDayCli.Ju, 20000)">Jueves</option>
                                        <option value="IFNULL(RelDayCli.Vi, 20000)">Viernes</option>
                                        <option value="IFNULL(RelDayCli.Sa, 20000)">Sábado</option>
                                        <option value="IFNULL(RelDayCli.Do, 20000)">Domingo</option>
                                    </select>
                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-3">
                                <div class="form-group">
                                    <!--@if(isset($codigos_dane) && !empty($codigos_dane))-->
                                        <select id="codigo" class="form-control chosen-select" required="true" style="width: 100%">
                                            <option value="">Seleccione Código Postal</option>
                                            <?php 
                                            /*
                                            if(isset($codigos_dane) && !empty($codigos_dane))
                                            {
                                            ?>
                                                <?php 
                                                foreach( $codigos_dane AS $p ): 
                                                    $cod = $p["cod_municipio"];
                                                    if(strlen($cod) == 4)
                                                        $cod = "0".$cod;
                                                ?>
                                                <option value="<?php echo $p["cod_municipio"]; ?>"><?php echo $cod." - ".$p["des_municipio"]." | ".$p["departamento"]; ?></option>
                                                <?php endforeach; ?>
                                            <?php 
                                            }
                                            */
                                            ?>
                                        </select>
                                    <!--@else
                                        <input type="text" name="codigo" id="codigo" class="form-control" required="true" style="width: 100%">
                                    @endif-->
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                    <div class="input-group-btn">

                                        <button onclick="ReloadGrid()" type="button" class="btn btn-primary" id="buscarP">
                                        <span class="fa fa-search"></span> Buscar
                                    </button>

                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="input-group">
                                    <div class="checkbox" id="check_entregas">
                                      <label for="btn-entregas">
                                        <input type="checkbox" name="entregas" id="btn-entregas">Entregas
                                      </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <a href="#" class="permiso_registrar" onclick="agregar()"><button class="btn btn-primary" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>

                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                                <!--<a href="/api/v2/clientes/destinatarios/exportar" target="_blank" class="btn btn-primary pull-right" style="margin-left:15px;"><span class="fa fa-upload"></span> Exportar</a>-->

                                <a href="#" id="generarExcelDestinatarios" class="btn btn-primary" style="margin: 10px;display: none;">
                                    <span class="fa fa-file-excel-o"></span> Exportar Destinatarios
                                </a>
                                <button class="btn btn-primary" id="generarExcelDestinatariosBTN" disabled style="margin-left:15px;"><span class="fa fa-file-excel-o"></span> Exportar Destinatarios</button>

                                <button class="btn btn-primary pull-right permiso_registrar" style="margin-left:15px;" data-toggle="modal" data-target="#importar"><span class="fa fa-download"></span> Importar</button>
                                <br><br>

                                <button class="btn btn-primary pull-right permiso_registrar" style="margin-left:15px;" data-toggle="modal" data-target="#importarpv"><span class="fa fa-download"></span> Importar Planeación de Visitas</button>
                                <button class="btn btn-primary pull-right" id="btn-rutasgps" onclick="verGpsRutas()" disabled style="margin-left:15px;"><span class="fa fa-map-marker"></span> Visualizar Rutas</button>
                                <br><br><br>
                                <button id="reiniciar_secuencia" class="btn btn-primary pull-right permiso_registrar" disabled style="margin-left:15px;"><i class="fa fa-refresh"></i> Reiniciar Secuencia</button>

                            </div>
                        </div>
                    </div>
                    
                    <div class="ibox-content">

                <div class="row" style="margin-top:15px">
                    <div class="col-md-4">
                        <label>Total de Clientes</label>
                        <input id="total_clientes" type="text" value="0" class="form-control" style="text-align: center" disabled><br>
                    </div>
                    <div class="col-md-4">
                        <label>Clientes por Ruta</label>
                        <input id="clientes_por_ruta" type="text" value="0" class="form-control" style="text-align: center" disabled><br>
                    </div>
                    <div class="col-md-4">
                        <label>Clientes por día</label>
                        <input id="clientes_por_dia" type="text" value="0" class="form-control" style="text-align: center" disabled><br>
                    </div>
                </div>
                        <div class="row">
                          <div class="col-lg-2">
                            <div class="checkbox" id="chb_asignar">
                              <label for="btn-asignarTodo">
                                <input type="checkbox" name="asignarTodo" id="btn-asignarTodo">Asignar Todo
                              </label>
                            </div>
                          </div>
                        </div>

                        <div class="table-responsive">
                            <table id="dt-detalles" class="table" style="table-layout: auto;width: 100%;">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 80px !important; min-width: 80px !important;">Acciones</th>
                                        <th scope="col" style="width: 80px !important; min-width: 80px !important;">Asignar</th><!--EDG-->
                                        <th scope="col" style="width: 80px !important; min-width: 80px !important;">Secuencia</th><!--EDG-->
                                        <th scope="col" style="text-align: center;width: 80px !important; min-width: 100px !important;">Ruta</th>
                                        <th scope="col" style="text-align: center;width: 180px !important; min-width: 180px !important; display: none;">Agente | Operador</th>
                                        <th scope="col" style="width: 100px !important; min-width: 100px !important;">Clave de Cliente</th>
                                        <th scope="col" style="width: 270px !important; min-width: 270px !important;">Razón Comercial</th> 
                                        <th scope="col" style="width: 270px !important; min-width: 270px !important;">Cliente</th> 
                                        <th scope="col" style="width: 100px !important; min-width: 100px !important;">Clave de Destinatario</th>
                                        <th scope="col" style="width: 180px !important; min-width: 180px !important;">Destinatario</th>
                                        <?php /* ?><th scope="col" style="width: 100px !important; min-width: 100px !important;">Clave de Sucursal</th><?php */ ?>
                                        <th scope="col" style="width: 270px !important; min-width: 270px !important;">Dirección</th>
                                        <th scope="col" style="width: 250px !important; min-width: 250px !important;">Colonia</th>
                                        <th scope="col" style="width: 100px !important; min-width: 100px !important;">Código Postal</th>
                                        <th scope="col" style="width: 200px !important; min-width: 200px !important;">Ciudad | Departamento</th>
                                        <th scope="col" style="width: 200px !important; min-width: 200px !important;">Alcaldía | Municipio</th>
                                        <th scope="col" style="width: 200px !important; min-width: 200px !important;">Latitud</th>
                                        <th scope="col" style="width: 200px !important; min-width: 200px !important;">Longitud</th>
                                        <th scope="col" style="width: 200px !important; min-width: 200px !important;">Contacto</th>
                                        <th scope="col" style="width: 100px !important; min-width: 100px !important;">Teléfono</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-info"></tbody>
<?php 

?>
                                <tfoot>
                                    <tr>
                                        <th colspan="22">
                                            <span class="ui-icon ui-icon-seek-first"></span>
                                            <span class="ui-icon ui-icon-seek-prev" style="position: relative;display: inline-block;cursor: pointer;top: 3px;"></span>
                                            Página 
                                            <input class="page" id="page" type="text" value="1" style="text-align:center"/> 
                                            de <span class="total_pages">0</span>
                                            <div class="sep"></div>
                                            <span class="ui-icon ui-icon-seek-next" style="position: relative;display: inline-block;cursor: pointer;top: 3px;"></span>
                                            <span class="ui-icon ui-icon-seek-end"></span>
                                            <select class="count" id="count">
                                                <option value="10">10</option>
                                                <option value="20">20</option>
                                                <option value="30">30</option>
                                                <option value="50">50</option>
                                                <option value="80">80</option>
                                                <option value="100">100</option>
                                                <option value="200">200</option>
                                            </select>
                                            <div class="sep"></div>
                                            Mostrando <span class="from">0</span> - <span class="to">0</span> de <span class="total">0</span>
                                        </th>
                                    </tr>
                                </tfoot>
<?php 

?>
                            </table>
                            <div id="grid-pager"></div>
                            <button id="btn-asignar" onclick="asignar()" type="button" class="btn btn-m btn-primary permiso_registrar permiso_editar">Asignar</button>
                            <br><br><br>
                        </div>
                      <?php 
                      /*
                      ?>
                        <div class="ibox-content"></br></br>
                          <div class="form-group">
                            <div class="input-group-btn">
                              <button id="btn-asignar" onclick="asignar()" type="button" class="btn btn-m btn-primary permiso_registrar permiso_editar">Asignar</button>
                            </div>
                          </div>
                        </div>
                        <?php  
                        */
                        ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="importarpv" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Importar Visitas</h4>
                    </div>
                    <div class="modal-body">
                        <form id="form-importpv" action="import" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="cve_almacen" value="<?php echo $_SESSION['cve_almacen']; ?>">
                            <div class="form-group">
                                <label>Seleccionar archivo excel para importar</label>
                                <input type="file" name="file" id="file" class="form-control" required>
                            </div>
                        </form>
                        <div class="col-md-12">
                            <div class="progress" style="display:none">
                                <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                                    <div class="percent">0%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="col-md-6" style="text-align: left">
                            <button id="btn-layoutpv" type="button" class="btn btn-primary">Descargar Layout</button>
                        </div>
                        <div style="text-align: right">
                            <button id="btn-importpv" type="button" class="btn btn-primary">Importar</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
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
                        <h4 class="modal-title">Importar destinatarios</h4>
                    </div>
                    <div class="modal-body">
                        <form id="form-import" action="import" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Seleccionar archivo excel para importar</label>
                                <input type="file" name="file" id="file" class="form-control" required>
                            </div>
                        </form>
                        <div class="col-md-12">
                            <div class="progress" style="display:none">
                                <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                                    <div class="percent">0%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="col-md-6" style="text-align: left">
                            <button id="btn-layout" type="button" class="btn btn-primary">Descargar Layout</button>
                        </div>
                        <div style="text-align: right">
                            <button id="btn-import" type="button" class="btn btn-primary">Importar</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>

//var agentes_init = "";
    $('#btn-layoutpv').on('click', function(e) {
        //e.preventDefault();  //stop the browser from following
        //window.location.href = 'http://www.tinegocios.com/proyectos/wms/Layout_Articulos_Importador.xlsx';
        window.location.href = '/Layout/Layout_Visitas.xlsx';
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
                url: '/clientes/destinatarios/importar',
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
                                percent.html(percentComplete + '%');
                                if (percentComplete === 100) {
                                    setTimeout(function() {
                                        $('.progress').hide();
                                    }, 2000);
                                }
                            }
                        }, false);
                    }
                    return myXhr;
                },
                success: function(data) {
                    setTimeout(
                        function() {
                            if (data.status == 200) {
                                swal("Exito", data.statusText, "success");
                                $('#importar').modal('hide');
                                ReloadGrid();
                            } else {
                                swal("Error", data.statusText, "error");
                            }
                        }, 1000)
                },
            });
        });

        $('#btn-importpv').on('click', function() {

            $('#btn-importpv').prop('disable', true);
            var bar = $('.progress-bar');
            var percent = $('.percent');
            //var status = $('#status');

            var formData = new FormData();
            formData.append("clave", "valor");

            $.ajax({
                // Your server script to process the upload
                url: '/clientes/destinatarios/importarpv',
                type: 'POST',

                // Form data
                data: new FormData($('#form-importpv')[0]),

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
                                percent.html(percentComplete + '%');
                                if (percentComplete === 100) {
                                    setTimeout(function() {
                                        $('.progress').hide();
                                    }, 2000);
                                }
                            }
                        }, false);
                    }
                    return myXhr;
                },
                success: function(data) {
                    console.log("SUCCESS FILE = ", data);
                    setTimeout(
                        function() {
                            if (data.status == 200) {
                                swal("Exito", data.statusText, "success");
                                $('#importar').modal('hide');
                                ReloadGrid();
                            } else {
                                swal("Error", data.statusText, "error");
                            }
                        }, 1000)
                }, error: function(data)
                {
                    console.log("ERROR FILE = ", data);
                }
            });
        });

    </script>

<?php 
/*
?>

    @if(isset($codigos_dane) && !empty($codigos_dane))
        <script>
            var postales = [];
        @foreach( $codigos_dane AS $value )
            postales.push({
                '{{ $value["cod_municipio"] }}' : '{{ $value["departamento"] | $value["des_municipio"] }}'
            });
        @endforeach

        function asyncSqrt(callback) {
            setTimeout(function() {
                callback();
            }, 0 | Math.random() * 100);
        }

        asyncSqrt(function() {
            $.each( postales, function(index, item){
                //$('#txtCod').append('<option value="'+Object.keys(item)+'">'+index+' - '+item[Object.keys(item)]+'</option>')
                $('#codigo').append('<option value="'+Object.keys(item)+'">'+index+' - '+item[Object.keys(item)]+'</option>')
            });
        });
        </script>
    @endif
<?php 

?>
    @if(isset($agentes_operadores) && !empty($agentes_operadores))
        <script>
            var postales = [];
        @foreach( $agentes_operadores AS $value )
            postales.push({
                '{{ $value["cve_Vendedor"] }}' : '{{ ($value["cve_Vendedor"]) -  $value["Nombre"] }}'
            });
        @endforeach

        function asyncSqrt(callback) {
            setTimeout(function() {
                callback();
            }, 0 | Math.random() * 100);
        }

        asyncSqrt(function() {
            $.each( postales, function(index, item){
                //$('#txtCod').append('<option value="'+Object.keys(item)+'">'+index+' - '+item[Object.keys(item)]+'</option>')
                $('#agentes').append('<option value="'+Object.keys(item)+'">'+index+' - '+item[Object.keys(item)]+'</option>')
            });
        });
        </script>
    @endif
<?php  
*/
?>

    <script type="text/javascript">
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
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/almacenPredeterminado/index.php',
                success: function(data) {
                    console.log("almacenPrede() SUCESS", data);
                    if (data.success == true) {
                        document.getElementById('almacenes').value = data.codigo.id;
                        filtrarRutas();
                        ReloadGrid();
                    }
                },
                error: function(res) {
                    console.log("Error Almacén", res);
                }
            });
        }
      
      
      $(document).ready(function(){
        setTimeout(function(){
          almacenPrede();
        }, 1000);
      });
    </script>


    <script type="text/javascript">
function utf8_decode (strData) { 

  var tmpArr = []
  var i = 0
  var c1 = 0
  var seqlen = 0

  strData += ''

  while (i < strData.length) {
    c1 = strData.charCodeAt(i) & 0xFF
    seqlen = 0

    if (c1 <= 0xBF) {
      c1 = (c1 & 0x7F)
      seqlen = 1
    } else if (c1 <= 0xDF) {
      c1 = (c1 & 0x1F)
      seqlen = 2
    } else if (c1 <= 0xEF) {
      c1 = (c1 & 0x0F)
      seqlen = 3
    } else {
      c1 = (c1 & 0x07)
      seqlen = 4
    }

    for (var ai = 1; ai < seqlen; ++ai) {
      c1 = ((c1 << 0x06) | (strData.charCodeAt(ai + i) & 0x3F))
    }

    if (seqlen === 4) {
      c1 -= 0x10000
      tmpArr.push(String.fromCharCode(0xD800 | ((c1 >> 10) & 0x3FF)))
      tmpArr.push(String.fromCharCode(0xDC00 | (c1 & 0x3FF)))
    } else {
      tmpArr.push(String.fromCharCode(c1))
    }

    i += seqlen
  }

  return tmpArr.join('')
}

function filtrarRutas()
{
    console.log("filtrarRutas(",$("#rutas").val(),")");
    $("#rutas").empty();
      $.ajax({
        type: "POST",
        dataType: "json",
        url: '/api/destinatarios/update/index.php',
        data: {
          action : "filtrar_rutas",
          almacen : $("#almacenes").val()
        },
        beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
        success: function(data) {
            //console.log("traer_agentes_ruta = ", data);
            $("#rutas").append(data);
            $(".chosen-select").trigger("chosen:updated");
            console.log("filtrarRutas()", data);
        }
      });
}

$("#almacenes").change(function(){
    filtrarRutas();
});

$("#rutas").change(function()
{
    //$("#agentes").val("");
    console.log("ruta = ", $(this).val());
    $("#agentes").empty();
    $("#dias").val("''");
    //if($(this).val() == 'null' || $(this).val() == '') return;
      $.ajax({
        type: "POST",
        dataType: "json",
        url: '/api/destinatarios/update/index.php',
        data: {
          action : "traer_agentes_ruta",
          ruta : $(this).val()
        },
        beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
        success: function(data) {
            console.log("traer_agentes_ruta = ", data);
            $("#agentes").append(data);
            //if($("#agentes").val())
            $(".chosen-select").trigger("chosen:updated");
        }
      });

      if($(this).val() == "") 
      {
        $("#agentes").append($("#agentes1").html());
        $("#btn-rutasgps").prop("disabled", true); 
        $("#generarExcelDestinatarios").hide();
        $("#generarExcelDestinatariosBTN").show();
      }
      else 
      {
        $("#btn-rutasgps").prop("disabled", false);
        $("#generarExcelDestinatarios").show();
        $("#generarExcelDestinatariosBTN").hide();
      }
});

$("#agentes").change(function()
{
    //$("#agentes").val("");
    console.log("Agente = ", $(this).val());
    $("#rutas").empty();
    $("#dias").val("''");
      $.ajax({
        type: "POST",
        dataType: "json",
        url: '/api/destinatarios/update/index.php',
        data: {
          action : "traer_rutas_agente",
          agente : $(this).val()
        },
        beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
        success: function(data) {
            console.log("traer_rutas_agente = ", data);
            $("#rutas").append(data);
            //if($("#agentes").val())
            $(".chosen-select").trigger("chosen:updated");
        }
      });

      if($(this).val() == "")
        filtrarRutas();

      /*
      if($(this).val() == "") 
      {
        $("#btn-rutasgps").prop("disabled", true); 
        $("#generarExcelDestinatarios").hide();
        $("#generarExcelDestinatariosBTN").show();
      }
      else 
      {
        $("#btn-rutasgps").prop("disabled", false);
        $("#generarExcelDestinatarios").show();
        $("#generarExcelDestinatariosBTN").hide();
      }
      */
});


var array_id_destinatarios = [];
//var array_id_clientes = [];

function ArreglarSecuencias(id_dest)
{
    //if(!$("#SecuenciaMayor").val() || $("#SecuenciaMayor").val() == 0) 
    $("#SecuenciaMayor").val($("#clientes_por_dia").val());//$("#SecuenciaMayor").val(0);

    console.log("id_fila =", id_dest, ", clientes_por_dia = ",$("#clientes_por_dia").val()," , Secuencia_Mayor =", $("#SecuenciaMayor").val(), ", inArray =", $.inArray(id_dest, array_id_destinatarios), " #sec_"+id_dest+" = ", $("#sec_"+id_dest).text());

    var sec_mayor = parseFloat($("#SecuenciaMayor").val()),
        sec_actual = ($("#sec_"+id_dest).text()).replace(" ", "");

        //console.log("sec_actual_0 = ", sec_actual);

        sec_actual = $.trim(sec_actual);

        if(!sec_actual || isNaN(sec_actual)) sec_actual = 0;

        sec_actual = parseFloat(sec_actual);

        //console.log("sec_mayor = ", sec_mayor, " - sec_actual = ", sec_actual, " - sec_actual2 = ", ($("#sec_"+id_dest).text()).replace(" ", ""));
    //if($("#SecuenciaMayor").val() <  ($("#sec_"+id_dest).text()).replace(" ", "") || ($("#sec_"+id_dest).text()).replace(" ", "") == "")
    if(sec_mayor <  sec_actual || $.trim(($("#sec_"+id_dest).text()).replace(" ", "")) == "")
    {
        if($.inArray(id_dest, array_id_destinatarios) == -1)
        {
            //console.log("array_id_destinatarios OK");
            array_id_destinatarios.push(id_dest);
            //array_id_clientes.push(clave_cliente);
        }
        else
        {
            //console.log("array_id_destinatarios NO");
            array_id_destinatarios = $.grep(array_id_destinatarios, function(value) {
              return value != id_dest;
            });
/*
            array_id_clientes = $.grep(array_id_clientes, function(value) {
                return value != clave_cliente;
            });
*/
            $("#sec_"+id_dest).text("");
        }

        for(var i = 0; i < array_id_destinatarios.length; i++)
        {
            $("#sec_"+array_id_destinatarios[i]).text(parseInt($("#SecuenciaMayor").val()) + i + 1);
        }
    }

    console.log("array_id_destinatarios", array_id_destinatarios);
    //console.log("");
}

$("#rutas, #codigo, #dias, #agentes").change(function(){

    array_id_destinatarios = [];
    //$("#SecuenciaMayor").val(0);
    $("#SecuenciaMayor").val($("#clientes_por_dia").val());
    ReloadGrid();

    if($("#dias").val() != "''" && $("#rutas").val() != "" && $("#agentes").val() != "")
        $("#reiniciar_secuencia").prop("disabled", false);
    else
        $("#reiniciar_secuencia").prop("disabled", true);
});

        $("#generarExcelDestinatarios").click(function(){

            var almacen = $("#almacenes").val(),
            criterio = $("#txtCriterio").val(),
            rutas = $("#rutas").val(),
            agentes = $("#agentes").val(),
            dias = $("#dias").val(),
            codigo = $("#codigo").val();

            console.log("almacen="+almacen+"&criterio="+criterio+"&rutas="+rutas+"&agentes="+agentes+"&dias="+dias+"&codigo="+codigo);

            $(this).attr("href", "/api/koolreport/excel/destinatarios/export.php?almacen="+almacen+"&criterio="+criterio+"&rutas="+rutas+"&agentes="+agentes+"&dias="+dias+"&codigo="+codigo+"");

        });


$("#reiniciar_secuencia").click(function()
{

    swal({
            title: "¿Desea Reiniciar Estas Secuencias?",
            text: "Esta acción implica que se eliminarán todas las secuencias planificadas de la ruta "+$("#rutas").val()+ " del día "+$("#dias option:selected").text()+" para luego volver a planificarlas",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Reiniciar",
            cancelButtonText: "Cancelar",
            closeOnConfirm: true
        },
        function() {

            console.log("OK reiniciar");
              $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/destinatarios/update/index.php',
                data: {
                  action : "ReiniciarSecuencia",
                  almacen: $("#clave_almacen").val(),
                  rutas: $("#rutas").val(),
                  agentes: $("#agentes").val(),
                  dias: $("#dias").val()
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
                success: function(data) {
                    console.log("SUCCESS = ", data);
                    swal("Exito","Secuencia Reiniciada", "success");
                    window.location.reload();
                },
                error: function(data)
                {
                    console.log("ERROR = ", data);
                }
              });

        });

});
        //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////
        function ReloadGrid() { //lilo
            if ($("#almacenes").val() !== '') {
                var pager_selector = "#dt-detalles";
                var pager_selector = "#grid-pager";
                console.log("*****************************************");
                console.log("dias send = ", $("#dias").val());
                console.log("page = ", $("#page").val());
                console.log("count = ", $("#count").val());
                console.log("criterio = ", $("#txtCriterio").val());
                console.log("almacen = ", $("#almacenes").val());
                console.log("codigo = ", $("#codigo").val());
                console.log("rutas = ", $("#rutas").val());
                console.log("agentes = ", $("#agentes").val());
                console.log("*****************************************");

                //if($("#rutas").val() == 'null' || $("#rutas").val() == null){ console.log("NULL");filtrarRutas(); return;}

                if($("#rutas").val() == '' && $("#txtCriterio").val() == '') 
                {
                    console.log("VACIO");
                    $('#dt-detalles tbody').html("");
                    $('#dt-detalles .total_pages').text(0);
                    $('#dt-detalles .page').text(0);
                    $('#dt-detalles .from').text(0);
                    $('#dt-detalles .to').text(0);
                    $('#dt-detalles .total').text(0);
                    $('#total_clientes').val(0);
                    $('#clientes_por_ruta').val(0);
                    $('#clientes_por_dia').val(0);
                    return;
                }
                $.ajax({
                    type: "GET",
                    dataType: "JSON",
                    url: '/api/v2/destinatarios',
                    viewrecords: true,
                    rowNum: 10,
                    rowList: [10, 20, 30, 50, 80, 100, 200],
                    pager: pager_selector,
                    data: {
                        criterio: $("#txtCriterio").val(),
                        almacen: $("#almacenes").val(),
                        codigo: $("#codigo").val(),
                        rutas: $("#rutas").val(),
                        dias: $("#dias").val(),
                        agentes: $("#agentes").val(),
                        count: $("#count").val(),
                        page: $("#page").val()
                    },
                    beforeSend: function(x){
                    if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
                    console.log("beforeSend 1: ", x);

                    }, always: function(data) {
                        console.log("always 1: ", data);
                    },
                    success: function(data) {
                        console.log("SUCCESS: ", data);
                        console.log("SUCCESS data.total_pages: ", data.total_pages);
                        console.log("SUCCESS data.page: ", data.page);
                        console.log("SUCCESS data.from: ", data.from);
                        console.log("SUCCESS data.to: ", data.to);
                        console.log("SUCCESS data.total: ", data.total);
                        console.log("SUCCESS data.total: ", data.total);
                        console.log("SUCCESS data.clientes_por_ruta: ", data.clientes_por_ruta);
                        console.log("SUCCESS data.clientes_por_dia: ", data.clientes_por_dia);

                        var ok = false, valor_length = 0;
                        if(data == null) {ok = true;}else {valor_length = data.data.length; ok = true;}

                        if(data == null)
                        console.log("SUCCESS length NULL: ", 'NULL');
                        else
                        console.log("SUCCESS length: ", valor_length);

                        if (valor_length >= 0) 
                        {
                            console.log("SUCCESS 2: ", valor_length);
                            if(valor_length == 0)
                            {
                                $('#dt-detalles .total_pages').text(0);
                                $('#dt-detalles .page').text(0);
                                $('#dt-detalles .from').text(0);
                                $('#dt-detalles .to').text(0);
                                $('#dt-detalles .total').text(0);
                                $('#total_clientes').val(0);
                                $('#clientes_por_ruta').val(0);
                                $('#clientes_por_dia').val(0);
                            }
                            else
                            {
                                $('#dt-detalles .total_pages').text(data.total_pages);
                                $('#dt-detalles .page').text(data.page);
                                $('#dt-detalles .from').text(data.from);
                                $('#dt-detalles .to').text(data.to);
                                $('#dt-detalles .total').text(data.total);

                                $('#total_clientes').val(data.total);
                                $('#clientes_por_ruta').val(data.clientes_por_ruta);
                                $('#clientes_por_dia').val(data.clientes_por_dia);
                            }
                            var row = '', i = 0;
                        if(valor_length > 0)
                        {
                            $.each(data.data, function(index, item){
                                i++;
                                //console.log("item = ", item);
                                //array($id_destinatario, $razonsocial, $latitud, $longitud, $fol_folio, $Estatus)
                                if(item.Secuencia == 20000) 
                                    item.Secuencia = "";
                                row += '<tr>'+
                                            '<td align="center">'+imageFormat(item.id, item.cliente, item.latitud, item.longitud, item.Secuencia)+'</td>'+
                                            '<td align="center">'+imageFormat2(item.id, item.clave_cliente, item.cliente,item.clave_destinatario, item.destinatario, item.ruta)+'</td>'+
                                            '<td id="sec_'+item.id+'"><span>'+item.Secuencia+'</span> <input type="number" class="form-control editar_secuencia" min="1" style="display:none;" value="'+item.Secuencia+'"><br class="editar_secuencia" style="display:none;"><a href="#sec_'+item.id+'" style="display:none;" class="editar_secuencia guardar_secuencia" data-dest="'+item.id+'" data-secuencia="'+item.Secuencia+'" onclick=""><i class="fa fa-save" alt="Guardar" title="Cambiar Secuencia"></i></a>&nbsp;<a href="#sec_'+item.id+'" class="editar_secuencia cancelar_secuencia" data-sec="'+item.id+'" style="display:none;float:right;" onclick=""><i class="fa fa-reply" alt="Cancelar" title="Cancelar"></i></a>&nbsp;'+imageFormat3(item.id, item.Secuencia)+'</td>'+
                                            '<td align="center">'+item.ruta+'</td>'+
                                            '<td align="center" style="display:none;">'+item.Agente+'</td>'+
                                            '<td>'+item.clave_cliente+'</td>'+
                                            '<td>'+item.razoncomercial+'</td>'+
                                            '<td>'+item.cliente+'</td>'+
                                            '<td>'+item.clave_destinatario+'</td>'+
                                            '<td>'+item.destinatario+'</td>'+
                                            '<td>'+utf8_decode(item.direccion)+'</td>'+
                                            '<td>'+utf8_decode(item.colonia)+'</td>'+
                                            '<td align="center">'+item.postal+'</td>'+
                                            '<td>'+utf8_decode(item.ciudad)+'</td>'+                                            
                                            '<td>'+utf8_decode(item.estado)+'</td>'+
                                            '<td>'+utf8_decode(item.latitud)+'</td>'+
                                            '<td>'+utf8_decode(item.longitud)+'</td>'+
                                            '<td>'+utf8_decode(item.contacto)+'</td>'+
                                            '<td align="right">'+item.telefono+'</td>'+
                                        '</tr>';
                                if($("#rutas").val() && $("#agentes").val() && $("#dias").val() != "''")
                                {
                                    if(item.Secuencia == "")
                                    {
                                        if(0 >= $("#SecuenciaMayor").val())
                                        $("#SecuenciaMayor").val(0);
                                    }
                                    else
                                    {
                                        if(item.Secuencia)
                                            $("#SecuenciaMayor").val(item.Secuencia);
                                        else
                                            $("#SecuenciaMayor").val($("#clientes_por_dia").val());
                                    }
                                }
                            });
                        }
                            $("#codigo").empty();
                            if(valor_length > 0)
                            {
                                $("#codigo").append(data.select_codigo_postal);
                                $("#codigo").val(data.codigo_postal);
                            }

                            $('#dt-detalles tbody').html(row);


                            /*
                            if($("#rutas").val() == "")
                            {
                                $("#agentes_chosen").hide();
                                $("#agentes1_chosen").show();
                            }
                            else
                            {
                                $("#agentes_chosen").show();
                                $("#agentes1_chosen").hide();
                            }
                            */
                            $(".chosen-select").trigger("chosen:updated");


    $(".cancelar_secuencia, .guardar_secuencia").click(function()
    {
        console.log("cancelar secuencia = ", $(this).data("sec"));
        $("#sec_"+$(this).data("sec")+" span").show();
        $("#edit_"+$(this).data("sec")+"").show();
        $("#sec_"+$(this).data("sec")+" .editar_secuencia").hide();
    });

    $(".guardar_secuencia").click(function()
    {
        console.log("guardar destinatario = ", $(this).data("dest"));
        console.log("guardar secuencia actual = ", $(this).data("secuencia"));
        console.log("guardar secuencia cambiar = ", $("#sec_"+$(this).data("dest")+" input").val());

        if(!$.isNumeric( $("#sec_"+$(this).data("dest")+" input").val() ) || $("#sec_"+$(this).data("dest")+" input").val() == 0)
        {
            swal("Error", "Debe Introducir un valor numérico mayor a cero", "error");
            return;
        }

        if($(this).data("secuencia") == $("#sec_"+$(this).data("dest")+" input").val()) return;

          $.ajax({
            type: "POST",
            dataType: "json",
            url: '/api/destinatarios/update/index.php',
            data: {
              action : "CambiarSecuenciaDestinatario",
              id_destinatario: $(this).data("dest"),
              rutas: $("#rutas").val(),
              secuencia_actual: $(this).data("secuencia"),
              secuencia_a_cambiar: $("#sec_"+$(this).data("dest")+" input").val(),
              dias: $("#dias").val()
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            success: function(data) {
                console.log("SUCCESS = ", data);
                //swal("Exito","Ruta Asignada a los Detinatarios", "success");
                //$("#modal_asignacion_ruta").modal('hide');
                ReloadGrid();
            },
            error: function(data)
            {
                console.log("ERROR = ", data);
            }
          });

        //$("#sec_"+$(this).data("sec")+" span").show();
        //$("#edit_"+$(this).data("sec")+"").show();
        //$("#sec_"+$(this).data("sec")+" .editar_secuencia").hide();
    });


                        } else //if(valor_length == 0)
                        {
                            swal({
                                title: "Error",
                                text: "No se pudo planificar el inventario",
                                type: "error"
                            });
                        }
                    }, error: function(data) {
                        console.log("ERROR 1: ", data);

                    }

                });
            

            } else {
                swal("Error", "Debe seleccionar al menos el almacén", "error");
            }
        }


        function imageFormat(id, razonsocial, latitud, longitud, secuencia) {

            var html = '';
            //console.log("latitud = ", latitud, "longitud = ", longitud);
            //if(latitud != '--' && longitud != '--' && latitud != '' && longitud != '' && latitud != '0' && longitud != '0')
            //html += '<a href="#" onclick="verGps(\'' + id + '\', \'' + razonsocial + '\', \'' + latitud + '\', \'' + longitud + '\')" title="GPS"><i class="fa fa-map-marker"></i></a>&nbsp;&nbsp;&nbsp;';

            if($("#permiso_editar").val() == 1)
            {
            html += '<a href="#" onclick="editar(\'' + id + '\')"><i class="fa fa-edit" alt="Editar" title="Editar"></i></a>&nbsp;';
            }

            var dias = ($("#dias").val()=="''")?(""):($("#dias").val());
            var mensaje = "Borrar Destinatario de toda la ruta y planeación";
            if(dias) 
                mensaje = "Borrar Visita del Destinatario";
            if($("#permiso_eliminar").val() == 1)
            {
            html += '<a href="#" onclick="borrar(\'' + id + '\', \'' + secuencia + '\')"><i class="fa fa-eraser" alt="Borrar" title="'+mensaje+'"></i></a>';
            }
            //html += '&nbsp;&nbsp;<a href="#" onclick="#"><i class="fa fa-file-excel-o" alt="Importar Planeación de Visitas" title="Importar Planeación de Visitas"></i></a>';
            return html;
        }
      //EDG
        function imageFormat2(id, clave_cliente, cliente, clave_destinatario, destinatario,ruta) 
        {
          //if(ruta == "--")
          //{
            var html= '';
            if($("#permiso_editar").val() == 1 && $("#permiso_registrar").val() == 1)
            {
                 html = '<input type="checkbox" aling="center" class="checkbox-asignator" onclick="ArreglarSecuencias('+id+')" id="'+id+'" value="'+destinatario+'-'+clave_cliente+'-'+cliente+'"/>';
            }
          //}
          //else
          //{
          //  var html = "";
          //}
          return html;
        }
      
        function imageFormat3(id, secuencia) 
        {
            var html = "";

            if(secuencia && $("#permiso_editar").val() == 1 && $("#permiso_registrar").val() == 1)
           html = '&nbsp;&nbsp;&nbsp;<a href="#sec_'+id+'" id="fix_'+id+'" onclick="editar_secuencia(\'' + id + '\', \'' + secuencia + '\', 1)"><i class="fa fa-sort-numeric-asc" alt="Reparar Secuencias" title="Arreglar Secuencias"></i></a>&nbsp;&nbsp;&nbsp;<a href="#sec_'+id+'" id="edit_'+id+'" onclick="editar_secuencia(\'' + id + '\', \'' + secuencia + '\', 0)"><i class="fa fa-edit" alt="Editar" title="Editar Secuencia"></i></a>&nbsp;';

          return html;
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
      
        $("#guardar_ruta").on("click", function(){

            var ruta = $("#ruta_asignada").val();
            var arr_clientes = [];
            $("#tabla_usuarios_select>tbody>tr").each(function(i, e){
              arr_clientes.push($(this).attr('id'));
            });

            console.log("guardar_ruta = ", arr_clientes, "ruta = ", ruta);
            console.log("almacen:", $("#clave_almacen").val());
            if(arr_clientes.length == 0 || ruta == "")
            {
              swal("Error","Seleccione una Ruta y Clientes para su asignacion", "error");
            }

            else
            {
                console.log("????????????????????????????????????????");
                  console.log("almacen:", $("#clave_almacen").val());
                  console.log("sec_dest:", array_id_destinatarios);
                  console.log("sec_mayor:", $("#SecuenciaMayor").val());
                  console.log("rutas:", $("#rutas").val());
                  console.log("agentes:", $("#agentes").val());
                  console.log("dias:", $("#dias").val());
                  console.log("destinatarios :", arr_clientes);
                  console.log("ruta :", ruta);
                console.log("????????????????????????????????????????");


              $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/destinatarios/update/index.php',
                data: {
                  action : "asignarRutaACliente",
                  almacen: $("#clave_almacen").val(),
                  sec_dest: array_id_destinatarios,
                  sec_mayor: $("#SecuenciaMayor").val(),
                  rutas: $("#rutas").val(),
                  agentes: $("#agentes").val(),
                  dias: $("#dias").val(),
                  destinatarios : arr_clientes,
                  ruta : ruta
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
                success: function(data) {
                    console.log("SUCCESS = ", data);
                  if (data.success == true) 
                  {
                    swal("Exito","Ruta Asignada a los Detinatarios", "success");
                    $("#modal_asignacion_ruta").modal('hide');
                    ReloadGrid();
                  }
                },
                error: function(data)
                {
                    console.log("ERROR = ", data);
                }
              });
            }
        });


    function editar_secuencia(destinatario, secuencia, fix)
    {

        if(fix == 1)
        {

        console.log("destinatario = ", destinatario);
        console.log("secuencia_actual = ", secuencia);
        console.log("fix = ", fix);
        
          $.ajax({
            type: "POST",
            dataType: "json",
            url: '/api/destinatarios/update/index.php',
            data: {
              action : "CambiarSecuenciaDestinatario",
              id_destinatario: destinatario,
              rutas: $("#rutas").val(),
              secuencia_actual: secuencia,
              secuencia_a_cambiar: 0,
              fix: fix,
              dias: $("#dias").val()
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            success: function(data) {
                console.log("SUCCESS = ", data);
                //swal("Exito","Ruta Asignada a los Detinatarios", "success");
                //$("#modal_asignacion_ruta").modal('hide');
                ReloadGrid();
            },
            error: function(data)
            {
                console.log("ERROR = ", data);
            }
          });
        }
        else
        {
            $("#sec_"+destinatario+" span").hide();
            $("#edit_"+destinatario+"").hide();
            $("#sec_"+destinatario+" .editar_secuencia").show();
        }
    }

    function verGpsRutas()
    {
    console.log("verGpsRutas()");

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                rutas: $("#rutas").val(),
                agentes: $("#agentes").val(),
                dias: $("#dias").val(),
                codigo: $("#codigo").val(),
                txtCriterio: $("#txtCriterio").val(),
                almacen: $("#almacenes").val(),
                action: 'get_gps'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url:'/api/destinatarios/update/index.php',
            success: function(data) {
                console.log("SUCCESS", data);
                if(data == null)
                {
                    swal("Aviso", "No hay Datos disponibles para mostrar", "error");
                    return;
                }
                //console.log(data);
                var datos = data.rows;
                console.log(datos);
                markers = [];
                info_map = [];
                credito_contado = [];

                datos.forEach(function(destinatario, i)
                {
                        //console.log(i, destinatario[0], destinatario[1], destinatario[2], destinatario[3]);
                        //'["' . $row['direccion'] . '", ' . $row['lat'] . ', ' . $row['lng'] . '],'
                        //markers += '[ "", '+destinatario[2]+','+destinatario[3]+'],';
                        var tooltip = "Sucursal: "+destinatario[6];
                        markers[0] = [tooltip, parseFloat(destinatario[4]), parseFloat(destinatario[5])];
                        info_map[0] = [destinatario[6], destinatario[6]];
                        credito_contado[0] = 0;
                        lat = destinatario[4];
                        lon = destinatario[5];

                        var secuencia = destinatario[7];
                        var credito = destinatario[8];
                        var saldo_deudor = destinatario[9];
                        var clasificacion = destinatario[10];
                        if(secuencia != '') secuencia = " | Secuencia: "+secuencia;
                        if(clasificacion != '') clasificacion = " \nClasificación: "+clasificacion;
                        if(credito == 1) saldo_deudor = " \nSaldo Deudor: "+saldo_deudor;

                        tooltip = "Cliente: "+destinatario[0]+" | "+destinatario[1]+secuencia+clasificacion+saldo_deudor;
                        markers[i+1] = [tooltip, parseFloat(destinatario[2]), parseFloat(destinatario[3])];
                        info_map[i+1] = [destinatario[0], destinatario[1]];
                        credito_contado[i+1] = credito;
                        //lat = destinatario[2];
                        //lon = destinatario[3];
                        //console.log(markers);
                });
                //google.maps.event.addDomListener(window, 'load', initMap);
                //initMapRutas();
                initMap();
                $("#show_points").text(info_map.length);
                $("#detalleGpsRutas").modal('show');
            },
            error: function(data){
                console.log("ERROR");
                console.log(data);
            }
        });
    }

//array($id_destinatario, $razonsocial, $latitud, $longitud, $fol_folio, $Estatus)
/*
        function verGps(id_destinatario, razonsocial, latitud, longitud)
        {
            console.log("verGps = ", id_destinatario);
              $("#n_inventario_gps").text(id_destinatario);
              $("#detalleGps").modal('show');

                markers = [];
                info_map = [];
                console.log(id_destinatario, razonsocial, latitud, longitud);
                //'["' . $row['direccion'] . '", ' . $row['lat'] . ', ' . $row['lng'] . '],'
                //markers += '[ "", '+latitud+','+longitud+'],';
                var tooltip = "Destinatario "+id_destinatario+"\n"+
                              id_destinatario+" | "+razonsocial+"\n";

                markers[0] = [tooltip, parseFloat(latitud), parseFloat(longitud)];
                info_map[0] = [id_destinatario, razonsocial];
                lat = latitud;
                lon = longitud;
                console.log(markers);
                //google.maps.event.addDomListener(window, 'load', initMap);
                initMap();
        }
*/
        function borrar_cliente_asignado(id_tr)
        {
            console.log("id_tr =" , id_tr);
          $("#tabla_usuarios_select>tbody>tr[id='"+id_tr+"']").remove();
          $("input[type=checkbox].checkbox-asignator[id='"+id_tr+"']").prop("checked", false);
        }
      
        function asignar()
        {
          $("#tabla_usuarios_select>tbody").empty()
          var folios = [];
          var asignados = [];//EDG
          
          var arr = [];
          var arr2 = [];
          var arr3 = [];
          var arr4 = [];
          $("input[type=checkbox].checkbox-asignator").each(function(i, e){
            if($(this).prop("checked") == true)
            {
              arr.push($(this).attr('id'));
              var value = $(this).attr('value').split('-');
              var nombre_destinatario = value[0];
              var clave_cliente = value[1];
              var nombre_cliente = value[2];
              
              arr2.push(nombre_destinatario);
              arr3.push(clave_cliente);
              arr4.push(nombre_cliente);
            }
          });
          
          for(var j = 0; j < arr.length; j++)
          {
            //arr.toString();
            if(arr != "")
            {
              $modal0 = $("#modal_asignacion_ruta");
              $modal0.modal('show');
              
              $("#tabla_usuarios_select").find('tbody').append(
                $('<tr id="'+arr[j]+'">'+
                    '<td>'+arr[j]+'</td>'+
                    '<td>'+arr2[j]+'</td>'+
                    '<td>'+arr3[j]+'</td>'+
                    '<td>'+arr4[j]+'</td>'+
                    //'<td> <a href="#" onclick="borrar_cliente_asignado(\'' + arr[j] + '\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp </td>'+
                  '</tr>')
              );
            }
          }

            //console.log(" #rutas = ", $("#rutas").val() , " #agentes = ", $("#agentes").val() , " #dias = ", $("#dias").val());
            if($("#rutas").val() && $("#agentes").val() && $("#dias").val())
            {
                $("#ruta_asignada").val($("#rutas").val());
                $('#ruta_asignada').prop('disabled', true);
            }
            else 
                $('#ruta_asignada').prop('disabled', false);

        }

        function borrar(_codigo, secuencia) {

            console.log("destinatario: ", _codigo);
            console.log("rutas: ", $("#rutas").val());
            var dias = ($("#dias").val()=="''")?(""):($("#dias").val());
            
            var title = "¿Está seguro que desea borrar el destinatario?";
            var mensaje = "Esta accíón elimina al destinatario de toda la ruta y planeación";
            if(dias)
            {
                title = "¿Está seguro de eliminar la visita?";
                mensaje = "Esta acción elimina la visita del destinatario pero lo mantiene asignado a la ruta";
            }

            swal({
                    title: title,
                    text: mensaje,
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Borrar",
                    cancelButtonText: "Cancelar",
                    closeOnConfirm: true
                },
                function() {
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        data: {
                            destinatario: _codigo,
                            rutas: $("#rutas").val(),
                            dias: dias,
                            secuencia: secuencia,
                            action: "delete"
                        },
                        beforeSend: function(x) {
                            if (x && x.overrideMimeType) {
                                x.overrideMimeType("application/json;charset=UTF-8");
                            }
                        },
                        url: '/api/destinatarios/update/index.php',
                        success: function(data) {
                            console.log("DELETE SUCCESS: ", data);
                            if (data.success == true) {
                                ReloadGrid();
                            }
                        }
                    });
                });


        }

      //lilo
        function editar(_codigo) {
            $("#_title").html('<h3>Editar Destinatario</h3>');
            $.ajax({
                url: '/api/destinatarios/update/index.php',
                type: "POST",
                dataType: "json",
                data: {
                    action: "load",
                    codigo: _codigo,
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) {
                  console.log(data);
                    if (data.success == true) {
                        $('#txtClaveDest').prop('disabled', true);
                        $("#txtClaveDest").val(data.id);
                        $("#claveDeDestinatario").val(data.ClaveDeDestinatario);
                        $("#txtRazonSocial").val(data.RazonSocial);
                        $("#txtCalleNumero").val(data.Direccion);
                        $("#txtColonia").val(data.Colonia);
                        $("#txtCod").val(data.CodigoPostal);
                        //$("#txtCod").change();
                        $("#txtTelefono").val(data.Telefono);
                        $("#txtContacto").val(data.Contacto);
                        $("#cboCliente").val(data.Cve_Clte);

                        $("#txtEmail").val(data.Email);
                        $("#txtLatitud").val(data.Latitud);
                        $("#txtLongitud").val(data.Longitud);
                        $("#dir_principal").val(data.dir_principal);

                        $(".chosen-select").trigger("chosen:updated");
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
            $(':input', '#myform')
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
            $('#list').show();

        }

        function agregar() 
        {
          $("#_title").html('<h3>Agregar Destinatario</h3>');
          $.ajax({
            url: '/api/clientes/lista/index.php',
            dataType: 'json',
            cache: false,
            data: {
              action: 'obtenerClaveDestinatario',
              type: 'GET'
            },
          }).done(function(data) {
            if (data.clave) {
              $("#txtClaveDest").val(data.clave);
            }
          });

          $("#txtDepart").val("");
          $("#txtMunicipio").val("");

          $(':input', '#myform')
              .removeAttr('checked')
              .removeAttr('selected')
              .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
              .val('');

          $('#list').hide();
          $('#FORM').show();
          $('#FORM').removeAttr('class').attr('class', '');
          $('#FORM').addClass('animated');
          $('#FORM').addClass("fadeInRight");
          $("#hiddenAction").val("add");
        }

function isEmail(email) {
  var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  return regex.test(email);
}
        $("#btnSave").on("click", function(e) {
            console.log("txtLatitud = ", $("#txtLatitud").val());
            e.preventDefault();
            var action = $("#hiddenAction").val();
            $("#btnCancel").hide();

            if(!isEmail($("#txtEmail").val()) && $("#txtEmail").val())
            {
                swal('Error', 'Por favor escriba el email correctamente', 'error');
            }
            else if ($("#cboCliente").val() == "") {
                swal('Error', 'Por favor seleccione un cliente', 'error');
            } else if ($("#claveDeDestinatario").val() == "") {
                swal('Error', 'Por favor indique una clave de destinatario', 'error');
            } else if ($("#txtRazonSocial").val() == "") {
                swal('Error', 'Por favor indique una razon social', 'error');
            } else if ($("#txtCalleNumero").val() == "") {
                swal('Error', 'Por favor indique una direccion', 'error');
            } else if ($("#txtCod").val() == "") {
                swal('Error', 'Por favor seleccione un codigo postal', 'error');
            } else {
                if (action === "add") 
                {
                    $.ajax({
                        url: '/api/destinatarios/update/index.php',
                        type: "POST",
                        dataType: "json",
                        data: {
                            action: action,
                            RazonSocial: $("#txtRazonSocial").val(),
                            Direccion: $("#txtCalleNumero").val(),
                            Colonia: $("#txtColonia").val(),
                            CodigoPostal: $("#txtCod").val(),
                            Ciudad: $("#txtDepart").val(),
                            Estado: $("#txtMunicipio").val(),
                            Telefono: $("#txtTelefono").val(),
                            Contacto: $("#txtContacto").val(),
                            Email: $("#txtEmail").val(),
                            Latitud: $("#txtLatitud").val(),
                            Longitud: $("#txtLongitud").val(),
                            Cve_Clte: $("#cboCliente").val(),
                            ClaveDeDestinatario: $("#claveDeDestinatario").val(),
                        },
                        beforeSend: function(x)
                        {
                            if (x && x.overrideMimeType) 
                            {
                              x.overrideMimeType("application/json;charset=UTF-8");
                            }
                        },
                        success: function(data) 
                        {
                            if (data.success) {
                                cancelar();
                                ReloadGrid();
                            } 
                            else {
                                $("#btnCancel").show();
                            }
                         }
                    });
                } else if (action === 'edit') {
                    console.log("dir_principal: ",$("#dir_principal").val());
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        data: {
                            id: $("#txtClaveDest").val(),
                            RazonSocial: $("#txtRazonSocial").val(),
                            Direccion: $("#txtCalleNumero").val(),
                            Colonia: $("#txtColonia").val(),
                            CodigoPostal: $("#txtCod").val(),
                            Ciudad: $("#txtDepart").val(),
                            Estado: $("#txtMunicipio").val(),
                            Telefono: $("#txtTelefono").val(),
                            Contacto: $("#txtContacto").val(),
                            Email: $("#txtEmail").val(),
                            Latitud: $("#txtLatitud").val(),
                            Longitud: $("#txtLongitud").val(),
                            Cve_Clte: $("#cboCliente").val(),
                            ClaveDeDestinatario: $("#claveDeDestinatario").val(),
                            dir_principal: $("#dir_principal").val(),
                            action: action
                        },
                        beforeSend: function(x) {
                            if (x && x.overrideMimeType) {
                                x.overrideMimeType("application/json;charset=UTF-8");
                            }
                        },
                        url: '/api/destinatarios/update/index.php',
                        success: function(data) {
                            if (data.success) {
                                ReloadGrid();
                                cancelar();
                            } else {
                                $("#btnCancel").show();
                            }
                        }
                    });
                }
            }

        });
    </script>
    <script>
        $(document).ready(function() {
            $('.chosen-select').chosen();
            $('.chosen-select-deselect').chosen({
                allow_single_deselect: true
            });
            $("#txtDepart").prop("disabled", true);
            $("#txtMunicipio").prop("disabled", true);
/*
            $("#txtCod").change(function() {
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        codigo: $("#txtCod").val(),
                        action: "getDane"
                    },
                    beforeSend: function(x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: "/api/destinatarios/update/index.php",
                    success: function(data) {
                        if (data.success == true) {
                            $("#txtDepart").val(data.departamento);
                            $("#txtMunicipio").val(data.municipio);
                        }
                    }
                });
            });
*/
            $("#txtCod").keyup(function() {
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        codigo: $("#txtCod").val(),
                        action: "getDaneText"
                    },
                    beforeSend: function(x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: "/api/destinatarios/update/index.php",
                    success: function(data) {
                        if (data.success == true) {
                            if(data.resultado == 0)
                            {
                                $("#mensaje_dane").text("CP no existe (se registrará como nuevo)");
                                $("#txtDepart").val("");
                                $("#txtMunicipio").val("");
                                $("#txtDepart").prop("disabled", false);
                                $("#txtMunicipio").prop("disabled", false);
                            }
                            else
                            {
                                $("#mensaje_dane").text("");
                                $("#txtDepart").val(data.departamento);
                                $("#txtMunicipio").val(data.des_municipio);
                                $("#txtDepart").prop("disabled", true);
                                $("#txtMunicipio").prop("disabled", true);
                            }
                        }
                    }
                });
            });

        });
    </script>
    <script type="text/javascript">
        $("#txtCriterio").keyup(function(event) {
            if (event.keyCode == 13) {
                $("#buscarP").click();
            }
        });

            $('#dt-detalles .page').keypress(function(e) {
        if(e.which == 13) {
            ReloadGrid();
        }
    });

    $('#dt-detalles .count').change(function() {       
        ReloadGrid();
    });


    $('.ui-icon-seek-prev').click(function() {
        var p = $("#page").val();

        if(p > 1) p--;

        $("#page").val(p);
        ReloadGrid();
    });

    $('.ui-icon-seek-next').click(function() {

        var p = $("#page").val();

        if(p < parseInt($(".total_pages").text())) p++;

        $("#page").val(p);


        ReloadGrid();
    });


    </script>

