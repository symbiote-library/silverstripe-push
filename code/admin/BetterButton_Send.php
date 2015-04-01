<?php

if (class_exists('BetterButton')) {
/**
 * 
 *
 * @author <marcus@silverstripe.com.au>
 * @license BSD License http://www.silverstripe.org/bsd-license
 */
class BetterButton_Send extends BetterButton {
	/**
     * Builds the button
     */
    public function __construct() {
        return parent::__construct('doSend', _t('Push.DO_SEND','Send'));
    }


    /**
     * Determines if the button should display
     * @return boolean
     */
    public function shouldDisplay() {
        $record = $this->gridFieldRequest->record;
        return $record->canEdit() && $record instanceof PushNotification;
    }
}
	
}
