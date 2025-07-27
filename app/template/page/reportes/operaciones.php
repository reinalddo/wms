<?php
$listaAlm = new \AlmacenP\AlmacenP();
$pedidos = new \Pedidos\Pedidos();
$almacenes = $listaAlm->getAll();

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

?>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet">
<link href="/css/plugins/selectize/selectize.css" rel="stylesheet"/>
<link href="/css/plugins/selectize/selectize.bootstrap3.css" rel="stylesheet"/>
<link href="/css/plugins/iCheck/custom.css" rel="stylesheet"/>

<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/moment.js"></script>
<script src="/js/moment-with-locales.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">


<!-- Peity -->
<script src="/js/plugins/peity/jquery.peity.min.js"></script>

<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.js"></script>

<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
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
<!-- Mainly scripts -->

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCc_ej3xfMCxtyW7oYhgUIw8Rk_NK_ASR0"></script>

<!--<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAAKrvy7BqPz9GOwH-Ss3yNwmBeykitNhI"></script>-->
<!-- &callback=initMap -->
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
    <h3>Log de Operaciones</h3>
    <div class="row">
        <div class="col-md-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
    <?php
    $cliente_almacen_style = ""; $cve_cliente = "";
    if($_SESSION['es_cliente'] == 1) 
    {
      $cliente_almacen_style = "style='display: none;'";
      $cve_cliente = $_SESSION['cve_cliente'];
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
                            <div class="form-group">
                                <label>Fecha Inicio</label>
                                <div class="input-group date" id="data_1">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="fechai" type="text" class="form-control" value="">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Fecha Fin</label>
                                <div class="input-group date" id="data_2">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="fechaf" type="text" class="form-control" value="">
                                </div>
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
                    </div>

                <hr>
<!--
<div style="height: 50px;">
    <strong>Tipo de Salida de Almacén</strong><br>
    <div class="col-md-1"><label style="cursor: pointer;"><input type="radio" name="tipo_salida" id="tipo_todos" checked value="todos">&nbsp;&nbsp;&nbsp;Todos</label></div>
    <div class="col-md-2"><label style="cursor: pointer;"><input type="radio" name="tipo_salida" id="tipo_devolucion" value="devolucion">&nbsp;&nbsp;&nbsp;Devolución a Proveedor</label></div>
    <div class="col-md-3"><label style="cursor: pointer;"><input type="radio" name="tipo_salida" id="tipo_muestras" value="muestras">&nbsp;&nbsp;&nbsp;Muestras, Obsequios, Promociones</label></div>
    <div class="col-md-3"><label style="cursor: pointer;"><input type="radio" name="tipo_salida" id="tipo_ajustes" value="ajustes">&nbsp;&nbsp;&nbsp;Ajustes por Pedidos u Ordenes de Trabajo</label></div>
    <div class="col-md-3"><label style="cursor: pointer;"><input type="radio" name="tipo_salida" id="tipo_merma" value="merma">&nbsp;&nbsp;&nbsp;Merma, Destrucción</label></div>
</div>
-->
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
                    <h4>Folio <span id="n_folio">0</span></h4>
                    <br>
                    <div style="text-align: center;"><strong id="tipo_label">Devolución</strong></div>
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
                if (data.success == true) 
                {
                    document.getElementById('almacen_filter').value = data.codigo.id;
                    setTimeout(function() {
                        ReloadGrid();
                    }, 1000);
                }
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
            url:'/api/reportes/lista/operaciones.php',
            datatype: 'local',
            shrinkToFit: false,
            height:'auto',
            mtype: 'POST',
            colNames:[ 'Modulo','Usuario','Fecha','Operacion', 'Dispositivo', 'Observaciones'],
            colModel:[
            {name:'modulo',index:'modulo', align:'left', editable:false, sortable:false,width:250},
            {name:'usuario',index:'usuario', align:'left', editable:false, sortable:false,width:100},
            {name:'fecha',index:'fecha',  sortable:false,width:150, align:"center"},
            {name:'operacion',index:'operacion',  sortable:false,width:400, align:"left"},
            {name:'dispositivo',index:'dispositivo',  sortable:false,width:100, align:"left"},
            {name:'observaciones',index:'observaciones', editable:false, sortable:false,width:350,align:"left"},
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
            },
            //loadonce: true,
            //loadComplete: almacenPrede()
        });

        $(grid_selector).jqGrid('navGrid',  "#grid-pager",{edit: false, add: false, del: false, search: false},{height: 200, reloadAfterSubmit: true});
      
        function getActions(cell, options, row)
        {
            var folio = row[1];
            var tipo_salida = row[3];
            var cve_cia = <?php echo $_SESSION['cve_cia']; ?>;
            //onclick="printExcel(${id},'${folio}')"printPDFDiscrepancias
            //<a href="/embarques/exportar?id=${id}&folios=${folio}" title="Imprimir ASN"><i class="fa fa-file-excel-o"></i></a>
            var html = `<a href="#" onclick="verDetalle('${folio}','${tipo_salida}')" title="Ver Detalle"><i class="fa fa-search"></i></a>&nbsp;&nbsp;&nbsp;`;
            html += `<a href="/api/koolreport/excel/salidas/export.php?folio=${folio}&cve_cia=${cve_cia}&tipo_salida=${tipo_salida}" target="_blank" title="Reporte de Salidas"><i class="fa fa-file-excel-o"></i></a>&nbsp;&nbsp;&nbsp;`;
/*
                html += `<a href="/api/koolreport/export/reportes/embarques/reporte_embarques?id=${id}&cve_cia=${cve_cia}" target="_blank" title="Entregas Programadas"><i class="fa fa-file-pdf-o"></i></a>&nbsp;&nbsp;&nbsp;`;
*/
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

    $("input[name=tipo_salida]").click(function(){ReloadGrid();});

    function ReloadGrid() 
    {
        console.log("cve_cliente = ", $("#cve_cliente").val());
        console.log("cve_proveedor = ", $("#cve_proveedor").val());
        var almacen = $("#almacen_filter").val();
        console.log("almacen_filter = ", almacen);
        if(almacen != '')
        {
          console.log("search5 = ", $("#txtCriterio").val());
            $('#grid-table').jqGrid('clearGridData')
                .jqGrid('setGridParam', {postData: {
                    search: $("#txtCriterio").val(),
                    fecha_inicio: $("#fechai").val(),
                    fecha_fin: $("#fechaf").val(),
                    almacen: almacen,
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
    function ReloadDetalle(folio, tipo_salida) 
    {
        console.log("folio = ", folio);
        $("#n_folio").text(folio);
        $("#tipo_label").text(tipo_salida);

        $('#grid-table2').jqGrid('clearGridData')
        .jqGrid('setGridParam', {postData: {
            action: 'detalle',
            folio: folio
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
            url:'/api/reportes/lista/operaciones.php',
            datatype: 'local',
            shrinkToFit: false,
            height:'auto',
            mtype: 'POST',
            colNames:['Clave','Articulo', 'Lote | Serie', 'Caducidad', 'Cantidad', 'Status', 'LP'],
            colModel:[
                {name:'clave',index:'clave', editable:false, sortable:false,width:120},
                {name:'articulo',index:'articulo', editable:false, sortable:false,width:200},
                {name:'lote_serie',index:'lote_serie', editable:false, sortable:false,width:150},
                {name:'caducidad',index:'caducidad', align:'center', editable:false, sortable:false,width:100},
                {name:'cantidad',index:'cantidad', editable:false, sortable:false,width:120},
                {name:'status',index:'status', editable:false, sortable:false,width:150},
                {name:'lp',index:'lp', editable:false, sortable:false,width:200}
            ],
            rowNum:30,
            rowList:[10,30,40,50],
            pager: pager_selector,
            sortname: 'folio',
            viewrecords: true,
            sortorder: "desc",
            loadComplete: function(data){console.log("Detalle = ", data);},
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

    function verDetalle(folio, tipo_salida)
    {
      $("#detalleModal").modal('show');
      ReloadDetalle(folio, tipo_salida);
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

                MarcarEntregadoOK(folio);

            });

    }

    function MarcarEntregadoOK(folio)
    {
    console.log("MarcarEntregado = ", folio);

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                folio: folio,
                action: 'marcar_entregado'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url:'/api/reportes/lista/operaciones.php',
            success: function(data) {
                //console.log("SUCCESS");
                //console.log(data);
                swal("Éxito", "Folio #"+folio+" marcado como Entregado", "success");
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
            url:'/api/reportes/lista/operaciones.php',
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
            url:'/api/reportes/lista/operaciones.php',
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
                    console.log(i, destinatario[0], destinatario[1], destinatario[2], destinatario[3], destinatario[4], destinatario[5]);
                    //'["' . $row['direccion'] . '", ' . $row['lat'] . ', ' . $row['lng'] . '],'
                    //markers += '[ "", '+destinatario[2]+','+destinatario[3]+'],';
                    var tooltip = "Entrega "+id+"\n"+
                                  destinatario[0]+" | "+destinatario[1]+"\n"+
                                  "Pedido "+destinatario[4];

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
        form.setAttribute('action', '/api/reportes/lista/operaciones.php');
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
