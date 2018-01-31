<?php

/**
 * ----------------------------------------
 * @title PHP-LG-SmartTV-Pre2012
 * @desc LG SmartTV Pre 2012 API
 * @author Daniel Sousa
 * @author Steve Winfield
 * ----------------------------------------
 * This code was adapted from PHP-LG-SmartTV by Steve Winfield
 * to work with older LG Smart TVs that use the HDCP protocol.
 * https://github.com/SteveWinfield/PHP-LG-SmartTV
**/

if (!extension_loaded('curl')) {
	die ('You have to install/enable curl in order to use this application.');
}

/**
 * Commands List
**/
define ('TV_CMD_CHANNEL_UP', 0);
define ('TV_CMD_CHANNEL_DOWN', 1);
define ('TV_CMD_VOLUME_UP', 2);
define ('TV_CMD_VOLUME_DOWN', 3);
define ('TV_CMD_RIGHT', 6);
define ('TV_CMD_LEFT', 7);
define ('TV_CMD_POWER', 8);
define ('TV_CMD_MUTE_TOGGLE', 9);
define ('TV_CMD_AUDIO_LANGUAGE', 10);
define ('TV_CMD_INPUT',11);
define ('TV_CMD_SLEEP_TIMER',14);
define ('TV_CMD_TV_RADIO',15);
define ('TV_CMD_NUMBER_0', 16);
define ('TV_CMD_NUMBER_1', 17);
define ('TV_CMD_NUMBER_2', 18);
define ('TV_CMD_NUMBER_3', 19);
define ('TV_CMD_NUMBER_4', 20);
define ('TV_CMD_NUMBER_5', 21);
define ('TV_CMD_NUMBER_6', 22);
define ('TV_CMD_NUMBER_7', 23);
define ('TV_CMD_NUMBER_8', 24);
define ('TV_CMD_NUMBER_9', 25);
define ('TV_CMD_PREVIOUS_CHANNEL', 26);
define ('TV_CMD_FAVORITES', 30);
define ('TV_CMD_TELETEXT', 32);
define ('TV_CMD_TELETEXT_OPTION', 33);
define ('TV_CMD_INFO_BAR', 35);
define ('TV_CMD_BACK', 40);
define ('TV_CMD_AV_MODE', 48);
define ('TV_CMD_SUBTITLE', 57);
define ('TV_CMD_UP', 64);
define ('TV_CMD_DOWN', 65);
define ('TV_CMD_HOME_MENU', 67);
define ('TV_CMD_OK', 68);
define ('TV_CMD_QUICK_MENU', 69);
define ('TV_CMD_DASH', 76);
define ('TV_CMD_PICTURE_MODE', 77);
define ('TV_CMD_SOUND_MODE', 82);
define ('TV_CMD_CHANNEL_LIST', 83);
define ('TV_CMD_PREMIUM', 89);
define ('TV_CMD_INPUT_AV1', 90);
define ('TV_CMD_EXIT', 91);
define ('TV_CMD_BLUE', 97);
define ('TV_CMD_YELLOW', 99);
define ('TV_CMD_GREEN', 113);
define ('TV_CMD_RED', 114);
define ('TV_CMD_ASPECT_RATIO_4_3', 118);
define ('TV_CMD_ASPECT_RATIO_16_9', 119);
define ('TV_CMD_ASPECT_RATIO', 121);
define ('TV_CMD_SIMPLINK', 126);
define ('TV_CMD_FAST_FORWARD', 142);
define ('TV_CMD_REWIND', 143);
define ('TV_CMD_AUDIO_DESCRIPTION', 145);
define ('TV_CMD_ENERGY_SAVING', 149);
define ('TV_CMD_INPUT_QUICK',152);
define ('TV_CMD_LIVE_TV', 158);
define ('TV_CMD_EPG', 169);
define ('TV_CMD_INFO', 170);
define ('TV_CMD_ZOOM_CINEMA', 175);
define ('TV_CMD_PLAY', 176);
define ('TV_CMD_STOP', 177);
define ('TV_CMD_PAUSE', 186);
define ('TV_CMD_RECORD', 189);
define ('TV_CMD_INPUT_COMPONENT', 191);
define ('TV_CMD_INPUT_HDMI', 198);
define ('TV_CMD_INPUT_USB', 200);
define ('TV_CMD_INPUT_HDMI2', 204);
define ('TV_CMD_INPUT_HDMI1', 206);
define ('TV_CMD_HOTEL_MENU', 207);
define ('TV_CMD_INPUT_AV2', 208);
define ('TV_CMD_INPUT_AV3', 209);
define ('TV_CMD_INPUT_RGB', 213);
define ('TV_CMD_INPUT_HDMI4', 218);
define ('TV_CMD_3D_VIDEO', 220);
define ('TV_CMD_INPUT_HDMI3', 233);

