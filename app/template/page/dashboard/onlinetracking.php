<?php
$listaNCompa = new \AlmacenP\AlmacenP();
$almacenes = new \AlmacenP\AlmacenP();
//$listaCliente = new \Clientes\Clientes();
$listaRuta = new \Ruta\Ruta();
$listaDiaO = new \Venta\Venta();

$listTransportes = new \Transporte\Transporte();
$vendedores = new \Usuarios\Usuarios();


$cve_almacen = $_SESSION['cve_almacen'];

//$confSql = \db()->prepare("SELECT IFNULL(Valor, '') AS api_key_google_maps FROM t_configuraciongeneral WHERE cve_conf = 'api_key_google_maps' LIMIT 1");
//$confSql->execute();
//$api_key_google_maps = $confSql->fetch()['api_key_google_maps'];

$api_key_google_maps = 'AIzaSyC5xF7JtKzw9cTRRXcDAqTThbYnMCiYOVM';

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
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<link href="/css/animate.css" rel="stylesheet">
<link href="/css/style.css" rel="stylesheet">
<link href="/css/plugins/footable/footable.core.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
      integrity="sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0v4LLanw2qksYuRlEzO+tcaEPQogQ0KaoGN26/zrn20ImR1DfuLWnOo7aBA=="
      crossorigin="anonymous" referrerpolicy="no-referrer"/>
<link rel="stylesheet" type="text/css" href="/maps/css/default.css"/>
<link rel="stylesheet" type="text/css" href="/maps/css/component.css"/>
<script src="/maps/js/modernizr.custom.js"></script>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
      integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>


<!-- Mainly scripts -->
<style type="text/css">
    ul.dropdown-menu.dropdown-menu-right {
        position: absolute;
        left: auto;
        right: 0;
    }

    #map {
        height: 100%;
    }

    .contain {
        background: #3d566e;
    }

    .fade:not(.show) {
        opacity: 1;
    }

    .navbar {
        display: block;
    }

    .nav {
        display: inherit;
    }

    .sidebar-collapse {
        background: #2f4050;
    }

    .btn-primary {
        background-color: #1ab394;
        border-color: #1ab394;
        color: #FFFFFF;
        font-size: 14px;
    }

    .dropdown-toggle::after {
        display: none;
        margin-left: 0.255em;
        vertical-align: 0.255em;
        content: "";
        border-top: 0.3em solid;
        border-right: 0.3em solid transparent;
        border-bottom: 0;
        border-left: 0.3em solid transparent;
    }

    .nav > li > a {
        color: #a7b1c2;
        font-weight: 600;
        padding: 14px 20px 14px 25px;
        text-decoration: none;
        font-weight: 400;
        font-size: 12px;
    }

    body {
        background: #2f4050;
    }
</style>


