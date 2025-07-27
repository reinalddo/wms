<?php 
$id_almacen = $_SESSION['id_almacen'];
$confSql = \db()->prepare("SELECT DISTINCT * FROM (SELECT IF(ID_Permiso = 2 AND Id_Tipo = 2, 1, IF(ID_Permiso = 2 AND Id_Tipo = 3, 0, -1)) AS con_recorrido FROM Rel_ModuloTipo WHERE Cve_Almac = '$id_almacen') AS cr WHERE cr.con_recorrido != -1");
$confSql->execute();
$con_recorrido = $confSql->fetch()['con_recorrido'];
if(!$con_recorrido) $con_recorrido = 0;
 

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

$kardex_consolidado = \db()->prepare("SELECT cve_conf, Valor FROM t_configuraciongeneral WHERE cve_conf = 'kardex_consolidado' AND id_almacen = $id_almacen");
$kardex_consolidado->execute();
$kardex_consolidado = $kardex_consolidado->fetch()["Valor"];

/*
if($k_cve_conf == "" && $k_valor == "")
{
    $kardex_consolidado = \db()->prepare("INSERT INTO t_configuraciongeneral(cve_conf, Valor, id_almacen) VALUES('kardex_consolidado', '0', $id_almacen)");
    $kardex_consolidado->execute();
    $kardex_consolidado = 0;
}
*/

 ?>

 <input type="hidden" id="id_almacen" value="<?php echo $id_almacen; ?>">
<link href="/css/plugins/iCheck/custom.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<div class="wrapper wrapper-content" style="padding-bottom: 10px">
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Configuración Instancia</h5>
                </div>
                <div class="ibox-content">
                    <div class="row" style="display:none;">
                        <div class="col-lg-6">
                            <label>Habilitar Surtido Completo de Pedidos</label>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <div class="checkbox">
                                    <input type="checkbox" name="habilitar_surtido_completo" id="habilitar_surtido_completo" value="0" style="cursor: pointer;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <h5> 
                    </h5>

                    <br><br>
                    <div class="row">
                        <div class="col-lg-6">
                            <label>Producción para Stock</label>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <div class="checkbox">
                                    <input type="checkbox" name="produccion_sin_surtir" id="produccion_sin_surtir" value="0" style="cursor: pointer;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <br><br>
                    <div class="row">
                        <div class="col-lg-6">
                            <label>Habilitar Recepción por cajas</label>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <div class="checkbox">
                                    <input type="checkbox" name="recepcion_por_cajas" id="recepcion_por_cajas" value="0" style="cursor: pointer;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <br><br>
                    <div class="row">
                        <div class="col-lg-6">
                            <label>Reporte de Existencias Ubicación Con Formato</label>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <div class="checkbox">
                                    <input type="checkbox" name="existencias_con_formato" id="existencias_con_formato" value="0" style="cursor: pointer;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <br><br>
                    <div class="row">
                        <div class="col-lg-6">
                            <label>Número de Decimales en Productos y Servicios</label>
                        </div>
                        <div class="col-lg-6" style="padding-left: 0 !important;">
                            <div class="form-group">
                                <div class="checkbox">
                                    <input type="number" name="decimales_cantidad_conf" id="decimales_cantidad_conf" min="0" max="5" class="form-control" onkeydown="filtro()" step="1" style="width:80px">
                                </div>
                            </div>
                        </div>
                    </div>
                    <br><br>
                    <div class="row">
                        <div class="col-lg-6">
                            <label>Número de Decimales en Costos $</label>
                        </div>
                        <div class="col-lg-6" style="padding-left: 0 !important;">
                            <div class="form-group">
                                <div class="checkbox">
                                    <input type="number" name="decimales_costo_conf" id="decimales_costo_conf" min="0" max="5" class="form-control" onkeydown="filtro2()" step="1" style="width:80px">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div style="text-align: right;">
                                <?php /* ?><button class="btn btn-default" type="button" onclick="clean()">Limpiar</button><?php */ ?>
                                <button class="btn btn-primary" type="button" onclick="save()">Guardar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Configuración Almacén</h5>
                </div>
                <div class="ibox-content">
    <div class="row">
        <div class="col-lg-6">
            <label>Usa Recorrido de Surtido</label>
        </div>
        <div class="col-lg-6">
            <div class="form-group">
                <div class="checkbox">
                <input type="checkbox" name="check_rs" id="check_rs" style="cursor: pointer;" <?php if ($con_recorrido == 1) echo "checked"; ?>>
                </div>
            </div>
        </div>
    </div>
