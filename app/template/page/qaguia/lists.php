<?php
$almacenes = new \AlmacenP\AlmacenP();
$model_almacen = $almacenes->getAll();

$listaProto = new \Protocolos\Protocolos();
$listaProvee = new \Proveedores\Proveedores();
$listaAlma = new \Almacen\Almacen();
$listaArtic = new \Articulos\Articulos();


$mod=60;
$var1=183;
$var2=184;
$var3=185;
$var4=186;

$vere = \db()->prepare("select * from t_profiles as a where id_menu='".$mod."' and id_submenu='".$var1."' and id_role='".$_SESSION["perfil_usuario"]."'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);


$agre = \db()->prepare("select * from t_profiles as a where id_menu='".$mod."' and id_submenu='".$var2."' and id_role='".$_SESSION["perfil_usuario"]."'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);


$edita = \db()->prepare("select * from t_profiles as a where id_menu='".$mod."' and id_submenu='".$var3."' and id_role='".$_SESSION["perfil_usuario"]."'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);


$borra = \db()->prepare("select * from t_profiles as a where id_menu='".$mod."' and id_submenu='".$var4."' and id_role='".$_SESSION["perfil_usuario"]."'");
$borra->execute();
$borrar = $borra->fetchAll(PDO::FETCH_ASSOC);


?>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css" rel="stylesheet">

<style>
    .tab .ui-jqgrid .ui-jqgrid-htable {
        width: 100% !important;
    }
    .tab .ui-jqgrid .ui-jqgrid-btable {
        table-layout: inherit;
        margin: 0;
        outline-style: none;
        width: auto !important;
    }
    .tab .ui-state-default ui-jqgrid-hdiv {
        width: 100% !important;
    }
    .tab .ui-jqgrid-view {
        width: 100% !important;
    }
    .tab .ui-state-default ui-jqgrid-hdiv {
        width: 100% !important;
    }
    .tab .ui-jqgrid .ui-jqgrid-bdiv {
        width: 100% !important;
        height: 118px !important;
        height: 10px;
    }
    #detalle_wrapper .ui-jqgrid-hdiv.ui-state-default.ui-corner-top, #detalle_wrapper #gview_grid-table2, #detalle_wrapper .ui-jqgrid.ui-widget.ui-widget-content.ui-corner-all, #detalle_wrapper .ui-jqgrid-pager.ui-state-default.ui-corner-bottom, #detalle_wrapper #grid-table2, #detalle_wrapper .ui-jqgrid-bdiv{
        max-width: 100% !important;
        width: 100% !important;
    }
    .tab .ui-state-default, .ui-widget-content, .ui-widget-header .ui-state-default  {
        width: 100% !important;
    }
    .tab .ui-widget-content {
        width: 100% !important;
    }
    #FORM {
        width: 100%;
        /*height: 100%;*/
        position: absolute;
        left: 0px;
        z-index: 999;
    }
    .inmodal .modal-body {
        height: 510px;
    }
    .ibox-content .tab {
        clear: both;
        height: 30px;
    }
    .ibox-content .tab input {
        clear: both;
        margin-left: -20px;
    }

    /* Sortable items */
    .sortable-list {
        background-color: #F5F5F5;
        list-style: none;
        margin: 0;
        /*min-height: 30px; */
        padding: 1px;
        overflow:auto;
        border: 1px solid #ccc;
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        border-radius: 5px;
        min-height: 200px;
        height: 200px;
    }
    .sortable-list2 {
        background-color: #F5F5F5;
        list-style: none;
        margin: 0;
        min-height: 30px;
        padding: 1px;
        height: 140px;
        overflow:auto;
    }
    .sortable-item {
        background-color: #FFF;
        border: 0px solid #000;
        cursor: pointer;
        display: block;
        font-weight: bold;
        margin-bottom: 1px;
        padding: 5px 0;
    }
    .sortable-item-over {
        background-color: #b2e1ff;
        border: 0px solid #000;
        cursor: pointer;
        display: block;
        font-weight: bold;
        margin-bottom: 1px;
        padding: 5px 0;
    }
    .activeItem{
        background-color: #7fceff;
        cursor: move;
        display: block;
        font-weight: bold;
        margin-bottom: 1px;
        padding: 5px 0;
        /*border-radius:10px 10px 10px 10px;
        -moz-border-radius:10px 10px 10px 10px;
        -webkit-border-radius: 10px 10px 10px 10px;
        -ms-border-radius-topleft: 10px;
        -ms-border-radius-topright: 10px;
        -ms-border-radius-bottomleft: 10px;
        -ms-border-radius-bottomright: 10px;
        text-align: center;*/
    }

    .art-content{

        min-width: 880px;

    }

    .art-wrapper{

        width: 100% !important;

    }



    #gview_grid-tablea2 > div > .ui-jqgrid-hbox{
        padding-right: 0px !important; 
    }

    #grid-tablea2{

        width: 100% !important;

    }

    #grid-tablea2_subgrid{

        width: 5% !important;
        text-align: center;

    }

    #grid-tablea2_cve_articulo{
        width: 25% !important;
    }

    #grid-tablea2_des_articulo{

        width: 25% !important;

    }

    #grid-tablea2_suma{

        width: 25% !important;

    }

    #grid-tablea2_acciones{

        width: 20% !important;


    }



    #grid-tablea2 > tbody > tr > td:nth-child(1){

        width: 5% !important;
        text-align: center;

    }

    #grid-tablea2 > tbody > tr > td:nth-child(2){

        width: 25% !important;
        text-align: center;

    }

    #grid-tablea2 > tbody > tr > td:nth-child(3){

        width: 25% !important;
        text-align: center;

    }

    #grid-tablea2 > tbody > tr > td:nth-child(4){

        width: 25% !important;
        text-align: center;

    }

    #grid-tablea2 > tbody > tr > td:nth-child(5){

        width: 20% !important;
        text-align: center;

    }

    #grid-tablea_cve_ubicacion{

        display: none;

    }

    #grid-tablea2_jqg1_t_cve_ubicacion{

        display: none;

    }


    td[aria-describedby="grid-tablea_cve_ubicacion"]{

        display: none;

    }

    td[aria-describedby="grid-tablea2_jqg1_t_cve_ubicacion"]{

        display: none;

    }

    td[aria-describedby="grid-tablea2_cve_ubicacion"]{

        display: none;

    }




    td[aria-describedby="grid-tablea2_jqg1_t_cb"]{

        width: 8% !important;

    }

    #grid-tablea2_jqg1_t_cb{

        width: 8% !important;

    }

    td[aria-describedby="grid-tablea2_jqg1_t_ubicacion"]{

        width: 23% !important;

    }

    #grid-tablea2_jqg1_t_ubicacion{

        width: 23% !important;

    }

    #grid-table2_ID{

        width: 20% !important

    }

    #grid-table2_tipo{

        width: 20% !important

    }


    #grid-table2_guia{

        width: 20% !important

    }

    #grid-table2_abierta{

        width: 20% !important

    }

    #grid-table2_peso{

        width: 20% !important

    }


    td[aria-describedby="grid-table2_ID"]{

        width: 20% !important;

    }

    table[aria-describedby="gbox_grid-table2"]{

        width: 100% !important;

    }
    
    .ui-jqgrid-hbox{
        
        padding-right: 0px !important;
        
    }



    td[aria-describedby="grid-table2_tipo"]{

        width: 20% !important;

    }




    td[aria-describedby="grid-table2_guia"]{

        width: 20% !important;

    }





    td[aria-describedby="grid-table2_abierta"]{

        width: 20% !important;

    }




    td[aria-describedby="grid-table2_peso]{

    width: 20% !important;

    }



    td[aria-describedby="grid-tablea_des_articulo"]{

        width: 22.3% !important;

    }

    td[aria-describedby="grid-tablea2_jqg1_t_cve_articulo"]{

        display: none;

    }



    td[aria-describedby="grid-tablea_suma"]{

        width: 22.3% !important;

    }


    td[aria-describedby="grid-tablea_total"]{

        width: 22.3% !important;

    }



    #grid-tablea2_cve_ubicacion{

        display: none;

    }

    #grid-tablea_cve_articulo{

        width: 22% !important;

    }

    #grid-tablea_des_articulo{

        width: 22% !important;

    }

    #grid-tablea_suma{

        width: 22% !important;

    }

    #grid-tablea_total{

        width: 22% !important;

    }

    #grid-tablea_cb{

        width: 12% !important;

    }


    #grid-tablea{

        table-layout: inherit !important;

    }

    #grid-tablea2_jqg1_t_cve_articulo{

        display: none;

    }

