<?php
namespace Pandora3\Widgets\Form;

use Pandora3\Core\Debug\Debug;
use Pandora3\Core\Interfaces\RequestInterface;
use Pandora3\Libs\Application\Application; // todo: think if can get rid of
use Pandora3\Libs\Widget\Exceptions\WidgetRenderException;
use Pandora3\Widgets\FormField\FormField;
use Pandora3\Widgets\Form\Exceptions\{
	FormFieldClassNotFoundException, SanitizerClassNotFoundException,
	UndefinedFormButtonException, UndefinedFormFieldException, UndefinedFormFieldTypeException,
	UnregisteredFieldTypeException, UnregisteredSanitizerException
};

/**
 * Class Form
 * @package Pandora3\Widgets\Form
 *
 * @property-read string $message
 * @property-read bool $isUpdate
 * @property-read array $values
 * @property-read object|null $model
 */
abstract class Form {

	/** @var string $action */
	public $action;

	/** @var RequestInterface $request */
	protected $request;

	/** @var array $values */
	protected $values = [];

	/** @var array $requestValues */
	protected $requestValues = [];

	/** @var array $requestExcludeFields */
	protected $requestExcludeFields = [];

	/** @var array $fieldMessages */
	protected $fieldMessages = [];

	/** @var string $message */
	protected $message = '';

	/** @var object|null $model */
	protected $model;

	/** @var string $method */
	protected $method = 'post';

	/** @var bool $autoLoad */
	protected $autoLoad = true;
	
	/** @var bool $isLoaded */
	protected $isLoaded = false;

	/** @var FormField[] $fields */
	protected $fields = [];

	/** @var array $buttons */
	protected $buttons = [];

	/** @var string $id */
	protected $id = '';

	/** @var string $baseUri */
	protected $baseUri = null;

	/** @var bool $files */
	protected $files = false;

	/** @var array */
	protected static $currentParams = [];

	/** @var int $index */
	protected static $index = 1;

	/** @var array $fieldTypes */
	protected static $fieldTypes = [];

	/** @var array $sanitizerTypes */
	protected static $sanitizerTypes = [];

	/**
	 * @param RequestInterface $request
	 * @param object|null $model
	 * @param string|null $action
	 */
	public function __construct(RequestInterface $request, $model = null, ?string $action = null) {
		$this->request = $request;
		$this->action = $action ?? $request->uri;
		if (is_array($model)) {
			$model = (object) $model;
		}
		$this->model = $model ? (object) $this->setModel($model) : null;
		$this->initFields($this->getFields());
		$this->initButtons($this->getButtons());
		$app = Application::getInstance();
		if (is_null($this->baseUri)) {
			$this->baseUri = preg_replace('#/$#', '', $app->baseUri);
		}
		if ($this->autoLoad) {
			$this->load();
		}
	}

	/**
	 * @param string $type
	 * @param string $className
 	 */
	public static function registerField(string $type, string $className): void {
		self::$fieldTypes[$type] = $className;
	}

	/**
	 * @param array $fieldTypes
 	 */
	public static function registerFields(array $fieldTypes): void {
		self::$fieldTypes = array_replace(self::$fieldTypes, $fieldTypes);
	}

	/**
	 * @param string $type
	 * @param string $className
 	 */
	public static function registerSanitizer(string $type, string $className): void {
		self::$sanitizerTypes[$type] = $className;
	}

	/**
	 * @param array $sanitizerTypes
 	 */
	public static function registerSanitizers(array $sanitizerTypes): void {
		self::$sanitizerTypes = array_replace(self::$sanitizerTypes, $sanitizerTypes);
	}

	/**
	 * @param object $model
 	 * @return object|array
 	 */
	protected function setModel($model) {
		return $model;
	}

	/**
	 * @return array
	 */
	abstract protected function getFields(): array;

	/**
	 * @return array
	 */
	protected function sanitizers(): array {
		return [];
	}

	/**
	 * @param array $values
	 * @return array
	 */
	protected function afterLoad(array $values): array {
		return $values;
	}

	// abstract protected function getButtons(): array;

	// todo: move to App\Widgets\ApplicationForm
	/**
	 * @return array
	 */
	protected function getButtons(): array {
		return [
			'save' => [
				'type' => 'submit',             'title' => $this->isUpdate ? 'Сохранить' : 'Добавить',
				'class' => 'button-primary',    'icon' => '<i class="mdi mdi-check"></i>',
			],
			'cancel' => [
				'type' => 'link',               'title' => 'Отмена',
				'icon' => '<i class="mdi mdi-close"></i>',
			]
		];
	}

