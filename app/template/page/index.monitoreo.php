<?php 
    include_once $_SERVER['DOCUMENT_ROOT']."/app/host.php";
    include_once $_SERVER['DOCUMENT_ROOT']."/config.php";
    $status = [];
    
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn, 'utf8');
    $sql = "SELECT ESTADO AS status, CONCAT(UCASE(LEFT(DESCRIPCION, 1)), LCASE(SUBSTRING(DESCRIPCION, 2))) AS descripcion FROM `cat_estados` WHERE ESTADO <> '*' ORDER BY descripcion ASC;";
    $query = mysqli_query($conn, $sql);

    if($query->num_rows > 0){
        $status = mysqli_fetch_all($query, MYSQLI_ASSOC);
    }
?>

<script src="/js/pdfmake.min.js"></script>
<script src="/js/vfs_fonts.js"></script>
<script src="/js/jszip.min.js"></script>
<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="/js/moment.js"></script>
<script src="/js/moment-with-locales.js"></script>
<!-- Peity -->
<script src="/js/plugins/peity/jquery.peity.min.js"></script>

<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.js"></script>

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


<style type="text/css">
    .text-green {
        color: greenyellow;
    }
    
    .text-blue {
        color: deepskyblue;
    }
    
    .text-yellow {
        color: yellow;
    }
    
    .text-red {
        color: red;
    }
    
    #grid-table tbody tr td {
        /*text-align: center;*/
    }
</style>

