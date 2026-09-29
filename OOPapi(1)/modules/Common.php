<?php
class Common {
    protected function getDataByTable($tablename, $condition, \PDO $pdo){ {
        $sqlString = "SELECT * FROM $tablename WHERE $condition";
        
        $data = array();
        $errmsg = "";
        $code = 0;
        
        try{
            if($result = $pdo->query($sqlString)->fetchAll()){
                foreach($result as $record){
                    array_push($data, $record);
                }
                $result = null;
                $code = 200;
                return array("code"=>$code, "data"=>$data); 
            }
            else{
                $errmsg = "No data found";
                $code = 404;
            }
        }
        catch(\PDOException $e){
            $errmsg = $e->getMessage();
            $code = 403;
        }
        return array("code"=>$code, "errmsg"=>$errmsg);
    }
   
    }
    protected function generateResponse($data, $remark, $message, $statusCode){
        $status = array(
            "remark" => $remark,
            "message" => $message
        );
        http_response_code($statusCode);
        return array(
            "payload" => $data,
            "status" => $status,
            "prepared_by" => "Jerald", 
            "date_generated" => date_create()
        );
    }
    protected function logger($user, $method, $action){
        //datetime, user,method,action -> text file .log
        $filename = date("Y-m-d") . ".log";
        $datetime = date("Y-m-d H:i:s");
        $logMessage = "$datetime,$method,$user,$action" . PHP_EOL;
       // file_put_contents("./logs/$filename", $logMessage, FILE_APPEND | LOCK_EX);
        error_log($logMessage, 3, "./logs/$filename");
    }
}
?>
