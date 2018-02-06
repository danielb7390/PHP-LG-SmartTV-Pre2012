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

class SmartTV {
	/**
	 * Commands List
	**/
	const CMD_CHANNEL_UP = 0;
	const CMD_CHANNEL_DOWN = 1;
	const CMD_VOLUME_UP = 2;
	const CMD_VOLUME_DOWN = 3;
	const CMD_RIGHT = 6;
	const CMD_LEFT = 7;
	const CMD_POWER = 8;
	const CMD_MUTE_TOGGLE = 9;
	const CMD_AUDIO_LANGUAGE = 10;
	const CMD_INPUT =11;
	const CMD_SLEEP_TIMER =14;
	const CMD_TV_RADIO =15;
	const CMD_NUMBER_0 = 16;
	const CMD_NUMBER_1 = 17;
	const CMD_NUMBER_2 = 18;
	const CMD_NUMBER_3 = 19;
	const CMD_NUMBER_4 = 20;
	const CMD_NUMBER_5 = 21;
	const CMD_NUMBER_6 = 22;
	const CMD_NUMBER_7 = 23;
	const CMD_NUMBER_8 = 24;
	const CMD_NUMBER_9 = 25;
	const CMD_PREVIOUS_CHANNEL = 26;
	const CMD_FAVORITES = 30;
	const CMD_TELETEXT = 32;
	const CMD_TELETEXT_OPTION = 33;
	const CMD_INFO_BAR = 35;
	const CMD_BACK = 40;
	const CMD_AV_MODE = 48;
	const CMD_SUBTITLE = 57;
	const CMD_UP = 64;
	const CMD_DOWN = 65;
	const CMD_HOME_MENU = 67;
	const CMD_OK = 68;
	const CMD_QUICK_MENU = 69;
	const CMD_DASH = 76;
	const CMD_PICTURE_MODE = 77;
	const CMD_SOUND_MODE = 82;
	const CMD_CHANNEL_LIST = 83;
	const CMD_PREMIUM = 89;
	const CMD_INPUT_AV1 = 90;
	const CMD_EXIT = 91;
	const CMD_BLUE = 97;
	const CMD_YELLOW = 99;
	const CMD_GREEN = 113;
	const CMD_RED = 114;
	const CMD_ASPECT_RATIO_4_3 = 118;
	const CMD_ASPECT_RATIO_16_9 = 119;
	const CMD_ASPECT_RATIO = 121;
	const CMD_SIMPLINK = 126;
	const CMD_FAST_FORWARD = 142;
	const CMD_REWIND = 143;
	const CMD_AUDIO_DESCRIPTION = 145;
	const CMD_ENERGY_SAVING = 149;
	const CMD_INPUT_QUICK =152;
	const CMD_LIVE_TV = 158;
	const CMD_SLIDE_USB2 = 168;
	const CMD_EPG = 169;
	const CMD_INFO = 170;
	const CMD_ZOOM_CINEMA = 175;
	const CMD_PLAY = 176;
	const CMD_STOP = 177;
	const CMD_PAUSE = 186;
	const CMD_RECORD = 189;
	const CMD_INPUT_COMPONENT = 191;
	const CMD_INPUT_HDMI = 198;
	const CMD_INPUT_USB = 200;
	const CMD_INPUT_HDMI2 = 204;
	const CMD_INPUT_HDMI1 = 206;
	const CMD_HOTEL_MENU = 207;
	const CMD_INPUT_AV2 = 208;
	const CMD_INPUT_AV3 = 209;
	const CMD_INPUT_RGB = 213;
	const CMD_INPUT_HDMI4 = 218;
	const CMD_3D_VIDEO = 220;
	const CMD_INPUT_HDMI3 = 233;
	const CMD_SLIDE_USB1 = 238;

	/**
	 * Query Commands List
	**/
	const INFO_MODEL = 'model_info';
	const INFO_CURRENT_CHANNEL = 'cur_channel';
	const INFO_CONTEXT_UI = 'context_ui';
	const INFO_FAV_LISTS = 'fav_list';
	
	
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
