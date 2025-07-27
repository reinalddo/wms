<?php
    $listaZR = new \CortinaEntrada\CortinaEntrada();
    $listaProvee = new \Proveedores\Proveedores();
    $listaProyectos = new \Proyectos\Proyectos();
    $listaAlma = new \Almacen\Almacen();
    $listaUser = new \Usuarios\Usuarios();
    $listaOC = new \OrdenCompra\OrdenCompra();
    $listaAP = new \AlmacenP\AlmacenP();
    $listaLotes = new \Lotes\Lotes();
    $listaProto = new \Protocolos\Protocolos();


$id_almacen = $_SESSION['id_almacen'];
$pedidos_cross_sql = \db()->prepare("SELECT * FROM th_pedido WHERE cve_almac = $id_almacen AND status = 'A' AND Fol_folio LIKE 'XD%' AND IFNULL(Ship_Num, '') = ''");
$pedidos_cross_sql->execute();
$pedidos_cross = $pedidos_cross_sql->fetchAll(PDO::FETCH_ASSOC);

$confSql = \db()->prepare("SELECT IFNULL(Valor, '') AS Valor FROM t_configuraciongeneral WHERE cve_conf = 'instancia' LIMIT 1 ");
$confSql->execute();
$instancia = $confSql->fetch()['Valor'];

?>
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet" />
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-css/1.4.6/select2-bootstrap.min.css">
<link href="/css/plugins/clockpicker/clockpicker.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">

<input type="hidden" id="num_oc" value="<?php echo $listaOC->getMax_RL()->id + 1;?>">
<input type="hidden" id="ucaja_val" value="">
<input type="hidden" id="c_peso_val" value="">
<input type="hidden" id="cant_max" value="">
<div id="app_recibir_articulo">
    <div class="wrapper wrapper-content  animated fadeInRight" id="FORM">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <div class="row">
                            <div class="col-lg-4" id="_title">
                                <h3>Recepción de Materiales+</h3>
                            </div>
                        </div>
                    </div>
                    <form id="myform">
                        <div class="ibox-content">
                            <div class="row">
                                <label>Tipo </label>
                                <div class="col-lg-12 text-center">
                                    <div class="form-group">
                                        <label class="col-md-4 options_checkbox" for="checkboxes-0">
                                            <input type="radio" v-model="tipo_entrada" v-bind:value="1" v-on:click="reset_ordenes();">
                                            <label>Orden de Compra</label>
                                        </label>
                                        <label class="col-md-4 options_checkbox" for="checkboxes-1" >
                                            <input type="radio" v-model="tipo_entrada" v-bind:value="2" v-on:click="empty_oc();traer_articulos();traer_proveedores();">
                                            <label>Recepción Libre</label>
                                        </label>
                                        <label class="col-md-4 options_checkbox" for="checkboxes-2" >
                                            <input type="radio" v-model="tipo_entrada" v-bind:value="3" v-on:click="empty_oc();traer_articulos();traer_proveedores();">
                                            <label>Cross Docking</label>
                                        </label>
                                    </div><br>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6 b-r b-b">
                                    <div class="form-group" id="data_2" style="display:none">
                                        <label>Fecha de Recepción</label>
                                        <input disabled id="fechaentrada" type="text" class="form-control" value="">
                                        <input id="fechainicio" type="text" class="form-control" value="" style="display: none">
                                    </div>
                                  
                                    <div class="form-group">
                                        <label>Almacén</label>
                                        <select v-model="almacen_principal" class="form-control" id="almacen_select">
                                            <option value="">Seleccione</option>
                                            <option v-for="alm in almacenes" :value="alm.clave">[{{alm.clave}}] - {{alm.nombre}}</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Número de Orden de Compra*</label>
                                        <select class="chosen-list form-control" id="orden_de_compra_check" v-model="orden.id" v-on:change="set_orden(orden.id);set_protocolo();traer_crossdocking(orden.id)" :disabled="tipo_entrada == 2">
                                        <option value="" >Seleccione una OC</option>
                                        <option v-for="ord in ordenes" v-if="(ord.status == 'C' || ord.status == 'I') && ord.Cve_Almac == almacen_principal" :value="ord.num_pedimento">[{{ord.num_pedimento}}] / [{{ord.Factura}}] - {{ord.nombre_proveedor_procedimiento}}</option>
                                    </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Folio de Recepcion</label> 
                                        <input  type="text"  placeholder="Folio de Recepción" class="form-control" v-model="folio_recepcion" disabled>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group col-lg-6" style="display: none">
                                        <label>Hora Inicio</label>
                                        <div class="input-group clockpicker" disabled data-autoclose="true">
                                            <input type="text" disabled id="hora" class="form-control">
                                            <span class="input-group-addon" style="display: none;">
                                            <span class="fa fa-clock-o"></span>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group col-lg-6" style="display: none">
                                        <label>Hora Fin</label>
                                        <div class="input-group clockpicker" disabled data-autoclose="true">
                                            <input type="text" disabled id="horafin" class="form-control">
                                            <span class="input-group-addon" style="display: none;">
                                            <span class="fa fa-clock-o"></span>
                                            </span>
                                        </div>
                                    </div>


                                    <div class="form-group col-md-12" id="empresaf">
                                        <label>Empresa</label>
                                        <select class="form-control" id="empresa_proveedor">
                                            <option value="">Seleccione</option>
                                            <?php foreach( $listaProvee->getAll(" AND es_cliente = 1  AND es_transportista = 0 ") AS $a ): ?>
                                                <option value="<?php echo $a->cve_proveedor; ?>"><?php echo $a->Nombre; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" id="empresa_proveedor_hide" />
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Proveedor</label>
                                        <select class="form-control" v-model="proveedor.id" v-if="tipo_entrada != 1" v-on:change="set_proveedores(proveedor.id)" >
                                            <option value="">Seleccione</option>
                                            <option v-for="proveedor in proveedores" :value="proveedor.ID_Proveedor" 
                                            >{{proveedor.Nombre}}</option>
                                        </select>
                                        <div>
                                            <!--
                                            <select class="form-control" v-model="proveedor.id" v-if="tipo_entrada == 1" v-on:change="set_proveedores(proveedor.id)" :disabled="tipo_entrada == 1">
                                                <option v-for="proveedor in proveedores" :value="proveedor.ID_Proveedor" 
                                                >{{proveedor.Nombre}}</option>
                                            </select>
                                            -->
                                            <input type="text" v-if="tipo_entrada == 1" id="proveedor_procedimiento" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Factura | Remisión</label> 
                                        <input id="facprove" type="text" placeholder="Factura" class="form-control" >
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Proyectos</label> 
                                        <select class="form-control" id="claveproyecto">
                                            <option value="">Seleccione</option>
                                            <?php foreach( $listaProyectos->getAllProyectos($_SESSION['id_almacen']) AS $a ): ?>
                                                <option value="<?php echo $a->Cve_Proyecto; ?>"><?php echo $a->Des_Proyecto; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>



                                    <div class="form-group col-md-6" id="protocolof" v-on:change="set_protocolo()">
                                        <label>Protocolo</label>
                                        <select class="form-control" id="protocolo">
                                            <?php /* ?>
                                            <option value="">Seleccione</option>
                                            <?php foreach( $listaProto->getAll() AS $a ): ?>
                                                <option <?php if($a->descripcion == 'Nacional') echo "selected"; ?> value="<?php echo $a->id; ?>"><?php echo $a->descripcion; ?></option>
                                            <?php endforeach; */?>
                                        <option value="">Seleccione</option>
                                        <?php foreach( $listaProto->getAll() AS $a ): ?>
                                            <?php 
                                            $selected = "";
                                            $c = strtolower($a->descripcion);
                                            $c_a = explode(" ", $c);
                                            if(in_array("nacional", $c_a))
                                                $selected = "selected";
                                            ?>
                                        <option <?php echo $selected; ?> value="<?php echo $a->ID_Protocolo; ?>"><?php echo $a->descripcion; ?></option>
                                        <?php endforeach; ?>

                                        </select>
                                        <input type="hidden" id="protocolooc" />
                                    </div>
                                    <div class="form-group col-md-6" id="cprotocolof" >
                                        <label>Consec Protocolo</label>
                                        <input id="cprotocolo" type="text" disabled placeholder="Consec Protocolo" class="form-control">
                                        <input type="hidden" id="cprotocolooc" />
                                    </div>

                                    <div class="form-group col-md-6" id="pedidos_cross_div" v-if="tipo_entrada == 3">
                                        <label>Pedidos CrossDocking</label>
                                        <select class="form-control" id="pedidos_cross" v-model="crossd.id">
                                            <option value="">Seleccione</option>
                                        <option v-for="cross in crossdocking" :value="cross.Fol_folio">{{cross.Fol_folio}}</option>

                                            <?php /* foreach( $pedidos_cross AS $a ): ?>
                                                <option value="<?php echo $a['Fol_folio']; ?>"><?php echo $a['Fol_folio']; ?></option>
                                            <?php endforeach; */ ?>
                                        </select>
                                        <input type="hidden" id="protocolooc" />
                                    </div>


                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6 b-t">
                                    <?php 
                                    
                                    ?>
                                    <div class="form-group">
                                        <label>Zona de Recepción*</label>
                                       <select v-model="zona.cve_ubicacion" class="form-control" v-on:change="set_zona(zona.cve_ubicacion)" required="true">
                                            <option value="" >Seleccione una Zona de Recepcion</option>
                                            <option v-for="zona in zonas" :value="zona.cve_ubicacion">{{zona.cve_ubicacion}} -{{zona.desc_ubicacion}}</option> 
                                        </select>
                                    </div>
                                    <?php 
                                    /*
                                    ?>
                                    <div class="form-group">
                                        <label>Zona de Recepción*</label>
                                        <select class="form-control" id="zona_recepcion_select">
                                            <option value="" >Seleccione una Zona de Recepcion</option>
                                        </select>
                                    </div>
                                    <?php 
                                    */
                                    ?>
                                    <div class="form-group">
                                        <label>Pallet/Contenedor</label>
                                        <select v-model="con.clave_contenedor" class="form-control chosen-select" id="pallet_list" v-on:change="set_con(con.clave_contenedor);"<?php //con.clave_lp=''; ?>>
                                            <option value="" >Seleccione un Pallet/Contenedor</option>
                                            <option v-for="con in contenedores" :value="con.clave_contenedor">[{{con.Generico}}][{{con.tipo}}][{{con.clave_contenedor}}] - {{con.descripcion}}</option> 
                                        </select>
                                    </div>
                                    <div id="pallet_contenedor" style="display: none;">
                                    <label for="cerrar_pallet">
                                    <input type="checkbox" name="cerrar_pallet" id="cerrar_pallet" v-on:change="active_pallet_gen()" value="0"> Cerrar Pallet Genérico
                                    <br><br>
                                    </label>
                                    </div>
                                    

                                    <?php 
                                    if($instancia == 'asl')
                                    {
                                    ?>
                                    <div class="form-group">
                                        <label>License Plate</label>
                                        <select v-model="pat.clave_lp" class="form-control" v-on:change="set_lp(pat.clave_lp)">
                                            <option value="" >Seleccione un License Plate</option>
                                            <option v-for="pat in license_plates" :value="pat.CveLP">{{pat.descripcion}}|{{pat.CveLP}}</option> 
                                        </select>
                                    </div>
                                    <?php 
                                    }
                                    ?>
                                    <div class="form-group">
                                        <label>Articulo*</label>
                                        <select class="form-control" v-model="articulo.cve_articulo" v-on:change="set_articulo(articulo.cve_articulo)" v-if="tipo_entrada != 2" :searchable="true" :filterable="true" :clearable="true" id="articulos_lista">
                                            <option value="" >Seleccione un Artículo</option>
                                            <option v-for="articulo in articulos" :value="articulo.val_articulo">[ {{articulo.cve_articulo}} ] - {{articulo.nombre_articulo_select}}</option>
                                        </select>
                                        <div>

                                        <?php 
                                        ?>
                                        <div>
                                        <input type="text" class="form-control" name="s_articulo" id="s_articulo" placeholder="Buscar Artículo..." v-if="tipo_entrada == 2" v-on:keyup="traer_articulos()">
                                        </div>
                                        <br>
                                        <?php 
                                        ?>
                                        <select class="form-control chosen-select" v-model="articulo.cve_articulo" v-on:change="set_articulo(articulo.cve_articulo)"  v-if="tipo_entrada == 2" id="articulos_lista2">
                                            <option value="" >Seleccione un articulo</option>
                                            <option v-for="articulo in articulos_todos" :value="articulo.cve_articulo">[ {{articulo.cve_articulo}} ] - {{articulo.des_articulo}}</option>
                                        </select>
                                        </div>
                                    </div>
                                    <div class="form-group" id="cantidadRecibidaOC">
                                        <?php 
                                        /*
                                        ?>
                                        <label id="label-cant-max">Cantidad Recibida* MAX(<span id="cant_recOC"></span>)</label>
                                        <input onkeyup="calcular_costo()" type="number" step="0.01" placeholder="Cantidad Recibida" class="form-control" >
                                        <?php 
                                        */
                                        ?>
                                        <label v-if="articulo.cantidad_pedida ==''" id="label-cant-max">Cantidad Recibida* {{articulo.cantidad_pedida}}</label>
                                        <label v-else id="label-cant-max">Cantidad a Recibir* MAX({{articulo.cantidad_pedida}})</label>
                                        <input type="hidden" id="cantidad_max" :value="articulo.cantidad_pedida">
                                        <input id="a_recibir" v-on:keyup="calcular_costo()" v-model="articulo.cantidad_por_recibir" :disabled="(articulo.cve_articulo == '' || articulo.maneja_series == 1)" oninput="parseFloat($(this).val()).toFixed(2)"  type="number" step="0.01" placeholder="Cantidad Recibida" class="form-control" >
                                        <?php 
                                        
                                        ?>
                                        <br v-if="tipo_protocolo==1">
                                        <label v-if="tipo_protocolo==1">Número Pedimento</label>
                                        <input v-if="tipo_protocolo==1" id="num_pedimento_inter" type="text" class="form-control">
                                        <br v-if="tipo_protocolo==1">
                                        <label v-if="tipo_protocolo==1">Fecha Pedimento</label>
                                        <input v-if="tipo_protocolo==1" id="fecha_pedimento_inter" type="date" class="form-control">
                                    </div>
                                    <?php 
                                    /*
                                    ?>
                                    <div class="form-group" id="nlotef" style="display: none;">
                                        <label>Lote*</label> 
                                        <input v-on:keyup="revisar_lote_existente(this)" type="text" placeholder="Lote" class="form-control"> 
                                        <label v-if="lote_existente != ''" style="color:red;">{{lote_existente}}</label>
                                    </div>
                                    <div class="form-group" id="nserief" style="display: none;">
                                        <label>Numero de Serie</label> 
                                        <div id="series">
                                            <input type="text" placeholder="Numero de Serie" class="form-control" v-on:keyup="revisar_lote_existente(this)">
                                            <label v-if="lote_existente != ''" style="color:red;">{{lote_existente}}</label>
                                        </div>
                                    </div>

                                    <?php 
                                    */
                                    ?>
                                    <div v-if="articulo.maneja_lotes == 1" class="form-group" id="nlotef">
                                        <label>Lote*</label> 
                                        <input v-model="articulo.lote" v-on:keyup="revisar_lote_existente(articulo.lote)" type="text" placeholder="Lote" class="form-control" v-on:keyup.enter="mayusculas"> 
                                        <label v-if="lote_existente != ''" style="color:red;">{{lote_existente}}</label>
                                    </div>
                                    <div v-if="articulo.maneja_series == 1" class="form-group" id="nserief">
                                        <label>Número de Serie</label> 
                                        <div id="series">
                                            <input v-model="articulo.serie" type="text" placeholder="Número de Serie" class="form-control" v-on:keyup.enter="mayusculas" v-on:keyup="revisar_lote_existente(articulo.serie)">
                                            <label v-if="lote_existente != ''" style="color:red;">{{lote_existente}}</label>
                                        </div>
                                    </div>
                                    <?php 
                                    
                                    ?>

                                    <div class="form-group" id="cantidadRecibidaLibre" style="display:none">
                                        <label>Cantidad Recibida</label> 
                                        <input :disabled="articulo.cve_articulo == ''"  type="text" placeholder="Cantidad Recibida" class="form-control">
                                    </div>
                                    <input id="xpieza" type="hidden" placeholder="Cantidad Recibida" class="form-control">
                                    <input id="xcaja" type="hidden" placeholder="Cantidad Recibida" class="form-control">
                                    <input id="xpallet" type="hidden" placeholder="Cantidad Recibida" class="form-control">
                                    <input id="xkg" type="hidden" placeholder="Cantidad Recibida" class="form-control">
                                </div>
                                <div class="col-lg-6 b-t">
                                    <div class="form-group">
                                       <label>Usuario</label>
                                       <div class="form-control" :value="orden.clave_usuario" disabled >({{orden.clave_usuario}}) {{orden.nombre_usuario}}</div>
                                    </div>
                                     <div class="form-group">
                                        <label>Unidad de Medida</label>
                                        <select class="form-control" id="umed" v-model="articulo.unidad_medida" v-on:change="set_med(med.id_umed)">
                                        <option value="" >Seleccion Medida</option>
                                        <option v-for="med in medidas" v-if="med.Activo == 1" :value="med.mav_cveunimed">{{med.des_umed}}</option>
                                        </select>
                                    </div>
                                     <div class="form-group">
                                        <label>Cantidad Recibida (Piezas)</label>
                                        <input id="cantidad_recibida" type="text" placeholder="Cantidad Piezas" class="form-control" readonly="">
                                    </div>
                                    <div class="form-group" id="costoUnitario" v-if="tipo_entrada != 1">
                                        <label>Costo Unitario ($)</label>
                                        <input v-on:keyup="calcular_costo()" v-model="articulo.costo" oninput="parseFloat($(this).val()).toFixed(2)"  type="number" step="0.01" placeholder="Costo Unitario" class="form-control">
                                    </div>

                                    <?php 
                                    /*
                                    ?>
                                    <div class="form-group" id="data_1" style="display: none;">
                                        <label>Caducidad*</label>
                                        <div class="input-group date">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="caducidad" type="date" class="form-control">
                                        </div>
                                    </div>
                                    <?php 
                                    */
                                    ?>
                                    <div v-if="articulo.maneja_caducidad == 1" class="form-group" id="data_1">
                                        <label>Caducidad*</label>
                                        <div class="input-group date">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input v-model="articulo.caducidad" :disabled="lote_existente != ''" id="caducidad" type="date" class="form-control">
                                        </div>
                                    </div>
                                   <?php 
                                   /*
                                   ?>
                                    <div class="form-group" id="costoEnOC">
                                        <label>Costo Total ($)</label> 
                                        <div class="form-control" disabled></div>
                                    </div>
                                   <?php 
                                   */

                                   ?>
                                    <div class="form-group" id="costoEnOC" >
                                        <label>Costo Total ($)</label> 
                                        <div class="form-control" disabled >{{costo_calculado}}</div>
                                    </div>
                                    <?php 

                                    ?>

                                </div>
                                <div class="col-lg-12">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <center>
                                                <div id="recibir_articulo">
                                                    <button type="button" class="btn btn-success t-10 permiso_registrar" v-on:click="traer_hora_actual()">Recibir artículo</button>
                                                </div>
                                            </center>
                                        </div>
                                        <div class="col-md-6">
                                                <div id="pallet_contenedor_btn" style="display: none;">
                                                <label for="cerrar_pallet_btn">
                                                <input type="button" class="btn btn-success t-10" name="cerrar_pallet_btn" id="cerrar_pallet_btn" v-on:click="active_pallet_gen()" value="Cerrar Pallet Genérico">
                                                </label>
                                                </div>

                                        </div>
                                        <div class="col-md-6" style="display: none;">
                                            <center>
                                                <button type="button" class="btn btn-success" v-on:click="recibir_todo()">Recibir toda la OC</button>
                                            </center>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-lg-12" id="botonesLibre" style="display:none">
                                    <center>
                                        <button type="button" class="btn btn-primary" id="addrowLibre">Agregar Producto</button>
                                        <button type="button" class="btn btn-success" onclick="receiveOC()" id="botonRecibirLibre">Recibir Entrada Libre</button>
                                    </center>
                                </div>
                            </div>
                            <div class="row">
                                <label id="label-table-1">Productos recibidos</label>
                                <div class="col-lg-12 table-responsive"><br>    
                                    <table class="table table-bordered ">
                                        <thead>
                                            <tr>
                                                <td><label>Pallet/Contenedor</label></td>
                                                <td><label>LP</label></td>
                                                <td><label>Clave</label></td>
                                                <td><label>Decripción</label></td>
                                                <td><label>Serie</label></td>
                                                <td><label>Lote</label></td>
                                                <td><label>Caducidad</label></td>
                                                <td><label>Peso</label></td>
                                                <td><label>Cajas</label></td>
                                                <td><label>Piezas</label></td>
                                                <td><label>Total (Pz)</label></td>
                                                <td><label>Zona de Recepción</label></td>
                                                <td><label>Usuario</label></td>
                                                <td><label>Costo</label></td>
                                                <td><label>Hora de recepcion</label></td>
                                                <td v-if="tipo_protocolo==1"><label>Num. Pedimento</label></td>
                                                <td v-if="tipo_protocolo==1"><label>Fecha Pedimento</label></td>
                                                <td style="display: none;"><label>Acciones</label></td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="art in articulos_recibidos">
                                                <td>{{art.contenedor}}</td>
                                                <td>{{art.pallet}}</td>
                                                <td v-if="tipo_entrada != 2">{{art.clave_articulo}}</td>
                                                <td v-if="tipo_entrada == 2">{{art.cve_articulo}}</td>
                                                <td>{{art.descripcion}}</td>
                                                <td class="n_series">{{art.serie}}</td>
                                                <td>{{art.lote}}</td>
                                                <td>{{art.caducidad}}</td>
                                                <td>{{art.peso}}</td>
                                                <td>{{art.n_cajas}}</td>
                                                <td>{{art.n_piezas}}</td>
                                                <td>{{art.cantidad_por_recibir}}</td>
                                                <td>{{art.zona_recepcion}}</td>
                                                <td>{{orden.clave_usuario}}</td>
                                                <td>{{art.costo_total}}</td>
                                                <td>{{art.hora_de_recepcion}}</td>
                                                <td v-if="tipo_protocolo==1" class="num_pedimento_inter">{{art.num_pedimento}}</td><?php /* ?><input type="text" class="form-control num_pedimento_inter"><?php */ ?>
                                                <td v-if="tipo_protocolo==1" class="fecha_pedimento_inter">{{art.fecha_pedimento}}</td><?php /* ?><input type="date" class="form-control fecha_pedimento_inter"><?php */ ?>
                                                <td style="display: none;"><a href="#" v-on:click="borrar_articulo_recibido(art.cve_articulo)"><i class="fa fa-eraser" alt="Borrar"></i></a></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <label >Productos esperados por la Orden de Compra ({{articulos.length}})</label>
                                <div class="col-lg-12 table-responsive"> 
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <td><label>Clave</label></td>
                                                <td><label>Lote|Serie</label></td>
                                                <td><label>License Plate (LP)</label></td>
                                                <td><label>Decripción</label></td>
                                                <td><label>Cantidad</label></td>
                                                <td><label>Costo</label></td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="art in articulos" v-bind:style= "[art.color_1 == 1 ? art.color_2 == 1 ? {'background':'green','color':'black'} : {'background':'yellow', 'color':'black'} : {'background':'red', 'color':'black'}]">
                                                <td>{{art.cve_articulo}}</td>
                                                <td>{{art.val_lote}}</td>
                                                <td>{{art.val_lp}}</td>
                                                <td>{{art.nombre_articulo}}</td>
                                                <td v-if="art.cve_unidad_medida == 'XBX'">{{art.cantidad*art.num_multiplo}}</td>
                                                <td v-if="art.cve_unidad_medida != 'XBX'">{{art.cantidad}}</td>
                                                <!--<td>{{art.cantidad}}</td>-->
                                                <td>{{art.costo}}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-success permiso_registrar" v-on:click="traer_hora_fin()">Guardar</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="pull-right">
                                        <a href="#" onclick="cancelar()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="hiddenAction">
                        <input type="hidden" id="hiddenID_Aduana">
                        <input type="hidden" id="guardo">
                        <input type="hidden" id="lote_valor">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-form2" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-md">
                <div class="modal-content">
                  <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                      <h4>Nuevo Lote</h4>
                  </div>
                  <div class="modal-body">
                    <div class="row">
                        <div class="form-group" id="lotex">
                           <label>Lote*</label>
                           <select class="form-control" id="lote1"></select>
                        </div>
                        <div class="form-group" id="cadu" >
                            <label>Caducidad*</label>
                            <div class="input-group date">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="caduci" type="text" class="form-control">
                            </div>
                        </div>
                      </div>
                  </div>
              </div>
          </div>
        </div>
    </div>
