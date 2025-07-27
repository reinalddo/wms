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
<input type="hidden" id="toogle_reabasto" value="0">
<div class="wrapper wrapper-content  animated " id="list">
    <h3>Reabasto Max | Min</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-lg-3">
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
                                    <option selected value="Picking">Picking</option>
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
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <br>
                            <div class="ibox-content">
                                  <label for="btn-asignarTodo">
                                    <input type="checkbox" name="asignarTodo" id="btn-asignarTodo" style="margin-right: 6px;margin-bottom: 10px;">Seleccionar Todo
                                  </label>

                                <div class="jqGrid_wrapper">
                                    <table id="grid-tabla"></table>
                                    <div id="grid-page"></div>
                                </div>

                                <br>
                              <div class="form-group">
                                <div class="input-group-btn">
                                  <button id="btn-asignar" type="button" class="btn btn-m btn-primary permiso_registrar">Reabastecer</button>
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

<div class="modal fade" id="reabasto_modal" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Información Reabastecimiento</h4>
                </div>
                <div class="modal-body">
                    <div id="tabla_reabastecimiento"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btn-reabastecer">Reabastecer</button>
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
            url: '/api/reabasto/lista/index.php',
            mtype: "POST",
            shrinkToFit: false,
            cache: false,
            height:'auto',
            datatype: 'local',
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            //colNames:['Acción','Reabastecer','Clave', 'Descripción', 'BL', 'Maximo', 'Minimo', 'Existencia', 'Reabasto (Piezas)', 'Reabasto',  'IDY','Almacen', 'Tipo de Ubicación', 'Zona Almacenaje', 'Reabastop_Val', 'Reabastoc_Val'],
            colNames:['Acción','Reabastecer','Clave', 'Descripción', 'BL', 'Tipo de Ubicación', 'Tipo Reabasto', 'Maximo', 'Minimo', 'Existencia', 'Reabasto (Piezas)', 'Reabasto (Cajas)',  'IDY','Almacen', 'Zona Almacenaje'],
            colModel:[
                {name:'myac',index:'', width:60, sortable:false, resize:false, formatter:imageFormat, hidden: true},
                {name:'asignar',index:'asignar', width:100, sortable:false, resize:false, align:'center', formatter:imageFormat2},
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
/*
                {name:'clave',index:'clave',width:100, editable:false, sortable:false},
                {name:'descripcion',index:'descripcion',width:282, editable:false, sortable:false},
                {name:'BL',index:'BL',width:100, editable:false, sortable:false},
                {name:'maximo',index:'maximo', align: 'right', width:120, editable:false, sortable:false},
                {name:'minimo',index:'minimo', align: 'right', width:80, editable:false, sortable:false},
                {name:'existencia',index:'existencia', align: 'right', width:80, editable:false, sortable:false},
                {name:'reabastop',index:'reabastop', align: 'right', width:120, editable:false, sortable:false},
                {name:'reabasto',index:'reabasto', align: 'right', width:120, editable:false, sortable:false},
                {name:'id',index:'id',width:50, editable:false, sortable:false, hidden: true},
                {name:'almacen',index:'almacen',width:170, editable:false, sortable:false},
                {name:'tipo',index:'tipo',width:120, editable:false, sortable:false},
                {name:'zona',index:'zona',width:170, editable:false, sortable:false},
                {name:'reabastop_val',index:'reabastop_val', align: 'right', width:120, editable:false, hidden: true, sortable:false},
                {name:'reabastoc_val',index:'reabastoc_val', align: 'right', width:120, editable:false, hidden: true, sortable:false}
*/
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'cve_gpoart',
            viewrecords: true,
            sortorder: "desc",
            loadComplete: function(data){console.log("SUCCESS DATOS: ", data);}
        });

        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
        function imageFormat( cellvalue, options, rowObject ){
            var articulo = rowObject[2];
            var ubicacion = rowObject[12];
            var maximo = rowObject[7];
            var minimo = rowObject[8];
            var descripcion = rowObject[3];
            var html = '';
            if($("#permiso_editar").val() == 1)
            html = `<a href="#" onclick="editar('${articulo}', '${ubicacion}', '${maximo}', '${minimo}', '${descripcion}')"><i class="fa fa-edit" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;`;
            return html;
        }

            function imageFormat2(cellvalue, options, rowObject) 
            {
                //return '<img src="'+cellvalue+'" />&nbsp;&nbsp;&nbsp;&nbsp;<img src="'+cellvalue+'" />';
                var articulo = rowObject[2];
                var ubicacion = rowObject[12];
                var BL = rowObject[4];
                var maximo = rowObject[7];
                var minimo = rowObject[8];
                var reabastop = rowObject[11];
                var reabastoc = rowObject[10];

                //var html = '';
                //if(ruta == "--")
                //{

                  var html = '';  
                  if(reabastop != 0 || reabastoc != 0)
                  html = '<input type="checkbox" aling="center" class="checkbox-asignator" data-articulo="'+articulo+'" data-ubicacion="'+ubicacion+'" data-reabastop="'+reabastop+'" data-reabastoc="'+reabastoc+'" data-bl="'+BL+'" />';

                  //if(!dir_principal) html = "";
                //}
                //else
                //{
                 // var html = "";
                //}
                return html;//EDG
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
    function editar(articulo, ubicacion, maximo, minimo, descripcion){

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
    }

        $("#btn-asignarTodo").on("click", function(){
          $("input[type=checkbox].checkbox-asignator").each(function(i, e){
            if($("#btn-asignarTodo").prop("checked") == false)
            {
              $("input[type=checkbox].checkbox-asignator").prop("checked", true);
              //
              if($("input[type=checkbox].checkbox-asignator").prop("checked") == true){$("input[type=checkbox].checkbox-asignator").prop("checked", false);}
              else{$("input[type=checkbox].checkbox-asignator").prop("checked", true);}
            }
            else
            {
              $("input[type=checkbox].checkbox-asignator").prop("checked", false);
              //
              if($("input[type=checkbox].checkbox-asignator").prop("checked") == true){$("input[type=checkbox].checkbox-asignator").prop("checked", false);}
              else{$("input[type=checkbox].checkbox-asignator").prop("checked", true);}
            }
          });
        });


    $("#btn-reabastecer").click(function(){

        if($("#toogle_reabasto").val() == '0')
            $("#toogle_reabasto").val("1");
        $("#btn-asignar").click();

    });

    $("#btn-asignar").click(function(){

          var arr1 = [];
          var arr2 = [];
          var arr3 = [];
          var arr4 = [];
          var arr5 = [];

          $("input[type=checkbox].checkbox-asignator").each(function(i, e){
            if($(this).prop("checked") == true)
            {
              arr1.push(String($(this).data('articulo')));
              arr2.push($(this).data('ubicacion'));
              arr3.push($(this).data('reabastop'));
              arr4.push($(this).data('reabastoc'));
              arr5.push($(this).data('bl'));
            }
          });
              console.log("***********************************");
              console.log("arr_articulo->", arr1);
              console.log("arr_ubicacion->", arr2);
              console.log("arr_reabastop->", arr3);
              console.log("arr_reabastoc->", arr4);
              console.log("arr_BL->", arr5);
              console.log("***********************************");

              if(arr1.length == 0) 
              {
                swal("Error", "No hay reabastos seleccionados", "error");
                return;
              }

            //if($("#toogle_reabasto").val() == '1')
            //    $("#toogle_reabasto").val("0");

            console.log("toogle_reabasto = ", $("#toogle_reabasto").val());

              $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        id_almacen: '<?php echo $_SESSION["id_almacen"]?>',
                        cve_usuario: '<?php echo $_SESSION["cve_usuario"]?>',
                        arr_articulo: arr1,
                        arr_ubicacion: arr2,
                        arr_reabastop: arr3,
                        arr_reabastoc: arr4,
                        arr_BL: arr5,
                        realizar_reabasto: $("#toogle_reabasto").val(),
                        action: 'reabastecer'
                    },
                    beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                    },
                    url: '/api/reabasto/update/index.php',
                    success: function(data) {
                            //$("#almacen").val(data.codigo.id);
                        if (data.success == true) {
                            //document.getElementById('almacen').value = data.codigo.id;

                            console.log("SUCCESS Reabasto = ", data);

                            $("#tabla_reabastecimiento").empty();
                            $("#tabla_reabastecimiento").append(data.data_reabasto);

                            $("#reabasto_modal").modal('toggle');

                            if(data.realizar_reabasto == 1 && data.reabastos_realizados > 0)
                            {
                                swal("Éxito", "Reabasto Realizado", "success");
                                ReloadGrid();
                            }
                            else if(data.realizar_reabasto == 1 && data.reabastos_realizados == 0)
                            {
                                swal("Error", "No Hay Ubicaciones disponibles para realizar reabastos", "success");
                            }

                            if($("#toogle_reabasto").val() == '1')
                                $("#toogle_reabasto").val("0");
                            //console.log("OK almacenPrede->Codigo = ", data.codigo.id);
                            //setTimeout(function() {
                            //    ReloadGrid();
                            //}, 1000);
                        }
                    },
                    error: function(res){
                        console.log("ERROR Reabasto = ", res);
                    }
                });

    });


    $("#guardar").on('click', function(e){
        e.preventDefault();
        //var minimo = parseInt($("input[name='minimo']").val())  ;
        //var maximo = parseInt($("input[name='maximo']").val())  ;

        var minimo = parseInt($("#minimo").val());
        var maximo = parseInt($("#maximo").val());

        console.log("maximo = ", maximo, ", minimo = ", minimo);

        if(minimo > maximo){
            swal("Error", "El mínimo no puede ser mayor al máximo", "error");
        }else{
            var form = $("#guardar_mm");

        console.log("cve_articulo = ", $("#cve_articulo").val(), ", idy_ubica = ", $("#idy_ubica").val(), ", maximo = ", $("#maximo").val(), ", minimo = ", $("#minimo").val());

            $.ajax({
                url: '/api/reabasto/update/index.php',
                type: 'POST',
                //dataType: 'json',
                //data: form.serialize(),
                data: {
                cve_articulo: $("#cve_articulo").val(),
                idy_ubica: $("#idy_ubica").val(),
                minimo: $("#minimo").val(),
                maximo: $("#maximo").val(),
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
</script>