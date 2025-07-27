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
</style>

<div class="wrapper wrapper-content  animated " id="list">
    <h3>Reporte Diferencia entre Conteos*</h3>
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
                    <div class="row" >
                        <a onclick="exportarExcel()" target="_blank" class="btn btn-primary pull-right" style="margin-left:15px"><span class="fa fa-upload"></span> Exportar Excel</a>
                        <a onclick="exportarPDF()" target="_blank" class="btn btn-primary pull-right" style="margin-left:15px"><span class="fa fa-upload"></span> Exportar PDF</a>
                    </div>
                    <br>
    				        <div class="table-responsive">
    				            <table id="table-info"  class="table table-hover table-striped no-margin">
            				        <thead>
                                <tr>
                                    <th>Acciones</th>
                                    <th>Inventario</th>
                                    <th>Fecha de Cierrre de Inventario</th>
                                    <th>Ubicación</th>
                                    <th>Diferencia</th>
                                    <th>Conteos Totales</th>
                                    <th>Usuario</th>
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
    var tableData = new TableData(),
        TABLE = null,
        buttons = [];

    searchData();

    function searchData()
    {
        $.ajax({
            type: "GET",
            dataType: "json",
            beforeSend: function(x) { 
                if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/reportes/lista/difconteos.php',
            success: function(data) 
            {
              fillTable(data);
            },
            error:function(res){
                window.console.log(res);
            }
        });
    }

    /** 
     * @author Ricardo Delgado.
     * Busca y llena el select productos.
     */
    function fillTable(node)
    {
        tableData.destroy();
        var body = document.getElementById('tbody');
        body.innerHTML = "";
        body.innerHTML += entrada(node);
        TABLE = tableData.init("table-info",buttons);
        function entrada(node)
        {
            var _body = "";
            for(var i = 0; i < node.length ; i++)
            {
                var style = ""; 
                _body += '<tr '+style+'>'+
                    '<td><a href="#" onclick="exportarPDF(\''+htmlEntities(node[i].consecutivo)+'\')"><i class="fa fa-file-pdf-o" title="PDF"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;  </td>'+
                    '<td>'+htmlEntities(node[i].consecutivo)+'</td>'+
                    '<td>'+htmlEntities(node[i].fecha_final)+'</td>'+
                    '<td>'+htmlEntities(node[i].zona)+'</td>'+
                    '<td>'+htmlEntities(node[i].diferencia)+'</td>'+
                    '<td>'+htmlEntities(node[i].conteos_totales)+'</td>'+ //Se esta colocando el numero de conteos totales
                    '<td>'+htmlEntities(node[i].usuario)+'</td>'+
                '</tr>';
            }
            return _body;
        }
    }
  
    function exportarPDF(consecutivo = 0) 
    {
        var form = document.createElement('form'),
        nobody = document.createElement('input'),
        folio = document.createElement('input'),
        estado = document.createElement('input'),
        action = document.createElement('input');

        form.setAttribute('target', '_blank');
        form.setAttribute('method', 'post');
        form.setAttribute('action', '/api/inventariosfisicos/update/index.php');
        nobody.setAttribute('type', 'hidden');
        nobody.setAttribute('name', 'nofooternoheader');
        nobody.setAttribute('value', 'true');
        folio.setAttribute('type', 'hidden');
        folio.setAttribute('name', 'id');
        folio.setAttribute('value', consecutivo);
        action.setAttribute('type', 'hidden');
        action.setAttribute('name', 'action');
        action.setAttribute('value', 'printDiferencias');
        form.appendChild(nobody);
        form.appendChild(folio);

        form.appendChild(action);
        document.getElementsByTagName('body')[0].appendChild(form);
        form.submit();
    }
  
    function exportarExcel() 
    {
        var form = document.createElement('form'),
        nobody = document.createElement('input'),
        folio = document.createElement('input'),
        estado = document.createElement('input'),
        action = document.createElement('input');

        form.setAttribute('target', '_blank');
        form.setAttribute('method', 'post');
        form.setAttribute('action', '/api/inventariosfisicos/update/index.php');
        nobody.setAttribute('type', 'hidden');
        nobody.setAttribute('name', 'nofooternoheader');
        nobody.setAttribute('value', 'true');
        action.setAttribute('type', 'hidden');
        action.setAttribute('name', 'action');
        action.setAttribute('value', 'printDiferenciasExcel');
        form.appendChild(nobody);
        form.appendChild(folio);

        form.appendChild(action);
        document.getElementsByTagName('body')[0].appendChild(form);
        form.submit();
    }
</script>