</div>


<input type="hidden" id="oc_cross" value="">
    <div class="modal fade" id="modal_zonasembarque" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Zonas de Embarque</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label for="zonaembarque">Zonas Disponibles</label>
                                <select id="zonaembarque" class="chosen-select form-control">
                                <option value="">Seleccione la zona</option>
                            </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                        <a href="#" onclick="asignarZonaEmbarque()"><button id ="asignarpedido" class="btn btn-primary pull-right" type="button">
                        <i class="fa fa-plus"></i>&nbsp;&nbsp;Asignar zona</button>
                    </a>
                    </div>
                </div>
            </div>
        </div>
    </div>


<!-- Mainly scripts -->
<script src="/js/moment.js"></script>
<script src="/js/moment-with-locales.js"></script>
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
<script src="/js/select2.js"></script>

<!-- Vue Js-->
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.10/dist/vue.js"></script>

<!-- Data picker -->
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script src="/js/plugins/clockpicker/clockpicker.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="/js/plugins/chosen/chosen.jquery.js"></script>

<style>
  .t-10{
    margin-top: 10px;
  }
</style>

<script type="text/javascript">
    var //select_nerp = document.getElementById('select-nerp'),
        select_lote = document.getElementById('lote');
    var acum_lp = 1;
    var prefijo_lp_cerrar = "";
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
                    searchERP();
                }
            },
            error: function(res) {
                window.console.log(res);
            }
        });
    }
    almacenPrede();

    function searchERP()
    {
         $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                action: 'ERP'
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) 
                {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/ordendecompra/update/index.php',
            success: function(data) {
               fillSelect(data.res);
            },
            error: function(res) {
                window.console.log(res);
            }
        });

        function fillSelect(node)
        {
            var option = "<option value =''>ERP...</option>";
            if(node.length > 0)
            {
                for(var i = 0; i < node.length; i++)
                {
                    if(node[i].factura)
                        option += "<option value = '"+node[i].num_pedimento+"-"+node[i].factura+"'>("+node[i].num_pedimento+") "+node[i].factura+"</option>";
                }
            }
/*
            select_nerp.innerHTML = option;
            $(select_nerp).trigger("chosen:updated");
            select_nerp.onchange = function()
            {
                if(this.value)
                {
                    var value = this.value.split("-");
                    document.getElementById('numeroOC').value = value[0];
                }
                else
                {
                    document.getElementById('numeroOC').value = "";
                }
                $("#numeroOC").change();
            };
*/
        } 
    }


    $("#nlote").on('input', function(){//1
      $.ajax({
        //EDG117EDG
            type: "POST",
            dataType: "json",
            url: '/api/lotes/update/index.php',
            data: {
                LOTE: $(this).val(),
                cve_articulo: $("#articulo").val(),
                action: "load2"
            },
            beforeSend: function(x) {
              if (x && x.overrideMimeType) 
              {
                  x.overrideMimeType("application/json;charset=UTF-8");
              }
            },
            success: function(data) {
                if (data.LOTE != null)
                {
                  console.log("data",data);
                  $("#nlote").val(data.LOTE);
                  $("#caducidad").val(data.CADUCIDAD);
                  $("#CodeMessage").html("Lote existente");
                  window.crearLote = false;
                }
                else{
                  if($("#nlote").val() != "")
                  {
                    window.crearLote = true;
                    $("#CodeMessage").show();
                    $("#CodeMessage").html("Lote no existe, se creará como nuevo lote");
                    $("#caducidad").val("");
                  }
                  else{
                    window.crearLote = false;
                  }
                }
            }
        });
    });

    $(function($) {
        var grid_selector = "#grid-tabla2";
        var pager_selector = "#grid-page2";

        //resize to fit page size
        $(window).on('resize.jqGrid', function() {
                $(grid_selector).jqGrid('setGridWidth', $(".page-content").width() - 60);
            })
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
            if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') 
            {
                setTimeout(function() {
                    $(grid_selector).jqGrid('setGridWidth', parent_column.width());
                }, 0);
            }
        })

        $(grid_selector).jqGrid({
            url: '#',
            datatype: "json",
            height: "auto",
            shrinkToFit: false,
            mtype: 'POST',
            colNames: ['Clave', 'Descripcion', "Cantidad", "Costo"],
            colModel: [
              {name: 'clave',index: 'clave',width: 200,editable: false,sortable: false}, 
              {name: 'desc',index: 'desc',width: 550,editable: false,sortable: false}, 
              {name: 'cantidad',index: 'cantidad',width: 150,editable: false,sortable: false}, 
              {name: 'costo',index: 'costo',width: 150,editable: false,sortable: false}, 
            ],
            rowNum: 30,
            rowList: [30, 40, 50],
            pager: "#grid-page2",
            sortname: 'cve_gpoart',
            viewrecords: true,
            sortorder: "desc",
            ondblClickRow: function(rowid, iRow, iCol) {
                var row_id = $(grid_selector).getGridParam('selrow');
                $(grid_selector).editRow(row_id, true);
            }
        });

        $(window).triggerHandler('resize.jqGrid'); //trigger window resize to make the grid get the correct size

        function imageFormat2(cellvalue, options, rowObject) 
        {
            var correl = options.rowId;
            var html = '<a href="#" onclick="borrarAdd(\'' + correl + '\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            return html;
        }

        function aceSwitch(cellvalue, options, cell) 
        {
            setTimeout(function() {
                $(cell).find('input[type=checkbox]')
                    .addClass('ace ace-switch ace-switch-5')
                    .after('<span class="lbl"></span>');
            }, 0);
        }

        function beforeDeleteCallback(e) 
        {
            var form = $(e[0]);
            if (form.data('styled')) return false;
            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_delete_form(form);
            form.data('styled', true);
        }

        function reloadPage() 
        {
            var grid = $(grid_selector);
            $.ajax({
                url: "index.php",
                dataType: "json",
                success: function(data) {
                    grid.trigger("reloadGrid", [{
                        current: true
                    }]);
                },
                error: function() {}
            });
        }

        function beforeEditCallback(e) 
        {
            var form = $(e[0]);
            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_edit_form(form);
        }

        function enableTooltips(table) 
        {
            $('.navtable .ui-pg-button').tooltip({
                container: 'body'
            });
            $(table).find('.ui-pg-div').tooltip({
                container: 'body'
            });
        }


        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });
    //////////////////////////////////////////////////////////Aqui se contruye el Grid 2 //////////////////////////////////////////////////////////
    $(function($) {

        var grid_selector = "#grid-tabla";
        var pager_selector = "#grid-page";

        $(window).on('resize.jqGrid', function() {
            $(grid_selector).jqGrid('setGridWidth', $(".page-content").width() - 60);
        })
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
            if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') 
            {
                setTimeout(function() {
                    $(grid_selector).jqGrid( 'setGridWidth', parent_column.width() );
                }, 0);
            }
        })

        $(grid_selector).jqGrid({
            url: '#',
            datatype: "json",
            height: "auto",
            mtype: 'POST',
            colNames: ['ID', 'Clave', 'Descripcion', 'Serie', 'Lote', 'Caducidad', "Recibidas (Pzs)", "Cve Zona", "Zona de Recepción", "Cve Usuario","Costo", "Acciones"],
            colModel: [
                {name: 'id',index: 'id',width: 10,editable: false,sortable: false,hidden: true},
                {name: 'articulo',index: 'articulo',width: 100,editable: false,sortable: false},
                {name: 'desc',index: 'desc',width: 280,editable: false,sortable: false},
                {name: 'serie',index: 'serie',width: 100,editable: false,sortable: false},
                {name: 'lote',index: 'lote',width: 100,editable: false,sortable: false},
                {name: 'caducidad',index: 'caducidad',width: 100,sortable: false,editable: false,edittype: "text"},
                {name: 'recibida',index: 'recibida',width: 150,sortable: false,editable: false,edittype: "text"},
                {name: 'cve_ubicacion',index: 'cve_ubicacion',hidden: true,width: 100,sortable: false,editable: false,edittype: "text"},
                {name: 'almacen',index: 'almacen',width: 190,sortable: false,editable: false,edittype: "text"},
                {name: 'cve_usuario',index: 'cve_usuario',width: 130,sortable: false,editable: false,edittype: "text"},
                {name: 'costo',index: 'costo',width: 110,sortable: false,editable: false,edittype: "text"},
                {name: 'myac',index: '',width: 100,fixed: true,sortable: false,resize: false,formatter: imageFormat2}
           ],
            rowNum: 30,
            rowList: [30, 40, 50],
            pager: "#grid-page",
            sortname: 'cve_gpoart',
            viewrecords: true,
            sortorder: "desc",
            ondblClickRow: function(rowid, iRow, iCol) {
                var row_id = $(grid_selector).getGridParam('selrow');
                $(grid_selector).editRow(row_id, true);
            }
        });


        $(window).triggerHandler('resize.jqGrid');

        function comprobar_campos()
        {
            var result = false;
            $("#guardo").val("true");
            $("#usuario").prop("disabled", true);

            if ($("#checkOC").is(":checked"))
            {
                if ($("#numeroOC").val() == "") 
                {
                    swal('Error','Debes indicar un numero de orden','warning');
                    return result;
                }
            }
            if ($("#zonaRecepción").val() == "") 
            {
                swal('Error','Debes seleccionar una zona de recepción','warning');
                return result;
            }
            if ($("#articulo").val() == "") 
            {
                swal('Error','Por favor selecciona un articulo','error');
                return result;
            }
            if ($("#nlotef").is(":visible") == true && $("#nlote").val() == "") 
            {
                swal('Error','Debes seleccionar o crear un Lote','warning');
                return result;
            }
            if ($("#nserief").is(":visible") == true && $("#nserie").val() == "") 
            {
                swal('Error','Debes crear un numero de serie','warning');
                return result;
            }
            if ($("#checkOC").is(":checked"))
            {
                if ($("#ctotal").val() == "0" || $("#ctotal").val() == "") 
                {
                    swal('Error','Por favor eliga la cantidad','error');
                    return result;
                }
            }
            if ($("#data_1").is(":visible") == true && $("#nlote").val() != "" && $("#caducidad").val() == "" && !$("#caducidad").prop("disabled")) 
            {
                swal('Error','La fecha de caducidad no puede estar vacia','warning');
                return;
            }
            return true;
        }

        function validar_lotes()
        {
            var loteF;
            console.log("validar_lote", $("#nlote").val()+"-"+window.crearLote);
            
            if ($("#nlote").val() != "") 
            {
              if(window.crearLote)
              {
                loteF = $("#nlote").val();
                var date = new Date();
                
                //EDG117EDG
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    async: false,
                    cache: false,
                    url: '/api/lotes/update/index.php',
                    data: {
                        cve_articulo: $("#cve_articulo").val(),
                        LOTE: $("#nlote").val(),
                        CADUCIDAD: $("#caducidad").val(),
                        action: "add"
                    },
                    beforeSend: function(x) {
                        if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                    },
                    success: function(data) 
                    {
                        console.log("GUARDE LOTE NUEVO");
                    }
                });
              }

            }
            if ($("#nserie").val() != "") 
            {
                loteF = "";
                $("#lote_valor").val(loteF);
              console.log("loteF", loteF);
            }
        }
      
            
        function traerserie() {
            if($('#nserie').val()!= "")
            {
              $.ajax({
                  type: "POST",
                  dataType: "json",
                  url: '/api/ordendecompra/update/index.php',
                  data: {
                      action : "traerserie",
                      numero_serie:$('#nserie').val(),
                      articulo:$("#cve_articulo").val(),
                  },
                  beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
                  success: function(data) {
                    $("#nlotef").hide();
                    $("#nlote").hide();
                      console.log("Creando serie",data);
                  }
              });
            }
        }

        $(window).triggerHandler('resize.jqGrid'); 

        function imageFormat2(cellvalue, options, rowObject) 
        {
            var correl = options.rowId;
            var html = '<a href="#" onclick="borrarAdd(\'' + correl + '\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            return html;
        }

        function aceSwitch(cellvalue, options, cell) 
        {
            setTimeout(function() {
                $(cell).find('input[type=checkbox]')
                    .addClass('ace ace-switch ace-switch-5')
                    .after('<span class="lbl"></span>');
            }, 0);
        }

        function beforeDeleteCallback(e) {
            var form = $(e[0]);
            if (form.data('styled')) return false;
            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_delete_form(form);
            form.data('styled', true);
        }

        function reloadPage() {
            var grid = $(grid_selector);
            $.ajax({
                url: "index.php",
                dataType: "json",
                success: function(data) {
                    grid.trigger("reloadGrid", [{
                        current: true
                    }]);
                },
                error: function() {}
            });
        }

        function beforeEditCallback(e) 
        {
            var form = $(e[0]);
            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_edit_form(form);
        }

        function enableTooltips(table) 
        {
            $('.navtable .ui-pg-button').tooltip({container: 'body'});
            $(table).find('.ui-pg-div').tooltip({container: 'body'});
        }

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });

    $("#cantrecib").change( function (e){//lilo
      console.log("change");
      var i= 1;
      var html="";
      for(i = 1 ; i<= $("#cantrecib").val();i++) 
      {
         html += '<input id="serie_'+i+'" type="text" class="form-control" value="" ></a>&nbsp;&nbsp;';
         
      }
       $("#series").html(html);
    });


    //MENUS 
    $('#checkRL').change(function(e) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                action : "folioConsecutico"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url: '/api/ordendecompra/update/index.php',
            success: function(data) {
                $('#foliorl').val(data.folioConsecutivo);
                $("#foliorl").prop('disabled', true);
            }
        });
    });

    $('#checkCD').change(function(e) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                action : "folioConsecutico"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url: '/api/ordendecompra/update/index.php',
            success: function(data) {
                $('#foliocd').val(data.folioConsecutivo);
                $("#foliocd").prop('disabled', true);
            }
        });
    });



    $('#lote').change(function(e) {
        if ($('#lote').val() == "") 
        {
          $("#nlotef").show();
        } 
        else  
        {
          //$("#nlotef").hide().val("");
        }
      //EDG117EDG
        $.ajax({
            type: "POST",
            dataType: "json",
            url: '/api/lotes/update/index.php',
            data: {
                LOTE: $(this).val(),
                cve_articulo: $("#articulo").val(),
                action: "load2"
            },
            beforeSend: function(x) {
              if (x && x.overrideMimeType) 
              {
                  x.overrideMimeType("application/json;charset=UTF-8");
              }
            },
            success: function(data) {
                if (data.success == true)
                {
                  $("#caducidad").val(data.CADUCIDAD);
                  console.log("datacaducidad",data.CADUCIDAD);
                }
            }
        });
    });
  
    function cargarOC()
    {
      $.ajax({
        type: "POST",
        dataType: "json",
        data: {
            action: "load2",
            codigo: $('#numeroOC').val()
        },
        beforeSend: function(x) {
            if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
        },
        url: '/api/ordendecompra/update/index.php',
        success: function(data) {renderOC(data);}
      });
    }
  
    function renderOC(data)
    {
        $("#fechaentrada").prop("disabled", true);
        $("#hora").prop("disabled", true);
//         $("#almacenoc").val(data.Cve_Almac);
        $("#protocolooc").val(data.ID_Protocolo);
        $("#cprotocolooc").val(data.Consec_protocolo);
        //select_nerp.value = $('#numeroOC').val()+"-"+data.factura;
        //$(select_nerp).trigger("chosen:updated");
        $("#proveedor").val(data.ID_Proveedor).change().prop("disabled", true);
        cargarOrden($('#numeroOC').val());//inserta los productos esperados y limita la cantidad de productos que pueden entrar todavia
        cargarTodo($('#numeroOC').val());//inserta los productos que ya han sido recibidos y cambia el hiddenAction a edit, si no hay productos es add
        cargarZonas(data);//Carga el listado de las zonas respecto al almacen
    }
  
    function cargarZonas(data)
    {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_almacenp: data.Cve_Almac,
                action: "loadPorAlmacen"
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) 
                {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/cortinaentrada/update/index.php',
            success: function(data) {
                var options = $("#zonaRecepción");
                options.empty();
                options.append(new Option("Seleccione", ""));
                for (var i = 0; i < data.zonas.length; i++) 
                {
                    options.append(new Option(data.zonas[i].desc_ubicacion, data.zonas[i].cve_ubicacion));
                }
                changeColorTable();//Cambia el color de las tablas relacionanado lo que espera recibir y lo que ha recibido
            }
        });
    }

  
    //$('#numeroOC').change(cambioNumeroOC);

    function changeColorTable()
    {
        var ids2 = $("#grid-tabla2").jqGrid('getDataIDs'), totalRe = 0, totalEs = 0;
        for (var i = 0; i < ids2.length; i++) 
        {
            var rowId2 = ids2[i];
            var rowData2 = $('#grid-tabla2').jqGrid('getRowData', rowId2);
            var recibido = getArti(rowData2.clave), total = parseInt(rowData2.cantidad);

            totalRe = totalRe + recibido;
            totalEs = totalEs + total;

            if(recibido === 0)
            {
                changeColor(rowId2, "#F74949");
            }
            else if(recibido === total)
            {
                changeColor(rowId2, "#45F842"); 
            }
            else
            {
                changeColor(rowId2, "#FAF44C");
            }
        }
        document.getElementById('label-table-1').innerHTML = "Productos recibidos. ( TOTAL RECIBIDOS "+totalRe+" )";
        document.getElementById('label-table-2').innerHTML = "Productos esperados por la Orden de Compra. ( TOTAL ESPERADOS "+totalEs+" )";
    }


    $("#nlote").keyup(function(e) {
        var claveCode = $(this).val();
        var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,20}$");
        if (claveCodeRegexp.test(claveCode)) 
        {
            $("#CodeMessage").html("");
            $("#btnSave").prop('disabled', false);

        } 
        else 
        {
            $("#CodeMessage").show();
            $("#CodeMessage").html("Por favor, ingresar una Clave de lote válida");
            $("#btnSave").prop('disabled', true);
        }
    });

    $('#nlote').change(function(e) {
        $("#CodeMessage").hide();
    });  

    $('#nlote').focus(function(e) {
        $("#CodeMessage").hide();
        $("#nserief").hide();
        $("#lotef").hide();
    });  

    $('#lote').focus(function(e) {
        $("#CodeMessage").hide();
        $("#nserief").hide();
    });  

    $('#nserie').focus(function(e) {
        $("#nlotef").hide();
        $("#lotef").hide();
    });  

    $('#nlote').focusout(function(e) {
        if ($('#nlote').val()==""){
            $("#CodeMessage").show();
            //$("#nserief").show();
            $("#lotef").show();
        }
    });  

    $('#lote').focusout(function(e) {
        if ($('#lote').val()==""){
            $("#CodeMessage").show();
            //$("#nserief").show();
            $("#lotef").show();
        }
    }); 

    $('#nserie').focusout(function(e) {
        if ($('#nserie').val()==""){
            //$("#nlotef").show();
            //$("#lotef").show();
        }
    }); 

    $('#umedida').change(function(e) {
        $("#cantrecib").val(0);
        $("#ctotal").val(0);
    });

