<?php include "_core_header_scripts.php" ?>

<style>
  .listado
  {
    text-align:center;
  }
  .listado button
  {
    display: block;
    width: 200px;
    padding: 5px;
    margin: 3px auto;
    background: linear-gradient(180deg, #1ab394, #13967c);
    color: white;
    border: solid 1px #0f8a72;
    border-radius: 10px;
    text-align:center;
  }
  .listado button:hover
  {
    background: linear-gradient( 180deg , #30debb, #21b99b);
  }
  .m-1
  {
    margin:10px;
  }
  
  [v-cloak] > * { display:none; }
  [v-cloak]::before { 
    content: " ";
    display: block;
    width: 25px;
    height: 25px;
    margin: auto;
    margin-top: 25%;
    background-repeat: no-repeat;
    background-size: cover;
    background-image: url('data:image/gif;base64,R0lGODlhEAAQAPIAAP///wAAAMLCwkJCQgAAAGJiYoKCgpKSkiH/C05FVFNDQVBFMi4wAwEAAAAh/hpDcmVhdGVkIHdpdGggYWpheGxvYWQuaW5mbwAh+QQJCgAAACwAAAAAEAAQAAADMwi63P4wyklrE2MIOggZnAdOmGYJRbExwroUmcG2LmDEwnHQLVsYOd2mBzkYDAdKa+dIAAAh+QQJCgAAACwAAAAAEAAQAAADNAi63P5OjCEgG4QMu7DmikRxQlFUYDEZIGBMRVsaqHwctXXf7WEYB4Ag1xjihkMZsiUkKhIAIfkECQoAAAAsAAAAABAAEAAAAzYIujIjK8pByJDMlFYvBoVjHA70GU7xSUJhmKtwHPAKzLO9HMaoKwJZ7Rf8AYPDDzKpZBqfvwQAIfkECQoAAAAsAAAAABAAEAAAAzMIumIlK8oyhpHsnFZfhYumCYUhDAQxRIdhHBGqRoKw0R8DYlJd8z0fMDgsGo/IpHI5TAAAIfkECQoAAAAsAAAAABAAEAAAAzIIunInK0rnZBTwGPNMgQwmdsNgXGJUlIWEuR5oWUIpz8pAEAMe6TwfwyYsGo/IpFKSAAAh+QQJCgAAACwAAAAAEAAQAAADMwi6IMKQORfjdOe82p4wGccc4CEuQradylesojEMBgsUc2G7sDX3lQGBMLAJibufbSlKAAAh+QQJCgAAACwAAAAAEAAQAAADMgi63P7wCRHZnFVdmgHu2nFwlWCI3WGc3TSWhUFGxTAUkGCbtgENBMJAEJsxgMLWzpEAACH5BAkKAAAALAAAAAAQABAAAAMyCLrc/jDKSatlQtScKdceCAjDII7HcQ4EMTCpyrCuUBjCYRgHVtqlAiB1YhiCnlsRkAAAOwAAAAAAAAAAAA==');
  }

  .d-inline
  {
    display: inline-block;
  }
  .selectBorder
  {
    border: solid 1px #4ca291;
    background: white;
    border-radius: 15px;
    padding: 5px 10px;
    font-size: 14px;
  }
  
  .selectBorderClickable
  {
    color: white;
    border: solid 1px #4ca291;
    background: linear-gradient(180deg, #1ab394, #13967c);
    border-radius: 15px;
    padding: 5px 10px;
    font-size: 14px;
    cursor:pointer;
  }

  .selectBorderClickable:hover
  {
    background: linear-gradient( 180deg , #30debb, #21b99b);
  }
  
  .br50
  {
    border-radius: 50%;
  }
  .sinRes
  {
    background: #ababab;
    padding: 10px 30px;
    border-radius: 20px;
    margin: 10px;
    color: white;
  }
  .totales
  {
    font-weight: 500;
  }
</style>


<div id="app" v-cloak>
  <h2 class="text-center">Reporte de ventas</h2>
  <div>
    <div class="d-inline selectBorder" v-if="filtros.fecha != ''">
      <div class="d-inline">
        Fecha: {{filtroEspecifico}} ({{displayDate(filtros.fecha)}})
        <button class="btn btn-xs btn-danger br50" type="button" v-on:click="mostrarMenuPrincipal = true; step = 0; resultadosReady = false;  filtros.fecha=''; mostrarRangoFechas = false; eliminarBurbujas(); "><i class="fa fa-times" aria-hidden="true"></i>
        </button>
      </div>
    </div>
    <div class="d-inline selectBorder" v-if="filtros.fechaIni != '' && filtros.fechaFin != '' && resultadosReady == true">
      <div class="d-inline">
        Fecha: {{filtroEspecifico}} ({{displayDate(filtros.fechaIni)}} - {{displayDate(filtros.fechaFin)}})
        <button class="btn btn-xs btn-danger br50" type="button" v-on:click="mostrarMenuPrincipal = true; step = 0; resultadosReady = false; filtros.fechaIni='' ; filtros.fechaFin=''; mostrarRangoFechas = false; eliminarBurbujas(); "><i class="fa fa-times" aria-hidden="true"></i></button>
      </div>
    </div>
    <div class="d-inline selectBorder" v-if="filtros.diaO != undefined && filtros.diaO != ''">
      <div class="d-inline">
        Dia Operativo: {{filtros.diaO}} 
        <button class="btn btn-xs btn-danger br50" type="button" v-on:click="filtros.diaO = ''; mostrarMenuPrincipal = true; step = 0; resultadosReady = false; mostrarDiaOp = false; eliminarBurbujas();"><i class="fa fa-times" aria-hidden="true"></i></button>
      </div>
    </div>
<!--     <div class="d-inline selectBorder" v-if="burbujaDiaOperativo==true">
      <div class="d-inline">
        Filtrado por Dia Operativo
        <button class="btn btn-xs btn-secondary br50" type="button" v-on:click="quitarDiaOp()"  ><i class="fa fa-times" aria-hidden="true"></i></button>
      </div>
    </div> -->
    <div class="d-inline selectBorder" v-if=" filtros.idProducto != undefined && filtros.idProducto != ''">
      <div class="d-inline">
        Articulo: {{filtros.idProducto}} 
        <button class="btn btn-xs btn-danger br50" type="button" v-on:click="filtros.idProducto = ''; test(filtros);"><i class="fa fa-times" aria-hidden="true"></i></button>
      </div>
    </div>
    <div class="d-inline selectBorder" v-if=" filtros.idRuta != undefined && filtros.idRuta != ''">
      <div class="d-inline">
        Ruta: {{filtros.idRuta}} 
        <button class="btn btn-xs btn-danger br50" type="button" v-on:click="filtros.idRuta = '';  test(filtros);"><i class="fa fa-times" aria-hidden="true"></i></button>
      </div>
    </div>
    <div class="d-inline selectBorder" v-if="filtros.idCliente != undefined && filtros.idCliente !=''">
      <div class="d-inline">
        Cliente: {{filtros.idCliente}} 
        <button class="btn btn-xs btn-danger br50" type="button" v-on:click="filtros.idCliente = ''; buc_venta_Cliente_venta = ''; test(filtros);"><i class="fa fa-times" aria-hidden="true"></i></button>
      </div>
    </div>
    <div class="d-inline selectBorder" v-if="filtros.nombreReponsable != undefined && filtros.nombreReponsable !=''">
      <div class="d-inline">
        Responsable: {{filtros.nombreReponsable}} 
        <button class="btn btn-xs btn-danger br50" type="button" v-on:click="filtros.nombreReponsable = ''; buc_venta_Responsable_venta = ''; test(filtros);"><i class="fa fa-times" aria-hidden="true"></i></button>
      </div>
    </div>
    <div class="d-inline selectBorder" v-if=" filtros.nombreCliente != undefined && filtros.nombreCliente !=''">
      <div class="d-inline">
        Nombre Comercial: {{filtros.nombreCliente}} 
        <button class="btn btn-xs btn-danger br50" type="button" v-on:click="filtros.nombreCliente=''; buc_venta_NombreComercial_venta = ''; test(filtros);"><i class="fa fa-times" aria-hidden="true"></i></button>
      </div>
    </div>
    <div class="d-inline selectBorder" v-if=" filtros.folio != undefined && filtros.folio != ''">
      <div class="d-inline">
        Folio: {{filtros.folio}} 
        <button class="btn btn-xs btn-danger br50" type="button" v-on:click="filtros.folio = ''; buc_venta_Folio_venta = ''; test(filtros);"><i class="fa fa-times" aria-hidden="true"></i></button>
      </div>
    </div>
    <div class="d-inline selectBorder" v-if=" filtros.tipo != undefined && filtros.tipo != ''">
      <div class="d-inline">
        Tipo: {{filtros.tipo}} 
        <button class="btn btn-xs btn-danger br50" type="button" v-on:click="filtros.tipo = ''; buc_venta_Tipo_venta = ''; test(filtros);"><i class="fa fa-times" aria-hidden="true"></i></button>
      </div>
    </div>
    <div class="d-inline selectBorder" v-if=" filtros.metodoPago != undefined && filtros.metodoPago != ''">
      <div class="d-inline">
        MetodoPago: {{filtros.metodoPago}} 
        <button class="btn btn-xs btn-danger br50" type="button" v-on:click="filtros.metodoPago = ''; buc_venta_MetodoPago_venta = ''; test(filtros);"><i class="fa fa-times" aria-hidden="true"></i></button>
      </div>
    </div>
    
    
    <div class="d-inline selectBorder" v-if=" filtros.comisiones != undefined && filtros.comisiones != ''">
      <div class="d-inline">
        {{filtros.comisiones}} Comision
        <button class="btn btn-xs btn-danger br50" type="button" v-on:click="filtros.comisiones = ''; buc_venta_Comisiones_venta = ''; checkComisiones = false; test(filtros);"><i class="fa fa-times" aria-hidden="true"></i></button>
      </div>
    </div>
    
    
    
    
    
    
    <div class="d-inline selectBorderClickable" v-if="resultadosReady">
      <div class="d-inline " >
        <span @click="addFiltro = !addFiltro;"> Filtros adicionales <i class="fa fa-search"></i></span>
      </div>
    </div>
    
  </div>
  
  <div class="row" v-if="mostrarMenuPrincipal" >
    <div class="col-md-4"></div>
    <div class="col-md-4">
      <div class="text-center h4 m-1">
        ¿ De cuando necesita el reporte de ventas ?
      </div>
      <div class="listado">
        <button @click="filtroFecha('HOY');">Hoy</button>
        <button @click="filtroFecha('AYE');">Ayer</button>
        <button @click="filtroRangoFecha();">Por fecha</button>
        <!-- step = 2; filtroEspecifico = ''; filtros.fechaIni = ''; filtros.fechaFin = ''; ventas = [];  -->
        <button @click="filtroDiaOp();">Por día operativo</button>
        <!-- step = 1;  buscarDiaOperativo();-->
        <button @click="filtroFecha('SAC');">Semana actual</button>
        <button @click="filtroFecha('SAN');">Semana anterior</button>
        <button @click="filtroFecha('MAC');">Mes actual</button>
        <button @click="filtroFecha('MAN');">Mes anterior</button>
        <button @click="filtroFecha('AAC');">Año actual</button>
        <button @click="filtroFecha('AAN');">Año anterior</button>
      </div>
    </div>
    <div class="col-md-4"></div>
  </div>
  
  <!-- Rango de fechas para el reporte de ventas -->
  <div class="row" v-if="mostrarRangoFechas">
    <div class="col-md-3"></div>
    <div class="col-md-6">
      <div class="text-center h4 m-1">
        Seleccione un rango de fechas para el reporte de ventas
      </div>
      <div>
        <div class="col-md-2 p-0">
          <div >&nbsp;</div>
          <button class="btn btn-info" @click="notFiltroRangoFecha();">
            <i class="fa fa-arrow-left" aria-hidden="true"></i> Regresar
          </button>
        </div>
        <div class="col-md-4 p-0" >
          <div>Fecha Inicio</div>
          <input type="date" :min="selected.sucursal.f_ini_min" :max="selected.sucursal.f_ini_max"  class="form-control" placeholder="" id="buc_sucursal_FechaIni_sucursal" v-model="filtros.fechaIni">
        </div>
        <div class="col-md-4 p-0">
          <div>Fecha Fin</div>
          <input type="date" :max="selected.sucursal.fvence_fin_min" :min="selected.sucursal.fvence_fin_max" class="form-control" placeholder="" id="buc_sucursal_FechaFin_sucursal" v-model="filtros.fechaFin">
        </div>
        <div class="col-md-2 p-0">
          <div >&nbsp;</div>
          <button class="btn btn-primary" @click="selectRangoFechas(); mostrarRangoFechas = false;">
            Filtrar <i class="fa fa-arrow-right" aria-hidden="true"></i>
          </button>
        </div>
      </div>
    </div>
    <div class="col-md-3"></div>
  </div>
  
  
<!--   <span class="listado"><button @click="addFiltro = true;">Filtros Adicionales</button></span> -->
  
  
  
  
<!--   <div class="row" v-if="addFiltro">
    <div class="col-md-4"></div>
    <div class="col-md-4">
      <div class="text-center h4 m-1">
      </div>
      <div class="listado">
        <button @click="step = 3; buscarArticulos();">Articulo</button>
      </div>
    </div>
    <div class="col-md-4"></div>
  </div> -->
  
  <!-- Dia operativo -->
  <div class="row" v-if="mostrarDiaOp">
    <div class="col-md-2"></div>
    <div class="col-md-8">
      <div class="text-center h4 m-1">
        
        
   
        <br>
        ¿ De que día operativo necesita el reporte de ventas ?
      </div>
      <button class="btn btn-info" @click="mostrarDiaOp = false; mostrarMenuPrincipal = true; ">
        <i class="fa fa-arrow-left" aria-hidden="true"></i> Regresar
      </button>
      <div class="listado">
        <div>
          <div class="row">
            <form v-on:submit.prevent="buscarDiaOperativo()">
              <div class="col-md-2 p-0" >
                <div>ID </div>
                <input type="number" class="form-control" placeholder="" id="buc_diaOp_Id_diaOp" v-model="buc_diaOp_Id_diaOp">
              </div>
              <div class="col-md-2 p-0">
                <div>Dia operativo</div>
                <input type="number" class="form-control" placeholder="" id="buc_diaOp_Num_diaOp" v-model="buc_diaOp_Num_diaOp">
              </div>
              <div class="col-md-3 p-0">
                <div>Fecha</div>
                <input type="date" class="form-control" placeholder="" id="buc_diaOp_Fecha_diaOp" v-model="buc_diaOp_Fecha_diaOp">
              </div>
              <div class="col-md-3 p-0">
                <div>Vendedor </div>
                <input type="text" class="form-control" placeholder="" id="buc_diaOp_Vendedor_diaOp" v-model="buc_diaOp_Vendedor_diaOp">
              </div>
              <div class="col-md-2">
                <div>&nbsp;</div>
                <input type="submit" name="commit" value="Filtrar" class="btn btn-primary">
              </div>
            </form>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="table-responsive" v-if="diasOp.length > 0">
                <table class="table table-striped table-sm text-center">
                  <thead>
                    <tr>
                      <td>
                        <div>Id</div>
                      </td>
                      <td>
                        <div>Dia Operativo</div>
                      </td>
                      <td>
                        <div>Fecha</div>
                      </td>
                      <td>
                        <div>Vendedor</div>
                      </td>
                      <td>
                        <div>Acción</div>
                      </td>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="dO in diasOp" >
                      <td>
                        <div>{{dO.Id}}</div>
                      </td>
                      <td>
                        <div>{{dO.DiaO}}</div>
                      </td>
                      <td>
                        <div>{{dO.Fecha}}</div>
                      </td>
                      <td>
                        <div>{{dO.Ve}}</div>
                      </td>
                      <td>
                        <div class="btn btn-sm btn-primary" style="margin:2px;" v-on:click="selectDiaO(dO.DiaO); mostrarDiaOp = false;" >
                          Seleccionar
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-else class="text-center">
                <div class="sinRes d-inline">
                  Sin resultados
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
<!--       <div>
        {{filtros.fecha}},{{filtros.fechaIni}},{{filtros.fechaFin}}
      </div> -->
    </div>
<!--     <div class="col-md-2"></div>
    <span class="listado"><button @click="addFiltro = true;">Filtros Adicionales</button></span> -->
  </div>
  
  
  
  
  <!-- FILTRO CLIENTE -->
  <div class="row" v-if="mostrarCliente">
    <div class="col-md-2"></div>
    <div class="col-md-8">
      <div class="text-center h4 m-1">
        
        <br>
        ¿ De que Cliente necesita el reporte de ventas ?
      </div>
      <button class="btn btn-info" @click="mostrarCliente = false; ">
        <i class="fa fa-arrow-left" aria-hidden="true"></i> Regresar
      </button>
      <div class="listado">
        <div>
          <div class="row">
            <form v-on:submit.prevent="buscarClientes()">
              <div class="col-md-2 p-0" >
                <div>ID</div>
                <input type="number" class="form-control" placeholder="" id="buc_cliente_Id_cliente" v-model="buc_cliente_Id_cliente">
              </div>
              <div class="col-md-2 p-0">
                <div>Nombre</div>
                <input type="text" class="form-control" placeholder="" id="buc_cliente_Nombre_cliente" v-model="buc_cliente_Nombre_cliente">
              </div>
              <div class="col-md-3 p-0">
                <div>Nombre Comercial</div>
                <input type="text" class="form-control" placeholder="" id="buc_cliente_NombreComercial_cliente" v-model="buc_cliente_NombreComercial_cliente">
              </div>
              <div class="col-md-2">
                <div>&nbsp;</div>
                <input type="submit" name="commit" value="Filtrar" class="btn btn-primary">
              </div>
            </form>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="table-responsive" v-if="clientes.length > 0">
                <table class="table table-striped table-sm text-center">
                  <thead>
                    <tr>
                      <td>
                        <div>Id</div>
                      </td>
                      <td>
                        <div>Nombre</div>
                      </td>
                      <td>
                        <div>Nombre Comercial</div>
                      </td>
                      <td>
                        <div>Acción</div>
                      </td>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="clte in clientes" >
                      <td>
                        <div>{{clte.Cve_Clte}}</div>
                      </td>
                      <td>
                        <div>{{clte.RazonComercial}}</div>
                      </td>
                      <td>
                        <div>{{clte.RazonSocial}}</div>
                      </td>
                      <td>
                        <div class="btn btn-sm btn-primary" style="margin:2px;" v-on:click="selectCliente(clte.Cve_Clte); mostrarCliente = false;">
                          Seleccionar
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-else class="text-center">
                <div class="sinRes d-inline">
                  Sin resultados
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
<!--       <div>
        {{filtros.fecha}},{{filtros.fechaIni}},{{filtros.fechaFin}}
      </div> -->
    </div>
<!--     <div class="col-md-2"></div>
    <span class="listado"><button @click="addFiltro = true;">Filtros Adicionales</button></span> -->
  </div>
  
  
  
  <!-- Filtro de RUTAS -->
  <div class="row" v-if="mostrarRutas">
    <div class="col-md-2"></div>
    <div class="col-md-8">
      <div class="text-center h4 m-1">
        
<!-- Saber que burbuja esta antes que yo?-->
        
       
        ¿ De que Ruta necesita el reporte de ventas ?
      </div>
      <button class="btn btn-info" @click="mostrarRutas = false; ">
        <i class="fa fa-arrow-left" aria-hidden="true"></i> Regresar
      </button>
      <div class="listado">
        <div>
          <div class="row">
            <form v-on:submit.prevent="buscarRutas()">
              <div class="col-md-3 p-0">
                <div>Clave Ruta</div>
                <input type="text" class="form-control" placeholder="" id="buc_ruta_id_ruta" v-model="buc_ruta_id_ruta">
              </div>
              <div class="col-md-3 p-0">
                <div>Nombre Ruta</div>
                <input type="text" class="form-control" placeholder="" id="buc_ruta_nombre_ruta" v-model="buc_ruta_nombre_ruta">
              </div>
              <div class="col-md-3">
                <div>&nbsp;</div>
                <input type="submit" name="commit" value="Filtrar" class="btn btn-primary">
              </div>
            </form>
            
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="table-responsive" v-if="rutas.length > 0">
                <table class="table table-striped table-sm text-center">
                  <thead>
                    <tr>
                      <td>
                        <div>Clave</div>
                      </td>
                      <td>
                        <div>Nombre ruta</div>
                      </td>
                      <td>
                        <div>Acción</div>
                      </td>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="ruta in rutas" >
                      <td>
                        <div>{{ruta.ID_Ruta}}</div>
                      </td>
                      <td>
                        <div>{{ruta.cve_ruta}}</div>
                      </td>
                      <td>
                        <div class="btn btn-sm btn-primary" style="margin:2px;" v-on:click="selectRuta(ruta.cve_ruta); mostrarRutas = false;">
                          Seleccionar
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-else class="text-center">
                <div class="sinRes d-inline">
                  Sin resultados
                </div>
              </div>
            </div>
          </div>
          
          
        </div>
      </div>
<!--       <div>
        {{filtros.fecha}},{{filtros.fechaIni}},{{filtros.fechaFin}}
      </div> -->
    </div>
    <div class="col-md-2"></div>
  </div>
  
  <!-- Filtro de Articulo -->
  <div class="row" v-if="mostrarArticulo">
    <div class="col-md-2"></div>
    <div class="col-md-8">
      <div class="text-center h4 m-1">
        
       
        ¿ De que Articulo necesita el reporte de ventas ?
      </div>
      <button class="btn btn-info" @click="mostrarArticulo = false;">
        <i class="fa fa-arrow-left" aria-hidden="true"></i> Regresar
      </button>
      <div class="listado">
        <div>
          <div class="row">
            <form v-on:submit.prevent="buscarArticulos()">
              <div class="col-md-2 p-0">
                <div>Clave Articulo</div>
                <input type="text" class="form-control" placeholder="" id="buc_articulo_ClaveArticulo_articulo" v-model="buc_articulo_ClaveArticulo_articulo">
              </div>
              <div class="col-md-2 p-0" >
                <div>CodigoBarras</div>
                <input type="number" class="form-control" placeholder="" id="buc_articulo_CodBarras_articulo" v-model="buc_articulo_CodBarras_articulo">
              </div>
              <div class="col-md-3 p-0">
                <div>Producto</div>
                <input type="text" class="form-control" placeholder="" id="buc_articulo_DesArticulo_articulo" v-model="buc_articulo_DesArticulo_articulo">
              </div>
              <div class="col-md-2">
                <div>&nbsp;</div>
                <input type="submit" name="commit" value="Filtrar" class="btn btn-primary">
              </div>
            </form>
            
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="table-responsive" v-if="articulos.length > 0">
                <table class="table table-striped table-sm text-center">
                  <thead>
                    <tr>
                      <td>
                        <div>Clave</div>
                      </td>
                      <td>
                        <div>CodBarras</div>
                      </td>
                      <td>
                        <div>Producto</div>
                      </td>
                      <td>
                        <div>Acción</div>
                      </td>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="arti in articulos" >
                      <td>
                        <div>{{arti.cve_articulo}}</div>
                      </td>
                      <td>
                        <div>{{arti.cve_codprov}}</div>
                      </td>
                      <td>
                        <div>{{arti.des_articulo}}</div>
                      </td>
                      <td>
                        <div class="btn btn-sm btn-primary" style="margin:2px;" v-on:click="selectArticulo(arti.cve_articulo); mostrarArticulo = false;">
                          Seleccionar
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-else class="text-center">
                <div class="sinRes d-inline">
                  Sin resultados
                </div>
              </div>
            </div>
          </div>
          
          
        </div>
      </div>
<!--       <div>
        {{filtros.fecha}},{{filtros.fechaIni}},{{filtros.fechaFin}}
      </div> -->
    </div>
    <div class="col-md-2"></div>
  </div>
  
  
  
  <!-- Burbujas Filtros Anteriores -->
  <!-- Burbuja Ruta -->
  <!--div class="d-inline selectBorder" v-if="selected.ruta != ''" data-comment="Interfaz cuando ya hay una ruta seleccionada">
    <div class="d-inline">
      Ruta: {{selected.ruta.cve_ruta}}
      <button class="btn btn-xs btn-secondary br50" type="button" v-on:click="retrySelection('ruta')"><i class="fa fa-times" aria-hidden="true"></i></button>
    </div>
  </div-->
  <!-- Burbuja Dia Operativo -->
  <!--div class="d-inline selectBorder" v-if="selected.ruta != '' && selected.diaOp != ''" data-comment="Interfaz cuando hay ruta y dia operativo seleccioados">
    <div class="d-inline">
      Dia operativo: {{selected.diaOp.DiaO}} ({{selected.diaOp.Fecha}}) {{selected.diaOp.Ve}}
      <button class="btn btn-xs btn-secondary br50" type="button" v-on:click="retrySelection('diaOp')"><i class="fa fa-times" aria-hidden="true"></i></button>
    </div>
  </div-->
  <!-- Burbuja Cliente -->
  <!--div class="d-inline selectBorder" v-if="selected.ruta != '' && selected.diaOp != '' && selected.cliente != ''" data-comment="Interfaz cuando hay ruta y dia operativo seleccioados">
    <div class="d-inline">
      <span v-if="selected.cliente.Cve_Clte != undefined">Cliente: {{selected.cliente.Cve_Clte}}</span>
      <span v-else>Cliente: Todos</span>
      <button class="btn btn-xs btn-secondary br50" type="button" v-on:click="retrySelection('cliente')"><i class="fa fa-times" aria-hidden="true"></i></button>
    </div>
  </div-->
  <!-- Burbuja Articulos -->
  <!--div class="d-inline selectBorder" v-if="selected.ruta != '' && selected.diaOp != '' && selected.cliente != '' && selected.articulo != ''" data-comment="Interfaz cuando hay ruta y dia operativo seleccioados">
    <div class="d-inline">
       <span v-if="selected.articulo.cve_articulo != undefined">Articulo: {{selected.articulo.cve_articulo}}</span>
      <span v-else>Articulo: Todos</span>
      <button class="btn btn-xs btn-secondary br50" type="button" v-on:click="retrySelection('articulo')"><i class="fa fa-times" aria-hidden="true"></i></button>
    </div>
  </div-->
  <!-- Burbuja Sucursal -->
  <!--div class="d-inline selectBorder" v-if="selected.ruta != '' && selected.diaOp != '' && selected.cliente != '' && selected.articulo !='' && selected.sucursal != ''" data-comment="Interfaz cuando hay ruta y dia operativo seleccioados">
    <div class="d-inline">
      Sucursal: {{selected.sucursal.nombre}} 
      <button class="btn btn-xs btn-secondary br50" type="button" v-on:click="retrySelection('sucursal')"><i class="fa fa-times" aria-hidden="true"></i></button>
    </div>
  </div-->
  <!-- Filtros -->
  <div class="row" v-if="addFiltro" aria-hidden="false" style="margin: 10px;" >
    <form v-on:submit.prevent="filtroVentas">
      <div class="row">
        <div class="col-md-2 p-0">
          <div>Articulo</div>
          <button class="btn btn-primary" @click=" mostrarArticulo = true; buscarArticulos();">Seleccionar Articulo</button>
<!--           <input type="number" class="form-control" placeholder="" id="buc_venta_Cliente_venta" v-model="buc_venta_Cliente_venta"> -->
        </div>
        <div class="col-md-2 p-0">
          <div>Ruta</div>
          <button class="btn btn-primary" @click="mostrarRutas = true; buscarRutas();">Seleccionar Ruta</button>
<!--           <input type="number" class="form-control" placeholder="" id="buc_venta_RutaId_venta" v-model="buc_venta_RutaId_venta"> -->
        </div>
        <div class="col-md-2 p-0">
          <div>Cliente</div>
          <button class="btn btn-primary" @click="mostrarCliente = true; buscarClientes();">Seleccionar Cliente</button>
<!--           <input type="number" class="form-control" placeholder="" id="buc_venta_Cliente_venta" v-model="buc_venta_Cliente_venta"> -->
        </div>
        <div class="col-md-2 p-0" >
          <div>Responsable</div>
          <input type="text" class="form-control" placeholder="" id="buc_venta_Responsable_venta" v-model="buc_venta_Responsable_venta">
        </div>
        <div class="col-md-2 p-0">
          <div>Nombre Comercial</div>
          <input type="text" class="form-control" placeholder="" id="buc_venta_NombreComercial_venta" v-model="buc_venta_NombreComercial_venta">
        </div>
        <div class="col-md-2 p-0">
          <div>Folio</div>
          <input type="number" class="form-control" placeholder="" id="buc_venta_Folio_venta" v-model="buc_venta_Folio_venta">
        </div>
        <div class="col-md-2 p-0">
          <div>Tipo</div>
<!--           <input type="text" class="form-control" placeholder="" id="buc_venta_Tipo_venta" v-model="buc_venta_Tipo_venta"> -->
          <select class="form-control" id="buc_venta_Tipo_venta" v-model="buc_venta_Tipo_venta">
            <option value="" selected >Todas</option>
            <option value="Credito">Credito</option>
            <option value="Contado">Contado</option>
            <option value="Obsequio">Obsequio</option>
          </select>
        </div>
        <div class="col-md-2 p-0">
          <div>Metodo de pago</div>
<!--           <input type="text" class="form-control" placeholder="" id="buc_venta_MetodoPago_venta" v-model="buc_venta_MetodoPago_venta"> -->
          <select class="form-control" id="buc_venta_MetodoPago_venta" v-model="buc_venta_MetodoPago_venta">
            <option value="" selected >Todas</option>
            <option value="Efectivo">Efectivo</option>
            <option value="Transferencia">Transferencia</option>
          </select>
        </div>
        <div class="col-md-2 p-0">
          <div>Entregas:</div>
          <input type="checkbox" name="Entregas" id="Entregas" value="1" v-model="filtros.entregas">
        </div>
        <div class="col-md-2 p-0">
          <div>Comisiones:</div>
          <input type="checkbox" name="Comisiones" id="Comisiones" value="1" v-model="checkComisiones">
        </div>
        <div class="col-md-2 p-0" v-if="checkComisiones == true">
          <div>Comisiones:</div>
          <select class="form-control" id="buc_venta_Comisiones_venta" v-model="buc_venta_Comisiones_venta">
            <option value="" selected >Todas</option>
            <option value="sin">Sin Comision</option>
            <option value="con">Con comision</option>
          </select>
        </div>
        <div class="col-md-2 p-0">
          <div>Cobranza</div>
          <input type="checkbox" name="Cobranza" id="Cobranza" value="1" v-model="checkCobranza">
        </div>
<!--         <div class="col-md-2 p-0" v-if="checkCobranza == true">
          <div>Fechas:</div>
          <select class="form-control" >
            <option value="" selected >Todas</option>
            <option value="">Fecha Registro</option>
            <option value="">Fecha Vencimiento</option>
          </select>
        </div> -->
        <div class="col-md-2 p-0" v-if="checkCobranza == true">
          <div>Sucursales</div>
            <select class="form-control" id="sucursal" v-model="indexSucursalSelected" @change="setSucursal()">
              <option value="" selected >Todas</option>
              <option :value="index" v-for="(ve, index) in ventas" >{{ve.sucursalNombre}}</option>
            </select>
        </div>
        
        <div class="col-md-2 p-0">
          <div>Visita sin venta</div>
          <input type="checkbox" name="visitaSV" id="visitaSV" value="1" v-model="checkvisitaSV">
        </div>
        
        <div class="col-md-2 p-0">
          <div>Cancelada</div>
          <input type="checkbox" name="Cancelada" id="Cancelada" value="1" v-model="filtros.cancelada">
        </div>
        
        <div class="col-md-2">
          <div>&nbsp;</div>
          <input type="submit" name="commit" value="Filtrar" class="btn btn-primary">
        </div>
      </div>
    </form>
  </div>
  
  
  <!-- Totales -->
  <div style="border-top: 1px solid #80808073; margin-top: 40px;" v-if="resultadosReady"></div>
  <div class="row" style="margin-top: 20px; margin-bottom: 20px; font-weight: bold; text-align:center;" v-if="resultadosReady">
    <div class="col-md-2">Total Efectivo <h2 class="totales">${{totales.totalEfectivo}}</h2></div>
    <div class="col-md-2">Efectivo <h2 class="totales">${{totales.efectivo}}</h2></div>
    <div class="col-md-2">Otros depositos <h2 class="totales">${{totales.otrosDepositos}}</h2></div>
    <div class="col-md-2">Total Contado <h2 class="totales">${{totales.totalContado}}</h2></div>
    <div class="col-md-2">Total Credito <h2 class="totales">${{totales.totalCredito}}</h2></div>
    <div class="col-md-2">Total Venta <h2 class="totales">${{totales.totalVenta}}</h2></div>
    <div class="col-md-2" v-if="checkComisiones == true">Total Comisiónes <h2 class="totales">${{totales.totalComisiones}}</h2></div>
    <div class="col-md-2" v-if="checkCobranza == true">Total Cobranza <h2 class="totales">${{totales.totalCobranza}}</h2></div>
    
<!--     <div class="col-md-2" v-if="checkCobranza == true">Saldo Vencido <h2 class="totales">${{totales.saldoVencido}}</h2></div>
    <div class="col-md-2" v-if="checkCobranza == true">Saldo Corriente <h2 class="totales">${{totales.saldoCorriente}}</h2></div>
    <div class="col-md-2" v-if="checkCobranza == true">Saldo Actual <h2 class="totales">${{totales.saldoActual}}</h2></div> -->
  </div>
  <div style="border-top: 1px solid #80808073; margin-top: 40px;" v-if="resultadosReady"></div>
  
  
 
  
  <!-- Buscador para Rutas Selector ? -->
  <!--div v-if="selected.ruta == ''" data-comment="Interfaz para la selección de rutas">
    <h3>Seleccione ruta</h3>
    <div class="row">
      <div class="col-md-3">
        <div>Comience a escribir la ruta que busca</div>
        <input type="text" class="form-control" id="ruta_buscador" aria-describedby="basic-addon3" v-on:keyup="buscarRuta()" v-model="ruta_buscador">
      </div>
      <div class="col-md-9" v-if="this.rutas.length > 0">
        <div>Rutas encontradas:</div>
        <div>
          <div class="btn btn-sm btn-primary" v-for="r in rutas" style="margin:2px;" v-on:click="doSelected('ruta',r)">
            {{r.cve_ruta}}
          </div>
        </div>
      </div>
      <div class="col-md-6" v-if="this.rutas.length == 0">
        <div class="sinRes d-inline">
          Sin resultados
        </div>
      </div>
    </div>
  </div-->
  
  <!-- Tabla de clientes para seleccion ? -->
  <div v-if="selected.ruta != '' && selected.diaOp != '' && selected.cliente == ''" data-comment="Interfaz para cuando ya está seleccionada una ruta y dia operativo, se podra seleccionar cliente">    
    <h3>Seleccione Cliente</h3>
    <div class="row">
      <form v-on:submit.prevent="buscarClientes()">
        <div class="col-md-2 p-0" >
          <div>ID</div>
          <input type="number" class="form-control" placeholder="" id="buc_cliente_Id_cliente" v-model="buc_cliente_Id_cliente">
        </div>
        <div class="col-md-2 p-0">
          <div>Nombre</div>
          <input type="text" class="form-control" placeholder="" id="buc_cliente_Nombre_cliente" v-model="buc_cliente_Nombre_cliente">
        </div>
        <div class="col-md-3 p-0">
          <div>Nombre Comercial</div>
          <input type="text" class="form-control" placeholder="" id="buc_cliente_NombreComercial_cliente" v-model="buc_cliente_NombreComercial_cliente">
        </div>
        <div class="col-md-2">
          <div>&nbsp;</div>
          <input type="submit" name="commit" value="Filtrar" class="btn btn-primary">
        </div>
      </form>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="table-responsive" v-if="clientes.length > 0">
          <table class="table table-striped table-sm text-center">
            <thead>
              <tr>
                <td>
                  <div>Id</div>
                </td>
                <td>
                  <div>Nombre</div>
                </td>
                <td>
                  <div>Nombre Comercial</div>
                </td>
                <td>
                  <div>Acción</div>
                </td>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="3">
                  <div>Todos los clientes</div>
                </td>
                <td>
                  <div class="btn btn-sm btn-primary" style="margin:2px;" v-on:click="doSelected('cliente','todos')">
                    Seleccionar
                  </div>
                </td>
              </tr>
              <tr v-for="clte in clientes" >
                <td>
                  <div>{{clte.Cve_Clte}}</div>
                </td>
                <td>
                  <div>{{clte.RazonSocial}}</div>
                </td>
                <td>
                  <div>{{clte.RazonComercial}}</div>
                </td>
                <td>
                  <div class="btn btn-sm btn-primary" style="margin:2px;" v-on:click="doSelected('cliente',clte)">
                    Seleccionar
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center">
          <div class="sinRes d-inline">
            Sin resultados
          </div>
        </div>
      </div>
    </div>
  </div>  

  <!-- Tabla de articulos para seleccion !! -->
  <div v-if="selected.ruta != '' && selected.diaOp != '' && selected.cliente != '' && selected.articulo =='' " data-comment="Interfaz para cuando ya está seleccionada una ruta, dia operativo,cliente, se podra seleccionar producto">
    <h3>Seleccione Articulos</h3>
    <div class="row">
      <form v-on:submit.prevent="buscarArticulos()">
        <div class="col-md-2 p-0">
          <div>Clave Articulo</div>
          <input type="text" class="form-control" placeholder="" id="buc_articulo_ClaveArticulo_articulo" v-model="buc_articulo_ClaveArticulo_articulo">
        </div>
        <div class="col-md-2 p-0" >
          <div>CodigoBarras</div>
          <input type="number" class="form-control" placeholder="" id="buc_articulo_CodBarras_articulo" v-model="buc_articulo_CodBarras_articulo">
        </div>
        <div class="col-md-3 p-0">
          <div>Producto</div>
          <input type="text" class="form-control" placeholder="" id="buc_articulo_DesArticulo_articulo" v-model="buc_articulo_DesArticulo_articulo">
        </div>
        <div class="col-md-2">
          <div>&nbsp;</div>
          <input type="submit" name="commit" value="Filtrar" class="btn btn-primary">
        </div>
      </form>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="table-responsive" v-if="articulos.length > 0">
          <table class="table table-striped table-sm text-center">
            <thead>
              <tr>
                <td>
                  <div>Clave</div>
                </td>
                <td>
                  <div>CodBarras</div>
                </td>
                <td>
                  <div>Producto</div>
                </td>
                <td>
                  <div>Acción</div>
                </td>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="3">
                  <div>Seleccionar Todos</div>
                </td>
                <td>
                  <div class="btn btn-sm btn-primary" style="margin:2px;" v-on:click="doSelected('articulo','todos')">
                    Seleccionar
                  </div>
                </td>
              </tr>
              <tr v-for="arti in articulos" >
                <td>
                  <div>{{arti.cve_articulo}}</div>
                </td>
                <td>
                  <div>{{arti.cve_codprov}}</div>
                </td>
                <td>
                  <div>{{arti.des_articulo}}</div>
                </td>
                <td>
                  <div class="btn btn-sm btn-primary" style="margin:2px;" v-on:click="doSelected('articulo',arti)">
                    Seleccionar
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center">
          <div class="sinRes d-inline">
            Sin resultados
          </div>
        </div>
      </div>
    </div>
  </div>   
  
<!-- --------------------------------  ? -------------------------------->
  <div v-if="selected.ruta != '' && selected.diaOp != '' && selected.cliente != '' && selected.articulo !=''" data-comment="Interfaz para cuando ya está seleccionada una ruta, dia operativo,cliente, se podra seleccionar producto">
    <h3>Seleccione Sucursal</h3>
    <div class="row mt-1" style="margin-bottom: 20px;">
      <form v-on:submit.prevent="buscarVentas">
        <div class="col-md-3 p-0">
          <div>Sucursal: <span class="badge">Selecciona una opcion</span></div>
          <select class="form-control" id="sucursal" v-model="indexSucursalSelected" @change="setSucursal()">
            <option value="" selected >Sucursales:</option>
            <option :value="index" v-for="ve in ventas" >{{ve.sucursalNombre}}</option>
          </select>
        </div>
        <div class="col-md-3 p-0" >
          <div>Fecha Inicio</div>
          <input type="date" :min="selected.sucursal.f_ini_min" :max="selected.sucursal.f_ini_max"  class="form-control" placeholder="" id="buc_sucursal_FechaIni_sucursal" v-model="buc_sucursal_FechaIni_sucursal">
        </div>
        <div class="col-md-3 p-0">
          <div>Fecha Fin</div>
          <input type="date" :max="selected.sucursal.fvence_fin_min" :min="selected.sucursal.fvence_fin_max" class="form-control" placeholder="" id="buc_sucursal_FechaFin_sucursal" v-model="buc_sucursal_FechaFin_sucursal">
        </div>
        <div class="col-md-1 p-0" >
          <div>Entregas:</div>
          <input type="checkbox" class="form-control" placeholder="" id="Entregas">
        </div>
        <div class="col-md-2 p-0" >
          <span class="badge">Comienza a buscar</span>
          <input type="submit" name="commit" value="Buscar" class="btn btn-block btn-primary " data-disable-with="Buscando">
        </div>
      </form>
    </div>
  <!--     <div class="col-md-1">
        <div>Entregas:</div>
        <div class="mt-1">
          <input type="checkbox" name="Entregas" id="Entregas" value="1" />
        </div>
      </div>
      <div class="col-md-3">
        <div>&nbsp;</div>
        <input type="submit" name="commit" value="Buscar" class="btn btn-primary mt-1" data-disable-with="Buscando">
      </div> -->
  </div>
<!-- --------------------------------  ? -------------------------------->
  
  <!-- Tabla Maestra -->
  <div v-if="resultadosReady" data-comment="Interfaz para cuando ya está seleccionada una ruta, dia operativo,cliente, se podra seleccionar producto">
    
    
    <!-- Tabla Principal -->
    <div class="row">
      <div class="col-md-12">
        <div class="table-responsive" v-if="ventas.length > 0">
          <table class="table table-striped table-sm text-center">
            <thead>
              <tr>
                <td>
                  <div>Sucursal</div>
                </td>
                <td>
                  <div>Fecha</div>
                </td>
                <td>
                  <div>DO</div>
                </td>
                <td>
                  <div>Ruta</div>
                </td>
                <td>
                  <div>Cliente</div>
                </td>
                <td>
                  <div>Responsable</div>
                </td>
                <td>
                  <div>Nombre Comercial</div>
                </td>
                <td>
                  <div>Folio</div>
                </td>
                <td>
                  <div>Articulo</div>
                </td>
                <td>
                  <div>Tipo</div>
                </td>
                <td>
                  <div>Método de pago</div>
                </td>
                <td>
                  <div>Importe</div>
                </td>
                <td>
                  <div>IVA</div>
                </td>
                <td>
                  <div>Descuento</div>
                </td>
                <td>
                  <div>Total</div>
                </td>
                <td>
                  <div>Comisiones</div>
                </td>
                <td>
                  <div>Utilidad</div>
                </td>
                <td>
                  <div>Cajas</div>
                </td>
                <td>
                  <div>Piezas</div>
                </td>
                <td>
                  <div>Cancelada</div>
                </td>
                <td>
                  <div>Vendedor</div>
                </td>
                <td>
                  <div>Ayudante 1</div>
                </td>
                <td>
                  <div>Ayudante 2</div>
                </td>
                <td>
                  <div>Promociones</div>
                </td>
                <!--Aqui inicia Cobranza-->
                <template v-if="checkCobranza == true">
                  <td>
                    <div>Total Cobranza</div>
                  </td>
<!--                   <td>
                    <div>Documento</div>
                  </td>
                  <td>
                    <div>Saldo Inicial</div>
                  </td>
                  <td>
                    <div>Abono</div>
                  </td>
                  <td>
                    <div>Saldo Actual</div>
                  </td>
                  <td>
                    <div>Fecha Registro</div>
                  </td>
                  <td>
                    <div>Fecha Vencimiento</div>
                  </td>
                  <td>
                    <div>Tipo Doc</div>
                  </td> -->
                </template>
                <template v-if="checkvisitaSV == true">
                  <td>
                    <div>Motivo de No Venta</div>
                  </td>
                </template>
                <td>
                  <div>Acción</div>
                </td>
              </tr>
            </thead>
            <tbody>
              <tr v-for="ve in ventas" >
                <td>
                  <div>{{ve.sucursalNombre}}</div>
                </td>
                <td>
                  <div>{{displayDate(ve.Fecha)}}</div>
                </td>
                <td>
                  <div>{{ve.DiaOperativo}}</div>
                </td>
                <td>
                  <div>{{ve.rutaName}}</div>
                </td>
                <td>
                  <div>{{ve.Cliente}}</div>
                </td>
                <td>
                  <div>{{ve.Responsable}}</div>
                </td>
                <td>
                  <div>{{ve.nombreComercial}}</div>
                </td>
                <td>
                  <div>{{ve.Folio}}</div>
                </td>
                <td>
                  <div>{{ve.Articulo}}</div>
                </td>
                <td>
                  <div>{{ve.Tipo}}</div>
                </td>
                <td>
                  <div>{{ve.metodoPago}}</div>
                </td>
                <td>
                  <div>{{ve.Importe}}</div>
                </td>
                <td>
                  <div>{{ve.IVA}}</div>
                </td>
                <td>
                  <div>{{ve.Descuento}}</div>
                </td>
                <td>
                  <div>{{ve.Total}}</div>
                </td>
                <td>
                  <div>{{ve.Comisiones}}</div>
                </td>
                <td>
                  <div>{{ve.Utilidad}}</div>
                </td>
                <td>
                  <div>{{ve.Cajas}}</div>
                </td>
                <td>
                  <div>{{ve.Piezas}}</div>
                </td>
                <td>
                  <div>{{ve.Cancelada}}</div>
                </td>
                <td>
                  <div>{{ve.Vendedor}}</div>
                </td>
                <td>
                  <div>{{ve.Ayudante1}}</div>
                </td>
                <td>
                  <div>{{ve.Ayudante2}}</div>
                </td>
                <td>
                  <div>{{ve.Promociones}}</div>
                </td>
                <template v-if="checkCobranza == true">
<!--                   <td>
                    <div>{{ve.Documento}}</div>
                  </td>
                  <td>
                    <div>{{ve.saldoInicial}}</div>
                  </td>
                  <td>
                    <div>{{ve.Abono}}</div>
                  </td>
                  <td>
                    <div>{{ve.saldoActual}}</div>
                  </td>
                  <td>
                    <div>{{ve.fechaRegistro}}</div>
                  </td>
                  <td>
                    <div>{{ve.fechaVence}}</div>
                  </td>
                  <td>
                    <div>{{ve.tipoDoc}}</div>
                  </td> -->
                </template>
                <template v-if="checkvisitaSV == true">
                </template>
<!--                 <td>
                  <div class="btn btn-sm btn-primary" style="margin:2px;" v-on:click="doSelected('diaOp',dO)">
                    Seleccionar
                  </div>
                </td> -->
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center">
          <div class="sinRes d-inline">
            Sin resultados
          </div>
          
        </div>
      </div>
    </div>
  </div>
  
 
  
  
</div>     


<?php include "_core_footer_scripts.php" ?>
<?php include "_core_footer_scripts_vue.php" ?>

<script>
  
  Date.prototype.addDays = function(days) {
    var date = new Date(this.valueOf());
    date.setDate(date.getDate() + days);
    return date;
  }
  
  window.app = new Vue({
    el: '#app',
    data: {
      ruta_buscador:"",
      rutas:[],
      buc_ruta_id_ruta:"",
      buc_ruta_nombre_ruta:"",
      buc_diaOp_Id_diaOp:"",
      buc_diaOp_Num_diaOp:"",
      buc_diaOp_Fecha_diaOp:"",
      buc_diaOp_Vendedor_diaOp:"",
      diasOp:[],
      inventario:[],
      clientes:[],
      buc_cliente_Id_cliente:"",
      buc_cliente_Nombre_cliente:"",
      buc_cliente_NombreComercial_cliente:"",
      articulos:[],
      buc_articulo_Id_articulo:"",
      buc_articulo_ClaveArticulo_articulo:"",
      buc_articulo_DesArticulo_articulo:"",
      buc_articulo_CodBarras_articulo:"",
      sucursales:[],
      buc_sucursal_FechaIni_sucursal:"",
      buc_sucursal_FechaFin_sucursal:"",
      indexSucursalSelected: "",
      ventas:[],
      buc_venta_Cliente_venta:"",
      buc_venta_Responsable_venta:"",
      buc_venta_NombreComercial_venta:"",
      buc_venta_Folio_venta:"",
      buc_venta_Tipo_venta:"",
      buc_venta_MetodoPago_venta:"",
      buc_venta_RutaId_venta:"",
      buc_venta_Comisiones_venta:"",
      burbujaDiaOperativo:false,
      selected:
      {
        ruta:"",
        diaOp:"",
        cliente:"", 
        articulo:"",
        sucursal:"",
        venta:"",
      },
      filtros:{
        fecha:"",
        fechaIni:"",
        fechaFin:""
      },
      step:0,
      resultadosReady: false,
      filtroEspecifico: "",
      addFiltro: false,
      
      totales: 
      {
        totalEfectivo: 0,
        efectivo: 0,
        otrosDepositos: 0,
        totalContado: 0,
        totalCredito: 0,
        totalVenta: 0,
        totalComisiones: 0,
        totalCobranza: 0,
        saldoVencido:0,
        saldoCorriente:0,
        saldoActual:0,
      },
      
      /*
      data: 
      {
        diaO: "",             // 2020-05-21 (DiasO.DiaO)
        fecha: "",            // 2020-05-21 (DiasO.Fecha)
        fechaIni: "",         // 2020-05-21 (DiasO.Fecha) or (DiasO.Fvence en entregas == 1) 
        fechaFin: "",         // 2020-05-21 (DiasO.Fecha) or (DiasO.Fvence en entregas == 1)
        entregas: 0,          // 0 or 1
        idRuta: 0,            // ID (Venta.RutaId)
        idCliente: 0,         // ID (Venta.CodCliente)
        idProducto: 0,        // ID (DetalleVet.Articulo)
        nombreCliente: "",    // Nombre busqueda en %LIKE% (c_cliente.RazonSocial)
        nombreReponsable: "", // Nombre busqueda en %LIKE% (t_vendedores.Nombre)
        idSucursal: 0,        // ID (Venta.IdEmpresa)
        metodoPago: "",       // "Efectivo" or "Transferencia" (FormasPag.Forma)
        folio: 0,             // INT (Cobranza.FolioInterno)
        tipo: "",             // "Contado" or "Credito" (Venta.TipoVta)
        comisiones: true      // (DetalleVet.Comisiones)
      }
      */
      mostrarMenuPrincipal:true,
      mostrarRangoFechas:false,
      checkComisiones: false,
      mostrarDiaOp:false,
      checkCobranza: false,
      mostrarRutas: false,
      mostrarCliente: false,
      mostrarArticulo : false,
      checkvisitaSV: false,
    },
    methods:
    {
      
      eliminarBurbujas:function()
      {
        this.buc_diaOp_Id_diaOp = '',
        this.buc_diaOp_Num_diaOp = '',
        this.buc_diaOp_Fecha_diaOp = '',
        this.buc_diaOp_Vendedor_diaOp = '',
        this.filtros.idProducto = '',
        this.filtros.idRuta = '',
        this.buc_venta_RutaId_venta = '',
        this.filtros.idCliente = '', 
        this.buc_venta_Cliente_venta = '',
        this.filtros.nombreReponsable = '', 
        this.buc_venta_Responsable_venta = '',
        this.filtros.nombreCliente='', 
        this.buc_venta_NombreComercial_venta = '',
        this.filtros.folio = '', 
        this.buc_venta_Folio_venta = '',
        this.filtros.tipo = '', 
        this.buc_venta_Tipo_venta = '',
        this.filtros.metodoPago = '', 
        this.buc_venta_MetodoPago_venta = '',
        this.filtros.comisiones = '', 
        this.buc_venta_Comisiones_venta = '', 
        this.checkComisiones = false,
        this.checkCobranza = false,
        this.checkvisitaSV = false,
        this.addFiltro = false;
        this.mostrarCliente = false;
        this.mostrarRutas = false;
        this.mostrarArticulo = false;
      },
      
      
      filtroFecha: function(filtro)
      {
        this.mostrarMenuPrincipal = false;
        switch(filtro)
        {
          case "HOY":
            this.filtros.fecha = this.gDay();
            this.filtros.fechaIni = '';
            this.filtros.fechaFin = '';
            this.filtroEspecifico = "Hoy";
            break;
          case "AYE":
            this.filtros.fecha = this.gDay(-1);
            this.filtros.fechaIni = '';
            this.filtros.fechaFin = '';
            this.filtroEspecifico = "Ayer";
            break;
          case "SAC":
            var diaSem = this.gDay(0,true);
            var toOne = diaSem - 1;
            var toSeven = 7 - diaSem;
            this.filtros.fechaIni = this.gDay(-1*toOne);
            this.filtros.fechaFin = this.gDay(toSeven);
            this.filtros.fecha = '';
            this.filtroEspecifico = "Semana actual";
            break;
          case "SAN":
            var diaSem = this.gDay(0,true);
            var toOne = diaSem - 1;
            var toSeven = 7 - diaSem;
            this.filtros.fechaIni = this.gDay(-1*toOne - 7);
            this.filtros.fechaFin = this.gDay(toSeven - 7);
            this.filtros.fecha = '';
            this.filtroEspecifico = "Semana anterior";
            break;
          case "MAC":
            var anio = this.gDay(0,false,"yyyy");
            var mes = this.gDay(0,false,"mm");
            var d = new Date(anio, parseFloat(mes), 0).getDate();
            this.filtros.fechaIni = anio+"-"+mes+"-01";
            this.filtros.fechaFin = anio+"-"+mes+"-"+d;
            this.filtros.fecha = '';
            this.filtroEspecifico = "Mes actual";
            break;
          case "MAN":
            var anio = this.gDay(-28,false,"yyyy");
            var mes = this.gDay(-28,false,"mm");
            var d = new Date(anio, parseFloat(mes), 0).getDate();
            this.filtros.fechaIni = anio+"-"+mes+"-01";
            this.filtros.fechaFin = anio+"-"+mes+"-"+d;
            this.filtros.fecha = '';
            this.filtroEspecifico = "Mes anterior";
            break;
          case "AAC":
            var anio = this.gDay(0,false,"yyyy");
            this.filtros.fechaIni = anio+"-01-01";
            this.filtros.fechaFin = anio+"-12-31";
            this.filtros.fecha = '';
            this.filtroEspecifico = "Año actual";
            break;
          case "AAN":
            var anio = parseFloat(this.gDay(0,false,"yyyy"))-1;
            this.filtros.fechaIni = anio+"-01-01";
            this.filtros.fechaFin = anio+"-12-31";
            this.filtros.fecha = '';
            this.filtroEspecifico = "Año anterior";
            break;
        }
        this.test(this.filtros);
        this.step = 2;
        this.buc_sucursal_FechaIni_sucursal = this.filtros.fechaIni;
        this.buc_sucursal_FechaFin_sucursal = this.filtros.fechaFin;
        
      },
      filtroDiaOp:function()
      {
        this.buscarDiaOperativo();
        this.mostrarMenuPrincipal = false;
        this.mostrarDiaOp = true;
      },
      
      filtroRangoFecha: function()
      {
        var diaSem = this.gDay(0,true);
        var toOne = diaSem;

        this.filtros.fechaIni = this.gDay(-14);
        this.filtros.fechaFin = this.gDay();
        this.filtros.fecha = '';
        this.filtroEspecifico = "Rango";
        console.log("",this.filtros);

        
        
        this.mostrarMenuPrincipal = false;
        this.mostrarRangoFechas = true;
      },
      notFiltroRangoFecha: function()
      {
        this.mostrarMenuPrincipal = true;
        this.mostrarRangoFechas = false;
        
        
        this.resultadosReady = false;  
        this.filtros.fechaIni = '';
        this.filtros.fechaFin = '';
        this.addFiltro = false;
      },
      
      
      gDay:function(daysToAdd=0,weekDay=false,format='')
      {
        var today = new Date();
        if(daysToAdd != 0)
        {
          today = today.addDays(daysToAdd);
        }
        var dd = String(today.getDate()).padStart(2, '0');
        var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
        var yyyy = today.getFullYear();
        if(weekDay)
        {
          var ds = today.getDay();
          if(ds == 0){ds = 7}
          return ds;
        }
        if(format != "")
        {
          if(format == "yyyy"){return yyyy;}
          if(format == "mm"){return mm;}
          if(format == "dd"){return dd;}
        }
        var todayText = yyyy + "-" +mm + "-" + dd;
        return todayText;
      },
      
      test:function(params={})
      {
        this.$api(API_URL,{queryName:"reporteVentas",data:{params},debug:true},"testSuccess");
      },
      
      testSuccess:function(result)
      {
        console.log(result);
        this.ventas = result.ventas.rows;
        this.resultadosReady = true;
        if(this.filtros.idProducto != undefined &&  this.filtros.idProducto !="")
        {
          console.log("Se filtra por producto");
          this.step = 2;
        }
        if(this.filtros.diaO != undefined && this.filtros.diaO != "")
        {
          console.log("Se filtra por dia operativos");
          this.burbujaDiaOperativo = true;
          this.step = 2;

        }
        
        this.calcularTotales();
        
      },
      calcularTotales: function()
      {
        this.totales.totalEfectivo = 0;
        this.totales.efectivo = 0;
        this.totales.otrosDepositos = 0;
        this.totales.totalContado = 0;
        this.totales.totalCredito = 0;
        this.totales.totalVenta = 0;
        this.totales.totalComisiones = 0;
        this.totales.totalCobranza = 0;
        this.totales.saldoVencido = 0;
        this.totales.saldoCorriente = 0;
        this.totales.saldoActual = 0;
        
        for(var i = 0; i < this.ventas.length; i++)
        {
          var venta = this.ventas[i];
          if(venta.Tipo == "Credito")
          {
            this.totales.totalCredito = parseInt(this.totales.totalCredito) + parseInt(venta.Importe);
          }
          else if(venta.Tipo == "Contado")
          {
            this.totales.totalContado = parseInt(this.totales.totalContado) + parseInt(venta.Importe);
            this.totales.totalEfectivo = parseInt(this.totales.totalEfectivo) + parseInt(venta.Importe);
            this.totales.efectivo = parseInt(this.totales.efectivo) + parseInt(venta.Importe);
          }
          this.totales.totalVenta =  parseInt(this.totales.totalVenta) + parseInt(venta.Importe);
          this.totales.totalComisiones = parseInt(this.totales.totalComisiones) + parseInt(venta.Comisiones);
        }
        
        this.totales.totalEfectivo = this.totales.totalEfectivo.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        this.totales.efectivo = this.totales.efectivo.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        this.totales.otrosDepositos = this.totales.otrosDepositos.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        this.totales.totalContado = this.totales.totalContado.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        this.totales.totalCredito = this.totales.totalCredito.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        this.totales.totalVenta = this.totales.totalVenta.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        
      },
      
      quitarDiaOp:function()
      {
        delete this.filtros.diaO;
        console.log("mis filtros para quitar diaOp",this.filtros)
        this.test(this.filtros);
        this.burbujaDiaOperativo = false;
        
      },
      
//       buscarRuta:function()
//       {
//         if(this.ruta_buscador=="")
//         {
//           this.rutas = [];
//           console.log(this.ruta_buscador);
//         }
//         else
//         {
//           console.log(this.ruta_buscador);
//           this.$api(API_URL,{queryName:"getRutasEmpresa",data:{ruta_buscador:this.ruta_buscador},debug:true},"buscarRutaSuccess");
//         }
//       },
      
//       buscarRutaSuccess(result)
//       {
//         this.rutas = result.rutas.rows;
//         //console.log("Exito !",result);
//       },
      
      buscarRutas:function()
      {
        this.rutas = [];
        
        var data = {
//           ruta_id:this.selected.ruta.ID_Ruta,
          ruta_id:this.buc_ruta_id_ruta,
          ruta_nombre:this.buc_ruta_nombre_ruta,
        };
        
        this.$api(API_URL,{queryName:"getRutasEmpresa",data:data,debug:true},"buscarRutasSuccess");
        
      },
      
      buscarRutasSuccess(result)
      {
        this.rutas = result.rutas.rows;
        console.log("Exito !",result);
      },
      
      
      
      buscarDiaOperativo()
      {
        this.diasOp = [];
        
        var data = {
//           ruta_id:this.selected.ruta.ID_Ruta,
          diaO_id:this.buc_diaOp_Id_diaOp,
          diaO_numero:this.buc_diaOp_Num_diaOp,
          diaO_fecha:this.buc_diaOp_Fecha_diaOp,
          diaO_vendedor:this.buc_diaOp_Vendedor_diaOp
        };
        
        this.$api(API_URL,{queryName:"getDiaOperativo",data:data,debug:true},"buscarDiaOperativoSuccess");
        
      },
      
      buscarDiaOperativoSuccess(result)
      {
        this.diasOp = result.diasOp.rows;
        console.log("Exito !",result);
      },
      
      buscarInventario()
      {
        
        var data = {
          ruta_id:this.selected.ruta.ID_Ruta,
          DiaO:this.selected.diaOp.DiaO
        };
        
        this.$api(API_URL,{queryName:"getInventario",data:data,debug:true},"buscarInventarioSuccess");
      },
      
      buscarInventarioSuccess(result)
      {
        this.inventario = result.inventario.rows;
      },
      
//       Aqui agregue Clientes
       buscarClientes()
      {
        this.clientes = [];
        
        var data = {
          ruta_id:this.selected.ruta.ID_Ruta,
          clte_id:this.buc_cliente_Id_cliente,
          clte_nombre:this.buc_cliente_Nombre_cliente,
          clte_nombreComercial:this.buc_cliente_NombreComercial_cliente
        };
        
        this.$api(API_URL,{queryName:"getClientes",data:data,debug:true},"buscarClientesSuccess");
        
      },
      
      buscarClientesSuccess(result)
      {
        this.clientes = result.clientes.rows;
        console.log("Exito !",result);
      },
      
      
      buscarArticulos()
      {
        this.articulos = [];
        var data = 
        {
          arti_cveArticulo:this.buc_articulo_ClaveArticulo_articulo,
          arti_desArticulo:this.buc_articulo_DesArticulo_articulo,
          arti_codBarras:this.buc_articulo_CodBarras_articulo
        };
//         if(this.selected.cliente=='todos')
//         {
//           data.id_clte='*';
          
//         }
//         else
//         {
//           data.id_clte=this.selected.cliente.Cve_Clte;
//         }
        
        this.$api(API_URL,{queryName:"getArticulos",data:data,debug:true},"buscarArticulosSuccess");
        
      },
      
      buscarArticulosSuccess(result)
      {
        this.articulos = result.articulos.rows;
        console.log("Exito !",result);
      },
      
      buscarSucursal:function()
      {
        
        var data ={
          sucu_FechaIni:this.buc_sucursal_FechaIni_sucursal,
          sucu_FechaIni:this.buc_sucursal_FechaFin_sucursal
        }
        var toDB = {};
        toDB.ruta_id = this.selected.ruta.ID_Ruta;
        this.$api(API_URL,{queryName:"getSucursal",data:toDB,debug:true},"buscarSucursalSuccess");
        
      },
      buscarSucursalSuccess(result)
      {
        var sucursales = result.sucursales.rows;
        var sucResult = {};
        
        var indexSucursalAnterior = 0;
        var sucursalAnterior = "";
        for(var i = 0; i < sucursales.length; i++)
        {
          var sucursal = sucursales[i];
          var partes = sucursal.Fecha.split(' ');
          sucursal.Fecha = partes[0];

          if(sucursalAnterior == "")
          {
            //Soy la primera sucursal
            sucResult[sucursal.nombre] = {
              nombre: sucursal.nombre,
              f_ini_min: sucursal.Fecha,
              fvence_fin_min: sucursal.Fvence,
              f_ini_max: sucursal.Fecha,
              fvence_fin_max: sucursal.Fvence,
            };
            sucursalAnterior = sucursal.nombre;
          }
          else
          {
            //Comprobar si he cambiado de nombre de sucursal
            if(sucursalAnterior != sucursal.nombre)
            {
              if(sucResult[sucursal.nombre] != undefined)
              {
                sucResult[sucursal.nombre].f_ini_max = sucursal.Fecha;
                sucResult[sucursal.nombre].fvence_fin_max = sucursal.Fvence;
                sucursalAnterior = sucursal.nombre;
              }
              else
              {
                sucResult[sucursal.nombre] = {
                  nombre: sucursal.nombre,
                  f_ini_min: sucursal.Fecha,
                  fvence_fin_min: sucursal.Fvence,
                  f_ini_max: sucursal.Fecha,
                  fvence_fin_max: sucursal.Fvence,
                };
                sucursalAnterior = sucursal.nombre;
              }
            }
            else
            {
              if(sucResult[sucursal.nombre] != undefined)
              {
                sucResult[sucursal.nombre].f_ini_max = sucursal.Fecha;
                sucResult[sucursal.nombre].fvence_fin_max = sucursal.Fvence;
                sucursalAnterior = sucursal.nombre;
              }
              else
              {
                sucResult[sucursal.nombre] = {
                  nombre: sucursal.nombre,
                  f_ini_min: sucursal.Fecha,
                  fvence_fin_min: sucursal.Fvence,
                  f_ini_max: sucursal.Fecha,
                  fvence_fin_max: sucursal.Fvence,
                };
                sucursalAnterior = sucursal.nombre;
              }
            }
          }
          indexSucursalAnterior ++;
        }
        
        console.log(sucResult);
        
        this.sucursales = sucResult;
//         console.log("Exito !",result);
      },
      
      setSucursal: function()
      {
        this.selected.sucursal =  this.sucursales[this.indexSucursalSelected];
      },
      
      buscarVentas:function(e)
      {
        e.preventDefault();
        this.ventas = [];
        
        var toDB = {};
        toDB.ruta_id = this.selected.ruta.ID_Ruta,
        toDB.diaO_id = this.selected.diaOp.DiaO,
        toDB.clte_id = this.selected.cliente.Cve_Clte,
        toDB.arti_cveArticulo = this.selected.articulo.cve_articulo,  
        toDB.sucu_id = this.selected.sucursal.IdEmpresa;
        
        this.$api(API_URL,{queryName:"getVentas",data:toDB,debug:true},"buscarVentasSuccess");
        
      },
      
      buscarVentasSuccess(result)
      {
        this.ventas = result.tablaVentas.rows;
        console.log("Exito !",result);
      },
      
      selectDiaO: function(id)
      {
        this.filtros.diaO = id;
        this.test(this.filtros);
        this.$forceUpdate();
//         this.step = 2;
        this.addFiltro = false;
      },
      selectRangoFechas: function()
      {
        this.test(this.filtros);
        this.$forceUpdate();
//         this.step = 2;
      },
      selectArticulo: function(id)
      {
        this.filtros.idProducto = id;
        this.test(this.filtros);
        this.$forceUpdate();
        this.addFiltro = false;
        //this.step = 2;
      },
      selectRuta: function(id)
      {
        this.filtros.idRuta = id;
        this.test(this.filtros);
        this.$forceUpdate();
        this.addFiltro = false;
        //this.step = 2;
      },
      selectComision: function(id)
      {
        this.filtros.comisiones = id;
        this.test(this.filtros);
        this.$forceUpdate();
        this.addFiltro = false;
        //this.step = 2;
      },
      
      selectCliente: function(id)
      {
        this.filtros.idCliente = id;
        this.test(this.filtros);
        this.$forceUpdate();
        this.addFiltro = false;
        //this.step = 2;
      },
      
      
      filtroVentas(e)
      {
        e.preventDefault();
        this.$forceUpdate();
        if(this.buc_venta_Cliente_venta !='')
        {
          
          this.filtros.idCliente = this.buc_venta_Cliente_venta; 
        }
        else
        {
          delete this.filtros.idCliente;
        }
        if(this.buc_venta_Responsable_venta !='')
        {
          this.filtros.nombreReponsable = this.buc_venta_Responsable_venta;
        }
        else
        {
          delete this.filtros.nombreReponsable;
        }
        if(this.buc_venta_NombreComercial_venta !='')
        {
          this.filtros.nombreCliente = this.buc_venta_NombreComercial_venta;
        }
        else
        {
          delete this.filtros.nombreCliente;
        }
        if(this.buc_venta_Folio_venta !='')
        {
          this.filtros.folio = this.buc_venta_Folio_venta;
        }
        else
        {
          delete this.filtros.folio;
        }
        if(this.buc_venta_Tipo_venta !='')
        {
          this.filtros.tipo = this.buc_venta_Tipo_venta;
        }
        else
        {
          delete this.filtros.tipo;
        }
        if(this.buc_venta_MetodoPago_venta !='')
        {
          this.filtros.metodoPago = this.buc_venta_MetodoPago_venta;
   
        }
        else
        {
          delete this.filtros.metodoPago;
        }
//         if(this.buc_venta_RutaId_venta !='')
//         {
//           this.filtros.idRuta = this.buc_venta_RutaId_venta;
   
//         }
//         else
//         {
//           delete this.filtros.idRuta;
//         }
        if(this.buc_venta_Comisiones_venta !='')
        {
          this.filtros.comisiones = this.buc_venta_Comisiones_venta;  
          
        }
        else
        {
          delete this.filtros.comisiones;
        }
        
        console.log("filtros a mandar",this.filtros);
        this.test(this.filtros);
        this.$forceUpdate();
//         var data ={
//           ve_cliente:this.buc_venta_Cliente_venta,
//           ve_responsable:this.buc_venta_Responsable_venta,
//           ve_nombreComercial:this.buc_venta_NombreComercial_venta,
//           ve_folio:this.buc_venta_Folio_venta,
//           ve_tipo:this.buc_venta_Tipo_venta,
//           ve_metodoPago:this.buc_venta_MetodoPago_venta
//         }
        
//         this.$api(API_URL,{queryName:"getVentas",data:data,debug:true},"filtroVentasSuccess");
        
      },
      
      
      filtroVentasSuccess(result)
      {
        this.ventas = result.tablaVentas.rows;
        console.log("Exito !",result);
      },
      
//       cumpleFiltro: function(venta)
//       {
//         var ve = venta;
//         if(this.buc_venta_Cliente_venta != '' || 
//           this.buc_venta_Responsable_venta != '' ||
//           this.buc_venta_NombreComercial_venta != '' ||
//           this.buc_venta_Folio_venta != '' || 
//           this.buc_venta_Tipo_venta != '' || 
//           this.buc_venta_MetodoPago_venta != ''
//         )
//         {
//           if(this.buc_venta_Cliente_venta != '' && ve.Cliente == this.buc_venta_Cliente_venta){return true;}
//           if(this.buc_venta_Responsable_venta != '' && ve.Responsable == this.buc_venta_Responsable_venta){return true;}
//           if(this.buc_venta_NombreComercial_venta != '' && ve.nombreComercial == this.buc_venta_NombreComercial_venta){return true;}
//           if(this.buc_venta_Folio_venta != '' && ve.Folio == this.buc_venta_Folio_venta){return true;}
//           if(this.buc_venta_Tipo_venta != '' && ve.Tipo == this.buc_venta_Tipo_venta){return true;}
//           if(this.buc_venta_MetodoPago_venta != '' && ve.metodoPago == this.buc_venta_MetodoPago_venta){return true;}
//           return false;
//         }
//         else
//         {
//           return true;
//         }
        
         
//       },
      
      
// Aqui termina      
      
      totalPiezas()
      {
        const total = this.inventario.reduce((sum, inv) => {
          return sum + parseFloat(inv.Stock);
        }, 0)
        return total;
      },
      
      
      
      doSelected(key,value)
      {
        console.log(key,value);
        this.selected[key] = value;
        if(key == "ruta")
        {
          this.buscarDiaOperativo();
        }
        if(key == "diaOp")
        {
          //this.buscarInventario();
          this.buscarClientes();
        }
        if(key == "cliente")
        {
          this.buscarArticulos();
        }
        if(key == "articulo")
        {
          this.buscarSucursal();
        }
         if(key == "venta")
        {
          this.buscarVentas();
        }
        
      },
      retrySelection(key)
      {
        this.selected[key] = "";
        if(key == "ruta")
        {
          this.selected["diaOp"] = "";
          this.clientes = [];
          this.buscarDiaOperativo();
        }
        if(key == "cliente")
        {
          this.clientes = [];
          this.buscarClientes();
        }
        if(key == "articulo")
        {
          this.articulos = [];
          this.buscarArticulos();
        }
        if(key == "sucursal")
        {
          this.sucursales = [];
          this.buscarSucursal();
          this.buscarVentas();
          this.filtroVentas();
        }
        
      },
      displayDate(fecha)
      {
        fecha = fecha.split(" ")[0];
        var arrFecha = fecha.split("-");
        return String(arrFecha[2])+"/"+String(arrFecha[1])+"/"+String(arrFecha[0]);
      }
    },
    mounted:function()
    {
      // Do something...
    }
  })
</script>