	/**
	 * @param array $fieldsData
	 */
	protected function initFields(array $fieldsData): void {
		foreach ($fieldsData as $name => $params) {
			$field = null;
			$value = $this->model->$name ?? $params['default'] ?? null;

			$fieldType = $params['type'] ?? null;
			if (!$fieldType) {
				throw new UndefinedFormFieldTypeException($name, static::class);
			}

			if (!array_key_exists($fieldType, self::$fieldTypes)) {
				throw new UnregisteredFieldTypeException($fieldType);
			}
			$fieldClass = self::$fieldTypes[$fieldType];
			if (!class_exists($fieldClass)) {
				throw new FormFieldClassNotFoundException($fieldClass);
			}

			$field = $fieldClass::create($name, $value, $params);
			$this->fields[$name] = $field;
			if ($this->model && property_exists($this->model, $name)) {
				$this->model->$name = $field->value; // todo: think
			}
			if ($params['ignoreRequest'] ?? false) {
				$this->requestExcludeFields[$name] = true;
			}
		}
	}

	/**
	 * @param array $buttonsData
	 */
	protected function initButtons(array $buttonsData): void {
		foreach ($buttonsData as $name => $params) {
			$this->buttons[$name] = (object) $params;
			// type, title, class, icon, href
		}
	}

