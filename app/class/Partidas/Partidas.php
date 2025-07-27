<?php
namespace Partidas;
$tools = new \Tools\Tools();

class Partidas
{

  const TABLE = 'c_partidas';
  var $identifier;
  var $result = array(
    "success"=>false,
    "data"=>null,
    "exec"=>array()
  );

  public function __construct()
  {
      $this->tools = new \Tools\Tools();
  }
  
  public function get($data)
  {
    $sql = 'SELECT * FROM c_partidas Where c_partidas.id = '.$data["id"];
    $partida = $this->tools->dbQuery($sql)->fetch();
    $this->result["data"]["data_result"] = $partida;
    if(isset($data["callback"]))
    {
      $this->result["exec"][] = $data["callback"]."(data_result);";
    }
    $this->result["success"] = true;
  }
  
  function save($data) 
  {
    try
    {
      $update = false;
      if(isset($data["id"]))
      {
        if($data["id"]!="")
        {
          $update = true;
        }
      }
      $fields = array(
        "id_presupuesto" => $data['id_presupuesto'],
        "clave_partida"  => $data['clave_partida'],
        "nombre_partida" => $data['nombre_partida']
      );
      if($update)
      {
        $where = array("id" => $data['id']);
        $res = $this->tools->dbUpdate("c_partidas",$fields,$where);
      }
      else
      {
        $res = $this->tools->dbInsert("c_partidas",$fields);
      }
      $this->result["data"]["data_result"] = $res;
      if(isset($data["callback"]))
      {
        $this->result["exec"][] = $data["callback"]."(data_result);";
      }
      $this->result["success"] = true;
    }
    catch(PDOException $e)  
    {
      echo 'ERROR: ' . $e->getMessage();
    }
  }
  
  function getPresupuestos($data)
  {
    $sql = 'SELECT * FROM c_presupuestos';
    $presupuestos = $this->tools->dbQuery($sql)->fetchAll();
    $this->result["data"]["data_presupuestos"] = $presupuestos;
    if(isset($data["callback"]))
    {
      $this->result["exec"][] = $data["callback"]."(data_presupuestos);";
    }
    $this->result["success"] = true;
  }
  
  function delete($data)
  {
    $sql = "DELETE FROM c_partidas WHERE id = '".$data["id"]."';";
    $res = $this->tools->dbQuery($sql);
    $this->result["data"]["data_delete"] = $res;
    if(isset($data["callback"]))
    {
      $this->result["exec"][] = $data["callback"]."(data_delete);";
    }
    $this->result["success"] = true;
  }
  
}
