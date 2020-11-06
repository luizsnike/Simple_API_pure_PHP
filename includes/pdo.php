<?php
function database(){
    try{
        $mysql = new \PDO("mysql:host=localhost;dbname=db_name", "user", "p4ss");
    }
    catch(PDOException $e){
        http_response_code(500);
        echo json_encode(array("error" => "DataBase Error"));
    }
    if(isset($mysql)){return $mysql;}
}

function verify_authorization($token){
    $tokens = Array(
        "123", //TOKEN PARCEIRO 1
        "456" //TOKEN PARCEIRO 2
    );
    if (in_array($token, $tokens)) {
        return true;
    }else{
        return false;
    }
}
?>