</style>

<div class="wrapper wrapper-content  animated " id="list">

    <h3>Guias</h3>

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">

                        <div class="col-lg-4">
                            <div class="input-group">
                                <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                <div class="input-group-btn">
                                    <a href="#" onclick="ReloadGrid2()">
                                        <button type="submit" class="btn btn-sm btn-primary" id="buscarA">
                                            Buscar
                                        </button>
                                    </a>
                                </div>
                            </div>
                            <!--   <input class="form-control" type="text" name="daterange" value="<?php echo date("d/m/Y"); ?> - <?php echo date("d/m/Y"); ?>" />-->
                        </div>

                        <div class="col-lg-8">
                            <div class="pull-right">

                                <style>

                                    <?php if($edit[0]['Activo']==0){?>

                                    .fa-edit{

                                        display: none;

                                    }

                                    <?php }?>


                                    <?php if($borrar[0]['Activo']==0){?>

                                    .fa-eraser{

                                        display: none;

                                    }

                                    <?php }?>

                                </style>


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
<div class="modal fade" id="coModal" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 1200px!important;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Guias</h4>
                </div>
                <div class="modal-body">
                    <div class="ibox-content">
                        <div class="jqGrid_wrapper" id="detalle_wrapper">
                            <table id="grid-table2"></table>
                            <div id="grid-pager2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mainly scripts -->
