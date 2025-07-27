<?php
$listaNCompa = new \AlmacenP\AlmacenP();
$almacenes = new \AlmacenP\AlmacenP();
//$listaCliente = new \Clientes\Clientes();
$listaRuta = new \Ruta\Ruta();
$listaDiaO = new \Venta\Venta();

$listTransportes = new \Transporte\Transporte();
$vendedores = new \Usuarios\Usuarios();


$cve_almacen = $_SESSION['cve_almacen'];
$listTransportes = \db()->prepare("SELECT *  FROM t_transporte WHERE Activo = 1 and id_almac = (SELECT id FROM c_almacenp WHERE clave = '$cve_almacen')");
$listTransportes->execute();
$listTransportes = $listTransportes->fetchAll(PDO::FETCH_ASSOC);

$vere = \db()->prepare("SELECT * from t_profiles as a where id_menu=20 and id_submenu=45 and id_role='" . $_SESSION["perfil_usuario"] . "'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);

$agre = \db()->prepare("SELECT * from t_profiles as a where id_menu=20 and id_submenu=46 and id_role='" . $_SESSION["perfil_usuario"] . "'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);

$edita = \db()->prepare("SELECT * from t_profiles as a where id_menu=20 and id_submenu=47 and id_role='" . $_SESSION["perfil_usuario"] . "'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);

$borra = \db()->prepare("SELECT * from t_profiles as a where id_menu=20 and id_submenu=48 and id_role='" . $_SESSION["perfil_usuario"] . "'");
$borra->execute();
$borrar = $borra->fetchAll(PDO::FETCH_ASSOC);

$nuevos_pedidos = new \NuevosPedidos\NuevosPedidos();

?>
<!--
<meta http-equiv='cache-control' content='no-cache'>
<meta http-equiv='expires' content='0'>
<meta http-equiv='pragma' content='no-cache'>
-->
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
<script src="https://momentjs.com/downloads/moment.js"></script>
<script src="https://momentjs.com/downloads/moment-with-locales.js"></script>
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>

<script src="/js/datatables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>

<link href="/css/datatables.min.css" rel="stylesheet"/>

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
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Mainly scripts -->
<style type="text/css">
    ul.dropdown-menu.dropdown-menu-right {
        position: absolute;
        left: auto;
        right: 0;
    }

    #tabla-semaforo {
        table-layout: auto;
        width: 100% !important;
    }

    #tabla-semaforo th,
    #tabla-semaforo td {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #tabla-semaforo th.column-white, td.column-white {
        background-color: white;
    }

    #tabla-semaforo th.column-blue-light, td.column-blue-light {
        background-color: #cae4ff;
    }

    #tabla-semaforo th.column-blue, td.column-blue {
        background-color: #62a1ff;
    }

    #tabla-semaforo th.column-yellow, td.column-yellow {
        background-color: #f6da7e;
    }

    #tabla-semaforo th.column-orange, td.column-orange {
        background-color: #ffb768;
    }

    #tabla-semaforo th.column-green-light, td.column-green-light {
        background-color: #b3ffb3;
    }

    #tabla-semaforo th.column-red-light, td.column-red-light {
        background-color: #ff9999;
    }

    #tabla-semaforo tbody td {
        /*    Center text*/
        text-align: center;
    }

    #tabla-semaforo th.rotated-text {
        position: relative;
        height: 140px;
        white-space: nowrap;
        /*padding: 0 !important;*/
    }

    #tabla-semaforo th.rotated-text > div {
        position: absolute;
        top: 100%;
        left: 50%;
        transform: rotate(-90deg) translateY(-50%);
        transform-origin: 0 0;
    }

    #tabla-semaforo th.rotated-text > div > span {
        display: inline-block;
        padding: 0px 15px;
        padding-left: 5px;
    }
</style>

<input type="hidden" name="folios_grupo" id="folios_grupo" value="">


