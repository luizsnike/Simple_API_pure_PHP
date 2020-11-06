<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
// load connection file
require("../includes/pdo.php");
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"));
    $headers = apache_request_headers();
    if(($headers["Content-Type"] <> "application/json")){
        http_response_code(500);
        echo json_encode(array("status" => "6", "info" => "Content-Type Error"));
        die;
    }
    if(!verify_authorization($headers["Authorization"])){
        http_response_code(500);
        echo json_encode(array("status" => "7", "info" => "Authorization Failed"));
        die;
    }
    if(isset($data->campo1)){
        $campo1 = (int)$data->campo1;
    }else{
        http_response_code(500);
        echo json_encode(array("status" => "8", "info" => "campo1 not received"));
        die;
    }
    if(isset($data->campo2)){
        $campo2 = strip_tags($data->campo2);
    }else{
        http_response_code(500);
        echo json_encode(array("status" => "9", "info" => "campo2 not received"));
        die;
    }

	$pdo = database();
	$salvar = $pdo->prepare("CALL ProcInsert ('{$campo1}','{$campo2}')");
	$salvar->execute();
	if ($salvar->rowCount() == 1) {
		$saida = $salvar->fetchAll(PDO::FETCH_ASSOC);
		$saida = $saida[0];
		$status_db = $saida["_RETURN"];
		// 1 - Insert OK
		// 2 - Error - campo1 Exists
		// 3 - Error - campo2 Exists
		// 4 - Error - Database
	} else {
		$status_db = "4";
	}


    if($status_db == "1" && ($status == "1" || $status == "0")) {
        http_response_code(200);
        echo json_encode(
            array(
                "status" => "{$status}",
                "campo1" => "{$campo1}",
                "campo2" => "{$campo2}"
            ));
        die;
    }else{
        if($status_db == "2") {
            $httpStatus = 403;
            $error = "campo1 already exists";
        }elseif($status_db == "3"){
            $httpStatus = 403;
            $error = "campo2 already exists";
        }elseif($status_db == "4"){
            $httpStatus = 500;
            $error = "Database Error";
        }else{
            $httpStatus = 500;
            $status_db = "5";
            $error = "Undefined error";
        }
        http_response_code($httpStatus);
        echo json_encode(
            array(
                "status" => "{$status_db}",
                "info" => "{$error}"
            ));
        die;
    }
}else{
    http_response_code(500);
    echo json_encode(array("status" => "12", "info" => "Method not suported"));
}
?>