<?php
$listaAlma = new \AlmacenP\AlmacenP();
?>
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/plugins/iCheck/custom.css" rel="stylesheet">
<link href="/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css" rel="stylesheet">
<!-- Sweet Alert -->
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">

<style>
    #list {
        width: 100%;
        height: 100%;
        position: absolute;
        left: 0px;
        z-index: 999;
    }

    #FORM {
        width: 100%;
        /*height: 100%;*/
        position: absolute;
        left: 0px;
        z-index: 999;
    }
</style>
<div class="wrapper wrapper-content  animated " id="list">
    <h3>Máximos y Mínimos</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Almacén</label>
                                <select name="almacen" id="almacen" class="chosen-select form-control">
                                    <option value="">Seleccione</option>
                                    <?php foreach( $listaAlma->getAll() AS $a ): ?>
                                    <option value="<?php echo $a->id; ?>"><?php echo "(".$a->clave.") - ".$a->nombre; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label>Tipo de ubicación</label>
                                <select name="almacen" id="tipo" class="chosen-select form-control">
                                    <option value="">Seleccione</option>
                                    <option value="Picking">Picking</option>
                                    <option value="PTL">PTL</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label>Tipo de Reabasto</label>
                                <select name="tipo_reabasto" id="tipo_reabasto" class="chosen-select form-control">
                                    <option value="">Seleccione</option>
                                    <option value="C">Caja</option>
                                    <option value="P">Pieza</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="input-group">
                                <input type="text" name="buscar" id="buscar" class="form-control input-sm">
                                <div class="input-group-btn">
                                    <button class="btn btn-sm btn-primary" onclick="buscar()">Buscar</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <button id="importMyM" type="button"  class="pull-left btn btn-primary permiso_registrar">
                                <i class="fa fa-file-excel-o"></i>
                                Importar Máximos y Mínimos
                            </button>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <br>
                            <div class="ibox-content">
                                <div class="jqGrid_wrapper">
                                    <table id="grid-tabla"></table>
                                    <div id="grid-page"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modalImportMyM" role="dialog">
    <div class="vertical-alignment-helper">
          <div class="modal-dialog vertical-align-center">
            <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Importar Máximos y Mínimos</h4>
                    </div>
                    <div class="modal-body">

                        <form id="form-import" action="import" method="post" enctype="multipart/form-data">

                            <div class="form-group">
                                <label>Seleccionar archivo excel para importar</label>
                                <input type="file" name="file" id="file" class="form-control" accept=".xlsx" required>
                            </div>
                        </form>
                        <div class="col-md-12">
                            <div class="progress" style="display:none">
                                <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar"
                                     aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                                    <div class="percent">0%</div >
                                </div>
                            </div>
                        </div>

                        <div class="row" id="loadgif" style="text-align: center;padding: 15px; display: none;font-size: 16px;top: 15px;position: relative;">
                            <div style="width: 50px;height: 50px;background-image: url(/img/load.gif);background-size: 100%;background-position: center;background-repeat: no-repeat;display: inline-flex;"></div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <div class="col-md-6" style="text-align: left">
                            <button id="btn-layoutMyM" type="button" class="btn btn-primary">Descargar Layout</button><!--cambiar layout-->
                        </div>
                        <div class="col-md-6" style="text-align: right">
                            <button id="btn-importMyM" type="button" class="btn btn-primary">Importar</button><!--funcion de import-->
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
          </div>
    </div>
</div>



<div class="modal fade" id="editar" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Editar Máximos y Mínimos</h4>
                </div>
                <div class="modal-body">
                    <h4 class="text-center" id="productName"></h4>
                    <form action="" id="guardar_mm" method="post">
                        <div class="form-group">
                            <label>Máximo</label>
                            <input type="text" class="form-control" name="maximo" id="maximo" required>
                        </div>
                        <div class="form-group">
                            <label>Mínimo</label>
                            <input type="text" class="form-control" name="minimo" id="minimo" required>
                        </div>
                        <input type="hidden" name="cve_articulo" id="cve_articulo">
                        <input type="hidden" name="idy_ubica" id="idy_ubica">
                        <input type="hidden" name="action" value="guardar">

                            <div><b>Manejar Reabasto con: </b></div>
                            <br>
                            <label for="rbcajas" style="font-weight: 600;font-size: 14px; -moz-user-select: none; -webkit-user-select: none;-ms-user-select: none; user-select: none;cursor: pointer;">
                            <input type="radio" name="tipo_reabasto" id="rbcajas" value="C">&nbsp;&nbsp;&nbsp;Cajas</label>
                            <br>
                            <label for="rbpiezas" style="font-weight: 600;font-size: 14px; -moz-user-select: none; -webkit-user-select: none;-ms-user-select: none; user-select: none;cursor: pointer;">
                            <input type="radio" name="tipo_reabasto" id="rbpiezas" value="P">&nbsp;&nbsp;&nbsp;Piezas</label>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="guardar">Guardar</button>
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
<script src="/js/plugins/chosen/chosen.jquery.js"></script>
<!-- iCheck -->
<script src="/js/plugins/iCheck/icheck.min.js"></script>
<!-- Sweet alert -->
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>

