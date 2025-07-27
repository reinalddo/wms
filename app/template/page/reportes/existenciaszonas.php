<?php
$almacenes = new \AlmacenP\AlmacenP();
?>

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
 <h3>Existencia por Zonas</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="email">Almacén</label>
                            <select name="almacen" id="almacen" class="chosen-select form-control">
                                <option value="">Seleccione Almacén</option>
                                <?php foreach ($almacenes->getAll() as $almacen) : ?>
                                    <?php if ($almacen->Activo == 1) :?>
                                    <option value="<?php echo $almacen->clave; ?>"><?php echo"($almacen->clave)". $almacen->nombre; ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        </div>
                           <div class="col-lg-4">
                        <div class="form-group">
                            <label for="email">Zona de Almacenaje</label>
                            <select name="zona" id="zona" class="chosen-select form-control">
                              <option value="">Seleccione Almacén primero</option>
                            </select>
                        </div>
                        </div>
                        <div class="col-lg-4">
                         <label for="email">&#160;&#160;</label>
                          <div class="form-group">

                               <button id="search" name="singlebutton" class="btn btn-primary" disabled="disabled">Buscar</button>

                        </div>
                        </div>

                    </div>
                    </div>
                </div>
                <div class="ibox-content">
                <div class="table-responsive">
                <table id="table-info"  class="table table-hover table-striped no-margin display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>Almacén</th>
                            <th>Zona</th>
                            <th>Ubicación</th>
                            <th>Clave</th>
                            <th>Descripción</th>
                            <th>Fecha de Ingreso</th>
                            <th>Lote</th>
                            <th>Caducidad</th>
                            <th>Serie</th>
                            <th>Existencia</th>
                        </tr>
                    </thead>

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

    var tableDataInfo = new TableDataRest(),
        TABLE = null,
        buttons = [{
                    extend: 'excelHtml5',
                    title: 'Existencia por Zonas',
                    customize: function() { swal("Descargando Excel", "Su descarga empezara en breve", "success");}
                    },
                    {
                    extend: 'pdfHtml5',
                    title: 'Existencia por Zonas',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    download: 'open',
                    customize: function() { swal("Descargando PDF", "Su descarga empezara en breve", "success");},
                    }
                ];
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
                        setTimeout(function() {
                            document.getElementById('almacen').value = data.codigo.clave;
                            $('#almacen').trigger('change');
                            $('#almacen').trigger("chosen:updated");
                            searchData();
                        }, 1000);
                    }
                },
                error: function(res){
                    window.console.log(res);
                }
            });
        }
        almacenPrede();

        $(function() {
            $('.chosen-select').chosen();
            $('.chosen-select-deselect').chosen({ allow_single_deselect: true });
        });

        function searchData(){

            var select_zona = document.getElementById('zona');

            var data = {};

            if(select_zona.value !== ''){
                data.zona = select_zona.value;
            }

            $.ajax({
                type: "POST",
                dataType: "json",
                data: data,
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                url: '/api/reportes/lista/existenciazona.php',
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
                    node[i].almacen,
                    node[i].zona,
                    node[i].ubicacion,
                    node[i].clave,
                    node[i].descripcion,
                    node[i].fecha_ingreso,
                    node[i].lote,
                    node[i].caducidad,
                    node[i].serie,
                    node[i].existencia
                    ]);
            }
            tableDataInfo.destroy();
            tableDataInfo.init("table-info", buttons, true, data);
        }


          $('#almacen').change(function(e) {
            var almacen= $(this).val();
            if(almacen.length > 0){
              $("#search").removeAttr('disabled');
            }
            $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                clave : almacen,
                action : "traerZonasDeAlmacenP"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/almacenp/update/index.php',
            success: function(data) {

                                var options = $("#zona");
                            options.empty();
                             options.append(new Option("Seleccione", ""));

                    for (var i=0; i<data.zonas.length; i++)
                    {
                        options.append(new Option(data.zonas[i].clave_zona +" "+data.zonas[i].nombre_zona, data.zonas[i].clave_zona));


                    }
                    $("#zona").trigger("chosen:updated");

                }

        });


        });


                $( "#search" ).click(function() {
                    if ($("#almacen").val()=="")
                        return;
                searchData();
});





</script>