/**
 * Query Commands List
**/
define ('TV_INFO_MODEL','model_info');
define ('TV_INFO_CURRENT_CHANNEL', 'cur_channel');
define ('TV_INFO_CONTEXT_UI', 'context_ui');


class SmartTV {
	public function __construct($ipAddress, $port = 8080) {
		$this->connectionDetails = array($ipAddress, $port);
	}
	
	public function setPairingKey($pk) {
		$this->pairingKey = $pk;
	}
	
	public function displayPairingKey() {
		$this->sendXMLRequest('/hdcp/api/auth', self::encodeData(
			array('type' => 'AuthKeyReq'), 'auth'
		));
	}
	
	public function setSession($sess) {
		$this->session = $sess;
	}
	
	public function authenticate() {
		if ($this->pairingKey === null) {
			throw new Exception('No pairing key given.');
		}
		return ($this->session = $this->sendXMLRequest('/hdcp/api/auth', self::encodeData(
			array(
				'type' => 'AuthReq',
				'value' => $this->pairingKey
			),
			'auth'
		))['session']);
	}

	public function processCommand($commandName, $parameters = []) {
		if ($this->session === null) {
			throw new Exception('No session id given.');
		}
		if (is_numeric($commandName) && count($parameters) < 1) {
			$parameters['value'] = $commandName;
			$commandName = 'HandleKeyInput';
		}
		if (is_string($parameters) || is_numeric($parameters)) {
			$parameters = array('value' => $parameters);
		} elseif (is_object($parameters)) {
			$parameters = (array)$parameters;
		}
		$parameters['name'] = $commandName;
		$parameters['session'] = $this->session;
		return ($this->sendXMLRequest('/hdcp/api/dtv_wifirc', 
			self::encodeData($parameters, 'command')
		));
	}
	
	public function queryData($targetId) {
		if ($this->session === null) {
			throw new Exception('No session id given.');
		}
		$var = $this->sendXMLRequest('/hdcp/api/data?target='.$targetId . '&session=' . $this->session);
		return isset($var['data']) ? $var['data'] : $var;
	}
	
	private function sendXMLRequest($actionFile, $data = '') {
		curl_setopt(($ch = curl_init()), CURLOPT_URL, $this->connectionDetails[0] . ':' . $this->connectionDetails[1] . $actionFile);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/atom+xml',
			'Connection: Keep-Alive'
		));
		if (strlen($data) > 0) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$envar   = curl_exec($ch);
		$execute = (array)@simplexml_load_string($envar);
		if (isset($execute['ROAPError']) && $execute['ROAPError'] != '200') {
			throw new Exception('Error (' . $execute['ROAPError'] . '): ' . $execute['ROAPErrorDetail']);
		}
		return count($execute) < 2 ? $envar : $execute;
	}
	
	private static function encodeData($data, $actionType, $xml=null) {
		if ($xml == null) {
			$xml = simplexml_load_string("<!--?xml version=\"1.0\" encoding=\"utf-8\"?--><".$actionType." />");
		}
		foreach($data as $key => $value) {
			if (is_array($value))  {
				$node = $xml->addChild($key);
				self::encodeData($value, $actionType, $node);
			} else  {
				$xml->addChild($key, htmlentities($value));
			}
		}
		return $xml->asXML();
	}
	
	private $connectionDetails;
	private $pairingKey;
	private $session;
}
