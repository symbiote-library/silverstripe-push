<?php
/**
 * Handles sending push notifications.
 */
class PushNotificationsAdminItemRequest extends GridFieldDetailForm_ItemRequest {

	public function doSend($data, $form) {
		try {
			 $this->record->doSend();
		} catch(PushException $ex) {
			return new SS_HTTPResponse(
				$this->ItemEditForm()->forAjaxTemplate(), 400, $ex->getMessage()
			);
		}

		$response = new SS_HTTPResponse($this->ItemEditForm()->forAjaxTemplate());
		$response->setStatusDescription(_t(
			'Push.PUSHSENT', 'The push notification has been sent'
		));
		return $response;
	}

}
