<?php

// your code goes here
$method = $_SERVER['REQUEST_METHOD'];
if($method == 'POST')
{
    $requestBody = file_get_contents('php://input');
    $json = json_decode($requestBody);
    $text = $json->result->fulfillment->messages;
    echo ($text + "test");
}

else {
    echo "Method not allowed";
}
?>
