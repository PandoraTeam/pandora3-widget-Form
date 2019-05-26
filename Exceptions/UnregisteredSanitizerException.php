<?php
namespace Pandora3\Widgets\Form\Exceptions;

use Throwable;
use RuntimeException;
use Pandora3\Core\Interfaces\Exceptions\CoreException;

/**
 * Class UnregisteredSanitizerException
 * @package Pandora3\Widgets\ValidationForm\Exceptions
 */
class UnregisteredSanitizerException extends RuntimeException implements CoreException {

	/**
	 * @param string $sanitizer
	 * @param Throwable|null $previous
	 */
	public function __construct(string $sanitizer, ?Throwable $previous = null) {
		$message = "Unregistered sanitizer '$sanitizer'";
		parent::__construct($message, E_WARNING, $previous);
	}

}