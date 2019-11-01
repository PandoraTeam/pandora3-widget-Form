<?php
namespace Pandora3\Widgets\Form\Sanitizers;

use Pandora3\Widgets\Form\Interfaces\SanitizerInterface;

/**
 * Class SanitizerLowercase
 * @package Pandora3\Widgets\Form\Sanitizers
 */
class SanitizerLowercase implements SanitizerInterface {

	/**
	 * @param string|null $value
	 * @param array $arguments
	 * @return string
	 */
	public static function sanitize($value, array $arguments = []): string {
		return $value ? mb_strtolower($value) : '';
	}

}