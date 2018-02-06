<?php

/**
 * ----------------------------------------
 * Example - PHP LG SmartTV Pre 2012 API
 * ----------------------------------------
**/
include 'smartTV.php';

//Connect to your TV (IP,Port=8080)
$tv = new SmartTV('192.168.0.75'/*,8080*/); 

//Shows the pairing key on the TV, you should run this to get
//the key to put bellow in setPairingKey
$tv->displayPairingKey();

//Set the pairing key that the TV shows
$tv->setPairingKey("DDMZVF");

//Try to authenticate to the TV
try {
	$tv->authenticate();
} catch (Exception $e) {
	die('Authentication failed, I am sorry.');
}



/*Send commands examples*/

//Send Command to Increase the Volume
$tv->processCommand(SmartTV::CMD_VOLUME_UP);

//Send Command to Decrease the Volume
$tv->processCommand(SmartTV::CMD_VOLUME_DOWN);



/*Get TV info examples*/

//Get TV Model Info
echo "\n --- Model Info ---\n";
print_r($tv->queryData(SmartTV::INFO_MODEL));
echo "------------------\n\n";

//Get the current channel info
echo "--- Current Channel Info --\n";
print_r($tv->queryData(SmartTV::INFO_CURRENT_CHANNEL));
echo "---------------------------\n";

