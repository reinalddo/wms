<?php
include '../../../config.php';
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();
$ga = new \RutasSurtido\RutasSurtido();

error_reporting(0);

if (isset($_POST) && !empty($_POST)) 
{
    if($_POST['oper'] == edit)
      {
        if($_POST['id']!="")
        {
            $_POST['idy_ubica'] = $_POST['id'];
        }
        else
        {
            $_POST['idy_ubica'] = $_POST['idy_ubica'];
        }
        $ga->actualizarRutasSurtido($_POST);
    }
  
    if($_POST['action'] == 'cargarUbicaciones')
    {
        $almacen = $_POST['almacen'];
        $zona = $_POST["zona"];
        $proveedor = $_POST["proveedores"];
        $and = "";
        $responce = [];
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        if($zona != ''){$and = " AND al.cve_almac = {$zona} ";}
        if($proveedor != ''){$and .= "and ts_existenciapiezas.ID_Proveedor = '{$proveedor}'";}
        $sql = "
            SELECT
                ub.idy_ubica,
                alp.nombre as almacen,
                al.des_almac as zona,
                if(ub.TECNOLOGIA = 'PTL','PTL', if(ub.picking='S','Picking','')) as picking,
                ub.orden_secuencia,
                ub.CodigoCSD as bl,
                ts_existenciapiezas.ID_Proveedor as proveedores
            FROM c_almacen al
                INNER JOIN c_almacenp alp on alp.id = al.cve_almacenp
                INNER JOIN c_ubicacion ub on ub.cve_almac = al.cve_almac
                left JOIN ts_existenciapiezas on ts_existenciapiezas.idy_ubica = ub.idy_ubica
            WHERE al.Activo = '1'
                AND ub.Activo = '1'
                AND alp.clave= '{$almacen}'
                AND ub.picking = 'S'
                #and ub.idy_ubica not in (select td_ruta_surtido.idy_ubica from td_ruta_surtido where td_ruta_surtido.Activo='1')
                #AND ub.idy_ubica IN (SELECT cve_ubicacion FROM V_ExistenciaGralProduccion WHERE cve_almac = alp.id)
                {$and}
            GROUP BY ub.CodigoCSD
            ORDER BY ub.CodigoCSD DESC
        ";
      
       

        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
        $arr = array();
        $i = 0;
        while ($row = mysqli_fetch_array($res)) 
        {
            $row = array_map("utf8_encode", $row);
            $arr[] = $row;
            extract($row);
            $responce[$i]['id']= $idy_ubica;
            $responce[$i]['cell']=array($idy_ubica,$bl,$almacen,$zona,$picking,$orden_secuencia);
            $i++;
        }
        echo json_encode($responce);
    }
    else if($_POST['action'] == 'traerSurtidores')
    {
$utf8Sql = \db()->prepare("SET NAMES 'utf8mb4';");
$utf8Sql->execute();

        $responce = [];
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $confSql = "SELECT IFNULL(Valor, '') AS instancia FROM t_configuraciongeneral WHERE cve_conf = 'instancia' LIMIT 1";
        if (!$res = mysqli_query($conn, $confSql)) echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        $instancia = mysqli_fetch_array($res)["instancia"];

    $UsuariosOtraRuta = " and u.id_user not in (SELECT id_usuario from rel_usuario_ruta WHERE id_ruta in (select idr from th_ruta_surtido where cve_almac = a.id )) ";

    if($instancia == 'welldex' || $instancia == 'asl')
    $UsuariosOtraRuta = "";

        $sql="
            SELECT
                id_user,
                nombre_completo  
            FROM `c_usuario` u
          INNER JOIN t_permisos_perfil pp ON pp.ID_PERFIL = u.perfil
          #INNER JOIN t_perfilesusuarios pu ON pu.ID_PERFIL = u.perfil AND pu.PER_NOMBRE = 'Surtidor'
          INNER JOIN s_permisos_modulo pm ON pm.ID_PERMISO = pp.ID_PERMISO
          INNER JOIN trel_us_alm ON trel_us_alm.cve_usuario = u.cve_usuario 
          INNER JOIN c_almacenp a ON a.clave = trel_us_alm.cve_almac
            WHERE pm.ID_PERMISO = 2
                and trel_us_alm.cve_almac = '".$_POST["almacen"]."' 
                {$UsuariosOtraRuta}
            GROUP BY u.nombre_completo
        ";
/*                LEFT JOIN t_permisos_perfil pp on pp.ID_PERFIL = u.perfil
                LEFT JOIN s_permisos_modulo pm on pm.ID_PERMISO = pp.ID_PERMISO
                LEFT JOIN trel_us_alm on trel_us_alm.cve_usuario = u.cve_usuario 
            where u.perfil = 15 
*/

        #AND pm.ID_PERMISO = 2
        if (!$res = mysqli_query($conn, $sql))
        {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
        $responce["success"]=true;
        $i=0;
        while ($row = mysqli_fetch_array($res)) 
        {
            //$row = array_map("utf8_encode", $row);
            $responce["user"][$i]['id']=$row[0];
            $responce["user"][$i]['nombre']=$row[1];
            $i++;
        }
        echo json_encode($responce);
    }
    else
    {
        $page = $_POST['page']; // get the requested page
        $limit = $_POST['rows']; // get how many rows we want to have into the grid
        $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
        $sord = $_POST['sord']; // get the direction
        $criterio = $_POST['criterio'];
        $_almacen = $_POST['almacen'];
        $zona = $_POST['zona'];
        $editaCampo = $_POST['oper'];
        if ($zona!="") $split.=" and ub.cve_almac='$zona' ";
        $start = $limit*$page - $limit; // do not put $limit*($page - 1)
        if(!$sidx) $sidx =1;

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $sqlCount = " 
            SELECT 
                count(*)
            FROM c_almacen al, c_almacenp alp, c_ubicacion ub 
            where ub.cve_almac = al.cve_almac 
                and alp.id = al.cve_almacenp 
                and al.Activo = 1 
                and ub.Activo = 1 $split 
                and ub.cve_almac <> '1' 
                and alp.clave='".$_almacen."'
                and ub.picking = 'S';
        ";
        if ((!$res = mysqli_query($conn, $sqlCount))) 
        {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }

        $row = mysqli_fetch_array($res);
        $count = $row[0];

        if ($editaCampo=="" && $sidx !="orden_secuencia" ) 
        {  
            $sqlUpdate = "UPDATE c_ubicacion SET orden_secuencia = null WHERE orden_secuencia <> '' or orden_secuencia = 0;";
            if (!($res = mysqli_query($conn, $sqlUpdate))) 
            {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }
        }

        $sql = "
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
            where al.Activo = 1 
                and ub.Activo = 1 $split  
                and alp.clave='".$_almacen."'
                and ub.picking = 'S'
            order by ub.CodigoCSD DESC
            LIMIT $start, $limit;
        ";

        if (!($res = mysqli_query($conn, $sql))) 
        {
          echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }

        if( $count >0 ) 
        {
            $total_pages = ceil($count/$limit);
        } 
        else 
        {
            $total_pages = 0;
        } 
        if ($page > $total_pages)
            $page=$total_pages;

        $responce->page = $page;
        $responce->total = $total_pages;
        $responce->records = $count;
        $responce->zona =$zona;
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
        echo json_encode($responce);
    }
}