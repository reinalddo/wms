<?php

namespace Tools;

class Tools {
  
  public $insertId = NULL;
  
  public function permiso($id_menu,$id_submenu)
  {
    $permiso_query = $this->dbquery("select Activo from t_profiles as a where id_menu='".$id_menu."' and id_submenu='".$id_submenu."' and id_role='".$_SESSION["perfil_usuario"]."' Limit 1");
    $permiso = $permiso_query->fetchAll();
    return $permiso[0]["Activo"];
  }
  
  public function permisos($id_menu,$lista_submenus)
  {
    $permisos = array();
    foreach($lista_submenus as $key => $item){
      $permisos[$key] = $this->permiso($id_menu,$item);
    }
    return $permisos;
  }
  
  public function dbQuery($query)
  {
    $conn = \db();
    $dbquery_item = $conn->prepare($query);
    $dbquery_item->execute();
    $this->insertId = $conn->lastInsertId();
    return $dbquery_item;
  }
  
  public function dbInsert($table,$data)
  {
    $keys = array();
    $values = array();
    foreach($data as $k=>$v)
    {
      $keys[] = $k;
      if($v == null)
      {
        $values[] = "NULL";
      }
      else
      {
        $values[] = "'".$v."'";
      }
    }
    $keys_list = implode(",",$keys);
    $values_list = implode(",",$values);
    $sql = "INSERT INTO ".$table." (".$keys_list.") values (".$values_list."); ";
    return $this->dbQuery($sql);
  }
  
  public function dbUpdate($table,$data,$where=array())
  {
    $update = array();
    foreach($data as $k=>$v)
    {
      $update[] = $k." = '".$v."'";
    }
    $update_list = implode(",",$update);
    
    $wh = "";
    if(count($where)>0)
    {
      $wh_array = array();
      $wh = " WHERE ";
      foreach($where as $k=>$v)
      {
        $wh_array[] = $k." = '".$v."'";
      }
      $wh .= implode(" AND ",$wh_array);
    }
    $sql = "UPDATE ".$table." SET ".$update_list.$wh;
    return $this->dbQuery($sql);
  }
  
  public function calcularCostoPromedio($table,$data,$where=array())
  {
      $update = array();
      foreach($data as $k=>$v)
      {
        $update[] = $k." = '".$v."'";
      }
      $update_list = implode(",",$update);

      $wh = "";
      if(count($where)>0)
      {
        $wh_array = array();
        $wh = " WHERE ";
        foreach($where as $k=>$v)
        {
          $wh_array[] = $k." = '".$v."'";
        }
        $wh .= implode(" AND ",$wh_array);
      }
      $sql = "UPDATE ".$table." SET ".$update_list.$wh;
      return $this->dbQuery($sql);
  }
  
}
