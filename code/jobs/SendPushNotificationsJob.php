<?php
/**
 * @package silverstripe-push
 */
class SendPushNotificationsJob extends AbstractQueuedJob {

	public function __construct($notification) {
		if($notification) $this->setObject($notification);
	}

	public function getTitle() {
		$message = _t('Push.SENDNOTIFICATIONSFOR', 'Send notifications for "%s"');
		return sprintf($message, $this->getObject()->Title);
	}

	public function process() {
		$this->getObject()->doSend();
		$this->isComplete = true;
	}

}
