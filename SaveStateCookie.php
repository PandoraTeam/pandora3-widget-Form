<?php
namespace Pandora3\Widgets\Form;

use Pandora3\Core\Interfaces\RequestInterface;
use Pandora3\Libs\Application\Application;
use Pandora3\Libs\Cookie\Cookie;
use Pandora3\Libs\Time\Date;
use Pandora3\Widgets\FieldFile\FieldFile;

/**
 * Trait SaveStateCookie
 * @package Pandora3\Widgets\Form
 *
 * @property string $method
 */
trait SaveStateCookie {

	/**
	 * @param RequestInterface $request
	 * @return array
	 */
	protected function loadFromRequest(RequestInterface $request): array {
		$values = $request->all($this->method);
		if ($values) {
			$cookies = Application::getInstance()->cookies;
			$name = preg_replace('#.*\\\\#', '', static::class);
			foreach ($values as $field => $value) {
				if ($field instanceof FieldFile) {
					continue;
				}
				$cookies->set(new Cookie(
					"formData.$name.$field", $value, [
						'path' => $request->uri,
						'expire' => Date::now()->addInterval('1 months')
					]
				));
			}
		}
		return $values;
	}
	
}