<div class="wrapper wrapper-content" style="padding-bottom: 10px">
    <div class="wrapper wrapper-content">
        <h3>Monitoreo de Entregas</h3>
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status">Status:</label>
                                    <select class="chosen-select form-control" id="status" name="status">
                                        <option value="">Seleccione status</option>
                                        <?php if(!empty($status)): ?>
                                            <?php foreach($status as $s): ?>
                                                <option value="<?php echo $s['status']?>"><?php echo $s['descripcion'] ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="input-group" style="margin-top: 23px">
                                    <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                    <div class="input-group-btn">                                        
                                        <button onclick="filtrar()" type="submit" class="btn btn-primary">
                                            <span class="fa fa-search"></span> Buscar
                                        </button>                                                                                
                                    </div>
                                    <div class="input-group-btn text-right"> 
                                        <button id="exportExcel" class="btn btn-primary">
                                            <i class="fa fa-file-excel-o"></i>
                                            Exportar a Excel
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>

                    <div class="ibox-content">
                        <div class="jqGrid_wrapper">
                            <table id="grid-table"></table>
                            <div id="grid-pager"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-md-12">
        <div class="footer">
            <div>
                <strong>Copyright </strong> AssistPro ADL ® Todos los derechos reservados. &copy; 2017
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function($) {
        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";

        //resize to fit page size
        $(window).on('resize.jqGrid', function() {
                $(grid_selector).jqGrid('setGridWidth', $(".jqGrid_wrappert").width() - 60);
            })
            //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
            if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $(grid_selector).jqGrid('setGridWidth', parent_column.width());
                }, 0);
            }
        })
        var lastsel;

        $(grid_selector).jqGrid({
            url: '/api/v2/monitoreo-de-entrega/paginate',
            datatype: 'json',
            mtype: 'GET',
            postData: {
                action: 'getDeliverys',
                search: $("#txtCriterio").val(),
                status: $("#status").val(),
            },
            shrinkToFit: false,
            height: 'auto',
            width: null,
            colModel: [
                {
                    label: 'Fecha de Factura / Entrega',
                    name: 'fecha_factura',
                    index: 'fecha_factura',
                    editable: false,
                    sortable: false
                }, {
                    label: '# Documento',
                    name: 'documento',
                    index: 'documento',
                    editable: false,
                    sortable: false,
                    width: 100
                }, {
                    label: 'C.P',
                    name: 'cp',
                    index: 'cp',
                    editable: false,
                    sortable: false,
                    width: 100
                }, {
                    label: 'Calle / Num Destino',
                    name: 'calle_destino',
                    index: 'calle_destino',
                    editable: false,
                    sortable: false,
                    width: 250
                }, {
                    label: 'Colonia Destino',
                    name: 'colonia_destino',
                    index: 'colonia_destino',
                    editable: false,
                    sortable: false,
                    width: 250
                }, {
                    label: 'Ciudad o Municipio de Envío',
                    name: 'ciudad_envio',
                    index: 'ciudad_envio',
                    editable: false,
                    sortable: false,
                    width: 250
                }, {
                    label: 'Estado de Envío',
                    name: 'estado_envio',
                    index: 'estado_envio',
                    editable: false,
                    sortable: false,
                    width: 250
                }, {
                    label: 'Status',
                    name: 'status',
                    index: 'status',
                    editable: false,
                    sortable: false
                }, {
                    label: 'Asignado',
                    name: 'cancelado',
                    index: 'cancelado',
                    editable: false,
                    sortable: false,
                    align: 'center'
                }, {
                    label: 'Cancelado',
                    name: 'cancelado',
                    index: 'cancelado',
                    editable: false,
                    sortable: false,
                    width: 100,
                    align: 'center'
                }, {
                    label: 'Surtido',
                    name: 'surtido',
                    index: 'surtido',
                    editable: false,
                    sortable: false
                }, {
                    label: 'Validado',
                    name: 'validado',
                    index: 'validado',
                    editable: false,
                    sortable: false
                }, {
                    label: 'Empacado',
                    name: 'empacado',
                    index: 'empacado',
                    editable: false,
                    sortable: false
                }, {
                    label: 'Fecha y Hora de Surtido',
                    name: 'fecha_surtido',
                    index: 'fecha_surtido',
                    width: 200,
                    editable: false,
                    sortable: false
                }, {
                    label: 'Fecha y Hora de Validación',
                    name: 'fecha_validacion',
                    index: 'fecha_validacion',
                    width: 200,
                    editable: false,
                    sortable: false
                }, {
                    label: 'Fecha y Hora de Empaque',
                    name: 'fecha_empaque',
                    index: 'fecha_empaque',
                    width: 200,
                    editable: false,
                    sortable: false
                }, {
                    label: 'Guía',
                    name: 'guia',
                    index: 'guia',
                    editable: false,
                    sortable: false,
                    width: 210,
                    align: 'center'
                }, {
                    label: 'Almacén',
                    name: 'almacen',
                    index: 'almacen',
                    editable: false,
                    sortable: false,
                    width: 100,
                    align: 'center'
                }, {
                    label: 'Tránsito',
                    name: 'transito',
                    index: 'transito',
                    editable: false,
                    sortable: false
                }, {
                    label: 'Entregado',
                    name: 'entregado',
                    index: 'entregado',
                    editable: false,
                    sortable: false
                }, {
                    label: 'Días de Tránsito',
                    name: 'dias_transito',
                    index: 'dias_transito',
                    editable: false,
                    sortable: false,
                    align: 'center'
                }, {
                    label: 'Fecha de Recepción',
                    name: 'fecha_recepcion',
                    index: 'fecha_recepcion',
                    editable: false,
                    sortable: false
                }, {
                    label: 'Persona de Recepción',
                    name: 'persona_recepcion',
                    index: 'persona_recepcion',
                    editable: false,
                    sortable: false
                }
            ],
            rowNum: 10,
            loadonce: true, 
            viewrecords: true,
            rowList: [10, 20, 30],
            pager: pager_selector,
            viewrecords: true,
            gridComplete: function() {
            }
        });

        // Setup buttons
        $("#grid-table").jqGrid('navGrid', '#grid-pager', {
            edit: false,
            add: false,
            del: false,
            search: false
        }, {
            height: 200,
            reloadAfterSubmit: true
        });


        $(window).triggerHandler('resize.jqGrid');

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
        //$('select').chosen();
    });



    $("#exportExcel").on("click", function(){
        var date = new Date();
        var hora = date.getHours();
        var minutos = date.getMinutes();
        if (hora>12){
            hora = hora-12;
        }
        if (hora==0)
            hora = 12;
        if (minutos<=9)
            minutos="0"+minutos;

        var Fecha = date.getDate() +'-'+ date.getMonth() +'-'+ date.getFullYear() +' '+hora+':'+minutos;
                    
        $("#grid-table").jqGrid("exportToExcel",{
            includeLabels : true,
            includeGroupHeader : true,
            includeFooter: true,
            fileName : "Monitoreo de Entregas "+Fecha+".xls",
            maxlength : 40 // maxlength for visible string data
        })
    })

    function filtrar() {
        $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    search: $("#txtCriterio").val(),
                    status: $("#status").val(),
                    action: 'getDeliverys'
                },
                datatype: 'json',
                page: 1
            })
            .trigger('reloadGrid', [{
                current: true
            }]);
    }

</script>