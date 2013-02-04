<?php

use Guzzle\Http\Client;
use Guzzle\Http\Exception\RequestException;
use Guzzle\Http\Message\RequestInterface;

/**
 * An Urban Airship broadcast api push notification provider.
 *
 * @package silverstripe-push
 */
class UrbanAirshipBroadcastPushProvider extends PushNotificationProvider {

	const ANDROID    = 'android';
	const BLACKBERRY = 'blackberry';
	const IOS        = 'ios';
	const MPNS       = 'mpns';
	const WNS        = 'wns';

	const V1_API_URL = 'https://go.urbanairship.com/api/push';
	const V2_API_URL = 'https://device-api.urbanairship.com/2/push';

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
		$app     = $this->getSetting('App');
		$devices = array_filter(explode(',', $this->getSetting('Devices')), 'strlen');

		if(!$app) {
			throw new PushException('No application was selected.');
		}

		if(!isset(self::$applications[$app])) {
			throw new PushException(sprintf('No settings were provided for application "%s"', $app));
		}

		if(!$devices) {
			throw new PushException('At least one device type must be selected to send to.');
		}
		
		$user = self::$applications[$app]['key'];
		$pass = self::$applications[$app]['secret'];
		
		// Use the V1 API for sending to Android, Blackberry and iOS.
		if(array_intersect($devices, array(self::ANDROID, self::BLACKBERRY, self::IOS))) {
			$client = $this->getClient(self::V1_API_URL, $user, $pass);

			$client->setMethod('POST');
			$client->setHeaders('Content-Type: application/json');

			$body = array();

			if(in_array(self::ANDROID, $devices)) {
				$body['android'] = array('alert' => $notification->Content);
			}

			if(in_array(self::BLACKBERRY, $devices)) {
				$body['blackberry'] = array(
					'content-type' => 'text/plain',
					'body'         => $notification->Content
				);
			}

			if(in_array(self::IOS, $devices)) {
				$body['aps'] = array(
					'badge' => $this->getSetting('Badge') == 'inc' ? '+1' : $this->getSetting('Badge'),
					'alert' => $notification->Content,
					'sound' => $this->getSetting('Sound'),
				);
			}

			$raw = Convert::raw2json($body);
			$client->setRawData($raw);

			$response = $client->request();
			if ($response->isError()) {
				throw new PushException($response->getBody(), $response->getStatus());
			}
		}

		// Use the V2 API for sending to Windows.
		if(array_intersect($devices, array(self::MPNS, self::WNS))) {
			$client = $this->getClient(self::V2_API_URL, $user, $pass);

			$client->setMethod('POST');
			$client->setHeaders('Content-Type: application/json');
			
			$types  = array();

			if(in_array(self::MPNS, $devices)) $types[] = 'mpns';
			if(in_array(self::WNS, $devices))  $types[] = 'wns';

			$body = array(
				'notification' => array('alert' => $notification->Content),
				'device_types' => $types
			);

			$raw = Convert::raw2json($body);
			$client->setRawData($raw);

			$response = $client->request();
			if ($response->isError()) {
				throw new PushException($response->getBody(), $response->getStatus());
			}
		}
	}
	
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

		if(isset($data['Devices'])) {
			if(is_array($data['Devices'])) {
				$this->setSetting('Devices', implode(',', $data['Devices']));
			} else {
				$this->setSetting('Devices', $data['Devices']);
			}
		}

		$this->setSetting('App', isset($data['App']) ? (string) $data['App'] : null);
		$this->setSetting('Sound', isset($data['Sound']) ? (string) $data['Sound'] : null);
		$this->setSetting('Badge', isset($data['Badge']) ? (string) $data['Badge'] : null);
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

		$fields = new FieldList(
			$app = new DropdownField(
				$this->getSettingFieldName('App'),
				_t('Push.UA_APP', 'Urban Airship Application'),
				$applications,
				$this->getSetting('App')
			),
			new CheckboxSetField(
				$this->getSettingFieldName('Devices'),
				_t('Push.DEVICES', 'Devices'),
				array(
					self::ANDROID    => 'Android',
					self::BLACKBERRY => 'Blackberry',
					self::IOS        => 'iOS',
					self::MPNS       => 'Windows Phone',
					self::WNS        => 'Windows 8'
				),
				$this->getSetting('Devices')
			),
			new LiteralField('', sprintf('<p>%s</p>', _t(
				'Push.UARECIPIENTSUPPORT', 'The Urban Airship provider does ' .
				'not support selecting recipients - the push notification ' .
				'will be sent to all devices using the broadcast API.'
			))),
			new HeaderField('IosOptionsHeader', _t('Push.IOSOPTIONS', 'iOS Options')),
			new DropdownField(
				$this->getSettingFieldName('Sound'),
				_t('Push.UA_SOUND', 'Trigger sound when alert is received?'),
				array('No', 'Yes'),
				$this->getSetting('Sound')
			),
			new DropdownField(
				$this->getSettingFieldName('Badge'),
				_t('Push.UA_BADGE', 'Badge'),
				$badges,
				$this->getSetting('Badge')
			)
		);

		$app->setHasEmptyDefault(true);

		return $fields;
	}
}
