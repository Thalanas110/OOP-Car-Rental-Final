<?php
class Authentication{

    protected $pdo;
    public function __construct(\PDO $pdo){
        $this->pdo = $pdo;
        
    }
    
    public function isAuthorized(){
        $headers=getallheaders();
        
        return $headers['Authorization']==$this->getToken();
    }
    
    private function getToken(){
        $headers=getallheaders();
        $sqlString = "SELECT token FROM accountstable WHERE user_email=?";
            $stmt =$this->pdo->prepare($sqlString);
            $stmt->execute([$headers['X-Auth-User']]);
           
            $result=$stmt->fetchAll()[0];
            return $result['token'];
            
    }
    public function saveToken($token,$userID){
        
        $errmsg = "";
        $code = 0;
        
        try{
            $sqlString = "UPDATE accountstable SET token=? WHERE userID = ?";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute([$token,$userID]);
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
    private function generateHeader(){
        $header=[
            "typ"=>"JWT",
            "alg"=>"HS256",
            "app"=>"CarRental",
            "dev"=>"Jerald Pangan"
        ];
        return base64_encode(json_encode($header));
    }
    private function generatePayload($id,$user_email){
        $payload=[
            "uid"=>$id,
            "uc"=>$user_email,
            "email"=> "jeraldpangan25@gmail.com",
            "date"=>date_create(),
            "exp"=>("Y-m-d    H:i:s")
        ];
        return base64_encode(json_encode($payload));
    }
    private function generateToken($id,$user_email){
        $header = $this->generateHeader();
        $payload = $this->generatePayload($id,$user_email);
        $signature=hash_hmac("sha256", $header.'.'.$payload, TOKEN_KEY);
        return $header . '.' . $payload . '.' . base64_encode($signature);
    }
    private function isSamePassword($inputPassword, $existingHash){
        
        $hash=crypt($inputPassword,$existingHash);
        return $hash==$existingHash;
    }
    private function encryptPassword($user_password){
        $hashFormat="$2y$10$";
        $saltLength=22;
        $salt=$this->generateSalt($saltLength);
        return crypt($user_password, $hashFormat . $salt);
    }
    private function generateSalt($length){
        $urs = md5(uniqid(mt_rand(),true));
        $b64String = base64_encode($urs);
        $mb64String = str_replace("+",".",$b64String);
        return substr($mb64String,0,$length);
    }

    
    public function login($body){
        $user_email = $body->user_email;
        $user_password = $body->user_password;

        $code = 0;
        $payload="";
        $remarks="";
        $message="";
        try{
            $sqlString = "SELECT userID, user_email,user_password,token FROM accountstable WHERE user_email=?";
            $stmt =$this->pdo->prepare($sqlString);
            $stmt->execute([$user_email]);
            if($stmt->rowCount()>0){
                $result=$stmt->fetchAll()[0];
                if($this->isSamePassword($user_password, $result['user_password'])){
                    $code = 200;
                    $remarks="success";
                    $message="Login successfull"; 

                    $token = $this->generateToken($result['userID'],$result['user_email']);
                    $token_arr= explode('.',$token);
                    $this->saveToken($token_arr[2],$result['userID']);
                    $payload=array("userID"=>$result['userID'],"username"=>$result['user_email'],"token"=>$token_arr[2]);
                     

                    
                }else{
                    $code = 401;
                    $payload=null;
                    $remarks="failed";
                    $message="Password is incorrect";
                }
            }else{
                $code = 401;
                $payload=null;
                $remarks="failed";
                $message="Username not found";
            }
        }
        catch(\PDOException $e){
            $errmsg = $e->getMessage();
            $code = 400;
        }
        return array("payload"=>$payload,"remarks"=>$remarks,"message"=>$message,"code"=>$code);
    }
    public function addAccounts($body){
        $values = [];
        $errmsg = "";
        $code = 0;

        $body->user_password= $this->encryptPassword($body->user_password);
        foreach($body as $value){
            array_push($values, $value);
        }
        
        try{
            $sqlString = "INSERT INTO accountstable(userID,user_email, user_password) VALUES (?,?,?)";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute($values);

            $code = 200;
            $message="Account created successfully";
            $data = null;

            return array("message"=>$message,"data"=>$data, "code"=>$code);
        }
        catch(\PDOException $e){
            $errmsg = $e->getMessage();
            $code = 400;
        }
        
        return array("errmsg"=>$errmsg, "code"=>$code);
    
    }
}


?>