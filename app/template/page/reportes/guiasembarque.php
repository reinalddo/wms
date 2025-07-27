<?php


?>
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<link href="/css/plugins/dataTables/jquery.dataTables.min.css" rel="stylesheet"/>
<link href="/css/plugins/dataTables/buttons.dataTables.min.css" rel="stylesheet"/>
<style>
    .bt{

        margin-right: 10px;
    }

    .btn-blue{

        background-color: blue !important;
        border-color: blue !important;
        color: white !important;
    }

</style>
<div class="modal" id="pleaseWaitDialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" id="headModal">
                <h2>Cargando...</h2>
            </div>
            <div class="modal-body">
                <div class="progress progress-striped active">
                    <div class="progress-bar" style="width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="rangeDialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" id="headModalRango">
                <h2>Imprimir Rango</h2>
            </div>
            <div class="modal-body">
                <div class="col-lg-4">
                    <label>Desde</label>
                    <input id="desde" type="text" maxlength="4" placeholder="Desde" class="form-control" name="desde" value="1">
                    <input id="hiddenFolPedidoCon" type="hidden" name="hiddenFolPedidoCon">
                    <input id="hiddenFacturaMadre" type="hidden" name="hiddenFacturaMadre">
                </div>
                <div class="col-lg-4">
                    <label>Hasta</label>
                    <input id="hasta" type="text" maxlength="4" placeholder="Hasta" class="form-control" name="hasta">
                </div>
                <br>
                <br>
                <br>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal" id="btnCancel">Cerrar</button>
                <button type="button" class="btn btn-primary ladda-button" data-style="contract" id="btnPrintRange">Imprimir</button>
            </div>
        </div>
    </div>
</div>

<div class="wrapper wrapper-content  animated " id="list">
    <h3>Gu&iacute;as de Embarque</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4">

                        </div>

                    </div>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="example" class="table table-hover table-striped no-margin">
                            <thead>
                            <tr>
                                <th>Cod</th>
                                <th>Punto de Venta</th>
                                <th>Factura Madre</th>
                                <th>Factura Hija</th>
                                <th>Orden de Compra</th>
                                <th>Cajas</th>
                                <th>Cantidad</th>
                                <th></th>
                                <th></th>
                            </tr>
                            </tr>
                            </thead>

                        </table>
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

<!-- Peity -->
<script src="/js/plugins/peity/jquery.peity.min.js"></script>

<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.min.js"></script>

<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>

<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>

<script src="/js/plugins/ladda/spin.min.js"></script>
<script src="/js/plugins/ladda/ladda.min.js"></script>
<script src="/js/plugins/ladda/ladda.jquery.min.js"></script>

<!-- Select -->
<script src="/js/select2.js"></script>
<!-- Data picker -->
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script src="/js/plugins/dataTables/jquery.dataTables.min.js"></script>
<script src="/js/plugins/dataTables/dataTables.buttons.min.js"></script>
<script src="/js/plugins/dataTables/buttons.flash.min.js"></script>
<script src="/js/plugins/dataTables/jszip.min.js"></script>
<script src="/js/plugins/dataTables/pdfmake.min.js"></script>
<script src="/js/plugins/dataTables/vfs_fonts.js"></script>
<script src="/js/plugins/dataTables/buttons.html5.min.js"></script>
<script src="/js/plugins/dataTables/buttons.print.min.js"></script>

