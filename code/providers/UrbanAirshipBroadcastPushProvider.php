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
		$data = Convert::array2json(array(
			'aps' => array('alert' => $notification->Content)
		));

		$srv = new RestfulService(self::BROADCAST_URL);
		$srv->basicAuth(self::$api_key, self::$api_secret);

		$resp = $srv->request(null, 'POST', $data, array(
			'Content-Type' => 'application/json'
		));

		if($resp->isError()) {
			throw new PushException($resp->getBody(), $resp->getStatusCode());
		}
	}

}
