<link href="/css/plugins/iCheck/custom.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<div class="wrapper wrapper-content" style="padding-bottom: 10px">
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Acerca de</h5>
                </div>
                <div class="ibox-content">


<table width="494" border="1" align="center">

  <tr>

    <td colspan="2" align="center"><strong>Acerca de AssistPro ADL WMS</strong></td>

  </tr>

  <tr>

    <td width="182">Versión</td>

    <td width="296">&nbsp;</td>

  </tr>

  <tr>

    <td>Actualización</td>

    <td>Diciembre 2020</td>

  </tr>

  <tr>

    <td>Registrado a nombre de:</td>

    <td>&quot;Variable de Empresa&quot;</td>

  </tr>

  <tr>

    <td>&nbsp;</td>

    <td>&nbsp;</td>

  </tr>

</table>

<p>&nbsp;</p>

<table width="494" border="1" align="center">

  <tr>

    <td colspan="2" align="center"><strong>Información Técnica</strong></td>

  </tr>

  <tr>

    <td width="199">Manual de Usuario</td>

    <td width="279"> http://assistprowms.com/manuales/AssistProWMS</td>

  </tr>

  <tr>

    <td>Email</td>

    <td>soporte@adventech-logistica.com</td>

  </tr>

  <tr>

    <td>Sitio Web del Sistema</td>

    <td>https://adventech-soluciones.com</td>

  </tr>

 <tr>

    <td>Sitio Web de la empresa</td>

    <td>https://adventech-logistica.com</td>

  </tr>

  <tr>

    <td colspan="2" align="center"><p><img src="app/template/page/acercade/logo/AssistProWMS-Logo.png" height="251" /></p>

<p><strong>Copyright 2020 Adventech Logística S. A. de C. V.</strong></p></td>

  </tr>

</table>



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
                action: 'get'
            },
            dataType: 'json',
            method: 'GET'
        }).done(function(data){
            //var checked = data.codigo.split("-");
            $(data).each(function(i,e){
                //console.log("i = ", i, " - e = ", e);
                $("#"+e.cve_conf).val(e.Valor);
            });
        });


    });

    function save(){

        var campos  = ["limite_surtido", "tituloguiaembarque", "pieguiaembarque"];
        var valores = [$("#limite_surtido").val(), $("#tituloguiaembarque").val(), $("#pieguiaembarque").val()];

        //console.log("campos = ", campos, " Valores = ", valores);
        $.ajax({
            url: '/api/configuraciongeneral/update/index.php',
            data: {
                action: 'save',
                campos: campos,
                valores: valores
            },
            dataType: 'json',
            method: 'POST'
        }).done(function(data){
            if(data.success)
                swal("Éxito", "Cambios Realizados", "success");
            else
                swal("Error", "Ha ocurrido un error, intente nuevamente por favor", "error");
        });

    }
</script>