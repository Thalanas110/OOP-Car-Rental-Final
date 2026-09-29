<?php
class Patch{
    protected $pdo;
    public function __construct(\PDO $pdo){
        $this->pdo = $pdo;
    }
    public function patchCars($body, $id){
        $values = [];
        $errmsg = "";
        $code = 0;
        foreach($body as $value){
            array_push($values, $value);
        }
        array_push($values, $id);
        
        try{
            $sqlString = "UPDATE carstable SET daily_rate=?, plate_no=? WHERE carID = ?";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute($values);
            $code = 200;
            $data = null;
            return array("data"=>$data, "code"=>$code);
        }
        catch(\PDOException $e){
            $errmsg = $e->getMessage();
            $code = 400;
        }
        
        return array("errmsg"=>$errmsg, "code"=>$code);
    }
    public function archiveCars($id){
        
        $errmsg = "";
        $code = 0;
        
        try{
            $sqlString = "UPDATE carstable SET isdeleted=1 WHERE carID = ?";
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
        
        return array("errmsg"=>$errmsg, "code"=>$code);
    }
    public function patchUsers($body, $id){
        $values = [];
        $errmsg = "";
        $code = 0;
        foreach($body as $value){
            array_push($values, $value);
        }
        array_push($values, $id);
        
        try{
            $sqlString = "UPDATE userstable SET name=?, contact_no=?, drivers_license=? WHERE userID = ?";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute($values);
            $code = 200;
            $data = null;
            return array("data"=>$data, "code"=>$code);
        }
        catch(\PDOException $e){
            $errmsg = $e->getMessage();
            $code = 400;
        }
        
        return array("errmsg"=>$errmsg, "code"=>$code);
    }
    public function patchUserAccount($body){
        $headers = getallheaders();
        $code = 0;
        $errmsg = "";
        $message = "";
    
        
        if (!isset($body->user_password) || empty($body->user_password)) {
            return array("errmsg" => "Password is required", "code" => 400);
        }
    
        
        if (!isset($headers['Authorization']) || empty($headers['Authorization'])) {
            return array("errmsg" => "Unauthorized: Missing token", "code" => 401);
        }
    
        $token = $headers['Authorization'];
    
        try {
        
            $sqlString = "SELECT userID FROM accountstable WHERE token = ?";
            $stmt = $this->pdo->prepare($sqlString);
            $stmt->execute([$token]);
    
            if ($stmt->rowCount() === 0) {
                return array("errmsg" => "Unauthorized: Invalid token", "code" => 401);
            }
    
            $result = $stmt->fetch();
            $userID = $result['userID'];
    
        
            $hashedPassword = password_hash($body->user_password, PASSWORD_BCRYPT);
    
        
            $updateSql = "UPDATE accountstable SET user_password = ? WHERE userID = ?";
            $updateStmt = $this->pdo->prepare($updateSql);
            $updateStmt->execute([$hashedPassword, $userID]);
    
            $code = 200;
            $message = "Password updated successfully";
            return array("message" => $message, "code" => $code);
        } catch (\PDOException $e) {
            $errmsg = "Failed to update password"; 
            $code = 400;
        }
    
        return array("errmsg" => $errmsg, "code" => $code);
    }
    
    public function archiveUsers($id){
        
        $errmsg = "";
        $code = 0;
        
        try{
            $sqlString = "UPDATE userstable SET isdeleted=1 WHERE userID = ?";
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
        
        return array("errmsg"=>$errmsg, "code"=>$code);
    }
    

}
?>