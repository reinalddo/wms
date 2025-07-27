<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

include '../../config.php';



class Reportes
{
  public $reporteName = "";
  private $result = array("success"=>false);
  private $inData = array();
  private $sqlRecord = array();
  private $dbConnection = null;
  private $s  = null;
  private $sc = null;

  public function process()
  {
    $this->s = $_SESSION;
    
    $inputJSON = file_get_contents('php://input');
    $this->inData = json_decode($inputJSON, TRUE);
    //if (isset($_POST) && !empty($_POST)){$this->inData = $_POST;}
    
    //echo var_dump($this->inData);
    
    $this->connect();
    $this->getComplementarySession();
    if(isset($this->inData["queryName"]))
    {
      $this->{$this->inData["queryName"]}();
      if(isset($this->inData["debug"]))
      {
        if($this->inData["debug"] == true)
        {
          $this->result["debug"] = array();
          $this->result["debug"]["inData"] = $this->inData;
          $this->result["debug"]["session"] = $this->s;
          $this->result["debug"]["sessionComplement"] = $this->sc;
          $this->result["debug"]["sqlRecord"] = $this->sqlRecord;
        }
      }
    }
    else{
      $this->result["clientes"] = $this->{"getClientes"}();
    }
    echo json_encode($this->result);
  }
  
