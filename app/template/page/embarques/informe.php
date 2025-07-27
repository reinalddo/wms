<?php 
$sql = "SELECT DISTINCT Fol_PedidoCon AS folio FROM `th_consolidado` WHERE Fol_PedidoCon IS NOT NULL ORDER BY Fol_PedidoCon ASC;";
$query = mysqli_query(\db2(), $sql);
$folios = mysqli_fetch_all($query, MYSQLI_ASSOC);
?>
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<div class="wrapper wrapper-content" style="padding-bottom: 10px">
    <div class="row">
        <div class="col-lg-6 col-lg-offset-3">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Informe de Embarque</h5>
                </div>
                <div class="ibox-content">
                    <form action="/embarques/informe" method="post" id="formEmbarque">
                        <div class="form-group">
                            <label>Seleccione Factura Madre</label>
                            <select class="form-control"name="folio" id="folio">
                                <option value="">Seleccione</option>
                                <?php if(!empty($folios)): ?>
                                <?php foreach($folios as $folio): ?>
                                <option value="<?php echo $folio['folio']?>"><?php echo $folio['folio']?></option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <input type="hidden" name="nofooternoheader" value="true">
                        <div style="text-align: right">
                            <button type="submit" class="btn btn-primary">Generar PDF</button>
                        </div>
                    </form>
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

<script src="/js/plugins/chosen/chosen.jquery.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>

<script type="text/javascript">
    (function($){
        $('select#folio').chosen();
        $("#formEmbarque").on('submit', function(e){
            e.preventDefault();
            if($("#folio").val() === ''){
                swal("Error", "Selecciona una Factura", "error");
            }else{

                
                var gg=$("#folio").val();

                var title = 'Informe de embarques';
                var cia = <?php echo $_SESSION['cve_cia'] ?>;
                var content = '';

                $.ajax({
                    url: "/api/embarques/update/index.php",
                    type: "POST",
                    data: {
                        "action":"informe",
                        "folio":gg
                    },
                    success: function(data, textStatus, xhr){
                        var content_wrapper = document.createElement('div');
                        var table = document.createElement('table');
                        table.style.width = "100%";
                        table.style.borderSpacing = "0";
                        table.style.borderCollapse = "collapse";
                        var thead = document.createElement('thead');
                        var tbody = document.createElement('tbody');
                        var head_content = '<tr>'+
                            '<th style="border: 1px solid #ccc">Cedi</th>'+
                            '<th style="border: 1px solid #ccc">Codigo</th>    '+
                            '<th style="border: 1px solid #ccc">Nombre del Almacen</th>'+
                            '<th style="border: 1px solid #ccc">Factura</th>   '+
                            '<th style="border: 1px solid #ccc">Cajas</th>  '+
                            '<th style="border: 1px solid #ccc">Unidades</th>  '+
                            '</tr>';
                        var body_content = '';
                        var data = JSON.parse(data).data;

                        data.forEach(function(item, index){
                            body_content += '<tr>'+
                                '<td style="border: 1px solid #ccc">'+item.CEDI+'</td> '+
                                '<td style="border: 1px solid #ccc">'+item.Cod+'</td>    '+
                                '<td style="border: 1px solid #ccc">'+item.Almacen+'</td>        '+
                                '<td style="border: 1px solid #ccc">'+item.OrdenCompra+'</td>'+
                                '<td style="border: 1px solid #ccc">'+item.Cajas+'</td>       '+
                                '<td style="border: 1px solid #ccc">'+item.Cantidad+'</td>     '+
                                '</tr>  ';                                                         

                        });
                        

                        tbody.innerHTML = body_content;
                        thead.innerHTML = head_content;
                        table.appendChild(thead);
                        table.appendChild(tbody);
                        content_wrapper.appendChild(table);
                        content = content_wrapper.innerHTML;

                        /*Creando formulario para ser enviado*/

                        var form = document.createElement("form");
                        form.setAttribute("method", "post");
                        form.setAttribute("action", "/api/reportes/generar/pdf.php");
                        form.setAttribute("target", "_blank");

                        var input_content = document.createElement('input');
                        var input_title = document.createElement('input');
                        var input_cia = document.createElement('input');
                        input_content.setAttribute('type', 'hidden');
                        input_title.setAttribute('type', 'hidden');
                        input_cia.setAttribute('type', 'hidden');
                        input_content.setAttribute('name', 'content');
                        input_title.setAttribute('name', 'title');
                        input_cia.setAttribute('name', 'cia');
                        input_content.setAttribute('value', content);
                        input_title.setAttribute('value', title);
                        input_cia.setAttribute('value', cia);

                        form.appendChild(input_content);
                        form.appendChild(input_title);
                        form.appendChild(input_cia);

                        document.body.appendChild(form);
                        form.submit();
                    }
                });

            }
        })
    })(jQuery);
</script>