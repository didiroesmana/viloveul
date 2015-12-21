<?php namespace Viloveul\Utility;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Utility
 */

use Viloveul\Core\Configure;
use Viloveul\Core\Object;
use Viloveul\Http\Request;

/**
 * Example to use :
 * 
 * $form = new \Viloveul\Utility\Form('http://localhost/project');
 * echo $form->open();
 * echo $form->inputText('my_email', 'your@email.com', array('id' => 'my-email'));
 * echo $form->close();
 */

class Form extends Object {

	private static $form_id = 0;

	protected $id = 0;

	protected $httpAction;

	protected $hiddenFields = array();

	protected $html5 = false;

	/**
	 * Constructor
	 * 
	 * @access	public
	 * @param	String target action url
	 * @param	Array hidden fields
	 */

	public function __construct($url = '', array $hiddenFields = array(), $html5 = false) {
		self::$form_id++;

		if ( $url ) {
			$this->httpAction = preg_match('#^http(s)?\:\/\/#', $url) ? $url : COnfigure::siteurl($url);
		} else {
			$this->httpAction = Request::currenturl();
		}

		$this->hiddenFields = (array) $hiddenFields;
		$this->html5 = (true === $html5) ? true : false;
	}

	/**
	 * open
	 * 
	 * @access	public
	 * @param	Array|String attributes
	 * @param	Boolean formdata
	 * @return	String form opening tag
	 */

	public function open($params = null, $formData = false) {
		$unique = sprintf('form-%d', self::$form_id);

		$defaults = array(
			'action' => $this->httpAction,
			'method' => 'post',
			'id' => $unique,
			'accept-charset' => 'utf8'
		);

		if ( $params === 'multipart/form-data' ) {
			$defaults['enctype'] = $params;
			$params = null;

		} elseif ( true === $formData ) {
			$defaults['enctype'] = 'multipart/form-data';
			if ( is_array($params) && isset($params['enctype']) ) {
				unset($params['enctype']);
			}
		}

		$html = '<form ' . $this->addAttributes($params, $defaults) . '>' . "\n";

		if ( count($this->hiddenFields) > 0 ) {
			foreach ( $this->hiddenFields as $hiddenName => $hiddenValue ) {
				$html .= $this->inputHidden($hiddenName, $hiddenValue);
			}
		}

		return $html;
	}

	/**
	 * close
	 * 
	 * @access	public
	 * @param	Array hidden field(s)
	 * @return	String form closing tag
	 */

	public function close(array $hiddenFields = array()) {
		$html = '';

		foreach ( $hiddenFields as $hiddenName => $hiddenValue ) {
			$html .= $this->inputHidden($hiddenName, $hiddenValue);
		}

		$html .= '</form>';
		return $html;
	}

	/**
	 * inputText
	 * create input type text
	 * 
	 * @access	public
	 * @param	Array|String attributes or value
	 * @param	Array attributes
	 * @return	String tag input type text
	 */

	public function inputText($name, $value = '', $params = array()) {
		return $this->input('text', $name, $value, $params);
	}

	/**
	 * inputEmail
	 * create input type email
	 * 
	 * @access	public
	 * @param	Array|String attributes or value
	 * @param	Array attributes
	 * @return	String tag input type email
	 */

	public function inputEmail($name, $value = '', $params = array()) {
		return $this->input('email', $name, $value, $params);
	}

	/**
	 * inputPassword
	 * create input type password
	 * 
	 * @access	public
	 * @param	Array|String attributes or value
	 * @param	Array attributes
	 * @return	String tag input type password
	 */

	public function inputPassword($name, $value = '', $params = array()) {
		return $this->input('password', $name, $value, $params);
	}

	/**
	 * inputHidden
	 * create input type hidden
	 * 
	 * @access	public
	 * @param	Array|String attributes or value
	 * @param	Array attributes
	 * @return	String tag input type hidden
	 */