  function getRutasEmpresa()
  {
    $ands = "";
    if($this->inData["data"]["ruta.ID_Ruta"] != "")
    {
      $ands .=" and  t_ruta.ID_Ruta = '".$this->inData["data"]["ruta.ID_Ruta"]."' ";
    }
    if($this->inData["data"]["ruta.cve_ruta"] != "")
    {
      $ands .=" and t_ruta.cve_ruta = '".$this->inData["data"]["ruta.cve_ruta"]."' ";
    }
    
    $rutas = $this->execSQL("
      Select
      t_ruta.ID_Ruta,t_ruta.cve_ruta
      from t_ruta
      left join c_almacenp on c_almacenp.id = t_ruta.cve_almacenp
      where
      c_almacenp.cve_cia = ".$this->s["cve_cia"]."
      
      ".$ands."
    ");
    $this->result["rutas"] = $rutas;
    $this->result["success"] = true;
    
    
  }
  
  function getInventario()
  {
    
    $inventario = $this->execSQL("
      Select 
      t_ruta.ID_Ruta,
      t_ruta.cve_ruta,
      c_articulo.cve_articulo,
      c_articulo.des_articulo,
      c_articulo.num_multiplo,
      c_unimed.id_umed,
      c_unimed.des_umed,
      StockHistorico.Stock,
      StockHistorico.Fecha,
      StockHistorico.DiaO
      from t_ruta
      left join StockHistorico on StockHistorico.RutaID = t_ruta.ID_Ruta
      left join c_articulo on c_articulo.cve_articulo = StockHistorico.Articulo
      left join c_unimed on c_unimed.id_umed = c_articulo.unidadMedida
      where
      t_ruta.ID_Ruta = ".$this->inData["data"]["ruta_id"]."
      and StockHistorico.DiaO = ".$this->inData["data"]["DiaO"]."
      and StockHistorico.Stock > 0
    ");
    $this->result["inventario"] = $inventario;
    $this->result["success"] = true;
  }
  
  
  
  function getDiaOperativo()
  {
    $ands = "";
    if($this->inData["data"]["diaO_id"] != "")
    {
      $ands .=" and DiasO.Id = '".$this->inData["data"]["diaO_id"]."' ";
    }
    if($this->inData["data"]["diaO_numero"] != "")
    {
      $ands .=" and DiasO.DiaO = '".$this->inData["data"]["diaO_numero"]."' ";
    }
    if($this->inData["data"]["diaO_fecha"] != "")
    {
      $ands .=" and DiasO.Fecha = '".$this->inData["data"]["diaO_fecha"]."' ";
    }
    if($this->inData["data"]["diaO_vendedor"] != "")
    {
      $ands .=" and DiasO.Ve = '".$this->inData["data"]["diaO_vendedor"]."' ";
    }
    //DiasO.RutaId= ".$this->inData["data"]["ruta_id"].
    $query = "
      Select
      DiasO.RutaId, 
      DiasO.DiaO,DiasO.Id, 
      DiasO.Fecha, 
      DiasO.Ve
      from DiasO
      left join t_ruta on t_ruta.ID_Ruta = DiasO.RutaId
      where 1
      ".$ands."
      order by id desc
    ";
    $diasOp = $this->execSQL($query);
    $this->result["diasOp"] = $diasOp;
    $this->result["success"] = true;
  }
  
  
  function getDiaOperativoBitacora()
  {
    $ands = "";
    if($this->inData["data"]["diaO_id"] != "")
    {
      $ands .=" and DiasO.Id = '".$this->inData["data"]["diaO_id"]."' ";
    }
    if($this->inData["data"]["diaO_numero"] != "")
    {
      $ands .=" and DiasO.DiaO = '".$this->inData["data"]["diaO_numero"]."' ";
    }
    if($this->inData["data"]["diaO_fecha"] != "")
    {
      $ands .=" and DiasO.Fecha = '".$this->inData["data"]["diaO_fecha"]."' ";
    }
    if($this->inData["data"]["diaO_vendedor"] != "")
    {
      $ands .=" and DiasO.Ve = '".$this->inData["data"]["diaO_vendedor"]."' ";
    }
    //DiasO.RutaId= ".$this->inData["data"]["ruta_id"].
    $query = "
      Select
      DiasO.RutaId, 
      DiasO.DiaO,DiasO.Id, 
      DiasO.Fecha, 
      DiasO.Ve
      from DiasO
      left join t_ruta on t_ruta.ID_Ruta = DiasO.RutaId
      inner join BitacoraTiempos on BitacoraTiempos.DiaO = DiasO.DiaO 
      where 1
      ".$ands."
      order by id desc
    ";
    $diasOp = $this->execSQL($query);
    $this->result["diasOp"] = $diasOp;
    $this->result["success"] = true;
  }
  
  function getClientes()
  {
    $ands = "";
    if($this->inData["data"]["clte_id"] != "")
    {
      $ands .=" and c_cliente.Cve_Clte = '".$this->inData["data"]["clte_id"]."' ";
    }
    if($this->inData["data"]["clte_nombre"] != "")
    {
      $ands .=" and c_cliente.RazonSocial = '".$this->inData["data"]["clte_nombre"]."' ";
    }
    if($this->inData["data"]["clte_nombreComercial"] != "")
    {
      $ands .=" and c_cliente.RazonComercial = '".$this->inData["data"]["clte_nombreComercial"]."' ";
    }
    
    $query = "
      Select
      t_ruta.ID_Ruta,
      c_cliente.Cve_Clte,
      c_cliente.RazonSocial,
      c_cliente.RazonComercial,
      c_cliente.cve_ruta
      from c_cliente
      left join t_ruta on t_ruta.cve_ruta = c_cliente.cve_ruta
      inner join Venta on Venta.CodCliente = c_cliente.Cve_Clte
      where 1
      
      ".$ands."
    ";
    $clientes = $this->execSQL($query);
    $this->result["clientes"] = $clientes;
    $this->result["success"] = true;
    
    //echo var_dump($query);
  }
  
  
  function _getArticulos()
  {
    $ands = "";
    if($this->inData["data"]["arti_id"] != "")
    {
      $ands .=" and c_articulo.id = '".$this->inData["data"]["arti_id"]."' ";
    }
    if($this->inData["data"]["arti_cveArticulo"] != "")
    {
      $ands .=" and c_articulo.cve_articulo = '".$this->inData["data"]["arti_cveArticulo"]."' ";
    }
    if($this->inData["data"]["arti_desArticulo"] != "")
    {
      $ands .=" and c_articulo.des_articulo = '".$this->inData["data"]["arti_desArticulo"]."' ";
    }
    
    $query = "
      Select
      c_articulo.id,
      c_articulo.cve_articulo,
      c_articulo.des_articulo
      from c_articulo
    ";
    $articulos = $this->execSQL($query);
    $this->result["articulos"] = $articulos;
    $this->result["success"] = true;
    
    //echo var_dump($query);
  }
  
  
  function _getArticulos_()
  {
    $ands = "";
    if($this->inData["data"]["arti_cveArticulo"] != "")
    {
      $ands .=" and c_articulo.cve_articulo = '".$this->inData["data"]["arti_cveArticulo"]."' ";
    }
    if($this->inData["data"]["arti_codBarras"] != "")
    {
      $ands .=" and c_articulo.cve_codprov = '".$this->inData["data"]["arti_codBarras"]."' ";
    }
    if($this->inData["data"]["arti_desArticulo"] != "")
    {
      $ands .=" and c_articulo.des_articulo = '".$this->inData["data"]["arti_desArticulo"]."' ";
    }
    
    
    $query = "
      Select
      th_pedido.Fol_folio,
      c_articulo.*
      from th_pedido
      
    ";
   //CAmbiar la query para que solo traiga los articulos que aparecen en nuestra tabla master (actualmente trae todos los articulos)
    $query.=" left join td_pedido on td_pedido.Fol_folio=th_pedido.Fol_folio";
    $query.=" left join c_articulo on c_articulo.cve_articulo=td_pedido.Cve_articulo";
    $query.= " where 1 ";
    
    
    if($this->inData["data"]["id_clte"] != "*")
    {
      //" where th_pedido.Cve_clte =  ".$this->inData["data"]["id_clte"].
      $query.= " ".$ands;
     
    }
    else
    {
      $myAnds = $ands;
      $query.= $myAnds;
    }
    
//     echo var_dump($this->inData["data"]);
    
//     echo var_dump($query);
    
//     die();
    $articulos = $this->execSQL($query);
    
    $this->result["articulos"] = $articulos;
    $this->result["success"] = true;
  }
  
  
  function getArticulos()
  {
    $ands = "";
    if($this->inData["data"]["arti_cveArticulo"] != "")
    {
      $ands .=" and c_articulo.cve_articulo = '".$this->inData["data"]["arti_cveArticulo"]."' ";
    }
    if($this->inData["data"]["arti_codBarras"] != "")
    {
      $ands .=" and c_articulo.cve_codprov = '".$this->inData["data"]["arti_codBarras"]."' ";
    }
    if($this->inData["data"]["arti_desArticulo"] != "")
    {
      $ands .=" and c_articulo.des_articulo = '".$this->inData["data"]["arti_desArticulo"]."' ";
    }
    
    $query= "
      Select
      c_articulo.cve_articulo,
      c_articulo.cve_codprov,
      c_articulo.des_articulo,
      c_articulo.cve_almac
      from c_articulo
      INNER JOIN c_almacenp on c_almacenp.id = c_articulo.cve_almac
      where
      c_almacenp.cve_cia = ".$this->s["cve_cia"]."
      ".$ands."
    ";
    
    
    
//     $query = "
//       Select
//       DetalleVet.Articulo,
//       c_articulo.*
//       from DetalleVet
      
//     ";
//    //CAmbiar la query para que solo traiga los articulos que aparecen en nuestra tabla master (actualmente trae todos los articulos)
//     $query.="LEFT JOIN c_articulo ON c_articulo.cve_articulo = DetalleVet.Articulo";
    
//     $query.= " where 1 ";
    
    
    if($this->inData["data"]["id_clte"] != "*")
    {
      //" where th_pedido.Cve_clte =  ".$this->inData["data"]["id_clte"].
      $query.= " ".$ands;
     
    }
    else
    {
      $myAnds = $ands;
      $query.= $myAnds;
    }
    
//     echo var_dump($this->inData["data"]);
    
//     echo var_dump($query);
    
//     die();
    $articulos = $this->execSQL($query);
    
    $this->result["articulos"] = $articulos;
    $this->result["success"] = true;
  }
  
  
  
  
  function getSucursal()
  {
    $ands = "";
    if($this->inData["data"]["sucu_FechaIni"] != "")
    {
      $ands .=" and Venta.Fecha = '".$this->inData["data"]["sucu_FechaIni"]."' ";
    }
    if($this->inData["data"]["sucu_FechaFin"] != "")
    {
      $ands .=" and Venta.Fvence = '".$this->inData["data"]["sucu_FechaFin"]."' ";
    }
    
    
    $query = "
      Select
      t_ruta.ID_Ruta,
      c_almacenp.nombre,
      Venta.Fecha,
      Venta.Fvence,
      Venta.IdEmpresa
      from Venta
      left join t_ruta on t_ruta.ID_Ruta = Venta.RutaId
      left join c_almacenp on c_almacenp.clave = Venta.IdEmpresa
      where
      Venta.RutaId = ".$this->inData["data"]["ruta_id"]
      .$ands.
      " ORDER BY Venta.Fecha ASC;
    ";
    $sucursales = $this->execSQL($query);
    $this->result["sucursales"] = $sucursales;
    $this->result["success"] = true;
    
    //echo var_dump($query);
  }
  
  function getVentas()
  {
    $ands = "";
    if($this->inData["data"]["sucu_FechaIni"] != "")
    {
      $ands .=" and Venta.Fecha = '".$this->inData["data"]["sucu_FechaIni"]."' ";
    }
    if($this->inData["data"]["sucu_FechaFin"] != "")
    {
      $ands .=" and Venta.Fvence = '".$this->inData["data"]["sucu_FechaFin"]."' ";
    }
    if($this->inData["data"]["ruta_id"] != "")
    {
      $ands .=" and t_ruta.ID_Ruta = '".$this->inData["data"]["ruta_id"]."' ";
    }
    if($this->inData["data"]["diaO_id"] != "")
    {
      $ands .=" and DiasO.DiaO = '".$this->inData["data"]["diaO_id"]."' ";
    }
    if($this->inData["data"]["clte_id"] != "")
    {
      $ands .=" and c_cliente.Cve_Clte = '".$this->inData["data"]["clte_id"]."' ";
    }
    if($this->inData["data"]["arti_cveArticulo"] != "")
    {
      $ands .=" and c_articulo.cve_articulo = '".$this->inData["data"]["arti_cveArticulo"]."' ";
    }
    if($this->inData["data"]["sucu_id"] != "")
    {
      $ands .=" and Venta.IdEmpresa = '".$this->inData["data"]["sucu_id"]."' ";
    }
//     Buscador de Ventas
    if($this->inData["data"]["ve_cliente"] != "")
    {
      $ands .=" and Venta.CodCliente = '".$this->inData["data"]["ve_cliente"]."' ";
    }
    if($this->inData["data"]["ve_responsable"] != "")
    {
      $ands .=" and t_vendedores.Nombre = '".$this->inData["data"]["ve_responsable"]."' ";
    }
    if($this->inData["data"]["ve_nombreComercial"] != "")
    {
      $ands .=" and c_cliente.RazonComercial = '".$this->inData["data"]["ve_nombreComercial"]."' ";
    }
    if($this->inData["data"]["ve_folio"] != "")
    {
      $ands .=" and Cobranza.FolioInterno = '".$this->inData["data"]["ve_folio"]."' ";
    }
    if($this->inData["data"]["ve_tipo"] != "")
    {
      $ands .=" and Venta.TipoVta = '".$this->inData["data"]["ve_tipo"]."' ";
    }
    if($this->inData["data"]["ve_metodoPago"] != "")
    {
      $ands .=" and FormasPag.Forma = '".$this->inData["data"]["ve_metodoPago"]."' ";
    }
    
    
    
    $query = "
      SELECT 
      c_almacenp.nombre as sucursalNombre,
      Venta.IdEmpresa as Sucursal,
      Venta.Fecha as Fecha,
      Venta.RutaId as Ruta,
      Venta.CodCliente as Cliente,
      t_vendedores.Nombre as Responsable,
      c_cliente.RazonComercial as nombreComercial,
      Cobranza.FolioInterno as Folio,
      Venta.TipoVta as Tipo,
      FormasPag.Forma as metodoPago,
      Venta.Subtotal as Importe,
      Venta.IVA as IVA,
      DetalleVet.DescMon as Descuento,
      Venta.TOTAL as Total,
      DetalleVet.Comisiones as Comisiones,
      DetalleVet.Utilidad as Utilidad,
      c_articulo.num_multiplo as Cajas,
      DetalleVet.Pza as Piezas,
      Venta.Cancelada as Cancelada,
      Venta.VendedorId as Vendedor,
      Venta.ID_Ayudante1 as Ayudante1,
      Venta.ID_Ayudante2 as Ayudante2,
      DetalleVet.DescPorc as Promociones
      FROM Venta
      left join DetalleVet ON DetalleVet.Docto = Venta.Documento
      LEFT JOIN t_ruta ON t_ruta.ID_Ruta = Venta.RutaId
      LEFT JOIN c_cliente ON c_cliente.id_cliente = Venta.CodCliente
      LEFT JOIN FormasPag ON  FormasPag.IdFpag = Venta.FormaPag 
      LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = Venta.VendedorId
      LEFT JOIN c_articulo ON c_articulo.cve_articulo = DetalleVet.Articulo
      INNER JOIN c_almacenp ON c_almacenp.clave = Venta.IdEmpresa
      LEFT JOIN Cobranza on Cobranza.Documento = Venta.Documento
      LEFT JOIN DiasO on DiasO.RutaId = Venta.RutaId
      Where 1
        
    ";
    $query.= $ands;
    $tablaVentas = $this->execSQL($query);
    $this->result["tablaVentas"] = $tablaVentas;
    $this->result["success"] = true;
  }
  
  function reporteVentas()
  {
    /*
    $this->inData["data"]["diaO"] = "";
    $this->inData["data"]["fecha"] = "";
    $this->inData["data"]["fechaIni"] = "2020-05-20";
    $this->inData["data"]["fechaFin"] = "2020-05-21";
    $this->inData["data"]["entregas"] = "";
    
    $this->inData["data"]["idRuta"] = 0;
    $this->inData["data"]["idCliente"] = 0;
    $this->inData["data"]["idProducto"] = 0;
    $this->inData["data"]["nombreCliente"] = "";
    $this->inData["data"]["nombreReponsable"] = "";
    $this->inData["data"]["idSucursal"]  = 0;
    $this->inData["data"]["metodoPago"]  = 0;
    $this->inData["data"]["folio"]  = 0;
    $this->inData["data"]["tipo"]  = 0;
    */
    
    
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
        // cobranza
        cDocumento:"",         // Cobranza.Documento
        tipoDocumento                // Venta.DocSalida
        fechaReg               //Cobranza.FechaReg
        fechaVen               //Cobranza.FechaVence
        vendedor               //t_vendedores.Nombre
        
      }
      
      Bitacora de tiempos:
      codigo:"",			  // A18253 (BitacoraTiempos.Codigo)
      descripcion:"",		  // OPERACION DEL DIA (BitacoraTiempos.Descripcion)
      horaIni:"",			  // 2020-05-20 17:26:00 (BitacoraTiempos.HI)
      horaFin:"";			  // 2020-05-20 17:26:00 (BitacoraTiempos.HF)
      tiempoTraslado		  // 00:00:00 (BitacoraTiempos.HT)
      tiempoServicio		  // 00:00:00 (BitacoraTiempos.TS)
      visita:"",			  // 0 or 1 (BitacoraTiempos.Visita)								  
      programado:"",		  // 0 or 1 (BitacoraTiempos.Programado)							
      cerrado:"",			  // 0 or 1 (BitacoraTiempos.Cerrado)						
      tip;"",				  // T (BitacoraTiempos.Tip)					
      latitud:"",		  	  // 19.4707 (.BitacoraTiempo.latituds)							
      longitud:"",		  // -99.0569 (BitacoraTiempos.longitude)							
      pila:"",			  // 100 (BitacoraTiempos.pila)						
      diaO: "",             // 2020-05-21 (DiasO.DiaO) (BitacoraTiempos.DiaO) (Continuidad.DiaO)
      idRuta: 0,            // ID (Venta.RutaId) (BitacoraTiempos.DiaO) (Continuidad.DiaO)
      vendedorID			  // (t_vendedores.Id_Vendedor) (BitacoraTiempos.DiaO)
      vendedor              //t_vendedores.Nombre
      diaO: "",             // 2020-05-21 (DiasO.DiaO) (BitacoraTiempos.DiaO) (Continuidad.DiaO)
      idRuta: 0,            // ID (Venta.RutaId) (BitacoraTiempos.DiaO) (Continuidad.DiaO)
      vendedorID			  // (t_vendedores.Id_Vendedor) (BitacoraTiempos.DiaO)
      vendedor              //t_vendedores.Nombre
    */
    
    
    $this->inData["data"] = $this->inData["data"]["params"];
    
    
    
    $ands = "";
    //Un dia operativo en concreto
    if(isset($this->inData["data"]["diaO"]) && $this->inData["data"]["diaO"] != "")
    {
      $ands .=" AND DiasO.DiaO = '".$this->inData["data"]["diaO"]."' ";
    }
    //Fecha en contreto
    if(isset($this->inData["data"]["fecha"]) && $this->inData["data"]["fecha"] != "")
    {
      ///DiasO.Fecha
      $ands .=" AND Venta.Fecha = '".$this->inData["data"]["fecha"]." 00:00:00"."' ";
    }
    //Rango de fechas
    if(isset($this->inData["data"]["fechaIni"]) && $this->inData["data"]["fechaIni"] != "" && 
       isset($this->inData["data"]["fechaFin"]) && $this->inData["data"]["fechaFin"] != "")
    {
      //Especifica si es para fecha de entrega o fecha de venta
      if(isset($this->inData["data"]["entregas"]) && $this->inData["data"]["entregas"] != 0)
      {
        //Entregas DiasO.Fvence
        $ands .=" AND Venta.Fvence >= '".$this->inData["data"]["fechaIni"]."' AND Venta.Fvence <= '".$this->inData["data"]["fechaFin"]."' ";
      }
      else if(!isset($this->inData["data"]["entregas"]))
      {
        //VEntas ///DiasO.Fecha
        $ands .=" AND Venta.Fecha >= '".$this->inData["data"]["fechaIni"]." 00:00:00"."' AND Venta.Fecha <= '".$this->inData["data"]["fechaFin"]." 00:00:00"."' ";
      }
    }
    
    //Ruta
    if(isset($this->inData["data"]["idRuta"]) && $this->inData["data"]["idRuta"] != 0)
    {
      //Especifica una ruta en concreto
      $ands .=" AND Venta.RutaId = '".$this->inData["data"]["idRuta"]."' ";
    }
    
    //ID Cliente
    if(isset($this->inData["data"]["idCliente"]) && $this->inData["data"]["idCliente"] != 0)
    {
      //Especifica un cliente en concreto
      $ands .=" AND Venta.CodCliente = '".$this->inData["data"]["idCliente"]."' ";
    }
    
    //Producto
    if(isset($this->inData["data"]["idProducto"]) && $this->inData["data"]["idProducto"] != 0)
    {
      //Especifica un producto en concreto
      $ands .=" AND DetalleVet.Articulo = '".$this->inData["data"]["idProducto"]."' ";
    }
    
    //Nombre cliente
    if(isset($this->inData["data"]["nombreCliente"]) && $this->inData["data"]["nombreCliente"] != "")
    {
      //Especifica un cliente en cualquier caracter
      $ands .=" AND c_cliente.RazonSocial LIKE '%".$this->inData["data"]["nombreCliente"]."%' ";
    }
    
    //Nombre Responsable (Vendedor)
    if(isset($this->inData["data"]["nombreReponsable"]) && $this->inData["data"]["nombreReponsable"] != "")
    {
      //Especifica un vendedor en cualquier caracter
      $ands .=" AND c_cliente.RazonComercial LIKE '%".$this->inData["data"]["nombreReponsable"]."%' ";
    }
    
    //ID Sucursal (idSucursal)
    if(isset($this->inData["data"]["idSucursal"]) && $this->inData["data"]["idSucursal"] != 0)
    {
      //Especifica un idSucursal en concreto
      $ands .=" AND Venta.IdEmpresa = '".$this->inData["data"]["idSucursal"]."' ";
    }
    
    //Metodo de pago (metodoPago)
    if(isset($this->inData["data"]["metodoPago"]) && $this->inData["data"]["metodoPago"] != "")
    {
      //Especifica un metodo de pago en concreto String
      $ands .=" AND FormasPag.Forma = '".$this->inData["data"]["metodoPago"]."' ";
    }
    
    //Folio (folio)
    if(isset($this->inData["data"]["folio"]) && $this->inData["data"]["folio"] != 0)
    {
      //Especifica un folio de cobranza en concreto (Existe la posibilidad de que sea NULL porque aun no se ejcuta la cobranza)
      $ands .=" AND Cobranza.FolioInterno = '".$this->inData["data"]["folio"]."' ";
    }
    
    //Tipo (tipo)
    if(isset($this->inData["data"]["tipo"]) && $this->inData["data"]["tipo"] != "")
    {
      //Especifica un tipo de venta en su detalle en concreto
      $ands .=" AND Venta.TipoVta = '".$this->inData["data"]["tipo"]."' ";
    }
    $campoAdicional = '';
    if(isset($this->inData["data"]["comisiones"]) && $this->inData["data"]["comisiones"] == true)
    {
      $campoAdicional = "DetalleVet.Comisiones as Comisiones,";
    }
    if(isset($this->inData["data"]["comisiones"]) && $this->inData["data"]["comisiones"] == 'con')
    {
      //Especifica un tipo de venta en su detalle en concreto
      $ands .=" AND DetalleVet.Comisiones > 0 ";
    }
    if(isset($this->inData["data"]["comisiones"]) && $this->inData["data"]["comisiones"] == 'sin')
    {
      //Especifica un tipo de venta en su detalle en concreto
      $ands .=" AND DetalleVet.Comisiones = 0 ";
    }
    //Vendedor
    if(isset($this->inData["data"]["vendedor"]) && $this->inData["data"]["vendedor"] != "")
    {
      //Especifica un vendedor en cualquier caracter
      $ands .=" AND t_vendedores.Nombre LIKE '%".$this->inData["data"]["vendedor"]."%' ";
    }
    //Documento
    if(isset($this->inData["data"]["cDocumento"]) && $this->inData["data"]["cDocumento"] != "")
    {
      //Especifica un documento en cualquier caracter
      $ands .=" AND Cobranza.Documento LIKE '%".$this->inData["data"]["cDocumento"]."%' ";
    }
    //TipoDoc
    if(isset($this->inData["data"]["tipoDocumento"]) && $this->inData["data"]["tipoDocumento"] != "")
    {
      //Especifica un documento en cualquier caracter
      $ands .=" AND Venta.DocSalida LIKE '%".$this->inData["data"]["tipoDocumento"]."%' ";
    }
    //FechaRegistro
    if(isset($this->inData["data"]["fechasRV"]) && $this->inData["data"]["fechasRV"] != 'fechaR')
    {
      //Especifica fecha registro en selector
      $ands .=" AND Cobranza.FechaReg LIKE '%".$this->inData["data"]["fechasRV"]."%' ";
    }
    //FechaRegistro
    if(isset($this->inData["data"]["fechasRV"]) && $this->inData["data"]["fechasRV"] != 'fechaV')
    {
      //Especifica fecha registro en selector
      $ands .=" AND Cobranza.FechaVence LIKE '%".$this->inData["data"]["fechasRV"]."%' ";
    }
    //Cancelada
    if(isset($this->inData["data"]["cancelada"]) && $this->inData["data"]["cancelada"] != 0)
    {
      //Especifica si es activa o cancelada
      $ands .=" AND Venta.Cancelada = '".$this->inData["data"]["cancelada"]."' ";
    }
    
    $query = "
      SELECT 
      ".$campoAdicional."
      c_almacenp.nombre as sucursalNombre,
      Venta.Id as idVenta,
      Venta.IdEmpresa as Sucursal,
      Venta.Fecha as Fecha,
      Venta.RutaId as Ruta,
      t_ruta.cve_ruta as rutaName,
      Venta.CodCliente as Cliente,
      c_cliente.RazonComercial as Responsable,
      c_destinatarios.razonsocial as nombreComercial,
      Cobranza.FolioInterno as Folio,
      Venta.TipoVta as Tipo,
      FormasPag.Forma as metodoPago,
      Venta.Subtotal as Importe,
      Venta.IVA as IVA,
      DetalleVet.DescMon as Descuento,
      Venta.TOTAL as Total,
      DetalleVet.Comisiones as Comisiones,
      DetalleVet.Utilidad as Utilidad,
      c_articulo.num_multiplo as Cajas,
      DetalleVet.Pza as Piezas,
      Venta.Cancelada as Cancelada,
      Venta.VendedorId as vendedorID,
      t_vendedores.Nombre as Vendedor,
      Venta.ID_Ayudante1 as Ayudante1,
      Venta.ID_Ayudante2 as Ayudante2,
      DetalleVet.DescPorc as Promociones,
      DiasO.DiaO as DiaOperativo,
      DetalleVet.Descripcion as Articulo,
      Cobranza.Documento as Documento,
      Cobranza.Saldo as saldoInicial,
      DetalleCob.Abono as Abono,
      Venta.Saldo as saldoActual,
      Cobranza.FechaReg as fechaRegistro,
      Cobranza.FechaVence as fechaVence,
      Venta.DocSalida as tipoDoc,


      Noventas.MotivoId as idMotivo,
      MotivosNoVenta.Motivo as Motivo


      FROM Venta
      left join DetalleVet ON DetalleVet.Docto = Venta.Documento
      LEFT JOIN t_ruta ON t_ruta.ID_Ruta = Venta.RutaId  
      LEFT JOIN FormasPag ON FormasPag.IdFpag = Venta.FormaPag 
      LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = Venta.VendedorId
      LEFT JOIN c_articulo ON c_articulo.cve_articulo = DetalleVet.Articulo
      INNER JOIN c_almacenp ON c_almacenp.clave = Venta.IdEmpresa
      LEFT JOIN Cobranza on Cobranza.Documento = Venta.Documento
      INNER JOIN DiasO on DiasO.DiaO = Venta.DiaO
      LEFT JOIN DetalleCob on DetalleCob.IdCobranza = Cobranza.id
      LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO
      LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
      LEFT JOIN c_destinatarios on c_destinatarios.Cve_Clte=Venta.CodCliente
      LEFT JOIN c_cliente on c_cliente.Cve_Clte=c_destinatarios.Cve_Clte



      Where 1
        
    ";
    $query.= $ands;
    $reporteVentas = $this->execSQL($query);
    $this->result["ventas"] = $reporteVentas;
    $this->result["success"] = true;
    
//     echo var_dump($query);
//     die();
  }
  
  function reporteBitacoraTiempo()
  {
    /*
    $this->inData["data"]["diaO"] = "";
    $this->inData["data"]["fecha"] = "";
    $this->inData["data"]["fechaIni"] = "2020-05-20";
    $this->inData["data"]["fechaFin"] = "2020-05-21";
    $this->inData["data"]["entregas"] = "";
    
    $this->inData["data"]["idRuta"] = 0;
    $this->inData["data"]["idCliente"] = 0;
    $this->inData["data"]["idProducto"] = 0;
    $this->inData["data"]["nombreCliente"] = "";
    $this->inData["data"]["nombreReponsable"] = "";
    $this->inData["data"]["idSucursal"]  = 0;
    $this->inData["data"]["metodoPago"]  = 0;
    $this->inData["data"]["folio"]  = 0;
    $this->inData["data"]["tipo"]  = 0;
    */
    
    
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
        // cobranza
        cDocumento:"",         // Cobranza.Documento
        tipoDocumento                // Venta.DocSalida
        fechaReg               //Cobranza.FechaReg
        fechaVen               //Cobranza.FechaVence
        vendedor               //t_vendedores.Nombre
        
      }
      
      Bitacora de tiempos:
      codigo:"",			  // A18253 (BitacoraTiempos.Codigo)
      descripcion:"",		  // OPERACION DEL DIA (BitacoraTiempos.Descripcion)
      horaIni:"",			  // 2020-05-20 17:26:00 (BitacoraTiempos.HI)
      horaFin:"";			  // 2020-05-20 17:26:00 (BitacoraTiempos.HF)
      tiempoTraslado		  // 00:00:00 (BitacoraTiempos.HT)
      tiempoServicio		  // 00:00:00 (BitacoraTiempos.TS)
      visita:"",			  // 0 or 1 (BitacoraTiempos.Visita)								  
      programado:"",		  // 0 or 1 (BitacoraTiempos.Programado)							
      cerrado:"",			  // 0 or 1 (BitacoraTiempos.Cerrado)						
      tip;"",				  // T (BitacoraTiempos.Tip)					
      latitud:"",		  	  // 19.4707 (.BitacoraTiempo.latituds)							
      longitud:"",		  // -99.0569 (BitacoraTiempos.longitude)							
      pila:"",			  // 100 (BitacoraTiempos.pila)						
      diaO: "",             // 2020-05-21 (DiasO.DiaO) (BitacoraTiempos.DiaO) (Continuidad.DiaO)
      idRuta: 0,            // ID (Venta.RutaId) (BitacoraTiempos.DiaO) (Continuidad.DiaO)
      vendedorID			  // (t_vendedores.Id_Vendedor) (BitacoraTiempos.DiaO)
      vendedor              //t_vendedores.Nombre
      diaO: "",             // 2020-05-21 (DiasO.DiaO) (BitacoraTiempos.DiaO) (Continuidad.DiaO)
      idRuta: 0,            // ID (Venta.RutaId) (BitacoraTiempos.DiaO) (Continuidad.DiaO)
      vendedorID			  // (t_vendedores.Id_Vendedor) (BitacoraTiempos.DiaO)
      vendedor              //t_vendedores.Nombre
    */
    
    
    $this->inData["data"] = $this->inData["data"]["params"];
    
    
    
    $ands = "";
    //Un dia operativo en concreto
    if(isset($this->inData["data"]["diaO"]) && $this->inData["data"]["diaO"] != "")
    {
      $ands .=" AND DiasO.DiaO = '".$this->inData["data"]["diaO"]."' ";
    }
    //Fecha en contreto
    if(isset($this->inData["data"]["fecha"]) && $this->inData["data"]["fecha"] != "")
    {
      ///DiasO.Fecha
      $ands .=" AND Venta.Fecha = '".$this->inData["data"]["fecha"]." 00:00:00"."' ";
    }
    //Rango de fechas
    if(isset($this->inData["data"]["fechaIni"]) && $this->inData["data"]["fechaIni"] != "" && 
       isset($this->inData["data"]["fechaFin"]) && $this->inData["data"]["fechaFin"] != "")
    {
      //Especifica si es para fecha de entrega o fecha de venta
//       if(isset($this->inData["data"]["entregas"]) && $this->inData["data"]["entregas"] != 0)
//       {
        //Entregas DiasO.Fvence
        $ands .=" AND BitacoraTiempos.HI >= '".$this->inData["data"]["fechaIni"]."' AND BitacoraTiempos.HF <= '".$this->inData["data"]["fechaFin"]."' ";
//       }
//       else if(!isset($this->inData["data"]["entregas"]))
//       {
        //VEntas ///DiasO.Fecha
       // $ands .=" AND BitacoraTiempos.HI >= '".$this->inData["data"]["fechaIni"]." 00:00:00"."' AND Venta.Fecha <= '".$this->inData["data"]["fechaFin"]." 00:00:00"."' ";
      //}
    }
    
    //Ruta
    if(isset($this->inData["data"]["idRuta"]) && $this->inData["data"]["idRuta"] != 0)
    {
      //Especifica una ruta en concreto
      $ands .=" AND Venta.RutaId = '".$this->inData["data"]["idRuta"]."' ";
    }
    
    //ID Cliente
    if(isset($this->inData["data"]["idCliente"]) && $this->inData["data"]["idCliente"] != 0)
    {
      //Especifica un cliente en concreto
      $ands .=" AND Venta.CodCliente = '".$this->inData["data"]["idCliente"]."' ";
    }
    
    //Producto
    if(isset($this->inData["data"]["idProducto"]) && $this->inData["data"]["idProducto"] != 0)
    {
      //Especifica un producto en concreto
      $ands .=" AND DetalleVet.Articulo = '".$this->inData["data"]["idProducto"]."' ";
    }
    
    //Nombre cliente
    if(isset($this->inData["data"]["nombreCliente"]) && $this->inData["data"]["nombreCliente"] != "")
    {
      //Especifica un cliente en cualquier caracter
      $ands .=" AND c_cliente.RazonSocial LIKE '%".$this->inData["data"]["nombreCliente"]."%' ";
    }
    
    //Nombre Responsable (Vendedor)
    if(isset($this->inData["data"]["nombreReponsable"]) && $this->inData["data"]["nombreReponsable"] != "")
    {
      //Especifica un vendedor en cualquier caracter
      $ands .=" AND c_cliente.RazonComercial LIKE '%".$this->inData["data"]["nombreReponsable"]."%' ";
    }
    
    //ID Sucursal (idSucursal)
    if(isset($this->inData["data"]["idSucursal"]) && $this->inData["data"]["idSucursal"] != 0)
    {
      //Especifica un idSucursal en concreto
      $ands .=" AND Venta.IdEmpresa = '".$this->inData["data"]["idSucursal"]."' ";
    }
    
    //Metodo de pago (metodoPago)
    if(isset($this->inData["data"]["metodoPago"]) && $this->inData["data"]["metodoPago"] != "")
    {
      //Especifica un metodo de pago en concreto String
      $ands .=" AND FormasPag.Forma = '".$this->inData["data"]["metodoPago"]."' ";
    }
    
    //Folio (folio)
    if(isset($this->inData["data"]["folio"]) && $this->inData["data"]["folio"] != 0)
    {
      //Especifica un folio de cobranza en concreto (Existe la posibilidad de que sea NULL porque aun no se ejcuta la cobranza)
      $ands .=" AND Cobranza.FolioInterno = '".$this->inData["data"]["folio"]."' ";
    }
    
    //Tipo (tipo)
    if(isset($this->inData["data"]["tipo"]) && $this->inData["data"]["tipo"] != "")
    {
      //Especifica un tipo de venta en su detalle en concreto
      $ands .=" AND Venta.TipoVta = '".$this->inData["data"]["tipo"]."' ";
    }
    $campoAdicional = '';
    if(isset($this->inData["data"]["comisiones"]) && $this->inData["data"]["comisiones"] == true)
    {
      $campoAdicional = "DetalleVet.Comisiones as Comisiones,";
    }
    if(isset($this->inData["data"]["comisiones"]) && $this->inData["data"]["comisiones"] == 'con')
    {
      //Especifica un tipo de venta en su detalle en concreto
      $ands .=" AND DetalleVet.Comisiones > 0 ";
    }
    if(isset($this->inData["data"]["comisiones"]) && $this->inData["data"]["comisiones"] == 'sin')
    {
      //Especifica un tipo de venta en su detalle en concreto
      $ands .=" AND DetalleVet.Comisiones = 0 ";
    }
    //Vendedor
    if(isset($this->inData["data"]["vendedor"]) && $this->inData["data"]["vendedor"] != "")
    {
      //Especifica un vendedor en cualquier caracter
      $ands .=" AND t_vendedores.Nombre LIKE '%".$this->inData["data"]["vendedor"]."%' ";
    }
    //Documento
    if(isset($this->inData["data"]["cDocumento"]) && $this->inData["data"]["cDocumento"] != "")
    {
      //Especifica un documento en cualquier caracter
      $ands .=" AND Cobranza.Documento LIKE '%".$this->inData["data"]["cDocumento"]."%' ";
    }
    //TipoDoc
    if(isset($this->inData["data"]["tipoDocumento"]) && $this->inData["data"]["tipoDocumento"] != "")
    {
      //Especifica un documento en cualquier caracter
      $ands .=" AND Venta.DocSalida LIKE '%".$this->inData["data"]["tipoDocumento"]."%' ";
    }
    //FechaRegistro
    if(isset($this->inData["data"]["fechasRV"]) && $this->inData["data"]["fechasRV"] != 'fechaR')
    {
      //Especifica fecha registro en selector
      $ands .=" AND Cobranza.FechaReg LIKE '%".$this->inData["data"]["fechasRV"]."%' ";
    }
    //FechaRegistro
    if(isset($this->inData["data"]["fechasRV"]) && $this->inData["data"]["fechasRV"] != 'fechaV')
    {
      //Especifica fecha registro en selector
      $ands .=" AND Cobranza.FechaVence LIKE '%".$this->inData["data"]["fechasRV"]."%' ";
    }
    
    
    $query = " 
      SELECT
	    BitacoraTiempos.Codigo as codigo,
      BitacoraTiempos.DiaO as diaOpB,
      BitacoraTiempos.Descripcion as descripcion,
      c_cliente.RazonComercial as Responsable,
      c_destinatarios.razonsocial as nombreComercial,
      DiasO.Fecha as fechaDO,
      BitacoraTiempos.HI as horaIni,
      BitacoraTiempos.HF as horaFin,
      BitacoraTiempos.HT as tiempoTraslado,
      BitacoraTiempos.TS as tiempoServicio,
      BitacoraTiempos.Visita as visita,
      BitacoraTiempos.Programado as programado,
      t_ruta.cve_ruta as rutaName,
      BitacoraTiempos.Cerrado as cerrado,
      BitacoraTiempos.IdVendedor as vendedorId,
      t_vendedores.Nombre as Vendedor,
      BitacoraTiempos.Tip as tip,
      BitacoraTiempos.latitude as latitud,
      BitacoraTiempos.longitude as longitud,
      BitacoraTiempos.pila as pila
      
      FROM BitacoraTiempos
      
      LEFT JOIN t_ruta ON t_ruta.ID_Ruta = BitacoraTiempos.RutaId
      LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = BitacoraTiempos.IdVendedor
      INNER JOIN DiasO on DiasO.DiaO = BitacoraTiempos.DiaO
      LEFT JOIN c_cliente on c_cliente.id_cliente = BitacoraTiempos.Codigo
      LEFT JOIN c_destinatarios on c_destinatarios.Cve_Clte = c_cliente.Cve_Clte
      
      
      WHERE 1
      
      
        
    ";
    $query.= $ands;
    $reporteBitacoraTiempo = $this->execSQL($query);
    $this->result["ventas"] = $reporteBitacoraTiempo;
    $this->result["success"] = true;
    //checar que rango de fecha se tomara(diaO o fecha ini y fin de Bitacora de tiempos)
//     echo var_dump($query);
//     die();
  }
  
