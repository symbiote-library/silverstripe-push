<?php
/**
 * A registry of all provider classes that are available.
 *
 * @package silverstripe-push
 */
class PushProvidersRegistry {

	private static $inst;

	protected $providers = array();

	/**
	 * @return PushProvidersRegistry
	 */
	public static function inst() {
		return self::$inst ? self::$inst : self::$inst = new self();
	}

	/**
	 * @param string $class
	 */
	public function add($class) {
		if(!is_subclass_of($class, 'PushNotificationProvider')) {
			throw new Exception('Provider classes must be subclasses of PushNotificationProvider.');
		}

		if($this->has($class)) {
			throw new Exception("The provider '$class' already exists.");
		}

		$this->providers[] = $class;
	}

	/**
	 * @param  string $class
	 * @return bool
	 */
	public function has($class) {
		return in_array($class, $this->providers);
	}

	/**
	 * @param string $class
	 */
	public function remove($class) {
		if($key = array_search($class, $this->providers)) {
			unset($this->providers[$key]);
		}
	}

	/**
	 * @return array
	 */
	public function getProviders() {
		return $this->providers;
	}

}
