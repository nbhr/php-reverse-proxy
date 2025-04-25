<?php
/* Function to show error and exit */
function error($msg) {
	header("HTTP/1.0 404 Not Found");
	echo $msg;
	exit;
}

function my_parse_str($str) {
	# Based on proper_parse_str() in https://www.php.net/manual/en/function.parse-str.php#76792
	#
	# In case of duplicate fields, PHP $_GET and parse_str() return the last one.
	# This function returns the first one.

	# result array
	$arr = array();

	# split on outer delimiter
	$pairs = explode('&', $str);

	# loop through each pair
	foreach ($pairs as $i) {
		# split into name and value
		list($name,$value) = explode('=', $i, 2);

		# if name already exists, ignore it
		if( isset($arr[$name]) ) {
		}
		# otherwise, simply stick it in a scalar
		else {
			$arr[$name] = $value;
		}
	}

	# return result array
	return $arr;
}

/* # 0. Configure the proxy */
/*
stream_context_set_default(
	array("http" => array(
			"proxy" => "tcp://your.proxy.com:8080",
			"request_fulluri" => TRUE,
			),
	)
);
*/


/* Hard-coded target host */
$url = "http://www.example.com/";

/* 1. Check the URL to retrieve */
if( isset( $url ) ) {
	$url = $url . $_SERVER['QUERY_STRING'];
} else {
	$my_GET = my_parse_str($_SERVER['QUERY_STRING']);
	if( FALSE == isset($my_GET["dst"]) ) {
		error("GET parameter dst is not set.");
	} else {
		$url = $my_GET["dst"];
	}
}


/* 2. Add http:// to URL */
if(strpos($url, "http://") === 0 || strpos($url, "https://") === 0) {
} else {
	$url = "http://" . $url;
}


/* 6. Retrieve the remote data */
$ret = file_get_contents($url, FALSE);
if( FALSE === $ret ) {
        error("Cannot get $url .");
}

/* 7. Set response_header to received header */ 
foreach ($http_response_header as $header) {
	header($header);
}

/* 8. Show */
echo $ret;

?>
