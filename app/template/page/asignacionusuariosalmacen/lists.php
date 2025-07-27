<?php
$almacenes = new \AlmacenP\AlmacenP();
$model_almacen = $almacenes->getAll();

?>

<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/bootstrap.min.css" rel="stylesheet">
<link href="/font-awesome/css/font-awesome.css" rel="stylesheet">
<link href="/css/animate.css" rel="stylesheet">
<link href="/css/style.css" rel="stylesheet">
<link href="/css/plugins/clockpicker/clockpicker.css" rel="stylesheet">
<link href="/css/plugins/iCheck/custom.css" rel="stylesheet">
 <style type="text/css">
#clientesEditar li{
	    cursor: pointer;
}

.wi{
    width: 90% !important;
}
.relative{
    position: relative;
}
.floating-button{
    position: absolute;
    right: 0;
    top: 40%;
    transform: translateY(-40%);
}
[aria-grabbed="true"]
{
    background: #1ab394 !important;
    color: #fff !important;
}
.input-group-addon {
    padding: 0px;
}
</style>
<!-- Mainly scripts -->
<!-- Barra de nuevo y busqueda -->
<div class="wrapper wrapper-content  animated fadeInRight">

    <h3>Asignación de Usuario a Almacén</h3>

    <div class="row">
        <div class="col-lg-12">
         <div class="panel-body">
            <div class="ibox ">
            
                    <div class="row">
                        <div class="col-md-6">
                            <label for="email">Almacen</label>
                            <div class="input-group">                        
                                <select id="txtAlmacen" class="chosen-select form-control">
                                    <!--<option value="">Clave de la Compañia</option>-->
                                    <?php foreach( $model_almacen AS $almacen ): ?>
                                        <?php if($almacen->Activo == 1):?>
                                        <option value="<?php echo $almacen->clave; ?>"><?php echo"($almacen->clave) ". $almacen->nombre; ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <div class="input-group-addon">
                                    <button id ="editar" class="btn btn-primary" type="button"><i class="fa fa-search"></i>&nbsp;&nbsp;Cargar Usuarios</button>
                                </div>
                            </div>
                        </div>
                          
                   </div>
                </div>
                <div class="ibox-content dragdrop">                             
                    
                    <div class="col-sm-12" style="margin-bottom: 20px">
                        <input type="checkbox" id="selectAll" >
                        <label for="selectAll">Seleccionar Todo</label>
                    </div>                                   
                    <div id="clientesEditar">
                        <div class="col-md-6" relative>
                            <label for="email">Usuarios Disponibles</label>
                            <ol data-draggable="target" id="from" class="wi"></ol>
                            <button class="btn btn-primary floating-button" onclick="add('#from', '#to')">>></button>
                            <button class="btn btn-primary floating-button" onclick="remove('#to', '#from')" style="margin-top: 40px"><<</button>
                        </div>
                        <div class="col-md-6">
                            <label for="email">Usuarios Asignados</label>
                            <ol data-draggable="target" id="to" class="wi"></ol>
                        </div>

                        <div class="col-md-12" style="margin-bottom: 100px">
                            <button id ="guardar" class="btn btn-primary pull-center permiso_registrar" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Planificar almacen</button>
                        </div>
                    </div>
                
                 </div>
             
            </div>
        </div>
        
    </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="conModal" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Aviso de confirmación</h4>
                    </div>
                    <div class="modal-body">
                        <p>Los datos fueron guardados satisfactoriamente</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
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

<!-- Drag & Drop Panel -->
<script src="/js/dragdrop.js"></script>
<!-- Data picker -->
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>

   <script src="/js/plugins/iCheck/icheck.min.js"></script>

<!-- Clock picker -->
<script src="/js/plugins/clockpicker/clockpicker.js"></script>

<!-- Sweet alert -->
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>

