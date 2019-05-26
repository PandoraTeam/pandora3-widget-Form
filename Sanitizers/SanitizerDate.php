<?php
namespace Pandora3\Widgets\Form\Sanitizers;

use Pandora3\Libs\Time\Date;
use Pandora3\Widgets\Form\Interfaces\SanitizerInterface;

/**
 * Class SanitizerDate
 * @package Pandora3\Widgets\Form\Sanitizers
 */
class SanitizerDate implements SanitizerInterface {
	
	/**
	 * @param string $value
	 * @param array $arguments
	 * @return Date|null
	 */
	public static function sanitize($value, array $arguments = []): ?Date {
		$format = $arguments['format'] ?? 'd.m.Y';
		return $value ? Date::createFromFormat($format, $value) : null;
	}

}