<br><br>
    <div class="row">
        <div class="col-lg-6">
            <label>Generar Kardex Consolidado</label>
        </div>
        <div class="col-lg-6">
            <div class="form-group">
                <div class="checkbox">
                <input type="checkbox" name="kardex_consolidado" id="kardex_consolidado" style="cursor: pointer;" <?php if ($kardex_consolidado == 1) echo "checked"; ?>>
                </div>
            </div>
        </div>
    </div>
<br><br>
    <div class="row">
        <div class="col-lg-6">
            <label>Reportes en Administración de Embarques:</label>
        </div>
        <div class="col-lg-6">
            <div class="form-group">
                <div class="row checkbox">
                <label><input type="checkbox" name="lista_empaque" id="lista_empaque" <?php if($lista_empaque == "" || $lista_empaque == 1) echo "checked"; ?> style="cursor: pointer;"> <b>Lista de Empaque</b></label>
                </div>
                <div class="row checkbox">
                <label><input type="checkbox" name="entrega_programada" id="entrega_programada" <?php if($entrega_programada == "" || $entrega_programada == 1) echo "checked"; ?> style="cursor: pointer;"> <b>Entregas Programadas</b></label>
                </div>
                <div class="row checkbox">
                <label><input type="checkbox" name="salida_inventario" id="salida_inventario" <?php if($salida_inventario == "" || $salida_inventario == 1) echo "checked"; ?> style="cursor: pointer;"> <b>Salida de Inventario</b></label>
                </div>
                <div class="row checkbox">
                <label><input type="checkbox" name="lista_embarque" id="lista_embarque" <?php if($lista_embarque == "" || $lista_embarque == 1) echo "checked"; ?> style="cursor: pointer;"> <b>Lista de Embarque</b></label>
                </div>
                <div class="row checkbox">
                <label><input type="checkbox" name="auditoria_embarque" id="auditoria_embarque" <?php if($auditoria_embarque == "" || $auditoria_embarque == 1) echo "checked"; ?> style="cursor: pointer;"> <b>Auditoría de Embarque</b></label>
                </div>
                <div class="row checkbox">
                <label><input type="checkbox" name="discrepancia" id="discrepancia" <?php if($discrepancia == "" || $discrepancia == 1) echo "checked"; ?> style="cursor: pointer;"> <b>Discrepancias de Embarque</b></label>
                </div>
                <div class="row checkbox">
                <label><input type="checkbox" name="imprimir_asn" id="imprimir_asn" <?php if($imprimir_asn == "" || $imprimir_asn == 1) echo "checked"; ?> style="cursor: pointer;"> <b>Imprimir ASN</b></label>
                </div>
                <div class="row checkbox">
                <label><input type="checkbox" name="imp_archivo_despacho" id="imp_archivo_despacho" <?php if($imp_archivo_despacho == "" || $imp_archivo_despacho == 1) echo "checked"; ?> style="cursor: pointer;"> <b>Imprimir Archivo de Despacho</b></label>
                </div>
                <div class="row checkbox">
                <label><input type="checkbox" name="des_aviso_despacho" id="des_aviso_despacho" <?php if($des_aviso_despacho == "" || $des_aviso_despacho == 1) echo "checked"; ?> style="cursor: pointer;"> <b>Descargar Aviso Despacho</b></label>
                </div>
                <div class="row checkbox">
                <label><input type="checkbox" name="des_archivo_despacho" id="des_archivo_despacho" <?php if($des_archivo_despacho == "" || $des_archivo_despacho == 1) echo "checked"; ?> style="cursor: pointer;"> <b>Descargar Archivo Despacho</b></label>
                </div>
            </div>
        </div>
    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div style="text-align: right;">
                                <?php /* ?><button class="btn btn-default" type="button" onclick="clean()">Limpiar</button><?php */ ?>
                                <button class="btn btn-primary" type="button" onclick="saveAlmacen()">Guardar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




</div>

<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

