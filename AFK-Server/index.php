<?php
require 'Slim/Slim.php';
require_once 'db.class.php';

DB::$user = 'XXXXXXXXXXXX';
DB::$password = 'XXXXXXXXXXXX';
DB::$dbName = 'XXXXXXXXXXXX';
DB::$host = 'XXXXXXXXXXXX';
DB::$port = null;
DB::$encoding = 'utf8';

$afkToken = "XXXXXXXXXXXX";
$whereisToken = "XXXXXXXXXXXX";

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim(array(
    'debug' => true
));

$app->response->headers->set('Content-Type', 'application/json');

$app->post(
    '/ping',
    function () {
        echo 'PONG';
    }
);

$app->post(
	'/afk', 
	 function () use ($app) {
		 
	$auth = $_POST['token'];

	if ($auth != $afkToken) {
		echo "Not authorized";
		return;
	}
		 
	$team_id = $_POST['team_id'];
	$channel_id = $_POST['channel_id'];
	$channel_name = $_POST['channel_name'];
	$userID = $_POST['user_id'];
	$userName = $_POST['user_name'];
	$text = $_POST['text'];
	$textArray = explode(' ',trim($text));
		 
	$users = DB::query("SELECT * FROM Users WHERE userID = %s", $userID);
	
	if ($text == "help") {
		echo("View documentation for AFK here: http://api.cocoaapp.com/afk/afk_slackbot.html");
		return;
	}
	
	if (count($users) == 0) {
		//add user
		$app->response->setStatus(201);
	    DB::insert('Users', array('userID' => $userID, 'userName' => $userName, 'awayMessage' => $text));
	    echo $userName . ' is now away with message "' . $text . '"';
	} else {
		//update user
		if (trim($text) == "online" || trim($text) == "back" || trim($text) == "clear") {
			//clear away message
			$app->response->setStatus(201);	    
			DB::update('Users', array('userName' => $userName, 'awayMessage' => ""), "userID=%s", $userID);
			echo 'Welcome back, ' . $userName;
			
			foreach($users as $user){
				if ($user['userToken'] != "") {
					setUserAvailable($user['userToken']);
				}
			}
			
		} else if (count($textArray) == 2) {
			
			if ($textArray[0] == "register") {
				$app->response->setStatus(201);	    
				DB::update('Users', array('userName' => $userName, 'userToken' => $textArray[1]), "userID=%s", $userID);
				echo 'You\'re registered for automatic away / online status updates!';
			} else {
				$app->response->setStatus(201);	    
				DB::update('Users', array('userName' => $userName, 'awayMessage' => $text), "userID=%s", $userID);
				echo $userName . ' is now away with message "' . $text . '"';
				
				foreach($users as $user){
					if ($user['userToken'] != "") {
						setUserAway($user['userToken']);
					}
				}
			}
		} else {
			$app->response->setStatus(201);	    
			DB::update('Users', array('userName' => $userName, 'awayMessage' => $text), "userID=%s", $userID);
			echo $userName . ' is now away with message "' . $text . '"';
			
			foreach($users as $user){
				if ($user['userToken'] != "") {
					setUserAway($user['userToken']);
				}
			}
		}
	}
});

$app->post(
	'/whereis', 
	 function () use ($app) {
		 
	$auth = $_POST['token'];

	if ($auth != $whereisToken) {
		echo "Not authorized";
		return;
	}

	$team_id = $_POST['team_id'];
	$channel_id = $_POST['channel_id'];
	$channel_name = $_POST['channel_name'];
	$userID = $_POST['user_id'];
	$userName = $_POST['user_name'];
	$text = $_POST['text'];
		 
	$users = DB::query("SELECT * FROM Users WHERE userName = %s", trim($text));
	
	if (count($users) == 0) {
		//add user
		$app->response->setStatus(201);
		echo 'User ' . $text . ' is not using the AFK slash command to update their status';
	} else {
		//update user
		$app->response->setStatus(201);
		
		foreach($users as $user){

			if ($user['awayMessage'] == "") {
				echo $user['userName'] . ' is currently online and available to chat.';
			} else {
				echo $user['userName'] . ' is currently away with status: "' . $user['awayMessage'] . '" as of ' .$user['awayDate'] . ' Pacific Time';
			}
			
		}
	}
});

$app->run();
?>

<?

function setUserAway($userToken) 
{
	$url = 'https://slack.com/api/users.setPresence?token='. $userToken .'&presence=away';
	echo url_get_contents($url);
}

function setUserAvailable($userToken) 
{
	$url = 'https://slack.com/api/users.setPresence?token='. $userToken .'&presence=auto';
	url_get_contents($url);
}

/*********************************
URL DOWNLOADING METHODS
*********************************/

function url_get_contents ($Url) 
{
	if (!function_exists('curl_init')){ 
    	die('CURL is not installed!');
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $Url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$output = curl_exec_follow($ch);
	curl_close($ch);

	return $output;
}

function curl_exec_follow($ch, &$maxredirect = null) 
{
  
  // we emulate a browser here since some websites detect
  // us as a bot and don't let us do our job
  $user_agent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5)".
                " Gecko/20041107 Firefox/1.0";
  curl_setopt($ch, CURLOPT_USERAGENT, $user_agent );

  $mr = $maxredirect === null ? 5 : intval($maxredirect);

  if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) {

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $mr > 0);
    curl_setopt($ch, CURLOPT_MAXREDIRS, $mr);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

  } else {
    
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

    if ($mr > 0)
    {
      $original_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
      $newurl = $original_url;
      
      $rch = curl_copy_handle($ch);
      
      curl_setopt($rch, CURLOPT_HEADER, true);
      curl_setopt($rch, CURLOPT_NOBODY, true);
      curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
      do
      {
        curl_setopt($rch, CURLOPT_URL, $newurl);
        $header = curl_exec($rch);
        if (curl_errno($rch)) {
          $code = 0;
        } else {
          $code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
          if ($code == 301 || $code == 302) {
            preg_match('/Location:(.*?)\n/', $header, $matches);
            $newurl = trim(array_pop($matches));
            
            // if no scheme is present then the new url is a
            // relative path and thus needs some extra care
            if(!preg_match("/^https?:/i", $newurl)){
              $newurl = $original_url . $newurl;
            }   
          } else {
            $code = 0;
          }
        }
      } while ($code && --$mr);
      
      curl_close($rch);
      
      if (!$mr)
      {
        if ($maxredirect === null)
        trigger_error('Too many redirects.', E_USER_WARNING);
        else
        $maxredirect = 0;
        
        return false;
      }
      curl_setopt($ch, CURLOPT_URL, $newurl);
    }
  }
  return curl_exec($ch);
}

	
?>
