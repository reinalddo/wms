<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<div class="wrapper wrapper-content" style="padding-bottom: 10px">    
    <div class="wrapper wrapper-content">
        <h3>Mensajes</h3>
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title">
                        <div style="text-align: right">
                            <button class="btn btn btn-primary" type="button" onclick="showModal()">Nuevo</button>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div id="grid">
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
</div>

<div class="modal inmodal" id="mensajeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <i class="fa fa-laptop modal-icon"></i>
                <h4 class="modal-title">Agregar Mensaje</h4>
            </div>
            <form id="mensajeForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Clave</label>
                        <input type="text" id="clave" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <input type="text" id="descripcion" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Mensaje</label>
                        <input type="text" id="mensaje" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Fecha Inicio</label>
                        <div class="input-group date" id="data_1">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="fecha_inicio" type="text" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Fecha Final</label>
                        <div class="input-group date" id="data_2">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="fecha_final" type="text" class="form-control">
                        </div>
                    </div>
                    <input type="hidden" id="hiddenAction">
                    <input type="hidden" id="hiddenID">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal" id="btnCancel">Cerrar</button>
                    <button type="submit" class="btn btn-primary" data-style="contract" id="btnSave">Guardar</button>
                </div>
            </form>
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
<script src="/js/plugins/jqGrid/jquery.jqGrid.js"></script>

<script src="https://momentjs.com/downloads/moment.js"></script>
<script src="https://momentjs.com/downloads/moment-with-locales.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>

<script type="text/javascript">
    $(function($) {
        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";

        //resize to fit page size
        $(window).on('resize.jqGrid', function () {
            $(grid_selector).jqGrid( 'setGridWidth', $(".page-content").width() - 60 );
        })
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) {
            if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $(grid_selector).jqGrid( 'setGridWidth', parent_column.width() );
                }, 0);
            }
        })

        $(grid_selector).jqGrid({
            url:'/api/utileria/mensajes.php',
            datatype: "json",
            shrinkToFit: true,
            height:'auto',
            mtype: 'POST',
            postData: {
                action: 'load'
            },
            colNames:["ID", "Clave", "Descripción", "Mensaje", "Fecha Inicio", "Fecha Final", "Acción"],
            colModel:[
                {name:'id',index:'id',editable:false, sortable:false, width: 100},
                {name:'clave',index:'clave',editable:false, sortable:false, width: 100},
                {name:'descripcion',index:'descripcion',editable:false, sortable:false, width: 100},
                {name:'mensaje',index:'mensaje',editable:false, sortable:false, width: 100},
                {name:'fecha_inicio',index:'fecha_inicio',editable:false, sortable:false, width: 100},
                {name:'fecha_fin',index:'fecha_fin',editable:false, sortable:false, width: 100},
                {name:'accion',index:'accion',editable:false, sortable:false, width: 100, formatter: getActtions},
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'id',
            viewrecords: true,
            sortorder: "desc",
        });

        // Setup buttons
        $(grid_selector).jqGrid('navGrid', pager_selector,
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );


        $(window).triggerHandler('resize.jqGrid');

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
        
        function getActtions(cell, xhr, row){
            var id = row[0];
            var html = "<div class='text-center'>";
            html += "<button class='btn' type='button' onclick='editMessage("+id+")'><i class='fa fa-pencil'></i></button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
            html += "<button class='btn' type='button' onclick='deleteMessage("+id+")'><i class='fa fa-trash-o'></i></button>";
            html += '</div>';
            return html;
        }
    });

    function ReloadGrid() {
        $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                action: 'load',
            }, datatype: 'json', page : 1})
            .trigger('reloadGrid',[{current:true}]);
        resizeGrid();
    }
    function resizeGrid(){
        $("#grid-table").jqGrid("setGridWidth", $("#grid").width(), true);
    }
    (function($){
        setTimeout(resizeGrid, 100);
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
        })(jQuery);
</script>

<script type="text/javascript">
    function editMessage(id){
        showModal('edit');
        $.ajax({
            dataType: 'json',
            method: 'post',
            url: '/api/utileria/mensajes.php',
            data: {
                action: 'loadOnce',
                id: id
            }
        }).done(function(data){
            $("#clave").val(data.clave);
            $("#descripcion").val(data.descripcion);
            $("#mensaje").val(data.mensaje);
            $("#fecha_inicio").val(data.fecha_inicio);
            $("#fecha_final").val(data.fecha_final);
            $("#hiddenAction").val('edit');
            $("#hiddenID").val(id);
        });
    }

    function deleteMessage(id){
        swal({
            title: 'Advertencia',
            text: '¿Está seguro de que desea eliminar este registro?',
            type: 'warning',
            confirmButtonText: 'Sí',
            cancelButtonText: 'No',
            showCancelButton: true,
            closeOnConfirm: false
        }, function(confirm){
            if(confirm){
                $.ajax({
                    url: '/api/utileria/mensajes.php',
                    method: 'post',
                    dataType: 'json',
                    data: {
                        action: 'delete',
                        id: id
                    }
                }).done(function(data){
                    swal(data.title, data.message, data.type);
                    ReloadGrid();
                })
            }
        });
    }

    function showModal(action = 'add'){
        $("#mensajeModal").modal('show');
        $("#mensajeForm").get()[0].reset();
        $("#hiddenAction").val(action);
    }
    function hideModal(action = 'add'){
        $("#mensajeModal").modal('hide');
        $("#mensajeForm").get()[0].reset();
        $("#hiddenAction").val(action);
    }
    $("#btnSave").on('click', function(e){
        e.preventDefault();
        $.ajax({
            dataType: 'json',
            method: 'post',
            url: '/api/utileria/mensajes.php',
            data: {
                clave: $("#clave").val(),
                descripcion: $("#descripcion").val(),
                mensaje: $("#mensaje").val(),
                fecha_inicio: moment($("#fecha_inicio").val(), 'DD-MM-YYYY').format("YYYY-MM-DD"),
                fecha_final: moment($("#fecha_final").val(), 'DD-MM-YYYY').format("YYYY-MM-DD"),
                action: $("#hiddenAction").val(),
                id: $("#hiddenID").val()
            }
        }).done(function(data){
            ReloadGrid();
            hideModal();
            swal(data.title, data.message, data.type);
        });
    });
</script>