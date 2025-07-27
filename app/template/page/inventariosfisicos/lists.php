<?php
$almacenes = new \AlmacenP\AlmacenP();
$model_almacen = $almacenes->getAll();
$usuarios = new \Usuarios\Usuarios();
$usuarios = $usuarios->getAll();


$mod=59;
$var1=179;
$var2=180;
$var3=181;
$var4=182;

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

$confSql = "SELECT DISTINCT IFNULL(Valor, '') as instancia FROM t_configuraciongeneral WHERE cve_conf = 'instancia'";
$res_conf = mysqli_query(\db2(), $confSql);
$row_conf = mysqli_fetch_assoc($res_conf);
$instancia_log = $row_conf['instancia'];

?>

<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/bootstrap.min.css" rel="stylesheet">
<link href="/font-awesome/css/font-awesome.css" rel="stylesheet">
<link href="/css/animate.css" rel="stylesheet">
<link href="/css/style.css" rel="stylesheet">
<link href="/css/plugins/clockpicker/clockpicker.css" rel="stylesheet">
<link href="/css/plugins/iCheck/custom.css" rel="stylesheet">


<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="/js/moment.js"></script>
<script src="/js/moment-with-locales.js"></script>

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

<!-- Sweet Alert -->
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
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
	#detalle_wrapper .ui-jqgrid-hdiv.ui-state-default.ui-corner-top, #detalle_wrapper #gview_grid-table3, #detalle_wrapper .ui-jqgrid.ui-widget.ui-widget-content.ui-corner-all, #detalle_wrapper .ui-jqgrid-pager.ui-state-default.ui-corner-bottom, #detalle_wrapper #grid-table3, #detalle_wrapper .ui-jqgrid-bdiv{
		max-width: 100% !important;
		width: 100% !important;
	}
	.tab .ui-state-default, .ui-widget-content, .ui-widget-header .ui-state-default  {
		width: 100% !important;
	}
	.tab .ui-widget-content {
		width: 100% !important;
	}


	#gview_grid-tablea3 > div > .ui-jqgrid-hbox{
		padding-right: 0px !important; 
	}

	#grid-tablea3{

		width: 100% !important;

	}

	#gbox_grid-table3{
		width: 100% !important;
	}

	div#gview_grid-table3{
		width: 100% !important;
	}


	#gview_grid-table3 .ui-widget-content{
		width: 100% !important;
	}

	div#gview_grid-table3 > .ui-jqgrid-hdiv{

		width: 100% !important;
	}

	#grid-pager3{
		width: 100% !important;
	}

	div#gview_grid-table3 > .ui-jqgrid-bdiv{
		width: 100% !important;
	}

	#grid-tablea3_subgrid{

		width: 5% !important;
		text-align: center;

	}

	#dragUbicaciones li{
		cursor: pointer;
	}
	.wi{
		width: 90% !important;
	}
	.relative{
		position: relative;
	}
	.relative .floating-button{
		position: absolute;
		right: 0;
		top: 50%;

	}
	[aria-grabbed="true"]
	{
		background: #1ab394 !important;
		color: #fff !important;
	}
	#detalle_wrapper .ui-jqgrid-hdiv.ui-state-default.ui-corner-top, #detalle_wrapper #gview_grid-table2, #detalle_wrapper .ui-jqgrid.ui-widget.ui-widget-content.ui-corner-all, #detalle_wrapper .ui-jqgrid-pager.ui-state-default.ui-corner-bottom, #detalle_wrapper #grid-table2, #detalle_wrapper .ui-jqgrid-bdiv{
		max-width: 100% !important;
		width: 100% !important;
	}
</style>