<!--<script src="/js/dropdownLists.js"></script>-->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="https://momentjs.com/downloads/moment.js"></script>
<script src="https://momentjs.com/downloads/moment-with-locales.js"></script>


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


<script src="/js/plugins/chosen/chosen.jquery.js"></script>

<!-- Select -->
<script src="/js/select2.js"></script>
<!-- Data picker -->
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script src="/js/plugins/clockpicker/clockpicker.js"></script>
<!-- iCheck -->
<script src="/js/plugins/iCheck/icheck.min.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>


<script type="text/javascript">

    //////////////////////////////////////////////////////////Aqui se contruye el Grid//////////////////////////////////////////////////////////
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
            url:'/api/qaguia/lista/index.php',
            datatype: "json",
            shrinkToFit: false,
            height:'auto',
            postData: {
                criterio: $("#txtCriterio").val(),
                _fecha: $("#_fecha").val(),
                _fechaFin: $("#_fechaFin").val()
            },
            mtype: 'POST',
            colNames:["Detalle",'Pedido','Folio' ,'Usuario', 'Fecha Pedido','Fecha Entrega','Destinatario'],
            colModel:[
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat, frozen : true},
                {name:'pedido',index:'pedido',width:120, editable:false, sortable:false, resizable: false},
                {name:'folio',index:'folio',width:120, editable:false, sortable:false, resizable: false},
                {name:'usuario',index:'usuario',width:180, editable:false, sortable:false, resizable: false},
                {name:'fechaIni',index:'fecha_p',width:180, editable:false, sortable:false, resizable: false},
                {name:'fechaFin',index:'fecha_e',width:180, editable:false, sortable:false, resizable: false},
                {name:'destinatario',index:'destinatario',width:150, editable:false, sortable:false, resizable: false},
                
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'pedido',
            viewrecords: true,
            sortorder: "desc"
        });

        // Setup buttons
        $("#grid-table").jqGrid('navGrid', '#grid-pager',
                                {edit: false, add: false, del: false, search: false},
                                {height: 200, reloadAfterSubmit: true}
                               );

        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////


        function imageFormat( cellvalue, options, rowObject ){
            var serie = rowObject[2];
            var html = '';


            html += '<a href="#" onclick="detalle(\''+serie+'\')"  title="Asignar Usuario y Contar"><i class="fa fa-check"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';


            return html;

        }

        function reloadPage() {
            var grid = $(grid_selector);
            $.ajax({
                url: "index.php",
                dataType: "json",
                success: function(data){
                    grid.trigger("reloadGrid",[{current:true}]);
                },
                error: function(){}
            });
        }

        //it causes some flicker when reloading or navigating grid
        //it may be possible to have some custom formatter to do this as the grid is being created to prevent this
        //or go back to default browser checkbox styles for the grid
        function styleCheckbox(table) {
        }

        //unlike navButtons icons, action icons in rows seem to be hard-coded
        //you can change them like this in here if you want
        function updateActionIcons(table) {
        }

        //replace icons with FontAwesome icons like above

        function updatePagerIcons(table) {
        }

        function enableTooltips(table) {
            $('.navtable .ui-pg-button').tooltip({container:'body'});
            $(table).find('.ui-pg-div').tooltip({container:'body'});
        }

        //var selr = jQuery(grid_selector).jqGrid('getGridParam','selrow');

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });


    //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////
    function ReloadGrid() {
        $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                criterio: $("#txtCriterio").val(),
                _fecha: $("#_fecha").val(),
                _fechaFin: $("#_fechaFin").val()
            }, datatype: 'json', page : 1})
            .trigger('reloadGrid',[{current:true}]);
    }

    function downloadxml( url ) {
        var win = window.open(url, '_blank');
        win.focus();
    }



    $modal0 = null;

    function cancelar() {
        $("#fromU .itemlist").remove();
        $("#toU .itemlist").remove();
        $(':input','#myform')
            .removeAttr('checked')
            .removeAttr('selected')
            .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
            .val('');
        $('#DetalleInventario').removeAttr('class').attr('class', '');
        $('#DetalleInventario').addClass('animated');
        $('#DetalleInventario').addClass("fadeOutRight");
        $('#DetalleInventario').hide();
        $('#almacen').val("");
        $('#txtCriterio2').val("");
        //$('#FechaUnSoloDia').val(new Date());



        $('#list').show();
        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeInRight");
        $('#list').addClass("wrapper");
        $('#list').addClass("wrapper-content");
        $('a[href="#tab-1"]').tab('show');
    }



    function buildRequestStringData(form) {
        var select = form.find('select'),
            input = form.find('input'),
            requestString = '{';
        for (var i = 0; i < select.length; i++) {
            requestString += '"' + $(select[i]).attr('name') + '": "' +$(select[i]).val() + '",';
        }
        if (select.length > 0) {
            requestString = requestString.substring(0, requestString.length - 1);
        }
        for (var i = 0; i < input.length; i++) {
            if ($(input[i]).attr('type') !== 'checkbox') {
                requestString += '"' + $(input[i]).attr('name') + '":"' + $(input[i]).val() + '",';
            } else {
                if ($(input[i]).attr('checked')) {
                    requestString += '"' + $(input[i]).attr('name') +'":"' + $(input[i]).val() +'",';
                }
            }
        }
        if (input.length > 0) {
            requestString = requestString.substring(0, requestString.length - 1);
        }
        requestString += '}';
        return requestString;
    }

    var l = $( '.ladda-button' ).ladda();

    function buscarEnLista(){    


        $("#fromU .itemlist").remove();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                almacen : $("#almacen").val(),
                parameter : $("#txtCriterio2").val(),
                action : "buscarArticulos"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/almacen/update/index.php',
            success: function(data) {

                if (data.success == true) {


                    var arr = $.map(data.articulos, function(el) { return el; })
                    arr.pop();
                    for (var i=0; i<data.articulos.length; i++)
                    {
                        var ul = document.getElementById("fromU");
                        var li = document.createElement("li");
                        var checkbox = document.createElement("input");
                        checkbox.style.marginRight = "10px";
                        checkbox.setAttribute("type", "checkbox");
                        checkbox.setAttribute("value", data.articulos[i].cve_articulo);
                        checkbox.setAttribute("class", "drag");
                        checkbox.setAttribute("onclick", "selectParent(this)");
                        li.appendChild(checkbox);
                        li.appendChild(document.createTextNode(data.articulos[i].cve_articulo + " - " + data.articulos[i].des_articulo + " ("+data.articulos[i].Suma+" piezas disp.)"));
                        li.setAttribute("dayta-draggable", "item");
                        li.setAttribute("draggable", "false");
                        li.setAttribute("aria-draggable", "false");
                        li.setAttribute("aria-grabbed","false");
                        li.setAttribute("tabindex","0");
                        li.setAttribute("class","itemlist");
                        li.setAttribute("onclick","selectChild(this)");
                        li.setAttribute("value",data.articulos[i].cve_articulo);
                        //  li.setAttribute("area", data.articulos[i].area === "true")
                        ul.appendChild(li);
                    }
                }
            }
        })
    }

    function almacen2(){	
        $('#grid-tablea').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                almacen: $("#almacenes").val(),
                action : "traerArticulosDeAlmacenExist2"
            }, datatype: 'json', page : 1})
            .trigger('reloadGrid',[{current:true}]);

    }



    $( ".borr" ).click(function() {
        $(this).parent('td').parent('tr').remove();
    });

    function borr(valor){
        //alert(valor);
        $('#jqg'+valor).remove();
    };



    function selectParent(e){
        if(e.checked){
            e.parentNode.setAttribute("aria-grabbed", "true");
        }else{
            e.parentNode.setAttribute("aria-grabbed", "false");
        }
    }
    function selectChild(e){
        if(e.getAttribute("aria-grabbed") == "true"){
            e.firstChild.checked = true;
        }else{
            e.firstChild.checked = false;
        }
    }

    $("#txtCriterio").keyup(function(event){
        if(event.keyCode == 13){
            $("#buscarA").click();
        }
    });

    $("#txtCriterio2").keyup(function(event){
        if(event.keyCode == 13){
            $("#buscar2").click();
        }
    });