<script>
    $(document).ready(function(){
        //$("#basic").select2();

        $('#example').DataTable( {
            "processing": true,
            dom: 'tipr',
            responsive: true,
            "pagingType": "full_numbers",
            "language": {
                "lengthMenu": "Mostrando _MENU_ registros",
                "zeroRecords": "Sin Registros - :(!!",
                "info": "Pagina _PAGE_ de _PAGES_",
                "infoEmpty": "No Existen Registros",
                "infoFiltered": "(Filtrando de un total de _MAX_ registros)",
                "sSearch": "Filtrar:",
                "sProcessing":   	"Cargando...",

                "oPaginate": {
                    "sNext": "Sig",
                    "sPrevious": "Ant",
                    "sLast": "Ultimo",
                    "sFirst":    	"Primero",
                }
            },
            serverSide:true,
            "ajax": {
                "url": "/api/reportes/lista/guiasembarque.php",
                "type": "GET",
                "columns": [
                    { "data": "Fol_PedidoCon" },
                    { "data": "RazonComercial" },
                    { "data": "Fact_Madre" },
                    { "data": "facturahija" },
                    { "data": "No_OrdComp" },
                    { "data": "Tot_Cajas" },
                    { "data": "Num_cantidad" },
                    { "data": "Fol_PedidoCon1" },
                    { "data": "Fol_PedidoCon1" }
                ],


            },
            "createdRow": function ( row, data, index ) {
                var _Fol_PedidoCon = data[0];
                var _FacturaMadre = data[3];
                $('td', row).eq(0).html(data[0]);
                $('td', row).eq(1).html(data[1]);
                $('td', row).eq(2).html(data[3]);
                $('td', row).eq(3).html(data[4]);
                $('td', row).eq(4).html(data[5]);
                $('td', row).eq(5).html(data[11]);
                $('td', row).eq(6).html(data[7]);
                $('td', row).eq(7).html('<button type="button" class="btn btn-success" onClick="printEtiqueta(\''+_Fol_PedidoCon+'\', \''+_FacturaMadre+'\')"><i class="fa fa-print"></i></button>');
                $('td', row).eq(8).html('<button type="button" class="btn btn-success" onClick="ImprimirRango(\''+_Fol_PedidoCon+'\', \''+_FacturaMadre+'\')"><i class="fa fa-print"></i>Imprimir Rango</button>');
                $(row).attr('id', 'tr_'+_Fol_PedidoCon);
            }

        } );

/*        $(".dt-button.buttons-csv").attr('class','btn btn-info btn-blue btn-sm bt');
        $(".dt-button.buttons-excel").attr('class','btn btn-primary btn-sm bt');
        $(".dt-button.buttons-pdf").attr('class', 'btn btn-danger btn-sm bt');
        //$(".paginate_button").attr('class', 'btn btn-primary btn-sm');
        $('input[type=search]').attr('class', 'form-control input-sm');*/


    });

    function printEtiqueta(_Fol_PedidoCon, _FacturaMadre){
        var title = '';
        var cia = <?php echo $_SESSION['cve_cia'] ?>;
        var content = '';

        $.ajax({
            url: "/api/reportes/update/index.php",
            type: "POST",
            data: {
                "action":"guiasembarqueshowreport",
                "FolPedidoCon" : _Fol_PedidoCon,
                "FacturaMadre" : _FacturaMadre
            },
            success: function(data, textStatus, xhr){
                var a = document.createElement('a');
                a.href='/api/reportes/generar/etiqueta.php';
                a.target = '_blank';
                document.body.appendChild(a);
                a.click();
            }
        });

    }

    var $modal0 = null;
    var totalEtiq = 0;

    function ImprimirRango(_Fol_PedidoCon, _FacturaMadre){
        $("#desde").val("1");
        $("#hasta").val("");
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                "action":"setRangoGuiasEmbarqueShowReport",
                "FolPedidoCon" : _Fol_PedidoCon,
                "FacturaMadre" : _FacturaMadre
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url: '/api/reportes/update/index.php',
            success: function(data) {
                $("#headModalRango").html("<h2>Imprimir Rango Consolidado " + _Fol_PedidoCon + "</h2>");
                $("#hiddenFolPedidoCon").val(_Fol_PedidoCon);
                $("#hiddenFacturaMadre").val(_FacturaMadre);
                $("#hasta").val(data.total);
                totalEtiq = parseInt(data.total);
                $modal0 = $("#rangeDialog");
                $modal0.modal('show');
            }
        });
    }

    $('#desde').keypress(function(e) {
        var a = [];
        var k = e.which;

        for (i = 48; i < 58; i++)
            a.push(i);

        if (!(a.indexOf(k)>=0))
            e.preventDefault();
    });

    $('#hasta').keypress(function(e) {
        var a = [];
        var k = e.which;

        for (i = 48; i < 58; i++)
            a.push(i);

        if (!(a.indexOf(k)>=0))
            e.preventDefault();
    });

    $('#btnPrintRange').on('click', function() {

        if ($("#desde").val()=="") {
            return;
        }

        var d = parseInt($("#desde").val());

        if (d<1) {
            return;
        }

        if ($("#hasta").val()=="") {
            return;
        }

        var h = parseInt($("#hasta").val());

        if (d>h) {
            return;
        }

        if (h>totalEtiq) {
            return;
        }

        $modal0.modal('hide');

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                desde : $("#desde").val(),
                hasta : $("#hasta").val(),
                FolPedidoCon : $("#hiddenFolPedidoCon").val(),
                FacturaMadre : $("#hiddenFacturaMadre").val(),
                action : "printRangeGuiasEmbarqueShowReport"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/reportes/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    var a = document.createElement('a');
                    a.href='/api/reportes/generar/etiquetaRango.php';
                    a.target = '_blank';
                    document.body.appendChild(a);
                    a.click();
                    $modal0.modal('hide');
                } else {
                    alert(data.err);
                }
            }
        });
    });

</script>