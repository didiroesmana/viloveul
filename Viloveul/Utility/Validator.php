<?php

namespace Viloveul\Utility;

/**
 * @email fajrulaz@gmail.com
 * @author Fajrul Akbar Zuhdi
 */

use Viloveul\Core\Events as EventManager;

/**
 * Example to use :.
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
 *      'email',
 *      'Label Email',
 *      array(
 *          function($v) use ($validation){
 *              do stuff
 *              $validation->setMessage('error');
 *              return false or true;
 *          }
 *      )
 * );
 * if ($validation->verified() !== false) :
 *      do stuff
 * endif;
 * [/end controller]
 *
 * [view]
 * \Viloveul\Core\Events::trigger('validation_error', array('<div class="wrapping">', '</div>'));
 * [/end view]
 */
class Validator
{
    /**
     * @var mixed
     */
    protected $currentField;

    /**
     * @var mixed
     */
    protected $currentLabel;

    /**
     * @var array
     */
    protected $errorMessages = [];

    /**
     * @var array
     */
    protected $validationRules = [];

    /**
     * @param  $data
     * @param  $callback
     * @return mixed
     */
    public function check($data, $callback)
    {
        $params = is_string($data) ? explode(':', $data, 2) : array_values((array) $data);

        $key = array_shift($params);
        $label = isset($params[0]) ? $params[0] : ucfirst($key);
        $callbacks = array_slice(func_get_args(), 1);

        $this->validationRules[$key] = compact('label', 'callbacks');

        return $this;
    }

    /**
     * @param  $prefix
     * @param  $suffix
     * @return mixed
     */
    public function displayErrors($prefix = '', $suffix = '')
    {
        if (count($this->errorMessages) < 1) {
            return;
        }
        $messages = array_map(
            function ($message) use ($prefix, $suffix) {
                return $prefix . $message . $suffix;
            },
            $this->errorMessages
        );
        echo implode("\n", $messages);
    }

    /**
     * @param $field
     */
    public function getMessage($field)
    {
        return isset($this->errorMessages[$field]) ? $this->errorMessages[$field] : null;
    }

    /**
     * @param $value
     */
    public function isRequired($value)
    {
        $check = !empty($value);
        if (!$check) {
            $this->setMessage($this->currentField, '%s Field is cannot be empty.');
            return false;
        }
        return true;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setMessage($key, $value = null)
    {
        if (is_null($value)) {
            $this->errorMessages[] = sprintf($key, $this->currentLabel);
        } else {
            $this->errorMessages[$key] = sprintf($value, $this->currentLabel);
        }
    }

    public function verified()
    {
        foreach ($this->validationRules as $field => $args) {
            if (false === $this->checkRules($field, $args['label'], $args['callbacks'])) {
                continue;
            }
        }
        if (1 > count($this->errorMessages)) {
            return true;
        }
        EventManager::addListener('validation_errors', array($this, 'displayErrors'));
        return false;
    }

    /**
     * @param $field
     * @param $label
     * @param $callbacks
     */
    protected function checkRules($field, $label, $callbacks)
    {
        if (!isset($_POST[$field])) {
            return false;
        }

        $value = &$_POST[$field];

        $this->currentLabel = $label;

        $this->currentField = $field;

        do {
            $function = $callback = current($callbacks);

            $params = array($value);
            if (is_string($callback)) {
                if (false !== strpos($callback, '[') && preg_match('#(.+?)\[(.+)\]#', $callback, $matches)) {
                    $callback = $matches[1];
                    $args = array_filter(explode(',', $matches[2]), 'trim');
                    if ($args) {
                        foreach ($args as $param) {
                            array_push($params, $param);
                        }
                    }
                }
                $methodName = 'is' . str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($callback))));
                if (method_exists($this, $methodName)) {
                    $function = array($this, $methodName);
                }
            }

            if (is_callable($function)) {
                $check = call_user_func_array($function, $params);
                if (false === $check) {
                    return false;
                }

                $value = (true === $check) ? $value : $check;
            }
        } while (next($callbacks) !== false);

        return true;
    }

    /**
     * @param $value
     */
    protected function isAlphabet($value)
    {
        $check = !preg_match('/[^a-zA-Z]/i', $value);
        if (!$check) {
            $this->setMessage($this->currentField, '%s Field is must be alphabet.');
            return false;
        }
        return true;
    }

    /**
     * @param $value
     */
    protected function isAlphanum($value)
    {
        $check = !preg_match('/[^a-zA-Z0-9]/i', $value);
        if (!$check) {
            $this->setMessage($this->currentField, '%s Field is must be alphabet and (or with) numeric.');
            return false;
        }
        return true;
    }

    /**
     * @param $value
     * @param $field
     */
    protected function isMatches($value, $field)
    {
        if (!isset($_POST[$field]) || $_POST[$field] != $check) {
            $this->setMessage($this->currentField, '%s Field is not matches.');
            return false;
        }
        return true;
    }

    /**
     * @param $value
     */
    protected function isNumeric($value)
    {
        $check = !preg_match('/[^0-9]/i', $value);
        if (!$check) {
            $this->setMessage($this->currentField, '%s Field is must numeric.');
            return false;
        }
        return true;
    }

    /**
     * @param $value
     */
    protected function isValidEmail($value)
    {
        $check = preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $value);
        if (!$check) {
            $this->setMessage($this->currentField, '%s Field is not valid email.');
            return false;
        }
        return true;
    }
}
