<?php
namespace KungFoo\Routing;
use KungFoo\Helpers\ServiceLocator;

class Request
{
	public $method;

	protected $path = '';
	private $container;

	/**
	 * constructor receives the current path, as it has been interpreted from the higher app layer
	 *
	 * @param string         $path api/something/nice
	 * @param ServiceLocator $container
	 */
	public function __construct($path, ServiceLocator $container) {
		$this->path = explode('/',$path);
		$this->container = $container;

		$this->method    = strtolower($_SERVER['REQUEST_METHOD']);
	}

	/**
	 * allow read access to the path
	 * @return array the path split by '/'
	 */
	public function getPath() {
		return $this->path;
	}

}
