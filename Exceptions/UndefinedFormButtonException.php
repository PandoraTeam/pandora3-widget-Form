<?php
namespace Pandora3\Widgets\Form\Exceptions;

use Throwable;
use RuntimeException;
use Pandora3\Core\Interfaces\Exceptions\CoreException;

/**
 * Class ClassNotFoundException
 * @package Pandora3\Core\Container\Exceptions
 */
class UndefinedFormButtonException extends RuntimeException implements CoreException {

	/**
	 * @param string $buttonName
	 * @param string $className
	 * @param Throwable|null $previous
	 */
	public function __construct(string $buttonName, string $className, ?Throwable $previous = null) {
		$message = "Undefined button '$buttonName' in [$className]";
		parent::__construct($message, E_WARNING, $previous);
	}

}