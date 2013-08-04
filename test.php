<?php
function assignFriend($status){
	$daturl = "https://api.sentigem.com/external/get-sentiment?api-key=75bb2830195e0ef2af7714e30bd337df7D-3dzCLGWprRax85XusgTYAJwVH1Bb0&text=";
	
	$total = $daturl . urlencode($status);
	
	$string = get_data($total);
	$json_a = json_decode($string,true);
	
	$sentiment = $json_a['polarity'];

	return $sentiment;
}

/* gets the data from a URL */
function get_data($url) {
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt ($ch, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem");
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}


?>

<html>
<head>
	<title>Sentiments test</title>
</head>
<body>

<?php echo assignFriend("I HATE you!!!") ?>

</body>
</head>