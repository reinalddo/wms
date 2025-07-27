<?php include $_SERVER['DOCUMENT_ROOT'] . '/Framework/autoload.php'; ?>
<?php 
include $_SERVER['DOCUMENT_ROOT']."/app/host.php";
include $_SERVER['DOCUMENT_ROOT']."/Application/Controllers/AlmacenPController.php";
    //use Application\Controllers\QACuarentenaController as QACuarentena;
    //use Application\Controllers\AlmacenPController as AlmacenP;

    //$qac = new QACuarentena();
    $almacen = new \Application\Controllers\AlmacenPController();
    $almacenesList = $almacen->activos();
    
    $contenedorAlmacen = new \AlmacenP\AlmacenP();
    $almacenes = $contenedorAlmacen->getAll();
    

    
?>
<?php include __DIR__.'/../includes/datatable.link.php'; ?>

<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/blueimp/css/blueimp-gallery.min.css" rel="stylesheet">
<link href="/css/plugins/imgloader/fileinput.min.css" rel="stylesheet">

<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>


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
<script src="/js/plugins/chosen/chosen.jquery.js"></script>

<div class="wrapper wrapper-content  animated " id="list">
 <h3>Productos en cuarentena</h3>
    <div class="row">
        <div class="col-md-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="email">Almacén</label>

                            <select name="almacen" id="almacen" class="chosen-select form-control">
                                <option value="">Seleccione</option>
                                    <?php if(isset($almacenes) && !empty($almacenes)): ?>
                                        <?php foreach($almacenes as $almacen): ?>
                                            <option value="<?php echo $almacen->clave ?>"><?php echo $almacen->nombre ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                            </select>

                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="zona">Zona de Almacenaje</label>
                            <select name="zona" id="zona" class="chosen-select form-control">
                                <option value="">Seleccione Zona</option>
                            </select>
                        </div>
                    </div>
                           <div class="col-md-3">
                        <div class="form-group">
                            <label for="email">Articulo</label>
                            <select name="articulo" id="articulo" class="chosen-select form-control">
                            <option value="">Seleccione Articulo</option>
                            </select>
                        </div>
                        </div>
                        <div class="col-md-3">
                            <label for="email">&#160;&#160;</label>
                            <div class="form-group text-right">
                                <button onclick="ReloadGrid()" class="btn btn-primary">Buscar</button>
                                <a href="#" onclick="agregar()">
                                    <button class="btn btn-primary" type="button">
                                        <i class="fa fa-plus"></i> Agregar productos
                                    </button>
                                </a>
                            </div>
                            
                        </div>  
                    </div>
                    </div>
                    
                </div>
                    
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-sm-12 text-right" style="margin-top:5px">
                            <button id="sacarProductosCuarentena" class="btn btn-primary">
                                <i class="fa fa-sign-out"></i>
                                Sacar de cuarentena
                            </button>
                            <button id="exportExcel" class="btn btn-primary">
                                <i class="fa fa-file-excel-o"></i>
                                Excel
                            </button>
                            <button id="exportPDF" class="btn btn-danger">
                                <i class="fa fa-file-pdf-o"></i>
                                PDF
                            </button>
                        </div>
                    
                        <br/>
                        
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
</div>

<div class="wrapper wrapper-content  animated fadeInRight" id="FORM" style="display: none">

