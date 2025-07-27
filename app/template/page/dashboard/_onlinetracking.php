<?php
$listaNCompa = new \AlmacenP\AlmacenP();
$almacenes = new \AlmacenP\AlmacenP();
//$listaCliente = new \Clientes\Clientes();
$listaRuta = new \Ruta\Ruta();
$listaDiaO = new \Venta\Venta();

$listTransportes = new \Transporte\Transporte();
$vendedores = new \Usuarios\Usuarios();


$cve_almacen = $_SESSION['cve_almacen'];

?>
<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

<!-- ClientesxRuta -->
<script src="/js/plugins/footable/footable.all.min.js"></script>

<!-- Peity -->
<script src="/js/plugins/peity/jquery.peity.min.js"></script>

<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.min.js"></script>
<script src="/js/plugins/chosen/chosen.jquery.js"></script>

<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>
<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="/js/plugins/ladda/spin.min.js"></script>
<script src="/js/plugins/ladda/ladda.min.js"></script>
<script src="/js/plugins/ladda/ladda.jquery.min.js"></script>
<script src="/js/select2.js"></script>
<script src="/js/plugins/footable/footable.all.min.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>

<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/bootstrap.min.css" rel="stylesheet">
<link href="/font-awesome/css/font-awesome.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<!-- FooTable -->
<link href="/css/plugins/footable/footable.core.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet" />
<link href="/css/animate.css" rel="stylesheet">
<link href="/css/style.css" rel="stylesheet">
<link href="/css/plugins/footable/footable.core.css" rel="stylesheet">

<!-- Mainly scripts -->
<style type="text/css">
    ul.dropdown-menu.dropdown-menu-right 
    {
        position: absolute;
        left: auto;
        right: 0;
    }
</style>


    <div class="modal fade" id="notificacion" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Notificación a Ruta</h4>
                    </div>
                    <div class="modal-body">
                            <div class="form-group">
                                <label for="rutas_list_modal">Rutas:</label> 
                                <select class="form-control chosen-select" id="rutas_list_modal" name="rutas_list_modal">
                                    <option value="">Seleccione Ruta</option>
                                    <?php 
                                    foreach( $listaRuta->getAll($cve_almacen) AS $r ): 
                                    ?>
                                    <option value="<?php echo $r->cve_ruta; ?>"><?php echo "( ".$r->cve_ruta." ) - ".$r->descripcion; ?></option>
                                    <?php endforeach; ?>

                                </select>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="mensaje">Mensaje:</label> 
                                <textarea class="form-control" id="mensaje_ruta" name="mensaje_ruta" placeholder="Mensaje a Ruta" rows="5"></textarea>
                            </div>
                    </div>
                    <div class="modal-footer">
                        <div style="text-align: right">
                            <button id="btn-enviar" type="button" class="btn btn-primary">Enviar</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

<!-- Barra de nuevo y busqueda -->
<div class="wrapper wrapper-content  animated fadeInRight" id="rut">
    <h3>Online Tracking</h3>
    <div class="row">
        <div class="col-md-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="almacenes">Almacen: </label>
                                <select class="form-control" id="almacenes" name="almacenes" onchange="almacen()">
                                    <option value=" ">Seleccione el Almacen</option>
                                    <?php foreach( $almacenes->getAll() AS $a ): ?>
                                        <option value="<?php echo $a->id; ?>"><?php echo $a->nombre; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="rutas_list">Rutas:</label> 
                                <select class="form-control chosen-select" id="rutas_list" name="rutas_list">
                                    <option value="">Seleccione Ruta</option>
                                    <?php 
                                    foreach( $listaRuta->getAll($cve_almacen) AS $r ): 
                                    ?>
                                    <option value="<?php echo $r->cve_ruta; ?>"><?php echo "( ".$r->cve_ruta." ) - ".$r->descripcion; ?></option>
                                    <?php endforeach; ?>

                                </select>
                            </div>
                        </div>


                    </div>
                    <div class="row">
                        <div class="col-md-4" style="display: none;">
                            <div id="busqueda">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="txtCriterio" style="margin: 10px;" id="txtCriterio" placeholder="Buscar...">
                                    <div class="input-group-btn">
                                        <button  onclick="ReloadGrid()" type="submit" id="buscarA" class="btn btn-primary" style="margin: 10px;">
                                            <span class="fa fa-search"></span> Buscar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="input-group">
                                <button type="button" class="btn btn-primary" style="margin: 10px;" data-toggle="modal" data-target="#notificacion"><i class="fa fa-envelope" aria-hidden="true"></i> Notificación a Ruta</button>
                            </div>
                        </div>

                    </div>

                </div>
                <div class="ibox-content">

                    <div id="mapa" style="overflow-x: scroll;">
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB3w-JXwq-Qjcsec-dP03a46kSd71c6pKQ"></script>
<script type="text/javascript">
var markers = [], lat = "", lon = "", info_map = [];


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
              marcadores, i;

        //console.log("POINT PRUEBA 1", mostrarMarcadores);

          // Colocamos los marcadores en el Mapa de Google 
          var color_point = "";
          for (i = 0; i < marcadores.length; i++) {
              var position = new google.maps.LatLng(marcadores[i][1], marcadores[i][2]);
              bounds.extend(position);

              color_point = "#ff3300";
              if(i == 0) color_point = "#0000ff";

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
              //console.log("POINT PRUEBA 2", i);
          }
        //console.log("POINT PRUEBA 2");
          // Aplicamos el evento 'bounds_changed' que detecta cambios en la ventana del Mapa de Google, también le configramos un zoom de 14 

          var boundsListener = google.maps.event.addListener((map), 'bounds_changed', function(event) {
              this.setZoom(6);
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


<script type="text/javascript">

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
                if (data.success == true) 
                {
                    document.getElementById('almacenes').value = data.codigo.id;
                    $('.chosen-select').chosen();
                    //almacen();
                }
            },
            error: function(res) {
                window.console.log(res);
            }
        });
    }
        almacenPrede();


    function verGpsRutas()
    {
        console.log("verGpsRutas()");


        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                rutas: $("#rutas_list").val(),
                almacen: $("#almacenes").val(),
                action: 'get_gps_bitacora'
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

                datos.forEach(function(destinatario, i)
                {
                        //console.log(i, destinatario[0], destinatario[1], destinatario[2], destinatario[3]);
                        //'["' . $row['direccion'] . '", ' . $row['lat'] . ', ' . $row['lng'] . '],'
                        //markers += '[ "", '+destinatario[2]+','+destinatario[3]+'],';
                        var tooltip = "Sucursal: "+destinatario[6];
                        markers[0] = [tooltip, parseFloat(destinatario[4]), parseFloat(destinatario[5])];
                        info_map[0] = [destinatario[6], destinatario[6]];
                        lat = destinatario[4];
                        lon = destinatario[5];

                        tooltip = "Destinatario: "+destinatario[0]+" | "+destinatario[1];
                        markers[i+1] = [tooltip, parseFloat(destinatario[2]), parseFloat(destinatario[3])];
                        info_map[i+1] = [destinatario[0], destinatario[1]];
                        //lat = destinatario[2];
                        //lon = destinatario[3];
                        //console.log(markers);
                });
                //google.maps.event.addDomListener(window, 'load', initMap);
                //initMapRutas();
                initMap();
                //$("#show_points").text(info_map.length);
                //$("#detalleGpsRutas").modal('show');
            },
            error: function(data){
                console.log("ERROR");
                console.log(data);
            }
        });
    }


    $("#rutas_list").change(function(){
        console.log("rutas_list change");
        verGpsRutas();

    });

</script>


