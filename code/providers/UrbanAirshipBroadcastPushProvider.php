<?php
/**
 * An Urban Airship broadcast api push notification provider.
 *
 * @package silverstripe-push
 */
class UrbanAirshipBroadcastPushProvider extends PushNotificationProvider {

	const BROADCAST_URL = 'https://go.urbanairship.com/api/push/broadcast';

	public static $api_key;
	public static $api_secret;

	public function getTitle() {
		return _t('Push.URBANAIRSHIPBROADCAST', 'Urban Airship Broadcast');
	}
	
	public function sendPushNotification(PushNotification $notification) {
		
		$client = $this->getClient(self::BROADCAST_URL, self::$api_key, self::$api_secret);
		
		$client->setMethod('POST');
		$client->setHeaders('Content-Type: application/json');
		
		$data = array(
			'aps'	=> array(
				'badge'		=> 'auto',
				'alert'		=> $notification->Content
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
			throw new PushException($resp->getBody(), $resp->getStatusCode());
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
}
