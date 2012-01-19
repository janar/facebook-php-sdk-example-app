<?php

require './config.php';
require './facebook.php';

//Create facebook application instance.
$facebook = new Facebook(array(
  'appId'  => $fb_app_id,
  'secret' => $fb_secret,
  'cookie' => true,
));

$friends = array();
$sent = false;
$userData = null;

$user = $facebook->getUser();

//redirect to facebook page
if(isset($_GET['code'])){
	if($fb_auto_post && $user){
		$which = (rand(1, count($pics)) - 1);
		$msg = array(
			'message' => 'I started using ' . $fb_app_url
		);
		$facebook->api('/me/feed', 'POST', $msg);
	}

	header("Location: " . $fb_app_url);
	exit;
}

if ($user) {
	//get user data
	try {
		$userData = $facebook->api('/me');
	} catch (FacebookApiException $e) {
		//do something about it
	}
	
	//get 5 random friends
	try {
		$friendsTmp = $facebook->api('/' . $userData['id'] . '/friends');
		shuffle($friendsTmp['data']);
		array_splice($friendsTmp['data'], 5);
		$friends = $friendsTmp['data'];
	} catch (FacebookApiException $e) {
		//do something about it
	}
	
	//post message to wall if it is sent trough form
	if(isset($_POST['mapp_message'])){
		try {
			$facebook->api('/me/feed', 'POST', array(
				'message' => $_POST['mapp_message']
			));
			$sent = true;
		} catch (FacebookApiException $e) {
			//do something about it
		}
	}

} else {
	$loginUrl = $facebook->getLoginUrl(array(
		'canvas' => 1,
		'fbconnect' => 0,
		'scope' => 'publish_stream',
	));

	if($fb_auto_redirect){
		header("Location: " . $loginUrl);
		exit;
	}
}

?>
<!DOCTYPE html 
	PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="et" lang="en">
	<head>
		<title>facebook-php-sdk example app</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<style type="text/css">
			body { font-family:Verdana,"Lucida Grande",Lucida,sans-serif; font-size: 12px}
		</style>
	</head>
	<body>
		<h1>Janar's graph-API example app using php-sdk</h1>
		
			<?php if ($user){ ?>
				<?php if ($sent){ ?>
					<p><strong>Message sent!</strong></p>
				<?php } ?>
				<form method="post" action="">
					<p><input type="text" value="Your message here..." size="60" name="mapp_message" /></p>
					<p><input type="submit" value="Send message to the wall" name="sendit" /></p>
				</form>
				<p>
					<br /><br />
					5 of your randomly picked friends:<br /><br />
					<?php foreach($friends as $k => $i){ ?>
						<strong><?php echo $i['name']; ?></strong><br />
					<?php } ?>
				</p>
			<?php } else { ?>
				<p>
				<strong><a href="<?php echo $loginUrl; ?>" target="_top">Allow this app to interact with my profile</a></strong>
				<br /><br />
				This is just a simple app for testing/demonstrating some facebook graph API calls usinf php-sdk library. After allowing this application, 
				it can be used to post messages on your wall. Also it will list 5 of your randomly picked friends.
				</p>
			<?php } ?>
			<p>
				<a href="http://eagerfish.eu/example-facebook-iframe-app-using-graph-api-through-php-sdk/"><strong>Download source and read blogpost about this</strong></a>
			</p>
			
	</body>
</html>
