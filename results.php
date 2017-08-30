<?php
$method = $_SERVER['REQUEST_METHOD'];
if($method == 'POST')
{
    $requestBody = file_get_contents('php://input');
    $json = json_decode($requestBody);
    $action=$json->result->action;
    if($action != "yahooWeatherForecast")
    {
        return "";
    }
    
    $api_url = "https://query.yahooapis.com/v1/public/yql?";
    $query = create_query($json);
    if($query == "")
    {
        return "" ;
    }
    $encoded_query = encodeURIComponent($query);
    $encoded_query = $api_url + $encoded_query + "&format=json";
    
    
}

function create_query($json)
{
    $city = $json->result->parameters->geo-city;
    if($city = "")
    {
        return "" ;
    }
    return "select * from weather.forecast where woeid in (select woeid from geo.places(1) where text='" + city + "')";
}

function encodeURIComponent($str) {
    $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
    return strtr(rawurlencode($str), $revert);
}
?>
