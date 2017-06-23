> ## **IMPORTANT**

> This module is no longer actively maintained, however, if you're interested in adopting it, please let us know!

# SilverStripe Push Notifications Module


## Maintainer Contacts

* Andrew Short (<andrew@symbiote.com.au>)
* Marcus Nyeholt (<marcus@symbiote.com.au>)

This module developed with the support of [BERZERK\* interactive](http://www.berzerk.co.nz/)

## Requirements

* SilverStripe 3.1+

## Installation

* Place module in project-root/push
* Run dev/build
* Add Urban Airship configuration to your project's config.php

```

UrbanAirshipBroadcastPushProvider::add_application('your_app_name', 'key', 'secret');

```

## Sending a notification

* Login to the CMS
* Navigate to the "Push" section in the left menu
* Create a new push notification
* Select the delivery channel
* Fill out details of what you're sending
* Select who to send to (ensure you save after doing so!) 
* Hit the 'Send' button

## Project Links

* [GitHub Project Page](https://github.com/symbiote-library/silverstripe-push)