   function reporteCobranza()
  {
    /*
    $this->inData["data"]["diaO"] = "";
    $this->inData["data"]["fecha"] = "";
    $this->inData["data"]["fechaIni"] = "2020-05-20";
    $this->inData["data"]["fechaFin"] = "2020-05-21";
    $this->inData["data"]["entregas"] = "";
    
    $this->inData["data"]["idRuta"] = 0;
    $this->inData["data"]["idCliente"] = 0;
    $this->inData["data"]["idProducto"] = 0;
    $this->inData["data"]["nombreCliente"] = "";
    $this->inData["data"]["nombreReponsable"] = "";
    $this->inData["data"]["idSucursal"]  = 0;
    $this->inData["data"]["metodoPago"]  = 0;
    $this->inData["data"]["folio"]  = 0;
    $this->inData["data"]["tipo"]  = 0;
    */
    
    
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
        // cobranza
        cDocumento:"",         // Cobranza.Documento
        tipoDocumento                // Venta.DocSalida
        fechaReg               //Cobranza.FechaReg
        fechaVen               //Cobranza.FechaVence
        vendedor               //t_vendedores.Nombre
        
      }
      
      Bitacora de tiempos:
      codigo:"",			  // A18253 (BitacoraTiempos.Codigo)
      descripcion:"",		  // OPERACION DEL DIA (BitacoraTiempos.Descripcion)
      horaIni:"",			  // 2020-05-20 17:26:00 (BitacoraTiempos.HI)
      horaFin:"";			  // 2020-05-20 17:26:00 (BitacoraTiempos.HF)
      tiempoTraslado		  // 00:00:00 (BitacoraTiempos.HT)
      tiempoServicio		  // 00:00:00 (BitacoraTiempos.TS)
      visita:"",			  // 0 or 1 (BitacoraTiempos.Visita)								  
      programado:"",		  // 0 or 1 (BitacoraTiempos.Programado)							
      cerrado:"",			  // 0 or 1 (BitacoraTiempos.Cerrado)						
      tip;"",				  // T (BitacoraTiempos.Tip)					
      latitud:"",		  	  // 19.4707 (.BitacoraTiempo.latituds)							
      longitud:"",		  // -99.0569 (BitacoraTiempos.longitude)							
      pila:"",			  // 100 (BitacoraTiempos.pila)						
      diaO: "",             // 2020-05-21 (DiasO.DiaO) (BitacoraTiempos.DiaO) (Continuidad.DiaO)
      idRuta: 0,            // ID (Venta.RutaId) (BitacoraTiempos.DiaO) (Continuidad.DiaO)
      vendedorID			  // (t_vendedores.Id_Vendedor) (BitacoraTiempos.DiaO)
      vendedor              //t_vendedores.Nombre
      diaO: "",             // 2020-05-21 (DiasO.DiaO) (BitacoraTiempos.DiaO) (Continuidad.DiaO)
      idRuta: 0,            // ID (Venta.RutaId) (BitacoraTiempos.DiaO) (Continuidad.DiaO)
      vendedorID			  // (t_vendedores.Id_Vendedor) (BitacoraTiempos.DiaO)
      vendedor              //t_vendedores.Nombre
    */
    
    
    $this->inData["data"] = $this->inData["data"]["params"];
    
    
    
