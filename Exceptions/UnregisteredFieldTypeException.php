<?php
namespace Pandora3\Widgets\Form\Exceptions;

use Throwable;
use RuntimeException;
use Pandora3\Core\Interfaces\Exceptions\CoreException;

/**
 * Class UnregisteredSanitizerException
 * @package Pandora3\Widgets\ValidationForm\Exceptions
 */
class UnregisteredFieldTypeException extends RuntimeException implements CoreException {

	/**
	 * @param string $fieldType
	 * @param Throwable|null $previous
	 */
	public function __construct(string $fieldType, ?Throwable $previous = null) {
		$message = "Unregistered field type '$fieldType'";
		parent::__construct($message, E_WARNING, $previous);
	}

}