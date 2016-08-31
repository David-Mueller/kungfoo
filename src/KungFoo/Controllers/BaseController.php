<?php
namespace KungFoo\Controllers;

abstract class BaseController
{

	/**
	 * render a template from src/templates/
	 * @param  string $template the template path and name
	 * @param  array  $data     the data that should be provided
	 * @return string 	output of the template
	 */
	protected function render($template, $data = array()) {
		// the render jail
		$render = function($template, $data = array()) {
			extract($data);
			ob_start();
			{
				require(APP_DIR.'/src/templates/' . $template . '.phtml');
			}
			return ob_get_clean();
		};

		return $render($template, $data);
	}
}