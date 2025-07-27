<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<link href="/css/plugins/dataTables/dataTables1.min.css" rel="stylesheet"/>
<link href="/css/plugins/dataTables/buttons.dataTables.min.css" rel="stylesheet"/>
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<style>
    .bt{

        margin-right: 10px;
    }

    .btn-blue{

        background-color: blue !important;
        border-color: blue !important;
        color: white !important;
    }

    .verde{

        background: #71ff33 ;
    }

    .verde > td{

        background: #71ff33;
    }

    .rojo > td{

        background: #FF5233;
    }

    .rojo {

        background: #FF5233;
    }


    .amarillo > td{

        background:  #fffc33 ;
    }

    .amarillo {

        background: #fffc33;
    }

    .aa{

        width: 30px;
        height: 30px;
        margin-left: auto;
        margin-right: auto;
        border-radius: 50%;

    }

</style>
<div class="wrapper wrapper-content  animated " id="list">
    <h3>Reabastecer PTL </h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="tabs-container">
                <ul class="nav nav-tabs">
                    <!--<li class="active"><a data-toggle="tab" href="#tab-1"> Por Producto</a></li>-->
                    <li class="active"><a data-toggle="tab" href="#tab-2" class="aaa">PTL</a></li>
                </ul>
                <div class="tab-content">
                    <div id="tab-1" class="tab-pane ">
                        <div class="panel-body">
                            <div class="ibox ">
                                <div class="ibox-title">
                                    <div class="row">
                                        <div class="col-lg-4">

                                        </div>

                                    </div>
                                </div>
                                <div class="ibox-content">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="example"  class="table table-hover table-striped no-margin">
                                            <thead>
                                                <tr>
                                                    <th>Producto</th>
                                                    <th>Descripcion</th>
                                                    <th>Grupo de Articulo</th>
                                                    <th>Unidad</th>
                                                    <th>Maximo</th>
                                                    <th>Minimo</th>
                                                    <th>Existencia</th>
                                                    <th>Reabastecer</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>

                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="tab-2" class="tab-pane active">
                        <div class="panel-body">
                            <div class="ibox ">
                                <div class="ibox-title">
                                    <div class="row">
                                        <div class="col-lg-4">

                                        </div>

                                    </div>
                                </div>
                                <div class="ibox-content">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="table-info"  class="table table-hover table-striped no-margin">
                                            <thead>
                                                <tr>
                                                    <th></th>
                                                    <th>Acciones</th>
                                                    <th>Clave de Producto</th>
                                                    <th>Descripción</th>
                                                    <th>BL</th>
                                                    <th>Pasillo</th>
                                                    <th>Rack</th>
                                                    <th>Nivel</th>
                                                    <th>Sección</th>
                                                    <th>Posición</th>
                                                    <!--th>Peso Màx.</th>
                                                    <th>Volumen (m3)</th>
                                                    <th>Dimensiones (Lar. X Anc. X Alt. )</th-->
                                                    <th>Maximo</th>
                                                    <th>Minimo</th>
                                                    <th>Existencia</th>
                                                    <th>Reabastecer</th>
                                                    <th>Status</th>
                                                    
                                                </tr>
                                            </thead>

                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="av" />

<!-- Mainly scripts -->
<!-- Mainly scripts -->
<script src="/js/jquery-2.1.4.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="/js/moment.js"></script>

<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>

<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>

<script src="/js/plugins/ladda/spin.min.js"></script>
<script src="/js/plugins/ladda/ladda.min.js"></script>
<script src="/js/plugins/ladda/ladda.jquery.min.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>

<!-- Select -->
<script src="/js/select2.js"></script>
<!-- Data picker -->
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script src="/js/plugins/dataTables/dataTables.min.js"></script>
<script src="/js/plugins/dataTables/dataTables_bootstrap.min.js"></script>
<script src="/js/plugins/dataTables/dataTables.buttons.min.js"></script>
<script src="/js/plugins/dataTables/buttons.flash.min.js"></script>
<script src="/js/plugins/dataTables/jszip.min.js"></script>
<script src="/js/plugins/dataTables/pdfmake.min.js"></script>
<script src="/js/plugins/dataTables/vfs_fonts.js"></script>
<script src="/js/plugins/dataTables/buttons.html5.min.js"></script>
<script src="/js/plugins/dataTables/buttons.print.min.js"></script> 
<script src="/js/plugins/chosen/chosen.jquery.js"></script>
<script src="/js/utils.js"></script> 

<script type="text/javascript">

    var tableDataInfo = new TableDataRest(),
        TABLE = null,
        buttons = [{
                    extend: 'excelHtml5',
                    title: 'Reabastecer Picking',
                    customize: function() { swal("Descargando Excel", "Su descarga empezara en breve", "success");}
                    },
                    {
                    extend: 'pdfHtml5',
                    title: 'Reabastecer Picking',
                    orientation: 'portrait',
                    pageSize: 'A4',
                    download: 'open',
                    customize: function() { swal("Descargando PDF", "Su descarga empezara en breve", "success");},
                    }
                ];

    searchData();
    function searchData(){
        $.ajax({
            type: "GET",
            dataType: "json",
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/reabasto/lista/ptl.php?m=2',
            success: function(data) {
                window.console.log(data);
                fillTableInfo(data);
            },
            error:function(res){
                window.console.log(res);
            }
        });

    }

    function fillTableInfo(node){

        var data = [];

        DATA = node;

        for(var i = 0; i < node.length ; i++){

            var state = 'green';

            if(node[i].sta === "amarillo")
                state = 'yellow'
            else if(node[i].sta === "rojo")
                state = 'red';

            data.push([
                '<input type="checkbox" class="editor-active">',
                '<i class="fa fa-asterisk"></i>',
                node[i].idy_ubica,
                node[i].des_articulo,
                node[i].CodigoCSD,
                node[i].cve_pasillo,
                node[i].cve_rack,
                node[i].cve_nivel,
                node[i].Seccion,
                node[i].Ubicacion,
                /*node[i].PesoMaximo,
                node[i].volumen,
                node[i].dim,*/
                node[i].maximo,
                node[i].minimo,
                node[i].existencia,
                node[i].reabastecer,
                '<i class="fa fa-bell" style="color : '+state+'"></i>',
                
                ]);
        }
        tableDataInfo.destroy();
        tableDataInfo.init("table-info", buttons, true, data);
    }
/*
        $('#example2').DataTable( {
            "processing": true,
            dom: 'Bfrtip',
            "bFilter": false,
            responsive: true,
            serverSide:true,
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
            "columns": [ 
                { "data": "accioness",formatter: imageFormat2 },
                { "data": "idy_ubica" },
                { "data": "des_articulo" },
                { "data": "CodigoCSD" },
                { "data": "cve_pasillo" },
                { "data": "cve_rack" },
                { "data": "cve_nivel" },
                { "data": "Seccion" },
                { "data": "Ubicacion" },
                { "data": "PesoMaximo" },
                { "data": "volumen" },
                { "data": "dim" },
                { "data": "maximo" },
                { "data": "minimo" },
                { "data": "existencia" },
                { "data": "reabastecer" },
                { "data": "sta",formatter: rowcolor },
                { "data": "accioness",formatter: imageFormat }
            ],
            select: {
                style:    'os',
                selector: 'td:first-child'
            },
            "pageLength": 30,
            "ajax": {
            "url": "/api/reabasto/lista/ptl.php?m=2",
            "type": "GET"	
        },
                                 "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
            // Bold the grade for all 'A' grade browsers
*/

    

</script>
