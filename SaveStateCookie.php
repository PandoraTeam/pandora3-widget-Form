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
	 * @var string|null $cookieUri
	 */
	public $cookieUri = null;

	/**
	 * @param RequestInterface $request
	 * @return array
	 */
	protected function loadFromRequest(RequestInterface $request): array {
		$values = $request->all($this->method);
		$cookieName = 'formData_'.preg_replace('#.*\\\\#', '', static::class);
		if ($values) {
			$cookies = Application::getInstance()->cookies;
			foreach (array_intersect_key($values, array_flip($this->getSaveFields())) as $field => $value) {
				if ($field instanceof FieldFile) {
					continue;
				}
				$cookies->set(new Cookie(
					$cookieName.'['.$field.']', $value, [
						'path' => $this->getCookieUri($request),
						'expire' => Date::now()->addInterval('1 months')
					]
				));
			}
		} else {
			$values = $request->getCookie($cookieName) ?? [];
			if ($values) {
				$values = array_intersect_key($values, array_flip($this->getSaveFields()));
			}
		}
		return $values;
	}

	protected function getSaveFields(): array {
		return array_keys($this->fields);
	}

	/**
	 * @param RequestInterface $request
	 * @return string
	 */
	protected function getCookieUri(RequestInterface $request): string {
		return $this->cookieUri ?? $request->uri;
	}
	
}