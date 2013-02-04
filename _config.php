<?php
/**
 * @package silverstripe-push
 */

set_include_path(
	__DIR__ . '/thirdparty' . PATH_SEPARATOR . get_include_path()
);

PushProvidersRegistry::inst()->add('EmailPushProvider');
PushProvidersRegistry::inst()->add('UrbanAirshipBroadcastPushProvider');

LeftAndMain::require_css('push/css/push.css');

if(interface_exists('QueuedJob')) {
	Object::set_static('PushNotification', 'has_one', array(
		'SendJob' => 'QueuedJobDescriptor'
	));
}
