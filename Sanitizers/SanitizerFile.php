<?php
namespace Pandora3\Widgets\Form\Sanitizers;

use Pandora3\Libs\File\UploadedFile;
use Pandora3\Widgets\Form\Interfaces\SanitizerInterface;

/**
 * Class SanitizerInteger
 * @package Pandora3\Widgets\Form\Sanitizers
 */
class SanitizerFile implements SanitizerInterface {

	/**
	 * @param mixed|null $value
	 * @param array $arguments
	 * @return UploadedFile|null
	 */
	public static function sanitize($value, array $arguments = []): UploadedFile {
		if (!is_array($value) || empty($value['tmp_name'])) {
			return null;
		}
		return new UploadedFile($value['tmp_name'], $value['name'], $value['type'], $value['size'], $value['error']);
	}

}