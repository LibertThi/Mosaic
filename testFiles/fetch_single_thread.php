<?php
set_time_limit(60);

define("USERS_REQUEST_URL", "https://api.github.com/users?per_page=5");
define("IMG_PATH", "D:\img");
$nextUrl = USERS_REQUEST_URL;

function getUsersInfo($url){
	// initiate curl
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // ONLY IN DEV ENVIRONMENT !!!
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERPWD, "LibertThi:monmotdepassededingo");
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_HEADERFUNCTION, 'getHeaderInfo');
		
	// Execute the curl command
	try{
		$body = curl_exec($ch);
	}
	catch(CURLException $e){
		$body = 'Curl timeout';
	}
	
	// Returns the error if encountered
	if (curl_errno($ch)) {
		print 'Error:' . curl_error($ch);
		$body = null;         
	}
	// Close curl
	curl_close ($ch);
	return $body;         
}

function getHeaderInfo($curl, $header){
	$pattern = "/^Link: <(.*?)>; rel=\"next\".*$/";		
	$match = preg_match($pattern,$header,$matches);
	if ($match){
		//echo '<br>' . $matches[0];
		global $nextUrl;
		$nextUrl = $matches[1];		
	}
	else{
		$nextUrl = null;
	}
	return strlen($header);
}

//$path = getcwd() . "\\img";
if (!file_exists(IMG_PATH)){
	mkdir(IMG_PATH);
}

// fetch until we reach 90% disk space or until we have fetch everything
while($nextUrl != null and (round(disk_free_space(IMG_PATH) / disk_total_space(IMG_PATH) * 100) > 20)){
	$json = getUsersInfo($nextUrl);
	$usersArray = json_decode($json);
	foreach ($usersArray as $user){
		$id = $user->id;
		$avatarUrl = $user->avatar_url;
		$localImgPath = IMG_PATH . "\\$id.png";
		if (!file_exists($localImgPath)){
			copy($avatarUrl, $localImgPath);
			//echo $id . ' copied';
		}
	}
}

echo "Execution ended!";