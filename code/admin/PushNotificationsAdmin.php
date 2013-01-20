<?php
/**
 * @package silverstripe-push
 */
class PushNotificationsAdmin extends ModelAdmin {

	public static $menu_title  = 'Push';
	public static $url_segment = 'push';

	public static $managed_models = array(
		'PushNotification' => array(
			'title'             => 'Push Notifications',
			'record_controller' => 'PushNotificationsAdminRecordController'
		)
	);

	public static $model_importers = array();

	public function init() {
		parent::init();
		Requirements::javascript('push/javascript/PushNotificationsAdmin.js');
	}

	public function getEditForm($id = null, $fields = null) {
		$form = parent::getEditForm($id, $fields);

		$name = $this->sanitiseClassName($this->modelClass);
		$conf = $form->Fields()->dataFieldByName($name)->getConfig();

		$conf->getComponentByType('GridFieldDetailForm')
			->setItemRequestClass('PushNotificationsAdminItemRequest')
			->setItemEditFormCallback(function($form, $component) {
				$record = $form->getRecord();

				if($record && $record->ID && !$record->Sent) {
					$form->Actions()->push(
						FormAction::create('doSend', 'Send')
							->addExtraClass('ss-ui-action')
							->setUseButtonTag(true)
					);
				}
			});

		return $form;
	}

}
