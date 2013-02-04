<?php
/**
 * @package silverstripe-push
 */

PushProvidersRegistry::inst()->add('EmailPushProvider');
PushProvidersRegistry::inst()->add('UrbanAirshipBroadcastPushProvider');

LeftAndMain::require_css('push/css/push.css');

if(interface_exists('QueuedJob')) {
	Object::set_static('PushNotification', 'has_one', array(
		'SendJob' => 'QueuedJobDescriptor'
	));
}
