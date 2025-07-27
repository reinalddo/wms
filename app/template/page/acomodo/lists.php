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
    <h3>Bitacora</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>Usuarios:</label>
                                <select id="select-usu" class="chosen-select form-control">
                                    <option value="">Seleccione Usuario</option>
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
                        <div class="col-lg-2">
                            <button id="button-buscar" style="margin-top: 25px;" class="btn btn-primary btn-sm" >Buscar</button>
                        </div>          
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="table-info"  class="table table-hover table-striped no-margin display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Modulo</th>
                                    <th>Mensaje</th>
                                    <th>Fecha</th>
                                    <th>Referencia</th>
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
    var bitacora = new Bitacora(); 
});
function Bitacora(){*/

    var select_usu = document.getElementById('select-usu'),
        input_fechaE = document.getElementById('input-fechaE'),
        input_fechaF = document.getElementById('input-fechaF'),
        button_buscar = document.getElementById('button-buscar'),
        tableDataInfo = new TableDataRest(),
        TABLE = null,
        buttons = [{
                    extend: 'excelHtml5',
                    title: 'Bitacora',
                    customize: function() { swal("Descargando Excel", "Su descarga empezara en breve", "success");}
                    },
                    {
                    extend: 'pdfHtml5',
                    title: 'Bitacora',
                    orientation: 'portrait',
                    pageSize: 'A4',
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

    button_buscar.onclick = function(){
        fillTable();
    };

    init();

    function init(){

        $.ajax({
            url: "/api/acomodo/bitacora/index.php",
            type: "POST",
            data: {
                "action" : "enter-view"
            },
            beforeSend: function(x){
                if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
            },
            success: function(res){
                console.log("SUCCESS", res);
                fillSelectUsers(res.users);
                fillTable();
            },
            error : function(res){
                console.log("ERROR", res);
                window.console.log(res);
            }
        });
    }

    function fillSelectUsers(node){

        var options = "";

        if(node){

            for(var i = 0; i < node.length; i++){
                options += "<option value = "+node[i].cve_usuario+">"+htmlEntities(node[i].cve_usuario)+"</option>";
            }
        }

        select_usu.innerHTML += options;
        $(select_usu).trigger("chosen:updated");
    }


    /**
     * @author Ricardo Delgado.
     * Busca y llena el select productos.
     */
    function fillTable(){

        var data = { action : "getListTable" },
            user = select_usu.value,
            fechaIn = input_fechaE.value,
            fechaEn = input_fechaF.value;

        if(user)
            data.user = user;

        if(fechaIn)
            data.feIn = fechaIn;

        if(fechaEn)
            data.feEn = fechaEn;

        $.ajax({
            url: "/api/acomodo/bitacora/index.php",
            type: "POST",
            data: data,
            beforeSend: function(x){
                if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
            },
            success: function(res){
                fillDta(res.table);
            },
            error : function(res){
                window.console.log(res);
            }
        });

        function fillDta(node){
            var array = [];
            for(var i = 0; i < node.length; i++){
                array.push([
                    node[i].cve_usuario,
                    node[i].MODULO,
                    node[i].mensage,
                    node[i].Fecha,
                    node[i].Referencia
                ]);
            }
            tableDataInfo.destroy();
            tableDataInfo.init("table-info",buttons, false, array);
        }
    }
//}
</script>
