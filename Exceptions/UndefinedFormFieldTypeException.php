<?php
namespace Pandora3\Widgets\Form\Exceptions;

use Throwable;
use RuntimeException;
use Pandora3\Core\Interfaces\Exceptions\CoreException;

/**
 * Class ClassNotFoundException
 * @package Pandora3\Core\Container\Exceptions
 */
class UndefinedFormFieldTypeException extends RuntimeException implements CoreException {

	/**
	 * @param string $fieldName
	 * @param string $className
	 * @param Throwable|null $previous
	 */
	public function __construct(string $fieldName, string $className, ?Throwable $previous = null) {
		$message = "Undefined type of field '$fieldName' for [$className]";
		parent::__construct($message, E_WARNING, $previous);
	}

}