//***************************************************************************************************
//                  ENVIAR PEDIDO RELACIONADO A STAGGING
//***************************************************************************************************
        function obtenerZonasDisponibles(oc) {

            $("#oc_cross").val(oc);

            $.ajax({
                    url: '/api/administradorpedidos/lista/index.php',
                    data: {
                        folio: $("#pedidos_cross").val(),
                        almacen: <?php echo $_SESSION['id_almacen']; ?>,
                        action: 'obtenerZonasEmbarque'
                    },
                    type: 'POST',
                    dataType: 'json'
                })
                .done(function(data) {
                    if (data.success) {

                        var options = '';
                        $.each(data.data, function(key, value) {
                            options += '<option value="' + value.id + '">' + value.nombre + '</option>';
                        });

                        $('#zonaembarque').html(options);
                        $('#zonaembarque').trigger("chosen:updated");
                        $('#modal_zonasembarque').modal('show');
                    } else {

                    }
                });
        }


        function asignarZonaEmbarque() {

            $.ajax({
                    url: '/api/administradorpedidos/update/index.php',
                    data: {
                        folio: $("#pedidos_cross").val(),
                        oc_cross: $("#oc_cross").val(),
                        almacen: <?php echo $_SESSION['id_almacen']; ?>,
                        status: 'C',
                        sufijo: 1,
                        zonaembarque: $('#zonaembarque').val(),
                        action: 'asignarZonaEmbarque'
                    },
                    type: 'POST',
                    dataType: 'json'
                })
                .done(function(data) {
                    if (data.success) {
                        $('#modal_zonasembarque').modal('hide');
                        $('#modalItems').modal('hide');
                        console.log("Listo Entrada 1 = ", data);
                        //swal("Éxito", "Status de la orden cambiado exitosamente", "success");
                        //swal("Éxito", "Pedido Enviado a Stagging Area exitosamente", "success");
                        var oc = $("#oc_cross").val();
                          swal({
                            title: 'Éxito',
                            text: `¡La Recepción #${oc} se ha recibido correctamente`,
                            type: 'success',
                            showConfirmButton: true,
                            confirmButtonText: 'Aceptar',
                            showCancelButton: false,
                            closeOnConfirm: true,
                            closeOnCancel: false
                          }, 
                          function() 
                          {
                              console.log("Reload...");
                              window.location.reload();
                          });


                        console.log("filtralo 2");
                        //filtralo();
                        //window.location.reload();
                    } else {
                        //swal("Error", "Ocurrió un error al cambiar el status de la orden", "success");
                        swal("Error", "Ocurrió un error al enviar el pedido a Embarques", "success");
                    }
                }).fail(function(data){
                    console.log("ERROR EMBARQUE = ", data);
                });
        }

//***************************************************************************************************
//***************************************************************************************************

    function Solo_Numerico(variable) 
    {
        Numer = parseInt(variable);
        if (isNaN(Numer)) {return "";}
        return Numer;
    }

    function ValNumero(Control) 
    {
        Control.value = Solo_Numerico(Control.value);
    }

    function ReloadGrid() 
    {
        $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    criterio: $("#txtCriterio").val(),
                },
                datatype: 'json',
                page: 1
            })
            .trigger('reloadGrid', [{
                current: true
            }]);
    }

    function ReloadGrid1() 
    {
        $('#grid-table2').jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    criterio: $("#txtCriterio2").val(),
                },
                datatype: 'json',
                page: 1
            })
            .trigger('reloadGrid', [{
                current: true
            }]);
    }

    function downloadxml(url) 
    {
        var win = window.open(url, '_blank');
        win.focus();
    }

    function viewPdf(url) 
    {
        var win = window.open(url, '_blank');
        win.focus();
    }

    $modal0 = null;

    function borrar(_codigo) 
    {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                ID_Aduana: _codigo,
                action: "delete"
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) 
                {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/ordendecompra/update/index.php',
            success: function(data) {
                if (data.success == true) 
                {
                    ReloadGrid();
                }
            }
        });
    }

    function selectarticulo() 
    {
        $("#modaltitle").html('<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><i class="fa fa-laptop modal-icon"></i><h4 class="modal-title">Agregar Proveedores</h4>');
        $modal0 = $("#myModal");
        $modal0.modal('show');
        l.ladda('stop');
        $("#hiddenAction").val("add");
        $("#btnCancel").show();
        $("#nombre_proveedor").val("");
        $("#contacto").val("");
    }


    function quehizo() 
    {
        borro = localStorage.getItem('borro');
        guardo = localStorage.getItem('guardo');
        var quehizo;
        if (guardo > 0 && borro > 0) 
        {
            quehizo = 'Agrego y borro producto(s)';
        } 
        else if (guardo > 0 && borro < 1) 
        {
            quehizo = 'Agrego producto(s)';
        } 
        else if (guardo < 1 && borro > 0) 
        {
            quehizo = 'Borro producto(s)';
        }
        return quehizo;
    }


    $('#data_2 .input-group.date').datepicker({
        todayBtn: "linked",
        keyboardNavigation: false,
        forceParse: false,
        calendarWeeks: true,
        autoclose: true,
        format: 'dd-mm-yyyy',
        'default': 'now',
        startDate: new Date()
    }).datepicker("setDate", new Date()).datepicker('option', 'minDate', new Date());

    $('.clockpicker').clockpicker({
        autoclose: true,
        'default': DisplayCurrentTime(),
        twelvehour: true,
    }).find('input').val(DisplayCurrentTime());

    function DisplayCurrentTime() 
    {
        var date = new Date();
        var hours = date.getHours() > 12 ? date.getHours() - 12 : date.getHours();
        var am_pm = date.getHours() >= 12 ? "PM" : "AM";
        hours = hours < 10 ? "0" + hours : hours;
        var minutes = date.getMinutes() < 10 ? "0" + date.getMinutes() : date.getMinutes();
        var seconds = date.getSeconds() < 10 ? "0" + date.getSeconds() : date.getSeconds();
        time = hours + ":" + minutes + ":" + am_pm;
        return time;
    }


    $('#foliorl').keyup(function(e) { // text written
        $("#fechaentrada").prop("disabled", true);
        $("#hora").prop("disabled", true);
    });

    $('#foliocd').keyup(function(e) { // text written
        $("#fechaentrada").prop("disabled", true);
        $("#hora").prop("disabled", true);
        cargarTodo($('#foliocd').val());
    });



    function clock() { // We create a new Date object and assign it to a variable called "time".
        var time = new Date(),
            hours = time.getHours(),
            minutes = time.getMinutes(),
            seconds = time.getSeconds();

        if (hours > 12) 
        {
            taime = "PM";
            hours = hours - 12;
        } 
        else
        {
            taime = "AM";
        }

        $('#horafin').val(harold(hours) + ":" + harold(minutes) + taime);

        function harold(standIn) 
        {
            if (standIn < 10) 
            {
                standIn = '0' + standIn
            }
            return standIn;
        }
    }
    setInterval(clock, 1000);

    var auto;
    //ESTO HACE UN FLOOP 

    $(document).ready(function() {
        localStorage.clear();

        var date;
        date = new Date();
        date = date.getFullYear() + '-' +
            ('00' + (date.getMonth() + 1)).slice(-2) + '-' +
            ('00' + date.getDate()).slice(-2) + ' ' +
            ('00' + date.getHours()).slice(-2) + ':' +
            ('00' + date.getMinutes()).slice(-2) + ':' +
            ('00' + date.getSeconds()).slice(-2);
        $("#fechaentrada").val(date);
        $("#fechainicio").val(date);
        $("#checkOC").prop('checked', true);
        $("#checkRL").prop('checked', false);
        $("#checkCD").prop('checked', false);

        $("#numeroOC").prop('disabled', false);
        $("#foliorl").prop('disabled', true);
        $("#foliocd").prop('disabled', true);
        $("#foliorl").val('');
        $("#foliocd").val('');
        $("#almacenf").hide();
        $("#almacenfoc").show();
        $("#proveedor").prop('disabled', true);
        $("#hiddenAction").val("add");

        $("#cprotocolof").hide();
        $("#protocolof").hide();

        $modal0 = $("#coModal");
        $modal0.modal('toggle');
        $("#_title").html('<h3>Recepción de Materiales</h3>');

        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeOutRight");
        $('#list').hide();

        $('#FORM').show();
        $('#FORM').removeAttr('class').attr('class', '');
        $('#FORM').addClass('animated');
        $('#FORM').addClass("fadeInRight");
        $("#basic").select2("val", "");

        $("#hiddenAction").val("add");
        $("#fechaentrada").trigger("change");

    });

    $("#txtCriterio").keyup(function(event) {
        if (event.keyCode == 13) 
        {
            $("#buscarA").click();
        }
    });


    function cargarOrden(_codigo) 
    {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                action: "load",
                codigo: _codigo
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
            },
            url: '/api/ordendecompra/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    $("#grid-tabla2").jqGrid("clearGridData");
                    console.log("data.detalle.leght", data.detalle.length);
                    console.log("cant_max = ", window.app.articulo.cantidad_pedida);
                    console.log("ucaja_val = ", $("#ucaja_val").val());
                    console.log("c_peso_val = ", $("#c_peso_val").val());

                    for (var i = 0; i < data.detalle.length; i++) 
                    {
                        var cant = data.detalle[i].cantidad;

                        if($("#ucaja_val").val() > 0 && $("#c_peso_val").val() == 'N' && $("#umed").val() == "XBX")//caja
                            cant = $("#cantidad_recibida").val();

                        emptyItem = [{
                            clave: data.detalle[i].cve_articulo,
                            desc: data.detalle[i].des_articulo,
                            cantidad: cant,
                            costo: data.detalle[i].costo
                        }];
                        $("#grid-tabla2").jqGrid('addRowData', 0, emptyItem);
                        console.log("Aqui Carga los articulos que se esperan de la orden de compra");
                    }
                    var options = $("#articulo");
                    options.empty();
                    options.append(new Option("Seleccione", ""));
                    for (var i = 0; i < data.sinCompletar.length; i++) 
                    {
                        options.append(new Option("(" + data.sinCompletar[i].cve_articulo + ") " + data.sinCompletar[i].des_articulo, data.sinCompletar[i].id));
                    }
                }
            }
        });
    }