	public function inputHidden($name, $value, $params = array()) {
		return $this->input('hidden', $name, $value, $params);
	}

	/**
	 * inputFile
	 * create input type file
	 * 
	 * @access	public
	 * @param	Array attributes
	 * @return	String tag input type file
	 */

	public function inputFile($name, $params = array()) {
		return $this->input('file', $name, null, $params);
	}

	/**
	 * textarea
	 * create textarea
	 * 
	 * @access	public
	 * @param	String value
	 * @param	Array attributes
	 * @return	String tag textarea
	 */

	public function textarea($name, $value = '', $params = array()) {
		$id = $class = $this->generateId();
		$defaults = compact('name', 'id', 'class');
		return '<textarea ' . $this->addAttributes($params, $defaults) . '>' . $value . '</textarea>' . "\n";
	}

	/**
	 * radio
	 * create input type radio
	 * 
	 * @access	public
	 * @param	String value
	 * @param	Boolean checked or not
	 * @param	Array attributes
	 * @return	String tag input type radio
	 */

	public function radio($name, $value = '', $checked = false, $params = array()) {
		$id = $class = $this->generateId();
		$type = 'radio';
		$defaults = compact('name', 'value', 'id', 'class', 'type');
		if ( 'post' == Request::method('strtolower') ) {
			$checked = ( $value == $this->catchValue($name) );
		}

		if ( true === $checked ) {
			$defaults['checked'] = 'checked';
		} elseif ( ! is_bool($checked) ) {
			$params = $checked;
		}

		return '<input ' . $this->addAttributes($params, $defaults) . ($this->html5 ? ' >' : ' />') . "\n";
	}

	/**
	 * checkbox
	 * create input type checkbox
	 * 
	 * @access	public
	 * @param	String value
	 * @param	Boolean checked or not
	 * @param	Array attributes
	 * @return	String tag input type checkbox
	 */

	public function checkbox($name, $value = '', $checked = false, $params = array()) {
		$id = $class = $this->generateId();
		$type = 'checkbox';
		$defaults = compact('name', 'value', 'id', 'class', 'type');
		if ( 'post' == Request::method('strtolower') ) {
			$checked = ( $value === $this->catchValue($name) );
		}

		if ( true === $checked ) {
			$defaults['checked'] = 'checked';
		}

		if ( ! is_bool($checked) ) {
			$params = $checked;
		}

		return '<input ' . $this->addAttributes($params, $defaults) . ($this->html5 ? ' >' : ' />') . "\n";
	}

	/**
	 * dropdown
	 * create dropdown
	 * 
	 * @access	public
	 * @param	Array option values
	 * @param	Array|String selected value(s)
	 * @param	Array attributes
	 * @return	String tag dropdown
	 */

	public function dropdown($name, $options = array(), $selected = array(), $params = null) {
		if ( ! is_array($selected) ) {
			$selected = is_string($selected) ?
				array($selected) :
					(array) $selected;
		}

		if ( count($selected) < 1 || empty($selected[0]) ) {
			if ( isset($_POST[$name]) ) {
				$tmp = $_POST[$name];
				if ( ! is_array($tmp) ) {
					$selected = is_string($tmp) ?
						array($tmp) :
							(array) $tmp;
				} else {
					$selected = $tmp;
				}
			}
		}

		$id = $class = $this->generateId();

		if ( is_string($params) && false !== strpos($params, 'multiple') ) {
			if ( strpos($params, 'multiple=multiple') === false ) {
				$params = 'multiple=multiple';
			}
			$name = "{$name}[]";
		}

		$defaults = compact('id', 'class', 'name');
		$attributes = $this->addAttributes($params, $defaults);
		$isMultiple = false;

		if ( strpos($attributes, 'multiple') !== false ) {
			$isMultiple = true;
		}

		$html = '<select ' . $attributes . '>' . "\n";
		foreach ( $options as $k => $v ) {
			if ( is_array($v) ) {
				$html .= '<optgroup label="' . $k . '">';
				foreach ( $v as $optk => $optv ) {
					if ( in_array($optk, $selected) ) {
						$html .= sprintf('<option value="%s" selected="selected">%s</option>', $optk, $optv) . "\n";
					} else {
						$html .= sprintf('<option value="%s">%s</option>', $optk, $optv) . "\n";
					}
				}
				$html .= '</optgroup>';
			} else {
				if ( in_array($k, $selected) ) {
					$html .= sprintf('<option value="%s" selected="selected">%s</option>', $k, $v) . "\n";
				} else {
					$html .= sprintf('<option value="%s">%s</option>', $k, $v) . "\n";
				}
			}
		}

		$html .= '</select>' . "\n";
		return $html;
	}

