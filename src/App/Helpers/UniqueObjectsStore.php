<?php

namespace App\Helpers;

/**
 * UniqueObjectsStore will be able to store and retrieve objects
 * TODO - add persistence
 */
class UniqueObjectsStore
{
	private $objects;

	/**
	 * does the key exist?
	 * @param  string  $key
	 * @return boolean
	 */
	public function has($key) {
		return isset($this->objects[$key]);
	}

	/**
	 * stores the given key
	 * @param  string $key
	 * @return boolean
	 */
	public function store($key) {
		return ($this->objects[$key] = true);
	}
}
