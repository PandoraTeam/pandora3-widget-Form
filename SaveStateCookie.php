<?php
namespace Pandora3\Widgets\Form;

use Pandora3\Core\Interfaces\RequestInterface;
use Pandora3\Libs\Application\Application;
use Pandora3\Libs\Cookie\Cookie;
use Pandora3\Libs\Time\Date;
use Pandora3\Widgets\FieldCheckbox\FieldCheckbox;
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
	 * @var bool $loadFromCookies
	 */
	protected $loadFromCookies = false;

	/**
	 * @param RequestInterface $request
	 * @return array
	 */
	protected function loadFromRequest(RequestInterface $request): array {
		$values = $request->all($this->method);
		$cookieName = $this->getCookieName();
		if ($values) {
			$this->loadFromCookies = false;
			$cookies = Application::getInstance()->cookies;
			
			// foreach (array_intersect_key($values, array_flip($this->getSaveFields())) as $field => $value) {
			foreach (array_intersect_key($this->fields, array_flip($this->getSaveFields())) as $fieldName => $field) {
				if ($field instanceof FieldFile) {
					continue;
				}
				$value = $values[$fieldName] ?? null;
				if ($field instanceof FieldCheckbox && !array_key_exists($fieldName, $values)) {
					$values[$fieldName] = 0;
				}
				// if (!is_null($value)) {
					$cookies->set(new Cookie(
						$cookieName.'['.$fieldName.']', $value, [
							'path' => $this->getCookieUri($request),
							'expire' => Date::now()->addInterval('1 months')
						]
					));
				// }
			}
		} else {
			$this->loadFromCookies = true;
			$values = $request->getCookie($cookieName) ?? [];
			if ($values) {
				$values = array_intersect_key($values, array_flip($this->getSaveFields()));
			}
		}
		return $values;
	}
	
	/**
	 * @param string $fieldName
	 * @param string|null $value
	 * @param RequestInterface $request
	 */
	public function saveCookieValue(string $fieldName, ?string $value, RequestInterface $request): void {
		$cookies = Application::getInstance()->cookies;
		$cookieName = $this->getCookieName();
		$cookies->set(new Cookie(
			$cookieName.'['.$fieldName.']', $value, [
				'path' => $this->getCookieUri($request),
				'expire' => Date::now()->addInterval('1 months')
			]
		));
	}
	
	/**
	 * @param string $fieldName
	 * @param RequestInterface $request
	 * @return string|null
	 */
	public function getCookieValue(string $fieldName, RequestInterface $request): ?string {
		$cookieName = $this->getCookieName();
		$values = $request->getCookie($cookieName) ?? [];
		return $values[$fieldName] ?? null;
	}
	
	/**
	 * @param array $values
	 * @return array
	 */
	protected function sanitize(array $values): array {
		$sanitizers = $this->sanitizers();
		if ($this->loadFromCookies) {
			$sanitizers = array_intersect_key($sanitizers, $values);
		}
		return $this->sanitizeFields($sanitizers, $values);
	}

	/**
	 * @return array
	 */
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
	
	/**
	 * @return string
	 */
	protected function getCookieName(): string {
		return 'formData_'.preg_replace('#.*\\\\#', '', static::class);
	}
	
}