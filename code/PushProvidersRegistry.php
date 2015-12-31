<?php
/**
 * A registry of all provider classes that are available.
 *
 * @package silverstripe-push
 */
class PushProvidersRegistry
{

    public $providers = array();

    /**
     * @param  string $class
     * @return bool
     */
    public function has($class)
    {
        return in_array($class, $this->providers);
    }

    /**
     * @param string $class
     */
    public function remove($class)
    {
        if ($key = array_search($class, $this->providers)) {
            unset($this->providers[$key]);
        }
    }

    /**
     * @return array
     */
    public function getProviders()
    {
        return $this->providers;
    }
}
