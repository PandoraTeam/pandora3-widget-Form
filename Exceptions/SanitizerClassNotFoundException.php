<?php
namespace Pandora3\Widgets\Form\Exceptions;

use Throwable;
use RuntimeException;
use Pandora3\Core\Interfaces\Exceptions\CoreException;

/**
 * Class ClassNotFoundException
 * @package Pandora3\Core\Container\Exceptions
 */
class SanitizerClassNotFoundException extends RuntimeException implements CoreException {

	/**
	 * @param string $className
	 * @param Throwable|null $previous
	 */
	public function __construct(string $className, ?Throwable $previous = null) {
		$message = "Sanitizer class '$className' not found";
		parent::__construct($message, E_WARNING, $previous);
	}

}