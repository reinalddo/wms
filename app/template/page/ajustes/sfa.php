<link href="/css/plugins/iCheck/custom.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<div class="wrapper wrapper-content" style="padding-bottom: 10px">
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Configuración</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-lg-6">
                            <label>Habilitar Modulo SFA</label>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <div class="checkbox">
                                    <input type="checkbox" name="habilitar_sfa" id="habilitar_sfa" value="0" style="cursor: pointer;">
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

        $.ajax({
            url: '/api/configuraciongeneral/lista/index.php',
            cache: false,
            data: {
                action: 'getHabilitarSFA'
            },
            dataType: 'json',
            method: 'GET'
        }).done(function(data){
            console.log("data = ", data);

            if(data.valorSFA == 1)
            {
                $("#habilitar_sfa").prop("checked", true);
                $("#habilitar_sfa").prop("disabled", true);
            }
            else
                $("#habilitar_sfa").prop("checked", false);
        });


    });

    function save(){

        var valor = 0;
        if($("#habilitar_sfa").is(":checked"))
            valor = 1;

        //console.log("Valor = ", valor);

        $.ajax({
            url: '/api/configuraciongeneral/update/index.php',
            data: {
                action: 'saveHabilitarSFA',
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
</script>