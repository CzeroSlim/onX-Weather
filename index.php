<?php
$debug=false;
$json = json_decode(file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?latlng=".$_GET["latlng"]."&sensor=false"));
$results=$json->results;
/*
$address=$results[0]->formatted_address;
if (strpos($address, "-")){
$add=explode("-", $address);
$address=$add[1];
}
*/
$done=false;
$a=count($results);
for ($i=0;$i<$a;$i++){
$b=count($results[$i]->address_components);
for ($j=0;$j<$b;$j++){
$res=$results[$i]->address_components;
if ($res[$j]->types[0]=="locality"){
if (!$done){
$address=$res[$j]->long_name;
$done=true;
}
}
}
}
$done=false;
$a=count($results);
for ($i=0;$i<$a;$i++){
$b=count($results[$i]->address_components);
for ($j=0;$j<$b;$j++){
$res=$results[$i]->address_components;
if ($res[$j]->types[0]=="administrative_area_level_1"){
if (!$done){
$address=$address.", ".$res[$j]->long_name;
$done=true;
}
}
}
}
/*
$done=false;
$a=count($results);
for ($i=0;$i<$a;$i++){
$b=count($results[$i]->address_components);
for ($j=0;$j<$b;$j++){
$res=$results[$i]->address_components;
if ($res[$j]->types[0]=="postal_code"){
if (!$done){
$address=$address." ".$res[$j]->long_name;
$done=true;
}
}
}
}
$done=false;
$a=count($results);
for ($i=0;$i<$a;$i++){
$b=count($results[$i]->address_components);
for ($j=0;$j<$b;$j++){
$res=$results[$i]->address_components;
if ($res[$j]->types[0]=="country"){
if (!$done){
$address=$address.", ".$res[$j]->long_name;
$done=true;
}
}
}
}
*/
if ($debug){
echo $address;
}
function post_request($url, $data, $referer='') {
 
    // Convert the data array into URL Parameters like a=b&foo=bar etc.
    $data = http_build_query($data);
 
    // parse the given URL
    $url = parse_url($url);
 
    if ($url['scheme'] != 'http') { 
        die('Error: Only HTTP request are supported !');
    }
 
    // extract host and path:
    $host = $url['host'];
    $path = $url['path'];
 
    // open a socket connection on port 80 - timeout: 30 sec
    $fp = fsockopen($host, 80, $errno, $errstr, 30);
 
    if ($fp){
 
        // send the request headers:
        fputs($fp, "POST $path HTTP/1.1\r\n");
        fputs($fp, "Host: $host\r\n");
 
        if ($referer != '')
            fputs($fp, "Referer: $referer\r\n");
 
        fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
        fputs($fp, "Content-length: ". strlen($data) ."\r\n");
        fputs($fp, "Connection: close\r\n\r\n");
        fputs($fp, $data);
 
        $result = ''; 
        while(!feof($fp)) {
            // receive the results of the request
            $result .= fgets($fp, 128);
        }
    }
    else { 
        return array(
            'status' => 'err', 
            'error' => "$errstr ($errno)"
        );
    }
 
    // close the socket connection:
    fclose($fp);
 
    // split the result header from the content
    $result = explode("\r\n\r\n", $result, 2);
 
    $header = isset($result[0]) ? $result[0] : '';
    $content = isset($result[1]) ? $result[1] : '';
 
    // return as structured array:
    return array(
        'status' => 'ok',
        'header' => $header,
        'content' => $content
    );
}
$data=array("FreeTextLocation" => $address);
$ret=post_request("http://m.accuweather.com/en/search-locations", $data);
$arr=explode('"', $ret["content"]);
if ($debug){
echo $arr[1];
}
if ($arr[1]==1){
echo "<h2>Error</h2>Reverse geocoding failed";
die;
}
if (!$debug){
header("location: http://m.accuweather.com" . $arr[1]);
}

?>