<div class="row">
    <div class="col-md-12">
        <div class="ibox ">
            <div class="ibox-title">
                <div class="row">
                    <div class="col-md-4" id="_title">
                        <h3>Agregar artículos a cuarentena</h3>
                    </div>
                </div>
            </div>
   
            <div class="ibox-content">
                <div class="row">

                    <div class="col-md-8 col-md-8 b-r">
                        <label>Busqueda</label>
                        <div class="input-group">                                    
                            <input id="txtBuscar" type="text" placeholder="Buscar..." class="form-control" value="" maxlength="20" required="true">
                            <div class="input-group-btn">
                                <a href="#" onclick="ReloadGridBusqueda()" class="btn btn-sm btn-primary">
                                    Buscar
                                </a>
                            </div>

                        </div>                              
                    </div>

                    <div class="col-md-4 col-md-4">
                        <div class="form-group">
                            <label>Filtros</label>
                            <select class="chosen-select form-control" id="cmbFiltro" name="filtro">
                                <!--<option value="">Seleccione un filtro</option>-->
                                <option value="1" selected>Producto</option>
                                <option value="2">Almacén</option>    
                                <option value="3">Zona de almacenaje</option>                                    
                                <option value="4">Lote</option>                                    
                                <option value="5">Todos</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12 col-md-12 text-right" style="margin-bottom:15px" >
                        <a href="#" onclick="agregarACuarentena()" class="btn btn-sm btn-primary">
                            Agregar a cuarentena
                        </a>
                        <a href="#" onclick="cancelar()" class="btn btn-sm btn-danger">
                            Cancelar
                        </a>
                    </div>

                    <div class="ibox-content">                            
                        <div class="jqGrid_wrapper">
                            <table id="grid-table-busqueda"></table>
                            <div id="grid-pager-busqueda"></div>
                        </div>
                    </div>

                    <div class="col-md-12 col-md-12 text-right" style="margin-bottom:15px" >
                        <a href="#" onclick="agregarACuarentena()" class="btn btn-sm btn-primary">
                            Agregar a cuarentena
                        </a>
                        <a href="#" onclick="cancelar()" class="btn btn-sm btn-danger">
                            Cancelar
                        </a>
                    </div>


                </div>

            </div>
         
        </div>
    </div>
</div>
</div>

