
<?php
$method = $_SERVER['REQUEST_METHOD'];
if($method == 'POST')
{
    $requestBody = file_get_contents('php://input');
    $json = json_decode($requestBody);
    $action=$json->result->action;
    if($action != "yahooWeatherForecast")
    {
        $api_res = array("speech" => "", "displayText" => "action", "source" => "");
        return json_encode($api_res);
    }
    
    $api_url = "https://query.yahooapis.com/v1/public/yql?q=";
    $query = create_query($json);
    header('Cache-Control: no-cache, must-revalidate');
    header('Content-Type: application/json');
    var_dump(headers_list());
    if($query == "")
    {
        $api_res = array("speech" => "", "displayText" => "query", "source" => "");
        return json_encode($api_res);
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
    return Response::$result;
    
					
}
else
{
	echo "Method not allowed";
}
function create_query($json)
{
    $city = $json->result->parameters->geo-city;
    if($city == "")
    {
        $api_res = array("speech" => "", "displayText" => "city", "source" => "");
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
    $json_response = json_decode($json_response);
    $query = $json_response->query;
    if($query == "")
    {
        $api_res = array("speech" => "", "displayText" => "query2", "source" => "");
        return json_encode($api_res);
    }
    $results = $json_response->query->results;
    if($results == "")
    {
        $api_res = array("speech" => "", "displayText" => "results", "source" => "");
        return json_encode($api_res);
    }
    $channel = $json_response->query->results->channel;
    if($channel == "")
    {
        $api_res = array("speech" => "", "displayText" => "channel", "source" => "");
        return json_encode($api_res);
    }
    $unit =  $json_response->query->results->channel->units;
    $location = $json_response->query->results->channel->location;
    $item = $json_response->query->results->channel->item;
    
    if($unit == "" || $location == "" || $item=="")
    {
        $api_res = array("speech" => "", "displayText" => "unit", "source" => "");
        return json_encode($api_res);
    }
    
    $condition = $json_response->query->results->channel->item->condition;
    if($condition == "")
    {
        $api_res = array("speech" => "", "displayText" => "condition", "source" => "");
        return json_encode($api_res);
    }
    $speech = $json_response->query->results->channel->item->condition->temp;
    
    $api_res = array("speech" => $speech, "displayText" => $speech, "source" => "webhook");
    return json_encode($api_res);
    
}
?>
