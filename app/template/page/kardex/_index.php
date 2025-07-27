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

    .blink {
  
      animation-name: blink;
      animation-duration: 4s;
      animation-timing-function: linear;
      animation-iteration-count: infinite;

      -webkit-animation-name:blink;
      -webkit-animation-duration: 4s;
      -webkit-animation-timing-function: linear;
      -webkit-animation-iteration-count: infinite;
    }

    @-moz-keyframes blink{  
      0% { opacity: 1.0; }
      50% { opacity: 0.0; }
      100% { opacity: 1.0; }
    }

    @-webkit-keyframes blink {  
      0% { opacity: 1.0; }
      50% { opacity: 0.0; }
       100% { opacity: 1.0; }
    }

    @keyframes blink {  
      0% { opacity: 1.0; }
       50% { opacity: 0.0; }
      100% { opacity: 1.0; }
    }

</style>
<div class="wrapper wrapper-content  animated " id="list">
    <h3>Kardex | Trazabilidad</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="email">Almacén</label>
                                <select name="almacen" id="select-almacen" class="chosen-select form-control">
                                    <option value="">Seleccione un Almacén</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="email">Producto</label>
                                <select name="producto" id="select-producto" class="chosen-select form-control">
                                    <option value="">Seleccione un Producto (0)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="email">Lote</label>
                                <select name="lote" id="select-lote" class="chosen-select form-control">
                                    <option value="">Seleccione un Lote (0)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Inicio</label>
                                <div class="input-group date" id="data_1">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input id="input-fechaE" type="text" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Fin</label>
                                <div class="input-group date" id="data_1">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input id="input-fechaF" type="text" class="form-control">
                                </div>
                            </div>
                        </div>
                        <!--<div class="col-lg-3">
                            <div class="form-group">
                                <label>Movimientos</label>
                                <select name="producto" id="select-movi" class="chosen-select form-control">
                                    <option value="">Seleccione un Movimiento</option>
                                </select>
                            </div>
                        </div>-->
                        <div class="col-lg-2">
                            <button id="button-buscar" style="margin-top: 25px;" class="btn btn-primary btn-sm" onclick="">Buscar</button>
                        </div>              
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="table-info"  class="table table-hover table-striped no-margin display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Clave</th>
                                    <th>Articulo</th>
                                    <th>Lote|Serie</th>
                                    <th>Caducidad</th>
                                    <th>Pallet|Contenedor</th>
                                    <th>License Plate (LP)</th>
                                    <th>Movimiento</th>
                                    <th>Descripción</th>
                                    <th>Clave Origen</th>
                                    <th>Origen</th>
                                    <th>BL</th>
                                    <th>Destino</th>
                                    <th>Cantidad</th>
                                    <th>Usuario</th>
                                    <th id="fecha_ord">Fecha</th>
