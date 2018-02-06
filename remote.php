<?php
	define("TV_IP","192.168.0.75");
	define("TV_PORT","8080");
	
	require 'smartTV.php';
	
	$tv = new SmartTV(TV_IP,TV_PORT);
	
	if(isset($_POST['cleanPairingKey'])){
		setcookie('pairingKey', null, -1);
		header('Location: '.$_SERVER['PHP_SELF']);
		die;
	}
	
	//Is this a POST? If yes, send the key using the library.
	if(isset($_POST['key']) && isset($_COOKIE['pairingKey'])){
		//Set the pairing key that the TV shows
		$tv->setPairingKey($_COOKIE['pairingKey']);

		//Try to authenticate to the TV
		try {
			$tv->authenticate();
		} catch (Exception $e) {
			die('Authentication failed, I am sorry.');
		}
		$tv->processCommand($_POST['key']);
	}elseif (isset($_POST['pairingKey'])){
		setcookie("pairingKey",$_POST['pairingKey'],time() + (10 * 365 * 24 * 60 * 60));
		$_COOKIE['pairingKey'] = $_POST['pairingKey'];
	}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Remote Control Sample</title>
		<script type="text/javascript" src="js/jquery-2.2.4.min.js"></script>
		<script type="text/javascript" src="js/jquery.maphilight-1.3.1.min.js"></script>
		<!--https://github.com/kemayo/maphilight-->
	</head>
	<body>
		<?php
		if (!isset($_COOKIE['pairingKey'])){
			$tv->displayPairingKey();
			?>
			<p>Please enter the pairing key displayed on the TV</p>
			<form action='' method='post'>
				<input type="text" value="" placeholder="Pairing Key" name="pairingKey"/>
				<input type="submit" name="submit" value="Save"/>
			</form>
		<?php
		}else{
		?>
			<img id="remoteImg" src="remote.png" usemap="#remoteImgMap">
			<map name="remoteImgMap">
			  <area shape="circle" coords="36,45,17" href="#" alt="Power" data-id="<?=SmartTV::CMD_POWER?>">
			  <area shape="circle" coords="29,103,13" href="#" alt="Energy" data-id="<?=SmartTV::CMD_ENERGY_SAVING?>">
			  <area shape="circle" coords="68,101,12" href="#" alt="AVmode" data-id="<?=SmartTV::CMD_AV_MODE?>">
			  <area shape="circle" coords="105,101,15" href="#" alt="Input" data-id="<?=SmartTV::CMD_INPUT?>">
			  <area shape="circle" coords="144,100,14" href="#" alt="TV_RADIO" data-id="<?=SmartTV::CMD_TV_RADIO?>">
			  <area shape="rect" coords="15,138,57,166" href="#" alt="Number 1" data-id="<?=SmartTV::CMD_NUMBER_1?>">
			  <area shape="rect" coords="66,138,106,166" href="#" alt="Number 2" data-id="<?=SmartTV::CMD_NUMBER_2?>">
			  <area shape="rect" coords="116,139,157,166" href="#" alt="Number 3" data-id="<?=SmartTV::CMD_NUMBER_3?>">
			  <area shape="rect" coords="15,179,56,206" href="#" alt="Number 4" data-id="<?=SmartTV::CMD_NUMBER_4?>">
			  <area shape="rect" coords="65,178,106,206" href="#" alt="Number 5" data-id="<?=SmartTV::CMD_NUMBER_5?>">
			  <area shape="rect" coords="115,177,157,207" href="#" alt="Number 6" data-id="<?=SmartTV::CMD_NUMBER_6?>">
			  <area shape="rect" coords="15,218,56,246" href="#" alt="Number 7" data-id="<?=SmartTV::CMD_NUMBER_7?>">
			  <area shape="rect" coords="65,217,107,248" href="#" alt="Number 8" data-id="<?=SmartTV::CMD_NUMBER_8?>">
			  <area shape="rect" coords="116,218,157,246" href="#" alt="Number 9" data-id="<?=SmartTV::CMD_NUMBER_9?>">
			  <area shape="rect" coords="15,258,57,288" href="#" alt="List" data-id="<?=SmartTV::CMD_CHANNEL_LIST?>">
			  <area shape="rect" coords="66,258,107,288" href="#" alt="Number 0" data-id="<?=SmartTV::CMD_NUMBER_0?>">
			  <area shape="rect" coords="115,256,158,288" href="#" alt="Quick View" data-id="<?=SmartTV::CMD_INPUT_QUICK?>">
			  <area shape="rect" coords="66,316,107,338" href="#" alt="Favorite" data-id="<?=SmartTV::CMD_FAVORITES?>">
			  <area shape="rect" coords="65,356,108,377" href="#" alt="3D" data-id="<?=SmartTV::CMD_3D_VIDEO?>">
			  <area shape="rect" coords="65,396,108,417" href="#" alt="Mute" data-id="<?=SmartTV::CMD_MUTE_TOGGLE?>">
			  <area shape="rect" coords="17,317,57,356" href="#" alt="Volume Up" data-id="<?=SmartTV::CMD_VOLUME_UP?>">
			  <area shape="rect" coords="16,379,57,418" href="#" alt="Volume Down" data-id="<?=SmartTV::CMD_VOLUME_DOWN?>">
			  <area shape="rect" coords="116,317,157,356" href="#" alt="Channel Up" data-id="<?=SmartTV::CMD_CHANNEL_UP?>">
			  <area shape="rect" coords="117,380,158,418" href="#" alt="Channel Down" data-id="<?=SmartTV::CMD_CHANNEL_DOWN?>">
			  <area shape="rect" coords="61,438,111,468" href="#" alt="Home" data-id="<?=SmartTV::CMD_HOME_MENU?>">
			  <area shape="rect" coords="14,437,61,469" href="#" alt="Premium" data-id="<?=SmartTV::CMD_PREMIUM?>">
			  <area shape="rect" coords="112,439,158,469" href="#" alt="Quick Menu" data-id="<?=SmartTV::CMD_QUICK_MENU?>">
			  <area shape="rect" coords="16,594,61,622" href="#" alt="Back" data-id="<?=SmartTV::CMD_BACK?>">
			  <area shape="rect" coords="61,596,111,623" href="#" alt="Guide" data-id="<?=SmartTV::CMD_EPG?>">
			  <area shape="rect" coords="112,595,157,621" href="#" alt="Exit" data-id="<?=SmartTV::CMD_EXIT?>">
			  <area shape="rect" coords="63,509,111,557" href="#" alt="OK" data-id="<?=SmartTV::CMD_OK?>">
			  <area shape="rect" coords="55,473,112,504" href="#" alt="Up" data-id="<?=SmartTV::CMD_UP?>">
			  <area shape="rect" coords="113,491,143,555" href="#" alt="Right" data-id="<?=SmartTV::CMD_RIGHT?>">
			  <area shape="rect" coords="26,505,56,559" href="#" alt="Left" data-id="<?=SmartTV::CMD_LEFT?>">
			  <area shape="rect" coords="61,560,112,590" href="#" alt="Down" data-id="<?=SmartTV::CMD_DOWN?>">
			  <area shape="rect" coords="16,638,45,655" href="#" alt="Red" data-id="<?=SmartTV::CMD_RED?>">
			  <area shape="rect" coords="55,639,85,656" href="#" alt="Green" data-id="<?=SmartTV::CMD_GREEN?>">
			  <area shape="rect" coords="90,639,121,655" href="#" alt="Yellow" data-id="<?=SmartTV::CMD_YELLOW?>">
			  <area shape="rect" coords="128,639,160,657" href="#" alt="Blue" data-id="<?=SmartTV::CMD_BLUE?>">
			  <area shape="rect" coords="16,676,56,691" href="#" alt="Teletext" data-id="<?=SmartTV::CMD_TELETEXT?>">
			  <area shape="rect" coords="66,674,109,694" href="#" alt="Teletext Option" data-id="<?=SmartTV::CMD_TELETEXT_OPTION?>">
			  <area shape="rect" coords="118,675,159,693" href="#" alt="Subtitle" data-id="<?=SmartTV::CMD_SUBTITLE?>">
			  <area shape="rect" coords="14,702,59,722" href="#" alt="Stop" data-id="<?=SmartTV::CMD_STOP?>">
			  <area shape="rect" coords="64,700,108,722" href="#" alt="Play" data-id="<?=SmartTV::CMD_PLAY?>">
			  <area shape="rect" coords="116,701,162,722" href="#" alt="Pause" data-id="<?=SmartTV::CMD_PAUSE?>">
			  <area shape="rect" coords="15,730,58,749" href="#" alt="Rewind" data-id="<?=SmartTV::CMD_REWIND?>">
			  <area shape="rect" coords="66,730,108,748" href="#" alt="Fast Foward" data-id="<?=SmartTV::CMD_FAST_FORWARD?>">
			  <area shape="rect" coords="117,730,160,750" href="#" alt="Ratio" data-id="<?=SmartTV::CMD_ASPECT_RATIO?>">
			  <area shape="rect" coords="16,759,56,778" href="#" alt="Info" data-id="<?=SmartTV::CMD_INFO?>">
			  <area shape="rect" coords="67,758,108,777" href="#" alt="Audio Description" data-id="<?=SmartTV::CMD_AUDIO_DESCRIPTION?>">
			</map>
			<br/>
			<form action='' method='post'>
				<input type="submit" name="cleanPairingKey" value="Forget Pairing Key"/>
			</form>
			<script type="text/javascript">
				$(function() {
					$('#remoteImg').maphilight();
				});
				
				window.onclick = function (e) {
					if (e.target.localName == 'area') {
						console.log(e.target.dataset.id);
						sendKey(e.target.dataset.id);
					}
				}
				
				function sendKey(key) {
					var http = new XMLHttpRequest();
					http.open("POST", "", true);
					http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
					http.send("key=" + key);
				}
			</script>
		<?php
		}
		?>
	</body>
</html>
