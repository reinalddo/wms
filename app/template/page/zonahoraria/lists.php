<?php

use \ZonaHoraria\ZonaHoraria as ZonaHoraria;
use \Companias\Companias as Compania;
$zonaHoraria = new ZonaHoraria();
$zonasHorarias =  $zonaHoraria->getZonas();
$zonaSelec = $zonaHoraria->getZonaHoraria();

?>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">

 <link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
 <link href="/css/bootstrap-imageupload.min.css" rel="stylesheet">
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

<div class="wrapper wrapper-content  animated" id="list">

    <h3>Configuración Zona horaria</h3>

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Zonas horarias</label>
                                    <select  id="ZonaHoraria" class="chosen-select form-control input-sm">
                                        <option value="">Seleccione una opcion</option>
                                        <?php foreach( $zonasHorarias as $p ): ?>
                                            <option value="<?php echo $p; ?>"><?php echo $p; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                            </div>                  
                        </div>
                        
                        <div class="col-sm-4">
                             <label> </label>
                             <div class="input-group-btn">
                               <a href="#" onclick="agregar()"><button class="btn btn-primary" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Asignar zona horaria</button></a>
                            </div>                  
                        </div>
                      </div>
                    </div>
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
 <script src="/js/plugins/sweetalert/sweetalert.min.js"></script>

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
<script src="/js/bootstrap-imageupload.js"></script>
<script type="text/javascript">

    function agregar() {
        $.ajax({
           url: '/api/zonahoraria/lista/index.php',
           type: "POST",
           dataType: "json",
           data: {
                tipo: "validar",
                action : "getZonaHoraria"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            success: function(data) {
                if (data.success == true) {
                    var zonaHoraria;
                    zonaHoraria = $("#ZonaHoraria").val();
                    
                    $.ajax({
                       url: '/api/zonahoraria/lista/index.php',
                       type: "POST",
                       dataType: "json",
                       data: {
                            descripcion : zonaHoraria,
                            action : "add"
                        },
                        beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                        },
                        success: function(data) {
                            if (data.success == true) {
                                 swal("Exito", "Zona horaria configurada exitosamente", "success");
                            }
                        }
                    });  
                    
                }
                else{
                    var zonaHoraria;
                    zonaHoraria = $("#ZonaHoraria").val();
                    
                    $.ajax({
                       url: '/api/zonahoraria/lista/index.php',
                       type: "POST",
                       dataType: "json",
                       data: {
                            descripcion : zonaHoraria,
                            action : "update"
                        },
                        beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                        },
                        success: function(data) {
                            if (data.success == true) {
                                 swal("Exito", "Zona horaria configurada exitosamente", "success");
                            }
                        }
                    });  
                    
                }
           }
      });




    }
</script>
<script>
    $(document).ready(function(){
    $.ajax({
           url: '/api/zonahoraria/lista/index.php',
           type: "POST",
           dataType: "json",
           data: {
                tipo: "buscar",
                action : "getZonaHoraria"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            success: function(data) {
                if (data.success == true) {
                     $("#ZonaHoraria").val(data.descripcion);
                     $('#ZonaHoraria').trigger("chosen:updated");
                }
        }
    }); 
    $(function() {
        $('.chosen-select').chosen();
        $('.chosen-select-deselect').chosen({ allow_single_deselect: true });
      });

    });
</script>