<!--
Fecha
Tipo de Movimiento
Folio
Proveedor | Cliente
Clave
Cantidad
Origen
Destino
Usuario
-->
<?php 
/*
?>
                                    <th>Pasillo</th>
                                    <th>Rack</th>
                                    <th>Nivel</th>
                                    <th>Seccion</th>
                                    <th>Posicion</th>
<?php 
*/
?>
                                </tr>
                            </thead>
                            <tbody id = "tbody">
                          </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


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
<script>
/*$(document).ready(function(){
    var kardex = new Kardex(); 
});
function Kardex(){*/

    var self = this;

    var select_almacen = document.getElementById('select-almacen'),
        select_producto = document.getElementById('select-producto'),
        select_lote = document.getElementById('select-lote'),
        select_movi = document.getElementById('select-movi'),
        input_fechaE = document.getElementById('input-fechaE'),
        input_fechaF = document.getElementById('input-fechaF'),
        button_buscar = document.getElementById('button-buscar'),
        tableData = new TableData(),
        TABLE = null,
        buttons = [{
                    extend: 'excelHtml5',
                    title: 'Reporte Kardex | Trazabilidad',
                    customize: function() { swal("Descargando Excel", "Su descarga empezara en breve", "success");}
                    },
                    {
                    extend: 'pdfHtml5',
                    title: 'Reporte Kardex | Trazabilidad',
                    orientation: 'landscape',
                    download: 'open',
                    customize: function() { swal("Descargando PDF", "Su descarga empezara en breve", "success");},
                    }
                ];

    $('.chosen-select').chosen();

    $('#input-fechaE').datetimepicker({
        locale: 'es',
        format: 'YYYY-MM-DD',
        useCurrent: false
    });
    $('#input-fechaF').datetimepicker({
        locale: 'es',
        format: 'YYYY-MM-DD',
        useCurrent: false
    });

    select_almacen.onchange = function(){searchListProductos();};
    select_producto.onchange = function(){searchListLote();};
    button_buscar.onclick = function(){
        var pro = select_producto.value,
            lote = select_lote.value;
        button_buscar.disabled = true;
        button_buscar.textContent = "Cargando...";
        $(button_buscar).addClass('blink');
        searchListProductos(pro);
        searchListLote(lote, pro);
        searchListTable();
    };

    init();
    //fillListMovi();

    function init(){

        $.ajax({
            url: "/api/kardex/index.php",
            type: "POST",
            data: {
                id_user: <?php echo $_SESSION['id_user']; ?>,
                "action" : "enter-view"
            },
            beforeSend: function(x){
                if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
            },
            success: function(res){
                fillSelectAlmacen(res.almacen);
                almacenPrede();
            },
            error : function(res){
                window.console.log(res);
            }
        });
    }

    function searchListTable(){

        var data = { action : "getListTable" };

        if(select_almacen.value !== ''){
            data.idAl = select_almacen.value;
        }

        if(select_producto.value !== ''){
            data.idAr = select_producto.value;
        }

        if(select_lote.value !== ''){
            data.idLo = select_lote.value;
        }

        if(input_fechaE.value !== ''){
            data.feIn = input_fechaE.value;
        }

        if(input_fechaF.value !== ''){
            data.feEn = input_fechaF.value;
        }

        $.ajax({
            url: "/api/kardex/index.php",
            type: "POST",
            data: data,
            beforeSend: function(x){
                if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
            },
            success: function(res){
                console.log("SUCCESS = ", res);
                fillTable(res);

                var table = $('#table-info').DataTable();

                table.order( [ 10, 'desc' ] ).draw();
                //$("#table-info #fecha_ord").attr("class", 'sorting_desc');
                button_buscar.disabled = false;
                button_buscar.textContent = "Buscar";
                $(button_buscar).removeClass('blink');
            },
            error : function(res){
                console.log("ERROR = ", res);
                button_buscar.disabled = false;
                button_buscar.textContent = "Buscar";
                $(button_buscar).removeClass('blink');
            }
        });
    }

    function fillSelectAlmacen(node){

        var options = "";

        if(node){

            for(var i = 0; i < node.length; i++){
                options += "<option value = "+node[i].id+">"+htmlEntities(node[i].nombre)+"</option>";
            }
        }

        select_almacen.innerHTML += options;
        $(select_almacen).trigger("chosen:updated");
    }

    /** 
     * @author Ricardo Delgado.
     * Busca y llena el select productos.
     */
    function fillTable(node){

        tableData.destroy();

        var body = document.getElementById('tbody');

        body.innerHTML = "";

        body.innerHTML += kardex(node.entrada);

        TABLE = tableData.init("table-info",buttons, true);

        function kardex(node){

            var _body = "", origen = "", destino = "";

            for(var i = 0; i < node.length ; i++){

                _body += '<tr>'+
                            '<td>'+htmlEntities(node[i].id_articulo)+'</td>'+
                            '<td title="'+htmlEntities(node[i].id_articulo)+'"><b>'+htmlEntities(node[i].des_articulo)+'</b></td>'+
                            '<td>'+htmlEntities(node[i].cve_lote)+'</td>'+
                            '<td>'+htmlEntities(node[i].Caducidad)+'</td>'+
                            '<td>'+htmlEntities(node[i].contenedor)+'</td>'+
                            '<td>'+htmlEntities(node[i].LP)+'</td>'+
                            '<td>Entrada</td>'+
                            '<td>'+htmlEntities(node[i].Des_Motivo)+'</td>'+
                            '<td>'+htmlEntities(node[i].almacen_clave)+'</td>'+
                            '<td title="Proveedor"><b>'+origen+'</b></td>'+
                            '<td></td>'+
                            '<td title="'+htmlEntities(node[i].almacen_nombre)+'"><b>'+destino+'</b></td>'+
                            '<td align="right">'+htmlEntities(node[i].cantidad)+'</td>'+
                            '<td>'+htmlEntities(node[i].cve_usuario)+'</td>'+
                            '<td>'+htmlEntities(node[i].fecha)+'</td>'+
                         '</tr>';
            }
            return _body;
        }

    }

    /**
     * @author Ricardo Delgado.
     * Busca y llena el select productos.
     */ 
    function searchListProductos(setValue){

        var data = { action : "getListProductos" };

        if(select_almacen.value !== ''){

            data.idAl = select_almacen.value;

            if(input_fechaE.value !== ''){
                data.feIn = input_fechaE.value;
            }

            if(input_fechaF.value !== ''){
                data.feEn = input_fechaF.value;
            }
        
            $.ajax({
                url: "/api/kardex/index.php",
                type: "POST",
                data: data,
                beforeSend: function(x){
                    if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
                },
                success: function(res){
                    console.log("KARDEX = ", res);
                    fillSelect(res);
                },
                error : function(res){
                    window.console.log(res);
                }
            });
        }
        else{
            select_producto.innerHTML = "<option value =''>Seleccione un Producto (0)</option>";
            select_lote.innerHTML = "<option value =''>Seleccione un Lote (0)</option>";
            $(select_producto).trigger("chosen:updated");
            $(select_lote).trigger("chosen:updated");
        }

        function fillSelect(node){  

            var option = "",
                options = "",
                nodeA = node.articulos,
                validate = [];

            if(nodeA){

                for(var l = 0; l < nodeA.length; l++){

                    for(var i = 0; i < nodeA[l].length; i++){
                        var cve = nodeA[l][i].cve_articulo;
                        if(!validate.find(function(x){if(x === cve) return x;})){
                            options += "<option value = "+htmlEntities(nodeA[l][i].cve_articulo)+">"+"( "+htmlEntities(nodeA[l][i].cve_articulo)+" ) "+htmlEntities(nodeA[l][i].des_articulo)+"</option>";
                            validate.push(cve);
                        }
                    }
                }
            }

            option = "<option value =''>Seleccione un Producto ("+validate.length+")</option>";

            select_producto.innerHTML = option+options;


            if(setValue){

                for(var i = 0; i < select_producto.options.length; i++){
                    if (select_producto.options[i].value == setValue) 
                        select_producto.value = setValue;
                }
            }


            $(select_producto).trigger("chosen:updated");
        }
    }
    /**
     * @author Ricardo Delgado.
     * Busca y llena el select lotes.
     */ 
    function searchListLote(setValue, setValueArti){


        var data = { 
            action : "getListLote" 
        };

        if(select_almacen.value !== ''){

            if(setValueArti)
                data.articulo = setValueArti;
            else
                data.articulo = select_producto.value;

            if(input_fechaE.value !== ''){
                data.feIn = input_fechaE.value;
            }

            if(input_fechaF.value !== ''){
                data.feEn = input_fechaF.value;
            }

            console.log("DATA LOTES = ", data);

            $.ajax({
                url: "/api/kardex/index.php",
                type: "POST",
                data: data,
                beforeSend: function(x){
                    if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
                },
                success: function(res){
                    fillSelect(res);
                },
                error : function(res){
                    window.console.log(res);
                }
            });
        }
        else{
            select_lote.innerHTML = "<option value =''>Seleccione un Lote (0)</option>";
        }

        function fillSelect(node){

            var option = "",
                nodeA = node.lote,
                validate = [],
                options = "";
            console.log("node LOTE", node);
            for(var l = 0; l < nodeA.length; l++){

                for(var i = 0; i < nodeA[l].length; i++){
                    var cve = nodeA[l][i].lote;
                    console.log("cve LOTE", cve);
                    if(!validate.find(function(x){if(x === cve) return x;})){
                        options += "<option value = "+htmlEntities(cve)+">"+htmlEntities(cve)+"</option>";
                        validate.push(cve);
                    }
                }
            }
            console.log("options LOTE", options);
            option = "<option value =''>Seleccione un Lote ("+validate.length+")</option>";

            select_lote.innerHTML = option+options;

            if(setValue){
                for(var i = 0; i < select_lote.options.length; i++){
                    if (select_lote.options[i].value == setValue) 
                        select_lote.value = setValue;
                }
            }

            $(select_lote).trigger("chosen:updated");
        }
    }

    /**
     * @author Ricardo Delgado.
     * Busca y llena el select lotes.
     */ 
   /* function fillListMovi(){

        var options = "";

        options += "<option value = '1'>Entrada</option>";
        options += "<option value = '2'>Acomodo</option>";
        options += "<option value = '20'>Traslado</option>";
        options += "<option value = '8'>Salida</option>";
        options += "<option value = '4'>Salida por Reabastecimiento</option>";
        options += "<option value = '5'>Entrada por Reabastecimiento</option>";
        options += "<option value = '9'>Entrada por Ajuste</option>";
        options += "<option value = '10'>Salida por Ajuste</option>";   
        
        select_movi.innerHTML += options;
        $(select_movi).trigger("chosen:updated");
        
    }*/

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
                if (data.success == true) {
                    select_almacen.value = data.codigo.id;
                    $(select_almacen).trigger("chosen:updated");
                    searchListProductos();
                    searchListLote();
                    searchListTable();
                }
            },
            error: function(res){
                window.console.log(res);
            }
        });
    }
//}

</script>

<style>
   
</style>