<?php
/**
 * @package silverstripe-push
 */
class PushNotificationsAdminRecordController extends ModelAdmin_RecordController {

	public function doSend() {
		try {
			$this->currentRecord->doSend();
		} catch(Exception $ex) {
			$response = new SS_HTTPResponse();
			$response->setStatusCode(400, $ex->getMessage());
			$response->setBody($this->EditForm()->forAjaxTemplate());
			return $response;
		}

		$response = new SS_HTTPResponse();
		$response->setStatusDescription(_t(
			'Push.PUSHSENT', 'The push notification has been sent'
		));
		$response->setBody($this->EditForm()->forAjaxTemplate());
		return $response;
	}

	public function EditForm() {
		$form    = parent::EditForm();
		$actions = $form->Actions();

		if($this->currentRecord->ID && !$this->currentRecord->Sent) {
			$actions->push(new FormAction('doSend', _t('Push.SEND', 'Send')));
		}

		return $form;
	}

}
