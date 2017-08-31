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
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $encoded_query);
    $response = curl_exec($curl);
    $json_response = json_encode($response);
    $result = makeWebhook($json_response);
    return $result;
    
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

function  makeWebhook($json_response)
{
    $query = $json_response->query;
    if($query == "")
    {
        return "";
    }
    $results = $json_response->query->results;
    if($results == "")
    {
        return "";
    }
    $channel = $json_response->query->results->channel;
    if($channel == "")
    {
        return "";
    }
    $unit =  $json_response->query->results->channel->unit;
    $location = $json_response->query->results->channel->location;
    $item = $json_response->query->results->channel->item;
    
    if($unit == "" || $location == "" || $item=="")
    {
        return "";
    }
    
    $condition = $json_response->query->results->channel->item->condition;
    if($condition == "")
    {
        return "";
    }
    $speech = $json_response->query->results->channel->item->condition->temp;
    
    $api_res = array("speech" => $speech, "displayText" => $speech);
    return json_encode($api_res);
    
}
?>