<script type="text/javascript">
    $('.i-checks').iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green'
    });
    /**
     * @author Ricardo Delgado.
     * Busca y selecciona el almacen predeterminado para el usuario.
     */ 
    function almacenPrede(){ 
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                idUser: '<?php echo $_SESSION["id_user"]?>',
                action: 'search_almacen_pre'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/almacenPredeterminado/index.php',
            success: function(data) {
                    //$("#almacen").val(data.codigo.id);
                if (data.success == true) {
                    //document.getElementById('almacen').value = data.codigo.id;
                    $("#almacen").val(data.codigo.id);
                    $('#almacen').trigger("chosen:updated");

                    //console.log("OK almacenPrede = ", data);
                    //console.log("OK almacenPrede->Codigo = ", data.codigo.id);
                    setTimeout(function() {
                        ReloadGrid();
                    }, 1000);
                }
            },
            error: function(res){
                window.console.log(res);
            }
        });
    }
    almacenPrede();
    //////////////////////////////////////////////////////////Aqui se contruye el Grid //////////////////////////////////////////////////////////

    $('#btn-layoutMyM').on('click', function(e) {
      //e.preventDefault();  //stop the browser from following
      //window.location.href = 'http://www.tinegocios.com/proyectos/wms/LAYOUT_OC_01.xlsx';//cambiar href por nuevo layout
      //console.log("Layout_EntradasRL", window.location.href, '/Layout/Layout_EntradasRL.xlsx');
      <?php //echo $_SERVER['HTTP_HOST']; ?>
      window.location.href = '/Layout/Layot_Maximos_Y_Minimos.xlsx';

    }); 

    $(function($) {
        var grid_selector = "#grid-tabla";
        var pager_selector = "#grid-page";

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

        /*************************************************************************************************/
        $(grid_selector).jqGrid({
            url: '/api/maximosyminimos/lista/index.php',
            mtype: "POST",
            shrinkToFit: false,
            cache: false,
            height:'auto',
            datatype: 'local',
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            colNames:['Acción','Clave', 'Descripción', 'BL', 'Tipo de Ubicación', 'Tipo Reabasto', 'Maximo', 'Minimo', 'Existencia', 'Reabasto (Piezas)', 'Reabasto (Cajas)',  'IDY','Almacen', 'Zona Almacenaje'],
            colModel:[
                {name:'myac',index:'', width:60, sortable:false, resize:false, formatter:imageFormat},
                {name:'clave',index:'clave',width:100, editable:false, sortable:false},
                {name:'descripcion',index:'descripcion',width:282, editable:false, sortable:false},
                {name:'BL',index:'BL',width:100, editable:false, sortable:false},
                {name:'tipo',index:'tipo',width:120, editable:false, sortable:false},
                {name:'tipo_reabasto',index:'tipo_reabasto',width:120, editable:false, sortable:false},
                {name:'maximo',index:'maximo', align: 'right', width:120, editable:false, sortable:false},
                {name:'minimo',index:'minimo', align: 'right', width:80, editable:false, sortable:false},
                {name:'existencia',index:'existencia', align: 'right', width:80, editable:false, sortable:false},
                {name:'reabastop',index:'reabastop', align: 'right', width:120, editable:false, sortable:false},
                {name:'reabasto',index:'reabasto', align: 'right', width:120, editable:false, sortable:false},
                {name:'id',index:'id',width:50, editable:false, sortable:false, hidden: true},
                {name:'almacen',index:'almacen',width:170, editable:false, sortable:false},
                {name:'zona',index:'zona',width:170, editable:false, sortable:false}
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'cve_gpoart',
            loadComplete: function(data){console.log("SUCCESS Init: ", data);},
            viewrecords: true,
            sortorder: "desc"
        });

        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
        function imageFormat( cellvalue, options, rowObject ){
            var articulo = rowObject[1];
            var ubicacion = rowObject[11];
            var maximo = rowObject[6];
            var minimo = rowObject[7];
            var descripcion = rowObject[2];
            var tipo_reabasto = rowObject[5];
            var html = '';

            if($("#permiso_editar").val() == 1)
            html = `<a href="#" onclick="editar('${articulo}', '${ubicacion}', '${maximo}', '${minimo}', '${descripcion}', '${tipo_reabasto}')"><i class="fa fa-edit" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;`;
            return html;
        }

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });

    function ReloadGrid() {
        $('#grid-tabla').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                almacen: $("#almacen").val(),
                buscar: $("#buscar").val(),
                type:$("#tipo").val(),
                tipo_reabasto:$("#tipo_reabasto").val(),
                action: 'load'
            }, datatype: 'json'})
            .trigger('reloadGrid',[{current:true}]);
    }

</script>

