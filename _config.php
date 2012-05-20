<?php
/**
 * @package silverstripe-push
 */

// PushProvidersRegistry::inst()->add('EmailPushProvider');
// PushProvidersRegistry::inst()->add('UrbanAirshipBroadcastPushProvider');

if(interface_exists('QueuedJob')) {
	Object::set_static('PushNotification', 'has_one', array(
		'SendJob' => 'QueuedJobDescriptor'
	));
}
