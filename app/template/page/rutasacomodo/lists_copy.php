<?php
$listaAlm = new \Almacen\Almacen();
?>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/iCheck/custom.css" rel="stylesheet">
<link href="/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css" rel="stylesheet">
<link href="/css/plugins/summernote/summernote.css" rel="stylesheet">

<link rel="stylesheet" href="/css/plugins/acordion/reset.css"> <!-- CSS reset -->
<link rel="stylesheet" href="/css/plugins/acordion/style.css"> <!-- Resource style -->

<style>
    #list {
        width: 100%;
        height: 100%;
        position: absolute;
        left: 0px;
        z-index: 999;
    }

    #FORM,#search {
        width: 100%;
        /*height: 100%;*/
        position: absolute;
        left: 0px;
        z-index: 999;
    }
</style>

<div class="wrapper wrapper-content  animated " id="list">

    <h3>Posición de Zona de Almacenaje</h3>

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="input-group">
                                <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                <div class="input-group-btn">
                                    <a href="#" onclick="ReloadGrid()">
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            Buscar
                                        </button>
                                    </a>
                                </div>
                            </div>

                        </div>
                        <div class="col-sm-8">
                            <a href="#" onclick="agregar()"><button class="btn btn-primary pull-right" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>
                        </div>
                    </div>
                </div>
                <div class="ibox-content">

                    <div class="jqGrid_wrapper">
                        <table id="grid-table"></table>
                        <div id="grid-pager"></div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<div class="wrapper wrapper-content  animated fadeInRight" id="FORM" style="display: none">

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4" id="_title">
                            <h3>Agregar Posición de Zona de Almacenaje</h3>
                        </div>
                    </div>
                </div>
                <form id="myform">
                    <input type="hidden" id="hiddenAction" name="hiddenAction">
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-lg-6 b-r">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="Almacen">Zona de Almacenaje:</label>
                                            <select class="form-control" id="Almacen" name="Almacen">
                                                <option value="">Seleccione la Zona de Almacenaje</option>
                                                <?php foreach( $listaAlm->getAll() AS $p ): ?>
                                                    <option value="<?php echo $p->cve_almac; ?>"><?php echo $p->des_almac; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12"><div class="form-group"><label>Pasillo:</label> <input id="Pas" type="text" placeholder="Pasillo" class="form-control"></div></div>
                                </div>
                                <div class="row">
                                   <div class="col-sm-12">
                                        <div class="form-group">
                                            <label>Rack:</label>
                                            <input id="NRack" class="form-control" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" value="0" name="NRack">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-5">
                                        <div class="form-group">
                                            <div class="radio">
                                                <input type="radio" name="radio1" id="radio1" value="1" checked="checked">
                                                <label for="radio1">
                                                    Niveles:
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-7">
                                        <div class="form-group">
                                            <input id="NNivel" class="form-control" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" value="0" name="NNivel">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-5">
                                        <div class="form-group">
                                            <div class="radio">
                                                <input type="radio" name="radio1" id="radio2" value="2">
                                                <label for="radio2" class="pull-left">
                                                    Rango de Nivel:
                                                </label>
                                                <span class="pull-right">Del</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3"><div class="form-group"><input id="Nini" class="form-control" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" type="text" value="0" name="demo1"></div></div>
                                    <div class="col-sm-1"><div class="form-group"><label style="margin-top:5px;">Al</label></div></div>
                                    <div class="col-sm-3"><div class="form-group"><input id="NFin" class="form-control" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" value="0" name="demo1"></div></div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-5">
                                        <div class="form-group">
                                            <div class="radio">
                                                <input type="radio" name="radio2" id="radio3" value="1" checked="checked">
                                                <label for="radio3">
                                                    Secciones
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-7">
                                        <div class="form-group">
                                            <input id="NSec" class="form-control" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" type="text" value="0" name="NSec">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-5">
                                        <div class="form-group">
                                            <div class="radio">
                                                <input type="radio" name="radio2" id="radio4" value="2">
                                                <label for="radio4" class="pull-left">
                                                    Rango de Secciones:
                                                </label>
                                                <span class="pull-right">Del</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3"><div class="form-group"><input id="Sini" class="form-control" type="text" value="0" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" name="demo2"></div></div>
                                    <div class="col-sm-1"><div class="form-group"><label style="margin-top:5px;">Al</label></div></div>
                                    <div class="col-sm-3"><div class="form-group"><input id="SFin" class="form-control" type="text" value="0" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" name="demo2"></div></div>
                                </div>
                                <!-- <div class="form-group"><label>Secciones por Rack</label>
                                    <input id="NSec" class="form-control" type="text" value="0" name="NSec">
                                </div> -->
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="UNiv">Ubicaciones por Sección:</label>
                                            <input id="UNiv" class="form-control" type="text" value="0" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" name="UNiv">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group"><label>Alto (mm)</label>
                                            <input id="AlUbi" class="form-control" type="text" maxlength="4" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" value="0" name="demo1">
                                        </div>
                                    </div>
                                </div>
								<div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group"><label>Ancho (mm)</label> <input id="AnUbi"  maxlength="6" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" class="form-control" value="0"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group"><label>Fondo(mm)</label>
                                            <input id="LaUbi" class="form-control"  maxlength="6" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" type="text" value="0" name="demo1">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group"><label>Peso Máximo (kgs)</label> <input id="PMax"  maxlength="6" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" class="form-control" value="0"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <div class="radio">
                                                <input type="radio" name="ubica_rack" id="radio5" value="1" checked="checked">
                                                <label for="radio5">
                                                    Ubicación de Rack:
                                                </label>
                                            </div>
                                            <div class="radio">
                                                <input type="radio" name="ubica_rack" id="radio6" value="2">
                                                <label for="radio6">
                                                    Ubicación de Piso:
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label style="position:relative;top:10px;">
                                                Status:
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <div class="radio">
                                                <input type="radio" name="Tipo" id="radio7" value="L" checked="checked">
                                                <label for="radio7">
                                                    Libre
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <div class="radio">
                                                <input type="radio" name="Tipo" id="radio8" value="R">
                                                <label for="radio8">
                                                    Reservada
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <div class="radio">
                                                <input type="radio" name="Tipo" id="radio9" value="Q">
                                                <label for="radio9">
                                                    Cuarentena
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <div class="checkbox">
                                                <input type="checkbox" name="Pick" id="Pick" value="1">
                                                <label for="checkbox1">
                                                    Picking
                                                </label>
                                            </div>
                                            <div class="checkbox">
                                                <input type="checkbox" name="ubicaptl" id="ChkPTL" value="1">
                                                <label for="checkbox2">
                                                    Ubicación de PTL
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <div class="pull-right"><br>
                                                <a href="#" onclick="cancelar()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>
                                                <button type="button" class="btn btn-primary ladda-button" data-style="contract" id="btnSave">Guardar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" id="hiddenUbicacion">
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight" id="search" style="display: none">

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4" id="_title">
                            <h3>Productos de la Zona de Almacenaje <span id="title_almacen"></span></h3>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="input-group">
                                <input type="text" class="form-control input-sm" name="txtCriterio_alm_produ" id="txtCriterio_alm_produ" placeholder="Buscar...">
                                <div class="input-group-btn">
                                    <a href="#" onclick="ReloadGrid_alm_produ()">
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            Buscar
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ibox-content">

                    <div class="jqGrid_wrapper">
                        <table id="grid-table_alm_produ"></table>
                        <div id="grid-pager_alm_produ"></div>
                    </div>
                    

                    <div class="row">
                        <div class="pull-right"><br>
                            <a href="#" onclick="cerrar_search()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="wrapper wrapper-content  animated fadeInRight" id="DetalleUbicacion" style="display: none">

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-6" id="_title2">
                            <h3>Editar Posición de la Zona de Almacenaje</h3>
                        </div>
                    </div>
                </div>
                <form id="myform">
                    <input type="hidden" id="hiddenAction" name="hiddenAction">
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-lg-6 b-r">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="Almacen">Zona de Almacenaje:</label>
                                            <select class="form-control" id="Almacen2" name="Almacen2">
                                                <option value="">Seleccione la Zona de Almacenaje</option>
                                                <?php foreach( $listaAlm->getAll() AS $p ): ?>
                                                    <option value="<?php echo $p->cve_almac; ?>"><?php echo $p->des_almac; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12"><div class="form-group"><label>Pasillo:</label> <input id="Pas2" name="Pas2" type="text" placeholder="Pasillo" class="form-control"></div></div>
                                </div>
                                <div class="row">
                                   <div class="col-sm-12">
                                        <div class="form-group">
                                            <label>Rack:</label>
                                            <input id="NRack2" class="form-control" type="text" value="0" name="NRack2">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-5">
                                        <div class="form-group">
                                            <div class="radio">
                                                <input type="radio" name="radio10" id="radio10" value="1">
                                                <label for="radio10">
                                                    Niveles:
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-7">
                                        <div class="form-group">
                                            <input id="NNive2" class="form-control" type="text" value="0" name="NNive2">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-5">
                                        <div class="form-group">
                                            <div class="radio">
                                                <input type="radio" name="radio10" id="radio11" value="2">
                                                <label for="radio11" class="pull-left">
                                                    Rango de Nivel:
                                                </label>
                                                <span class="pull-right">Del</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3"><div class="form-group"><input id="Nini2" class="form-control" type="text" value="0" name="demo1"></div></div>
                                    <div class="col-sm-1"><div class="form-group"><label style="margin-top:5px;">Al</label></div></div>
                                    <div class="col-sm-3"><div class="form-group"><input id="NFin2" class="form-control" type="text" value="0" name="demo1"></div></div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-5">
                                        <div class="form-group">
                                            <div class="radio">
                                                <input type="radio" name="radio12" id="radio12" value="1">
                                                <label for="radio12">
                                                    Secciones
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-7">
                                        <div class="form-group">
                                            <input id="NSec2" class="form-control" type="text" value="0" name="NSec2">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-5">
                                        <div class="form-group">
                                            <div class="radio">
                                                <input type="radio" name="radio12" id="radio13" value="2">
                                                <label for="radio13" class="pull-left">
                                                    Rango de Secciones:
                                                </label>
                                                <span class="pull-right">Del</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3"><div class="form-group"><input id="Sini2" class="form-control" type="text" value="0" name="demo2"></div></div>
                                    <div class="col-sm-1"><div class="form-group"><label style="margin-top:5px;">Al</label></div></div>
                                    <div class="col-sm-3"><div class="form-group"><input id="SFin2" class="form-control" type="text" value="0" name="demo2"></div></div>
                                </div>
                                <!-- <div class="form-group"><label>Secciones por Rack</label>
                                    <input id="NSec" class="form-control" type="text" value="0" name="NSec">
                                </div> -->
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="UNiv">Ubicaciones por Sección:</label>
                                            <input id="UNiv2" class="form-control" type="text" value="0" name="UNiv2">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group"><label>Alto de Ubicación:</label>
                                            <input id="AlUbi2" class="form-control" type="text" value="0" name="demo1">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group"><label>Largo de Ubicación:</label>
                                            <input id="LaUbi2" class="form-control" type="text" value="0" name="demo1">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group"><label>Ancho de Ubicación:</label> <input id="AnUbi2" type="text" class="form-control" value="0"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group"><label>Peso Máximo:</label> <input id="PMax2" type="text" class="form-control" value="0"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <div class="radio">
                                                <input type="radio" name="ubica_rack2" id="radio14" value="1">
                                                <label for="radio14">
                                                    Ubicación de Rack:
                                                </label>
                                            </div>
                                            <div class="radio">
                                                <input type="radio" name="ubica_rack2" id="radio15" value="2">
                                                <label for="radio15">
                                                    Ubicación de Piso:
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label style="position:relative;top:10px;">
                                                Status:
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <div class="radio">
                                                <input type="radio" name="Tipo2" id="radio16" value="L">
                                                <label for="radio16">
                                                    Libre
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <div class="radio">
                                                <input type="radio" name="Tipo2" id="radio17" value="R">
                                                <label for="radio17">
                                                    Reservada
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <div class="radio">
                                                <input type="radio" name="Tipo2" id="radio18" value="Q">
                                                <label for="radio18">
                                                    Cuarentena
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <div class="checkbox">
                                                <input type="checkbox" name="Pick2" id="Pick2" value="1">
                                                <label for="checkbox1">
                                                    Picking
                                                </label>
                                            </div>
                                            <div class="checkbox">
                                                <input type="checkbox" name="ubicaptl2" id="ChkPTL2" value="1">
                                                <label for="checkbox2">
                                                    Ubicación de PTL
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <div class="pull-right"><br>
                                                <a href="#" onclick="cancelar_two()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                                                <button type="button" class="btn btn-primary ladda-button2" data-style="contract" id="btnSave">Editar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="hiddenUbicacion">
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="wrapper wrapper-content  animated fadeInRight" id="FORM2" style="display: none">
    <div class="wrapper wrapper-content">
        <div class="row">
            <div class="col-lg-4">
                <div class="ibox float-e-margins">
                    <div class="ibox-content mailbox-content">
                        <div class="file-manager">
                            <div class="widget style1 navy-bg">
                                <div class="row vertical-align">
                                    <div class="col-xs-3">
                                        <i class="fa fa-list fa-3x"></i>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <h2 class="font-bold">PASILLO <span id="Pasillo"></span></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="space-25"></div>

                            <ul class="cd-accordion-menu animated">
                                <li class="has-children">
                                    <input type="checkbox" name ="group-1" id="group-1" checked>
                                    <label for="group-1">Rack <span id="Numero_Rack"></span></label>

                                    <ul id="NivelesSecciones">
                                    </ul>
                                </li>

                            </ul> <!-- cd-accordion-menu -->

                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 animated fadeInRight">
                <div class="mail-box">
                    <div class="mail-body">
                        <div class="widget style1 navy-bg">
                            <div class="row vertical-align">
                                <div class="col-xs-3">
                                    <i class="fa fa-tasks fa-3x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <h2 id="title"></h2>
                                </div>
                            </div>
                        </div>
                        <div ><hr>

                        <div class="col-lg-12" id="Niveles">
                        </div>
                        <div class="col-lg-12" id="Secciones">
                        </div>
                        <div class="col-lg-12" id="Ubicaciones">
                        </div>

                    </div>
                    <div class="clearfix"></div>

                </div>
            </div>
            <div class="col-lg-12 animated fadeInRight text-right">
                <a href="#" onclick="cancelar_tree()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
            </div>
        </div>
    </div>