<script>


    $("#importMyM").on("click", function(){
        //console.log("mostrar modal de importador");
        $moda200 = $("#modalImportMyM");
        $moda200.modal('show');

        $("#almacenes").change();
    });


    $(document).ready(function(){
        $("div.ui-jqgrid-bdiv").css("max-height",$("#list").height()-80);
        $(function() {
            $('.chosen-select').chosen();
            $('.chosen-select-deselect').chosen({ allow_single_deselect: true });
        });
    });
    $("#almacen").on('change', function(e){
        if(e.target.value !== ''){
            ReloadGrid();
        }
    })
    $("#buscar").on('keyup', function(e){
        if(e.keyCode === 13){
            ReloadGrid();
        }
    })
    function buscar(){
        ReloadGrid();
    }
    function editar(articulo, ubicacion, maximo, minimo, descripcion, tipo_reabasto){

        //$("input[name='cve_articulo']").val(articulo);
        //$("input[name='idy_ubica']").val(ubicacion);
        //$("input[name='maximo']").val(maximo);
        //$("input[name='minimo']").val(minimo);
        $("#cve_articulo").val(articulo);
        $("#idy_ubica").val(ubicacion);
        $("#maximo").val(maximo);
        $("#minimo").val(minimo);

        console.log("cve_articulo = ", articulo, ", idy_ubica = ", ubicacion, ", maximo = ", maximo, ", minimo = ", minimo);
        $("#productName").text(descripcion)
        $("#editar").modal('toggle');
        if(tipo_reabasto == 'Cajas')
        {
            $("#rbcajas").prop("checked", true);
            $("#rbpiezas").prop("checked", false);
        }
        else
        {
            $("#rbcajas").prop("checked", false);
            $("#rbpiezas").prop("checked", true);
        }
    }


    $("#guardar").on('click', function(e){
        e.preventDefault();
        //var minimo = parseInt($("input[name='minimo']").val())  ;
        //var maximo = parseInt($("input[name='maximo']").val())  ;

        var minimo = parseInt($("#minimo").val());
        var maximo = parseInt($("#maximo").val());
        var tipo_reabasto = ($('#rbcajas').is(':checked'))?('C'):('P') ;



        console.log("maximo = ", maximo, ", minimo = ", minimo, ", tipo_reabasto = ", tipo_reabasto);

        if(minimo > maximo){
            swal("Error", "El mínimo no puede ser mayor al máximo", "error");
        }else{
            var form = $("#guardar_mm");

        console.log("cve_articulo = ", $("#cve_articulo").val(), ", idy_ubica = ", $("#idy_ubica").val(), ", maximo = ", $("#maximo").val(), ", minimo = ", $("#minimo").val());

            $.ajax({
                url: '/api/maximosyminimos/update/index.php',
                type: 'POST',
                //dataType: 'json',
                //data: form.serialize(),
                data: {
                cve_articulo: $("#cve_articulo").val(),
                idy_ubica: $("#idy_ubica").val(),
                minimo: $("#minimo").val(),
                maximo: $("#maximo").val(),
                tipo_reabasto: tipo_reabasto,
                action: 'guardar'
            }
            }).done(function(data){
                console.log("SUCCESS: ", data);
            }).fail(function(data){
                console.log("ERROR: ", data);}).always(function(){
                ReloadGrid();
                $("#editar").modal('toggle');
            });
        }
    });

    $('#btn-importMyM').on('click', function() {


        if($("#file").val() == '' || $("#file").val() == null)
        {
            swal("Error", "Debe seleccionar un archivo excel para importar", "error");
            return;
        }

        $("#loadgif").show();
        var bar = $('.progress-bar');
        var percent = $('.percent');

        var formData = new FormData();
        formData.append("clave", "valor");
        console.log("formData = ", formData);
        $.ajax({
            // Your server script to process the upload
            url: '/maximosyminimos/importar',
            type: 'POST',

            // Form data
            data: new FormData($('#form-import')[0]),

            // Tell jQuery not to process data or worry about content-type
            // You *must* include these options!
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $('.progress').show();
                var percentVal = '0%';
                bar.width(percentVal);
                percent.html(percentVal);
            },
            // Custom XMLHttpRequest
            xhr: function() {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    // For handling the progress of the upload
                    myXhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            var percentComplete = e.loaded / e.total;
                            percentComplete = parseInt(percentComplete * 100);
                            bar.css("width", percentComplete + "%");
                            percent.html(percentComplete+'%');
                            if (percentComplete === 100) {
                                setTimeout(function(){$('.progress').hide();}, 2000);
                            }
                        }
                    } , false);
                }
                return myXhr;
            },
            success: function(data) {
                console.log("SUCESS", data);
                setTimeout(
                    function(){if (data.status == 200) {
                        swal(
                            {
                                html: true,
                                title: "Éxito", 
                                text: data.statusText, 
                                type: "success"
                            });

                        $('#modalImportMyM').modal('hide');
                        ReloadGrid();
                        $("#loadgif").hide();
                    }
                    else {
                        swal("Error", data.statusText, "error");
                    }
                },1000);
            }, error: function(data){
                console.log("ERROR", data);
            }
        });
    });


</script>