<?php 
/*
$num_almacenes = \db()->prepare("SELECT COUNT(*) as num_almacenes FROM c_almacenp WHERE Activo = 1");
$num_almacenes->execute();
$num_almacenes = $num_almacenes->fetch()['num_almacenes'];
*/
 ?>
<link href="/css/plugins/iCheck/custom.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<div class="wrapper wrapper-content" style="padding-bottom: 10px">
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Configurar Código BL</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-lg-6">
                            <label>Seleccione los campos para construir el Cógido BL</label>
                            <div class="checkbox">
                                <label id="cve_pasillo">
                                    <input type="checkbox" name="csd" value="cve_pasillo">
                                    Pasillo
                                </label>
                            </div>
                            <div class="checkbox">
                                <label id="cve_rack">
                                    <input type="checkbox" name="csd" value="cve_rack">
                                    Rack
                                </label>
                            </div>
                            <div class="checkbox" id="cve_nivel">
                                <label>
                                    <input type="checkbox" name="csd" value="cve_nivel">
                                    Nivel
                                </label>
                            </div>
                            <div class="checkbox">
                                <label id="Seccion">
                                    <input type="checkbox" name="csd" value="Seccion">
                                    Sección
                                </label>
                            </div>
                            <div class="checkbox">
                                <label id="Ubicacion">
                                    <input type="checkbox" name="csd" value="Ubicacion">
                                    Posición
                                </label>
                            </div>                            
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Vista preliminar del Código BL</label>
                                <input type="text" name="codigocsd" id="codigocsd" readonly class="form-control">
                                <input type="hidden" name="codigo" id="codigo">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div style="text-align: right;">
                                <button class="btn btn-default" type="button" onclick="clean()">Limpiar</button>
                                <button class="btn btn-primary" type="button" onclick="save()">Guardar</button>
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

<!-- Flot -->
<script src="/js/plugins/flot/jquery.flot.js"></script>
<script src="/js/plugins/flot/jquery.flot.tooltip.min.js"></script>
<script src="/js/plugins/flot/jquery.flot.spline.js"></script>
<script src="/js/plugins/flot/jquery.flot.resize.js"></script>
<script src="/js/plugins/flot/jquery.flot.pie.js"></script>

<!-- Peity -->
<script src="/js/plugins/peity/jquery.peity.min.js"></script>
<script src="/js/demo/peity-demo.js"></script>

<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>

<!-- jQuery UI -->
<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>

<!-- GITTER -->
<script src="/js/plugins/gritter/jquery.gritter.min.js"></script>

<!-- Sparkline -->
<script src="/js/plugins/sparkline/jquery.sparkline.min.js"></script>

<!-- Sparkline demo data  -->
<script src="/js/demo/sparkline-demo.js"></script>

<!-- ChartJS-->
<script src="/js/plugins/chartJs/Chart.min.js"></script>

<!-- Toastr -->
<script src="/js/plugins/toastr/toastr.min.js"></script>

<!-- Morris -->
<script src="/js/plugins/morris/raphael-2.1.0.min.js"></script>
<script src="/js/plugins/morris/morris.js"></script>

<!-- d3 and c3 charts -->
<script src="/js/plugins/d3/d3.min.js"></script>
<script src="/js/plugins/c3/c3.min.js"></script>

<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.min.js"></script>

<!-- iCheck -->
<script src="/js/plugins/iCheck/icheck.min.js"></script>
<!-- Sweet Alert -->
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>

