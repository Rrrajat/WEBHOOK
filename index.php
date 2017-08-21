<?php

// your code goes here
$method = $_SERVER['REQUEST_METHOD'];
if($method == 'POST')
{
    $requestBody = file_get_contents('php://input');
    $json = json_decode($requestBody);
    $text = $json->result->fulfillment->speech;
    $speech=$text;
    $respone=new \stdClass();
    $respone->speech = $speech;
    $respone->displayText=$speech;
    $respone->source="webhook";
    echo json_encode($respone);
}

else {
    echo "Method not allowed";
}
?>

