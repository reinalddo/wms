<?php
include '../../../config.php';

error_reporting(0);

$page = $_POST['page']; // get the requested page
$limit = $_POST['rows']; // get how many rows we want to have into the grid
$start = $limit*$page - $limit; // do not put $limit*($page - 1)

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

$sqlCount = "select count(*) from(
                Select	
                V.fol_folio as `Factura/Entrega`,
                P.Fec_Pedido as `Fecha`,
                P.Fec_Entrega as `Fecha Entrega`,
                  (Select 
                  Count(Fol_Folio) 
                  From th_cajamixta 
                  Where fol_folio=V.Fol_folio) as `Num Bultos`,
                C.RazonSocial as `Cliente`
                from	V_PedidosSinGuia V 
                Join th_pedido P On V.fol_folio=P.fol_folio And V.cve_almac=P.cve_almac
                Join c_cliente C On P.cve_clte=C.Cve_Clte And P.Cve_CteProv=C.Cve_CteProv
             )x";
if (!($res = mysqli_query($conn, $sqlCount)))
  echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
$row = mysqli_fetch_array($res);
$count = $row['cuenta'];

$sql = "Select	
        V.fol_folio as `Factura/Entrega`,
        P.Fec_Pedido as `Fecha`,
        P.Fec_Entrega as `Fecha Entrega`,
          (Select 
          Count(Fol_Folio) 
          From th_cajamixta 
          Where fol_folio=V.Fol_folio) as `Num Bultos`,
        C.RazonSocial as `Cliente`
        from	V_PedidosSinGuia V 
        Join th_pedido P On V.fol_folio=P.fol_folio And V.cve_almac=P.cve_almac
        Join c_cliente C On P.cve_clte=C.Cve_Clte And P.Cve_CteProv=C.Cve_CteProv";
if (!($res = mysqli_query($conn, $sql)))
  echo "Falló la preparación: (" . mysqli_error($conn) . ") ";

$total_pages = ( $count >0 )?ceil($count/$limit):$total_pages = 0;
$page = ($page > $total_pages)?$total_pages:$page;  

$responce->page = $page;
$responce->total = $total_pages;
$responce->records = $count;

$arr = array();
$i = 0;
while ($row = mysqli_fetch_array($res)) {
  $row=array_map('utf8_encode',$row);
      $arr[] = $row;
      $responce->rows[$i]['id']=$row['fol-folio'];
      $responce->rows[$i]['cell']=array($row['Factura/Entrega'], $row['Fecha'],$row['Fecha Entrega'], $row['Num Bultos'],$row['Cliente']);
  $i++;
}
echo json_encode($responce);


if(isset($_GET) && !empty($_GET) && $_GET['action'] === 'obtenerClaveDestinatario'){
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql = "SELECT COALESCE(MAX(id_destinatario), 0) + 1 AS clave FROM c_destinatarios;";
    $query = mysqli_query($conn, $sql);
    $clave = '';
    if($query->num_rows > 0){
        $clave = mysqli_fetch_assoc($query)['clave'];
    }
    mysqli_close($conn);
    echo json_encode(array(
        "clave"  => $clave
    ));
}