</div>

<!-- Input Mask-->
<script src="/js/plugins/jasny/jasny-bootstrap.min.js"></script>

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
<!-- iCheck -->
<script src="/js/plugins/iCheck/icheck.min.js"></script>
<!-- TouchSpin -->
<script src="/js/plugins/touchspin/jquery.bootstrap-touchspin.min.js"></script>
<!-- Acordion -->
<script src="/js/plugins/acordion/tree.js"></script>

<script type="text/javascript">
    /****************************** ACORDION ******************************/
    var acc = document.getElementsByClassName("accordion");
    var i;

    for (i = 0; i < acc.length; i++) {
        acc[i].onclick = function() {
            this.classList.toggle("active");
            var panel = this.nextElementSibling;
            if (panel.style.maxHeight){
                panel.style.maxHeight = null;
            } else {
                panel.style.maxHeight = '100%';
            }
        }
    }
    /*********************************************************************/

    $(".touchspin1").TouchSpin({
        min: -1000000000,
        max: 1000000000,
        stepinterval: 50,
        maxboostedstep: 10000000,
        buttondown_class: 'btn btn-white',
        buttonup_class: 'btn btn-white'
    });

    $('.i-checks').iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green'
    });

    function setGridWidth(grid_selector){
        $(window).on('resize.jqGrid', function () {
            $(grid_selector).jqGrid( 'setGridWidth', $(".page-content").width() - 60 );
        });

        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) {
            if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $(grid_selector).jqGrid( 'setGridWidth', parent_column.width() );
                }, 0);
            }
        });
    }

    function search_alm_produ(cve_almac, idy_ubica){
        $('#search').show();
        var grid_selector_alm_produ = "#grid-table_alm_produ";
        var pager_selector_alm_produ = "#grid-pager_alm_produ";

        setGridWidth(grid_selector_alm_produ);

        $.ajax({
            url:'/api/ubicacionalmacenaje/get/index.php',
            method: 'POST',
            data: {
                cve_almac:cve_almac
            },
            dataType: "JSON",
            success: function(data){
                $('#title_almacen').html(data); 
            }
        });

        $(grid_selector_alm_produ).jqGrid({
            url:'/api/ubicacionalmacenaje/lista_producto/index.php',
            datatype: "json",
            height: 250,
            postData: {
                idy_ubica: idy_ubica,
                criterio: $("#txtCriterio_alm_produ").val()
            },
            mtype: 'POST',
            colNames:['','Clave','Artículo','Ubicación','Lote','Caducidad','Cajas','Piezas'],
            colModel:[
                {name:'cve_almac',index:'cve_almac',width:50, editable:false, sortable:false, hidden:true},
                {name:'cve_articulo',index:'cve_articulo',width:120, editable:false, sortable:false, align:"center"},
                {name:'des_articulo',index:'des_articulo',width:350, editable:false, sortable:false, align:"justify"},
                {name:'idy_ubica',index:'idy_ubica',width:120, editable:false, sortable:false, align:"center"},
                {name:'LOTE',index:'LOTE',width:120, editable:false, sortable:false, align:"center"},
                {name:'CADUCIDAD',index:'',width:120, editable:false, sortable:false, align:"center", formatter:dateExplode},
                {name:'exi_caja',index:'exi_caja',width:120, editable:false, sortable:false, align:"center"},
                {name:'exi_pieza',index:'exi_pieza',width:120, editable:false, sortable:false, align:"center"}
                
            ],
            rowNum:30,
            rowList:[10,20,30],
            pager: pager_selector_alm_produ,
            sortname: 'cve_articulo',
            viewrecords: true,
            sortorder: "asc"
        });

        function dateExplode(cellvalue, options, rowObject){
            if (rowObject[4]==null){
                return "";
            }
            var datetime = new Date(rowObject[4]);
            var day = datetime.getDate();
            var month = datetime.getMonth(); 
            var year = datetime.getFullYear();
            return day + "/" + month + "/" + year;
        }
    }

    function cerrar_search(){
        $('#txtCriterio_alm_produ').val("");
        $('#search').hide();
    }

    $(document).on('click', '#radio1', function(event) { 
        $('#Nini').removeAttr('disabled');
        $('#Nini').removeAttr('enable');
        $('#Nini').attr("value", "0");
        $('#Nini').val(0);
        $('#Nini').attr("disabled", "disabled");
        
        $('#NFin').removeAttr('disabled');
        $('#NFin').removeAttr('enable');
        $('#NFin').attr("value", "0");
        $('#NFin').val(0);
        $('#NFin').attr("disabled", "disabled");

        $('#NNivel').removeAttr('disabled');
        $('#NNivel').removeAttr('enable');
        $('#NNivel').attr("value", "0");
        $('#NNivel').val(0);
        $('#NNivel').attr('enable', 'enable');

        $('#Pick').removeAttr("value");
        $('#Pick').removeAttr("enable");
        $('#Pick').removeAttr("disabled");
        $('#Pick').attr("value", "N");
        $("#Pick").attr("disabled","disabled");
    });

    $(document).on('click', '#radio10', function(event) { 
        $('#Nini2').removeAttr('disabled');
        $('#Nini2').removeAttr('enable');
        $('#Nini2').attr("value", "0");
        $('#Nini2').val(0);
        $('#Nini2').attr("disabled", "disabled");
        
        $('#NFin2').removeAttr('disabled');
        $('#NFin2').removeAttr('enable');
        $('#NFin2').attr("value", "0");
        $('#NFin2').val(0);
        $('#NFin2').attr("disabled", "disabled");

        $('#NNivel2').removeAttr('disabled');
        $('#NNivel2').removeAttr('enable');
        $('#NNivel2').attr("value", "0");
        $('#NNivel2').val(0);
        $('#NNivel2').attr('enable', 'enable');

        $('#Pick2').removeAttr("value");
        $('#Pick2').removeAttr("enable");
        $('#Pick2').removeAttr("disabled");
        $('#Pick2').attr("value", "N");
        $("#Pick2").attr("disabled","disabled");
    });

    $(document).on('click', '#radio2', function(event) {  
        $('#Nini').removeAttr('disabled');
        $('#Nini').removeAttr('enable');
        $('#Nini').attr("value", "0");
        $('#Nini').val(0);
        $('#Nini').attr("enable", "enable");
        
        $('#NFin').removeAttr('disabled');
        $('#NFin').removeAttr('enable');
        $('#NFin').attr("value", "0");
        $('#NFin').val(0);
        $('#NFin').attr("enable", "enable");

        $('#NNivel').removeAttr('disabled');
        $('#NNivel').removeAttr('enable');
        $('#NNivel').attr("value", "0");
        $('#NNivel').val(0);
        $('#NNivel').attr('disabled', 'disabled');

        $('#Pick').removeAttr("value");
        $('#Pick').removeAttr("enable");
        $('#Pick').removeAttr("disabled");
        $('#Pick').attr("value", "N");
        $("#Pick").attr("disabled","disabled");
    });

    $(document).on('click', '#radio11', function(event) {  
        $('#Nini2').removeAttr('disabled');
        $('#Nini2').removeAttr('enable');
        $('#Nini2').attr("value", "0");
        $('#Nini2').val(0);
        $('#Nini2').attr("enable", "enable");
        
        $('#NFin2').removeAttr('disabled');
        $('#NFin2').removeAttr('enable');
        $('#NFin2').attr("value", "0");
        $('#NFin2').val(0);
        $('#NFin2').attr("enable", "enable");

        $('#NNivel2').removeAttr('disabled');
        $('#NNivel2').removeAttr('enable');
        $('#NNivel2').attr("value", "0");
        $('#NNivel2').val(0);
        $('#NNivel2').attr('disabled', 'disabled');

        $('#Pick2').removeAttr("value");
        $('#Pick2').removeAttr("enable");
        $('#Pick2').removeAttr("disabled");
        $('#Pick2').attr("value", "N");
        $("#Pick2").attr("disabled","disabled");
    });

    $(document).on('click', '#radio3', function(event) {    
        $('#Sini').removeAttr('disabled');
        $('#Sini').removeAttr('enable');
        $('#Sini').attr("value", "0");
        $('#Sini').val(0);
        $('#Sini').attr("disabled", "disabled");
        
        $('#SFin').removeAttr('disabled');
        $('#SFin').removeAttr('enable');
        $('#SFin').attr("value", "0");
        $('#SFin').val(0);
        $('#SFin').attr("disabled", "disabled");

        $('#NSec').removeAttr('disabled');
        $('#NSec').removeAttr('enable');
        $('#NSec').attr("value", "0");
        $('#NSec').val(0);
        $('#NSec').attr('enable', 'enable');
    });

    $(document).on('click', '#radio12', function(event) {    
        $('#Sini2').removeAttr('disabled');
        $('#Sini2').removeAttr('enable');
        $('#Sini2').attr("value", "0");
        $('#Sini2').val(0);
        $('#Sini2').attr("disabled", "disabled");
        
        $('#SFin2').removeAttr('disabled');
        $('#SFin2').removeAttr('enable');
        $('#SFin2').attr("value", "0");
        $('#SFin2').val(0);
        $('#SFin2').attr("disabled", "disabled");

        $('#NSec2').removeAttr('disabled');
        $('#NSec2').removeAttr('enable');
        $('#NSec2').attr("value", "0");
        $('#NSec2').val(0);
        $('#NSec2').attr('enable', 'enable');
    });

    $(document).on('click', '#radio4', function(event) {   
        $('#Sini').removeAttr('disabled');
        $('#Sini').removeAttr('enable');
        $('#Sini').attr("value", "0");
        $('#Sini').val(0);
        $('#Sini').attr("enable", "enable");
        
        $('#SFin').removeAttr('disabled');
        $('#SFin').removeAttr('enable');
        $('#SFin').attr("value", "0");
        $('#SFin').val(0);
        $('#SFin').attr("enable", "enable");

        $('#NSec').removeAttr('disabled');
        $('#NSec').removeAttr('enable');
        $('#NSec').attr("value", "0");
        $('#NSec').val(0);
        $('#NSec').attr('disabled', 'disabled');
    });

    $(document).on('click', '#radio13', function(event) {   
        $('#Sini2').removeAttr('disabled');
        $('#Sini2').removeAttr('enable');
        $('#Sini2').attr("value", "0");
        $('#Sini2').val(0);
        $('#Sini2').attr("enable", "enable");
        
        $('#SFin2').removeAttr('disabled');
        $('#SFin2').removeAttr('enable');
        $('#SFin2').attr("value", "0");
        $('#SFin2').val(0);
        $('#SFin2').attr("enable", "enable");

        $('#NSec2').removeAttr('disabled');
        $('#NSec2').removeAttr('enable');
        $('#NSec2').attr("value", "0");
        $('#NSec2').val(0);
        $('#NSec2').attr('disabled', 'disabled');
    });

    $(document).on('change','#NNivel', function(event) {     
        if ( ($('#NNivel').val() == 1) || ($('#NNivel').val() == 2) ){
            $('#Pick').removeAttr("value");
            $('#Pick').removeAttr("enable");
            $('#Pick').removeAttr("disabled");
            $('#Pick').attr("value", "S");
            $("#Pick").attr("enable","enable");
        }

        if ($('#NNivel').val() >= 3){
            $('#Pick').removeAttr("value");
            $('#Pick').removeAttr("enable");
            $('#Pick').removeAttr("disabled");
            $('#Pick').attr("value", "N");
            $("#Pick").attr("disabled","disabled");
        }
    });

    $(document).on('change','#NNivel2', function(event) {     
        if ( ($('#NNivel2').val() == 1) || ($('#NNivel2').val() == 2) ){
            $('#Pick2').removeAttr("value");
            $('#Pick2').removeAttr("enable");
            $('#Pick2').removeAttr("disabled");
            $('#Pick2').attr("value", "S");
            $("#Pick2").attr("enable","enable");
        }

        if ($('#NNivel2').val() >= 3){
            $('#Pick2').removeAttr("value");
            $('#Pick2').removeAttr("enable");
            $('#Pick2').removeAttr("disabled");
            $('#Pick2').attr("value", "N");
            $("#Pick2").attr("disabled","disabled");
        }
    });

    $(document).on('change','#Nini', function(event) {   
        if ( ($('#Nini').val() == 1) || ($('#Nini').val() == 2) ){
            $('#Pick').removeAttr("value");
            $('#Pick').removeAttr("enable");
            $('#Pick').removeAttr("disabled");
            $('#Pick').attr("value", "S");
            $("#Pick").attr("enable","enable");
        }

        if ($('#Nini').val() >= 3){
            $('#Pick').removeAttr("value");
            $('#Pick').removeAttr("enable");
            $('#Pick').removeAttr("disabled");
            $('#Pick').attr("value", "N");
            $("#Pick").attr("disabled","disabled");
        }
    });

    $(document).on('change','#Nini2', function(event) {   
        if ( ($('#Nini2').val() == 1) || ($('#Nini2').val() == 2) ){
            $('#Pick2').removeAttr("value");
            $('#Pick2').removeAttr("enable");
            $('#Pick2').removeAttr("disabled");
            $('#Pick2').attr("value", "S");
            $("#Pick2").attr("enable","enable");
        }

        if ($('#Nini2').val() >= 3){
            $('#Pick2').removeAttr("value");
            $('#Pick2').removeAttr("enable");
            $('#Pick2').removeAttr("disabled");
            $('#Pick2').attr("value", "N");
            $("#Pick2").attr("disabled","disabled");
        }
    });

    $(document).on('change','#Nfin', function(event) {   
        if ( ($('#Nfin').val() == 1) || ($('#Nfin').val() == 2) ){
            $('#Pick').removeAttr("value");
            $('#Pick').removeAttr("enable");
            $('#Pick').removeAttr("disabled");
            $('#Pick').attr("value", "S");
            $("#Pick").attr("enable","enable");
        }

        if ($('#NFin').val() >= 3){
            $('#Pick').removeAttr("value");
            $('#Pick').removeAttr("enable");
            $('#Pick').removeAttr("disabled");
            $('#Pick').attr("value", "N");
            $("#Pick").attr("disabled","disabled");
        }
    });

    $(document).on('change','#Nfin2', function(event) {   
        if ( ($('#Nfin2').val() == 1) || ($('#Nfin2').val() == 2) ){
            $('#Pick2').removeAttr("value");
            $('#Pick2').removeAttr("enable");
            $('#Pick2').removeAttr("disabled");
            $('#Pick2').attr("value", "S");
            $("#Pick2").attr("enable","enable");
        }

        if ($('#NFin2').val() >= 3){
            $('#Pick2').removeAttr("value");
            $('#Pick2').removeAttr("enable");
            $('#Pick2').removeAttr("disabled");
            $('#Pick2').attr("value", "N");
            $("#Pick2").attr("disabled","disabled");
        }
    });

    $(document).on('click', '#radio9', function(event) { 
        $('#Pick').removeAttr("value");
        $('#Pick').removeAttr("enable");
        $('#Pick').removeAttr("disabled");
        $('#Pick').attr("value", "N");
        $("#Pick").attr("disabled","disabled");
    });

    $(document).on('click', '#radio18', function(event) { 
        $('#Pick2').removeAttr("value");
        $('#Pick2').removeAttr("enable");
        $('#Pick2').removeAttr("disabled");
        $('#Pick2').attr("value", "N");
        $("#Pick2").attr("disabled","disabled");
    });

    //////////////////////////////////////////////////////////Aqui se contruye el Grid//////////////////////////////////////////////////////////
    $(function($) {
        $("#radio1").attr('checked', true);
        $("#radio3").attr('checked', true);
        $("#radio5").attr('checked', true);
        $("#radio7").attr('checked', true);
        $("#Pick").iCheck('disable');
        $('#NNivel').attr('enable', 'enable');
        $('#Nini').attr('disabled','disabled');
        $('#NFin').attr('disabled','disabled');
        $('#Nsec').attr('enable', 'enable');
        $('#Sini').attr('disabled','disabled');
        $('#SFin').attr('disabled','disabled');

        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";

        setGridWidth(grid_selector);

        $(grid_selector).jqGrid({
            url:'/api/ubicacionalmacenaje/lista/index.php',
            datatype: "json",
            height: 250,
            postData: {
                criterio: $("#txtCriterio").val()
            },
            mtype: 'POST',
            colNames:['','','Clave','Zona de Almacenaje','Pasillo','Rack','Nivel','Sección','Ubicación','Peso Máx.','Dimensiones (Lar. X Anc. X Alt. )','Picking', 'Acciones'],
            colModel:[
                {name:'cve_almac',index:'cve_almac', width:50, editable:false, sortable:false, hidden:true},
                {name:'idy_ubica',index:'idy_ubica', width:50, editable:false, sortable:false, hidden:true},
                {name:'CodigoCSD',index:'', width:120, fixed:true, sortable:false, resize:false, align:"center", formatter:csd},
                {name:'des_almac',index:'des_almac',width:400, editable:false, sortable:false, align:"justify"},
                {name:'cve_pasillo',index:'cve_pasillo', width:200, editable:false, sortable:false, align:"center"},
                {name:'cve_rack',index:'', width:200, editable:false, sortable:false, align:"center", formatter:rack},
                {name:'cve_nivel',index:'cve_nivel',width:200, editable:false, sortable:false, align:"center"},
                {name:'Seccion',index:'Seccion', width:200, editable:false, sortable:false, align:"center"},
                {name:'Ubicacion',index:'Ubicacion', width:200, editable:false, sortable:false, align:"center"},
                {name:'PesoMaximo',index:'PesoMaximo', width:200, editable:false, sortable:false, align:"center"},
                {name:'dim',index:'',width:600, editable:false, sortable:false, align:"center", formatter:dimensiones},
                {name:'picking',index:'', width:70, fixed:true, sortable:false, resize:false, align:"center", formatter:imageCheck},
                {name:'myac',index:'', width:100, fixed:true, sortable:false, resize:false, align:"center", formatter:imageFormat},
            ],
            rowNum:10,
            rowList:[10,20,30],
            pager: "#grid-pager",
            sortname: 'idy_ubica',
            viewrecords: true,
            sortorder: "desc"
        });

        // Setup buttons
        $("#grid-table").jqGrid('navGrid', '#grid-pager',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );

        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size 

        function rack(cellvalue, options, rowObject){
            var rack = rowObject[5];
            var cero = 0;

            return cero+rack;
        }

        function csd(cellvalue, options, rowObject){
            var rack = rowObject[5];
            var seccion = rowObject[7];
            var nivel = rowObject[6];
            var ubicacion = rowObject[8];
            var cero = 0;

            return cero+rack+'-'+seccion+'-'+nivel+'-'+ubicacion;
        }

        function dimensiones(cellvalue, options, rowObject){
            var decimal = 2;
            var num_largo = Number(rowObject[10]);
            var num_ancho = Number(rowObject[11]);
            var num_alto = Number(rowObject[12]);

            return num_largo.toFixed(decimal)+'  X  '+num_ancho.toFixed(decimal)+'  X  '+num_alto.toFixed(decimal);
        }

        function imageCheck(cellvalue, options, rowObject){
            var picking = rowObject[13];
            if (picking=="S"){
                return '<div class="text-success"><i class="fa fa-check"></i></div>';
                //return '<input type="checkbox" checked="checked" disabled="disabled" />';
            }else{
                return '<div class="text-danger"><i class="fa fa-close"></div</i>';
                //return '<input type="checkbox" disabled="disabled" />';
            }
        }

        function imageFormat( cellvalue, options, rowObject ){
            //return '<img src="'+cellvalue+'" />&nbsp;&nbsp;&nbsp;&nbsp;<img src="'+cellvalue+'" />';
            var serie = rowObject[0];
            var almacen = rowObject[1];
            var correl = rowObject[4];
            var url = "x/?serie="+serie+"&correl="+correl;
            var url2 = "v/?serie="+serie+"&correl="+correl;
            $("#hiddenUbicacion").val(serie);

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="resultados(\''+serie+'\',\''+almacen+'\')" alt="Editar" title="Editar"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="borrar(\''+serie+'\',\''+almacen+'\')" alt="Eliminar" title="Eliminar"><i class="fa fa-eraser"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="search_alm_produ('+serie+','+almacen+')" alt="Buscar" title="Buscar"><i class="fa fa-search"></i></a>';

            //return '<a href="#" onclick="downloadxml(\''+url+'\')"><i class="ace-icon fa fa-download" alt="Download"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onclick="viewPdf(\''+url2+'\')"><i class="ace-icon fa fa-file-pdf-o" alt="Ver PDF"></i></a>';
            return html;
        }

        function aceSwitch( cellvalue, options, cell ) {
            setTimeout(function(){
                $(cell) .find('input[type=checkbox]')
                    .addClass('ace ace-switch ace-switch-5')
                    .after('<span class="lbl"></span>');
            }, 0);
        }
        //enable datepicker
        function pickDate( cellvalue, options, cell ) {
            setTimeout(function(){
                $(cell) .find('input[type=text]')
                    .datepicker({format:'yyyy-mm-dd' , autoclose:true});
            }, 0);
        }

        function beforeDeleteCallback(e) {
            var form = $(e[0]);
            if(form.data('styled')) return false;

            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_delete_form(form);

            form.data('styled', true);
        }

        function reloadPage() {
            var grid = $(grid_selector);
            $.ajax({
                url: "index.php",
                dataType: "json",
                success: function(data){
                    grid.trigger("reloadGrid",[{current:true}]);
                },
                error: function(){}
            });
        }

        function reloadPage_alm_produ() {
            var grid = $(grid_selector_alm_produ);
            $.ajax({
                url: "index.php",
                dataType: "json",
                success: function(data){
                    grid.trigger("reloadGrid_alm_produ",[{current:true}]);
                },
                error: function(){}
            });
        }

        function beforeEditCallback(e) {
            var form = $(e[0]);
            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_edit_form(form);
        }

        //it causes some flicker when reloading or navigating grid
        //it may be possible to have some custom formatter to do this as the grid is being created to prevent this
        //or go back to default browser checkbox styles for the grid
        function styleCheckbox(table) {
        }

        //unlike navButtons icons, action icons in rows seem to be hard-coded
        //you can change them like this in here if you want
        function updateActionIcons(table) {
        }

        //replace icons with FontAwesome icons like above

        function updatePagerIcons(table) {
        }

        function enableTooltips(table) {
            $('.navtable .ui-pg-button').tooltip({container:'body'});
            $(table).find('.ui-pg-div').tooltip({container:'body'});
        }

        //var selr = jQuery(grid_selector).jqGrid('getGridParam','selrow');

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector_alm_produ).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });

    //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////
    function ReloadGrid() {
        $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                criterio: $("#txtCriterio").val(),
            }, datatype: 'json', page : 1})
            .trigger('reloadGrid',[{current:true}]);
    }

    function ReloadGrid_alm_produ() {
        $('#grid-table_alm_produ').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                criterio: $("#txtCriterio_alm_produ").val(),
            }, datatype: 'json', page : 1})
            .trigger('reloadGrid_alm_produ',[{current:true}]);
    }

    function downloadxml( url ) {
        var win = window.open(url, '_blank');
        win.focus();
    }

    function viewPdf( url ) {
        var win = window.open(url, '_blank');
        win.focus();
    }

    $modal0 = null;

    function Secciones(_codigo,_nivel) {

        $("#title").html('<h2 class="font-bold">Nivel '+_nivel+'</h2>');

        $('#Ubicaciones').hide();

        $('#Niveles').removeAttr('class').attr('class', '');
        $('#Niveles').addClass('animated');
        $('#Niveles').addClass("fadeOutRight");
        $('#Niveles').hide();

        $('#Secciones').show();
        $('#Secciones').removeAttr('class').attr('class', '');
        $('#Secciones').addClass('animated');
        $('#Secciones').addClass("fadeInRight");
    }

    function Ubicaciones(_almacen,_nivel,_seccion) {

        $("#title").html('<h2 class="font-bold">Sección '+_seccion+'</h2>');

        $('#Secciones').removeAttr('class').attr('class', '');
        $('#Secciones').addClass('animated');
        $('#Secciones').addClass("fadeOutRight");
        $('#Secciones').hide();

        $('#Ubicaciones').show();
        $('#Ubicaciones').removeAttr('class').attr('class', '');
        $('#Ubicaciones').addClass('animated');
        $('#Ubicaciones').addClass("fadeInRight");

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_almac : _almacen,
                cve_nivel : _nivel,
                Seccion : _seccion,
                action : "ubicacion"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ubicacionalmacenaje/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    $('#Ubicaciones').hide();
                    $("#Ubicaciones").html(data.Ubicaciones);
                    $('#Ubicaciones').show();
                }
            }
        });
    }

    function borrarUbicacion(_almacen,_nivel,_seccion,_ubicacion) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_almac : _almacen,
                cve_nivel : _nivel,
                Seccion : _seccion,
                Ubicacion : _ubicacion,
                action : "deleteUbica"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ubicacionalmacenaje/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    $('#Ubicaciones').removeAttr('class').attr('class', '');
                    $('#Ubicaciones').addClass('animated');
                    $('#Ubicaciones').addClass("fadeOutRight");
                    $('#Ubicaciones').hide();
                    $("#Ubicaciones").html(data.Ubicaciones);
                    $('#Ubicaciones').show();
                    $('#Ubicaciones').removeAttr('class').attr('class', '');
                    $('#Ubicaciones').addClass('animated');
                    $('#Ubicaciones').addClass("fadeInRight");
                }
            }
        });
    }

    function borrar(_almacen) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_almac : _almacen,
                action : "delete"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ubicacionalmacenaje/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    ReloadGrid();
                }
            }
        });
    }

    function resultados(_codigo) {

        $("#title").html('<h2 class="font-bold">Niveles</h2>');

        $('#Secciones').hide();
        $('#Ubicaciones').hide();

        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeOutRight");
        $('#list').hide();

        $('#FORM2').show();
        $('#FORM2').removeAttr('class').attr('class', '');
        $('#FORM2').addClass('animated');
        $('#FORM2').addClass("fadeInRight");

        /****************** RACK / PASILLO *********************/
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                codigo : _codigo,
                action : "rack_pasillo"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ubicacionalmacenaje/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    $("#Pasillo").html(data.cve_pasillo);
                    $("#Numero_Rack").html(data.cve_rack);
                    $("#Niveles").html(data.Niveles);
                    $("#Secciones").html(data.Secciones);
                    $("#Ubicaciones").html(data.Ubicaciones);
                    $("#NivelesSecciones").html(data.NivelesSecciones);
                }
            }
        });
    }

    function DetalleUbicacion(_codigo,_ubicacion) {
        $("#_title2").html('<h3>Editar Ubicación de Zona de Almacenaje (Ubicación <span id="id_ubicacion"></span>)</h3>');
        $('#FORM2').removeAttr('class').attr('class', '');
        $('#FORM2').addClass('animated');
        $('#FORM2').addClass("fadeOutRight");
        $('#FORM2').hide();

        $('#DetalleUbicacion').show();
        $('#DetalleUbicacion').removeAttr('class').attr('class', '');
        $('#DetalleUbicacion').addClass('animated');
        $('#DetalleUbicacion').addClass("fadeInRight");

        /************** ENABLED ***************/
        // $("#Pick2").click(function() {
        //     $("#ChkPTL2").iCheck('enable');
        // });
        // $('input[name^="radio1"]').change(function () {
        //     if (this.value == 1) {
        //         $('#txtNivel2').attr("disabled", false);
        //         $('#Nini2').attr("disabled", true);
        //         $('#NFin2').attr("disabled", true);
        //     } else if (this.value == 2) {
        //         $('#NNivel2').attr("disabled", true); //$("#NNivel").attr("disabled", false)
        //         $('#Nini2').attr("disabled", false);
        //         $('#NFin2').attr("disabled", false);
        //     }
        // });

        $("#hiddenUbicacion").val(_codigo);
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                idy_ubica : _codigo,
                action : "cargar"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ubicacionalmacenaje/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    $("#id_ubicacion").html(data.idy_ubica);
                    $("#Almacen2").val(data.cve_almac);
                    $("#Pas2").val(data.cve_pasillo);
                    $("#NRack2").val(data.cve_rack);
                    $("#NSec2").val(data.Seccion);
                    $("#NNivel2").val(data.cve_nivel);
                    $("#AlUbica2").val(data.Ubicacion);
                    $("#LaUbi2").val(data.num_largo);
                    $("#AlUbi2").val(data.num_alto);
                    $("#AnUbi2").val(data.num_ancho);
                    $("#PMax2").val(data.PesoMaximo);
                    if (data.orden_secuencia == '1') {
                        $('#radio12').iCheck('check');
                        $('#radio22').iCheck('uncheck');
                    } else {
                        $('#radio22').iCheck('check');
                        $('#radio12').iCheck('uncheck');
                    }
                    if (data.orden_secuencia == '2') {
                        $('#radio12').iCheck('uncheck');
                        $('#radio22').iCheck('check');
                    } else {
                        $('#radio22').iCheck('uncheck');
                        $('#radio12').iCheck('check');
                    }
                    if (data.picking == 'S') {
                        $('#Pick2').iCheck('check');
                    } else {
                        $('#Pick2').iCheck('uncheck');
                    }
                    if (data.Reabasto == '1') {
                        $('#ChkPTL2').iCheck('check');
                    } else {
                        $('#ChkPTL2').iCheck('uncheck');
                    }
                    l.ladda('stop');
                    $("#btnCancel").show();

                    $('#FORM2').removeAttr('class').attr('class', '');
                    $('#FORM2').addClass('animated');
                    $('#FORM2').addClass("fadeOutRight");
                    $('#FORM2').hide();

                    $('#DetalleUbicacion').show();
                    $('#DetalleUbicacion').removeAttr('class').attr('class', '');
                    $('#DetalleUbicacion').addClass('animated');
                    $('#DetalleUbicacion').addClass("fadeInRight");

                    $("#hiddenAction").val("edit");
                }
            }
        });
    }

    function cancelar_tree() {
        $('#FORM2').removeAttr('class').attr('class', '');
        $('#FORM2').addClass('animated');
        $('#FORM2').addClass("fadeOutRight");
        $('#FORM2').hide();

        $('#list').show();
        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeInRight");
        $('#list').addClass("wrapper");
        $('#list').addClass("wrapper-content");
    }

    function cancelar_two() {
        $('#DetalleUbicacion').removeAttr('class').attr('class', '');
        $('#DetalleUbicacion').addClass('animated');
        $('#DetalleUbicacion').addClass("fadeOutRight");
        $('#DetalleUbicacion').hide();

        $('#FORM2').show();
        $('#FORM2').removeAttr('class').attr('class', '');
        $('#FORM2').addClass('animated');
        $('#FORM2').addClass("fadeInRight");
        $('#FORM2').addClass("wrapper");
        $('#FORM2').addClass("wrapper-content");
    }

    function cancelar() {

        $('#FORM').removeAttr('class').attr('class', '');
        $('#FORM').addClass('animated');
        $('#FORM').addClass("fadeOutRight");
        $('#FORM').hide();

        $('#list').show();
        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeInRight");
        $('#list').addClass("wrapper");
        $('#list').addClass("wrapper-content");
    }

    function salir() {
        $(':input','#myform')
            .removeAttr('checked')
            .removeAttr('selected')
            .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
            .val('');
        $('#FORM2').removeAttr('class').attr('class', '');
        $('#FORM2').addClass('animated');
        $('#FORM2').addClass("fadeOutRight");
        $('#FORM2').hide();

        $('#list').show();
        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeInRight");
        $('#list').addClass("wrapper");
        $('#list').addClass("wrapper-content");
    }

    function agregar() {
        $("#_title").html('<h3>Agregar Ubicación de Zona de Almacenaje</h3>');
        $(':input','#myform')
            .removeAttr('checked')
            .removeAttr('selected')
            .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
            .val('');
        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeOutRight");
        $('#list').hide();

        $('#FORM').show();
        $('#FORM').removeAttr('class').attr('class', '');
        $('#FORM').addClass('animated');
        $('#FORM').addClass("fadeInRight");
        $("#hiddenAction").val("add");
        $("#hiddenUbicacion").val("0");
        $('#txtNivel').attr("disabled", true);
        $('#Nini').attr("disabled", true);
        $('#NFin').attr("disabled", true);
        $('#ChkPTL').iCheck('disable');
        /************** ENABLED ***************/
        $("#Pick").click(function() {
            $("#ChkPTL").iCheck('enable');
        });
        $('input[name^="radio1"]').change(function () {
            if (this.value == 1) {
                $('#txtNivel').attr("disabled", false);
                $('#Nini').attr("disabled", true);
                $('#NFin').attr("disabled", true);
            } else if (this.value == 2) {
                $('#NNivel').attr("disabled", true); //$("#NNivel").attr("disabled", false)
                $('#Nini').attr("disabled", false);
                $('#NFin').attr("disabled", false);
            }
        });
    }

    function MyParse(h)
    {
        var back, error = 0;
        if (h == "" )
            return error;
        try
        {
            parseFloat(h);
        }
        catch (Exception)
        {
            return error;
        }
        var ar = h.split('.');
        if (ar.length < 2)
            return parseFloat(h);
        back = parseInt(ar[0]);
        while (ar[1].length < 4)
            ar[1] += "0";
        ar[1] = ar[1].substring(0, 4);
        back += parseFloat(ar[1]) / 10000;
        return back;
    }

    function padLeft(nr, n, str){
        return Array(n-String(nr).length+1).join(str||'0')+nr;
    }

    var l = $( '.ladda-button' ).ladda();
    l.click(function() {

        $("#btnCancel").hide();

        var rack, sec = 0, nivs = 0, nivi = 0, nivf = 0, ubis;
		
        if ($("#Almacen").val() == "") {
            alert("Informacion Incompleta, Selecciona la Zona de Almacenaje al que perteneceran las ubicaciones a crear");
            $("#Almacen").focus();
            return;
        }
        if ($("#txtClavePasillo").val() == "") {
            alert("Informacion Incompleta, Introduce el nombre del pasillo");
            $("#txtClavePasillo").focus();
            return;
        }
        if ($("#NRack").val() == "") {
            alert("Informacion Incompleta, Selecciona el rack a crear");
            $("#NRack").focus();
            return;
        }else{
            rack = $("#NRack").val();
        }

        if($("#radio1").is(':checked')) {
            if ($("#NNivel").val() == 0){
                alert("Informacion Incompleta, El rack al menos debe tener un nivel");
                $("#NNivel").focus();
                return;
            } else{
                nivs = $("#NNivel").val();
                nivi = 1;
                nivf = nivs;
            }
        } 

        if($("#radio2").is(':checked')) {
            if ($("#Nini").val() == 0) {
                alert("Informacion Incompleta, Se debe especificar al menos un nivel mínimo");
                $("#Nini").focus();
                return;
            } else {
                nivs = 0;
                nivi = $("#Nini").val();
                nivf = $("#NFin").val();
            }

            if ($("#NFin").val() == 0) {
                alert("Informacion Incompleta, Se debe especificar al menos un nivel máximo");
                $("#NFin").focus();
                return;
            } else {
                nivs = 0;
                nivi = $("#Nini").val();
                nivf = $("#NFin").val();
            }

            if ($("#Nini").val() > $("#NFin").val()) {
                alert("Informacion Incorrecta, El nivel de inicio es mayor que el nivel final");
                nivi = 0;
                nivf = 0;
                $("#Nini").focus();
                return;
            }
        }

        if($("#radio3").is(':checked')) {  
            if ($("#NSec").val() == 0){
                alert("Informacion Incompleta, El rack al menos debe tener una sección");
                $("#NSec").focus();
                return;
            }else{
                sec = $("#NSec").val();
                sivi = 1;
                sivf = sec;
            } 
        } 

        if($("#radio4").is(':checked')) {
            if ($("#Sini").val() == 0) {
                alert("Informacion Incompleta, Se debe especificar al menos una sección mínima");
                $("#Sini").focus();
                return;
            } else if ($("#SFin").val() == 0) {
                alert("Informacion Incompleta, Se debe especificar al menos una sección máxima");
                $("#SFin").focus();
                return;
            } else if ($("#Sini").val() > $("#SFin").val()) {
                alert("Informacion Incorrecta, La sección de inicio es mayor que la sección final");
                sivi = 0;
                sivf = 0;
                $("#Sini").focus();
                return;
            }else {
				sec = 0;
                sivi = $("#Sini").val();
                sivf = $("#SFin").val();
			}
        }

        if ($("#UNiv").val() == 0) {
            alert("Informacion Incompleta, Los niveles deben tener al menos una ubicacion");
            $("#UNiv").focus();
            return;
        } else {
            ubis = $("#UNiv").val();
        }

        var status = "1", orden = "1", cverp, picking;

        var alto, ancho, largo, poc, pmax, vdis = 0;

        cverp = "";

        alto = $("#AlUbi").val();

        //AlUbi.Text = DB.MyParse(AlUbi.Text).ToString();
        ancho = $("#AnUbi").val();
        //AnUbi.Text = DB.MyParse(AnUbi.Text).ToString();
        largo = $("#LaUbi").val();
        //LaUbi.Text = DB.MyParse(LaUbi.Text).ToString();
        if($("#Pick").prop("checked"))
            picking = "S";
        else
            picking = "N";
        poc = 0;
        pmax = $("#PMax").val();
        //PMax.Text = DB.MyParse(PMax.Text).ToString();
        var cont = 1;
        var niv_dif = 0, Nini = 0, nfin = 0;
        if (nivs == 0) {
            niv_dif = (nivf - nivi) + 1;
            Nini = nivi;
            nfin = nivf;
        }else{
            niv_dif = nivs;
			Nini = 1;
            nfin = nivs;
        }
		
		if (sec == 0) {
            sec_dif = (sivf - sivi) + 1;
            Sini = sivi;
            Sfin = sivf;
        }else{
            sec_dif = sec;
			Sini = 1;
            Sfin = sec;
        }
		
		var _t = "";

		if ($("#ChkPTL").prop("checked"))
			_t = "PTL";
		
		if ($("#radio7").prop("checked"))
			status = "L";
		if ($("#radio8").prop("checked"))
			status = "R";
		if ($("#radio9").prop("checked"))
			status = "C";
		
		var TipoUbicacion = ($("#RBRack").prop("checked")) ? "R" : "P";

        arrDet = [];

        var total = sec_dif * ubis * niv_dif;
		console.log("Total de niveles: "+niv_dif);
		console.log("Total de secciones: "+sec_dif);
		console.log("Total de ubicaciones: "+ubis);
		console.log("Total de inserts: "+total);
        for (NIV = Nini; NIV <= nfin; NIV++){
			console.log("Creando nivel: "+NIV);
            for (var secc = Sini; secc <= Sfin; secc++) {
                //PBar.Texto = "Creando Seccion " + secc.ToString();
				console.log("Creando seccion: "+secc);
                for (var UBI = 1; UBI <= ubis; UBI++){
                    
					console.log("Creando ubicacion: "+UBI);
					
                    arrDet.push({
                        cve_almac : $("#Almacen").val(),
                        cve_pasillo : $("#Pas").val(),
                        cve_rack : rack,
                        cve_nivel : NIV,
                        Ubicacion : UBI,
                        orden_secuencia : orden,
                        Status : status,
                        CodigoCSD : cverp,
                        num_alto : alto,
                        num_ancho : ancho,
                        num_largo : largo,
                        num_volumenDisp : vdis,
                        PesoMaximo : pmax,
                        PesoOcupado : poc,
                        picking : picking,
                        Seccion : padLeft(secc, 3),
                        TipoUbicacion : TipoUbicacion,
                        Tecnologia : _t
                    });
					//console.log("ubicacion: "+UBI);
                    cont++;
                }
            }
        }

		
		console.log(arrDet);
		
        l.ladda( 'start' );

        $.post('/api/ubicacionalmacenaje/update/index.php', {
                action : "add",
                arrDet : arrDet
            },
            function(response){
                console.log(response);
            }, "json")
            .always(function() {
                l.ladda('stop');
                $('#FORM').removeAttr('class').attr('class', '');
                $('#FORM').addClass('animated');
                $('#FORM').addClass("fadeOutRight");
                $('#FORM').hide();
                ReloadGrid();
                $('#list').show();
                $('#list').removeAttr('class').attr('class', '');
                $('#list').addClass('animated');
                $('#list').addClass("fadeInRight");
            });
        });

    /**************************************** EDITAR *******************************************/

    var l = $( '.ladda-button2' ).ladda();
    l.click(function() {

        $("#btnCancel").hide();

        var rack, sec = 0, nivs = 0, nivi = 0, nivf = 0, ubis;

        if ($("#Almacen2").val() == "") {
            alert("Informacion Incompleta, Selecciona la Zona de Almacenaje al que perteneceran las ubicaciones a crear");
            $("#Almacen2").focus();
            return;
        }
        if ($("#txtClavePasillo2").val() == "") {
            alert("Informacion Incompleta, Introduce el nombre del pasillo");
            $("#txtClavePasillo2").focus();
            return;
        }
        if ($("#NRack2").val() == "") {
            alert("Informacion Incompleta, Selecciona el rack a crear");
            $("#NRack2").focus();
            return;
        }else{
            rack = $("#NRack2").val();
        }

        if($("#radio10").is(':checked')) {
            if ($("#NNivel2").val() == 0){
                alert("Informacion Incompleta, El rack al menos debe tener un nivel");
                $("#NNivel2").focus();
                return;
            } else{
                nivs = $("#NNivel2").val();
                nivi = 0;
                nivf = 0;
            }
        } 

        if($("#radio11").is(':checked')) {
            if ($("#Nini2").val() == 0) {
                alert("Informacion Incompleta, Se debe especificar al menos un nivel mínimo");
                $("#Nini2").focus();
                return;
            } else {
                nivs = 0;
                nivi = $("#Nini2").val();
                nivf = $("#NFin").val();
            }

            if ($("#NFin2").val() == 0) {
                alert("Informacion Incompleta, Se debe especificar al menos un nivel máximo");
                $("#NFin2").focus();
                return;
            } else {
                nivs = 0;
                nivi = $("#Nini2").val();
                nivf = $("#NFin2").val();
            }

            if ($("#Nini2").val() > $("#NFin2").val()) {
                alert("Informacion Incorrecta, El nivel de inicio es mayor que el nivel final");
                nivi = 0;
                nivf = 0;
                $("#Nini2").focus();
                return;
            }
        }

        if($("#radio12").is(':checked')) {  
            if ($("#NSec2").val() == 0){
                alert("Informacion Incompleta, El rack al menos debe tener una sección");
                $("#NSec2").focus();
                return;
            }else{
                sec = $("#NSec2").val();
                sivi = 0;
                sivf = 0;
            } 
        } 

        if($("#radio13").is(':checked')) {
            if ($("#Sini2").val() == 0) {
                alert("Informacion Incompleta, Se debe especificar al menos una sección mínima");
                $("#Sini2").focus();
                return;
            } else {
                nivs = 0;
                nivi = $("#Sini2").val();
                nivf = $("#SFin2").val();
            }

            if ($("#SFin2").val() == 0) {
                alert("Informacion Incompleta, Se debe especificar al menos una sección máxima");
                $("#SFin2").focus();
                return;
            } else {
                nivs = 0;
                nivi = $("#Sini2").val();
                nivf = $("#SFin2").val();
            }
            
            if ($("#Sini2").val() > $("#SFin2").val()) {
                alert("Informacion Incorrecta, La sección de inicio es mayor que la sección final");
                nivi = 0;
                nivf = 0;
                $("#Sini2").focus();
                return;
            }
        }

        if ($("#UNiv2").val() == 0) {
            alert("Informacion Incompleta, Los niveles deben tener al menos una ubicacion");
            $("#UNiv2").focus();
            return;
        } else {
            ubis = $("#UNiv2").val();
        }

        var status = "1", orden = "1", cverp, picking;

        var alto, ancho, largo, poc, pmax, vdis = 0;

        cverp = "";

        alto = $("#AlUbi2").val();

        //AlUbi.Text = DB.MyParse(AlUbi.Text).ToString();
        ancho = $("#AnUbi2").val();
        //AnUbi.Text = DB.MyParse(AnUbi.Text).ToString();
        largo = $("#LaUbi2").val();
        //LaUbi.Text = DB.MyParse(LaUbi.Text).ToString();
        if($("#Pick2").prop("checked"))
            picking = "S";
        else
            picking = "N";
        poc = 0;
        pmax = $("#PMax2").val();
        //PMax.Text = DB.MyParse(PMax.Text).ToString();
        var cont = 1;
        var niv_dif = 0, Nini = 0, nfin = 0;
        if (nivs == 0) {
            niv_dif = (nivf - nivi) + 1;
            Nini = nivi;
            nfin = nivf;
        }else{
            niv_dif = nivs;
            Nini = 1;
            nfin = nivs;
        }

        arrDet = [];

        var total = sec * ubis * niv_dif;
        for (NIV = Nini; NIV <= nfin; NIV++) {
            var ubi = 1;
            for (secc = 1; secc <= sec; secc++) {
                //PBar.Texto = "Creando Seccion " + secc.ToString();
                for (UBI = 1; UBI <= ubis; UBI++){
                    var _t = "";

                    if ($("#ChkPTL2").prop("checked"))
                        _t = "PTL";

                    var TipoUbicacion = ($("#RBRack2").prop("checked")) ? "R" : "P";

                    arrDet.push({
                        cve_almac : $("#Almacen2").val(),
                        cve_pasillo : $("#Pas2").val(),
                        cve_rack : rack,
                        cve_nivel : NIV,
                        Ubicacion : ubi,
                        orden_secuencia : orden,
                        Status : status,
                        CodigoCSD : cverp,
                        num_alto : alto,
                        num_ancho : ancho,
                        num_largo : largo,
                        num_volumenDisp : vdis,
                        PesoMaximo : pmax,
                        PesoOcupado : poc,
                        picking : picking,
                        Seccion : padLeft(secc, 3),
                        TipoUbicacion : TipoUbicacion,
                        Tecnologia : _t
                    });
                    cont++;
                    ubi++;
                }
            }
        }

		console.log(JSON.parse(JSON.stringify(arrDet)));
		
        l.ladda( 'start' );

        $.post('/api/ubicacionalmacenaje/update/index.php',{
                action : "edit",
                arrDet : arrDet
            },
            function(response){
                console.log(response);
            }, "json")
            .always(function() {
                l.ladda('stop');
                $('#DetalleUbicacion').removeAttr('class').attr('class', '');
                $('#DetalleUbicacion').addClass('animated');
                $('#DetalleUbicacion').addClass("fadeOutRight");
                $('#DetalleUbicacion').hide();
                ReloadGrid();
                $('#FORM2').show();
                $('#FORM2').removeAttr('class').attr('class', '');
                $('#FORM2').addClass('animated');
                $('#FORM2').addClass("fadeInRight");
            });
        });
</script>