<?php
/**
 * @package silverstripe-push
 */
abstract class PushNotificationProvider {

	protected $settings = array();
	protected $field;

	/**
	 * @return string
	 */
	abstract public function getTitle();

	/**
	 * @param PushNotification $notification the notification to send
	 * @throws PushException if sending the notification fails
	 */
	abstract public function sendPushNotification(PushNotification $notification);

	/**
	 * @return array
	 */
	public function getSettings() {
		return $this->settings;
	}

	/**
	 * Populates this provider's settings from an array of data, usually
	 * received in a request.
	 *
	 * @param array $data
	 */
	public function setSettings(array $data) {
	}

	public function getSetting($key) {
		if(array_key_exists($key, $this->settings)) {
			return $this->settings[$key];
		}
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function setSetting($key, $value) {
		$this->settings[$key] = $value;
	}

	/**
	 * Returns a list of form fields used for populating the custom settings.
	 *
	 * @param  PushProviderField $field
	 * @return FieldList
	 */
	public function getSettingsFields() {
		return new FieldList();
	}

	/**
	 * Validates if the currently set settings are valid.
	 *
	 * @return ValidationResult
	 */
	public function validateSettings() {
		return new ValidationResult();
	}

	/**
	 * @return PushProviderField
	 */
	public function getFormField() {
		return $this->field;
	}

	public function setFormField(PushProviderField $field) {
		$this->field = $field;
	}

	/**
	 * @param  string $setting
	 * @return string
	 */
	protected function getSettingFieldName($setting) {
		return sprintf('%s[Settings][%s]', $this->field->getName(), $setting);
	}

}
