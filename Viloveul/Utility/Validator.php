<?php namespace Viloveul\Utility;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Utility
 */

use Viloveul\Core\Events as EventManager;
use Viloveul\Http\Request;

/**
 * Example to use :
 * 
 * <form method="post">
 * <input name="email" type="text">
 * </form>
 * 
 * [controller]
 * $validation = new \Viloveul\Utility\Validator();
 * $validation->check('email', 'Label Email', 'valid_email');
 * or
 * $validation->check(
 * 		'email',
 * 		'Label Email',
 * 		array(
 * 			function($v) use ($validation){
 * 				do stuff
 * 				$validation->setMessage('error');
 * 				return false or true;
 * 			}
 * 		)
 * );
 * if ($validation->verified() !== false) :
 * 		do stuff
 * endif;
 * [/end controller]
 * 
 * [view]
 * \Viloveul\Core\Events::trigger('validation_error', array('<div class="wrapping">', '</div>'));
 * [/end view]
 */

class Validator {

	protected $validationRules = array();

	protected $errorMessages = array();

	protected $currentLabel;

	protected $currentField;

	/**
	 * isMatches
	 * 
	 * @access	protected
	 * @param	String value
	 * @param	String fieldname
	 * @return	Boolean
	 */

	protected function isMatches($value, $field) {
		if ( ! isset($_POST[$field]) || $_POST[$field] != $check ) {
			$this->setMessage($this->currentField, '%s Field is not matches.');
			return false;
		}

		return true;
	}

	/**
	 * isRequired
	 * 
	 * @access	protected
	 * @param	String value
	 * @return	Boolean
	 */

	public function isRequired($value) {
		$check = ! empty($value);
		if ( ! $check ) {
			$this->setMessage($this->currentField, '%s Field is cannot be empty.');
			return false;
		}

		return true;
	}

	/**
	 * isValidEmail
	 * 
	 * @access	protected
	 * @param	String value
	 * @return	Boolean
	 */

	protected function isValidEmail($value) {
		$check = preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $value);
		if ( ! $check ) {
			$this->setMessage($this->currentField, '%s Field is not valid email.');
			return false;
		}

		return true;
	}

	/**
	 * isAlphabet
	 * 
	 * @access	protected
	 * @param	String value
	 * @return	Boolean
	 */

	protected function isAlphabet($value) {
		$check = ! preg_match("/[^a-zA-Z]/i", $value);
		if ( ! $check ) {
			$this->setMessage($this->currentField, '%s Field is must be alphabet.');
			return false;
		}

		return true;
	}

	/**
	 * isAlphanum
	 * 
	 * @access	protected
	 * @param	String value
	 * @return	Boolean
	 */

	protected function isAlphanum($value) {
		$check = ! preg_match("/[^a-zA-Z0-9]/i", $value);
		if ( ! $check ) {
			$this->setMessage($this->currentField, '%s Field is must be alphabet and (or with) numeric.');
			return false;
		}

		return true;
	}

	/**
	 * isNumeric
	 * 
	 * @access	protected
	 * @param	String value
	 * @return	Boolean
	 */

	protected function isNumeric($value) {
		$check = ! preg_match("/[^0-9]/i", $value);
		if ( ! $check ) {
			$this->setMessage($this->currentField, '%s Field is must numeric.');
			return false;
		}

		return true;
	}

	/**
	 * checkRules
	 * 
	 * @access	protected
	 * @param	String field name
	 * @param	String label
	 * @param	String|Array rules
	 */

	protected function checkRules($field, $label, $callbacks) {
		if ( ! isset($_POST[$field]) )
			return false;

		$value =& $_POST[$field];

		$this->currentLabel = $label;

		$this->currentField = $field;

		do {
			$function = $callback = current($callbacks);

			$params = array($value);
			if ( is_string($callback) ) {
				if ( false !== strpos($callback, '[') && preg_match('#(.+?)\[(.+)\]#', $callback, $matches) ) {
					$callback = $matches[1];
					$args = array_filter(explode(',', $matches[2]), 'trim');
					if ( $args ) {
						foreach ( $args as $param ) {
							array_push($params, $param);
						}
					}
				}
				$methodName = 'is' . str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($callback))));
				if ( method_exists($this, $methodName) ) {
					$function = array($this, $methodName);
				}
			}

			if ( is_callable($function) ) {
				$check = call_user_func_array($function, $params);
				if ( false === $check ) {
					return false;
				}

				$value = (true === $check) ? $value : $check;
			}

		} while ( next($callbacks) !== false );

		return true;

	}

	/**
	 * displayErrors
	 * 
	 * @access	public
	 * @param	String prefix
	 * @param	String Suffix
	 */

	public function displayErrors($prefix = '', $suffix = '') {
		if ( count($this->errorMessages) < 1 ) {
			return null;
		}

		$messages = array_map(
			function($message) use ($prefix, $suffix) {
				return $prefix.$message.$suffix;
			},
			$this->errorMessages
		);

		echo implode("\n", $messages);
	}

	/**
	 * check
	 * 
	 * @access	protected
	 * @param	Array|String field:label
	 * @param	[mixed] Callable callback
	 * @return	void
	 */

	public function check($data, $callback) {
		$params = is_string($data) ?
			explode(':', $data, 2) :
				array_values((array) $data);

		$key = array_shift($params);
		$label = isset($params[0]) ? $params[0] : ucfirst($key);
		$callbacks = array_slice(func_get_args(), 1);

		$this->validationRules[$key] = compact('label', 'callbacks');
		return $this;
	}

	/**
	 * setMessage
	 * 
	 * @access	public
	 * @param	String rule name
	 * @param	String message
	 */

	public function setMessage($key, $value = null) {
		if ( is_null($value) ) {
			$this->errorMessages[] = sprintf($key, $this->currentLabel);
		} else {
			$this->errorMessages[$key] = sprintf($value, $this->currentLabel);
		}
	}

	/**
	 * getMessage
	 * 
	 * @access	public
	 * @param	String field
	 * @return	String error message
	 */

	public function getMessage($field) {
		return isset($this->errorMessages[$field]) ?
			$this->errorMessages[$field] :
				null;
	}

	/**
	 * verified
	 * 
	 * @access	public
	 * @return	Boolean
	 */

	public function verified() {
		foreach ( $this->validationRules as $field => $args ) {
			if ( false === $this->checkRules($field, $args['label'], $args['callbacks']) ) {
				continue;
			}
		}

		if ( 1 > count($this->errorMessages) ) {
			return true;
		}

		EventManager::addListener('validation_errors', array($this, 'displayErrors'));
		return false;
	}

}
