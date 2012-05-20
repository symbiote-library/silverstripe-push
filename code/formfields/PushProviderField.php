<?php
/**
 * Allows users to select and configure the push notification provider to use.
 *
 * @package silverstripe-push
 */
class PushProviderField extends FormField {

	public static $url_handlers = array(
		'fields/$Class!' => 'fields'
	);

	public static $allowed_actions = array(
		'fields'
	);

	protected $registry;
	protected $provider;
	protected $providers = array();

	public function __construct($name, $title, PushProvidersRegistry $registry) {
		$this->registry = $registry;

		foreach($this->registry->getProviders() as $class) {
			$inst = new $class;
			$inst->setFormField($this);
			$this->providers[$class] = $inst;
		}

		parent::__construct($name, $title);
	}

	public function fields($request) {
		$class = $request->param('Class');

		if(!$this->registry->has($class)) {
			$this->httpError(404);
		}

		if($this->provider && $class == get_class($this->provider)) {
			$inst = $this->provider;
		} else {
			$inst = new $class;
			$inst->setFormField($this);
		}

		if($this->isReadonly()) {
			$fields = $inst->getSettingsFields()->makeReadonly();
		} else {
			$fields = $inst->getSettingsFields();
		}

		$data = new ArrayData(array(
			'SettingsFields' => $fields
		));
		return $data->renderWith('PushProviderField_ProviderFields');
	}

	public function validate(Validator $validator) {
		if($this->provider) {
			$result = $this->provider->validateSettings();

			if(!$result->valid()) {
				$validator->validationError($this->name, $result->message(), 'validation');
				return false;
			}
		}

		return true;
	}

	public function performReadonlyTransformation() {
		$field = clone $this;
		$field->setReadonly(true);
		return $field;
	}

	public function setValue($value) {
		if($value instanceof PushNotificationProvider) {
			$this->provider = $value;
			$this->provider->setFormField($this);
		} elseif(is_array($value)) {
			$class    = isset($value['Provider']) ? $value['Provider'] : null;
			$settings = isset($value['Settings']) ? $value['Settings'] : null;

			if($class && is_subclass_of($class, 'PushNotificationProvider')) {
				$this->provider = new $class;
				$this->provider->setFormField($this);

				if(is_array($settings)) {
					$this->provider->setSettings($settings);
				}
			} else {
				$this->provider = null;
			}
		}
	}

	public function saveInto(DataObject $record) {
		$record->{$this->name} = $this->provider;
	}

	public function FieldHolder() {
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript('push/javascript/PushProviderField.js');
		Requirements::css('push/css/PushProviderField.css');

		return $this->renderWith('PushProviderField');
	}

	public function ProviderField() {
		$values = array();

		foreach($this->providers as $class => $inst) {
			$values[$class] = $inst->getTitle();
		}

		$field = new DropdownField(
			"$this->name[Provider]",
			_t('Push.DELIVERYCHANNEL', 'Delivery Channel'),
			$values,
			$this->provider ? get_class($this->provider) : null,
			null,
			true);

		if($this->isReadonly()) {
			return $field->performReadonlyTransformation();
		} else {
			return $field;
		}
	}

	public function SettingsFields() {
		if($this->provider) {
			$fields = $this->provider->getSettingsFields();

			if($this->isReadonly()) {
				return $fields->makeReadonly();
			} else {
				return $fields;
			}
		}
	}

}
