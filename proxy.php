<?php
require_once("lib/Cache/Lite.php");

/* Function to show error and exit */
function error($msg) {
	header("HTTP/1.0 404 Not Found");
	echo $msg;
	exit;
}

/* Function to generate the key to store the timestamp of URL */
function timestamp_url_key($url) {
	return $url . "<TIMESTAMP>";
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


/* 1. Check the URL to retrieve */
if( FALSE == isset($_GET["dst"]) ) {
	error("GET parameter dst is not set.");
}
$url = $_GET["dst"];


/* 2. Add http:// to URL */
if(strpos($url, "http://") === 0 || strpos($url, "https://") === 0) {
} else {
	$url = "http://" . $url;
}


/* 3. Check the timestamp */
$headers = get_headers($url, 1);
$ts_remote = null;
if($headers) {
	if( array_key_exists('Last-Modified', $headers) ) {
		// most of web sites
		$ts_remote = strtotime($headers['Last-Modified']);
	} else if( array_key_exists('etag', $headers) ) {
		// dl.dropboxusercontent.com
		$ts_remote = $headers['etag'];
	} else if( array_key_exists('ETag', $headers) ) {
		$ts_remote = $headers['ETag'];
	}
}
header("X-Timestamp-Remote: $ts_remote");


/* 4. Init the cache */
$options = array(
	'lifeTime' => null,
	'pearErrorMode' => CACHE_LITE_ERROR_DIE
);
$cache = new Cache_Lite($options);


/* 5. Check if cached or not */
if($ts_remote != null) {
	$ts_local = $cache->get(timestamp_url_key($url));
	if($ts_local) {
		header("X-Timestamp-Local: $ts_local");
		if($ts_remote == $ts_local) {
			header("X-Cache-Used: Yes");
			echo $cache->get($url);
			exit;
		}
	}
}


/* 6. Retrieve the remote data */
$ret = file_get_contents($url, FALSE);
if( FALSE === $ret ) {
        error("Cannot get $url .");
}


/* 7. Cache */
if( $ts_remote ) {
	header("X-Cache-Updated: Yes");
	$cache->save($ret, $url);
	$cache->save($ts_remote, timestamp_url_key($url));
}


/* 8. Show */
header("X-Cache-Used: No");
echo $ret;

?>