<script type="text/javascript">

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
                    document.getElementById('txtAlmacen').value = data.codigo.clave;
                    setTimeout(function() {
                        $('#editar').trigger('click');
                    }, 1000);
                }
            },
            error: function(res){
                window.console.log(res);
            }
        });
    }
    almacenPrede();
    $(document).on("click","#editar", function(){
        $("#from .itemlist").remove();
        $("#to .itemlist").remove();
     //   $(".itemlist").remove();
        if($('#txtAlmacen').val()){
            $("#guardar").prop( "disabled", false );
        }

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_almac : $('#txtAlmacen').val(),
                action : "traerUsuariosDeAlmacen"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/almacen/update/index.php',
            success: function(data) {
				
                 if (data.success == true) {
					
					var arr = $.map(data.todosUsuarios, function(el) { return el; })
                    arr.pop();
                    for (var i=0; i<data.todosUsuarios.length; i++)
                    {
                        var ul = document.getElementById("from");
                        var li = document.createElement("li");
                        var checkbox = document.createElement("input");
                        checkbox.style.marginRight = "10px";
                        checkbox.setAttribute("type", "checkbox");
                        checkbox.setAttribute("class", "drag");
                        checkbox.setAttribute("onclick", "selectParent(this)");
                        li.appendChild(checkbox);
                        li.appendChild(document.createTextNode(data.todosUsuarios[i].nombre_usuario));
                        li.setAttribute("dayta-draggable", "item");
                        li.setAttribute("draggable", "false");
                        li.setAttribute("aria-draggable", "false");
                        li.setAttribute("aria-grabbed","false");
                        li.setAttribute("tabindex","0");
                        li.setAttribute("class","itemlist");
                        li.setAttribute("onclick","selectChild(this)");
                        li.setAttribute("value",data.todosUsuarios[i].clave_usuario);
                        ul.appendChild(li);

                    }

					var arr1 = $.map(data.usuariosAlmacen, function(el) { return el; })
                    arr1.pop();
                    for (var i=0; i<data.usuariosAlmacen.length; i++)
                    {

                        var ul = document.getElementById("to");
                        var li = document.createElement("li");
                        var checkbox = document.createElement("input");
                        checkbox.style.marginRight = "10px";
                        checkbox.setAttribute("type", "checkbox");
                        checkbox.setAttribute("class", "drag");
                        checkbox.setAttribute("onclick", "selectParent(this)");
                        li.appendChild(checkbox);
                        li.appendChild(document.createTextNode(data.usuariosAlmacen[i].nombre_usuario));
                        li.setAttribute("dayta-draggable", "item");
                        li.setAttribute("draggable", "false");
                        li.setAttribute("aria-draggable", "false");
                        li.setAttribute("aria-grabbed","false");
                        li.setAttribute("tabindex","0");
                        li.setAttribute("class","itemlist");
                        li.setAttribute("onclick","selectChild(this)");
                        li.setAttribute("value",data.usuariosAlmacen[i].clave_usuario);
                        ul.appendChild(li);

                    }
                }
            }
        });

    });

    $(document).on("click","#guardar", function(){

        var rels = [];

        $("#to").each(function() {
            var localRels = [];

            $(this).find('li').each(function(){
                localRels.push( $(this).attr('value') );
            });

            rels.push(localRels);
        });
		console.log(rels);
		
          $.post('/api/almacen/update/index.php',
            {
                 cve_almac:$('#txtAlmacen').val(),
                 action : 'guardarUsuario',
                 usuarios: rels
            },
            function(response){
                console.log(response);
            }, "json")
            .always(function() {
                $("#conModal").modal();
                setTimeout(function(){
                    $("#conModal").modal("hide");
                }, 3000);
                $("#selectAll").iCheck('uncheck')
                $("#from .itemlist").remove();
                $("#to .itemlist").remove();
                $('.chosen-select').trigger("chosen:updated");
            });

            /*$.post( "/api/almacen/update/index.php",
            {
                cve_almac:$('#txtAlmacen').val(),
                action : 'guardarUsuario',
                usuarios: rels

            } ,function( data ) {
            alert(data);
         });*/
    });
	
	/*	   $("#selectAll").click(function(){
	         $('#from li').each(function(i)
              {
	//console.log(this);
	          $('#to').append($(this));

   //$(this).remove();
   

  
});
	   
	   }); */

    function add(from, to){
        console.log("Entro");
        var elements = document.querySelectorAll(`${from} input.drag:checked`),
            li, newli;
        for(e of elements){
            e.checked = false;
            li = e.parentElement;
            newli = li.cloneNode(true);
            newli.setAttribute("aria-grabbed", "false");
            document.querySelector(`${from}`).removeChild(li);
            document.querySelector(`${to}`).appendChild(newli);
        }
    }
    function remove(to, from){
        var elements = document.querySelectorAll(`${to} input.drag:checked`),
            li, newli;
        for(e of elements){
            e.checked = false;
            li = e.parentElement;
            newli = li.cloneNode(true);
            newli.setAttribute("aria-grabbed", "false");
            document.querySelector(`${to}`).removeChild(li);
            document.querySelector(`${from}`).insertBefore(newli, document.querySelector(`${from}`).firstChild);
        }
    }
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

</script>

<script>
    $(document).ready(function(){
                 $(function() {
        $('.chosen-select').chosen();
        $('.chosen-select-deselect').chosen({ allow_single_deselect: true });
      });
       $('#selectAll').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green'
        });
    $("body").on("ifToggled", function(e){
          
            if(e.target.checked && e.target.id === 'selectAll'){
                $('#from li input[type="checkbox"].drag').each(function(i, e){
                    e.checked = true;
                    e.parentElement.setAttribute('aria-grabbed', true);
                });
            }else{
                $('#from li input[type="checkbox"].drag').each(function(i, e){
                    e.checked = false;
                    e.parentElement.setAttribute('aria-grabbed', false);
                });
            }
        });

        $("#guardar").prop( "disabled", true );
    });
</script>