<!-- Flot -->
<script src="/js/plugins/flot/jquery.flot.js"></script>
<script src="/js/plugins/flot/jquery.flot.tooltip.min.js"></script>
<script src="/js/plugins/flot/jquery.flot.spline.js"></script>
<script src="/js/plugins/flot/jquery.flot.resize.js"></script>
<script src="/js/plugins/flot/jquery.flot.pie.js"></script>

<!-- Peity -->
<script src="/js/plugins/peity/jquery.peity.min.js"></script>
<script src="/js/demo/peity-demo.js"></script>

<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>

<!-- jQuery UI -->
<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>

<!-- GITTER -->
<script src="/js/plugins/gritter/jquery.gritter.min.js"></script>

<!-- Sparkline -->
<script src="/js/plugins/sparkline/jquery.sparkline.min.js"></script>

<!-- Sparkline demo data  -->
<script src="/js/demo/sparkline-demo.js"></script>

<!-- ChartJS-->
<script src="/js/plugins/chartJs/Chart.min.js"></script>

<!-- Toastr -->
<script src="/js/plugins/toastr/toastr.min.js"></script>

<!-- Morris -->
<script src="/js/plugins/morris/raphael-2.1.0.min.js"></script>
<script src="/js/plugins/morris/morris.js"></script>

<!-- d3 and c3 charts -->
<script src="/js/plugins/d3/d3.min.js"></script>
<script src="/js/plugins/c3/c3.min.js"></script>

<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.min.js"></script>

<!-- iCheck -->
<script src="/js/plugins/iCheck/icheck.min.js"></script>
<!-- Sweet Alert -->
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>

<script type="text/javascript">
    $(document).ready(function(){

    $('#check_rs').click(function()
    {
        var valor = 0;
        if($(this).is(':checked'))
            valor = 1;

        CambiarTipoRecorridoSurtido(valor);

    });

    function CambiarTipoRecorridoSurtido(valor)
    {
        var msj = "No está";
        if(valor) msj = "está";

        console.log("msj = ", msj);
        console.log("valor = ", valor);
        //console.log("id_almacen", $("#almacen").val());

        $.ajax({
          url: '/api/rutassurtido/update/index.php',
          dataType: 'json',
          data: {
            action: 'CambiarTipoRecorridoSurtido',
            valor: valor,
            id_almacen: <?php echo $_SESSION['id_almacen']; ?>
          },
          type: 'POST'
        }).done(function(data) 
        {
            swal({
                    title: "Cambio Realizado",
                    text: "Este Almacén "+msj+" usando Ruta de Surtido",
                    type: "success",

                    showCancelButton: false,
                    cancelButtonText: "No",
                    cancelButtonColor: "#14960a",

                    confirmButtonColor: "#55b9dd",
                    confirmButtonText: "OK",
                    closeOnConfirm: true
                },
                function(e) {

                    if (e == true) {
                        //Reabasto
                        window.location.reload();
                    }

                });
        }
        ).fail(function(data){console.log("ERROR RUTA SURTIDO", data);});

    }

        $.ajax({
            url: '/api/configuraciongeneral/lista/index.php',
            cache: false,
            data: {
                action: 'getDatosConfig'//'getSurtidoCompleto'
            },
            dataType: 'json',
            method: 'GET'
        }).done(function(data){
            console.log("data = ", data);

            if(data.valorSC == 1)
            {
                $("#habilitar_surtido_completo").prop("checked", true);
                //$("#habilitar_surtido_completo").prop("disabled", true);
            }
            else
                $("#habilitar_surtido_completo").prop("checked", false);

            if(data.valorOT == 1)
            {
                $("#produccion_sin_surtir").prop("checked", true);
                //$("#habilitar_surtido_completo").prop("disabled", true);
            }
            else
                $("#produccion_sin_surtir").prop("checked", false);

            if(data.valorCajas == 1)
            {
                $("#recepcion_por_cajas").prop("checked", true);
                //$("#habilitar_surtido_completo").prop("disabled", true);
            }
            else
                $("#recepcion_por_cajas").prop("checked", false);

            if(data.existencias_con_formato == 1)
            {
                $("#existencias_con_formato").prop("checked", true);
                //$("#habilitar_surtido_completo").prop("disabled", true);
            }
            else
                $("#existencias_con_formato").prop("checked", false);

        });


    });

    function save(){

        var valor = 0;
        if($("#habilitar_surtido_completo").is(":checked"))
            valor = 1;

        var valor2 = 0;
        if($("#produccion_sin_surtir").is(":checked"))
            valor2 = 1;

        var valor3 = 0;
        if($("#recepcion_por_cajas").is(":checked"))
            valor3 = 1;

        var valor4 = 0;
        if($("#decimales_cantidad_conf").val())
            valor4 = $("#decimales_cantidad_conf").val();

        var valor5 = 0;
        if($("#decimales_costo_conf").val())
            valor5 = $("#decimales_costo_conf").val();

        var valor6 = 0;
        if($("#existencias_con_formato").is(":checked"))
            valor6 = 1;


        //console.log("Valor = ", valor);

        $.ajax({
            url: '/api/configuraciongeneral/update/index.php',
            data: {
                action: 'saveDatosConfig',//'saveSurtidoCompleto',
                valor6: valor6,
                valor5: valor5,
                valor4: valor4,
                valor3: valor3,
                valor2: valor2,
                valor: valor
            },
            dataType: 'json',
            method: 'POST'
        }).done(function(data){
            if(data.success)
            {
                swal("Éxito", "Cambios Realizados", "success");
                window.location.reload();
            }
            else
                swal("Error", "Ha ocurrido un error, intente nuevamente por favor", "error");
        });

    }