</script>
<script type="text/javascript">
    function detalle(_codigo) {
        loadArticleDetails(_codigo);
        $modal0 = $("#coModal");
        $modal0.modal('show');
    }

    var loadArticleDetails;
    (function(){
        loadArticleDetails = function(codigo){
            $.jgrid.gridUnload("#grid-table2");
            var grid_selector = "#grid-table2";
            var pager_selector = "#grid-pager2";

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
                url:'/api/qaguia/update/index.php',
                datatype: "json",
                postData: {
                    ID_PLAN: codigo,
                    action : "loadDetalle"
                },
                shrinkToFit: false,
                height:'auto',
                mtype: 'POST',
                colNames:['ID', 'Tipo de Caja','Guia','Abierta','Peso'],
                colModel:[
                    {name:'ID',index:'Cve_CajaMix', width:180, sorttype:"int", editable: false},
                    {name:'tipo',index:'TipoCaja',width:180, editable:false, sortable:false},
                    {name:'guia',index:'Guia',width:180, editrules:{integer: true}, editable:true, edittype:'text', sortable:false},
                    {name:'abierta',index:'abierta',width:180, editable:false, sortable:false},
                    {name:'peso',index:'peso',width:180, editable:false, sortable:false}
                ],
                rowNum:30,
                rowList:[30,40,50],
                pager: pager_selector,
                viewrecords: true,
                editurl: '/api/qaguia/update/index.php', 
                onSelectRow: function (id) {
                    var ID_PLAN = id;
                    console.log(id);
                    jQuery('#grid-table2').jqGrid(
                        'editRow',
                        id, {
                            keys: true, 
                            oneditfunc: function(){
                                console.error("editando");
                            },
                            successfunc: function(){
                                $('#grid-table2').jqGrid('clearGridData')
                                    .jqGrid('setGridParam', {postData: {
                                        action: 'loadDetalle',
                                        ID_PLAN: ID_PLAN
                                    }, datatype: 'json', page : 1})
                                    .trigger('reloadGrid',[{current:true}]);
                            }
                        }
                    );
                },
            });

            // Setup buttons
            $("#grid-table2").jqGrid('navGrid', '#grid-pager2',
                                     {edit: false, add: false, del: false, search: false},
                                     {height: 200, reloadAfterSubmit: true}
                                    );


            $(window).triggerHandler('resize.jqGrid');
        }


    })();
</script>