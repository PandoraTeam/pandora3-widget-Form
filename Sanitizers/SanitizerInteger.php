<?php
namespace Pandora3\Widgets\Form\Sanitizers;

use Pandora3\Widgets\Form\Interfaces\SanitizerInterface;

/**
 * Class SanitizerInteger
 * @package Pandora3\Widgets\Form\Sanitizers
 */
class SanitizerInteger implements SanitizerInterface {
	
	/**
	 * @param string|null $value
	 * @param array $arguments
	 * @return int
	 */
	public static function sanitize($value, array $arguments = []): int {
		return (int) $value;
	}

}