</script>

<script type="text/javascript">

$(function($) 
{
    $("#orden_de_compra").change(function()
    {
        console.log("Valor orden_de_compra", $(this).val());


        $.ajax({
            type: "POST",
            //dataType: "json",
            url: '/api/ordendecompra/update/index.php',
            data: {
                action: 'getDetallesFolio',
                folio: $(this).val()
            },
            beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
            },
            success: function(data) 
            {
                //console.log("datos OC", data);
                //console.log("datos OC Select", data.rows[0]);
                window.app.almacenes = data.almacenes;
                //$("#articulos_lista").append(data.rows[0]);
            },
            error: function(data) 
            {
                console.log("error", data);
            }
        });

    });

    $("#articulos_lista, #articulos_lista2").change(function()
    {
        console.log("articulos_lista = ", $(this).val());
        var val_articulo = $(this).val();
        console.log("val_articulo = ", val_articulo);
        var array_val_lp = val_articulo.split(":::::");
        var array_val = array_val_lp[0].split(";;;;;");
        //console.log("array_val.length = ", array_val.length);
        var cve_articulo = array_val[0];
        $.ajax({
            type: "POST",
            //dataType: "json",
            url: '/api/ordendecompra/update/index.php',
            data: {
                action: 'getUnidadesCaja',
                cve_articulo: cve_articulo
            },
            beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
            },
            success: function(data) 
            {
                console.log("SUCCESS = ", data);

                /*
                if(data.control_peso == 'N')
                {
                    $("#umed").children('option[value="XBX"]').show();//attr('disabled', false);
                    $("#umed").children('option[value="H87"]').show();//attr('disabled', false);

                    //if(data.num_multiplo > 0)
                    //    $("#umed").val("2");
                    //else 
                    //    $("#umed").val("");
                }
                else
                {
                    $("#umed").children('option[value="XBX"]').hide();//attr('disabled', true);
                    $("#umed").children('option[value="H87"]').hide();//attr('disabled', true);
                }
                */
                $("#ucaja_val").val(data.num_multiplo);
                $("#c_peso_val").val(data.control_peso);

                //this.articulo.cantidad_pedida = this.ordenes[this.numero_interno_orden].articulos[j].cantidad;

                //console.log("cant_max = ", $("#cant_max").val());
                console.log("cant_max = ", window.app.articulo.cantidad_pedida);
                console.log("ucaja_val = ", $("#ucaja_val").val());
                console.log("c_peso_val = ", $("#c_peso_val").val());

                if($("#umed").val() == "XBX")
                {
                    $("#cantidad_recibida").val($("#a_recibir").val()*$("#ucaja_val").val());
                }
                else //if($("#umed").val() == "H87")
                        $("#cantidad_recibida").val($("#a_recibir").val());

/*
                if($("#ucaja_val").val() > 0 && $("#c_peso_val").val() == 'N')
                var cant = data.detalle[i].cantidad
                {
                    var val_max = window.app.articulo.cantidad_pedida;
                    val_max = parseFloat(val_max) / parseFloat($("#ucaja_val").val());
                    val_max = parseInt(val_max);
                    console.log("val_max = ", val_max);
                    window.app.articulo.cantidad_pedida = val_max;
                }
*/
            },
            error: function(data) 
            {
                console.log("error", data);
            }
        });

        /*
        var valores = $(this).val();
        var valores_array = valores.split("*-*");
        console.log("ID_Aduana", valores_array[0]);
        console.log("clave", valores_array[1]);
        console.log("pedidas", valores_array[2]);
        console.log("lote", valores_array[3]);
        console.log("serie", valores_array[4]);
        console.log("caducidad", valores_array[5]);

        //app.set_articulo("'"+valores_array[1]+"'");

        if(valores)
            $("#label-cant-max #cant_recOC").text(valores_array[2]);
        else 
            $("#label-cant-max #cant_recOC").text("");

        if(valores_array[4])//maneja serie
        {
            $("#cantidadRecibidaOC input").val(valores_array[2]);
            $("#cantidadRecibidaOC input").prop("disabled", true);
            $("#nlotef").hide();
            $("#nserief").show();
            $("#nserief input").val(valores_array[4]);
            $("#nserief input").prop("disabled", true);
            $("#data_1").hide();
            $("#data_1 #caducidad").val("");
            $("#data_1 #caducidad").prop("disabled", false);
        }
        else if(valores_array[3])//maneja lote
        {
            console.log("Lote ON");
            $("#cantidadRecibidaOC input").val("");
            $("#cantidadRecibidaOC input").prop("disabled", false);
            $("#nlotef").show();
            $("#nserief").hide();
            $("#nlotef input").val(valores_array[3]);
            $("#nlotef input").prop("disabled", true);
            if(valores_array[5])
            {
                console.log("caducidad ON");
                $("#data_1").show();
                var data_caducidad = valores_array[5].split("-");
                $("#data_1 #caducidad").val(data_caducidad[2]+"-"+data_caducidad[1]+"-"+data_caducidad[0]);
                $("#data_1 #caducidad").prop("disabled", true);
            }
            else
            {
                $("#data_1").hide();
                $("#data_1 #caducidad").val("");
                $("#data_1 #caducidad").prop("disabled", false);
            }
        }
        else
        {
            $("#cantidadRecibidaOC input").val("");
            $("#cantidadRecibidaOC input").prop("disabled", false);
            $("#nlotef").hide();
            $("#nserief").hide();
            $("#nserief input").val("");
            $("#nserief input").prop("disabled", false);
            $("#nlotef input").val("");
            $("#nlotef input").prop("disabled", false);
            $("#data_1 #caducidad").hide();
            $("#data_1 #caducidad").val("");
            $("#data_1 #caducidad").prop("disabled", false);
        }
        */
    });
/*
    $("#s_articulo").keyup(function(){

        console.log("Valor = ");//, e.key

    });
*/
    $("#a_recibir").keyup(function()
    {
        //console.log("cant_max = ", window.app.articulo.cantidad_pedida);
        console.log("ucaja_val = ", $("#ucaja_val").val());
        //console.log("c_peso_val = ", $("#c_peso_val").val());

        console.log("a_recibir = ", $(this).val());
        console.log("window.app.articulo.cantidad_pedida = ", window.app.articulo.cantidad_pedida);
        
        if($("#umed").val() == "XBX")
        {
            $("#cantidad_recibida").val($(this).val()*$("#ucaja_val").val());

            //if(parseFloat($("#cantidad_recibida").val()) > parseFloat(window.app.articulo.cantidad_pedida)) //*parseFloat($("#ucaja_val").val())
            //   $("#cantidad_recibida").val(window.app.articulo.cantidad_pedida);
        }
        else //if($("#umed").val() == "H87")
                $("#cantidad_recibida").val($(this).val());

        if($(this).val() == "")
           $(this).val("0");
        /*
        var val_max = window.app.articulo.cantidad_pedida;
        val_max = parseFloat(val_max) / parseFloat($("#ucaja_val").val());
        val_max = parseInt(val_max);
        console.log("val_max = ", val_max);
        window.app.articulo.cantidad_pedida = val_max;
        */
    });

    $("#umed").change(function()
    {
        //console.log("cant_max = ", window.app.articulo.cantidad_pedida);
        //console.log("ucaja_val = ", $("#ucaja_val").val());
        //console.log("c_peso_val = ", $("#c_peso_val").val());

        console.log("a_recibir = ", $("#a_recibir").val());
        console.log("window.app.articulo.cantidad_pedida = ", window.app.articulo.cantidad_pedida);
        console.log("umed = ", $("#umed").val());
        console.log("this.tipo_entrada = ", window.app.tipo_entrada);

        if($("#umed").val() == "XBX" && window.app.tipo_entrada == 1)
        {
            window.app.articulo.cantidad_pedida = parseInt(window.app.articulo.cantidad_pedida/$("#ucaja_val").val());
            console.log("CAJA window.app.articulo.cantidad_pedida = ", window.app.articulo.cantidad_pedida);
            setTimeout(function(){
                $("#a_recibir").val(window.app.articulo.cantidad_pedida);
                $("#cantidad_recibida").val(parseInt($("#a_recibir").val()*$("#ucaja_val").val()));
                console.log("SI CAMBIO POR UNIDAD");
                //window.app.cantidad_por_recibir = window.app.articulo.cantidad_pedida;
            }, 500);
            //if(parseFloat($("#cantidad_recibida").val()) > parseFloat(window.app.articulo.cantidad_pedida)) //*parseFloat($("#ucaja_val").val())
            //   $("#cantidad_recibida").val(window.app.articulo.cantidad_pedida);
        }
        else //if($("#umed").val() == "H87")
        {
            $("#cantidad_recibida").val($("#a_recibir").val());
            window.app.articulo.cantidad_pedida = $("#cant_max").val();
            $("#a_recibir").val($("#cant_max").val());
        }

        if($("#a_recibir").val() == "")
           $("#a_recibir").val("0");
        /*
        var val_max = window.app.articulo.cantidad_pedida;
        val_max = parseFloat(val_max) / parseFloat($("#ucaja_val").val());
        val_max = parseInt(val_max);
        console.log("val_max = ", val_max);
        window.app.articulo.cantidad_pedida = val_max;
        */
    });

    $("#protocoloX").change(function() {
        var ID_Protocolo = $(this).val();
        console.log("Protocolo = ", ID_Protocolo);
        console.log("Protocolo Texto = ", $( "#protocolo option:selected" ).text());

        var str = $( "#protocolo option:selected" ).text();
        var strPos = str.indexOf("Inter");

        if(strPos >= 0)
        {
            console.log("Protocolo Res = InterNacional");
            this.tipo_protocolo = 1;
        }
        else
        {
            console.log("Protocolo Res = Nacional");
            this.tipo_protocolo = 0;
        }



        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                ID_Protocolo: ID_Protocolo,
                action: "getConsecutivo"
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) 
                {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/ordendecompra/update/index.php',
            success: function(data) {
                console.log("PROTOCOLO = ", data);
                $('#cprotocolo').val(data.consecutivo);
            }, error: function(data){
                console.log("ERROR PROTOCOLO", data);
            }

        });

    });

    $("#almacen_select").change(function(){

        console.log("almacen_select = ", $(this).val());
        //zona_recepcion_select
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/ordendecompra/update/index.php',
                data: {
                    action: 'traer_zonas',
                    almacen: $(this).val()
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    if (data.success == true) 
                    {
                        console.log("ZONAS = ", data.zonas);
                        window.app.zonas = data.zonas;
                    }
                },
                error: function(data) 
                {
                    window.console.log(data);
                }
            });

    });

});

    $('#data_1 .input-group.date').datetimepicker({
        locale: 'es',
        format: 'DD-MM-YYYY',
        useCurrent: false
    });

    //VUE EDG