<!-- Mainly scripts -->
<!-- Barra de nuevo y busqueda -->
<div class="wrapper wrapper-content  animated fadeInRight">
	<h3>Inventario Físico</h3>
	<div class="row">
		<div class="col-lg-12">
			<div class="tabs-container">
				<ul class="nav nav-tabs">
					<li class="active"><a data-toggle="tab" href="#tab-1"> Programación de Inventario Físico</a></li>
					<!--<li class=""><a data-toggle="tab" href="#tab-2">Administración de Inventario Físico</a></li>-->
				</ul>
				<div class="tab-content">
					<div id="tab-1" class="tab-pane active">
						<div class="panel-body">
							<div class="ibox">
								<div class="row">
									<div class="col-md-3">
										<div class="form-group">
											<label>Fecha</label>
											<div class="input-group date" id="data_1">
												<span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="Fecha" type="text" class="form-control" required>
											</div>
										</div>
									</div>
									<div class="col-md-3">
										<div class="form-group">
											<label for="email">Almacén</label>
											<select name="almacen" id="almacen" onchange="almacen()" class="chosen-select form-control">
												<option value="">Seleccione Almacén</option>
												<?php foreach( $model_almacen AS $almacen ): ?>
												<?php if($almacen->Activo == 1):?>
												<option value="<?php echo $almacen->clave."|".$almacen->id; ?>"><?php echo "($almacen->clave) ". $almacen->nombre; ?></option>
												<?php endif; ?>
												<?php endforeach; ?>
											</select>
										</div>
									</div>
									<div class="col-md-3">
										<div class="form-group">
											<label for="email">Zona de Almacenaje</label>
											<select name="zona" id="zona" class="chosen-select form-control">

											</select>
										</div>
									</div>
									<div class="col-md-3">
										<div class="form-group">
											<label for="rack">BL</label>
											<select name="rack" id="rack" onchange="cargar_productos()" class="chosen-select form-control">
											</select>
										</div>
									</div>

									<div class="col-md-2">
										<div class="form-group">
											<label for="pasillo">Pasillo</label>
											<select name="pasillo" id="pasillo" onchange="cargar_productos()" class="chosen-select form-control">
											</select>
										</div>
									</div>

									<div class="col-md-2">
										<div class="form-group">
											<label for="rackk">Rack</label>
											<select name="rackk" id="rackk" onchange="cargar_productos()" class="chosen-select form-control">
											</select>
										</div>
									</div>

									<div class="col-md-2">
										<div class="form-group">
											<label for="nivel">Nivel</label>
											<select name="nivel" id="nivel" onchange="cargar_productos()" class="chosen-select form-control">
											</select>
										</div>
									</div>

									<div class="col-md-2">
										<div class="form-group">
											<label for="seccion">Sección</label>
											<select name="seccion" id="seccion" onchange="cargar_productos()" class="chosen-select form-control">
											</select>
										</div>
									</div>

									<div class="col-md-2">
										<div class="form-group">
											<label for="ubicacion">Ubicación</label>
											<select name="ubicacion" id="ubicacion" onchange="cargar_productos()" class="chosen-select form-control">
											</select>
										</div>
									</div>

				                  	<div class="col-md-3">
										<div class="form-group">
											<label for="email">Producto</label>
											<select name="producto" id="productos" class="chosen-select form-control">
											</select>
										</div>
									</div>
									<div class="col-md-3">
										<div class="form-group">
                      					<label for="email"></label><br><br>
											<input type="checkbox" name="recepcion" id="recepcion" class="i-checks" disabled>
											<label style="margin-left: 10px;">Áreas de Recepción</label>
										</div>
									</div>
								</div>
								<?php 
								//echo "::::::::::::::".$instancia_log;
								//if($instancia_log == 'dicoisa' || $instancia_log == 'dev' || $instancia_log == 'foam')
								if($instancia_log != 'asl')
								{
								?>
								<div class="row">
									<div class="col-md-12 text-center">
										<div class="form-group">
											<button id ="cargarUbicaciones" class="btn btn-primary" type="button">Cargar Ubicaciones</button>
                      <button id ="cargarInventarioTotal" class="btn btn-primary" type="button">Cargar Inventario Total</button>
										</div>
									</div>
								</div>
								<?php 
								}
								?>
								<div class="ibox-content dragdrop">
									<div class="form-group">
										<div class="col-sm-12" style="margin-bottom: 30px">
											<input type="checkbox" id="selectAll" >
											<label for="selectAll">Seleccionar Todo</label>
										</div>
									</div>
									<div class="form-group" id="dragUbicaciones">
										<div class="col-md-6 relative">
											<label id ="title_ubicaciones_dispo" for="email">Ubicaciones Disponibles</label>
											<ol data-draggable="target" id="fromU" class="wi">
											</ol>
											<button class="btn btn-primary floating-button" id="button_añadir" onclick="add('#fromU', '#toU')">>></button>
											<button class="btn btn-primary floating-button" id="button_remover" onclick="remove('#toU', '#fromU')" style="margin-top: 40px"><<</button>
										</div>
										<div class="col-md-6">
											<label for="email">Ubicaciones Asignadas</label>
											<ol data-draggable="target" id="toU" class="wi">
											</ol>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<button id ="guardar" class="btn btn-primary pull-right permiso_registrar" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Planificar Inventario</button>
									</div>


								</div>

		                        <div class="row" id="planificar_inventario" style="text-align: center;padding: 15px;font-size: 16px;top: 15px;position: relative; display: none;">
		                            <div style="width: 50px;height: 50px;background-image: url(https://dev.assistpro-adl.com/img/load.gif);background-size: 100%;background-position: center;background-repeat: no-repeat;display: inline-flex;"></div><br>
		                            <span>Planificando Inventario, Espere por favor...</span>
		                        </div>

							</div>
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
                    document.getElementById('almacen').value = data.codigo.clave+'|'+data.codigo.id;
                    almacen();
                }
            },
            error: function(res){
                window.console.log(res);
            }
        });
    }
    almacenPrede();

	$('#data_1').datetimepicker({
		locale: 'es',
		format: 'DD-MM-YYYY',
		useCurrent: false,
		date: new Date()
	});

	$('#data_1').data("DateTimePicker").minDate(moment().add(-1, 'days'));

	$(document).on("click", "#guardar", function(){
//*********************************************************************************************
//*********************************************************************************************
//*********************** VERIFICAR SI HAY INVENTARIOS ****************************************
//*********************************************************************************************
//*********************************************************************************************
			$.ajax({
				type: "POST",
				dataType: "json",
				url: '/api/inventariosfisicos/update/index.php',
				data:
			        {
          			action : "VerificarInventariosAbiertos"
				},
				beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
				success: function(data) 
    	        {
/*
    	        	if(data.abiertos > 0)
    	        	{
			          swal({
				          		title: 'No puede Planificar Inventario',
				          		text: 'Tiene Inventarios Pendientes por Cerrar en Administración',
				          		type: 'error'
			          		});
			      		return;
			      	}
*/
			      	//else 
			      	//{else123
//*********************************************************************************************
//*********************************************************************************************

		var ubicaciones = [];
		var usuarios = [];
		$("#toU").each(function() {
			var localRels = [];
			$(this).find('li').each(function(){
				localRels.push( $(this).attr('value') + " | " + $(this).attr('area') );
			});
      if(localRels.length > 0 )
      {
			  ubicaciones.push(localRels);
      }
		});
/*
			console.log("**************************************************");
			console.log("Valores Enviados");
			console.log("**************************************************");
    		console.log("ubicaciones = ", ubicaciones[0]);
    		console.log("ubicaciones.length = ", ubicaciones[0].length);
    		console.log("ubicaciones* = ", ubicaciones[0][0]);
    		console.log("ubicaciones.length* = ", ubicaciones[0][0].length);
    		console.log("ubicaciones split0 = ", ubicaciones[0][0].split("|")[0], "ubicaciones split1 = ", ubicaciones[0][0].split("|")[1]);
    		console.log("usuarios = ", usuarios);
			console.log("almacen = ", $('#almacen').val().split("|")[0]);
			console.log("zona = ", $('#zona').val());
			console.log("fecha = ", $('#Fecha').val());
			console.log("**************************************************");
			console.log("pasillo = ", $('#pasillo').val());
			console.log("rackk = ", $('#rackk').val());
			console.log("nivel = ", $('#nivel').val());
			console.log("seccion = ", $('#seccion').val());
			console.log("ubicacion = ", $('#ubicacion').val());
			console.log("productos = ", $('#productos').val());
			console.log("cuadro_bl_vacio =", ubicaciones[0][0].split("|")[0].trim());
			console.log("**************************************************");
*/

			//return;
			var ubicaciones_vacias = "";
			if(ubicaciones[0] != null)
				ubicaciones_vacias = ubicaciones[0][0];

    		if (/*ubicaciones.length === 0*/ ubicaciones_vacias.split("|")[0].trim() == "" && $("#pasillo").val() == "" && $("#rackk").val() == "" && $("#nivel").val() == "" && $("#seccion").val() == "" && $("#ubicacion").val() == "" && $("#zona").val() == "" /*&& $("#productos").val() == ""*/){
			swal({
				title: "Error",
				//text: "Es necesario cargar las ubicaciones",
				text: "Debe Cargar las Ubicaciones con los filtros seleccionados o solo seleccionar los filtros que se necesitan y Planificar",
				type: "error"
			});
		}
    else  if ($('#almacen').val() === "" ){
			swal({
				title: "Error",
				text: "Es necesario colocar un almacen",
				type: "error"
			});
		}
    else if (($('#Fecha').val() ==="")){
			swal({
				title: "Error",
				text: "Es necesario colocar una fecha",
				type: "error"
			});
		}
		else
    {

    	$("#planificar_inventario").show();

			$.ajax({
				type: "POST",
				dataType: "json",
				url: '/api/inventariosfisicos/update/index.php',
				data:
        {
          action : "guardarInventario",
          			cuadro_bl_vacio: ubicaciones_vacias.split("|")[0].trim(),
					pasillo_inv: $("#pasillo").val(),
					rackk_inv: $("#rackk").val(),
					nivel_inv: $("#nivel").val(),
					seccion_inv: $("#seccion").val(),
					ubicacion_inv: $("#ubicacion").val(),
					productos_inv: $("#productos").val(),
					almacen : $('#almacen').val().split("|")[0],
					zona: $('#zona').val(),
					fecha: $('#Fecha').val(),
					ubicaciones: ubicaciones,
				},
				beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
				success: function(data) 
    	        {
    	        	console.log(data);
		          swal({title: data.title,text: data.txt,type: data.success});
		          if(data.success=="success")
		          {
		          	$("#planificar_inventario").hide();
		            $("#button_remover").show();
		            $("#button_añadir").show();
		            $("#fromU").show();
		            $("#title_ubicaciones_dispo").show();
		            
		            $("#selectAll").iCheck('uncheck')
		            $("#recepcion").iCheck('uncheck')
		            $("#fromU .itemlist").remove();
		            $("#toU .itemlist").remove();
		            $('#almacen').val("");
		            $('#zona').val("");
		            $('#rack').val("");
		            $('#productos').val("");
		            $("#Fecha").val(moment().format("DD-MM-YYYY"));
		            $('.chosen-select').trigger("chosen:updated");
		            almacenPrede();
		            //reload();
		          }
        		},
        		error: function(data)
        		{
        			console.log("ERROR 1", data);
        		}
      });

		}
//*********************************************************************************************
//*********************************************************************************************
			      	//}else123
        		},
        		error: function(data)
        		{
        			console.log("ERROR 2", data);
        		}
      });
//*********************************************************************************************
//*********************************************************************************************
//*********************************************************************************************

	});
  
  $("#cargarInventarioTotal").on("click", function()
  {
    $('#zona').val("");
    $('#zona').trigger("chosen:updated");
    $('#rack').val("");
    $('#rack').trigger("chosen:updated");
    if($("#almacen").val()!="")
    {
      $("#fromU .itemlist").remove();
      $("#toU .itemlist").remove();
	  var rack = "", rack0 = "", rack1 = "";
	  if($("#rack").val())
	  {
	      	rack = $("#rack").val().split("/");
	      	rack0 = rack[0];
	      	rack1 = rack[1];
	      	console.log("rack[0]", rack0);
	      	rack = rack0;
	  }
      $.ajax({
        type: "POST",
        dataType: "json",
        data: {
          zona : $("#zona").val(),
          rack: rack0,
          area: document.getElementById("recepcion").checked,
          almacen: $("#almacen").val().split("|")[1],
          producto:$("#productos").val(),
          conProducto: true,
          action : "traerUbicacionesDeZonas"
        },
        beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                    },
        url: '/api/almacen/update/index.php',
        success: function(data) {

          if (data.success == true) {
            var arr = $.map(data.ubicaciones, function(el) { return el; })
            arr.pop();
            for (var i=0; i<data.ubicaciones.length; i++)
            {
              var ul = document.getElementById("toU");
              var li = document.createElement("li");
              var checkbox = document.createElement("input");
              checkbox.style.marginRight = "10px";
              checkbox.setAttribute("type", "checkbox");
              checkbox.setAttribute("checked", true);
              checkbox.setAttribute("disabled", true);
              checkbox.setAttribute("value", data.ubicaciones[i].id_ubicacion);
              checkbox.setAttribute("class", "drag");
              checkbox.setAttribute("onclick", "selectParent(this)");
              li.appendChild(checkbox);
              li.appendChild(document.createTextNode(data.ubicaciones[i].ubicacion));
              li.setAttribute("dayta-draggable", "item");
              li.setAttribute("draggable", "false");
              li.setAttribute("aria-draggable", "false");
              li.setAttribute("aria-grabbed","false");
              li.setAttribute("tabindex","0");
              li.setAttribute("class","itemlist");
              li.setAttribute("onclick","selectChild(this)");
              li.setAttribute("value",data.ubicaciones[i].id_ubicacion);
              li.setAttribute("area", data.ubicaciones[i].area === "true")
              ul.appendChild(li);
            }
          }
        }
      });
      $("#button_remover").hide();
      $("#button_añadir").hide();
      $("#fromU").hide();
      $("#title_ubicaciones_dispo").hide();
    }
    else
    {
      $("#button_remover").show();
      $("#button_añadir").show();
      $("#fromU").show();
      swal("Error", "Selecione un Almacen", "error");
    }
  });
  
  
  
	$("#cargarUbicaciones").on("click", function()
  {
    $("#button_remover").show();
    $("#button_añadir").show();
    $("#fromU").show();
    $("#title_ubicaciones_dispo").show();
    /*
    if($("#zona").val() == "")
    {
      swal("Selecione Zona", "Selecione una Zona de almacenaje o Carge Inventario Total", "warning");
      $("#fromU>li").remove();
      $("#toU>li").remove();
      return;
    }
    */
    if($("#almacen").val()!="")
    {
      $("#fromU .itemlist").remove();
      $("#toU .itemlist").remove();

	  var rack = "", rack0 = "", rack1 = "";
	  if($("#rack").val())
	  {
	      	rack = $("#rack").val().split("/");
	      	rack0 = rack[0];
	      	rack1 = rack[1];
	      	console.log("rack[0]", rack0);
	      	rack = rack0;
	  }

console.log("$(#zona).val()", $("#zona").val());
console.log("document.getElementById(recepcion).checked", document.getElementById("recepcion").checked);
console.log("$(#almacen).val().split(|)[1]", $("#almacen").val().split("|")[1]);
console.log("$(#productos).val()", $("#productos").val());

      $.ajax({
        type: "POST",
        dataType: "json",
        data: {
          zona : $("#zona").val(),
          rack:rack0,

		  pasillo: $("#pasillo").val(),
		  rackk: $("#rackk").val(),
		  nivel: $("#nivel").val(),
		  seccion: $("#seccion").val(),
		  ubicacion: $("#ubicacion").val(),

          area: document.getElementById("recepcion").checked,
          almacen: $("#almacen").val().split("|")[1],
          producto:$("#productos").val(),
          conProducto: true,
          action : "traerUbicacionesDeZonas"
        },
        beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                    },
        url: '/api/almacen/update/index.php',
        success: function(data) {
        	console.log("SUCCESS", data);

          if (data.success == true) {
            var arr = $.map(data.ubicaciones, function(el) { return el; })
            arr.pop();
            for (var i=0; i<data.ubicaciones.length; i++)
            {
              var ul = document.getElementById("fromU");
              var li = document.createElement("li");
              var checkbox = document.createElement("input");
              checkbox.style.marginRight = "10px";
              checkbox.setAttribute("type", "checkbox");
              checkbox.setAttribute("value", data.ubicaciones[i].id_ubicacion);
              checkbox.setAttribute("class", "drag");
              checkbox.setAttribute("onclick", "selectParent(this)");
              li.appendChild(checkbox);
              li.appendChild(document.createTextNode(data.ubicaciones[i].ubicacion));
              li.setAttribute("dayta-draggable", "item");
              li.setAttribute("draggable", "false");
              li.setAttribute("aria-draggable", "false");
              li.setAttribute("aria-grabbed","false");
              li.setAttribute("tabindex","0");
              li.setAttribute("class","itemlist");
              li.setAttribute("onclick","selectChild(this)");
              li.setAttribute("value",data.ubicaciones[i].id_ubicacion);
              li.setAttribute("area", data.ubicaciones[i].area === "true")
              ul.appendChild(li);
            }
          }
        }, error: function(data){
        	console.log("ERROR", data);
        }
      })
    }
    else
    {
      swal("Error", "Selecione un Almacen", "error");
    }
	});
  
  function cargar_productos()
  {
	console.log("almacen = ", $('#almacen').val());
	console.log("zona = ", $('#zona').val());
	console.log("rack = ", $('#rack').val());
	console.log("pasillo = ", $('#pasillo').val());
	console.log("rackk = ", $('#rackk').val());
	console.log("nivel = ", $('#nivel').val());
	console.log("seccion = ", $('#seccion').val());
	console.log("ubicacion = ", $('#ubicacion').val());

    $.ajax({
			type: "POST",
			dataType: "json",
			data: {
        	almacen : $('#almacen').val(),
			zona : $('#zona').val(),
        	rack : $('#rack').val(),
        	pasillo : $('#pasillo').val(),
        	rackk : $('#rackk').val(),
        	nivel : $('#nivel').val(),
        	seccion : $('#seccion').val(),
        	ubicacion : $('#ubicacion').val(),
			action : "traerProductos"
			},
			beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
									},
			url: '/api/inventariosfisicos/update/index.php',
			success: function(data) {
				console.log("traerProductos = ", data);
				if (data.success == true) {
					var options = $("#productos");
					options.empty();
					options.append(new Option("Seleccione", ""));
					for (var i=0; i<data.productos.length; i++)
					{
						options.append(new Option("( "+data.productos[i][0]+" )" + " - " + data.productos[i][1], data.productos[i][0]));
					}
					$('.chosen-select').trigger("chosen:updated");
				}
			}
		});
    setBLAsignado();
  }
  
  function setBLAsignado()
  {
	  var rack = "", rack0 = "", rack1 = "";
	  if($("#rack").val())
	  {
	      	rack = $("#rack").val().split("/");
	      	rack0 = rack[0];
	      	rack1 = rack[1];
	      	console.log("rack[0]", rack0);
	      	rack = rack0;
	  }
    console.log("rack = ", $("#rack").val());
    console.log("rack = ", rack0);
    $("#fromU .itemlist").remove();
		$("#toU .itemlist").remove();
    var ul = document.getElementById("toU");
    var li = document.createElement("li");
    var checkbox = document.createElement("input");
    checkbox.style.marginRight = "10px";
    checkbox.setAttribute("type", "checkbox");
    checkbox.setAttribute("value", rack0);
    checkbox.setAttribute("class", "drag");
    checkbox.setAttribute("onclick", "selectParent(this)");
    li.appendChild(checkbox);
    li.appendChild(document.createTextNode(rack1));
    li.setAttribute("dayta-draggable", "item");
    li.setAttribute("draggable", "false");
    li.setAttribute("aria-draggable", "false");
    li.setAttribute("aria-grabbed","false");
    li.setAttribute("tabindex","0");
    li.setAttribute("class","itemlist");
    li.setAttribute("onclick","selectChild(this)");
    li.setAttribute("value",rack0);
    li.setAttribute("area", "true")
    ul.appendChild(li);
  }

	function almacen()
  {
		if($("#almacen").val() !== '')
    {
			$("#recepcion").iCheck('enable');
		}
		//$('#zona').find('option').remove().end();
    
    $('#zona').prop('selectedIndex',0);
    $('#rack').prop('selectedIndex',0);
    $('#productos').prop('selectedIndex',0);
    $("#recepcion").iCheck('uncheck')
    
		
		//$("#cargarUbicaciones").prop('disabled',true);
		console.log("ALMACEN = ", $('#almacen').val());
		var value = $('#almacen').val().split("|");
		var clave = value[0],
			id = value[1];

		$("#fromU .itemlist").remove();
		$("#toU .itemlist").remove();

		$.ajax({
			type: "POST",
			dataType: "json",
			data: {
				clave : clave,
				action : "traerZonasDeAlmacenP"
			},
			beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
									},
			url: '/api/almacenp/update/index.php',
			success: function(data) {
				if (data.success == true) {
					var options = $("#zona");
					options.empty();
					options.append(new Option("Seleccione", ""));
					for (var i=0; i<data.zonas.length; i++)
					{
						options.append(new Option(data.zonas[i].nombre_zona, data.zonas[i].clave_zona));
					}
					$('.chosen-select').trigger("chosen:updated");
				}
			}
		});
    cargar_productos();
    CargarBLs();
	}
  
	$("#pasillo, #rackk, #nivel, #seccion, #ubicacion").change(function(){
		CargarBLs(false);
	});

	function CargarBLs(reiniciar_bl_parts = true)
	{
		var id_almacen = $("#almacen").val();
		id_alm = id_almacen.split("|")[1].trim();

		$.ajax({
			type: "POST",
			dataType: "json",
			data: {
				zona : $('#zona').val(),
				pasillo: $("#pasillo").val(),
				rackk: $("#rackk").val(),
				nivel: $("#nivel").val(),
				seccion: $("#seccion").val(),
				ubicacion: $("#ubicacion").val(),
				id_almacen: id_alm,
				action : "traerRackDeZonas",
				conProducto: true
			},
			beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
									},
			url: '/api/almacen/update/index.php',
			success: function(data) {
			  	if (data.success == true) {
					console.log("SUCCESS", data);
					var arr_pasillo = [], arr_rackk = [], arr_nivel = [], arr_seccion = [], arr_ubicacion = [];
					var options = $("#rack"), options_pasillo = $("#pasillo"), options_rackk = $("#rackk"), 
					options_nivel = $("#nivel"), options_seccion = $("#seccion"), options_ubicacion = $("#ubicacion");
					options.empty();
					options.append(new Option("Seleccione", ""));
					if(reiniciar_bl_parts)
					{
						options_pasillo.empty();options_rackk.empty();
						options_nivel.empty();options_seccion.empty();options_ubicacion.empty();
						
						options_pasillo.append(new Option("Pasillo", ""));
						options_rackk.append(new Option("Rack", ""));
						options_nivel.append(new Option("Nivel", ""));
						options_seccion.append(new Option("Sección", ""));
						options_ubicacion.append(new Option("Ubicación", ""));
					}
					for (var i=0; i<data.racks.length; i++)
					{
						options.append(new Option(data.racks[i].rack, (data.racks[i].id+'/'+data.racks[i].rack)));

						if(reiniciar_bl_parts)
						{
							if($.inArray(data.racks[i].cve_pasillo, arr_pasillo) == -1 && data.racks[i].cve_pasillo != '')
							{
								arr_pasillo.push(data.racks[i].cve_pasillo);
								options_pasillo.append(new Option(data.racks[i].cve_pasillo, data.racks[i].cve_pasillo));
							}

							if($.inArray(data.racks[i].cve_rack, arr_rackk) == -1 && data.racks[i].cve_rack != '')
							{
								arr_rackk.push(data.racks[i].cve_rack);
								options_rackk.append(new Option(data.racks[i].cve_rack, data.racks[i].cve_rack));
							}

							if($.inArray(data.racks[i].cve_nivel, arr_nivel) == -1 && data.racks[i].cve_nivel != '')
							{
								arr_nivel.push(data.racks[i].cve_nivel);
								options_nivel.append(new Option(data.racks[i].cve_nivel, data.racks[i].cve_nivel));
							}

							if($.inArray(data.racks[i].Seccion, arr_seccion) == -1 && data.racks[i].Seccion != '')
							{
								arr_seccion.push(data.racks[i].Seccion);
								options_seccion.append(new Option(data.racks[i].Seccion, data.racks[i].Seccion));
							}

							if($.inArray(data.racks[i].Ubicacion, arr_ubicacion) == -1 && data.racks[i].Ubicacion != '')
							{
								arr_ubicacion.push(data.racks[i].Ubicacion);
								options_ubicacion.append(new Option(data.racks[i].Ubicacion, data.racks[i].Ubicacion));
							}
						}
					}
					$('.chosen-select').trigger("chosen:updated");
				}
			}, error: function(data){
				console.log("ERROR", data);
			}
		});
	}

	$('#zona').on('change', function() {
    
    if($('#zona').val() != "")
    {
      $("#button_remover").show();
      $("#button_añadir").show();
      $("#fromU").show();
      $("#title_ubicaciones_dispo").show();
      $("#fromU .itemlist").remove();
      $("#toU .itemlist").remove();
    }
/*
		if ($('#zona').val() == "") {
			$("#cargarUbicaciones").prop('disabled',true);
		} else {
			$("#cargarUbicaciones").prop('disabled',false);
		}
*/
		CargarBLs();

	});

</script>

<script>
	$(document).ready(function(){
		$(function() {
			$('.chosen-select').chosen();
			$('.chosen-select-deselect').chosen({ allow_single_deselect: true });
		});

		$('#recepcion, #selectAll').iCheck({
			checkboxClass: 'icheckbox_square-green',
			radioClass: 'iradio_square-green'
		});
		$("body").on("ifToggled", function(e){
			/*if(e.target.checked && e.target.id === 'recepcion'){
				$("#cargarUbicaciones").removeAttr("disabled");
			}else{
				if($("#zona").val() === ''){
					$("#cargarUbicaciones").attr("disabled", "disabled");
				}
			}*/
			if(e.target.checked && e.target.id === 'selectAll'){
				$('#fromU li input[type="checkbox"].drag').each(function(i, e){
					e.checked = true;
					e.parentElement.setAttribute('aria-grabbed', true);
				});
			}else{
				$('#fromU li input[type="checkbox"].drag').each(function(i, e){
					e.checked = false;
					e.parentElement.setAttribute('aria-grabbed', false);
				});
			}
		});
	});

	function add(from, to){
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