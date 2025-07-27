<?php
include '../../../config.php';
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

error_reporting(0);

if (isset($_POST) && !empty($_POST)) 
{
  if($_POST['action'] == "loadDetails")
  {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $id = $_POST['id'];
    $almacen = $_POST['almacen'];

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $start = $limit*$page - $limit; // do not put $limit*($page - 1)
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql3  ="update c_ubicacion set orden_secuencia = null";
    mysqli_query($conn, $sql3);

    if(!$sidx) $sidx = 1;

    $sqlCount = 
//           "SELECT COUNT(td.idr) AS cuenta FROM td_ruta_surtido td WHERE td.idr = $id and Activo = '1'";
    "
      SELECT * from(SELECT 
            ub.idy_ubica, 
            alp.nombre as almacen,
            al.des_almac as zona, 
            if(ub.TECNOLOGIA='PTL','PTL', if(ub.picking='S','Picking','')) as picking,
            if(tdr.orden_secuencia = NULL,ub.orden_secuencia,tdr.orden_secuencia) as orden_secuencia,
            ub.CodigoCSD as bl      
            FROM c_almacen al
          inner join c_almacenp alp on alp.id = al.cve_almacenp
          inner join c_ubicacion ub on ub.cve_almac = al.cve_almac
            left join td_ruta_surtido tdr on tdr.idy_ubica = ub.idy_ubica
            where al.Activo = 1 
            and tdr.idr = $id
            and ub.Activo = 1   
            and alp.clave= '$almacen'
            and ub.picking = 'S'
            order by tdr.orden_secuencia asc)as x
      UNION 
      SELECT 
          ub.idy_ubica, 
          alp.nombre as almacen, 
          al.des_almac as zona, 
          if(ub.TECNOLOGIA='PTL','PTL', if(ub.picking='S','Picking','')) as picking,
          ub.orden_secuencia,
          ub.CodigoCSD as bl 
          FROM c_almacen al
          inner join c_almacenp alp on alp.id = al.cve_almacenp
          inner join c_ubicacion ub on ub.cve_almac = al.cve_almac
          and alp.clave= '$almacen'
          and ub.picking = 'S'
          and al.Activo = 1 
          and ub.Activo = 1 
          and ub.idy_ubica NOT in (select idy_ubica from td_ruta_surtido where idr = $id)
    ";

    if((!$res = mysqli_query($conn, $sqlCount))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $lilo = $res->num_rows;
    $count = $lilo;

    mysqli_close();
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//         $row = mysqli_fetch_array($res);
//         $count = $row['cuenta'];
    $_page = 0;

    if(intval($page)>0) $_page = ($page-1)*$limit;//

    $sql = "SELECT nombre FROM `th_ruta_surtido` where idr = ".$id;
    if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $i=0;
    while ($row = mysqli_fetch_array($res)) 
    {
      $responce->nombre = $row[0];
      $i++;
    }

    $sql ="
      SELECT * from(SELECT 
            ub.idy_ubica, 
            alp.nombre as almacen,
            al.des_almac as zona, 
            if(ub.TECNOLOGIA='PTL','PTL', if(ub.picking='S','Picking','')) as picking,
            if(tdr.orden_secuencia = NULL,ub.orden_secuencia,tdr.orden_secuencia) as orden_secuencia,
            ub.CodigoCSD as bl      
            FROM c_almacen al
          inner join c_almacenp alp on alp.id = al.cve_almacenp
          inner join c_ubicacion ub on ub.cve_almac = al.cve_almac
            left join td_ruta_surtido tdr on tdr.idy_ubica = ub.idy_ubica
            where al.Activo = 1 
            and tdr.idr = $id
            and ub.Activo = 1   
            and alp.clave= '$almacen'
            and ub.picking = 'S'
            order by tdr.orden_secuencia asc)as x
      UNION 
      SELECT 
          ub.idy_ubica, 
          alp.nombre as almacen, 
          al.des_almac as zona, 
          if(ub.TECNOLOGIA='PTL','PTL', if(ub.picking='S','Picking','')) as picking,
          ub.orden_secuencia,
          ub.CodigoCSD as bl 
          FROM c_almacen al
          inner join c_almacenp alp on alp.id = al.cve_almacenp
          inner join c_ubicacion ub on ub.cve_almac = al.cve_almac
          and alp.clave= $almacen
          and ub.picking = 'S'
          and al.Activo = 1 
          and ub.Activo = 1 
          and ub.idy_ubica NOT in (select idy_ubica from td_ruta_surtido where idr = $id)
      
    ";
//LIMIT $_page, $limit;
    
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    if($count >0) 
    {
      $total_pages = ceil($count/$limit);
      //$total_pages = ceil($count/1);
    }
    else
    {
      $total_pages = 0;
    }
    if ($page > $total_pages) 
    {
      $page=$total_pages;
    }
    if( $count >0 ) 
    {
      $total_pages = ceil($count/$limit);
      //$total_pages = ceil($count/1);
    } 
    else 
    {
      $total_pages = 0;
    } 
    if ($page > $total_pages)
    {
      $page=$total_pages;
    }

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $responce->sql = $sql;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) 
    {
      $row = array_map("utf8_encode", $row);
      $arr[] = $row;
      extract($row);
      $responce->rows[$i]['id']= $idy_ubica;
      $responce->rows[$i]['cell']=array($idy_ubica,$almacen,$zona,$picking,$orden_secuencia,$bl);
      $i++;
    }
    echo var_dump($responce);
    die();
    echo json_encode($responce);
  }
  else if($_POST['action'] == "loadDetails_pro")
  {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $id = $_POST['id'];
    $almacen = $_POST['almacen'];

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//         $row = mysqli_fetch_array($res);
//         $count = $row['cuenta'];
    $sql ="
      SELECT * from(SELECT 
            ub.idy_ubica, 
            alp.nombre as almacen,
            al.des_almac as zona, 
            if(ub.TECNOLOGIA='PTL','PTL', if(ub.picking='S','Picking','')) as picking,
            if(tdr.orden_secuencia = NULL,ub.orden_secuencia,tdr.orden_secuencia) as orden_secuencia,
            ub.CodigoCSD as bl      
            FROM c_almacen al
          inner join c_almacenp alp on alp.id = al.cve_almacenp
          inner join c_ubicacion ub on ub.cve_almac = al.cve_almac
            left join td_ruta_surtido tdr on tdr.idy_ubica = ub.idy_ubica
            where al.Activo = 1 
            and tdr.idr = $id
            AND tdr.Activo = 1 
            and ub.Activo = 1   
            #and alp.clave= '$almacen'
            and ub.picking = 'S'
            order by tdr.orden_secuencia asc)as x
      #UNION 
      #SELECT 
      #    ub.idy_ubica, 
      #    concat(alp.nombre,'-') as almacen, 
      #    al.des_almac as zona, 
      #    if(ub.TECNOLOGIA='PTL','PTL', if(ub.picking='S','Picking','')) as picking,
      #    ub.orden_secuencia,
      #    ub.CodigoCSD as bl 
      #    FROM c_almacen al
      #    inner join c_almacenp alp on alp.id = al.cve_almacenp
      #    inner join c_ubicacion ub on ub.cve_almac = al.cve_almac
      #    and alp.clave= '$almacen'
      #    and ub.picking = 'S'
      #    and al.Activo = 1 
      #    and ub.Activo = 1 
      #    and ub.idy_ubica NOT in (select idy_ubica from td_ruta_surtido)
      ORDER BY orden_secuencia
      
    ";
//LIMIT $_page, $limit;
    
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    $i=0;
    $responce["ubicaciones"]=array();
    while ($row = mysqli_fetch_array($res)) 
    {
      $row = array_map("utf8_encode", $row);
      $responce["ubicaciones"][$i]['id']     = $row["idy_ubica"];
      $responce["ubicaciones"][$i]['datos'] = array($row["idy_ubica"],$row["almacen"],$row["zona"],$row["picking"],$row["orden_secuencia"],$row["bl"]);
      $i++;
    }
    
    echo json_encode($responce);
  }
  
    else if($_POST['action'] == "loadDetails_pro2")
  {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $id = $_POST['id'];
    $almacen = $_POST['almacen'];

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//         $row = mysqli_fetch_array($res);
//         $count = $row['cuenta'];
    $sql ="
      SELECT * from(SELECT 
            ub.idy_ubica, 
            alp.nombre as almacen,
            al.des_almac as zona, 
            if(ub.TECNOLOGIA='PTL','PTL', if(ub.picking='S','Picking','')) as picking,
            if(tdr.orden_secuencia = NULL,ub.orden_secuencia,tdr.orden_secuencia) as orden_secuencia,
            ub.CodigoCSD as bl      
            FROM c_almacen al
          inner join c_almacenp alp on alp.id = al.cve_almacenp
          inner join c_ubicacion ub on ub.cve_almac = al.cve_almac
            left join td_ruta_surtido tdr on tdr.idy_ubica = ub.idy_ubica
            where al.Activo = 1 
            and tdr.idr = $id
            and ub.Activo = 1   
            and alp.clave= '$almacen'
            and ub.picking = 'S'
            order by tdr.orden_secuencia asc)as x
      UNION 
      SELECT 
          ub.idy_ubica, 
          concat(alp.nombre,'-') as almacen, 
          al.des_almac as zona, 
          if(ub.TECNOLOGIA='PTL','PTL', if(ub.picking='S','Picking','')) as picking,
          ub.orden_secuencia,
          ub.CodigoCSD as bl 
          FROM c_almacen al
          inner join c_almacenp alp on alp.id = al.cve_almacenp
          inner join c_ubicacion ub on ub.cve_almac = al.cve_almac
          where ub.orden_secuencia is not null
          and alp.clave= '$almacen'
          and ub.picking = 'S'
          and al.Activo = 1 
          and ub.Activo = 1 
          and ub.idy_ubica NOT in (select idy_ubica from td_ruta_surtido)
      ORDER BY orden_secuencia
    ";
//LIMIT $_page, $limit;
    
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    $i=0;
    $responce["ubicaciones"]=array();
    while ($row = mysqli_fetch_array($res)) 
    {
      $row = array_map("utf8_encode", $row);
      $responce["ubicaciones"][$i]['id']     = $row["idy_ubica"];
      $responce["ubicaciones"][$i]['datos'] = array($row["idy_ubica"],$row["almacen"],$row["zona"],$row["picking"],$row["orden_secuencia"],$row["bl"]);
      $i++;
    }
    
    echo json_encode($responce);
  }
  
  else if($_POST['action'] == "loadDetails2")
  {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'utf8') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $id=$_POST['id'];
    $responce["success"] = true;
    $sql = "SELECT nombre FROM `th_ruta_surtido` where idr = ".$id;
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $i=0;
    while ($row = mysqli_fetch_array($res)) 
    {
      $responce[$i]["nombre"]=$row[0];
      $i++;
    }

    $sql = "SELECT id_usuario FROM `rel_usuario_ruta` LEFT join c_usuario on c_usuario.id_user = rel_usuario_ruta.id_usuario where id_ruta = ".$id;
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $i=0;
    while ($row = mysqli_fetch_array($res)) 
    {
      //echo var_dump($row);
      $responce["nombre_surtidor"]=$row[0];
      $i++;
    }

    $sql="
      SELECT
        id_user,
        nombre_completo  
      FROM `c_usuario` u
      inner join t_permisos_perfil pp on pp.ID_PERFIL = u.perfil
      #INNER JOIN t_perfilesusuarios pu ON pu.ID_PERFIL = u.perfil AND pu.PER_NOMBRE = 'Surtidor'
      inner join s_permisos_modulo pm on pm.ID_PERMISO = pp.ID_PERMISO
      INNER JOIN trel_us_alm on trel_us_alm.cve_usuario = u.cve_usuario 
      where pm.ID_PERMISO = 2
      and trel_us_alm.cve_almac = '".$_POST["almacen"]."' 
      AND u.id_user NOT IN (SELECT id_usuario FROM rel_usuario_ruta WHERE id_ruta IN (SELECT idr FROM th_ruta_surtido WHERE cve_almac = (SELECT id FROM c_almacenp WHERE clave = '".$_POST["almacen"]."') AND Activo = 1))
      GROUP BY u.nombre_completo
    ";
    if(!$res = mysqli_query($conn, $sql))
    {
      echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $i=0;
    $responce["user"]=array();
    while ($row = mysqli_fetch_array($res)) 
    {
      $row = array_map("utf8_encode", $row);
      $responce["user"][$i]['id']=$row[0];
      $responce["user"][$i]['nombre']=$row[1];
      $i++;
    }

    $sql="
      SELECT
        id_user,
        nombre_completo  
      FROM `c_usuario` u
      where u.id_user in (select id_usuario from rel_usuario_ruta WHERE rel_usuario_ruta.id_ruta = ".$id.")
      GROUP BY u.nombre_completo
    ";
    if(!$res = mysqli_query($conn, $sql)){echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $i=0;
    $responce["user_asign"]=array();
    while ($row = mysqli_fetch_array($res)) 
    {
      $row = array_map("utf8_encode", $row);
      $responce["user_asign"][$i]['id']=$row[0];
      $responce["user_asign"][$i]['nombre']=$row[1];
      $i++;
    }
    //$responce["query"]=$sql;
    echo json_encode($responce);
  }
  else if($_POST['action'] == "guardarRuta2")
  {
    $responce->success=true;
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql=
    "
      SELECT nombre_completo 
      from rel_usuario_ruta 
      LEFT JOIN th_ruta_surtido th ON th.idr = rel_usuario_ruta.id_ruta
      LEFT JOIN c_almacenp al ON al.id = th.cve_almac
      inner join c_usuario on c_usuario.id_user = rel_usuario_ruta.id_usuario  
      where id_usuario in ('".join("','",$_POST["id"])."') AND al.clave = '".$_POST['almacen']."' 
      AND id_ruta != ".$_POST["id_ruta"];
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}
    $responce="";
    while ($row = mysqli_fetch_array($res)) 
    {
      $responce.=$row["nombre_completo"].", ";
      $i++;
    }
    if($responce == "")
    {
      $sql1="UPDATE th_ruta_surtido set nombre = '".$_POST["nombre"]."' where idr = ".$_POST["id_ruta"];
      mysqli_query($conn, $sql1);

      $sql3="DELETE from rel_usuario_ruta where id_ruta = ".$_POST["id_ruta"];
      mysqli_query($conn, $sql3);

      if($_POST["id"])
      {
        foreach ($_POST["id"] as $key => $val) 
        {
          $sql="INSERT into rel_usuario_ruta (id_usuario,id_ruta) values(".$val.",".$_POST["id_ruta"].")";
          if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(3): (" . mysqli_error($conn) . ")";}
        }
      }
      $arr = array("success"=>true,"text"=>"");
      //$sql3="DELETE from td_ruta_surtido where idr = ".$_POST["id_ruta"];
      //mysqli_query($conn, $sql3);
    }
    else
    {
      $arr = array("success"=>false,"text"=>"Este usuario ya cuenta con una ruta asignada, 
                                              para liberar al usuario ir al Administrador de Rutas de Surtido.
                                              (".$responce.")");
    }
    echo json_encode($arr);
  }
  else if($_POST['action'] == "eliminarRS")
  {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $id_ruta =$_POST["id_ruta"];

    $sql3="delete from rel_usuario_ruta where id_ruta = ".$id_ruta;
    mysqli_query($conn, $sql3);

    $sql1="update th_ruta_surtido set activo = 0 where idr = ".$id_ruta;
    mysqli_query($conn, $sql1);

    $sql1="update td_ruta_surtido set activo = 0 where idr = ".$id_ruta;
    mysqli_query($conn, $sql1);
    echo json_encode(array("success"=>true));
  }
  else
  {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $criterio = $_POST['criterio'];
    $_almacen = $_POST['almacen'];
    $split="";

    //if ($_almacen!="") $split.=" and alp.clave = '$_almacen' ";
    //if ($_almacen!="") $split.=" and (SELECT c.cve_almacenp FROM c_almacen c WHERE c.cve_almac = rs.cve_almac) = (SELECT p.id FROM c_almacenp p WHERE p.clave = '$_almacen') ";
    if ($criterio!="") $split.=" and (rs.nombre like '%$criterio%' OR c_usuario.nombre_completo LIKE '%$criterio%') ";
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'utf8') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset2'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $sqlCount = "SELECT COUNT(rs.idr) AS cuenta FROM c_almacenp alp, c_almacen al, th_ruta_surtido rs 
    LEFT JOIN rel_usuario_ruta ON rel_usuario_ruta.id_ruta = rs.idr
    LEFT JOIN c_usuario ON c_usuario.id_user = rel_usuario_ruta.id_usuario
    WHERE alp.id = al.cve_almacenp $split and rs.Activo = '1'";

    if ((!$res = mysqli_query($conn, $sqlCount))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    $row = mysqli_fetch_array($res);
    $count = $row['cuenta'];

    ///////////////////////////////////////////////////////////////////////////////////////////////////
    $sql = "
        SELECT 
            rs.idr as id, 
            rs.nombre as nombre, 
            alp.nombre as almacen ,
            GROUP_CONCAT( DISTINCT c_usuario.nombre_completo) as nombre_completo,
            #(SELECT DISTINCT COUNT(u.idy_ubica) FROM c_almacenp, c_ubicacion u, c_almacen  
            # WHERE u.cve_almac = c_almacen.cve_almac AND c_almacen.cve_almacenp = c_almacenp.id AND c_almacenp.clave = '$_almacen' 
            # AND u.cve_almac = c_almacen.cve_almac AND u.Activo = '1' AND u.picking = 'S') AS ubica
            #(SELECT COUNT(*) AS Picking FROM c_ubicacion WHERE cve_almac = rs.cve_almac AND picking = 'S') AS ubica
            (SELECT COUNT(*) FROM td_ruta_surtido WHERE td_ruta_surtido.idr = rs.idr) AS ubica
        FROM c_almacenp alp, c_almacen al, th_ruta_surtido rs
            left join rel_usuario_ruta on rel_usuario_ruta.id_ruta = rs.idr
            left join c_usuario on c_usuario.id_user = rel_usuario_ruta.id_usuario
        where 
            #rs.cve_almac = al.cve_almacenp
            #and 
            alp.clave = '{$_almacen}' AND 
            alp.id = al.cve_almacenp  
            AND alp.id = rs.cve_almac
            $split
            and rs.Activo = 1 
            and alp.Activo = 1 
            and al.Activo = 1 
        GROUP BY rs.idr
        ORDER BY  rs.idr 
        LIMIT $start, $limit;  
    ";
    //LIMIT $start, $limit;
    //ORDER BY  $sidx $sord 
    //SELECT rs.idr as id, rs.nombre as nombre, alp.nombre as almacen FROM c_almacenp alp, c_almacen al, th_ruta_surtido rs where rs.cve_almac = al.cve_almac and alp.id = al.cve_almacenp $split and rs.Activo = 1 and alp.Activo = 1 and al.Activo = 1 ORDER BY $sidx $sord LIMIT $start, $limit;";

    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    if( $count >0 ) 
    {
      $total_pages = ceil($count/$limit);
      //$total_pages = ceil($count/1);
    } 
    else 
    {
      $total_pages = 0;
    } 
    if($page > $total_pages) $page=$total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $responce->query = $sql;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) 
    {
      //$row = array_map("utf8_encode", $row);
      $arr[] = $row;
      extract($row);
      $responce->rows[$i]['id']= $id;
      $responce->rows[$i]['cell']=array("",$id, $nombre, $ubica, $nombre_completo,$almacen);
      $i++;
    }
    echo json_encode($responce);
  }
}
