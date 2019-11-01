<?php
namespace Pandora3\Widgets\Form\Sanitizers;
use Pandora3\Widgets\Form\Interfaces\SanitizerInterface;

/**
 * Class SanitizerBoolean
 * @package Pandora3\Widgets\Form\Sanitizers
 */
class SanitizerTrim implements SanitizerInterface {
	
	/**
	 * @param string|null $value
	 * @param array $arguments
	 * @return string
	 */
	public static function sanitize($value, array $arguments = []): string {
		return $value ? trim($value) : '';
	}
	
}