    $ands = "";
    //Un dia operativo en concreto
    if(isset($this->inData["data"]["diaO"]) && $this->inData["data"]["diaO"] != "")
    {
      $ands .=" AND DiasO.DiaO = '".$this->inData["data"]["diaO"]."' ";
    }
    //Fecha en contreto
    if(isset($this->inData["data"]["fecha"]) && $this->inData["data"]["fecha"] != "")
    {
      ///DiasO.Fecha
      $ands .=" AND Venta.Fecha = '".$this->inData["data"]["fecha"]." 00:00:00"."' ";
    }
    //Rango de fechas
    if(isset($this->inData["data"]["fechaIni"]) && $this->inData["data"]["fechaIni"] != "" && 
       isset($this->inData["data"]["fechaFin"]) && $this->inData["data"]["fechaFin"] != "")
    {
      //Especifica si es para fecha de entrega o fecha de venta
//       if(isset($this->inData["data"]["entregas"]) && $this->inData["data"]["entregas"] != 0)
//       {
        //Entregas DiasO.Fvence
        $ands .=" AND Cobranza.FechaReg >= '".$this->inData["data"]["fechaIni"]."' AND Cobranza.FechaVence <= '".$this->inData["data"]["fechaFin"]."' ";
//       }
//       else if(!isset($this->inData["data"]["entregas"]))
//       {
        //VEntas ///DiasO.Fecha
       // $ands .=" AND BitacoraTiempos.HI >= '".$this->inData["data"]["fechaIni"]." 00:00:00"."' AND Venta.Fecha <= '".$this->inData["data"]["fechaFin"]." 00:00:00"."' ";
      //}
    }
    
