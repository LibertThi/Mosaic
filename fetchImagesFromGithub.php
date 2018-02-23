<?php
require_once 'inc/config.php';
define("USERS_REQUEST_URL", "https://api.github.com/users?per_page=100");

$opt = getopt("",["user:","password:"]);

if (isset($opt['user']) and isset($opt['password'])){
    $userpwd = $opt['user'] . ':' . $opt['password'];
}
else{
    echo "You must enter your GitHub credentials to use this script.\nUsage:\n[script] --user <GitHub username> --password <GitHub password>\n";
    exit;
}

class Fetch extends Threaded{    
    public function __construct(string $url, string $userpwd){
        $this->url = $url;
        $this->userpwd = $userpwd;
    }

    private function getUsersInfo($url){
        // initiate curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // ONLY IN DEV ENVIRONMENT
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, $this->userpwd);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($ch, CURLOPT_HEADER, 0);
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

    public function run(){
        $json = $this->getUsersInfo($this->url);
        $usersArray = json_decode($json);
        foreach ($usersArray as $user){
            $id = $user->id;
            $avatarUrl = $user->avatar_url;
            $type = exif_imagetype($avatarUrl);
            switch ($type){
                case IMAGETYPE_GIF:
                    $ext = 'gif';
                    break;
                case IMAGETYPE_JPEG:
                    $ext = 'jpg';
                    break;                
                case IMAGETYPE_BMP:
                    $ext = 'bmp';
                    break;
                case IMAGETYPE_PNG:
                default:
                    $ext = 'png';
                    break;
            }
            $localImgPath = $imageDir . "\\$id.$ext";
            if (!file_exists($localImgPath)){
                copy($avatarUrl, $localImgPath);
                echo "Copied $id\n";
            }
            else{
                echo "Skipped $id : already exists\n";
            }
        }
    }
}
// Create directory if needed
if (!file_exists($imageDir)){
	mkdir($imageDir);
}

// Create a pool of workers
$pool = new Pool(50);

// Start at user 0
$i = 0;
// Fetch until set limit or disk 90% full
while(($i < $fetchLimit) and 
(round(disk_free_space($imageDir) / disk_total_space($imageDir) * 100) > 10)){
    $nextUrl = USERS_REQUEST_URL . "&since=$i";
    $pool->submit(new Fetch($nextUrl, $userpwd));
    $i += 100;
    while ($pool->collect());
}
$pool->shutdown();
?>