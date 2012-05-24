<?php
/**
 * @package silverstripe-push
 */
class PushNotificationsAdmin extends ModelAdmin {

	public static $menu_title  = 'Push';
	public static $url_segment = 'push';

	public static $managed_models = array(
		'PushNotification' => array(
			'record_controller' => 'PushNotificationsAdminRecordController'
		)
	);

	public static $model_importers = array();

	public function init() {
		parent::init();
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-livequery/jquery.livequery.js');
		Requirements::javascript('push/javascript/PushNotificationsAdmin.js');
	}

}
