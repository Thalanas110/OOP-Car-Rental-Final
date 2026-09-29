<?php 
include_once "./modules/Common.php";
class Get extends Common{
    protected $pdo;
    public function __construct(\PDO $pdo){
        $this->pdo = $pdo;
    }
    public function getCars($id = null){
        $condition = "isdeleted=0";
        if($id != null){
            $condition .= " AND carID=" . $id; 
        }
        $result= $this->getDataByTable("carstable", $condition,$this->pdo);
        if($result['code'] == 200){
            return $this->generateResponse($result['data'], "success", "Successfully retrieved records.", $result['code']);
        }
        return $this->generateResponse(null, "failed", $result['errmsg'], $result['code']);
    }
   public function getUsers($id = null){
        $condition = "isdeleted=0";
        if($id != null){
            $condition .= " WHERE userID=" . $id; 
        }
       
        $result= $this->getDataByTable("userstable", $condition,$this->pdo);
        if($result['code'] == 200){
            
            return $this->generateResponse($result['data'], "success", "Successfully retrieved records.", $result['code']);
        }
        return $this->generateResponse(null, "failed", $result['errmsg'], $result['code']);
    }
    }
    

?>