    //Ruta
    if(isset($this->inData["data"]["idRuta"]) && $this->inData["data"]["idRuta"] != 0)
    {
      //Especifica una ruta en concreto
      $ands .=" AND Venta.RutaId = '".$this->inData["data"]["idRuta"]."' ";
    }
    
    //ID Cliente
    if(isset($this->inData["data"]["idCliente"]) && $this->inData["data"]["idCliente"] != 0)
    {
      //Especifica un cliente en concreto
      $ands .=" AND Venta.CodCliente = '".$this->inData["data"]["idCliente"]."' ";
    }
    
    //Producto
    if(isset($this->inData["data"]["idProducto"]) && $this->inData["data"]["idProducto"] != 0)
    {
      //Especifica un producto en concreto
      $ands .=" AND DetalleVet.Articulo = '".$this->inData["data"]["idProducto"]."' ";
    }
    
    //Nombre cliente
    if(isset($this->inData["data"]["nombreCliente"]) && $this->inData["data"]["nombreCliente"] != "")
    {
      //Especifica un cliente en cualquier caracter
      $ands .=" AND c_cliente.RazonSocial LIKE '%".$this->inData["data"]["nombreCliente"]."%' ";
    }
    
    //Nombre Responsable (Vendedor)
    if(isset($this->inData["data"]["nombreReponsable"]) && $this->inData["data"]["nombreReponsable"] != "")
    {
      //Especifica un vendedor en cualquier caracter
      $ands .=" AND c_cliente.RazonComercial LIKE '%".$this->inData["data"]["nombreReponsable"]."%' ";
    }
    
