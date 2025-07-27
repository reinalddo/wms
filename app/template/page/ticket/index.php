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

<!-- Mainly scripts -->
<style type="text/css">
    ul.dropdown-menu.dropdown-menu-right 
    {
        position: absolute;
        left: auto;
        right: 0;
    }
</style>

<!-- Barra de nuevo y busqueda -->
<div class="wrapper wrapper-content  animated fadeInRight" id="rut">
    <h3>Ticket</h3>
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


                    </div>
<br><br><br><br><br>
    <div class="row">
        <div class="col-md-3">
        </div>

        <div class="col-md-6">

        <table class="table table-striped">
            <tr>
                <td align="right" width="200px"><b>Nombre Comercial:</b></td>
                <td><input type="text" id="nombre_comercial" class="form-control" placeholder="Nombre Comercial"></td>
            </tr>
            <tr>
                <td align="right" width="200px"><b>Sucursal:</b></td>
                <td><input type="text" id="sucursal" class="form-control" placeholder="Sucursal"></td>
            </tr>
            <tr>
                <td align="right" width="200px"><b>Dirección 1:</b></td>
                <td><input type="text" id="direccion1" class="form-control" placeholder="Dirección 1"></td>
            </tr>
            <tr>
                <td align="right" width="200px"><b>Dirección 2:</b></td>
                <td><input type="text" id="direccion2" class="form-control" placeholder="Dirección 2"></td>
            </tr>
            <tr>
                <td align="right" width="200px"><b>Mensaje:</b></td>
                <td><input type="text" id="mensaje" class="form-control" placeholder="Mensaje"></td>
            </tr>
            <tr>
                <td align="right" width="200px"><b>Habilitar ticket de visita:</b></td>
                <td><input type="checkbox" id="ticket_visita" class="form-control" style="width: auto;margin-top: -6px;"></td>
            </tr>
            <tr>
                <td align="right" width="200px"><b>Habilitar mensajes de liquidación:</b></td>
                <td><input type="checkbox" id="mensajes_liquidacion" class="form-control" style="width: auto;margin-top: -6px;"></td>
            </tr>
        </table>

        <br><br>


            <div class="col-md-3">
            </div>
        </div>
    </div>

        <div class="row permiso_registrar" style="text-align: center;">
            <input type="button" id="registrar_modificar" class="btn btn-primary btn-large" value="Registrar/Actualizar">
        </div>
<br><br><br><br><br><br>
        </div>


            </div>
        </div>


    </div>

</div>

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



        $("#registrar_modificar").click(function(){

            var ticket_visita = "0", mensajes_liquidacion = "0";

            if($("#ticket_visita").is(':checked'))
                ticket_visita = "1";

            if($("#mensajes_liquidacion").is(':checked'))
                mensajes_liquidacion = "1";

            $.ajax({
                url: '/api/ticket/update/index.php',
                data: {
                    action: 'registrar_actualizar',
                    almacen: <?php echo $_SESSION['cve_almacen']; ?>,
                    Linea1: $("#nombre_comercial").val(),
                    Linea2: $("#sucursal").val(),
                    Linea3: $("#direccion1").val(),
                    Linea4: $("#direccion2").val(),
                    Mensaje: $("#mensaje").val(),
                    Tdv: ticket_visita,
                    MLiq: mensajes_liquidacion
                },
                dataType: 'json',
                method: 'POST',
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                success: function(data) 
                {
                    console.log("SUCCESS: ", data);
                    swal("Éxito", "Ticket Generado con Éxito", "success");
                }, error: function(data){
                    console.log("ERROR: ", data);
                }

            });
        });



            $.ajax({
                url: '/api/ticket/update/index.php',
                data: {
                    action: 'extraer_ticket',
                    almacen: <?php echo $_SESSION['cve_almacen']; ?>
                },
                dataType: 'json',
                method: 'POST',
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                success: function(data) 
                {
                    console.log("SUCCESS: ", data);
                    $("#nombre_comercial").val(data.Linea1);
                    $("#sucursal").val(data.Linea2);
                    $("#direccion1").val(data.Linea3);
                    $("#direccion2").val(data.Linea4);
                    $("#mensaje").val(data.Mensaje);

                    if(data.Tdv == 1)
                        $("#ticket_visita").prop("checked", true);
                    if(data.MLiq == 1)
                        $("#mensajes_liquidacion").prop("checked", true);

                }, error: function(data){
                    console.log("ERROR: ", data);
                }

            });


</script>