<script>


    $("#expor4tExcel").on("click", function(){        
        //if($("#almacen").val() === '') return false;

        var options = {
            almacen: $("#almacen").val(),
            zona: $("#zona").val(),
            articulo: $("#articulo").val()
        };

        var form = document.createElement("form"),
            input1 = document.createElement("input"),
            input2 = document.createElement("input"),
            input3 = document.createElement("input"),
            input4 = document.createElement("input");

            input1.setAttribute('name', 'action');
            input1.setAttribute('value', 'reporteExcel');

            input2.setAttribute('name', 'almacen');
            input2.setAttribute('value', options.almacen);

            input3.setAttribute('name', 'zona');
            input3.setAttribute('value', options.zona);

            input4.setAttribute('name', 'articulo');
            input4.setAttribute('value', options.articulo);

            form.setAttribute('action', '/api/QaCuarentena/index.php');
            form.setAttribute('method', 'post');
            form.setAttribute('target', '_blank');
            form.appendChild(input1);
            form.appendChild(input2);
            form.appendChild(input3);
            form.appendChild(input4);
            document.body.appendChild(form);
            form.submit()
    });


    $("#exportPDF").on("click", function(){
        $("#grid-table").jqGrid("exportToPdf",{
            orientation: 'landscape',
            pageSize: 'A4',
            description: '',
            customSettings: null,
            download: 'download',
            includeLabels : true,
            includeGroupHeader : true,
            includeFooter: true,
            fileName : "Reporte de cuarentena.pdf",
        })
    })


    function agregarACuarentena(){
        var items = [];
        var selected = $("#grid-table-busqueda").jqGrid('getGridParam','selarrrow');
        itemEnCuarentena = [];

        $.each( selected, function( index, value){
            row = $("#grid-table-busqueda").jqGrid('getRowData',value);
            itemEnCuarentena.push({
                id : row.id,
                clave_producto : row.clave_producto,
                ubicacion_id : row.ubicacion_id,
                lote : row.lote,
                tipo : row.tipo
             });
        });


        $.ajax({
            url: '/api/QaCuarentena/index.php',
            dataType: "json",
            type: "POST",
            data : {
                action : 'agregarACuarentena',
                items : itemEnCuarentena
            },
            success: function(data){
                cancelar();
                ReloadGrid();
            }
        });

    }


    function sacarDeCuarentena(){
        swal({
            title: "¿Confirma que desea sacar estos articulos de cuarentena?",
            text: "Está a punto de sacar de cuarentena unos articulos",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Borrar",
            cancelButtonText: "Cancelar",
            closeOnConfirm: true
        },
        function(){
            var items = [];
            var selected = $("#grid-table").jqGrid('getGridParam','selarrrow');
            itemEnCuarentena = [];

            $.each( selected, function( index, value){
                row = $("#grid-table").jqGrid('getRowData',value);
                itemEnCuarentena.push({
                    id : row.id,
                    tipo : row.tipo
                });
            });

            $.ajax({
                url: '/api/QaCuarentena/index.php',
                dataType: "json",
                type: "POST",
                data : {
                    action : 'sacarDeCuarentena',
                    items : itemEnCuarentena
                },
                success: function(data){
                    ReloadGrid();
                }
            });

        }); 

    }

    $('#sacarProductosCuarentena').click(sacarDeCuarentena);
    
    function sacarUno( id, tipo ){
 
        swal({
            title: "¿Confirma que desea sacar este articulo de cuarentena?",
            text: "Está a punto de sacar de cuarentena unos articulos",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Borrar",
            cancelButtonText: "Cancelar",
            closeOnConfirm: true
        },
        function(){
            $.ajax({
                url: '/api/QaCuarentena/index.php',
                dataType: "json",
                type: "POST",
                data : {
                    action : 'sacarUno',
                    tipo : tipo,
                    id : id,
                },
                success: function(data){
                    ReloadGrid();
                }
            });
        });
    }


    function agregar() {
        $("#_title").html('<h3>Agregar artículos a cuarentena</h3>');
        $(':input','#myform')
            .removeAttr('checked')
            .removeAttr('selected')
            .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
            .val('');
        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeOutRight");
        $('#list').hide();

        $('#FORM').show();
        $('#FORM').removeAttr('class').attr('class', '');
        $('#FORM').addClass('animated');
        $('#FORM').addClass("fadeInRight");
        $("#hiddenAction").val("add");
        $("#hiddenUbicacion").val("0");
        $('#checkbox2').iCheck('disable');
        $("#checkbox1").click(function() {
            $("#checkbox2").iCheck('enable');
        });
    }


    $(document).ready(function(){
        $(function() {
            $('.chosen-select').chosen();
            $('.chosen-select-deselect').chosen({ allow_single_deselect: true });
        });
    });


    function ReloadGrid() {
        $('#grid-table')
            .jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    almacen : $("#almacen").val(),
                    articulo : $("#articulo").val(),
                    zona : $("#zona").val(),
                }, datatype: 'json',
                page : 1
            })
            .trigger('reloadGrid',[{current:true}]);
    }

    function ReloadGridBusqueda() {
        $('#grid-table-busqueda')
            .jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    criterio: $("#txtBuscar").val(),
                    filtro: $("#cmbFiltro").val()
                }, 
                datatype: 'json',
                page : 1
            })
            .trigger('reloadGrid',[{current:true}]);
    }



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
            url:'/api/QaCuarentena/index.php',
            datatype: "json",
            height: 250,
            shrinkToFit: false,
            postData: {
                action : 'all',
                almacen : $("#almacen").val(),
                articulo : $("#producto").val(),
                zona : $("#zona").val(),
            },
            mtype: 'GET',
            colNames:['id','Almacén','Zona','Pasillo','Rack', 'Nivel', 'Sección', 'Ubicación', 'Clave', 
                    'Descripción', 'Lote', 'Caducidad', 'N° de serie','Cantidad', 'tipo',
                    'Entrada','','Responsable',
                    'Salida','','Responsable',''],
            colModel:[
                {name:'id',index:'id',width:0, editable:false, sortable:false, hidden:true},
                {name:'nombre_almacen',index:'nombre_almacen',width:120, editable:false, sortable:false},
                {name:'zona_id',index:'zona_id',width:100, editable:false, sortable:false, resizable: false},
                {name:'pasillo',index:'pasillo',width:100, editable:false, sortable:false},
                {name:'rack',index:'rack',width:150, editable:false, sortable:false, resizable: false},
                {name:'nivel',index:'nivel',width:150, editable:false, sortable:false, resizable: false},
                {name:'seccion',index:'seccion',width:150, editable:false, sortable:false, resizable: false},
                {name:'ubicacion',index:'ubicacion',width:150, editable:false, sortable:false, resizable: false},
                {name:'clave_producto',index:'clave_producto',width:150, editable:false, sortable:false, resizable: false},
                {name:'nombre_producto',index:'nombre_producto',width:150, editable:false, sortable:false, resizable: false},
                {name:'lote',index:'lote',width:150, editable:false, sortable:false, resizable: false},
                {name:'caducidad',index:'caducidad',width:150, editable:false, sortable:false, resizable: false},
                {name:'nserie',index:'nserie',width:150, editable:false, sortable:false, resizable: false},
                {name:'existencia',index:'cantidad',width:150, editable:false, sortable:false, resizable: false, align:"right"},
                {name:'tipo',index:'tipo',width:150, editable:false, sortable:false, resizable: false,hidden:true},

                {name:'cuarentena_ini',index:'cuarentena_ini',width:150, editable:false, sortable:false, resizable: false},
                {name:'cuarentena_ini_user',index:'cuarentena_ini_user',width:150, editable:false, sortable:false, resizable: false, hidden:true},
                {name:'cuarentena_ini_user_desc',index:'cuarentena_ini_user_desc',width:150, editable:false, sortable:false, resizable: false},
                {name:'cuarentena_fin',index:'cuarentena_fin',width:150, editable:false, sortable:false, resizable: false},
                {name:'cuarentena_fin_user',index:'cuarentena_fin_user',width:150, editable:false, sortable:false, resizable: false, hidden:true},
                {name:'cuarentena_fin_user_desc',index:'cuarentena_fin_user_desc',width:150, editable:false, sortable:false, resizable: false},
                {name:'myac',index:'', width:40, fixed:true, sortable:false, resize:false, formatter:imageFormatView, frozen : true},
            ],
            multiselect: true,
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'cuarentena_ini',
            viewrecords: true,
            sortorder: "desc"
        });

        // Setup buttons
        $("#grid-table").jqGrid('navGrid', '#grid-pager',
                                {edit: false, add: false, del: false, search: false},
                                {height: 200, reloadAfterSubmit: true}
                               );

        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
       

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });

    });


  
    $(function($) { 
       //////////////////////////////////////////////////////////Aqui se contruye el Grid//////////////////////////////////////////////////////////

        var grid_selector_busqueda = "#grid-table-busqueda";
        var pager_selector_busqueda = "#grid-pager-busqueda";

        //resize to fit page size
        $(window).on('resize.jqGrid', function () {
            $(grid_selector_busqueda).jqGrid( 'setGridWidth', $(".page-content").width() - 50 );
        });
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector_busqueda).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) {
            if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $(grid_selector_busqueda).jqGrid( 'setGridWidth', parent_column.width() );
                }, 0);
            }
        })

        $(grid_selector_busqueda).jqGrid({
            url:'/api/QaCuarentena/index.php',
            datatype: "json",
            height: 250,
            postData: {
                action : 'findProductos',
                criterio: $("#txtBuscar").val(),
                filtro: $("#cmbFiltro").val()
            },
            mtype: 'GET',
            colNames:['id','ubicacion','Clave del producto','Nombre','clave_almacen','Almacén','zona_id','Zona','Lote','Cantidad','Tipo_id','Tipo'],
            colModel:[
                {name:'id',index:'id',width:0, editable:false, sortable:false, hidden:true},
                {name:'ubicacion_id',index:'ubicacion_id',width:0, editable:false, sortable:false, hidden:true},
                {name:'clave_producto',index:'clave_producto',width:120, editable:false, sortable:false},
                {name:'nombre_producto',index:'nombre_producto',width:250, editable:false, sortable:false, resizable: false},
                {name:'clave_almacen',index:'clave_almacen',width:0, editable:false, sortable:false, hidden:true},
                {name:'almacen',index:'almacen',width:80, editable:false, sortable:false},               
                {name:'zona_id',index:'zona_id',width:70, editable:false, sortable:false, resizable: false, hidden:true},
                {name:'zona',index:'zona',width:70, editable:false, sortable:false, resizable: false},   
                {name:'lote',index:'lote',width:70, editable:false, sortable:false, resizable: false},               
                {name:'existencia',index:'existencia',width:70, editable:false, sortable:false, align:"right", resizable: false},
                {name:'tipo',index:'tipo',width:70, editable:false, sortable:false, resizable: false, hidden:true},
                {name:'tipo_descripcion',index:'tipo_descripcion',width:70, editable:false, sortable:false, resizable: false},
            ],
            multiselect: true,
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector_busqueda,
            sortname: 'producto',
            viewrecords: true,
            sortorder: "desc"
        });

        // Setup buttons
        $(grid_selector_busqueda).jqGrid('navGrid', '#grid-pager-busqueda',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );


        $(window).triggerHandler('resize.jqGrid');

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector_busqueda).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });




    function imageFormatView( cellvalue, options, rowObject ){
        //return '<img src="'+cellvalue+'" />&nbsp;&nbsp;&nbsp;&nbsp;<img src="'+cellvalue+'" />';
        var id = rowObject['id'];
        var tipo = rowObject['tipo'];
        var html = '<a href="#" onclick="sacarUno('+id+','+tipo+')"><i class="fa fa-sign-out" alt="Sacar"></i></a>&nbsp';
        

        //return '<a href="#" onclick="downloadxml(\''+url+'\')"><i class="ace-icon fa fa-download" alt="Download"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onclick="viewPdf(\''+url2+'\')"><i class="ace-icon fa fa-file-pdf-o" alt="Ver PDF"></i></a>';
        return html;
    }





   function cancelar() {
        $(':input','#myform')
            .removeAttr('checked')
            .removeAttr('selected')
            .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
            .val('');
        $('#FORM').removeAttr('class').attr('class', '');
        $('#FORM').addClass('animated');
        $('#FORM').addClass("fadeOutRight");
        $('#FORM').hide();

        $('#list').show();
        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeInRight");
        $('#list').addClass("wrapper");
        $('#list').addClass("wrapper-content");

        $('#grid-table-busqueda').jqGrid('clearGridData');
    }




      $('#almacen').change(function(e) {
        var almacen= $(this).val();
            $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_almac : almacen,
                action : "getArticulosYZonasAlmacenConExistencia"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ubicaciones/update/index.php',
            success: function(data) {

                    var options_articulos = $("#articulo");
                    options_articulos.empty();
                    options_articulos.append(new Option("Seleccione Artículo", ""));

                    var options_zonas = $("#zona");
                    options_zonas.empty();
                    options_zonas.append(new Option("Seleccione Zona", ""));

                    for (var i=0; i<data.articulos.length; i++)
                    {
                        options_articulos.append(new Option(data.articulos[i].id_articulo +" "+data.articulos[i].articulo, data.articulos[i].id_articulo));
                    }

                    for (var i=0; i<data.zonas.length; i++)
                    {
                        options_zonas.append(new Option(data.zonas[i].clave +" "+data.zonas[i].descripcion, data.zonas[i].clave));
                    }
                    $("#articulo").trigger("chosen:updated");
                    $("#zona").trigger("chosen:updated");

                }

        });


        });

 




</script>