    //ID Sucursal (idSucursal)
    if(isset($this->inData["data"]["idSucursal"]) && $this->inData["data"]["idSucursal"] != 0)
    {
      //Especifica un idSucursal en concreto
      $ands .=" AND Venta.IdEmpresa = '".$this->inData["data"]["idSucursal"]."' ";
    }
    
    //Metodo de pago (metodoPago)
    if(isset($this->inData["data"]["metodoPago"]) && $this->inData["data"]["metodoPago"] != "")
    {
      //Especifica un metodo de pago en concreto String
      $ands .=" AND FormasPag.Forma = '".$this->inData["data"]["metodoPago"]."' ";
    }
    
    //Folio (folio)
    if(isset($this->inData["data"]["folio"]) && $this->inData["data"]["folio"] != 0)
    {
      //Especifica un folio de cobranza en concreto (Existe la posibilidad de que sea NULL porque aun no se ejcuta la cobranza)
      $ands .=" AND Cobranza.FolioInterno = '".$this->inData["data"]["folio"]."' ";
    }
    
    //Tipo (tipo)
    if(isset($this->inData["data"]["tipo"]) && $this->inData["data"]["tipo"] != "")
    {
      //Especifica un tipo de venta en su detalle en concreto
      $ands .=" AND Venta.TipoVta = '".$this->inData["data"]["tipo"]."' ";
    }
    $campoAdicional = '';
    if(isset($this->inData["data"]["comisiones"]) && $this->inData["data"]["comisiones"] == true)
    {
      $campoAdicional = "DetalleVet.Comisiones as Comisiones,";
    }
    if(isset($this->inData["data"]["comisiones"]) && $this->inData["data"]["comisiones"] == 'con')
    {
      //Especifica un tipo de venta en su detalle en concreto
      $ands .=" AND DetalleVet.Comisiones > 0 ";
    }
    if(isset($this->inData["data"]["comisiones"]) && $this->inData["data"]["comisiones"] == 'sin')
    {
      //Especifica un tipo de venta en su detalle en concreto
      $ands .=" AND DetalleVet.Comisiones = 0 ";
    }
    //Vendedor
    if(isset($this->inData["data"]["vendedor"]) && $this->inData["data"]["vendedor"] != "")
    {
      //Especifica un vendedor en cualquier caracter
      $ands .=" AND t_vendedores.Nombre LIKE '%".$this->inData["data"]["vendedor"]."%' ";
    }
    //Documento
    if(isset($this->inData["data"]["cDocumento"]) && $this->inData["data"]["cDocumento"] != "")
    {
      //Especifica un documento en cualquier caracter
      $ands .=" AND Cobranza.Documento LIKE '%".$this->inData["data"]["cDocumento"]."%' ";
    }
    //TipoDoc
    if(isset($this->inData["data"]["tipoDocumento"]) && $this->inData["data"]["tipoDocumento"] != "")
    {
      //Especifica un documento en cualquier caracter
      $ands .=" AND Venta.DocSalida LIKE '%".$this->inData["data"]["tipoDocumento"]."%' ";
    }
    //FechaRegistro
    if(isset($this->inData["data"]["fechasRV"]) && $this->inData["data"]["fechasRV"] != 'fechaR')
    {
      //Especifica fecha registro en selector
      $ands .=" AND Cobranza.FechaReg LIKE '%".$this->inData["data"]["fechasRV"]."%' ";
    }
    //FechaRegistro
    if(isset($this->inData["data"]["fechasRV"]) && $this->inData["data"]["fechasRV"] != 'fechaV')
    {
      //Especifica fecha registro en selector
      $ands .=" AND Cobranza.FechaVence LIKE '%".$this->inData["data"]["fechasRV"]."%' ";
    }
    //Cancelada
    if(isset($this->inData["data"]["cancelada"]) && $this->inData["data"]["cancelada"] != 0)
    {
      //Especifica si es activa o cancelada
      $ands .=" AND Venta.Cancelada = '".$this->inData["data"]["cancelada"]."' ";
    }
    