<script type="text/javascript">
    $(document).ready(function(){

        var init = 0;
        $.ajax({
            url: '/api/codigocsd/lista/index.php',
            data: {
                action: 'get',
                id_almacen: '<?php echo $_SESSION['id_almacen']; ?>',
                cia: '<?php echo $_SESSION['cve_cia']; ?>'
            },
            dataType: 'json',
            cache: false,
            method: 'GET'
        }).done(function(data){
            console.log("CODIGO CSD = ", data);
            var checked = data.codigo.split("-");
            if(init == 0)
            {
                $("#codigocsd").val(data.codigo_init);
                $("#codigo").val(data.codigo);
            }
            $('input[type="checkbox"').each(function(i,e){
                if(checked.indexOf(e.value) !== -1){
                    $(e).iCheck("check");
                }
            });
                init = 1;
        });

        $('input[type="checkbox"]').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green'
        });
        $('input[type="checkbox"]').on('ifChecked', function(e){
            var el = e.target.value;
            if(init == 1)
            {
                asignarValor($(`#${el}`).text(), el);
                //console.log("asignar = ",$(`#${el}`).text());
            }
        });

        $('input[type="checkbox"]').on('ifUnchecked', function(e){
            var el = e.target.value;
            removerValor($(`#${el}`).text(), el);
        });

        function asignarValor(valor, clave){
            var target = $("#codigocsd");
            var targeth = $("#codigo");
            var valor = $.trim(valor);

            if(target.val() === ''){
                target.val(valor);
            }else{
                target.val(target.val() + "-" + valor);
            }

            if(targeth.val() === ''){
                targeth.val(clave);
            }else{
                targeth.val(targeth.val() + "-" + clave);
            }
        }

        function removerValor(valor, clave){
            var target = $("#codigocsd");
            var targeth = $("#codigo");
            var valor = $.trim(valor);

            if(target.val() === '' || targeth.val() === ''){
                return false;
            }

            if(target.val().indexOf(valor) !== 0  || target.val().indexOf(valor) !== -1){
                if(target.val().length === valor.length){
                    target.val(target.val().replace(valor,""));
                }
                else if((target.val().indexOf(valor) + valor.length) === target.val().length){
                    target.val(target.val().replace("-" + valor,""));
                }
                else{
                    target.val(target.val().replace(valor + "-",""));
                }
            }

            if(targeth.val().indexOf(clave) !== 0  || targeth.val().indexOf(clave) !== -1){
                if(targeth.val().length === clave.length){
                    targeth.val(targeth.val().replace(clave,""));
                }
                else if((targeth.val().indexOf(clave) + clave.length) === targeth.val().length){
                    targeth.val(targeth.val().replace("-" + clave,""));
                }
                else{
                    targeth.val(targeth.val().replace(clave + "-",""));
                }
            }
        }
    });
    function clean(){
        $('input[type="checkbox"][name="csd"]').each(function(i,e){
            $(e).iCheck('uncheck');
        });
        $('input[type="text"]').each(function(i,e){
            $(e).val('');
        });
    }
    function save(){
        console.log("codigo", $("#codigo").val());
        console.log("cia", <?php echo $_SESSION['cve_cia'] ?>);
        $.ajax({
            url: '/api/codigocsd/update/index.php',
            data: {
                action: 'save',
                codigo: $("#codigo").val(),
                id_almacen: '<?php echo $_SESSION['id_almacen']; ?>',
                cia: <?php echo $_SESSION['cve_cia'] ?>
            },
            dataType: 'json',
            method: 'POST'
        }).done(function(data){
            if(data.success){
                //¿Desea aplicar el Código BL a las ubicaciones existentes?
                swal({
                    title: "Éxito",
                    text: "Código BL guardado correctamente",
                    type: "success",
                    showCancelButton: false,
                    cancelButtonText: "No",
                    showConfirmButton: true,
                    confirmButtonText: "Ok",
                    closeOnConfirm: false,
                    closeOnCancel: true
                }, function(confirm){
                    window.location.reload();
                    /*
                    if(confirm){
                    	$.ajax({
                    		url: '/api/codigocsd/update/index.php',
				            data: {
				                action: 'applyCSD',
				                cia: <?php echo $_SESSION['cve_cia'] ?>
				            },
				            dataType: 'json',
            				method: 'POST'
                    	}).done(function(data){
                    		if(data.success){
                    			swal("Éxito", "Código BL aplicado a las ubicaciones existentes", "success");
                    		}else{
                    			swal("Error", data.error, "error");
                    		}
                    	});
                    }
                    */
                });
            }
            else console.log("Error", data);
        }).fail(function(data){console.log("Error", data);});
    }
</script>