	/**
	 * @param string $name
	 * @return FormField|null
	 */
	public function getField(string $name): ?FormField {
		return $this->fields[$name] ?? null;
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasField(string $name): bool {
		return array_key_exists($name, $this->fields);
	}

	/**
 	 * @ignore
	 * @param string $property
	 * @return mixed
	 */
	public function __get(string $property) {
		$methods = [
			'isUpdate' => 'isUpdate',
			'values' => 'getValues',
			'model' => 'getModel',
			'message' => 'getMessage',
		];
		$methodName = $methods[$property] ?? '';
		if ($methodName && method_exists($this, $methodName)) {
			return $this->{$methodName}();
		}

		if ($this->hasField($property)) {
			return $this->get($property);
		}

		$className = static::class;
		Debug::logException(new \Exception("Undefined property '$property' for [$className]", E_NOTICE));
		return null;
	}

	/**
	 * @param string $property
	 * @param mixed $value
	 */
	public function __set(string $property, $value): void {
		$this->set($property, $value);
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function get(string $name) {
		if (array_key_exists($name, $this->values)) {
			return $this->values[$name];
		}
		return $this->model->$name ?? null;
	}

	/**
	 * @param string $name
	 * @param mixed $value
 	 */
	public function set(string $name, $value): void {
		if ($this->hasField($name)) {
			$this->values[$name] = $value;
		}
	}

	/**
	 * @return array
	 */
	public function getValues(): array {
		$values = [];
		foreach (array_keys($this->fields) as $name) {
			$values[$name] = $this->get($name);
		}
		return $values;
	}

	/**
	 * @param array $values
 	 */
	public function setValues(array $values): void {
		foreach ($values as $name => $value) {
			$this->set($name, $value);
		}
	}

	/**
	 * @param array ...$arguments
 	 * @return array
 	 */
	public function only(...$arguments): array {
		$fieldNames = array_intersect($arguments, array_keys($this->fields));
		$values = [];
		foreach ($fieldNames as $name) {
			$values[$name] = $this->get($name);
		}
		return $values;
	}

	/**
	 * @param array ...$arguments
 	 * @return array
 	 */
	public function except(...$arguments): array {
		$fieldNames = array_diff(array_keys($this->fields), $arguments);
		$values = [];
		foreach ($fieldNames as $name) {
			$values[$name] = $this->get($name);
		}
		return $values;
	}

	/**
	 * @param RequestInterface $request
	 * @return array
	 */
	protected function loadFromRequest(RequestInterface $request): array {
		return $request->all($this->method);
	}

	/**
	 * @return array
	 */
	public function load(): array {
		$values = $this->requestValues = array_diff_key(
			$this->loadFromRequest($this->request),
			$this->requestExcludeFields
		);
		$values = array_intersect_key($values, $this->fields);
		$values = $this->sanitize($values);
		$this->isLoaded = true;
		return $this->values = $this->afterLoad($values);
	}

	/**
	 * @param array $values
	 * @return array
	 */
	protected function sanitize(array $values): array {
		$sanitizers = $this->sanitizers();
		foreach ($sanitizers as $fieldName => $fieldSanitizers) {
			if (!$this->hasField($fieldName)) {
				continue;
			}
			if (is_string($fieldSanitizers)) {
				$fieldSanitizers = [$fieldSanitizers];
			}
			$value = $values[$fieldName] ?? null;
			if (!is_null($value)) {
				foreach ($fieldSanitizers as $key => $sanitizer) {
					$arguments = [];
					if (!is_numeric($key)) {
						$arguments = $sanitizer;
						$sanitizer = $key;
					}
					if (!array_key_exists($sanitizer, self::$sanitizerTypes)) {
						throw new UnregisteredSanitizerException($sanitizer);
					}
					$sanitizerClass = self::$sanitizerTypes[$sanitizer];
					if (!class_exists($sanitizerClass)) {
						throw new SanitizerClassNotFoundException($sanitizerClass);
					}
					$value = $sanitizerClass::sanitize($value, $arguments);
				}
				$values[$fieldName] = $value;
			}
		}
		return $values;
	}

	/**
	 * @return mixed
	 */
	public function getModel() {
		return $this->model;
	}

	/**
	 * @return bool
	 */
	public function isUpdate(): bool {
		return !is_null($this->model);
	}

	/**
	 * @return string
 	 */
	protected static function generateId(): string {
		return 'form-'.(self::$index++);
	}

	// todo: CSRF token field
	/**
	 * @param array $params
	 * @return string
	 */
	public function begin($params = []): string {
		$id = $params['id'] ?? $this->id;
		$files = $params['files'] ?? $this->files;
		$action = $params['action'] ?? $this->action;
		$method = $params['method'] ?? $this->method;
		$baseUri = $params['baseUri'] ?? $this->baseUri;

		$id = $id ?: self::generateId();
		$class = $params['class'] ?? '';
		$attribs = $params['attribs'] ?? '';

		self::$currentParams = compact('id', 'files', 'action', 'method', 'baseUri', 'class');

		if ($files) {
			$attribs .= ' enctype="multipart/form-data"';
		}
		if ($id) {
			$id = 'id="'.$id.'"';
		}
		if ($class) {
			$class = 'class="'.$class.'"';
		}
		ob_start();
			?><form <?= $id ?> <?= $class ?> action="<?= $baseUri.$action ?>" method="<?= $method ?>" <?= $attribs ?>><?php
		return ob_get_clean();
	}

	/**
	 * @return string
	 */
	public function end(): string {
		self::$currentParams = [];
		return '</form>';
	}

	/**
	 * @param string $name
	 * @param array $params
	 * @return string
	 * @throws WidgetRenderException
	 */
	public function field(string $name, array $params = []): string {
		$field = $this->fields[$name] ?? null;
		if (!$field) {
			throw new UndefinedFormFieldException($name, static::class);
		}
		$field->setValue($this->get($name));
		return self::renderField($field, $params);
	}

	/**
	 * @return string
	 */
	public function fakePassword(): string {
		return '<div style="overflow: hidden; height: 0;"><input type="password" name="fakePassword"></div>';
	}

	/**
	 * @param FormField $field
	 * @param array $params
	 * @return string
	 * @throws WidgetRenderException
	 */
	protected static function renderField(FormField $field, array $params = []): string {
		$html = $field->render($params);
		$wrap = !(is_a($field, '\Pandora3\Widgets\FieldHidden\FieldHidden'));
		$wrap = $params['wrap'] ?? $field->context['wrap'] ?? $wrap;
		if (!$wrap) {
			return $html;
		}
		$idDisabled = $params['disabled'] ?? $field->context['disabled'] ?? false;
		$fieldClass = $params['fieldClass'] ?? $field->context['fieldClass'] ?? '';
		if ($idDisabled) {
			$fieldClass .= ' disabled';
		}
		$labelAfter = $params['labelAfter'] ?? $field->context['labelAfter'] ?? false;
		$label = $params['label'] ?? $field->label;
		// todo: move to template
		ob_start();
		?><div class="field <?= $labelAfter ? 'label-after' : '' ?> <?= $fieldClass ?>"><?php
			if (
				$label &&
				!is_a($field, '\Pandora3\Widgets\FieldCheckbox\FieldCheckbox') &&
				!is_a($field, '\Pandora3\Widgets\FieldRadio\FieldRadio') &&
				!is_a($field, '\Pandora3\Widgets\FieldCheckboxGroup\FieldCheckboxGroup')
			) { // todo: implement property or method instead of class checks
				if ($labelAfter) {
					?><label><?php
						echo $html;
						?><div class="label"><?= htmlentities($label) ?></div><?php // id="field1-label"
					?></label><?php
				} else {
					?><label><?php
						?><div class="label"><?= htmlentities($label) ?></div><?php // id="field1-label"
						echo $html;
					?></label><?php
				}
			} else {
				echo $html;
			}
		?></div><?php
		return ob_get_clean();
	}

	/**
	 * @param string $name
	 * @param array $params
	 * @return string
	 */
	public function button(string $name, array $params = []): string {
		$button = $this->buttons[$name] ?? null;
		if (!$button) {
			throw new UndefinedFormButtonException($name, static::class);
		}
		$params['name'] = $name;
		if (!empty($params['class'])) {
			$params['class'] = ltrim($button->class.' '.$params['class']);
		}
		return self::renderButton(array_replace((array) $button, $params));
	}

	/**
	 * @param array $params
	 * @return string
	 */
	protected function renderButton(array $params): string {
		$type = $params['type'] ?? 'link';
		$class = $params['class'] ?? '';
		$attribs = $params['attribs'] ?? '';
		$title = $params['title'] ?? '';
		$icon = $params['icon'] ?? '';
		$href = $params['href'] ?? 'javascript:void(0)';
		$width = $params['width'] ?? '';
		if ($params['disabled'] ?? false) {
			$class .= ' disabled';
			$attribs .= ' tabindex="-1"';
		}

		if ($type === 'submit') {
			$href = 'javascript:document.forms[\''.self::$currentParams['id'].'\'].submit()';
		}
		if ($width) {
			if (is_numeric($width)) {
				$width .= 'px';
			}
			$attribs .= ' style="width: '.$width.'"';
		}
		ob_start();
		/* ?><button class="button <?= $class ?>" onclick="<?= $href ?>"><?= $icon.$title ?></button><?php */
		?><a class="button <?= $class ?>" href="<?= $href ?>" <?= $attribs ?>><?= $icon . htmlentities($title) ?></a><?php
		return ob_get_clean();
	}

	/**
	 * @param array $params
	 * @return string
	 * @throws WidgetRenderException
	 */
	public function render(array $params = []): string {
		ob_start();
		echo $this->begin($params);
			foreach ($this->fields as $name => $field) {
				// echo $field->render();
				echo self::renderField($field);
			}
			echo '<div class="form-toolbar">';
				foreach ($this->buttons as $button) {
					echo self::renderButton((array) $button);
				}
			echo '</div>';
		echo $this->end();
		return ob_get_clean();
	}

	/**
	 * @param string $field
	 * @param string $message
	 */
	public function setFieldMessage(string $field, string $message) {
		$this->fieldMessages[$field] = $message;
	}

	/**
	 * @param string $field
	 * @return string
	 */
	public function getFieldMessage(string $field): string {
		return $this->fieldMessages[$field] ?? '';
	}

	/**
	 * @param string $message
	 */
	public function setMessage(string $message) {
		$this->message = $message;
	}

	/**
	 * @return string
	 */
	public function getMessage(): string {
		return $this->message;
	}

	/**
	 * @return string
	 */
	public function getMessages(): string {
		$messages = $this->fieldMessages;
		if ($this->message) {
			$messages = array_replace(['' => $this->message], $messages);
		}
		if (!$messages) {
			return '';
		}
		ob_start();
		?><div class="form-messages"><?php
			foreach ($messages as $field => $fieldMessages) {
				if (!is_array($fieldMessages)) {
					$fieldMessages = [$fieldMessages];
				}
				foreach ($fieldMessages as $message) {
					?><div class="message message-danger"><?php
						/* ?><i class="mdi mdi-alert-circle"></i><?php */
						?><span><?= $message ?></span><?php
					?></div><?php
				}
			}
		?></div><?php
		return ob_get_clean();
	}

}

Form::registerSanitizers([
	'bool' => '\Pandora3\Widgets\Form\Sanitizers\SanitizerBoolean',
	'int' => '\Pandora3\Widgets\Form\Sanitizers\SanitizerInteger',
	'date' => '\Pandora3\Widgets\Form\Sanitizers\SanitizerDate',
	'lower' => '\Pandora3\Widgets\Form\Sanitizers\SanitizerLowercase',
	'trim' => '\Pandora3\Widgets\Form\Sanitizers\SanitizerTrim',
	'file' => '\Pandora3\Widgets\Form\Sanitizers\SanitizerFile',
]);

Form::registerFields([
	'input' => '\Pandora3\Widgets\FieldText\FieldText',
	'password' => '\Pandora3\Widgets\FieldText\FieldText',
	'date' => '\Pandora3\Widgets\FieldDate\FieldDate',

	'hidden' => '\Pandora3\Widgets\FieldHidden\FieldHidden',
	'select' => '\Pandora3\Widgets\FieldSelect\FieldSelect',
	'checkbox' => '\Pandora3\Widgets\FieldCheckbox\FieldCheckbox',
	'file' => '\Pandora3\Widgets\FieldFile\FieldFile',
	'radio' => '\Pandora3\Widgets\FieldRadio\FieldRadio',
	'textarea' => '\Pandora3\Widgets\FieldTextarea\FieldTextarea',

	'passwordView' => '\Pandora3\Widgets\FieldPasswordView\FieldPasswordView',
	'checkboxGroup' => '\Pandora3\Widgets\FieldCheckboxGroup\FieldCheckboxGroup',

	'inputFiltered' => '\Pandora3\Widgets\FieldTextFiltered\FieldTextFiltered',
	'number' => '\Pandora3\Widgets\FieldTextFiltered\FieldTextFiltered',
	'int' => '\Pandora3\Widgets\FieldTextFiltered\FieldTextFiltered',
]);