    $query = "
      SELECT 
      
      Cobranza.RutaId as Ruta,
      t_ruta.cve_ruta as rutaName,
      Venta.VendedorId as vendedorID,
      t_vendedores.Nombre as Vendedor,
      Cobranza.Cliente as Cliente,
      c_cliente.RazonComercial as Responsable,
      c_destinatarios.razonsocial as nombreComercial,
      Cobranza.Documento as Documento,
      Cobranza.Saldo as saldoInicial,
      DetalleCob.Abono as Abono,
      Venta.Saldo as saldoActual,
      Cobranza.FechaReg as fechaRegistro,
      Cobranza.FechaVence as fechaVence,
      Cobranza.TipoDoc as tipoDoc

      FROM Cobranza
      LEFT JOIN t_ruta ON t_ruta.ID_Ruta = Cobranza.RutaId  
      LEFT JOIN Venta ON Venta.Documento = Cobranza.Documento
      LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = Venta.VendedorId
      INNER JOIN DiasO on DiasO.DiaO = Cobranza.DiaO
      LEFT JOIN DetalleCob on DetalleCob.IdCobranza = Cobranza.id
      LEFT JOIN c_destinatarios on c_destinatarios.id_destinatario= Cobranza.Cliente
      LEFT JOIN c_cliente on c_cliente.Cve_Clte= c_destinatarios.Cve_Clte