function filtro()
{
var tecla = event.key;
//console.log("tecla = ", tecla);

if (!['1', '2', '3', '4', '5', '0','e'].includes(tecla) || parseFloat($("#decimales_cantidad_conf").val()) <= 5)
   event.preventDefault()
}

function filtro2()
{
var tecla = event.key;
//console.log("tecla = ", tecla);

if (!['1', '2', '3', '4', '5', '0','e'].includes(tecla) || parseFloat($("#decimales_costo_conf").val()) <= 5)
   event.preventDefault()
}

    function saveAlmacen(){

        var lista_empaque = 0; if($("#lista_empaque").is(":checked")) lista_empaque = 1;
        var entrega_programada = 0; if($("#entrega_programada").is(":checked")) entrega_programada = 1;
        var salida_inventario = 0; if($("#salida_inventario").is(":checked")) salida_inventario = 1;
        var lista_embarque = 0; if($("#lista_embarque").is(":checked")) lista_embarque = 1;
        var auditoria_embarque = 0; if($("#auditoria_embarque").is(":checked")) auditoria_embarque = 1;
        var discrepancia = 0; if($("#discrepancia").is(":checked")) discrepancia = 1;
        var imprimir_asn = 0; if($("#imprimir_asn").is(":checked")) imprimir_asn = 1;
        var imp_archivo_despacho = 0; if($("#imp_archivo_despacho").is(":checked")) imp_archivo_despacho = 1;
        var des_aviso_despacho = 0; if($("#des_aviso_despacho").is(":checked")) des_aviso_despacho = 1;
        var des_archivo_despacho = 0; if($("#des_archivo_despacho").is(":checked")) des_archivo_despacho = 1;
        var kardex_consolidado = 0; if($("#kardex_consolidado").is(":checked")) kardex_consolidado = 1;

        $.ajax({
            url: '/api/configuraciongeneral/update/index.php',
            data: {
                action: 'saveDatosConfigAlmacen',//'saveSurtidoCompleto',
                id_almacen: $("#id_almacen").val(),
                lista_empaque: lista_empaque,
                entrega_programada: entrega_programada,
                salida_inventario: salida_inventario,
                lista_embarque: lista_embarque,
                auditoria_embarque: auditoria_embarque,
                discrepancia: discrepancia,
                imprimir_asn: imprimir_asn,
                imp_archivo_despacho: imp_archivo_despacho,
                des_aviso_despacho: des_aviso_despacho,
                des_archivo_despacho: des_archivo_despacho,
                kardex_consolidado: kardex_consolidado
            },
            dataType: 'json',
            method: 'POST'
        }).done(function(data){
            if(data.success)
            {
                swal("Éxito", "Cambios Realizados", "success");
                window.location.reload();
            }
            else
                swal("Error", "Ha ocurrido un error, intente nuevamente por favor", "error");
        });

    }

    $("#decimales_cantidad_conf").val($("#decimales_cantidad").val());
    $("#decimales_costo_conf").val($("#decimales_costo").val());

</script>