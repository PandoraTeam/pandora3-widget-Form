<?php
namespace Pandora3\Widgets\Form\Sanitizers;
use Pandora3\Widgets\Form\Interfaces\SanitizerInterface;

/**
 * Class SanitizerBoolean
 * @package Pandora3\Widgets\Form\Sanitizers
 */
class SanitizerBoolean implements SanitizerInterface {
	
	/**
	 * @param string|null $value
	 * @param array $arguments
	 * @return bool
	 */
	public static function sanitize($value, array $arguments = []): bool {
		return (bool) $value;
	}

}