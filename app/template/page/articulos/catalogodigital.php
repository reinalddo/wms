<?php

include_once  $_SERVER['DOCUMENT_ROOT']."/app/host.php";
include ("barcode.php");
$listaAlm = new \AlmacenP\AlmacenP();
$listaGrup = new \GrupoArticulos\GrupoArticulos();
$listaMed = new \UnidadesMedida\UnidadesMedida();
$listaProv = new \Proveedores\Proveedores();
$listaTipcaja = new \TipoCaja\TipoCaja();
$listaSubgp = new \SubGrupoArticulos\SubGrupoArticulos();
$listaSubsub = new \SSubGrupoArticulos\SSubGrupoArticulos();
$almacenes = new \AlmacenP\AlmacenP();

$vere = \db()->prepare("SELECT * FROM t_profiles AS a WHERE id_menu=25 and id_submenu=57 and id_role='".$_SESSION["perfil_usuario"]."'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);

$agre = \db()->prepare("SELECT * FROM t_profiles AS a WHERE id_menu=25 and id_submenu=58 and id_role='".$_SESSION["perfil_usuario"]."'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);

$edita = \db()->prepare("SELECT * FROM t_profiles AS a WHERE id_menu=25 and id_submenu=59 and id_role='".$_SESSION["perfil_usuario"]."'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);

$borra = \db()->prepare("SELECT * FROM t_profiles AS a WHERE id_menu=25 and id_submenu=60 and id_role='".$_SESSION["perfil_usuario"]."'");
$borra->execute();
$borrar = $borra->fetchAll(PDO::FETCH_ASSOC);

$tipo_caracteristicas = \db()->prepare("SELECT c.Id_Carac, c.Cve_Carac, t.TipoCar_Desc, c.Des_Carac
                                        FROM c_tipo_car t, c_caracteristicas c
                                        WHERE t.Id_Tipo_car = c.Id_Tipo_car AND c.Activo = 1");
$tipo_caracteristicas->execute();
$tipo_caracteristicas = $tipo_caracteristicas->fetchAll(PDO::FETCH_ASSOC);

?>
    <link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
    <link href="/css/plugins/blueimp/css/blueimp-gallery.min.css" rel="stylesheet">
    <link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
    <link href="/css/plugins/imgloader/fileinput.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Mainly scripts -->
    <script src="/js/jquery-2.1.1.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">

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
    <script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
    <!-- Select -->
    <script src="/js/plugins/chosen/chosen.jquery.js"></script>

    <script src="/js/plugins/imgloader/fileinput.min.js"></script>
    <script src="/js/plugins/imgloader/locales/es.js"></script>
    <!-- Jquery Validate -->
    <script src="/js/plugins/validate/jquery.validate.min.js"></script>
    <script src="/js/plugins/blueimp/jquery.blueimp-gallery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>

    <style>
        #list {
            width: 100%;
            height: 100%;
            position: relative;
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
        
        .ui-jqgrid-bdiv {
            height: 100%;
            width: 100% !important;
            max-width: 100% !important;
        }

        .select2 /*, .select2-container, .select2-container--default, .select2-container--below*/
        {
            width: 100% !important;
        }
    </style>

    <input type="hidden" id="ruta_barcode" value="<?php echo "/app/template/page/articulos/"; ?>">

    <div class="wrapper wrapper-content  animated fadeInRight" id="arti">
        <h3>Artículos*</h3>
        <div class="row">
            <div class="col-md-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Almacén</label>
                                    <select class="form-control" id="almacenes" name="almacenes">
                                        <!--<option value="">Seleccione un Almacen</option>-->
                                        <?php foreach( $almacenes->getAll() AS $a ): ?>
                                            <option value="<?php echo $a->id; ?>" <?php if($a->clave==$_GET["almacen"]) echo "selected";?>><?php echo "($a->clave) $a->nombre"; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Grupo de Artículo</label>
                                    <select class="chosen-select form-control" id="grupo_b" name="grupo_b">
                                        <option value="">Seleccione un grupo</option>
                                        <?php foreach( $listaGrup->getAll() AS $p ): ?>
                                            <option value="<?php echo $p->cve_gpoart; ?>"><?php echo $p->des_gpoart; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Clasificación de Artículo</label>
                                    <select class="chosen-select form-control" id="clasificacion_b" name="clasificacion_b">
                                        <option value="">Clasificación de Artículo</option>
                                        <?php foreach( $listaSubgp->getAll() AS $p ): ?>
                                            <option value="<?php echo $p->cve_sgpoart; ?>"><?php echo $p->des_sgpoart; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tipo de Artículo</label>
                                    <select class="chosen-select form-control" id="tipo_b" name="tipo_b">
                                        <option value="">Tipo de Artículo</option>
                                        <?php foreach( $listaSubsub->getAll() AS $p ): ?>
                                            <option value="<?php echo $p->cve_ssgpoart; ?>"><?php echo $p->des_ssgpoart; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row" class="col-md-12">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Artículo Compuesto</label>
                                    <select name="tipo_c" id="tipo_c" class="chosen-select form-control">
                                        <option value="">Seleccione una opcion</option>
                                        <option value="S">Si</option>
                                        <option value="N">No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">  
                                <div class="form-group">
                                    <label>Proveedor</label> <!--lilo-->
                                    <select class="chosen-select form-control" id="proveedor" name="proveedor">
                                        <option value="">Seleccione un Proveedor</option>  
                                        <?php foreach( $listaProv->getAll() AS $p ): ?>    
                                            <option value="<?php echo $p->ID_Proveedor; ?>"><?php echo $p->Nombre; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <label>&nbsp; </label>
                                    <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                    <div class="input-group-btn">
                                        <button onclick="ReloadGrid()" style="margin-top: 22px" type="submit" class="btn btn-primary" id="buscarA">
                                            <span class="fa fa-search"></span>  Buscar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php 
                            /*
                            ?>
                            <div class="col-md-6">
                                <a href="/api/v2/articulos/exportar" target="_blank" class="btn btn-primary pull-right" style="margin-left:15px; margin-top: 22px;"><span class="fa fa-upload"></span> Exportar</a>
                                <button class="btn btn-primary pull-right" style="margin-left:15px; ; margin-top: 22px" data-toggle="modal" data-target="#importar"><span class="fa fa-download"></span> Importar</button>
                                <button class="btn btn-primary pull-right" type="button" id="inactivos" style="margin-top: 22px;  margin-left:10px; "><i class="fa fa-search"></i> Artículos inactivos</button>
                                <?php if($ag[0]['Activo']==1){?>
                                    <button onclick="agregar()" class="btn btn-primary pull-right"  type="button" style="margin-top: 22px; margin-left:10px; ">
                                        <i class="fa fa-plus"></i> Nuevo
                                    </button>
                                <?php }?>
                                <?php if(isSCTP()): ?>
                                    <div style="display: flex; flex-direction: row; justify-content: flex-end; width: 100%; margin-top: 30px">
                                        <button class="btn btn-primary" onclick="obtenerProductosSCTP()">Obtener Artículos de Rutas SCTP</button>
                                    </div>
                                <?php endif; ?>
                                <?php if(isLaCentral()): ?>
                                    <div style="display: flex; flex-direction: row; justify-content: flex-end; width: 100%; margin-top: 30px">
                                        <button class="btn btn-primary" onclick="obtenerProductosLaCentral()">Obtener Artículos de Rutas de La Central</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php 
                            */
                            ?>
                        </div>
                    </div>
                    <?php 
                    /*
                    ?>
                    <div class="ibox-content">
                        <div class="jqGrid_wrapper">
                            <table id="grid-table"></table>
                            <div id="grid-pager"></div>
                        </div>
                    </div>
                    <?php 
                    */
                    ?>

<style>
    .fila_producto
    {
        text-align: center;
        margin-bottom: 20px;
    }

    .fila_producto .caja
    {
        margin: 10px;
        border: 1px solid #aaa;
        border-radius: 5px;
    }
    .fila_producto .foto
    {
        width: 100%;
        border-top-left-radius: 5px;
        border-top-right-radius: 5px;
    }

    .fila_producto .clave_articulo
    {
        font-size: 13px;
        font-weight: bold;
        text-align: center;
        margin: 10px 0;
    }

    .fila_producto .articulo
    {
        font-size: 17px;
        text-align: left;
        font-weight: bold;
        margin: 10px;
    }

    .fila_producto .des_articulo
    {
        font-size: 15px;
        text-align: left;
        margin: 10px;
    }

    .fila_producto .precio_articulo
    {
        font-size: 15px;
        text-align: left;
        margin: 10px;
        position: relative;
    }

    .fila_producto .precio_articulo .pr_titulo
    {
        font-size: 14px;
        text-align: left;
        font-weight: bold;
        margin: 20px 0;
    }

    .fila_producto .precio_articulo .pr_precio
    {
        font-size: 15px;
        text-align: right;
        margin: 20px 0;
        color: #36a9e1 !important;
        font-weight: bold;
        position: absolute;
        right: 10px;
        top: -20px;
    }

</style>

                    <div class="ibox-content">

                        <?php //include("api/kreportes/reports/barcode/products/Products.view.php"); ?>

                    </div>


                </div>
            </div>
        </div>
    </div>

<script type="text/javascript">
    // initialize with defaults
    $("#input-2").fileinput({
        language: 'es',
        maxFileCount: 5,
        allowedFileExtensions: ["jpg", "png"]
    });

    /**
     * @author Ricardo Delgado.
     * Busca y selecciona el almacen predeterminado para el usuario.
     */
    function almacenPrede() 
    {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                idUser: '<?php echo $_SESSION["id_user"]?>',
                action: 'search_almacen_pre'
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) 
                {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/almacenPredeterminado/index.php',
            success: function(data) {
                if (data.success == true) 
                {
                    document.getElementById('almacenes').value = data.codigo.id;
                    setTimeout(function() {
                        //ReloadGrid();
                    }, 1500);
                }
            },
            error: function(res) {
                window.console.log(res);
            }
        });
    }

    function ReloadGrid()
    {
        $(".ibox-content").empty();


        if(!$("#proveedor").val() && !$("#txtCriterio").val() && !$("#grupo_b").val() && !$("#clasificacion_b").val() && !$("#tipo_b").val() && !$("#tipo_c").val())
            return;

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                proveedor: $("#proveedor").val(),//lilo
                criterio: $("#txtCriterio").val(),
                grupo: $("#grupo_b").val(),
                clasificacion: $("#clasificacion_b").val(),
                tipo: $("#tipo_b").val(),
                compuesto: $("#tipo_c").val(),
                action: "traer_catalogo"
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
            }, 
            url: '/api/articulos/lista/index.php',
            success: function(data) {
                //console.log("CATALOGO DIGITAL = ", data);
                //console.log("CATALOGO DIGITAL LENGHT = ", data.rows.length);

                var html = "", imagen = "", imagensrc = "", clave_articulo = "";

                //$(".ibox-content").empty();
            if(data != null)
            {
                for(var i = 0; i < data.rows.length; i++)
                {
                    html += "<div class='row fila_producto'>";

                    for(var j = 0; j < 3; j++)
                    {
                        clave_articulo = data.rows[i].cell[2];
                        //console.log("I = ", i, "CLAVE = ", clave_articulo, " | ARTÍCULO = ", data.rows[i].cell[6], " | DESCRIPCION = ", data.rows[i].cell[22], " | PRECIO = ", data.rows[i].cell[11], " | IMAGEN = ", data.rows[i].cell[21]);

                        imagen = data.rows[i].cell[22];
                        imagensrc = '../img/articulo/'+imagen;

                        $.ajax({
                            url: imagensrc,
                            async: false,
                            type:'HEAD',
                            error: function()
                            {
                                //file not exists
                                console.log("NO IMAGE");
                                imagen = "noimage.jpg";
                            },
                            success: function()
                            {
                                //file exists
                                console.log("OK IMAGE");
                            }
                        });

                        if(!imagen) imagen = "noimage.jpg";
                        html += "<div class='col-md-4'>";
                            html += "<div class='caja'>";
                            html += "<img class='card-img-top foto' src='../img/articulo/"+imagen+"' alt='"+imagen+"'>";
                            //html += "<div class='clave_articulo'>"+clave_articulo+"</div>";
                            //html += "<div class='clave_articulo'>"+'<img alt="'+clave_articulo+'" src="/app/template/page/articulos/barcode.php?codetype=Code128&size=20&text='+clave_articulo+'&print=true"/>'+"</div>";
                            html += "<div class='clave_articulo'>"+data.rows[i].cell[25]+"</div>";
                            html += "<div class='articulo'>"+data.rows[i].cell[2]+"</div>";
                            html += "<div class='des_articulo'>"+data.rows[i].cell[24]+"</div>";
                            html += "<div class='precio_articulo'><span class='pr_titulo'>Precio: </span> <span class='pr_precio'>$"+data.rows[i].cell[12]+"</span> </div>";
                            html += "</div>";
                        html += "</div>";
                        if((i+1) < data.rows.length) i++;
                        else break;
                    }
                    html += "</div>";
                }

                //console.log(html);
                $(".ibox-content").append(html);

            }
            else
                swal("Búsqueda Vacía", "No hay datos disponibles para la búsqueda realizada", "warning");


            },
            error: function(data) {
                console.log("ERROR CATALOGO DIGITAL = ", data);
            }

        });
    }
</script>

<style>
    <?php if($edit[0]['Activo']==0) {?>
        .fa-edit {
            display: none;
        }
    <?php } ?>
    <?php if($borrar[0]['Activo']==0) { ?>
        .fa-eraser {
            display: none;
        }
    <?php } ?>
    </style>