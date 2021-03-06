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
    
    $api_url = "https://query.yahooapis.com/v1/public/yql?q=";
    $query = create_query($json);
    if($query == "")
    {
        return "" ;
    }
    $encoded_query = encodeURIComponent($query);
    $encoded_query = $api_url . $encoded_query . "&format=json";
    $options = array(
        CURLOPT_RETURNTRANSFER => true,   // return web page
        CURLOPT_HEADER         => false,  // don't return headers
        CURLOPT_FOLLOWLOCATION => true,   // follow redirects
        CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
        CURLOPT_ENCODING       => "",     // handle compressed
        CURLOPT_USERAGENT      => "test", // name of client
        CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
        CURLOPT_TIMEOUT        => 120,    // time-out on response
    );
    $curl = curl_init($encoded_query);
    curl_setopt_array($curl, $options);
    $response = curl_exec($curl);
    $result = makeWebhook($response);
    sendMessage($result);
}
else
{
	echo "Method not allowed";
}
function create_query($json)
{
    $geocity="geo-city";
    $city = $json->result->parameters->$geocity;
    if($city == "")
    {
       $api_res = array(
        "speech" => "query",
                "displayText" => "query",
                "data" => new ArrayObject(),
                "contextOut" => [],
                "source" => "agent" );
        return json_encode($api_res);
    }
    $city = "\"$city\"";
    return "select * from weather.forecast where woeid in (select woeid from geo.places(1) where text = $city)";
}
function encodeURIComponent($str) {
    $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
    return strtr(rawurlencode($str), $revert);
}
function  makeWebhook($json_response)
{
    {
    $json_response = json_decode($json_response);
    $query = $json_response->query;
    if($query == "")
    {
         $api_res = array(
        "speech" => "query null",
                "displayText" => "query null",
                "data" => new ArrayObject(),
                "contextOut" => [],
                "source" => "agent" );
        return json_encode($api_res);
    }
    $results = $json_response->query->results;
    if($results == "")
    {
        $api_res = array(
        "speech" => "result null",
                "displayText" => "result null",
                "data" => new ArrayObject(),
                "contextOut" => [],
                "source" => "agent" );
        return json_encode($api_res);
    }
    $channel = $json_response->query->results->channel;
    if($channel == "")
    {
        $api_res = array(
        "speech" => "result channel",
                "displayText" => "result channel",
                "data" => new ArrayObject(),
                "contextOut" => [],
                "source" => "agent" );
        return json_encode($api_res);
    }
    $unit =  $json_response->query->results->channel->units;
    $location = $json_response->query->results->channel->location->city;
    $item = $json_response->query->results->channel->item;
    $wind_speed = $json_response->query->results->channel->wind->speed;
    if($unit == "" || $location == "" || $item=="")
    {
        return "";
    }
    
    if($unit != "")
    {
        $temp_unit = $json_response->query->results->channel->units->temperature;
        $speed_unit = $json_response->query->results->channel->units->speed;
    }
    $condition = $json_response->query->results->channel->item->condition->text;
    if($condition == "")
    {
        return "";
    }
    $temp = $json_response->query->results->channel->item->condition->temp;
    if($temp_unit=="F" and $temp!= "")
    {
        $temp = ($temp-32)*0.556;
        $temp_unit = "C";
    }
    $speech = "Today in $location, temperature is $temp $temp_unit and $condition.\n Wind speed -: $wind_speed $speed_unit.";
    
    $api_res = array(
        "speech" => $speech,
                "displayText" => $speech,
	    	"data" => new ArrayObject(),
                "contextOut" => [],
                "source" => "agent" );
    return $api_res;
    
}
    
}

function sendMessage($parameters) {
    	header("Content-type: application/json");
        	echo json_encode($parameters);
}
?>
