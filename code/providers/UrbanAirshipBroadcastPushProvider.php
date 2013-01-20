<?php
/**
 * An Urban Airship broadcast api push notification provider.
 *
 * @package silverstripe-push
 */
class UrbanAirshipBroadcastPushProvider extends PushNotificationProvider {

	const BROADCAST_URL = 'https://go.urbanairship.com/api/push/broadcast/';
	
	public static $applications = array();

	public static function add_application($title, $key, $secret) {
		self::$applications[$title] = array(
			'key'    => $key,
			'secret' => $secret,
		);
	}
	
	public function getTitle() {
		return _t('Push.URBANAIRSHIPBROADCAST', 'Urban Airship Broadcast');
	}
	
	public function sendPushNotification(PushNotification $notification) {
		$title = $this->getSetting('App');
		if (!$title) {
			throw new PushException("No application selected. Please add configuration for a new application");
		}

		$settings = isset(self::$applications[$title]) ? self::$applications[$title] : null;
		
		if (!$settings) {
			throw new PushException("No settings provided for application " . Convert::raw2xml($title));
		}

		$client = $this->getClient(self::BROADCAST_URL, $settings['key'], $settings['secret']);

		$client->setMethod('POST');
		$client->setHeaders('Content-Type: application/json');

		$data = array(
			'aps'	=> array(
				'badge'		=> $this->getSetting('Badge') == 'inc' ? '+1' : $this->getSetting('Badge'), 
				'alert'		=> $notification->Content,
				'sound'		=> $this->getSetting('Sound'), 
			),

// TODO: When android is needed...
//			'android'	=> array(
//				'alert'		=> $message
//			)
		);

		$raw = Convert::raw2json($data);
		$client->setRawData($raw);
		
		$response = $client->request();
		if ($response->isError()) {
			throw new PushException($response->getBody(), $response->getStatus());
		}
		return true;
	}
	
	
	protected $httpClient;
	
	/**
	 * @return type Zend_Http_Client
	 */
	protected function getClient($uri, $user=null, $pass=null) {
		if (!$this->httpClient) {
			
			set_include_path(get_include_path() . PATH_SEPARATOR . Director::baseFolder() . '/push/thirdparty');
			include_once Director::baseFolder().'/push/thirdparty/Zend/Http/Client.php';

			$this->httpClient = new Zend_Http_Client(
				$uri, 
				array(
					'maxredirects' => 0,
					'timeout' => 10
				)
			);
		} else {
			$this->httpClient->setUri($uri);
		}

		$this->httpClient->resetParameters();

		if ($user) {
			$this->httpClient->setAuth($user, $pass);
		}

		return $this->httpClient;
	}
	
	public function setSettings(array $data) {
		parent::setSettings($data);

		$this->setSetting('Sound', isset($data['Sound']) ? (string) $data['Sound'] : null);
		$this->setSetting('Badge', isset($data['Badge']) ? (string) $data['Badge'] : null);
		$this->setSetting('App', isset($data['App']) ? (string) $data['App'] : null);
	}

	public function getSettingsFields() {
		$badges = array('auto' => 'Auto', 'inc' => 'Increment');

		foreach(range(1, 5) as $val) {
			$badges[$val] = $val;
		}

		$applications = array_keys(self::$applications);

		if($applications) {
			$applications = ArrayLib::valuekey($applications);
		}

		return new FieldList(
			new DropdownField(
				$this->getSettingFieldName('App'),
				_t('Push.UA_APP', 'Urban Airship Application'),
				$applications,
				$this->getSetting('App')
			),
			new DropdownField(
				$this->getSettingFieldName('Sound'),
				_t('Push.UA_SOUND', 'Trigger sound when alert is received?'),
				array('No', 'Yes'),
				$this->getSetting('Sound')
			),
			new DropdownField(
				$this->getSettingFieldName('Badge'),
				_t('Push.UA_BADGE', 'Badge value'),
				$badges,
				$this->getSetting('Badge')
			),
			new LiteralField('', sprintf('<p>%s</p>', _t(
				'Push.UARECIPIENTSUPPORT', 'The Urban Airship provider does ' .
				'not support selecting recipients - the push notification ' .
				'will be sent to all devices using the broadcast API.'
			)))
		);
	}
}
