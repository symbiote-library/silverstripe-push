<?php
/**
 * @package silverstripe-push
 */
class PushNotification extends DataObject {

	public static $db = array(
		'Title'            => 'Varchar(100)',
		'Content'          => 'Text',
		'ProviderClass'    => 'Varchar(50)',
		'ProviderSettings' => 'Text',
		'Sent'             => 'Boolean',
		'SentAt'           => 'SS_Datetime'
	);

	public static $many_many = array(
		'RecipientMembers' => 'Member',
		'RecipientGroups'  => 'Group'
	);

	public static $summary_fields = array(
		'Title',
		'Sent'
	);

	public static $searchable_fields = array(
		'Title',
		'Content',
		'Sent'
	);

	protected $providerInst;

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->removeByName('ProviderClass');
		$fields->removeByName('ProviderSettings');
		$fields->removeByName('Sent');
		$fields->removeByName('SentAt');
		$fields->removeByName('RecipientMembers');
		$fields->removeByName('RecipientGroups');

		if($this->Sent) {
			$sentAt = _t('Push.SENTAT', 'This notification was sent at %s.');
			$sentAt = sprintf($sentAt, $this->obj('SentAt')->Nice());

			$fields->insertAfter(new LiteralField('SentAtMessage', "<p>$sentAt</p>"), 'Content');
		}

		$fields->addFieldsToTab('Root.Main', array(
			new TreeMultiselectField(
				'RecipientMembers',
				_t('Push.RECIPIENTMEMBERS', 'Recipient Members'),
				'Member'),
			new TreeMultiselectField(
				'RecipientGroups',
				_t('Push.RECIPIENTGROUPS', 'Recipient Groups'),
				'Group'),
			new PushProviderField(
				'Provider',
				_t('Push.PROVIDER', 'Provider'),
				PushProvidersRegistry::inst())
		));

		return $fields;
	}

	public function getValidator() {
		return new RequiredFields('Title');
	}

	public function canEdit($member = null) {
		return !$this->Sent && parent::canEdit($member);
	}

	/**
	 * @return PushNotificationProvider
	 */
	public function getProvider() {
		if($this->providerInst) {
			return $this->providerInst;
		}

		$class    = $this->ProviderClass;
		$settings = $this->ProviderSettings;

		if($class) {
			if(!is_subclass_of($class, 'PushNotificationProvider')) {
				throw new Exception("An invalid provider class $class was encountered.");
			}

			$this->providerInst = new $class;
			if($settings) $this->providerInst->setSettings(unserialize($settings));

			return $this->providerInst;
		}
	}

	public function setProvider(PushNotificationProvider $provider) {
		if($provider) {
			$this->providerInst     = $provider;
			$this->ProviderClass    = get_class($provider);
			$this->ProviderSettings = serialize($provider->getSettings());
		} else {
			$this->providerInst     = null;
			$this->ProviderClass    = null;
			$this->ProviderSettings = null;
		}
	}

	/**
	 * Returns all member recipient objects.
	 *
	 * @return DataObjectSet
	 */
	public function getRecipients() {
		$set = new DataObjectSet();
		$set->merge($this->RecipientMembers());

		foreach($this->RecipientGroups() as $group) {
			$set->merge($group->Members());
		}

		$set->removeDuplicates();
		return $set;
	}

	/**
	 * Sends the push notification then locks this record so it cannot be sent
	 * again.
	 *
	 * @throws PushException
	 */
	public function doSend() {
		$provider = $this->getProvider();

		if($this->Sent) {
			throw new PushException('This notification has already been sent.');
		}

		if(!$provider) {
			throw new PushException('No push notification provider has been set.');
		}

		$provider->sendPushNotification($this);

		$this->Sent   = true;
		$this->SentAt = date('Y-m-d H:i:s');
		$this->write();
	}

}
