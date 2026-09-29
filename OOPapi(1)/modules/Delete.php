<?php
include_once "./modules/Common.php";
class Delete extends Common{
    protected $pdo;
    public function __construct(\PDO $pdo){
        $this->pdo = $pdo;
    }
  
    public function deleteCars($id){
        
        $errmsg = "";
        $code = 0;
        
        try{
            $sqlString = "UPDATE FROM carstable WHERE carID = ?";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute([$id]);
            $code = 200;
            $data = null;
            return array("data"=>$data, "code"=>$code);
        }
        catch(\PDOException $e){
            $errmsg = $e->getMessage();
            $code = 400;
        }
        
        $result= array("errmsg"=>$errmsg, "code"=>$code);
        if($result['code'] == 200){
            $this->logger("Jerald", "DELETE", "Archived a new car record");
            return $this->generateResponse($result['data'], "success", "Successfully archived a new record.", $result['code']);
          }
          $this->logger("Jerald", "DELETE", $result['errmsg']);
          return $this->generateResponse(null, "failed", $result['errmsg'], $result['code']);
    }
    
    public function deleteUsers($id){
        
        $errmsg = "";
        $code = 0;
        
        try{
            $sqlString = "UPDATE FROM userstable WHERE userID = ?";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute([$id]);
            $code = 200;
            $data = null;
            return array("data"=>$data, "code"=>$code);
        }
        catch(\PDOException $e){
            $errmsg = $e->getMessage();
            $code = 400;
        }
        
        $result= array("errmsg"=>$errmsg, "code"=>$code);
        if($result['code'] == 200){
            $this->logger("Jerald", "DELETE", "Archived a new user record");
            return $this->generateResponse($result['data'], "success", "Successfully archived a new record.", $result['code']);
          }
          $this->logger("Jerald", "DELETE", $result['errmsg']);
          return $this->generateResponse(null, "failed", $result['errmsg'], $result['code']);
    }
    
}
?>