	/**
	 * button
	 * create button element
	 * 
	 * @access	public
	 * @param	String value
	 * @param	String type
	 * @param	Array attributes
	 * @return	String tag button
	 */

	public function button($value, $type = 'button', $params = array()) {
		$id = $class = $this->generateId();
		$defaults = compact('type', 'id', 'class');
		return '<button ' . $this->addAttributes($params, $defaults) . '>' . $value . '</button>' . "\n";
	}

	/**
	 * input
	 * create input element
	 * 
	 * @access	public
	 * @param	String type
	 * @param	String name
	 * @param	Array|String attributes or value
	 * @param	Array attributes
	 * @return	String tag input
	 */

	public function input($type, $name, $value, $params) {
		if ( $type == 'textarea' ) {
			return $this->textarea($name, $value, $params);
		}

		$id = $class = $this->generateId();
		$defaults = compact('name', 'type', 'id', 'class');
		if ( 'file' != $type ) {
			if ( is_array($value) ) {
				$params = $value;
			} elseif ( is_array($params) && ! isset($params['value']) ) {
				$params['value'] = $value;
			} elseif ( is_string($params) ) {
				$params = trim($params.'&value='.$value, '&');
			}
		}

		return '<input ' . $this->addAttributes($params, $defaults) . " />\n";
	}

	/**
	 * catchValue
	 * 
	 * @access	public
	 * @param	String field name
	 * @param	Array|String value(s)
	 * @param	Any default value
	 * @return	Any
	 */

	public function catchValue($field, $default = null) {
		if ( 'post' == Request::method('strtolower') || ! isset($_POST[$field]) ) {
			return $default;
		}

		return $_POST[$field];
	}

	/**
	 * generateId
	 * 
	 * @access	protected
	 * @return	String form-id
	 */

	protected function generateId() {
		$id = 'form-'.self::$form_id.'-';
		return $id.(++$this->id);
	}

	/**
	 * addAttributes
	 * its mean generating attribute to string
	 * 
	 * @access	protected
	 * @param	Array|String attribute(s)
	 * @param	Array default
	 * @return	String attributes
	 */

	protected function addAttributes($params, $defaults = array()) {
		$args = array();
		if ( is_array($params) ) {
			$args = $params;
		} elseif ( is_object($params) ) {
			$args = get_object_vars($params);
		} elseif ( is_string($params) ) {
			parse_str($params, $args);
		}

		$attributes = array_merge((array) $defaults, $args);
		$validAttributes = array();
		if ( ! empty($attributes) ) {
			foreach ( $attributes as $attrKey => $attrVal ) {
				$validAttributes[] = sprintf('%s="%s"', $attrKey, is_array($attrVal) ? implode(' ', $attrVal) : $attrVal);
			}
		}

		return implode(' ', $validAttributes);
	}

	/**
	 * create
	 * called object form statically context
	 * 
	 * @access	public
	 * @param	String target action url
	 * @param	Array hidden fields
	 * @return	Object of "self"
	 */

	public static function create($url = '', array $hiddenFields = array()) {
		return self::createInstance($url, $hiddenFields);
	}

}