      Where 1
      
        
    ";
    $query.= $ands;
    $reporteCobranza = $this->execSQL($query);
    $this->result["ventas"] = $reporteCobranza;
    $this->result["success"] = true;
    //checar que rango de fecha se tomara(diaO o fecha ini y fin de Bitacora de tiempos)
//     echo var_dump($query);
//     die();
  }
  
  function _reporteVentas()
  {
    /*
    $this->inData["data"]["diaO"] = "";
    $this->inData["data"]["fecha"] = "";
    $this->inData["data"]["fechaIni"] = "2020-05-20";
    $this->inData["data"]["fechaFin"] = "2020-05-21";
    $this->inData["data"]["entregas"] = "";
    
    $this->inData["data"]["idRuta"] = 0;
    $this->inData["data"]["idCliente"] = 0;
    $this->inData["data"]["idProducto"] = 0;
    $this->inData["data"]["nombreCliente"] = "";
    $this->inData["data"]["nombreReponsable"] = "";
    $this->inData["data"]["idSucursal"]  = 0;
    $this->inData["data"]["metodoPago"]  = 0;
    $this->inData["data"]["folio"]  = 0;
    $this->inData["data"]["tipo"]  = 0;
    */
    
    
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
      }
    */
    
    
    $this->inData["data"] = $this->inData["data"]["params"];
    
    
    
    $ands = "";
    //Un dia operativo en concreto
    if(isset($this->inData["data"]["diaO"]) && $this->inData["data"]["diaO"] != "")
    {
      $ands .=" AND DiasO.DiaO = '".$this->inData["data"]["diaO"]."' ";
    }
    //Fecha en contreto
    if(isset($this->inData["data"]["fecha"]) && $this->inData["data"]["fecha"] != "")
    {
      ///DiasO.Fecha
      $ands .=" AND Venta.Fecha = '".$this->inData["data"]["fecha"]." 00:00:00"."' ";
    }
    //Rango de fechas
    if(isset($this->inData["data"]["fechaIni"]) && $this->inData["data"]["fechaIni"] != "" && 
       isset($this->inData["data"]["fechaFin"]) && $this->inData["data"]["fechaFin"] != "")
    {
      //Especifica si es para fecha de entrega o fecha de venta
      if(isset($this->inData["data"]["entregas"]) && $this->inData["data"]["entregas"] != 0)
      {
        //Entregas DiasO.Fvence
        $ands .=" AND Venta.Fvence >= '".$this->inData["data"]["fechaIni"]."' AND Venta.Fvence <= '".$this->inData["data"]["fechaFin"]."' ";
      }
      else if(!isset($this->inData["data"]["entregas"]))
      {
        //VEntas ///DiasO.Fecha
        $ands .=" AND Venta.Fecha >= '".$this->inData["data"]["fechaIni"]." 00:00:00"."' AND Venta.Fecha <= '".$this->inData["data"]["fechaFin"]." 00:00:00"."' ";
      }
    }
    
    //Ruta
    if(isset($this->inData["data"]["idRuta"]) && $this->inData["data"]["idRuta"] != 0)
    {
      //Especifica una ruta en concreto
      $ands .=" AND Venta.RutaId = '".$this->inData["data"]["idRuta"]."' ";
    }
    
    //ID Cliente
    if(isset($this->inData["data"]["idCliente"]) && $this->inData["data"]["idCliente"] != 0)
    {
      //Especifica un cliente en concreto
      $ands .=" AND Venta.CodCliente = '".$this->inData["data"]["idCliente"]."' ";
    }
    
    //Producto
    if(isset($this->inData["data"]["idProducto"]) && $this->inData["data"]["idProducto"] != 0)
    {
      //Especifica un producto en concreto
      $ands .=" AND DetalleVet.Articulo = '".$this->inData["data"]["idProducto"]."' ";
    }
    
    //Nombre cliente
    if(isset($this->inData["data"]["nombreCliente"]) && $this->inData["data"]["nombreCliente"] != "")
    {
      //Especifica un cliente en cualquier caracter
      $ands .=" AND c_cliente.RazonSocial LIKE '%".$this->inData["data"]["nombreCliente"]."%' ";
    }
    
    //Nombre Responsable (Vendedor)
    if(isset($this->inData["data"]["nombreReponsable"]) && $this->inData["data"]["nombreReponsable"] != "")
    {
      //Especifica un vendedor en cualquier caracter
      $ands .=" AND t_vendedores.Nombre LIKE '%".$this->inData["data"]["nombreReponsable"]."%' ";
    }
    
    //ID Sucursal (idSucursal)
    if(isset($this->inData["data"]["idSucursal"]) && $this->inData["data"]["idSucursal"] != 0)
    {
      //Especifica un idSucursal en concreto
      $ands .=" AND Venta.IdEmpresa = '".$this->inData["data"]["idSucursal"]."' ";
    }
    
    //Metodo de pago (metodoPago)
    if(isset($this->inData["data"]["metodoPago"]) && $this->inData["data"]["metodoPago"] != "")
    {
      //Especifica un metodo de pago en concreto String
      $ands .=" AND FormasPag.Forma = '".$this->inData["data"]["metodoPago"]."' ";
    }
    
    //Folio (folio)
    if(isset($this->inData["data"]["folio"]) && $this->inData["data"]["folio"] != 0)
    {
      //Especifica un folio de cobranza en concreto (Existe la posibilidad de que sea NULL porque aun no se ejcuta la cobranza)
      $ands .=" AND Cobranza.FolioInterno = '".$this->inData["data"]["folio"]."' ";
    }
    
    //Tipo (tipo)
    if(isset($this->inData["data"]["tipo"]) && $this->inData["data"]["tipo"] != "")
    {
      //Especifica un tipo de venta en su detalle en concreto
      $ands .=" AND Venta.TipoVta = '".$this->inData["data"]["tipo"]."' ";
    }
    $campoAdicional = '';
    if(isset($this->inData["data"]["comisiones"]) && $this->inData["data"]["comisiones"] == true)
    {
      $campoAdicional = "DetalleVet.Comisiones as Comisiones,";
    }
    if(isset($this->inData["data"]["comisiones"]) && $this->inData["data"]["comisiones"] == 'con')
    {
      //Especifica un tipo de venta en su detalle en concreto
      $ands .=" AND DetalleVet.Comisiones > 0 ";
    }
    if(isset($this->inData["data"]["comisiones"]) && $this->inData["data"]["comisiones"] == 'sin')
    {
      //Especifica un tipo de venta en su detalle en concreto
      $ands .=" AND DetalleVet.Comisiones = 0 ";
    }
    
    $query = "
      SELECT 
      ".$campoAdicional."
      c_almacenp.nombre as sucursalNombre,
      Venta.IdEmpresa as Sucursal,
      Venta.Fecha as Fecha,
      Venta.RutaId as Ruta,
      t_ruta.cve_ruta as rutaName,
      Venta.CodCliente as Cliente,
      c_cliente.RazonComercial as Responsable,
      c_cliente.RazonSocial as nombreComercial,
      Cobranza.FolioInterno as Folio,
      Venta.TipoVta as Tipo,
      FormasPag.Forma as metodoPago,
      Venta.Subtotal as Importe,
      Venta.IVA as IVA,
      DetalleVet.DescMon as Descuento,
      Venta.TOTAL as Total,
      DetalleVet.Comisiones as Comisiones,
      DetalleVet.Utilidad as Utilidad,
      c_articulo.num_multiplo as Cajas,
      DetalleVet.Pza as Piezas,
      Venta.Cancelada as Cancelada,
      Venta.VendedorId as vendedorID,
      t_vendedores.Nombre as Vendedor,
      Venta.ID_Ayudante1 as Ayudante1,
      Venta.ID_Ayudante2 as Ayudante2,
      DetalleVet.DescPorc as Promociones,
      DiasO.DiaO as DiaOperativo,
      DetalleVet.Descripcion as Articulo,
      Cobranza.Documento as Documento,
      Cobranza.Saldo as saldoInicial,
      DetalleCob.Abono as Abono,
      Venta.Saldo as saldoActual,
      Cobranza.FechaReg as fechaRegistro,
      Cobranza.FechaVence as fechaVence,
      Venta.DocSalida as tipoDoc
      FROM Venta
      left join DetalleVet ON DetalleVet.Docto = Venta.Documento
      LEFT JOIN t_ruta ON t_ruta.ID_Ruta = Venta.RutaId   
      LEFT JOIN c_cliente ON c_cliente.Cve_Clte = Venta.CodCliente
      LEFT JOIN FormasPag ON  FormasPag.IdFpag = Venta.FormaPag 
      LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = Venta.VendedorId
      LEFT JOIN c_articulo ON c_articulo.cve_articulo = DetalleVet.Articulo
      INNER JOIN c_almacenp ON c_almacenp.clave = Venta.IdEmpresa
      LEFT JOIN Cobranza on Cobranza.Documento = Venta.Documento
      LEFT JOIN DiasO on DiasO.DiaO = Venta.DiaO
      LEFT JOIN DetalleCob on DetalleCob.IdCobranza = Cobranza.id
      Where 1
        
    ";
    $query.= $ands;
    $reporteVentas = $this->execSQL($query);
    $this->result["ventas"] = $reporteVentas;
    $this->result["success"] = true;
    
//     echo var_dump($query);
//     die();
  }
  
  function connect()
  {
    $this->dbConnection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $this->dbConnection->set_charset("utf8");
  }
  
  private function getComplementarySession()
  {
    $sql = "Select * from c_compania where cve_cia = ".$this->s["cve_cia"];
    $compania = $this->execSQL($sql);
    if($compania["num_rows"] > 0)
    {
      $this->sc["c_compania"] = $compania["rows"][0];
    }
  }
  
  private function execSQL($sql)
  {
    $this->sqlRecord[] = $sql;
    $res = [
      "num_rows"=>0,
      "rows"=>[],
      "error"=>0
    ];
    if (!($resSql = mysqli_query($this->dbConnection, $sql))) {
      $res["error"] = 1;
    }
    else
    {
      $i = 0;
      while ($row = mysqli_fetch_array($resSql)) {
          $res["rows"][] = $row;
          $i++;
      }
      $res["num_rows"] = $i;
    }
    return $res;
  }
  
  private function demo()
  {
    $this->result["data"] = array("demo"=>"demo");
  }
  
}

$reporte = new Reportes;
echo $reporte->process();


?>