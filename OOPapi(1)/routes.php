<?php
require_once "./modules/Get.php";
require_once "./modules/Post.php";
require_once "./config/database.php";
require_once "./modules/Patch.php";
require_once "./modules/Delete.php";
require_once "./modules/Auth.php";
require_once "./modules/Booking.php";
require_once "./modules/Billing.php";
require_once "./modules/Common.php";

$db = new Connection();
$pdo = $db->connect();
$post = new Post($pdo);
$get = new Get($pdo);
$patch = new Patch($pdo);
$delete = new Delete($pdo);
$auth = new Authentication($pdo);
$book = new Booking($pdo);
$bill = new Billing($pdo);

if(isset($_REQUEST['request'])){
    $request = explode("/", $_REQUEST['request']);
}
else{
    echo "URL does not exist.";
}
switch($_SERVER['REQUEST_METHOD']){
    case "GET":
        if($auth->isAuthorized()){
        switch($request[0]){
            case "cars":
                if(count($request) > 1){
                    echo json_encode($get->getCars($request[1]));
                }
                else{
                    echo json_encode($get->getCars());
                }
            break;
            case "users":
                if(count($request) > 1){
                    echo json_encode($get->getUsers($request[1]));
                }
                else{
                    echo json_encode($get->getUsers());
                }
            break;
            
            case"carchecking":
                echo json_encode($book->carMenu());
            break;
            
            default:
                http_response_code(401);
                echo "Invalid endpoint";
            break;
        }
    
    }else{
        echo "Unauthorized";
    }
        
        break;
    case "POST":
        
        $body = json_decode(file_get_contents("php://input"));
        switch($request[0]){
            case "cars":
                if($auth->isAuthorized()){
                echo json_encode($post->postCars($body));
                break;
        }else{
            echo "Unauthorized";
        }
            case "users":
                echo json_encode($post->postUsers($body));
            break;
            
            case "useraccount":
                
                echo json_encode($auth->addAccounts($body));
            break;
            
            case "login":
                echo json_encode($auth->login($body));
            break;
            
            case "carbooking":
                if($auth->isAuthorized()){
                echo json_encode($book->carBooking($body));
            break;
        }else{
            echo "Unauthorized";
        }
            case "billing":
                echo json_encode($bill->addPayment($body));
            break;
            default:
                http_response_code(400);
                echo "Invalid endpoint";
            break;
        }
        
    break;
            
    case "PATCH":
        if($auth->isAuthorized()){
        $body = json_decode(file_get_contents("php://input"));
        switch($request[0]){
            case "cars":
                echo json_encode($patch->patchCars($body, $request[1]));
            break;

            case "users":
                echo json_encode($patch->patchUsers($body, $request[1]));
            break;
            
            case "useraccount":
                echo json_encode($patch->patchUserAccount($body));
            break;
            default:
            http_response_code(400);
                echo "Invalid  Endpoint";
            break;

        }
        break;
    }else{
        echo "Unauthorized";
    }
    case "DELETE":
        switch($request[0]){
            case "cars":
                echo json_encode($patch->archiveCars($request[1]));
            break;
            case "users":
                echo json_encode($patch->archiveUsers($request[1]));
            break;
        }
            case "destroycars":
                echo json_encode($delete->deleteCars($request[1]));
            break;
            case "destroyusers":
                echo json_encode($delete->deleteUsers($request[1]));
            break;
    default:
        http_response_code(400);
        echo "Invalid Request Method";
    break;
}
?>