<div class="modal fade" id="notificacion" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">

                    <h4 class="modal-title">Notificación a Ruta</h4>

                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rutas_list_modal">Rutas:</label>
                        <select class="form-control chosen-select" id="rutas_list_modal" name="rutas_list_modal">
                            <option value="">Seleccione Ruta</option>
                            <?php
                            foreach ($listaRuta->getAll() as $r):
                                ?>
                                <option
                                        value="<?php echo $r->cve_ruta; ?>"><?php echo "( " . $r->cve_ruta . " ) - " . $r->descripcion; ?></option>
                            <?php endforeach; ?>

                        </select>
                    </div>
                    <br>
                    <div class="form-group">
                        <label for="mensaje">Mensaje:</label>
                        <textarea class="form-control" id="mensaje_ruta" name="mensaje_ruta"
                                  placeholder="Mensaje a Ruta" rows="5"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <div style="text-align: right">
                        <button id="btn-enviar" type="button" class="btn btn-primary">Enviar</button>
                        <button id="btn-cerrar-modal" type="button" class="btn btn-default" data-dismiss="modal">
                            Cerrar
                        </button>
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
                                    <?php foreach ($almacenes->getAll() as $a): ?>
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
                                    <option value="0">Todos</option>
                                    <?php
                                    foreach ($listaRuta->getAll($cve_almacen) as $r):
                                        ?>
                                        <option
                                                value="<?php echo $r->cve_ruta; ?>"><?php echo "( " . $r->cve_ruta . " ) - " . $r->descripcion; ?></option>
                                    <?php endforeach; ?>

                                </select>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="clientes">Clientes en Ruta:</label>
                                <input type="text" class="form-control" id="nClientes" name="nClientes" disabled>
                            </div>
                        </div>


                    </div>
                    <div class="row">
                        <div class="col-md-4" style="display: none;">
                            <div id="busqueda">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="txtCriterio" style="margin: 10px;"
                                           id="txtCriterio" placeholder="Buscar...">
                                    <div class="input-group-btn">
                                        <button onclick="ReloadGrid()" type="submit" id="buscarA"
                                                class="btn btn-primary" style="margin: 10px;">
                                            <span class="fa fa-search"></span> Buscar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="input-group">
                                <button type="button" id="" class="btn btn-primary" style="margin: 10px;"
                                        data-toggle="modal" data-target="#notificacion"><i class="fa fa-envelope"
                                                                                           aria-hidden="true"></i>
                                    Notificación a Ruta
                                </button>
                            </div>
                        </div>

                    </div>

                </div>
                <div class="ibox-content">


                    <div class="contain">

                        <div class="row">

                            <div class="col-11" style="padding:15px">
                                <div id="map"></div>
                            </div>

                            <div class="col-1" style="height:70vh">

                                <ul class="cbp-vimenu">
                                    <li><a href="#" class="icon-logo">Logo</a></li>
                                    <li><a href="#" data-toggle="modal" data-target="#notificacion"
                                           class="icon-archive">Archive</a></li>
                                    <li><a id="zoom" href="#" class="icon-search">Search</a></li>
                                    <li><a id="delete" href="#" class="icon-pencil">Pencil</a></li>
                                    <!-- Example for active item:
                                                    <li class="cbp-vicurrent"><a href="#" class="icon-pencil">Pencil</a></li>
                                                    -->
                                    <li><a id="gps" href="#" class="icon-location">Location</a></li>
                                    <li><a href="#" class="icon-images">Images</a></li>
                                    <li><a id="near" href="#" class="icon-download">Download</a></li>
                                </ul>

                            </div>

                        </div>


                        <div class="row1-container">
                            <div class="box cyan">
                                <h2>STATUS</h2>
                                <p>Vehiculos: <span id="vehicles"> N/A</span></p>
                                <p>Ultima Actualizacion: <span id="update">N/A </span></p>
                                <img src="/maps/svg/geo.svg" style="width:64px;height:64px" alt="">
                            </div>

                            <div class="box red">
                                <h2>ALERTAS</h2>
                                <p>Activas:</p>
                                <p>Inactivas:</p>
                                <img src="/maps/svg/status.svg" style="width:64px;height:64px" alt="">
                            </div>

                            <div class="box blue">
                                <h2>MONITOREO</h2>
                                <p>Activos:</p>
                                <p>Inactivos:</p>
                                <img src="/maps/svg/cpu.svg" style="width:64px;height:64px" alt="">
                            </div>
                        </div>


                    </div>


                </div>
            </div>
        </div>
    </div>
</div>

<script async type="text/javascript"
        src="https://maps.googleapis.com/maps/api/js?key=<?php echo $api_key_google_maps; ?>&callback=almacenPrede"></script>

<style>
    #mapa {
        height: 50vh;
    }

    .bt {
        margin-right: 10px;
    }

    .btn-blue {
        background-color: blue !important;
        border-color: blue !important;
        color: white !important;
    }

    .ui-jqgrid tr.jqgrow td[aria-describedby="grid-table_accion"] {
        display: table;
        width: 100%;
        text-align: center;
    }

    .ui-jqgrid tr.jqgrow td[aria-describedby="grid-table_accion"] a {
        margin: 0 5px;
    }
