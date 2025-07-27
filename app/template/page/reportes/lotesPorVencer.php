<?php
$almacenes = new \AlmacenP\AlmacenP();
$model_almacen = $almacenes->getAll();
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
    <h3>Lotes por Vencer</h3>
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
                                    <?php foreach( $model_almacen AS $almacen ): ?>
                                        <?php if($almacen->Activo == 1):?>
                                        <option value="<?php echo $almacen->id; ?>"><?php echo"($almacen->clave) ". $almacen->nombre; ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>                  
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="Fecha">Fecha Límite</label>
                                <input type="date" name="fecha_limite" class="form-control" id="fecha_limite" min="<?php 
                                    $fecha = getDate();
                                    $y = $fecha["year"];
                                    $m = $fecha["mon"];
                                    if($m < 10) $m = "0".$m;
                                    $d = $fecha["mday"];
                                    if($d < 10) $d = "0".$d;
                                    echo $y."-".$m."-".$d;
                                 ?>">
                            </div>
                        </div>

                    </div>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="table-info"  class="table table-hover table-striped no-margin display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>CLAVE ARTICULO</th>
                                    <th>ARTÍCULO</th>
                                    <th>LOTE</th>
                                    <th>CADUCIDAD</th>
                                    <th>UBICACIÓN</th>
                                    <th>EXISTENCIA</th>
                                    <th>FECHA DE INGRESO</th>
                                    <th>PROVEEDOR</th>
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


    var tableDataInfo = new TableDataRest(),
        TABLE = null,
        buttons = [{
                    extend: 'excelHtml5',
                    title: 'Lotes por Vencer'
                    //customize: function() { swal("Descargando Excel", "Su descarga empezara en breve", "success");}
                    },
                    {
                    extend: 'pdfHtml5',
                    title: 'Lotes por Vencer',
                    orientation: 'portrait',
                    pageSize: 'A4',
                    download: 'open'
                    //customize: function() { swal("Descargando PDF", "Su descarga empezara en breve", "success");},
                    }
                ];

    almacenPrede();

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
                    document.getElementById('almacen').value = data.codigo.id;
                    searchData();
                }
            },
            error: function(res){
                window.console.log(res);
            }
        });
    }

    /**
     * @author Ricardo Delgado.
     * Busca y llena el select productos.
     */

     $("#fecha_limite").change(function(){

        console.log("fecha change");
        searchData();

     });

    function searchData(){

        var select_almacen = document.getElementById('almacen');
        var fecha_limite_input = document.getElementById('fecha_limite');

        var data = {};

        if(select_almacen.value !== ''){
            data.almacen = select_almacen.value;
        }
        data.fecha_limite = fecha_limite_input.value;

        console.log("data.fecha_limite = ",data.fecha_limite);

        $.ajax({
            type: "POST",
            dataType: "json",
            data: data,
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/reportes/lista/lotesPorVencer.php',
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

        for(var i = 0; i < node.length ; i++){

            data.push([
               node[i].cve_articulo,
                node[i].articulo,
                node[i].lote,
                node[i].caducidad,
                node[i].ubicacion,
                node[i].existencia,
                node[i].fecha_ingreso,
                node[i].Proveedor
                ]);
        }
        tableDataInfo.destroy();
        tableDataInfo.init("table-info", buttons, true, data);
    }
//}
</script>