window.app = new Vue({
    el: '#app_recibir_articulo',
    data: 
    {
        almacen_principal: '',
        numero_interno_orden: '',
        almacenes: [],
        ordenes: [],
        crossdocking: [],
        crossd:{id:''},
        zonas: [],
        contenedores: [],
        license_plates: [],
        articulos: [],
        articulos_recibidos: [],
        articulos_todos: [],
        articulos_con_lotes: [],
        articulos_con_series: [],
        articulos_con_peso: [],
        proveedores: [],
        lotes: [],
        medidas: [],
        orden:{
            id:'',
            articulos: [],
            status:'',
            clave_usuario:'',
            nombre_usuario:'',
        },
        articulo:{
            cve_articulo:'',
            descripcion:'',
            serie:'',
            lote:'',
            caducidad:'',
            peso: '',
            costo:'',
            cantidad_pedida:'',
            cantidad_por_recibir:'',
            maneja_lotes: 0,
            lp: '',
            maneja_caducidad: 0,
            maneja_series: 0,
            maneja_peso: 0,
            zona_recepcion: '',
            num_pedimento_int: '',
            fecha_pedimento_int: '',
            costo_total: 0,
            hora_de_recepcion:'',
            se_crea_lote: 0,
            acepto_entrada_articulo: 1,
            lote_creado: 0,
            contenedor: '',
            maneja_contenedor: 0,
            multiplo: '',
            id_con: '',
            n_cajas: 0,
            n_piezas: 0,
            perma: '',
            pallet: '',
            pesomax:'',
            volumen:'',
            unidad_medida:'',
            caja_generica:'',
            num_pedimento: '',
            fecha_pedimento: '',
        },
        proveedor:{
            id:'',
            Nombre:''
        },
        tipo_entrada: 1,
        tipo_protocolo: 0,
        costo_calculado: '',
        lote_existente: '',
        hora_actual:'',
        lp_selected: '',
        zona:{
            cve_ubicacion: '',
            desc_ubicacion: '',
        },
        con:{
            permanente: '',
            id: '',
            clave_contenedor: '',
            clave_lp: '',
            descripcion: '',
            volu:'',
            tipoGen: '',
        },
        pat:{
            permanente: '',
            id: '',
            clave_contenedor: '',
            clave_lp: '',
            descripcion: '',
            volu:'',
            tipoGen: '',
        },
        med:{
            id_umed:'',
            descripcion:'',
        },
        folio_recepcion:'',
    },
    methods: 
    {
        f_recibir: function(event) 
        {
            agregar_linea();
            console.log("agregar_linea");
        },
      
        mayusculas: function()
        {
            this.articulo.lote = this.articulo.lote.toUpperCase()
            this.articulo.serie = this.articulo.serie.toUpperCase()
        },
      
        traer_almacenes: function()
        {
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/ordendecompra/update/index.php',
                data: {
                    action: 'traer_almacenes',
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    if (data.success == true) 
                    {
                        console.log(data.almacenes);
                        window.app.almacenes = data.almacenes;

                    }
                },
                error: function(data) 
                {
                    window.console.log(data);
                }
            });
        },
      
        traer_zonas: function()
        {
            console.log("traer_zonas = ", this.almacen_principal);
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/ordendecompra/update/index.php',
                data: {
                    action: 'traer_zonas',
                    almacen: this.almacen_principal,
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    if (data.success == true) 
                    {
                        console.log(data.zonas);
                        window.app.zonas = data.zonas;
                    }
                },
                error: function(data) 
                {
                    window.console.log(data);
                }
            });
        },
      
        traer_contenedores: function()
        {
            console.log("almacen: ",<?php echo $_SESSION['id_almacen']; ?>);
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/ordendecompra/update/index.php',
                data: {
                    almacen: <?php echo $_SESSION['id_almacen']; ?>,
                    action: 'traer_contenedores',
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    if (data.success == true) 
                    {
                      console.log("DATA CONTENEDORES/PALLETS", data.contenedores);
                      window.app.contenedores = data.contenedores;
                        
                        //AL ACTIVARSE EL CHOSEN NO PASA EL PALLET
                      //setTimeout(function(){$("#pallet_list").trigger("chosen:updated").chosen();}, 1000);
                    
                    //$('.chosen-select').chosen();
                    //$(".chosen-select").chosen("destroy").chosen();//.trigger("chosen:updated");
                    }
                },
                error: function(data) 
                {
                    window.console.log(data);
                }
            });
        },

        traer_lps: function()
        {
            console.log("almacen: ",<?php echo $_SESSION['id_almacen']; ?>);
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/ordendecompra/update/index.php',
                data: {
                    almacen: <?php echo $_SESSION['id_almacen']; ?>,
                    action: 'traer_lps',
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    console.log("traer_lps SUCCESS", data);
                    if (data.success == true) 
                    {
                      console.log(data.contenedores);
                      window.app.license_plates = data.contenedores;
                      //window.app.contenedores = data.contenedores;
                      
                    }
                },
                error: function(data) 
                {
                    console.log("traer_lps ERROR", data);
                    window.console.log(data);
                }
            });
        },
         traer_medidas: function(articulo)
        {
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/ordendecompra/update/index.php',
                data: {
                    cve_articulo: articulo,
                    action: 'traer_medidas',
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    if (data.success == true) 
                    {
                      console.log("medidas",data.medidas);
                      window.app.medidas = data.medidas;
                    }
                },
                error: function(data) 
                {
                    window.console.log(data);
                }
            });
        },
      
        traer_crossdocking: function(id)
        {
            console.log("TRAER CrossDocking -> OC = ", id);
            console.log("TRAER CrossDocking -> almacen = ", <?php echo $_SESSION['id_almacen']; ?>);
            
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/ordendecompra/update/index.php',
                data: {
                    oc: id,
                    almacen: <?php echo $_SESSION['id_almacen']; ?>,
                    action: 'traer_crossdocking',
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    if (data.success == true) 
                    {
                        console.log("ok crossdocking",data);
                        window.app.crossdocking = data.crossdocking;
                    }
                },
                error: function(data) 
                {
                    console.log("no",data);
                    window.console.log(data);
                }
            });
        },

        traer_ordenes: function()
        {
            console.log("TRAER_ORDENES", "<?php echo $_SESSION['cve_almacen']; ?>");
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/ordendecompra/update/index.php',
                data: {
                    almacen: "<?php echo $_SESSION['cve_almacen']; ?>",
                    action: 'traer_ordenes',
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    if (data.success == true) 
                    {
                        console.log("ok",data);
                        window.app.ordenes = data.ordenes;
                        console.log("app.ordenes = ", window.app.ordenes);
                    }
                },
                error: function(data) 
                {
                    console.log("no",data);
                    window.console.log(data);
                }
            });
        },
      
        reset_ordenes: function()
        {
            console.log("reset_ordenes");
            $("#orden_de_compra_check").val("");
            setTimeout(function(){$("#orden_de_compra_check").val("");}, 1000);
            setTimeout(function(){$("#orden_de_compra_check").val("");}, 1000);
            $("#cprotocolof").hide();
            $("#protocolof").hide();

        },
        empty_oc: function()
        {
            setTimeout(function(){$("#orden_de_compra_check").val("");}, 3000);
        },

        traer_proveedores: function()
        {
            setTimeout(function(){$("#empresa_proveedor").prop("readonly", false);}, 1500);
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/ordendecompra/update/index.php',
                data: {
                    almacen: this.almacen_principal,
                    action: 'traer_proveedores',
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    if (data.success == true) 
                    {
                        console.log("ok prov",data);
                        window.app.proveedores = data.proveedores;
                    }
                },
                error: function(data) 
                {
                    console.log("no",data);
                    window.console.log(data);
                }
            });
        },

        traer_articulos: function()
        {
            $("#cprotocolof").show();
            $("#protocolof").show();
            this.tipo_protocolo = 0;
                $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/ordendecompra/update/index.php',
                data: {
                    almacen: <?php echo $_SESSION['id_almacen']; ?>,
                    articulo_search: $("#s_articulo").val(),
                    action: 'traer_todos_los_articulos',
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    if (data.success == true) 
                    {
                        window.app.articulos_todos = data.todos_los_articulos;
                        window.app.articulos_con_lotes = data.articulos_con_lotes;
                        window.app.articulos_con_series = data.articulos_con_series;
                        window.app.articulos_con_peso = data.articulos_con_peso;

                    }
                    console.log("chosen-select 1");
                    //$('.chosen-select').chosen();
                    //$(".chosen-select").trigger("chosen:updated");
                    //$("#articulos_lista_chosen").hide();
                },
                error: function(data) 
                {
                    window.console.log(data);
                }
            });
            //console.log("chosen-select 2");
            //$(".chosen-select").trigger("chosen:updated");

        },
      
        traer_lotes: function()
        {
            console.log("traer lotes");
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/ordendecompra/update/index.php',
                data: {
                    action: 'traer_lotes',
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    if (data.success == true) 
                    {
                        console.log("traer lotes2");
                        window.app.lotes = data.lotes;
                    }
                },
                error: function(data) 
                {
                    window.console.log(data);
                }
            });
        $("#pallet_list").chosen("destroy").chosen();//.trigger("chosen:updated");
        },
      
        set_articulo(id)
        {
            console.log("Set Artículo", id);
            this.reset_articulo();
            this.traer_medidas(id);

            var ucaja = '';
            $("#nlotef input").prop("disabled", false);
            $("#nserief input").prop("disabled", false);
            $("#data_1 input").prop("disabled", false);
            $("#a_recibir").prop("readonly", false);
            if(this.tipo_entrada != 2)
            {
                var val_articulo = id;
                console.log("val_articulo = ", val_articulo);
                console.log("this.articulos = ", this.articulos);

                var array_val_lp = val_articulo.split(":::::");
                var array_val = array_val_lp[0].split(";;;;;");
                console.log("array_val.length = ", array_val.length);
                console.log("array_val_lp[1] = ", array_val_lp[1]);
                for (var i = 0; i < this.articulos.length; i++) 
                {
                    if(this.articulos[i].cve_articulo == array_val[0])
                    {
                        this.articulo.cve_articulo = id;
                        this.articulo.clave_articulo = array_val[0];

                        if(array_val_lp.length == 2)
                        {
                            this.articulo.lp = array_val_lp[1];
                            //this.lp_selected = 'import_type';
                            //window.app.lp_selected = array_val_lp[1];
                            this.articulo.contenedor = array_val_lp[1];
                            this.articulo.pallet = array_val_lp[1];
                            this.con.clave_contenedor = array_val_lp[1];
                            this.con.clave_lp = array_val_lp[1];
                            //this.con.clave_lp = array_val_lp[1];
                        }

                        this.articulo.descripcion = this.articulos[i].nombre_articulo;
                        this.articulo.costo = this.articulos[i].costo;
                        this.articulo.unidad_medida = this.articulos[i].unidadMedida;
                        this.articulo.multiplo = this.articulos[i].num_multiplo;
                        //console.log("articulo multiplo = ", this.articulo.multiplo);
                        this.articulo.volumen = (this.articulos_todos[i].ancho / 1000) * (this.articulos_todos[i].alto / 1000) * (this.articulos_todos[i].fondo / 1000);
                        ucaja = this.articulos_todos[i].num_multiplo;

                        if(array_val.length == 3)
                        {

                            if(this.articulo.maneja_lotes == 1)
                            {
                                this.articulo.lote = array_val[2];
                                //this.articulo.lote.disabled = "disabled";
                                setTimeout(function(){$("#nlotef input").prop("disabled", true);}, 500);
                            }
                            if(this.articulo.maneja_series == 1)
                            {
                                this.articulo.serie = array_val[2];
                                //this.articulo.serie.disabled = "disabled";
                                setTimeout(function(){$("#nserief input").prop("disabled", true);}, 500);
                            }
                        }
                        if(array_val.length == 4)
                        {
                            this.articulo.lote = array_val[2];
                            //this.articulo.lote.disabled = "disabled";
                            setTimeout(function(){$("#nlotef input").prop("disabled", true);}, 500);
                            this.articulo.caducidad = array_val[3];
                            //this.articulo.caducidad.disabled = "disabled";
                            setTimeout(function(){$("#data_1 input").prop("disabled", true);}, 500);
                        }


                        console.log("ucaja for = ", ucaja);
                        this.comparativa();
                        $("#ucaja_val").val(ucaja);
                        $("#c_peso_val").val(this.articulos_todos[i].control_peso);

                        //console.log("ucaja 1 = ", ucaja);
                    }
                }
                //console.log("this.numero_interno_orden", this.numero_interno_orden);
                //console.log("this.ordenes[this.numero_interno_orden].articulos.length", this.ordenes[this.numero_interno_orden].articulos.length);
                for (var j = 0; j < this.ordenes[this.numero_interno_orden].articulos.length; j++) 
                {
                    //console.log("this.ordenes[this.numero_interno_orden].articulos[j].cve_articulo", this.ordenes[this.numero_interno_orden].articulos[j].cve_articulo);
                    //console.log("this.articulo.cve_articulo", this.articulo.cve_articulo);
                    //console.log("this.ordenes[this.numero_interno_orden].articulos[j].cantidad", this.ordenes[this.numero_interno_orden].articulos[j].cantidad);
                    var val_articulo = id;
                    console.log("val_articulo = ", val_articulo);
                    console.log("this.ordenes[this.numero_interno_orden].articulos[j].lp = ", this.ordenes[this.numero_interno_orden].articulos[j].val_lp);


                    var array_val_lp = val_articulo.split(":::::");
                    var array_val = array_val_lp[0].split(";;;;;");

                    console.log("array_val_lp[1] = ", array_val_lp[1]);


                    if(this.ordenes[this.numero_interno_orden].articulos[j].cve_articulo == array_val[0] && this.ordenes[this.numero_interno_orden].articulos[j].val_lp == array_val_lp[1])
                    {
                        console.log("***********ENTRÓ AQUÍ***************");
                        console.log("array_val.length", array_val.length);
                        console.log("this.ordenes[this.numero_interno_orden].articulos[j].cantidad", this.ordenes[this.numero_interno_orden].articulos[j].cantidad);

                        if(array_val.length == 1)
                        {
                            this.articulo.cantidad_pedida = this.ordenes[this.numero_interno_orden].articulos[j].cantidad;
                            $("#cant_max, #a_recibir").val(this.ordenes[this.numero_interno_orden].articulos[j].cantidad);
                        }
                        else 
                        {
                            this.articulo.cantidad_pedida = array_val[1];
                            $("#cant_max, #a_recibir").val(array_val[1]);
                            //setTimeout(function(){$("#a_recibir").val(array_val[1]);}, 1000);
                            if(this.articulo.maneja_series == 1)
                                this.articulo.cantidad_por_recibir = 1;
                            else
                                this.articulo.cantidad_por_recibir = this.articulo.cantidad_pedida;

                            setTimeout(function(){$("#a_recibir").prop("readonly", true);}, 1500);
                        }

                        if(this.articulo.multiplo > 1) this.articulo.unidad_medida = "XBX";
                        else this.articulo.unidad_medida = this.ordenes[this.numero_interno_orden].articulos[j].cve_unidad_medida;
                        //$("#umed").change();
                        console.log("************************************************");
                        console.log("this.articulo.unidad_medida 1 = ", this.articulo.unidad_medida);

                    }
                    else if(this.ordenes[this.numero_interno_orden].articulos[j].cve_articulo == array_val[0])
                    {
                        if(array_val.length == 1)
                        {
                            this.articulo.caja_generica = this.ordenes[this.numero_interno_orden].articulos[j].caja_generica;
                            console.log("----------------------------------------");
                            console.log("----------------------------------------");
                            console.log("this.articulo = ", this.articulo);
                            console.log("----------------------------------------");
                            console.log("----------------------------------------");
    
                            this.articulo.cantidad_pedida = this.ordenes[this.numero_interno_orden].articulos[j].cantidad;
                            $("#cant_max").val(this.ordenes[this.numero_interno_orden].articulos[j].cantidad);

                            if(this.articulo.maneja_series == 1)
                                this.articulo.cantidad_por_recibir = 1;
                            else
                                this.articulo.cantidad_por_recibir = this.articulo.cantidad_pedida;

                        }
                        else 
                        {
                            this.articulo.cantidad_pedida = array_val[1];
                            $("#cant_max").val(array_val[1]);

                            
                            //setTimeout(function(){$("#a_recibir").val(array_val[1]);}, 1000);
                            if(this.articulo.maneja_series == 1)
                                this.articulo.cantidad_por_recibir = 1;
                            else
                                this.articulo.cantidad_por_recibir = this.articulo.cantidad_pedida;

                            setTimeout(function(){$("#a_recibir").prop("readonly", true);}, 1500);
                        }
                        if(this.articulo.multiplo > 1) this.articulo.unidad_medida = "XBX";
                        else this.articulo.unidad_medida = this.ordenes[this.numero_interno_orden].articulos[j].cve_unidad_medida;
                        //$("#umed").change();
                        console.log("************************************************");
                        console.log("this.articulo.unidad_medida 2 = ", this.articulo.unidad_medida);
                        $("#cant_max").val(this.articulo.cantidad_pedida);
                    }

                }
                //$('.chosen-select').chosen();
                //$(".chosen-select").trigger("chosen:updated");
                console.log("this.articulo.cantidad_pedida = ", this.articulo.cantidad_pedida);

                //this.articulo.cantidad_pedida = 2222;
                //$("#cant_max").val(this.articulo.cantidad_pedida);

                //$("#cantidadRecibidaOC #a_recibir").val(this.articulo.cantidad_pedida);
            }
            else
            {
                for (var i = 0; i < this.articulos_todos.length; i++) 
                {
                    if(this.articulos_todos[i].cve_articulo == id)
                    {
                        this.articulo.cve_articulo = id;
                        this.articulo.descripcion = this.articulos_todos[i].des_articulo;
                        this.articulo.costo = this.articulos_todos[i].costo;
                        //this.articulo.unidad_medida = this.articulos_todos[i].unidadMedida;
                        this.articulo.unidad_medida = this.articulos_todos[i].cve_unidad_medida;
                        this.articulo.caja_generica = this.articulos_todos[i].caja_generica;
                        this.articulo.multiplo = this.articulos_todos[i].num_multiplo;
                        this.articulo.volumen = (this.articulos_todos[i].ancho / 1000) * (this.articulos_todos[i].alto / 1000) * (this.articulos_todos[i].fondo / 1000);
                        console.log("vol",this.articulo.volumen);
                        ucaja = this.articulos_todos[i].num_multiplo;
                        this.comparativa();
                        console.log("ucaja 2= ", ucaja);
                        $("#ucaja_val").val(ucaja);
                        $("#c_peso_val").val(this.articulos_todos[i].control_peso);
                    }
                }  
                console.log("this.ordenes = ", this.ordenes);
                console.log("this.articulo = ", this.articulo);
                if(this.articulo.multiplo > 1) this.articulo.unidad_medida = "XBX";
                //else this.articulo.unidad_medida = this.articulo;//this.ordenes[this.numero_interno_orden].articulos[j].cve_unidad_medida;

                console.log("entrada = ","2");
            }

/*
            if(ucaja > 0)
                $("#umed").val("H87");
            else
                $("#umed").val("");
*/
            setTimeout(function(){$("#umed").change();}, 500);
            
            console.log("cant_max 0 = ", this.articulo.cantidad_pedida);
            console.log("ucaja_val 0 = ", $("#ucaja_val").val());
            console.log("c_peso_val 0 = ", $("#c_peso_val").val());
            //$('.chosen-select').chosen();
            //$(".chosen-select").trigger("chosen:updated");
        },
        buscar_articulo()
        {
            var k = $("#s_articulo").val(), j = "";
            console.log("valor keyup = ", k);

            $("#articulos_lista2 option").each(function(index, i){

                console.log("valor index = ", index);
                console.log("valor I = ", i);
                console.log("valor = ", $(this).val());
                j = $(this).text();
                console.log("valor i = ", j);
                //LO MEJOR ES BUSCAR CON UN ENTER Y DESPLEGAR LOS RESULTADOS DEL INPUT
/*
                if(j.indexOf(k) && $(this).val())
                {
                    console.log("MOSTRAR");
                    //i.show();
                    $("#articulos_lista2 option[value=" + $(this).val() + "]").hide();
                }
                else if($(this).val())
                {
                    console.log("OCULTAR");
                    //i.hide();
                    $("#articulos_lista2 option[value=" + $(this).val() + "]").hide();
                }
*/
            });

        },
        set_orden(id)
        {
            if(this.tipo_entrada == 1 || this.tipo_entrada == 3)
            { 
                for (var i = 0; i < this.ordenes.length; i++) 
                {
                    if(this.ordenes[i].num_pedimento == id)
                    {
                        console.log("Protocolo ", id ," = ", this.ordenes[i].protocolo);
                        console.log("cveProveedor ", id ," = ", this.ordenes[i].cve_proveedor_procedimiento);
                        console.log("Proveedor ", id ," = ", this.ordenes[i].nombre_proveedor_procedimiento);

                        console.log("cveProveedor | Empresa = ", id ," = ", this.ordenes[i].cve_proveedor);
                        console.log("Proveedor | Empresa = ", id ," = ", this.ordenes[i].nombre_proveedor);

                        console.log("articulos = ", id ," = ", this.ordenes[i].articulos);
                        //this.set_proveedores('331');
                        $("#empresa_proveedor").val(this.ordenes[i].cve_proveedor);
                        $("#proveedor_procedimiento").val(this.ordenes[i].nombre_proveedor_procedimiento);
                        this.tipo_protocolo = this.ordenes[i].protocolo;
                        this.articulos = this.ordenes[i].articulos;
                        //this.proveedor.id = this.ordenes[i].ID_Proveedor;
                        this.proveedor.id = this.ordenes[i].ID_Proveedor_entrada;
                        this.proveedor.Nombre = this.ordenes[i].nombre_proveedor;
                        this.orden.status = this.ordenes[i].status;
                        this.numero_interno_orden = i;
                        setTimeout(function(){$("#empresa_proveedor").prop("readonly", true);}, 1500);
                    }
                }
                this.colores_tabla();
/*
                if(this.tipo_protocolo == 1)
                {
                    console.log("PROTOCOLO = ", 1);
                    $(".internacional").show();
                }
                else
                {
                    console.log("PROTOCOLO = ", 0);
                    $(".internacional").hide();
                }
*/
            }
            else $("#empresa_proveedor").prop("readonly", false);
        },

        set_proveedores(id)
        {
            for (var i = 0; i < this.proveedores.length; i++) 
            {
                if(this.proveedores[i].ID_Proveedor == id)
                //if(this.proveedores[i].ID_Proveedor_entrada == id)
                {
                    this.proveedor.id = this.proveedores[i].ID_Proveedor;
                    //this.proveedor.id = this.proveedores[i].ID_Proveedor_entrada;
                    this.proveedor.Nombre = this.proveedores[i].Nombre;
                }
            }
        },

        set_zona(id)
        {
            for (var i = 0; i < this.zonas.length; i++) 
            {
                if(this.zonas[i].cve_ubicacion == id)
                {
                    this.zona.cve_ubicacion = id;
                    this.zona.desc_ubicacion = this.zonas[i].desc_ubicacion;
                    this.zona.clave = this.zonas[i].clave;
                    this.articulo.zona_recepcion = this.zona.desc_ubicacion;
                }
            }
        },

        set_con(id)
        {
            console.log("set_con = ", id);
            if(id == "") this.pallet_gen(id);
             for (var i = 0; i < this.contenedores.length; i++) 
             {
                console.log("this.contenedores[",i,"].clave_contenedor = ", this.contenedores[i].clave_contenedor);
                 if(this.contenedores[i].clave_contenedor == id)
                 {
                    console.log("this.con = ", this.con);

                     this.con.clave_contenedor = id;
                     this.con.descripcion = this.contenedores[i].descripcion;
                     this.articulo.contenedor = this.con.clave_contenedor;
                     this.con.id = this.contenedores[i].IDContenedor;
                     this.articulo.id_con = this.con.id;
                     this.con.permanente = this.contenedores[i].tipo;
                     this.articulo.perma = this.con.permanente;
                     this.articulo.pesomax = this.contenedores[i].pesomax;
                     this.con.volu = this.contenedores[i].capavol;
                     this.con.tipoGen = this.contenedores[i].TipoGen;
                     this.con.clave_lp = this.contenedores[i].CveLP;

                     this.pallet_gen(this.con.tipoGen);
                     this.set_lp(this.con.id);
                 }
             }
        },

        set_lp(id)
        {
            console.log("this.con.clave_lp = ", id);
            this.lp_selected = id;
            console.log("this.lp_selected = ", this.lp_selected);
             /*for (var i = 0; i < this.license_plates.length; i++) 
             {
                 if(this.license_plates[i].clave_lp == id)
                 {
                     this.con.clave_lp = id;
                     console.log("this.con.clave_lp = ", this.con.clave_lp);
                     //this.con.descripcion = this.contenedores[i].descripcion;
                     //this.articulo.contenedor = this.con.clave_contenedor;
                     //this.con.id = this.contenedores[i].IDContenedor;
                     //this.articulo.id_con = this.con.id;
                     //this.con.permanente = this.contenedores[i].tipo;
                     //this.articulo.perma = this.con.permanente;
                     //this.articulo.pesomax = this.contenedores[i].pesomax;
                     //this.con.volu = this.contenedores[i].capavol;
                 }
             }*/
        },

        pallet_gen(gen)
        {//
            $("#cerrar_pallet").prop("checked", false);
            //setTimeout(function(){
            if(gen == '1') 
            {
                //$("#pallet_contenedor").show();
                $("#pallet_contenedor_btn").show();
            }
            else
            {
                //$("#pallet_contenedor").hide();
                $("#pallet_contenedor_btn").hide();
            }
            //}, 1000);
        },
        active_pallet_gen()
        {
/*
            if($("#cerrar_pallet").is(":checked"))
                $("#cerrar_pallet").prop("checked", false);
            else
                $("#cerrar_pallet").prop("checked", true);
*/

                    swal({
                      title: 'Cerrar Pallet?',
                      text: 'Al cerrar un pallet genérico, el siguiente pallet que se cree, se creará con un LP diferente',
                      type: 'warning',
                      showConfirmButton: true,
                      confirmButtonText: 'Cerrar',
                      showCancelButton: true,
                      cancelButtonText: 'No',
                      closeOnConfirm: true,
                      closeOnCancel: true
                    }, 
                    function(confirm) 
                    {
                        if (confirm) 
                        {
                            $("#cerrar_pallet").prop("checked", true);
                            setTimeout(
                                function(){swal("Pallet Cerrado","Se ha cerrado el Pallet Correctamente", "success");}, 1000
                            );
                        }
                    });


            //if($("#cerrar_pallet").is(":checked"))
            //{
            //    swal("Advertencia","Al cerrar un pallet genérico, el siguiente pallet que se cree, se creará con un LP diferente", "warning");
            //} //else prefijo_lp_cerrar = "";
        },

        set_med(id)
        {
            for (var i = 0; i < this.medidas.length; i++) 
            {
                 if(this.medidas[i].id_umed == id)
                 {
                    this.med.id_umed = id;
                    this.med.descripcion = this.medidas[i].des_umed;
                   
                 }
            }
        },
      
        clone: function(item)
        {
            return JSON.parse(JSON.stringify(item));
        },
      
        reset_articulo: function()
        {
            this.articulo.cve_articulo = "";
            this.articulo.descripcion = "";
            this.articulo.serie = "";
            this.articulo.lote = "";
            this.articulo.caducidad = "";
            this.articulo.costo = "";
            this.articulo.cantidad_pedida = "";
            this.costo_calculado = "";
            this.articulo.cantidad_por_recibir = "";
            this.articulo.maneja_lotes = "";
            this.articulo.lp = "";
            this.articulo.maneja_caducidad = "";
            this.articulo.maneja_series = "";
            this.articulo.costo_total = "";
            this.articulo.num_pedimento = "";
            this.articulo.fecha_pedimento = "";
            this.articulo.contenedor = "";
            this.articulo.pallet = "";
            this.con.clave_lp = "";
        },
      
        set_almacenprede: function()
        {
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/almacenPredeterminado/index.php',
                data: {
                    action: 'search_almacen_pre',
                    idUser: '<?php echo $_SESSION["id_user"]?>',
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    if (data.success == true) 
                    {
                        window.app.almacen_principal = data.codigo.clave;
                        window.app.almacenes = data.codigo;
                        window.app.traer_zonas();
                    }
                },
                error: function(data) 
                {
                    window.console.log(data);
                }
            });
        },
      
        set_usuario: function()
        {
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/almacenPredeterminado/index.php',
                data: {
                    action: 'search_usuario',
                    idUser: '<?php echo $_SESSION["id_user"]?>',
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    if (data.success == true) 
                    {
                        window.app.orden.clave_usuario = data.data.cve_usuario;
                        window.app.orden.nombre_usuario = data.data.nombre_completo;
                    }
                },
                error: function(data) 
                {
                  window.console.log(data);
                }
            });
        },
      
        calcular_costo: function()
        { 
            console.log("1",this.articulo.cantidad_por_recibir);
            console.log("2",this.articulo.costo);
            if(this.articulo.costo != 0.00)
            {
                this.costo_calculado =  parseFloat(this.articulo.cantidad_por_recibir) * parseFloat(this.articulo.costo);
                console.log("3",this.costo_calculado);
            }
        },

        recibir_un_articulo: function()
        {
            this.articulo.hora_de_recepcion = this.hora_actual;
            console.log("Recibir Artículo/recibir_un_articulo = ", this.articulo);
            console.log("Recibir Contenedor = ", this.con);
            console.log("Recibir Pallet = ", this.pat);

            if(this.articulo.cve_articulo == "" )
            {
                swal("Error","Por favor Selecione Un Articulo", "error");
                return;
            }
          
            if(this.articulo.cantidad_por_recibir == "" || this.articulo.cantidad_por_recibir == 0)
            {
                swal("Error","Debe recibir por lo menos 1 articulo", "error");
                return;
            }
          
            var u_pxc = 1;
            var cantidad_pz_cj = this.articulo.cantidad_por_recibir;
            if($("#umed").val() == 'XBX')
            {   
                u_pxc = $("#ucaja_val").val();
                cantidad_pz_cj = $("#cantidad_recibida").val();
            }
            console.log("UNIDAD MEDIDA EVALUAR = ", $("#umed").val(), " - u_pxc = ", u_pxc);
            console.log("parseFloat(this.articulo.cantidad_por_recibir) = ", parseFloat(this.articulo.cantidad_por_recibir));
            console.log("cantidad_recibida = ", $("#cantidad_recibida").val());
                if(parseFloat(cantidad_pz_cj)/**parseFloat(u_pxc)*/ > (parseFloat(this.articulo.cantidad_pedida)*parseFloat(u_pxc)))
                {
                    console.log("u_pxc = ", u_pxc);
                    console.log("parseFloat(this.articulo.cantidad_por_recibir)*parseFloat(u_pxc) > parseFloat(this.articulo.cantidad_pedida)", parseFloat(this.articulo.cantidad_por_recibir)*parseFloat(u_pxc), ">", parseFloat(this.articulo.cantidad_pedida));

                    swal("Error","La cantidad por recibir excede la cantidad solicitada", "error");
                    return;
                }
          
            if(this.articulo.maneja_lotes == 1)
            {
                if(this.articulo.lote == "")
                {
                    swal("Error","Debe introducir un lote, ya que el articulo "+"("+this.articulo.cve_articulo+")"+" maneja lotes", "error");
                    return;
                }
            }
          
            if(this.articulo.maneja_caducidad == 1)
            {
                if(this.articulo.caducidad == "")
                {
                    swal("Error","Debe introducir una caducidad, ya que el articulo "+"("+this.articulo.cve_articulo+")"+" maneja caducidad", "error");
                    return;
                }
            }
          
            if(this.articulo.maneja_series == 1)
            {
                if(this.articulo.serie == "")
                {
                    swal("Error","Debe introducir un numero de serie, ya que el articulo "+"("+this.articulo.cve_articulo+")"+" maneja numero de serie", "error");
                    return;
                }
            }
          
            if(this.proveedor.id == "")
            {
                swal("Error","Debe seleccionar un Proveedor", "error");
                return;
            }
          
            if(this.articulo.zona_recepcion == "")
            {
                swal("Error","Debe seleccionar una zona de recepcion", "error");
                return;
            }
          
            if(this.articulo.se_crea_lote == 1)
            {
                if(window.app.crear_lote(this.articulo.cve_articulo, this.articulo.lote, this.articulo.caducidad))
                {
                    console.log("Se ha creado ese lote");
                    console.log("acepto_entrada_articulo = ", this.articulo.acepto_entrada_articulo);
                    
                    this.traer_lotes();
                }
            }

            if(this.articulo.maneja_peso == 1)
            {
                this.articulo.peso = this.articulo.cantidad_por_recibir;
            }

            if(this.articulo.maneja_peso == 0)
            {
                this.articulo.peso = "";
            } 
            /* 
            if(this.articulo.contenedor != "")
            {
                //if(this.articulo.perma != 'Contenedor')
                //{  
                    if(this.con.clave_lp == '')
                    {
                        var a =  new String (this.con.id);
                        var b = a.padStart(6,0);
                        this.articulo.pallet = "LP" + "" +b+ "" + "" +this.folio_recepcion+ "";
                        console.log("this.articulo.pallet",this.articulo.pallet);
                    }
                    else
                    {
                        this.articulo.pallet = this.con.clave_lp;
                        console.log("this.articulo.pallet",this.articulo.pallet);
                    }
                //}
                //else
                //{
                //    this.articulo.pallet = "";
                //}
            }
            */
            console.log("this.pat = ", this.pat);
            console.log("this.con = ", this.con);
            console.log("window.app.contenedores = ", window.app.contenedores);
            console.log("this.con.clave_contenedor = ", this.con.clave_contenedor);
            var generar_nuevo_lp = false;
            var lp_asignado = "", tipo_tarima = "", prefijo_LP = 'LP';
            var clave_comparar = this.con.clave_contenedor;
            window.app.contenedores.forEach(function(value, key){
            //console.log("window.app.contenedores[",key, "].clave_contenedor = ", window.app.contenedores[key].clave_contenedor);
                if(window.app.contenedores[key].clave_contenedor == clave_comparar)
                    if(window.app.contenedores[key].CveLP == "" || window.app.contenedores[key].TipoGen == "1")
                    {
                        generar_nuevo_lp = true;
                        tipo_tarima = window.app.contenedores[key].tipo;
                        console.log("tipo_tarima = ", tipo_tarima);
                    }
                    else 
                        lp_asignado = window.app.contenedores[key].CveLP;
            });
            
            this.articulo.contenedor = this.con.clave_contenedor;
            //this.articulo.pallet = this.pat.clave_lp;


            if($("#cerrar_pallet").is(":checked")){prefijo_lp_cerrar = "0"+acum_lp+""; acum_lp++;}
            if(generar_nuevo_lp == true) 
            {
                if(tipo_tarima == 'CONTE') prefijo_LP = 'CT';
                this.articulo.pallet = prefijo_LP+"00"+this.con.id+""+window.app.folio_recepcion+""+prefijo_lp_cerrar;
            }
            else this.articulo.pallet = lp_asignado;

            this.articulo.num_pedimento = $("#num_pedimento_inter").val();
            this.articulo.fecha_pedimento = $("#fecha_pedimento_inter").val();

            console.log("PALLET AUTOMATICO = ", this.articulo.pallet);

            $("#num_pedimento_inter").val("");
            $("#fecha_pedimento_inter").val("");
            //if(this.articulo.volumen > this.con.volu && this.articulo.contenedor != "")
            //{
            //   swal("Error","El volumen es mayor al permitido", "error");
            //   return;
            //}

            console.log("******************************");
            console.log("U Caja = ", $("#ucaja_val").val());
            console.log("******************************");

            //if(this.articulo.unidad_medida == "")
            if($("#umed").val() == "")
            {
                swal("Error","Debe seleccionar una Unidad de Medida", "error");
                return;
            }

            if($("#ucaja_val").val() > 0 && $("#c_peso_val").val() == 'N' && $("#umed").val() == "XBX")
            {
                this.articulo.cantidad_por_recibir = $("#cantidad_recibida").val();
                //swal("Error","Debe seleccionar una Unidad de Medida", "error");
                //return;
            }


//********************************************************************************************
                //this.articulo.multiplo = 18;
                this.articulo.n_cajas = (this.articulo.multiplo == 1 || this.articulo.multiplo == 0 || this.articulo.multiplo == "")?(0):(parseInt(this.articulo.cantidad_por_recibir/this.articulo.multiplo));
                this.articulo.n_piezas = (this.articulo.multiplo == 1 || this.articulo.multiplo == 0 || this.articulo.multiplo == "")?(this.articulo.cantidad_por_recibir):((this.articulo.cantidad_por_recibir)-(parseInt(this.articulo.cantidad_por_recibir/this.articulo.multiplo)*this.articulo.multiplo));
                console.log("********************************************************************************************");
                console.log("this.articulo = ", this.articulo);
                console.log("********************************************************************************************");
//********************************************************************************************

            if(this.ya_recibido(this.articulo.cve_articulo, this.articulo.contenedor, this.articulo.pallet))// && !$("#cerrar_pallet").is(":checked")
            {
               swal("Éxito","El artículo "+"("+this.articulo.clave_articulo+") ha sido actualizado correctamente", "success");

                $(".options_checkbox input, #almacen_select, #orden_de_compra_check").prop("disabled", true);
                $("#cantidad_max").val(parseFloat($("#cantidad_max").val())-parseFloat($("#a_recibir").val()));
                console.log("Resta ", $("#cantidad_max").val(), " de ", this.articulo.cve_articulo);
                if($("#cantidad_max").val() == '0')
                {
                    $("#articulos_lista option[value='"+this.articulo.cve_articulo+"']").remove();
                }

               this.actualizar_articulo(this.articulo.cve_articulo);
               this.reset_articulo();

               return;
            }


            if(this.articulo.acepto_entrada_articulo == 1)
            {
                   console.log("voy a actualizar articulo");
                   console.log("#cantidad_max = ", $("#cantidad_max").val());
                   console.log("#a_recibir = ", $("#a_recibir").val());
                   this.articulo.costo_total = this.costo_calculado;
                   //var cve_art = this.articulo.clave_articulo;
                   if(this.tipo_entrada == 2)
                        this.articulo.clave_articulo = this.articulo.cve_articulo;
                   swal("Éxito","El artículo "+"("+this.articulo.clave_articulo+") ha sido agregado correctamente", "success");
                   this.articulos_recibidos.push(this.clone(this.articulo));
                   //this.articulos_recibidos.push(this.articulo);

                    $(".options_checkbox input, #almacen_select, #orden_de_compra_check").prop("disabled", true);
                    $("#cantidad_max").val(parseFloat($("#cantidad_max").val())-parseFloat($("#a_recibir").val()));
                    console.log("Resta ", $("#cantidad_max").val(), " de ", this.articulo.clave_articulo);

                    if(parseFloat(this.articulo.cantidad_pedida*this.articulo.multiplo)-$("#cantidad_max").val() == '0')
                    {
                        $("#articulos_lista option[value='"+this.articulo.cve_articulo+"']").remove();
                    }

                   this.actualizar_articulo(this.articulo.cve_articulo);

                   this.reset_articulo();          
            }
            else
            {
                swal("Serie ya existente","El número de serie ya existe", "error");
            }

       },
      
       comparativa: function()
       {
           this.articulo.maneja_lotes = 0;
           this.articulo.maneja_caducidad = 0;
           this.articulo.maneja_series = 0;
           this.articulo.maneja_peso = 0;
           this.articulo.maneja_contenedor = 0;

            var val_articulo = this.articulo.cve_articulo;
            console.log("----------------------------------------");
            console.log("----------------------------------------");
            console.log("val_articulo = ", val_articulo);
            console.log("this.articulos_con_series = ", this.articulos_con_series);
            console.log("this.articulos_con_lotes = ", this.articulos_con_lotes);
            console.log("----------------------------------------");
            console.log("----------------------------------------");
            var array_val_lp = val_articulo.split(":::::");
            var array_val = array_val_lp[0].split(";;;;;");

           for (var i = 0; i < this.articulos_con_lotes.length; i++) 
           {
               if(this.articulos_con_lotes[i].cve_articulo == array_val[0])
               {
                    this.articulo.maneja_lotes = 1;
                    if(this.articulos_con_lotes[i].Caduca == 'S')
                    {
                        this.articulo.maneja_caducidad = 1;
                    }
               }
          }
          for (var i = 0; i < this.articulos_con_series.length; i++) 
          {
              if(this.articulos_con_series[i].cve_articulo == array_val[0])
              {
                  this.articulo.maneja_series = 1;
                  this.articulo.cantidad_por_recibir = 1;
              }
          }
          for (var i = 0; i < this.articulos_con_peso.length; i++) 
          {
              if(this.articulos_con_peso[i].cve_articulo == array_val[0])
              {
                  this.articulo.maneja_peso = 1;
              }
          }
          for (var i = 0; i < this.contenedores.length; i++) 
          {
              if(this.contenedores[i].clave_contenedor == this.articulo.contenedor)
              {
                  this.articulo.maneja_contenedor = 1;
              }
          }
      },
      
      revisar_lote_existente(lote)
      {
          this.articulo.se_crea_lote = 0;
          this.lote_existente = '';
          this.articulo.caducidad  = '';
          for(var i = 0; i < this.lotes.length; i++)
          {
              if(lote == this.lotes[i].LOTE && this.lotes[i].cve_articulo == this.articulo.cve_articulo)
              {
                  if(this.articulo.maneja_lotes == 1)
                  {
                      this.lote_existente = "Este Lote ya existe, el producto "+"("+this.articulo.cve_articulo+")"+"'"+this.articulo.descripcion+"'"+" sera añadido a ese lote";
                  }
                  if(this.articulo.maneja_series == 1)
                  {
                      this.lote_existente = "Esta serie ya existe, es necesario colocar una serie diferente";
                  }
                  if(this.articulo.maneja_caducidad == 1)
                  {
                      this.articulo.caducidad = this.lotes[i].Caducidad;
                  }
              }
          }
          if(this.lote_existente =="")
          {
              this.articulo.se_crea_lote = 1;
              console.log("Se creara lote", this.articulo.se_crea_lote);
          }
      },
      
      borrar_articulo_recibido(cve_articulo)
      {
          for(var i = this.articulos_recibidos.length - 1; i >= 0; i--) 
          {
              if(this.articulos_recibidos[i].cve_articulo == cve_articulo) 
              {
                  this.articulos_recibidos.splice(i, 1);
              }
          }
      },
      
      traer_hora_actual: function()
      {
          $.ajax({
              type: "POST",
              dataType: "json",
              url: '/api/ordendecompra/update/index.php',
              data: {
                  action: 'hora_actual',
              },
              beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
              },
              success: function(data) 
              {
                console.log("Recibir Artículo = ", data);
                  if(data.success == true) 
                  {
                      window.app.hora_actual = data.hora_actual;
                      window.app.recibir_un_articulo();
                      $("#cerrar_pallet").prop("checked", false);
                  }
              },
              error: function(data) 
              {
                  window.console.log(data);
              }
          });
      },
      traer_hora_fin: function()
      {
          $.ajax({
              type: "POST",
              dataType: "json",
              url: '/api/ordendecompra/update/index.php',
              data: {
                  action: 'hora_actual',
              },
              beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
              },
              success: function(data) 
              {
                  if(data.success == true) 
                  {
                      window.app.hora_actual = data.hora_actual;
                      window.app.guardar_entrada();
                  }
              },
              error: function(data) 
              {
                  window.console.log(data);
              }
          });
      },

      traer_folio_R: function()
      {
          $.ajax({
              type: "POST",
              dataType: "json",
              url: '/api/ordendecompra/update/index.php',
              data: {
                  action: 'traer_folio_R',
              },
              beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
              },
              success: function(data) 
              {
                  if(data.success == true) 
                  {
                      console.log("folio libre", data.data)
                      window.app.folio_recepcion = data.data;
                  }
              },
              error: function(data) 
              {
                  window.console.log(data);
              }
          });
      },

      actualizar_articulo(cve_articulo)
      {
        console.log("Entró en actualizar articulo");

        var val_articulo = cve_articulo;
        console.log("val_articulo = ", val_articulo);
        var array_val_lp = val_articulo.split(":::::");
        var array_val = array_val_lp[0].split(";;;;;");
        //var array_val = val_articulo.split(";;;;;");

        console.log("cve_articulo = ", array_val[0]);
        console.log("this.articulos = ", this.articulos);
  /*  
          if(this.articulos.length == 0)
          {
            for(var i = 0; i < window.app.articulos_recibidos.length; i++)
            {
                window.app.articulos_recibidos[i].n_cajas = (window.app.articulos_recibidos[i].multiplo == 1 || window.app.articulos_recibidos[i].multiplo == 0 || window.app.articulos_recibidos[i].multiplo == "")?(0):(parseInt(window.app.articulos_recibidos[i].cantidad_por_recibir/window.app.articulos_recibidos[i].multiplo));
                window.app.articulos_recibidos[i].n_piezas = (window.app.articulos_recibidos[i].multiplo == 1 || window.app.articulos_recibidos[i].multiplo == 0 || window.app.articulos_recibidos[i].multiplo == "")?(window.app.articulos_recibidos[i].cantidad_por_recibir):((window.app.articulos_recibidos[i].cantidad_por_recibir)-(parseInt(window.app.articulos_recibidos[i].cantidad_por_recibir/window.app.articulos_recibidos[i].multiplo)*window.app.articulos_recibidos[i].multiplo));
            }
          }
*/

        console.log("articulos_recibidos[",333,"] arr = ", window.app.articulos_recibidos);

          for(var i = 0; i < this.articulos.length; i++)
          {

                if(this.articulos[i].cve_unidad_medida == 'XBX') this.articulos[i].cantidad = parseFloat(this.articulos[i].cantidad)*parseFloat(this.articulos[i].num_multiplo);



              if(this.articulos[i].cve_articulo == array_val[0])
              {
/*
                console.log("articulos_recibidos[",i,"] arr = ", window.app.articulos_recibidos[i]);
                console.log("this.articulos[i].cve_articulo = ", this.articulos[i].cve_articulo);
                console.log("cve_articulo arr = ", this.articulos[i]);
                console.log("this.articulos[i].cantidad = ", this.articulos[i].cantidad);
                console.log("this.articulo.cantidad_por_recibir = ", this.articulo.cantidad_por_recibir);

                window.app.articulos_recibidos[i].multiplo = this.articulos[i].num_multiplo;
                window.app.articulos_recibidos[i].n_cajas = (window.app.articulos_recibidos[i].multiplo == 1 || window.app.articulos_recibidos[i].multiplo == 0 || window.app.articulos_recibidos[i].multiplo == "")?(0):(parseInt(window.app.articulos_recibidos[i].cantidad_por_recibir/window.app.articulos_recibidos[i].multiplo));
                window.app.articulos_recibidos[i].n_piezas = (window.app.articulos_recibidos[i].multiplo == 1 || window.app.articulos_recibidos[i].multiplo == 0 || window.app.articulos_recibidos[i].multiplo == "")?(window.app.articulos_recibidos[i].cantidad_por_recibir):((window.app.articulos_recibidos[i].cantidad_por_recibir)-(parseInt(window.app.articulos_recibidos[i].cantidad_por_recibir/window.app.articulos_recibidos[i].multiplo)*window.app.articulos_recibidos[i].multiplo));
*/
                if(array_val.length == 1)
                {
                  this.articulos[i].cantidad = parseFloat(this.articulos[i].cantidad) - parseFloat(this.articulo.cantidad_por_recibir);
                  if(this.articulos[i].cantidad == 0)
                  {
                    console.log("COLOR 1");
                      this.articulos[i].color_1 = 1;
                      this.articulos[i].color_2 = 1;
                      //$("#articulos_lista option[value='"+cve_articulo+"']").remove();
                  }
                  else
                  {
                        console.log("COLOR 2");
                      this.articulos[i].color_1 = 1;
                      this.articulos[i].color_2 = 0;
                  }
                }
                else if(this.articulos[i].cve_lote == array_val[2] && this.articulos[i].lp == array_val_lp[1])
                {
                  this.articulos[i].cantidad = parseFloat(array_val[1]) - parseFloat(this.articulo.cantidad_por_recibir);
                  if(this.articulos[i].cantidad == 0)
                  {
                    console.log("COLOR 3");
                      this.articulos[i].color_1 = 1;
                      this.articulos[i].color_2 = 1;
                      //$("#articulos_lista option[value='"+cve_articulo+"']").remove();
                  }
                  else
                  {
                    console.log("COLOR 4");
                      this.articulos[i].color_1 = 1;
                      this.articulos[i].color_2 = 0;
                  }
                }
                else if(this.articulos[i].cve_lote == '' && this.articulos[i].lp == array_val_lp[1])
                {
                  this.articulos[i].cantidad = parseFloat(array_val[1]) - parseFloat(this.articulo.cantidad_por_recibir);
                  if(this.articulos[i].cantidad == 0)
                  {
                    console.log("COLOR 5");
                      this.articulos[i].color_1 = 1;
                      this.articulos[i].color_2 = 1;
                      //$("#articulos_lista option[value='"+cve_articulo+"']").remove();
                  }
                  else
                  {
                    console.log("COLOR 6");
                      this.articulos[i].color_1 = 1;
                      this.articulos[i].color_2 = 0;
                  }
                }
                else if(this.articulos[i].cve_lote == array_val[2])
                {
                  this.articulos[i].cantidad = parseFloat(array_val[1]) - parseFloat(this.articulo.cantidad_por_recibir);
                  if(this.articulos[i].cantidad == 0)
                  {
                    console.log("COLOR 7");
                      this.articulos[i].color_1 = 1;
                      this.articulos[i].color_2 = 1;
                      //$("#articulos_lista option[value='"+cve_articulo+"']").remove();
                  }
                  else
                  {
                    console.log("COLOR 8");
                      this.articulos[i].color_1 = 1;
                      this.articulos[i].color_2 = 0;
                  }
                }

              }
          }
          var j = 0;
          console.log("entrar");
          for(var i = 0; i < this.articulos.length; i++)
          { 
              console.log("articulos",this.articulos.length);
              j+= this.articulos[i].cantidad; 
          }
          console.log(j);
          if(j == 0 && this.tipo_entrada == 1)
          {
              console.log("guardar");
              this.guardar_entrada();
          }
      },
      
      ya_recibido(cve_articulo, contenedor, pallet)
      {
        console.log("ya_recibido");
        //console.log("this.articulo", this.articulo);
        //console.log("this.articulos_recibidos", this.articulos_recibidos);
          for(var i = 0; i < this.articulos_recibidos.length; i++)
          {
              if(this.articulos_recibidos[i].cve_articulo == cve_articulo && this.articulos_recibidos[i].contenedor == contenedor && this.articulos_recibidos[i].pallet == pallet)
              {
                  if(this.articulos_recibidos[i].lote == this.articulo.lote && this.articulo.serie == "")
                  {
                      this.articulo.costo_total = this.costo_calculado;
                      this.articulos_recibidos[i].cantidad_por_recibir = parseFloat(this.articulos_recibidos[i].cantidad_por_recibir) + parseFloat(this.articulo.cantidad_por_recibir);
                      this.articulos_recibidos[i].costo_total = parseFloat(this.articulos_recibidos[i].costo_total) + parseFloat(this.articulo.costo_total);
/*
                    this.articulo.n_cajas = (this.articulo.multiplo == 1 || this.articulo.multiplo == 0 || this.articulo.multiplo == "")?(0):(parseInt(this.articulo.cantidad_por_recibir/this.articulo.multiplo));
                    this.articulo.n_piezas = (this.articulo.multiplo == 1 || this.articulo.multiplo == 0 || this.articulo.multiplo == "")?(this.articulo.cantidad_por_recibir):((this.articulo.cantidad_por_recibir)-(parseInt(this.articulo.cantidad_por_recibir/this.articulo.multiplo)*this.articulo.multiplo));
*/
                    this.articulos_recibidos[i].n_cajas = parseFloat(this.articulos_recibidos[i].n_cajas)+parseFloat(this.articulo.n_cajas);
                    this.articulos_recibidos[i].n_piezas = parseFloat(this.articulos_recibidos[i].n_piezas)+parseFloat(this.articulo.n_piezas);
                      return true;
                  }
              }
          }
          return false;
      },
      
      colores_tabla: function()
      {
          for(var i = 0; i < this.articulos.length; i++)
          {
              this.articulos[i].color_1 = 0;
              this.articulos[i].color_2 = 0;
          }
      },
      guardar_entrada: function()
      {
          var almacen = this.almacen_principal;
          var oc = this.orden.id;
          var tipo;
          var activos_fijos = [];
          if(this.tipo_entrada == 1) {tipo = "OC";}
          if(this.tipo_entrada == 2) {tipo = "RL";}
          if(this.tipo_entrada == 3) {tipo = "CD";}

          console.log("Tipo", tipo);
        
          var sum = 0;
          for(var i = 0; i < this.articulos_recibidos.length; i++)
          {
              if(this.articulos.tipo_producto === 'Activo Fijo')
              {
                  var total_activos = activos_fijos.push(this.articulos_recibidos[i]);
              }
              else
              {
                  console.log("peso1",this.articulos_recibidos[i].peso);
                  sum+= parseFloat(this.articulos_recibidos[i].peso);
                  console.log("sum",sum);
                  console.log("pesmax",this.articulos_recibidos[i].pesomax);
                  if(sum > parseFloat(this.articulos_recibidos[i].pesomax))
                  {
                      console.log("pes2");
                      swal("Error", "El peso es mayor al soportado por la caja, elimine articulos", "error");
                      return;
                  }
              }
              
          }
        
          if(this.articulos_recibidos.length >= 1)
          {
              if(tipo != "")
              {
                var arrDetalle;
                if (oc === '' && !this.tipo_entrada == 2) 
                {
                  swal("Error", "Seleccione una OC primero", "error");
                  return;
                }
                else if(this.tipo_entrada == 3 && $("#pedidos_cross").val() == '')
                {
                  swal("Error", "Seleccione un Pedido tipo CrossDocking", "error");
                  return;
                }
                else //if(this.tipo_protocolo == 0)
                {
                    if(this.tipo_entrada == 2 || this.tipo_entrada == 3) oc = $("#num_oc").val();
                    swal({
                      title: 'Advertencia',
                      text: '¿Está seguro que desea guardar la Recepcion? Ésta acción no se puede deshacer.',
                      type: 'warning',
                      showConfirmButton: true,
                      confirmButtonText: 'Sí',
                      showCancelButton: true,
                      cancelButtonText: 'No',
                      closeOnConfirm: true,
                      closeOnCancel: true
                    }, 
                    function(confirm) 
                    {
                        console.log("confirm = "+confirm);
                        if (confirm) 
                        {
                            console.log("Cve_Almac: "+almacen+" - oc: "+oc+" - tipo: "+tipo+" - arrDetalle: "+window.app.articulos_recibidos+" - Cve_Usuario: "+window.app.orden.clave_usuario+" - usuario: "+window.app.orden.clave_usuario+" - Cve_Proveedor: "+window.app.proveedor.id+" - fechafin: "+window.app.hora_actual+" - STATUS: "+"E"+" - Fact_Prov: "+$("#facprove").val()+" - Contenedor:", window.app.con.clave_contenedor);
                            console.log("window.app.articulos_recibidos = ", window.app.articulos_recibidos);

                            window.app.articulos_recibidos.forEach(function(value, key){
                            if(window.app.articulos_recibidos[key].serie != "")
                            {
                              window.app.articulos_recibidos[key].lote = window.app.articulos_recibidos[key].serie;
                            }
                            if(window.app.articulos_recibidos[key].zona_recepcion != "")
                            {
                              window.app.articulos_recibidos[key].zona_recepcion = window.app.zona.cve_ubicacion;
                              console.log("zonaass",window.app.articulos_recibidos[key].zona_recepcion);
                            }

                            //window.app.articulos_recibidos[key].lp_selected = (window.app.lp_selected=='')?(window.app.articulos_recibidos[key].lp):(window.app.lp_selected);
                            window.app.articulos_recibidos[key].lp_selected = (window.app.articulos_recibidos[key].pallet=='')?(window.app.articulos_recibidos[key].lp):(window.app.articulos_recibidos[key].pallet);
                            //if ( $(".num_pedimento_inter")[0] ) {
                            //window.app.articulos_recibidos[key].num_pedimento_int = $(".num_pedimento_inter").eq(key)[0].value;
                            //window.app.articulos_recibidos[key].fecha_pedimento_int = $(".fecha_pedimento_inter").eq(key)[0].value;
                            //window.app.articulos_recibidos[key].num_pedimento_int = $(".num_pedimento_inter").eq(key)[0].value;
                            //window.app.articulos_recibidos[key].fecha_pedimento_int = $(".fecha_pedimento_inter").eq(key)[0].value;

                            //}
                         });

                        console.log("window.app.articulos_recibidos = ", window.app.articulos_recibidos);
                        console.log("************************************************************");
                        console.log("folio_entrada = ", window.app.folio_recepcion);
                        console.log("folio_oc = ", oc);
                        console.log("tipo_entrada = ", tipo);
                        console.log("proveedor = ", window.app.proveedor.id);
                        console.log("empresa = ", $("#empresa_proveedor").val());

                        console.log("************************************************************");

                        //return;

                         $.ajax({
                            method: 'POST',
                            dataType: 'json',
                            data: {
                                action: 'receiveOC',
                                Cve_Almac: almacen,
                                oc: oc,
                                folio_recepcion: window.app.folio_recepcion,
                                tipo: tipo,
                                folio_xd: $("#pedidos_cross").val(),
                                empresa: $("#empresa_proveedor").val(),
                                arrDetalle: window.app.articulos_recibidos,
                                Cve_Usuario: window.app.orden.clave_usuario,
                                usuario: window.app.orden.clave_usuario,
                                Cve_Proveedor: window.app.proveedor.id,
                                ID_Protocolo: $("#protocolo").val(),
                                Consec_protocolo: $("#cprotocolo").val(),
                                //proveedorID: $("#proveedorID").val(),
                                fechafin: window.app.hora_actual,
                                STATUS: "E",
                                claveproyecto: $("#claveproyecto").val(),
                                Fact_Prov: $("#facprove").val()
                            },
                            beforeSend: function(x) {
                                if (x && x.overrideMimeType) 
                                {
                                    x.overrideMimeType("application/json;charset=UTF-8");
                                }
                            },
                            url: '/api/ordendecompra/update/index.php',
                            success: function(data) {
                                console.log("Entró en data.success out", data);
                                    if (data.success == true) 
                                    {
                                          console.log("Entró en data.success");
                                          //if(tipo == 'OC' && data.instanciasap == true)
                                          //{
                                          //      window.app.OCSap(window.app.folio_recepcion, oc);
                                          //}
                                          if(tipo != 'CD')
                                          {
                                              swal({
                                                title: 'Éxito',
                                                text: `¡La Recepción #${oc} se ha recibido correctamente`,
                                                type: 'success',
                                                showConfirmButton: true,
                                                confirmButtonText: 'Aceptar',
                                                showCancelButton: false,
                                                closeOnConfirm: true,
                                                closeOnCancel: false
                                              }, 
                                              function() 
                                              {
                                                  console.log("Reload...");
                                                  window.location.reload();
                                              });
                                          }
                                          else
                                            obtenerZonasDisponibles(oc);
                                    }
                                    else if(data.success == 'NOXD')
                                    {
                                        swal("Error", "Las Cantidades de la OC deben ser mayores o iguales a las del pedido tipo CrossDocking a relacionar con la entrada", "error");
                                    }
                                    else 
                                    {
                                        swal("Error", "Ocurrió un error al guardar los datos", "error");
                                    }
                            },
                            error: function(res) {
                                window.console.log(res);
                            }
                            }); /*.done(function(data) 
                            {
                                console.log("data.success = "+data.success);
                              if(data.success) 
                              {
                                console.log("Entró en data.success");
                              swal({
                                title: 'Éxito',
                                text: `¡La Recepción #${oc} se ha recibido correctamente`,
                                type: 'success',
                                showConfirmButton: true,
                                confirmButtonText: 'Aceptar',
                                showCancelButton: false,
                                closeOnConfirm: true,
                                closeOnCancel: false
                              }, 
                              function() 
                              {
                                  window.location.reload();
                              });
                              } 
                              else 
                              {
                                  swal("Error", "Ocurrió un error al guardar los datos", "error");
                              }
                      });*/
                    }
                  });
                }
              }
            }
            if(activos_fijos.length >= 1)
            {
                $.ajax({
                    method: 'POST',
                    dataType: 'json',
                    url: '/api/ordendecompra/update/index.php',
                    data: {
                        action: 'activos_fijos',
                        activos: activos_fijos,
                    }
                    });
            }
          },

    OCSap(folio_entrada, folio_oc){
        console.log("folio_oc = ", folio_oc);
        console.log("folio_entrada = ", folio_entrada);

      $.ajax({
        type: "POST",
        dataType: "json",
        url: '/api/adminentrada/update/index.php',
        data: 
        {
          action: 'ConectarSAP',
          funcion: 'Login',
          metodo: 'POST',
          folio_oc: folio_oc
        },
        beforeSend: function(x){if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
        },
        success: function(data) 
        {
            console.log("SUCCESS", data);
            //return;
            if(data.SessionId)
            {
                console.log("SessionId OK:", data.SessionId);
                    //return;
                  $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: '/api/adminentrada/update/index.php',
                    data: 
                    {
                      action: 'EjecutarOCSAP',
                      pedimento_oc: '',
                      fecha_pedimento_oc: '',
                      tipo_cambio: '1',
                      folio_entrada: folio_entrada,
                      funcion: 'PurchaseDeliveryNotes',
                      //funcion: 'Drafts',
                      metodo: 'POST',
                      folio_oc: folio_oc,

                      sesion_id: data.SessionId
                    },
                    beforeSend: function(x){if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                    },
                    success: function(data) 
                    {
                      console.log("SUCCESS EjecutarOCSAP", data);
                      if(data.error)
                      {
                         swal("Error", data.error.message.value, "error");
                      }
                      else
                      {
                         swal("Envío Correcto", "Actualización a ERP Correcta", "success");
                         /*RETURN PARA SOLO DEVOLVER JSON*/ 
                         //return;
                         $("#enviarSAP").modal('hide');
                          $.ajax({
                            type: "POST",
                            dataType: "json",
                            url: '/api/adminentrada/update/index.php',
                            data: 
                            {
                              action: 'CambiarAEnviadoOCSAP',
                              folio_entrada: folio_entrada,
                              folio_oc: folio_oc,
                              sesion_id: data.SessionId
                            },
                            beforeSend: function(x){if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                            },
                            success: function(data) 
                            {
                                    ReloadGrid();
                            }, error: function(data) 
                            {
                              console.log("ERROR EjecutarOCSAP", data);
                            }
                          });

                      }
                    }, error: function(data) 
                    {
                      console.log("ERROR EjecutarOCSAP", data);
                    }
                  });
            }
            else
            {
                console.log("SessionId ERROR:", data);
                swal("Error", "No hay Conexión a SAP", "error");
            }


        }, error: function(data) 
        {
          console.log("ERROR SAP", data);
        }
      });

    },
      crear_lote(cve_articulo, lote, caducidad)
      {
          var lote = window.app.articulo.lote, ls = '';
          //if(lote == ""){lote = window.app.articulo.serie; ls = '';}
          if(window.app.articulo.lote != ""){ls = 'L';}
          if(window.app.articulo.serie != ""){ls = 'S';}
          console.log("Lote",lote);
          console.log("Lote - L",window.app.articulo.lote);
          console.log("Lote - S",window.app.articulo.serie);
/*
            $.ajax({
                type: "POST",
                dataType: "json",
                async: false,
                cache: false,
                url: '/api/lotes/update/index.php',
                data: {
                    action: "add",
                    cve_articulo: window.app.articulo.cve_articulo,
                    LOTE:lote,
                    CADUCIDAD: window.app.articulo.caducidad,
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    console.log("GUARDE LOTE NUEVO");
                }, error: function(data)
                {
                    console.log("Error Lote = ", data);
                }
            });
*/
            console.log("EXISTE LOTE VERIFICAR lote_serie: = ", ls);
            console.log("EXISTE LOTE VERIFICAR lote: = ", lote);
                

            $.ajax({
                type: "POST",
                dataType: "json",
                async: false,
                cache: false,
                url: '/api/lotes/update/index.php',
                data: {
                    action: "ExisteLote",
                    lote_serie: ls,
                    lote:lote
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) 
                {
                    console.log("LOTE/SERIE EXISTE Si/No = ", data);
                    if(ls == 'L')
                    {
                        if(data.res.length > 0)
                        {
                            console.log("LOTE = ", data.res[0].Lote);
                            console.log("CADUCIDAD = ", data.res[0].Caducidad);
                            window.app.articulo.acepto_entrada_articulo = 1;
                            if(data.res[0].Caducidad != '0000-00-00')
                            {
                                window.app.articulo.caducidad = data.res[0].Caducidad;
                                /*
                                setTimeout(function(){
                                    swal("Lote Existente", "El lote "+lote+" ya existe, se usará la caducidad "+data.res[0].Caducidad+" ya asignada", "success");
                                }
                                ,2000);
                                */
                            }
                        }
                    }
                    else
                    {
                        if(data.res.length > 0)
                        {
                            console.log("SIZE = ", data.res.length);
                            console.log("SERIE = ", data.res[0].numero_serie);
                            window.app.articulo.acepto_entrada_articulo = 0;
                        }
                        else
                        {
                            console.log("SIZE = ", 0);
                            console.log("SERIE = ");
                            window.app.articulo.acepto_entrada_articulo = 1;
/*
                            $(".n_series").each(function(index, value){

                                console.log("n_series index = ", index);
                                console.log("n_series value = ", $(this).text());

                                if($(this).text() == lote)
                                    window.app.articulo.acepto_entrada_articulo = 0;

                            });
*/
                        }
                        if(ls == '')
                            window.app.articulo.acepto_entrada_articulo = 1;
                    }
                    
                }, error: function(data)
                {
                    console.log("Error Lote = ", data);
                }
            });


            return true;
          },

        set_protocolo: function() {
        var ID_Protocolo = $("#protocolo").val();
        console.log("Protocolo = ", ID_Protocolo);
        console.log("Protocolo Texto = ", $( "#protocolo option:selected" ).text());

        var str = $( "#protocolo option:selected" ).text();
        var strPos = str.indexOf("Inter");

        if(strPos >= 0)
        {
            console.log("Protocolo Res = InterNacional");
            this.tipo_protocolo = 1;
        }
        else
        {
            console.log("Protocolo Res = Nacional");
            this.tipo_protocolo = 0;
        }



        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                ID_Protocolo: ID_Protocolo,
                action: "getConsecutivo"
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) 
                {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/ordendecompra/update/index.php',
            success: function(data) {
                console.log("PROTOCOLO = ", data);
                $('#cprotocolo').val(data.consecutivo);
            }, error: function(data){
                console.log("ERROR PROTOCOLO", data);
            }

        });
    },

      recibir_todo: function()
      {
          this.articulos_recibidos = this.articulos;
          for(var i = 0; i < this.articulos.length; i++)
          {
              this.articulos_recibidos[i].cve_articulo = this.articulos[i].cve_articulo;
              this.articulos_recibidos[i].descripcion = this.articulos[i].nombre_articulo;
              this.articulos_recibidos[i].serie = "";
              this.articulos_recibidos[i].lote = "";
              this.articulos_recibidos[i].caducidad = "";
              this.articulos_recibidos[i].cantidad_por_recibir = parseFloat(this.articulos[i].cantidad);
              this.articulos_recibidos[i].zona_recepcion = this.zona.desc_ubicacion;
              this.articulos_recibidos[i].contenedores = "";
              this.articulos_recibidos[i].license_plates = "";
              this.articulos_recibidos[i].clave_usuario = "";
              this.articulos_recibidos[i].costo_total = "";
              this.articulos_recibidos[i].hora_de_recepcion = "";
              this.articulos_recibidos[i].tipo = this.articulos[i].tipo_producto;
              if(this.articulos_recibidos[i].cantidad_por_recibir !="")
              {
                  console.log(parseFloat(this.articulos[i].cantidad));
                  this.actualizar_articulo(this.articulos[i].cve_articulo);
              }
          }
      }
  },
  beforeMount()
  {
      this.set_almacenprede();
      this.traer_almacenes();
      this.traer_ordenes();
      //this.traer_crossdocking();
      this.traer_folio_R();
      this.traer_proveedores();
      this.traer_contenedores();
      this.traer_lps();
      this.set_usuario();
      this.traer_articulos();
      this.traer_lotes();
      //this.traer_medidas();
      this.orden_reset = this.clone(this.orden);
      this.set_protocolo();
      //$('.chosen-select').chosen();
      //$(".chosen-select").trigger("chosen:updated");
      //$('.chosen-select').chosen();
      //$(".chosen-select").chosen("destroy").chosen();//.trigger("chosen:updated");

      //console.log("chosen-select 3");



  }


}); 
//$(".chosen-select").trigger("chosen:updated");
//console.log("chosen-select 4");
/*
    $('.chosen-list').select2({
        theme: "bootstrap4",
        selectOnClose: true
    });
*/

var actualizar = false;
setTimeout(function(){
//console.log("ALMACEN = ", $("#almacen_select").eq(1).val());
$("#almacen_select option").each(function(index, i){
    //console.log("OK ALMACEN", index, i.value);
    //console.log("ACTUALIZAR ALMACEN");
    actualizar = false;
    if(i.value == "") actualizar = true;
});

if(actualizar == false)
    console.log("OK ALMACEN");
else
{
    console.log("ACTUALIZAR ALMACEN");
    window.location.reload();
}

}, 3000);
</script>