</style>
<script type="text/javascript">
    var markers = [], lat = "", lon = "", info_map = [], info_map = [];
    var marks = [];
    var map;
    var geopos = false;
    var t;
    $(document).ready(function () {
        console.log("ready!");
        almacenPrede();
    });

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

        map = new google.maps.Map(document.getElementById('map'), {
            center: {lat: lt, lng: ln},
            zoom: 14,
            mapTypeId: 'roadmap'
        });

        map.setTilt(50);
        console.log("markers initMap = ", markers);
        var marcadores =
            markers
        ;
        console.log("marcadores initMap = ", marcadores);

        console.log("marcadores initMap = ", marcadores);
        var mostrarMarcadores = new google.maps.InfoWindow(),
            marcadores, i, center_almacen;
        var color_point = "";
        console.log("marcadores.length = ", marcadores.length)
        for (i = 0; i < marcadores.length; i++) {
            var position = new google.maps.LatLng(marcadores[i][1], marcadores[i][2]);
            bounds.extend(position);

            if (i == 0) center_almacen = position;

            color_point = "#ff3300";
            const imageStore = {
                url: "/maps/svg/store.svg",
                scaledSize: new google.maps.Size(35, 35)
            };
            if (i == 0) {
                console.log('almacen')
                color_point = "#0000ff";
            } else if (visitado[i] == 'Visita con venta') color_point = "#009900";
            else if (visitado[i] == 'Visita con pedido') color_point = "#009900";
            else if (visitado[i] == 'Visita sin venta') color_point = "#0000ff";
            const image = {
                url: "/maps/svg/cart.svg",
                scaledSize: new google.maps.Size(35, 35)
            };

            marker = new google.maps.Marker({
                position: position,
                map: map,
                icon: (marcadores.length - 1 == i && marcadores.length > 1) ? image
                    : i == 0 ? imageStore
                        : {
                            path: marcadores.length - 1 == i ? google.maps.SymbolPath.CIRCLE : 'M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z',
                            scale: 1, //tamaño
                            strokeColor: '#000', //color del borde
                            strokeWeight: 1, //grosor del borde
                            fillColor: color_point, //color de relleno
                            fillOpacity: 1// opacidad del relleno
                        },
                title: marcadores[i][0]
            });

            // Colocamos la ventana de información a cada Marcador del Mapa de Google
            google.maps.event.addListener(marker, 'click', (function (marker, i) {
                return function () {
                    mostrarMarcadores.open(map, marker);
                }
            })(marker, i));
        }
        map.setCenter(center_almacen);
        var boundsListener = google.maps.event.addListener((map), 'bounds_changed', function (event) {
            this.setZoom(14);
            google.maps.event.removeListener(boundsListener);
        });

    }

    function initMapNoCars() {
        console.log("initMap")
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

        map = new google.maps.Map(document.getElementById('map'), {
            center: {lat: lt, lng: ln},
            zoom: 14,
            mapTypeId: 'roadmap'
        });

        map.setTilt(50);
        console.log("markers initMap = ", markers);
        var marcadores =
            markers
        ;
        console.log("marcadores initMap = ", marcadores);

        console.log("marcadores initMap = ", marcadores);
        var mostrarMarcadores = new google.maps.InfoWindow(),
            marcadores, i, center_almacen;
        var color_point = "";
        console.log("marcadores.length = ", marcadores.length)
        for (i = 0; i < marcadores.length; i++) {
            console.log("marcadores[i]", marcadores[i]);
            var position = new google.maps.LatLng(marcadores[i][1], marcadores[i][2]);
            bounds.extend(position);

            if (i == 0) center_almacen = position;

            color_point = "#ff3300";
            const imageStore = {
                url: "/maps/svg/store.svg",
                scaledSize: new google.maps.Size(35, 35)
            };
            if (i == 0) {
                console.log('almacen')
                color_point = "#0000ff";
            } else if (visitado[i] == 'Visitado') color_point = "#009900";
            else if (visitado[i] == 'Visita Programada') color_point = "#0000ff";

            marker = new google.maps.Marker({
                position: position,
                map: map,
                icon: i == 0 ? imageStore
                    : {
                        path: 'M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z',
                        scale: 1, //tamaño
                        strokeColor: '#000', //color del borde
                        strokeWeight: 1, //grosor del borde
                        fillColor: color_point, //color de relleno
                        fillOpacity: 1// opacidad del relleno
                    },
                title: marcadores[i][0]
            });

            // Colocamos la ventana de información a cada Marcador del Mapa de Google
            google.maps.event.addListener(marker, 'click', (function (marker, i) {
                return function () {
                    mostrarMarcadores.open(map, marker);
                }
            })(marker, i));
        }
        map.setCenter(center_almacen);
        var boundsListener = google.maps.event.addListener((map), 'bounds_changed', function (event) {
            this.setZoom(14);
            google.maps.event.removeListener(boundsListener);
        });

    }

    function redraw() {
        console.log(map)

        var marcadores = markers;
        console.log("marcadorees = ", marcadores);
        var bounds = new google.maps.LatLngBounds();
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
        var currentdate = new Date();
        var datetime = "" + currentdate.getDate() + "/"
            + (currentdate.getMonth() + 1) + "/"
            + currentdate.getFullYear() + " @ "
            + currentdate.getHours() + ":"
            + currentdate.getMinutes() + ":"
            + currentdate.getSeconds();


        $("#vehicles").text(marcadores.length);
        $("#update").text(datetime);
        const image = {
            url: "/maps/svg/cart.svg",
            scaledSize: new google.maps.Size(35, 35)
        };
        //change color to image (svg)
        image.fillColor = "yellow";
        var color_point = "";
        for (i = 1; i < marcadores.length; i++) {
            if (i == marcadores.length - 1) {
                console.log("marcadores[i][1] = ", marcadores[i][1]);
                var position = new google.maps.LatLng(marcadores[i][1], marcadores[i][2]);
                bounds.extend(position);

                color_point = "#ff3300";
                if (i == 0) color_point = "#0000ff";

                const marker = new google.maps.Marker({
                    scaledSize: new google.maps.Size(30, 30),
                    position: position,
                    map: map,
                    icon: image,
                    title: marcadores[i][0]
                });

                marks.push(marker);
                /*
                marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    icon: {
                        icon: image,
                        scale: 1, //tamaño
                        strokeColor: '#000', //color del borde
                        strokeWeight: 1, //grosor del borde
                        fillColor: color_point, //color de relleno
                        fillOpacity:1// opacidad del relleno
                    },
                    title: marcadores[i][0]
                });
                */

                // Colocamos la ventana de información a cada Marcador del Mapa de Google
                google.maps.event.addListener(marker, 'click', (function (marker, i) {
                    return function () {
                        mostrarMarcadores.open(map, marker);
                    }
                })(marker, i));

                // Centramos el Mapa de Google para que todos los marcadores se puedan ver

                // map.fitBounds(bounds);
            }
        }
        //console.log("POINT PRUEBA 2", i);

    }


    function setMapOnAll(map) {
        for (let i = 0; i < marks.length; i++) {
            marks[i].setMap(map);
        }
    }

    function hideMarkers() {
        setMapOnAll(null);
    }


    function almacenPrede() {
        console.log("almacenPrede")
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                idUser: '<?php echo $_SESSION["id_user"]?>',
                action: 'search_almacen_pre'
            },
            beforeSend: function (x) {
                if (x && x.overrideMimeType) {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/almacenPredeterminado/index.php',
            success: function (data) {
                if (data.success == true) {
                    console.log(data);
                    document.getElementById('almacenes').value = data.codigo.id;
                    $('.chosen-select').chosen();
                    almacen(data.codigo.id);
                }
            },
            error: function (res) {
                window.console.log(res);
            }
        });
    }

    almacenPrede();

    function almacen(codigo) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                codigo: codigo,
                action: "load"
            },
            beforeSend: function (x) {
                if (x && x.overrideMimeType) {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/almacenp/update/index.php',
            success: function (data) {
                console.log(data);
                if (data.success == true) {
                    lat = data.latitud;
                    lon = data.longitud;
                    var tooltip = "Sucursal: " + data.nombre;
                    markers[0] = [tooltip, parseFloat(lat), parseFloat(lon)];
                    initMap();
                }
            }
        });

    }

    // function verGpsRutas() {
    //     console.log("verGpsRutas()");
    //     clearTimeout(t);
    //     initMap();
    //     firstcheck();
    //
    //
    // }
    var banderaTimeOut = false;

    function verGpsRutas() {
        console.log("verGpsRutas()");
        markers = [];
        info_map = [];
        $('#nClientes').text('');
        $dias = '';
        //saber que dia es hoy
        var d = new Date();
        var weekday = new Array(7);
        weekday[0] = "Domingo";
        weekday[1] = "Lunes";
        weekday[2] = "Martes";
        weekday[3] = "Miercoles";
        weekday[4] = "Jueves";
        weekday[5] = "Sabado";
        weekday[6] = "Domingo";
        var n = weekday[d.getDay()];
        switch (n) {
            case "Lunes":
                $dias = "IFNULL(RelDayCli.Lu, 20000)";
                break;
            case "Martes":
                $dias = "IFNULL(RelDayCli.Ma, 20000)";
                break;
            case "Miercoles":
                $dias = "IFNULL(RelDayCli.Mi, 20000)";
                break;
            case "Jueves":
                $dias = "IFNULL(RelDayCli.Ju, 20000)";
                break;
            case "Viernes":
                $dias = "IFNULL(RelDayCli.Vi, 20000)";
                break;
            case "Sabado":
                $dias = "IFNULL(RelDayCli.Sa, 20000)";
                break;
            case "Domingo":
                $dias = "IFNULL(RelDayCli.Do, 20000)";
                break;
        }

        var ruta = $("#rutas_list").val();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                rutas: ruta,
                dias: $dias,
                almacen: $("#almacenes").val(),
                action: 'get_gps'
            },
            beforeSend: function (x) {
                if (x && x.overrideMimeType) {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/destinatarios/update/index.php',
            success: function (data) {
                console.log("SUCCESS", data);
                console.log('datos get_gps', data);
                if (data == null) {
                    almacenPrede();
                    swal("Aviso", "No hay Datos disponibles para mostrar", "error");
                    banderaTimeOut = false;
                    return;
                }

                banderaTimeOut = true;
                var datos = data.rows;
                markers = [];
                info_map = [];
                credito_contado = [];
                visitado = [];
                $('#nClientes').val(datos.length);
                datos.forEach(function (destinatario, i) {
                    var tooltip = "Sucursal: " + destinatario[6];
                    markers[0] = [tooltip, parseFloat(destinatario[4]), parseFloat(destinatario[5])];
                    info_map[0] = [destinatario[6], destinatario[6]];
                    credito_contado[0] = 0;
                    lat = destinatario[4];
                    lon = destinatario[5];

                    var secuencia = destinatario[7];
                    var credito = destinatario[8];
                    var saldo_deudor = destinatario[9];
                    var clasificacion = destinatario[10];
                    var estatus = destinatario[10];
                    if (secuencia != '') secuencia = " | Secuencia: " + secuencia;
                    if (clasificacion != '') clasificacion = " \nClasificación: " + clasificacion;
                    if (credito == 1) saldo_deudor = " \nSaldo Deudor: " + saldo_deudor;
                    if (estatus != '') estatus = " | Estatus: " + estatus;

                    tooltip = "Cliente: " + destinatario[0] + " | " + destinatario[1] + estatus;
                    markers[i + 1] = [tooltip, parseFloat(destinatario[2]), parseFloat(destinatario[3])];
                    info_map[i + 1] = [destinatario[0], destinatario[1]];
                    credito_contado[i + 1] = credito;
                    visitado[i + 1] = destinatario[10];
                });
                console.log("markers 123", markers);
                $("#show_points").text(info_map.length);
                $("#detalleGpsRutas").modal('show');
                checkroutes();
            },
            error: function (data) {
                console.log("ERROR");
                console.log(data);
            }
        });
    }

    function checkroutes() {
        console.log("checkroutes()");
        var ruta = $("#rutas_list").val();
        $.ajax({
            type: "POST",
            dataType: "json",
            async: false,
            data: {
                rutas: ruta,
                almacen: $("#almacenes").val(),
                action: 'get_gps_bitacora2'
            },
            beforeSend: function (x) {
                if (x && x.overrideMimeType) {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/destinatarios/update/index.php',
            success: function (data) {
                ajaxSuccess = true;
                console.log(ajaxSuccess)
                console.log("SUCCESS 3", data);
                if (data == null) {
                    banderaTimeOut = false;
                    initMapNoCars();
                    swal("Aviso", "No hay Datos disponibles para mostrar, el dia operativo de la ruta no ha iniciado", "error");
                    return;
                }
                console.log('bitacora', data)
                var currentdate = new Date();
                var datetime = "" + currentdate.getDate() + "/"
                    + (currentdate.getMonth() + 1) + "/"
                    + currentdate.getFullYear() + " @ "
                    + currentdate.getHours() + ":"
                    + currentdate.getMinutes() + ":"
                    + currentdate.getSeconds();


                $("#vehicles").text(data.rows.length);
                $("#update").text(datetime);
                var datos = data.rows;
                datos.forEach(function (destinatario, i) {
                    console.log(i, destinatario[0], destinatario[1], destinatario[2], destinatario[3]);
                    var tooltip = destinatario[0];
                    //verifica si no se repite el auto
                    var existe = false;
                    for (var i = 0; i < markers.length; i++) {
                        if (markers[i][0] == tooltip && markers[i][1] == parseFloat(destinatario[1]) && markers[i][2] == parseFloat(destinatario[2])) {
                            existe = true;
                            break;
                        }
                    }
                    if (!existe) {
                        //valida que no haya mas de 1 auto
                        for (var i = 0; i < markers.length; i++) {
                            if (markers[i][0] == tooltip) {
                                markers.splice(i, 1);
                                info_map.splice(i, 1);
                                break;
                            }
                        }
                        markers.push([tooltip, parseFloat(destinatario[1]), parseFloat(destinatario[2])]);
                        info_map.push([destinatario[0], destinatario[0]]);
                        lat = destinatario[1];
                        lon = destinatario[2];
                    }
                });
                if (markers.length > 2) {
                    for (var i = 0; i < markers.length; i++) {
                        if (markers[i][0].indexOf("AUTO") > -1) {
                            initMap();
                            break;
                        }
                    }
                }
            },
            error: function (data) {
                initMapNoCars();
                console.log("ERROR");
                console.log(data);
            }
        });

    }

    var ajaxSuccess = false;
    $("#rutas_list").change(function () {
        console.log("rutas_list change");
        $('#nClientes').val(0);
        banderaTimeOut = false;
        verGpsRutas();
        console.log('banderaTimeOut', banderaTimeOut);
        setInterval(function () {
            if (banderaTimeOut) {
                console.log('ajaxSuccess', ajaxSuccess);
                if (ajaxSuccess) {
                    ajaxSuccess = false;
                    console.log('banderaTimeOut', banderaTimeOut);
                    verGpsRutas();
                }
            }
        }, 20000);
    });

    $("#btn-enviar").click(function () {


        var ruta = $("#rutas_list_modal").val();
        var msg = $("#mensaje_ruta").val();
        console.log("SEND NOT");

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                'function': 'strNotify',
                'str': ruta,
                'msg': msg
            },
            beforeSend: function (x) {
                if (x && x.overrideMimeType) {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/ws/webservicenot.php',
            success: function (data) {
                $("#notificacion").modal('hide')
                console.log("Respuesta " + JSON.stringify(data));
                if (data.success == true) {
                    swal("Aviso", "Notificación enviada", "success");
                    $('#btn-cerrar-modal').click();
                }
            },
            error: function (res) {
                swal("Aviso", "Error al enviar notificación", "error");
            }
        });


    });

    $("#gps").click(function () {
        verGpsRutas();
    });


    var DELAY = 700, clicks = 0, timer = null;

    $("#delete").on("click", function (e) {
        hideMarkers();
        clearTimeout(t);
    });


    $("#near").on("click", function (e) {
        let lat = markers[0][1];
        let lng = markers[0][2];
        const center = new google.maps.LatLng(lat, lng);

        map.panTo(center);
    });

    $("#zoom").on("click", function (e) {

        clicks++;  //count clicks

        if (clicks === 1) {

            timer = setTimeout(function () {

                map.setZoom(map.getZoom() + 1);
                clicks = 0;

            }, DELAY);

        } else {

            clearTimeout(timer);

            clicks = 0;


        }

    })
        .on("dblclick", function (e) {

            map.setZoom(map.getZoom() - 1);  //cancel system double-click event
        });


</script>


