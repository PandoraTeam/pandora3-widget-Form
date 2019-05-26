<?php
namespace Pandora3\Widgets\Form\Interfaces;

/**
 * Interface SanitizerInterface
 * @package Pandora3\Widgets\Form\Interfaces
 */
interface SanitizerInterface {

	/**
	 * @param mixed $value
	 * @param array $arguments
	 * @return mixed
	 */
	static function sanitize($value, array $arguments = []);

}