<!-- Barra de nuevo y busqueda -->
<div class="wrapper wrapper-content  animated fadeInRight" id="rut">
    <h3>Semaforo de Ventas</h3>
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
                                        <option value="<?php echo $a->id; ?>"><?php echo "(" . $a->clave . ") - " . $a->nombre; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="rutas_list">Rutas:</label>
                                <select class="form-control chosen-select" id="rutas_list" name="rutas_list">
                                    <option value="">Seleccione Ruta</option>
                                    <option selected value="todas">Todas las Rutas</option>
                                    <?php
                                    foreach ($listaRuta->getAll($cve_almacen) as $r):
                                        ?>
                                        <option value="<?php echo $r->cve_ruta; ?>"><?php echo "( " . $r->cve_ruta . " ) - " . $r->descripcion; ?></option>
                                    <?php endforeach; ?>

                                </select>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="diao_list">Día Operativo:</label>
                                <select class="form-control chosen-select" id="diao_list" name="diao_list">
                                    <option value="">Seleccione Día Operativo</option>
                                    <?php
                                    foreach ($listaDiaO->getAllDiaO($cve_almacen) as $r):
                                        ?>
                                        <option value="<?php echo $r->DiaO; ?>"><?php echo $r->DiaO; ?></option>
                                    <?php endforeach; ?>

                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">

                            <div class="form-group">
                                <label>Fecha de Consulta:</label>
                                <div class="input-group date" id="data_1">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input id="fechaini" type="text" class="form-control"
                                           value=""><?php // echo $fecha_semana; ?>
                                </div>
                            </div>

                        </div>
                        <!--                        Button Generar Reporte-->
                        <div class=" col-md-2" style="margin-top: 25px;">
                            <a type="button" class="btn btn-primary" id="btnGenerar" onclick="generarReporte()">
                                <span class="fa fa-search"></span> Generar Reporte
                            </a>
                        </div>
                        <div class=" col-md-2" style="margin-top: 25px;">
                            <a href="#" id="generarExcelDescarga" class="btn btn-primary">
                                <span class="fa fa-file-excel-o"></span> Exportar Tabla a XLS
                            </a>
                        </div>

                    </div>
                    <br>

                    <br>
                    <div class="row" style="text-align: center;display: none;">
                        <br>
                        <b>Importe: </b><span id="timporte"></span><b> | </b><b>IVA: </b><span id="tiva"></span><b>
                            | </b>
                        <b>Descuento: </b><span id="tdescuento"></span><b> | </b><b>Total: </b><span id="ttotal"></span><b>
                            | </b>
                        <b>Total C: </b><span id="ttotalc"></span><b> | </b><b>Total P: </b><span
                                id="ttotalp"></span><b> | </b>
                        <b>Promo C: </b><span id="tpromoc"></span><b> | </b><b>Promo P: </b><span
                                id="tpromop"></span><b> | </b>
                        <b>Obseq C: </b><span id="tobseqc"></span><b> | </b><b>Obseq P: </b><span id="tobseqp"></span>
                    </div>
                    <div class="row" style="text-align: center;display: none;">
                        <br>
                        <b>Credito: </b><span id="tcredito">0.00</span><b> | </b>
                        <b>Cobranza: </b><span id="tcobranza">0.00</span><b> | </b>
                        <b>Adeudo: </b><span id="tadeudo">0.00</span>
                    </div>

                    <br>


                </div>
                <div class="ibox-content">
                    <div style="width: 100%; overflow-y: auto; overflow-x: auto;">
                        <section>
                            <h2>Semaforo de Ventas</h2>
                            <table id="tabla-semaforo"
                                   class="table table-striped table-bordered table-hover dataTables-example">
                                <thead>
                                <tr>
                                    <th class="column-white rotated-text">
                                        <div><span>Rutas</span></div>
                                    </th> <!-- column-white -->
                                    <th class="column-blue-light rotated-text">
                                        <div><span>Agente</span></div>
                                    </th> <!-- column-blue-light -->
                                    <th style="width: 100% !important;" class="column-yellow rotated-text">
                                        <div><span>VISITAS<br> PROGRAMADAS</span></div>
                                    </th><!-- column-yellow -->
                                    <th class="column-yellow rotated-text">
                                        <div><span>VISITAS<br> REALIZADAS</span></div>
                                    </th><!-- column-yellow -->
                                    <th class="column-yellow rotated-text">
                                        <div><span>VISITAS A CLIENTES<br> PROGRAMADOS</span></div>
                                    </th><!-- column-yellow -->
                                    <th class="column-yellow rotated-text">
                                        <div><span>VISITAS A CLIENTES <br>NO PROGRAMADOS</span></div>
                                    </th>
                                    <!-- column-yellow -->
                                    <th class="column-yellow rotated-text">
                                        <div><span>CLIENTES C/PEDIDO<br> O VENTA</span></div>
                                    </th><!-- column-yellow -->
                                    <th class="column-yellow rotated-text">
                                        <div><span>CLIENTES SIN VENTA<br> O SIN PEDIDO</span></div>
                                    </th><!-- column-yellow -->
                                    <th class="column-green-light rotated-text">
                                        <div><span>CLIENTES <br>SIN PROMOCION</span></div>
                                    </th>
                                    <!-- column-green-light -->
                                    <th class="column-orange rotated-text">
                                        <div><span>CLIENTES <br>CON PROMOCION</span></div>
                                    </th><!-- column-orange -->
                                    <th class="column-blue rotated-text">
                                        <div><span>CLIENTES <br>S/VISITA</span></div>
                                    </th><!-- column-blue -->
                                    <th class="column-blue rotated-text">
                                        <div><span>EFECTIVIDAD <br>* VISITAS</span></div>
                                    </th><!-- column-blue -->
                                    <th class="column-blue rotated-text">
                                        <div><span>EFECTIVIDAD * VENTA<br> * VISITA</span></div>
                                    </th><!-- column-blue -->
                                    <th class="column-yellow rotated-text">
                                        <div><span>CAJAS PREVENDIDAS <br>o VENDIDAS</span></div>
                                    </th><!-- column-yellow -->
                                    <th class="column-blue-light rotated-text">
                                        <div><span>EFECTIVO</span></div>
                                    </th><!-- column-blue-light -->
                                    <th class="column-blue-light rotated-text">
                                        <div><span>CREDITO</span></div>
                                    </th><!-- column-blue-light -->
                                    <th class="column-blue-light rotated-text">
                                        <div><span>COBRANZA</span></div>
                                    </th><!-- column-blue-light -->
                                    <th class="column-blue-light rotated-text">
                                        <div><span>DESCUENTOS</span></div>
                                    </th><!-- column-blue-light -->
                                    <th class="column-yellow rotated-text">
                                        <div><span>PRESUPUESTO DIARIO</span></div>
                                    </th><!-- column-yellow -->
                                    <th class="column-yellow rotated-text">
                                        <div><span>DIFERENCIA</span></div>
                                    </th><!-- column-yellow -->
                                    <th class="column-orange rotated-text">
                                        <div><span>PROMOCIONES</span></div>
                                    </th>
                                    <th class="column-green-light rotated-text">
                                        <div><span>CAJAS PREVENDIDAS <br>O VENDIDAS A DETALLE</span></div>
                                    </th>
                                    <!-- column-green -->
                                    <th class="column-green-light rotated-text">
                                        <div><span>% DE PREVENTA <br>A DETALLE</span></div>
                                    </th> <!-- column-green -->
                                    <th class="column-orange rotated-text">
                                        <div><span>CAJAS PREVENDIDAS<br> CON PROMOCION</span></div>
                                    </th>
                                    <!-- column-orange -->
                                    <th class="column-orange rotated-text">
                                        <div><span>% DE CAJAS PREVENDIDAS<br> CON PROMOCION</span></div>
                                    </th>
                                    <!-- column-orange -->
                                    <th class="column-yellow rotated-text">
                                        <div><span>TOTAL DE CAJAS QUE<br> LLEVA CADA ZONA</span></div>
                                    </th>
                                    <!-- column-yellow -->
                                    <th class="column-green-light rotated-text">
                                        <div><span>Total Carga <br>En Kilos</span></div>
                                    </th> <!-- column-green -->
                                    <th class="column-red-light rotated-text">
                                        <div><span>Los 3 Producto<br> mas vendido</span></div>
                                    </th> <!-- column-red -->
                                    <th class="column-red-light rotated-text">
                                        <div><span>Los 3 Sabores<br> mas vendido</span></div>
                                    </th> <!-- column-red -->
                                    <th class="column-blue-light rotated-text">
                                        <div><span>Inicio_Operativo</span></div>
                                    </th> <!-- column-blue-light -->
                                    <th class="column-blue-light rotated-text">
                                        <div><span>Inicio_Primer_cliente</span></div>
                                    </th><!-- column-blue-light -->
                                    <th class="column-blue-light rotated-text">
                                        <div><span>Tiempo_Traslado</span></div>
                                    </th><!-- column-blue-light -->
                                    <th class="column-blue-light rotated-text">
                                        <div><span>Cierre_UltimoCliente</span></div>
                                    </th><!-- column-blue-light -->
                                    <th class="column-blue-light rotated-text">
                                        <div><span>Cierre_Operativo</span></div>
                                    </th><!-- column-blue-light -->
                                    <th class="column-blue-light rotated-text">
                                        <div><span>Ultimo_Tiempo_Traslado</span></div>
                                    </th><!-- column-blue-light -->
                                    <th class="column-blue-light rotated-text">
                                        <div><span>Tiempo de ruta <br>(Inicio de operación <br>a Cierre de
                                        operación)</span></div>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </section>
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
                    <h4 class="modal-title">Importar</h4>
                </div>
                <div class="modal-body">
                    <form id="form-import" action="../../../../../index.php" method="post"
                          enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Seleccionar archivo excel para importar</label>
                            <input type="file" name="file" id="file" class="form-control" required>
                        </div>
                    </form>
                    <div class="col-md-12">
                        <div class="progress" style="display:none">
                            <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar"
                                 aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                                <div class="percent">0%</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div style="text-align: right">
                        <button id="btn-import" type="button" class="btn btn-primary">Importar</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $('#tabla-semaforo').DataTable({
        language: {
            url: '/assets/plugins/DataTable/spanish.json'
        },
        searching: true,
        "paging": true,
        autoWidth: true,
    });

    $('#avanzada').on('click', function () {
        $("#busqueda")[0].style.display = 'none';
    });
    $('#simple').on('click', function () {
        $("#busqueda")[0].style.display = 'block';
    });

    var click_from_grupos = false;

    //almacenPrede();
    function almacenPrede() {
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
                    document.getElementById('almacenes').value = data.codigo.id;
                    $('.chosen-select').chosen();
                    //almacen();
                    var fecha = new Date();
                    var dia = fecha.getDate();
                    var mes = fecha.getMonth() + 1;
                    if (mes < 10) {
                        mes = '0' + mes;
                    }
                    var anio = fecha.getFullYear();
                    var fechaHoy = dia + '-' + mes + '-' + anio;
                    $('#fechaini').val(fechaHoy);
                    generarReporte();
                }
            },
            error: function (res) {
                window.console.log(res);
            }
        });
    }


    almacenPrede();
    var datosTabla = [];
    var datosTotalTabla = [];
    var datosExcel = [];

    function generarReporte() {
        var diao_list = $('#diao_list').val();
        var fechaini = $('#fechaini').val();
        var almacen = $('#almacenes').val();
        var ruta = $('#rutas_list').val();

        if (diao_list == '' && fechaini == '') {
            Swal.fire({
                icon: 'error',
                title: 'No se pudo cargar la tabla',
                text: 'Debe seleccionar un Dia Operativo o Fecha a consultar',
            });
            return false;
        }
        if (diao_list != '' && fechaini != '') {
            Swal.fire({
                icon: 'error',
                title: 'No se pudo cargar la tabla',
                text: 'Debe seleccionar un Dia Operativo o Fecha a consultar, no ambos',
            });
            return false;
        }
        datosTabla = [];
        datosTotalTabla = [];
        $.ajax({
            url: "/api/reportesRutas/lista/semaforo.php",
            type: "POST",
            dataType: "json",
            async: true,
            data: {
                ruta: ruta,
                diao: diao_list,
                almacen: almacen,
                accion: "obtener_reporte_semaforo",
                fecha: fechaini
            },
            success: function (data) {
                if (data.status == "success") {
                    datosTablaSemaforo = [];
                    datosTotales = {
                        VisitasProgramadas: 0,
                        VisitasRealizadas: 0,
                        VisitasClientesProgramados: 0,
                        VisitasClientesNoProgramados: 0,
                        ClientesVisitaVenta: 0,
                        ClientesVisitaNoVenta: 0,
                        ClientesPromocion: 0,
                        ClientesSinPromocion: 0,
                        ClientesSinVisita: 0,
                        EfectividadxVisita: 0,
                        EfectividadxVisitaxVenta: 0,
                        CajasPrevendidasVendidas: 0,
                        Efectivo: 0,
                        Credito: 0,
                        Cobranza: 0,
                        Descuentos: 0,
                        PresupuestoDiario: 0,
                        Diferencia: 0,
                        Promociones: 0,
                        Obsequios: 0,
                        Pilones: 0,
                        CajasPrevendidasVendidasDetalle: 0,
                        PorcentajeCajasPrevendidasVendidas: 0,
                        CajasPrevendidasVendidasPromocion: 0,
                        PorcentajeCajasPrevendidasVendidasPromocion: 0,
                        TotalCajasZona: 0,
                        TotalCargaKilos: 0,
                    }
                    console.log(datosTotales);
                    datosIniciales = data.data;
                    if (datosIniciales.length > 0) {
                        for (i = 0; i < datosIniciales.length; i++) {
                            var efectividadxVisita = 0;
                            var efectividadxVisitaxVenta = 0;
                            if (datosIniciales[i]['visitas_programadas'] > 0 && datosIniciales[i]['visitas_clientes_programados'] > 0) {
                                efectividadxVisita = (100 / datosIniciales[i]['visitas_programadas']) * datosIniciales[i]['visitas_clientes_programados'];
                            }
                            if (datosIniciales[i]['visitas_realizadas'] > 0 && datosIniciales[i]['ClientesVisitaVenta'] > 0) {
                                efectividadxVisitaxVenta = (datosIniciales[i]['ClientesVisitaVenta'] * 100) / datosIniciales[i]['visitas_realizadas'];
                            }
                            cajasPrevendidasDetalle = datosIniciales[i]['Cajas'] - datosIniciales[i]['CajasVentaProm'];
                            datosTablaSemaforo.push(
                                {
                                    Ruta: datosIniciales[i]['cve_ruta'],
                                    Agente: datosIniciales[i]['nombre_completo'],
                                    VisitasProgramadas: datosIniciales[i]['visitas_programadas'],
                                    VisitasRealizadas: datosIniciales[i]['visitas_realizadas'],
                                    VisitasClientesProgramados: datosIniciales[i]['visitas_clientes_programados'],
                                    VisitasClientesNoProgramados: datosIniciales[i]['visitas_realizadas'] - datosIniciales[i]['visitas_clientes_programados'],
                                    ClientesVisitaVenta: datosIniciales[i]['ClientesVisitaVenta'],
                                    ClientesVisitaNoVenta: (datosIniciales[i]['visitas_realizadas'] - datosIniciales[i]['ClientesVisitaVenta']),
                                    ClientesPromocion: datosIniciales[i]['ClientesPromocion'],
                                    ClientesSinPromocion: datosIniciales[i]['ClientesVisitaVenta'] - datosIniciales[i]['ClientesPromocion'],
                                    ClientesSinVisita: datosIniciales[i]['visitas_programadas'] == 0 ? 0 : (datosIniciales[i]['visitas_programadas'] - datosIniciales[i]['visitas_clientes_programados']) * -1,
                                    EfectividadxVisita: efectividadxVisita,
                                    EfectividadxVisitaxVenta: efectividadxVisitaxVenta,
                                    CajasPrevendidasVendidas: datosIniciales[i]['Cajas'],
                                    Efectivo: datosIniciales[i]['efectivo'],
                                    Credito: datosIniciales[i]['credito'],
                                    Cobranza: datosIniciales[i]['cobranza'],
                                    Descuentos: datosIniciales[i]['descuentos'],
                                    PresupuestoDiario: 0,
                                    Diferencia: 0,
                                    Promociones: datosIniciales[i]['CajasPromocion'],
                                    Obsequios: 0,
                                    Pilones: 0,
                                    CajasPrevendidasVendidasDetalle: cajasPrevendidasDetalle,
                                    PorcentajeCajasPrevendidasVendidas: cajasPrevendidasDetalle == 0 ? 0 : (cajasPrevendidasDetalle * 100) / datosIniciales[i]['Cajas'],
                                    CajasPrevendidasVendidasPromocion: datosIniciales[i]['CajasVentaProm'],
                                    PorcentajeCajasPrevendidasVendidasPromocion: datosIniciales[i]['CajasVentaProm'] == 0 ? 0 : (datosIniciales[i]['CajasVentaProm'] * 100) / datosIniciales[i]['Cajas'],
                                    TotalCajasZona: 0,
                                    TotalCargaKilos: 0,
                                    ProductosMasVendidos: datosIniciales[i]['3MasVendidos'] ?? 'SIN PRODUCTOS MAS VENDIDOS',
                                    SaboresMasVendidos: datosIniciales[i]['3SaboresMasVendidos'] ?? 'SIN SABORES MAS VENDIDOS',
                                    InicioOperativo: datosIniciales[i]['InicioOperativo'],
                                    InicioPrimerCliente: datosIniciales[i]['InicioPrimerCliente'],
                                    TiempoTranscurrido: datosIniciales[i]['TiempoTranscurrido'],
                                    UltimoCliente: datosIniciales[i]['UltimoCliente'],
                                    CierreOperativo: datosIniciales[i]['CierreOperativo'],
                                    UltimoTiempoTraslado: datosIniciales[i]['UltimoTiempoTraslado'],
                                    TiempoRuta: calcularTiempoRuta(datosIniciales[i]['InicioOperativo'], datosIniciales[i]['CierreOperativo'], datosIniciales[i]['UltimoCliente'])
                                }
                            );
                        }
                        datosTablaSemaforo.forEach(function (datos) {
                            datosTotales.VisitasProgramadas += parseInt(datos.VisitasProgramadas);
                            datosTotales.VisitasRealizadas += parseInt(datos.VisitasRealizadas);
                            datosTotales.VisitasClientesProgramados += parseInt(datos.VisitasClientesProgramados);
                            datosTotales.VisitasClientesNoProgramados += parseInt(datos.VisitasClientesNoProgramados);
                            datosTotales.ClientesVisitaVenta += parseInt(datos.ClientesVisitaVenta);
                            datosTotales.ClientesVisitaNoVenta += parseInt(datos.ClientesVisitaNoVenta);
                            datosTotales.ClientesPromocion += parseInt(datos.ClientesPromocion);
                            datosTotales.ClientesSinPromocion += parseInt(datos.ClientesSinPromocion);
                            datosTotales.ClientesSinVisita += parseInt(datos.ClientesSinVisita);
                            datosTotales.EfectividadxVisita += parseFloat(datos.EfectividadxVisita);
                            datosTotales.EfectividadxVisitaxVenta += parseFloat(datos.EfectividadxVisitaxVenta);
                            datosTotales.CajasPrevendidasVendidas += parseInt(datos.CajasPrevendidasVendidas);
                            datosTotales.Efectivo += parseFloat(datos.Efectivo);
                            datosTotales.Credito += parseFloat(datos.Credito);
                            datosTotales.Cobranza += parseFloat(datos.Cobranza);
                            datosTotales.Descuentos += parseFloat(datos.Descuentos);
                            datosTotales.PresupuestoDiario += parseFloat(datos.PresupuestoDiario);
                            datosTotales.Diferencia += parseInt(datos.Diferencia);
                            datosTotales.Promociones += parseInt(datos.Promociones);
                            datosTotales.Obsequios += parseInt(datos.Obsequios);
                            datosTotales.Pilones += parseInt(datos.Pilones);
                            datosTotales.CajasPrevendidasVendidasDetalle += parseInt(datos.CajasPrevendidasVendidasDetalle);
                            datosTotales.PorcentajeCajasPrevendidasVendidas += parseFloat(datos.PorcentajeCajasPrevendidasVendidas);
                            datosTotales.CajasPrevendidasVendidasPromocion += parseInt(datos.CajasPrevendidasVendidasPromocion);
                            datosTotales.PorcentajeCajasPrevendidasVendidasPromocion += parseFloat(datos.PorcentajeCajasPrevendidasVendidasPromocion);
                            datosTotales.TotalCajasZona += parseInt(datos.TotalCajasZona);
                            datosTotales.TotalCargaKilos += parseInt(datos.TotalCargaKilos);
                        });
                    }
                    console.log(datosTotales);
                    if ($.fn.DataTable.isDataTable('#tabla-semaforo')) {
                        $('#tabla-semaforo').DataTable().destroy();
                    }
                    $('#tabla-semaforo').DataTable({
                        responsive: true,
                        autoWidth: true,
                        language: {
                            url: '/assets/plugins/DataTable/spanish.json'
                        },
                        data: datosTablaSemaforo,
                        columns: [
                            {data: 'Ruta'},
                            {data: 'Agente'},
                            {data: 'VisitasProgramadas'},
                            {data: 'VisitasRealizadas'},
                            {data: 'VisitasClientesProgramados'},
                            {data: 'VisitasClientesNoProgramados'},
                            {data: 'ClientesVisitaVenta'},
                            {data: 'ClientesVisitaNoVenta'},
                            {data: 'ClientesSinPromocion'},
                            {data: 'ClientesPromocion'},
                            {
                                data: 'ClientesSinVisita',
                                render: function (data, type, row) {
                                    return data;
                                },
                                createdCell: function (td, cellData, rowData, row, col) {
                                    if (cellData <= 0) {
                                        //agrega la class colum-red-light
                                        $(td).addClass('column-red-light');
                                    } else {
                                        //agrega la class column-greeb-light
                                        $(td).addClass('column-green-light');
                                    }
                                }
                            },
                            {
                                data: 'EfectividadxVisita',
                                render: function (data, type, row) {
                                    return data.toFixed(2) + '%';
                                },
                                createdCell: function (td, cellData, rowData, row, col) {
                                    if (cellData < 50) {
                                        //agrega la class colum-red-light
                                        $(td).addClass('column-red-light');
                                    } else {
                                        //agrega la class column-greeb-light
                                        $(td).addClass('column-green-light');
                                    }
                                }
                            },
                            {
                                data: 'EfectividadxVisitaxVenta',
                                render: function (data, type, row) {
                                    return data.toFixed(2) + '%';
                                },
                                createdCell: function (td, cellData, rowData, row, col) {
                                    if (cellData < 50) {
                                        //agrega la class colum-red-light
                                        $(td).addClass('column-red-light');
                                    } else {
                                        //agrega la class column-greeb-light
                                        $(td).addClass('column-green-light');
                                    }
                                }
                            },
                            {data: 'CajasPrevendidasVendidas'},
                            {
                                data: 'Efectivo', render: function (data, type, row) {
                                    return data.toFixed(2);
                                },
                            },
                            {
                                data: 'Credito', render: function (data, type, row) {
                                    return data.toFixed(2);
                                },
                            },
                            {
                                data: 'Cobranza', render: function (data, type, row) {
                                    return data.toFixed(2);
                                },
                            },
                            {
                                data: 'Descuentos', render: function (data, type, row) {
                                    return data.toFixed(2);
                                },
                            },
                            {data: 'PresupuestoDiario'},
                            {data: 'Diferencia'},
                            {data: 'Promociones'},
                            {data: 'CajasPrevendidasVendidasDetalle'},
                            {
                                data: 'PorcentajeCajasPrevendidasVendidas', render: function (data, type, row) {
                                    return data.toFixed(2) + '%';
                                },
                            },
                            {data: 'CajasPrevendidasVendidasPromocion'},
                            {
                                data: 'PorcentajeCajasPrevendidasVendidasPromocion',
                                render: function (data, type, row) {
                                    return data.toFixed(2) + '%';
                                },
                            },
                            {data: 'TotalCajasZona'},
                            {data: 'TotalCargaKilos'},
                            {data: 'ProductosMasVendidos'},
                            {data: 'SaboresMasVendidos'},
                            {data: 'InicioOperativo'},
                            {data: 'InicioPrimerCliente'},
                            {data: 'TiempoTranscurrido'},
                            {data: 'UltimoCliente'},
                            {data: 'CierreOperativo'},
                            {data: 'UltimoTiempoTraslado'},
                            {data: 'TiempoRuta'}
                        ]
                    });

                    $('#tabla-semaforo tfoot').remove();
                    var tfootHtml = '<tfoot>' +
                        '<tr>' +
                        '<th colspan="2">Total</th>' +
                        '<th rowspan="1" colspan="1">' + datosTotales.VisitasProgramadas + '</th>' +
                        '<th rowspan="1" colspan="1" rowspan="1" colspan="1">' + datosTotales.VisitasRealizadas + '</th>' +
                        '<th rowspan="1" colspan="1">' + datosTotales.VisitasClientesProgramados + '</th>' +
                        '<th rowspan="1" colspan="1">' + datosTotales.VisitasClientesNoProgramados + '</th>' +
                        '<th rowspan="1" colspan="1">' + datosTotales.ClientesVisitaVenta + '</th>' +
                        '<th rowspan="1" colspan="1">' + datosTotales.ClientesVisitaNoVenta + '</th>' +
                        '<th rowspan="1" colspan="1">' + datosTotales.ClientesPromocion + '</th>' +
                        '<th rowspan="1" colspan="1">' + datosTotales.ClientesSinPromocion + '</th>' +
                        '<th rowspan="1" colspan="1">' + datosTotales.ClientesSinVisita + '</th>' +
                        '<th rowspan="1" colspan="1">' + datosTotales.EfectividadxVisita.toFixed(2) + '</th>' +
                        '<th rowspan="1" colspan="1">' + datosTotales.EfectividadxVisitaxVenta.toFixed(2) + '</th>' +
                        '<th rowspan="1" colspan="1">' + datosTotales.CajasPrevendidasVendidas + '</th>' +
                        '<th rowspan="1" colspan="1">' + datosTotales.Efectivo.toFixed(2) + '</th>' +
                        '<th rowspan="1" colspan="1">' + datosTotales.Credito.toFixed(2) + '</th>' +
                        '<th rowspan="1" colspan="1">' + datosTotales.Cobranza.toFixed(2) + '</th>' +
                        '<th rowspan="1" colspan="1">' + datosTotales.Descuentos.toFixed(2) + '</th>' +
                        '<th rowspan="1" colspan="1">' + datosTotales.PresupuestoDiario + '</th>' +
                        '<th rowspan="1" colspan="1">' + datosTotales.Diferencia + '</th>' +
                        '<th rowspan="1" colspan="1">' + datosTotales.Promociones + '</th>' +
                        '<th rowspan="1" colspan="1">' + datosTotales.CajasPrevendidasVendidasDetalle + '</th>' +
                        '<th rowspan="1" colspan="1">' + datosTotales.PorcentajeCajasPrevendidasVendidas.toFixed(2) + '</th>' +
                        '<th rowspan="1" colspan="1">' + datosTotales.CajasPrevendidasVendidasPromocion + '</th>' +
                        '<th rowspan="1" colspan="1">' + datosTotales.PorcentajeCajasPrevendidasVendidasPromocion.toFixed(2) + '</th>' +
                        '<th rowspan="1" colspan="1">' + datosTotales.TotalCajasZona + '</th>' +
                        '<th rowspan="1" colspan="1">' + datosTotales.TotalCargaKilos + '</th>' +
                        '<th rowspan="1" colspan="1"></th>' +
                        '<th rowspan="1" colspan="1"></th>' +
                        '<th rowspan="1" colspan="1"></th>' +
                        '<th rowspan="1" colspan="1"></th>' +
                        '<th rowspan="1" colspan="1"></th>' +
                        '<th rowspan="1" colspan="1"></th>' +
                        '<th rowspan="1" colspan="1"></th>' +
                        '<th rowspan="1" colspan="1"></th>' +
                        '<th rowspan="1" colspan="1"></th>' +
                        '</tr>' +
                        '</tfoot>';

                    $('#tabla-semaforo').append(tfootHtml);
                    datosTabla = datosTablaSemaforo;
                    datosTotalTabla = datosTotales;
                    datosExcel = {
                        datosTabla: datosTabla,
                        datosTotalTabla: datosTotalTabla
                    };
                    console.log(datosExcel);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'No se pudo cargar la tabla',
                        text: 'No se pudo cargar la tabla correctamente, contacte al administrador del sistema'
                    });
                }
            },
            error: function (xhr, textStatus, errorThrown) {
                setTimeout(function () {
                    Swal.close();
                }, 500);
                Swal.fire({
                    icon: 'error',
                    title: 'Error al cargar la tabla',
                    text: 'No se pudo cargar la tabla correctamente, contacte al administrador del sistema'
                });
            }
        });
    }

    $("#generarExcelDescarga").click(function () {

        var diao_list = $('#diao_list').val();
        var fechaini = $('#fechaini').val();
        var almacen = $('#almacenes').val();
        var ruta = $('#rutas_list').val();

        if (diao_list == '' && fechaini == '') {
            Swal.fire({
                icon: 'error',
                title: 'No se pudo cargar la tabla',
                text: 'Debe seleccionar un Dia Operativo o Fecha a consultar',
            });
            return false;
        }
        if (diao_list != '' && fechaini != '') {
            Swal.fire({
                icon: 'error',
                title: 'No se pudo cargar la tabla',
                text: 'Debe seleccionar un Dia Operativo o Fecha a consultar, no ambos',
            });
            return false;
        }
        $.ajax({
            url: '/api/koolreport/export/reportes/ventas/ventas_sfa/ExportExcelSemaforoService.php',
            type: "POST",
            async: true,
            data: {
                ruta: ruta,
                diao: diao_list,
                almacen: almacen,
                fecha: fechaini
            },
            success: function (data) {
                console.log(data);
                var link = document.createElement('a');
                link.href = "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64," + data;
                link.target = '_blank';
                link.download = 'semaforo_ventas.xlsx';
                link.dispatchEvent(new MouseEvent('click'));

            },
            error: function (res) {
                swal("Error", 'Error al generar el reporte', "error");
            }
        });
    });

    function calcularTiempoRuta(inicioOperativo, cierreOperativo, ultimoCliente) {

        if (cierreOperativo === 'S/R' && ultimoCliente != 'S/R') {
            return calcularDiferenciaTiempo(inicioOperativo, ultimoCliente);
        }
        if (ultimoCliente == 'S/R') {
            return 'S/R';
        } else {
            return calcularDiferenciaTiempo(inicioOperativo, cierreOperativo);
        }
    }

    function calcularDiferenciaTiempo(horaInicio, horaFin) {
        const fechaInicio = new Date(horaInicio);
        const fechaFin = new Date(horaFin);
        const diferencia = fechaFin - fechaInicio;
        const horas = Math.floor(diferencia / 1000 / 60 / 60);
        const minutos = Math.floor((diferencia / 1000 / 60) % 60);
        const segundos = Math.floor((diferencia / 1000) % 60);
        return `${horas}:${minutos}:${segundos}`;
    }

    $(function ($) {
        //almacenPrede();
        console.log("almacen:", $("#almacenes").val());

        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";

        //resize to fit page size
        $(window).on('resize.jqGrid', function () {
            $(grid_selector).jqGrid('setGridWidth', $(".page-content").width() - 60);
        })
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid', function (ev, event_name, collapsed) {
            if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function () {
                    $(grid_selector).jqGrid('setGridWidth', parent_column.width());
                }, 0);
            }
        })

        $(grid_selector).jqGrid({
            //url: '/api/reportesRutas/lista/index.php',
            datatype: "json",
            height: 250,
            shrinkToFit: false,
            height: 'auto',
            postData: {
                criterio: $("#txtCriterio").val(),
                ruta: $("#rutas_list").val(),
                diao: $("#diao_list").val(),
                fechaini: $("#fechaini").val(),
                fechafin: $("#fechafin").val(),
                almacen: $("#almacenes").val(),

            },
            mtype: 'POST',
            colNames: ["Dia O", 'Fecha', 'Clave Artículo', 'Descripción', 'Cantidad', 'Folio'],
            colModel: [
                {name: 'dia_o', index: 'dia_o', width: 50, editable: false, sortable: false, align: 'right'},//1
                {name: 'fecha', index: 'fecha', width: 80, editable: false, sortable: false, align: 'center'},//2
                {name: 'clave', index: 'clave', width: 100, editable: false, sortable: false, align: 'left'},//3
                {
                    name: 'descripcion',
                    index: 'descripcion',
                    width: 200,
                    editable: false,
                    sortable: false,
                    align: 'left'
                },//4
                {name: 'cantidad', index: 'cantidad', width: 100, editable: false, sortable: false, align: 'right'},//5
                {name: 'folio', index: 'folio', width: 120, editable: false, sortable: false, align: 'left'},//6
            ],
            rowNum: 30,
            rowList: [30, 40, 50],
            pager: pager_selector,
            sortname: 'ID_Venta',
            viewrecords: true,
            //loadError: function(data){console.log("ERROR: ", data);},
            sortorder: "desc",
            loadComplete: function (data) {
                console.log("Cobranza RES: ", data);
                console.log("Cobranza SQL Totales: ", data.sql_conteos);

                /*
                                $("#timporte").text(data.timporte);
                                $("#tiva").text(data.tiva);
                                $("#tdescuento").text(data.tdescuento);
                                $("#ttotal").text(data.ttotal);
                                $("#ttotalc").text(data.ttotalc);
                                $("#ttotalp").text(data.ttotalp);
                                $("#tpromoc").text(data.tpromoc);
                                $("#tpromop").text(data.tpromop);
                                $("#tobseqc").text(data.tobseqc);
                                $("#tobseqp").text(data.tobseqp);
                */
                //$("#tcredito").text(data.total_credito);
                //$("#tcobranza").text(data.total_cobranza);
                //$("#tadeudo").text(data.total_adeudo);
            },
            loadError: function (data) {
                console.log("Cobranza ERROR: ", data);
            }
        });

        // Setup buttons
        // $("#grid-table").jqGrid('navGrid', '#grid-pager', {
        //     edit: false,
        //     add: false,
        //     del: false,
        //     search: false
        // }, {
        //     reloadAfterSubmit: true
        // });


        $(window).triggerHandler('resize.jqGrid'); //trigger window resize to make the grid get the correct size

        function imageFormat(cellvalue, options, rowObject) {
            var id_venta = rowObject[1];
            var html = '';
            return html;
        }

        function aceSwitch(cellvalue, options, cell) {
            setTimeout(function () {
                $(cell).find('input[type=checkbox]')
                    .addClass('ace ace-switch ace-switch-5')
                    .after('<span class="lbl"></span>');
            }, 0);
        }

        //enable datepicker
        function pickDate(cellvalue, options, cell) {
            setTimeout(function () {
                $(cell).find('input[type=text]')
                    .datepicker({
                        format: 'yyyy-mm-dd',
                        autoclose: true
                    });
            }, 0);
        }

        function beforeDeleteCallback(e) {
            var form = $(e[0]);
            if (form.data('styled')) return false;
            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_delete_form(form);
            form.data('styled', true);
        }

        function reloadPage() {
            var grid = $(grid_selector);
            $.ajax({
                url: "index.php",
                dataType: "json",
                success: function (data) {
                    grid.trigger("reloadGrid", [{
                        current: true
                    }]);
                },
                error: function () {
                }
            });
        }

        function beforeEditCallback(e) {
            var form = $(e[0]);
            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_edit_form(form);
        }

        //it causes some flicker when reloading or navigating grid
        //it may be possible to have some custom formatter to do this as the grid is being created to prevent this
        //or go back to default browser checkbox styles for the grid
        function styleCheckbox(table) {
        }

        //unlike navButtons icons, action icons in rows seem to be hard-coded
        //you can change them like this in here if you want
        function updateActionIcons(table) {
        }

        //replace icons with FontAwesome icons like above

        function updatePagerIcons(table) {
        }

        function enableTooltips(table) {
            $('.navtable .ui-pg-button').tooltip({
                container: 'body'
            });
            $(table).find('.ui-pg-div').tooltip({
                container: 'body'
            });
        }

        $(document).one('ajaxloadstart.page', function (e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });

    //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////

    $("#diao_list").change(function () {

        ReloadGrid();

    });


    $("#rutas_list").change(function () {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_ruta: $(this).val(),
                action: "extraer_diaso"
            },
            beforeSend: function (x) {
                if (x && x.overrideMimeType) {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/reportesRutas/lista/filtros_select.php',
            success: function (data) {
                //console.log("SUCCESS DIAS_O = ", data);
                $("#diao_list").empty();
                $("#diao_list").append(data);
                $("#diao_list").trigger("chosen:updated");
                //$('.chosen-select').chosen();
            },
            error: function (data) {
                console.log("ERROR = ", data);
            }
        });
        //setTimeout(function(){ReloadGrid();}, 2000);
        ReloadGrid();
    });

    function ReloadGrid() {
        console.log("almacen INIT: ", $("#almacenes").val());
        setTimeout(function () {
            $('#grid-table').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        criterio: $("#txtCriterio").val(),
                        ruta: $("#rutas_list").val(),
                        diao: $("#diao_list").val(),
                        fechaini: $("#fechaini").val(),
                        fechafin: $("#fechafin").val(),
                        almacen: $("#almacenes").val()
                    },
                    url: '/api/reportesRutas/lista/index_semaforodeventas.php',
                    datatype: 'json',
                    page: 1
                })
                .trigger('reloadGrid', [{
                    current: true
                }]);
        }, 2000);
    }

    function almacen() {
        $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    criterio: $("#txtCriterio").val(),
                    ruta: $("#rutas_list").val(),
                    diao: $("#diao_list").val(),
                    fechaini: $("#fechaini").val(),
                    fechafin: $("#fechafin").val(),
                    almacen: $("#almacenes").val(),
                },
                datatype: 'json',
                page: 1,
                fromServer: true
            })
            .trigger('reloadGrid', [{
                current: true
            }]);

        /*
        var filtering = FooTable.get('.footable').use(FooTable.Filtering), // get the filtering component for the table
            filter = $("#almacenes option:selected").text(); // get the value to filter by
        if (filter == 'Seleccione el Almacen')
        { // if the value is "none" remove the filter
            filtering.removeFilter('Almacen');
        }
        else
        { // otherwise add/update the filter.
            filtering.addFilter('Almacen', filter);
        }
        filtering.filter();
        */
    }

    function downloadxml(url) {
        var win = window.open(url, '_blank');
        win.focus();
    }

    function viewPdf(url) {
        var win = window.open(url, '_blank');
        win.focus();
    }

    $modal0 = null;


</script>

<script>
    $(document).ready(function () {
        $("#inactivos").on("click", function () {
            $modal0 = $("#coModal");
            $modal0.modal('show');
            ReloadGrid1();
        });
        // $("#cve_almacenp").select2();
        $("div.ui-jqgrid-bdiv").css("max-height", $("#list").height() - 80);
    });
</script>


<script type="text/javascript">
    // IE9 fix
    if (!window.console) {
        var console = {
            log: function () {
            },
            warn: function () {
            },
            error: function () {
            },
            time: function () {
            },
            timeEnd: function () {
            }
        }
    }

    jQuery(function ($) {
        $('.footable').footable();
    });

    $("#txtCriterio").keyup(function (event) {
        if (event.keyCode == 13) {
            $("#buscarA").click();
        }
    });

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
    <?php if($edit[0]['Activo']==0) { ?>
    .fa-edit {
        display: none;
    }

    <?php } ?>
    <?php if($borrar[0]['Activo']==0) { ?>
    .fa-eraser {
        display: none;
    }

    <?php } ?>

</style>