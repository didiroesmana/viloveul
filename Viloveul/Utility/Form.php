<?php

namespace Viloveul\Utility;

/**
 * @email fajrulaz@gmail.com
 * @author Fajrul Akbar Zuhdi
 */

use Viloveul\Core\Configure;
use Viloveul\Core\Factory;

/**
 * Example to use :.
 *
 * $form = new \Viloveul\Utility\Form('http://localhost/project');
 * echo $form->open();
 * echo $form->inputText('my_email', 'your@email.com', array('id' => 'my-email'));
 * echo $form->close();
 */
class Form extends Factory
{
    /**
     * @var array
     */
    protected $hiddenFields = [];

    /**
     * @var mixed
     */
    protected $html5 = false;

    /**
     * @var mixed
     */
    protected $httpAction;

    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var int
     */
    private static $form_id = 0;

    /**
     * @param $url
     * @param array    $hiddenFields
     * @param $html5
     */
    public function __construct($url = '', array $hiddenFields = [], $html5 = false)
    {
        ++static::$form_id;

        if ($url) {
            $this->httpAction = preg_match('#^http(s)?\:\/\/#', $url) ? $url : COnfigure::siteurl($url);
        } else {
            $this->httpAction = $this->uri->currentUrl();
        }

        $this->hiddenFields = (array) $hiddenFields;
        $this->html5 = (true === $html5) ? true : false;
    }

    /**
     * @param $value
     * @param $type
     * @param array    $params
     */
    public function button($value, $type = 'button', $params = [])
    {
        $id = $class = $this->generateId();
        $defaults = compact('type', 'id', 'class');

        return '<button ' . $this->addAttributes($params, $defaults) . '>' . $value . '</button>' . "\n";
    }

    /**
     * @param  $field
     * @param  $default
     * @return mixed
     */
    public function catchValue($field, $default = null)
    {
        if (!$this->request->isMethod('post') || !isset($_POST[$field])) {
            return $default;
        }

        return $_POST[$field];
    }

    /**
     * @param $name
     * @param $value
     * @param $checked
     * @param array      $params
     */
    public function checkbox($name, $value = '', $checked = false, $params = [])
    {
        $id = $class = $this->generateId();
        $type = 'checkbox';
        $defaults = compact('name', 'value', 'id', 'class', 'type');
        if ($this->request->isMethod('post')) {
            $checked = ($value === $this->catchValue($name));
        }

        if (true === $checked) {
            $defaults['checked'] = 'checked';
        }

        if (!is_bool($checked)) {
            $params = $checked;
        }

        return '<input ' . $this->addAttributes($params, $defaults) . ($this->html5 ? ' >' : ' />') . "\n";
    }

    /**
     * @param  array   $hiddenFields
     * @return mixed
     */
    public function close(array $hiddenFields = [])
    {
        $html = '';

        foreach ($hiddenFields as $hiddenName => $hiddenValue) {
            $html .= $this->inputHidden($hiddenName, $hiddenValue);
        }

        $html .= '</form>';

        return $html;
    }

    /**
     * @param $url
     * @param array  $hiddenFields
     */
    public static function create($url = '', array $hiddenFields = [])
    {
        return new static($url, $hiddenFields);
    }

    /**
     * @param $name
     * @param array     $options
     * @param array     $selected
     * @param $params
     */
    public function dropdown($name, $options = [], $selected = [], $params = null)
    {
        if (!is_array($selected)) {
            $selected = is_string($selected) ? [$selected] : (array) $selected;
        }

        if (count($selected) < 1 || empty($selected[0])) {
            if (isset($_POST[$name])) {
                $tmp = $_POST[$name];
                if (!is_array($tmp)) {
                    $selected = is_string($tmp) ? [$tmp] : (array) $tmp;
                } else {
                    $selected = $tmp;
                }
            }
        }

        $id = $class = $this->generateId();

        if (is_string($params) && false !== strpos($params, 'multiple')) {
            if (strpos($params, 'multiple=multiple') === false) {
                $params = 'multiple=multiple';
            }
            $name = "{$name}[]";
        }

        $defaults = compact('id', 'class', 'name');
        $attributes = $this->addAttributes($params, $defaults);
        $isMultiple = false;

        if (strpos($attributes, 'multiple') !== false) {
            $isMultiple = true;
        }

        $html = '<select ' . $attributes . '>' . "\n";
        foreach ($options as $k => $v) {
            if (is_array($v)) {
                $html .= '<optgroup label="' . $k . '">';
                foreach ($v as $optk => $optv) {
                    if (in_array($optk, $selected)) {
                        $html .= sprintf('<option value="%s" selected="selected">%s</option>', $optk, $optv) . "\n";
                    } else {
                        $html .= sprintf('<option value="%s">%s</option>', $optk, $optv) . "\n";
                    }
                }
                $html .= '</optgroup>';
            } else {
                if (in_array($k, $selected)) {
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
     * @param  $type
     * @param  $name
     * @param  $value
     * @param  $params
     * @return mixed
     */
    public function input($type, $name, $value, $params)
    {
        if ($type == 'textarea') {
            return $this->textarea($name, $value, $params);
        }

        $id = $class = $this->generateId();
        $defaults = compact('name', 'type', 'id', 'class');
        if ('file' != $type) {
            if (is_array($value)) {
                $params = $value;
            } elseif (is_array($params) && !isset($params['value'])) {
                $params['value'] = $value;
            } elseif (is_string($params)) {
                $params = trim($params . '&value=' . $value, '&');
            }
        }

        return '<input ' . $this->addAttributes($params, $defaults) . " />\n";
    }

    /**
     * @param  $name
     * @param  $value
     * @param  array    $params
     * @return mixed
     */
    public function inputEmail($name, $value = '', $params = [])
    {
        return $this->input('email', $name, $value, $params);
    }

    /**
     * @param  $name
     * @param  array   $params
     * @return mixed
     */
    public function inputFile($name, $params = [])
    {
        return $this->input('file', $name, null, $params);
    }

    /**
     * @param  $name
     * @param  $value
     * @param  array    $params
     * @return mixed
     */
    public function inputHidden($name, $value, $params = [])
    {
        return $this->input('hidden', $name, $value, $params);
    }

    /**
     * @param  $name
     * @param  $value
     * @param  array    $params
     * @return mixed
     */
    public function inputPassword($name, $value = '', $params = [])
    {
        return $this->input('password', $name, $value, $params);
    }

    /**
     * @param  $name
     * @param  $value
     * @param  array    $params
     * @return mixed
     */
    public function inputText($name, $value = '', $params = [])
    {
        return $this->input('text', $name, $value, $params);
    }

    /**
     * @param  $params
     * @param  null      $formData
     * @return mixed
     */
    public function open($params = null, $formData = false)
    {
        $unique = sprintf('form-%d', static::$form_id);

        $defaults = [
            'action' => $this->httpAction,
            'method' => 'post',
            'id' => $unique,
            'accept-charset' => 'utf8',
        ];

        if ($params === 'multipart/form-data') {
            $defaults['enctype'] = $params;
            $params = null;
        } elseif (true === $formData) {
            $defaults['enctype'] = 'multipart/form-data';
            if (is_array($params) && isset($params['enctype'])) {
                unset($params['enctype']);
            }
        }

        $html = '<form ' . $this->addAttributes($params, $defaults) . '>' . "\n";

        if (count($this->hiddenFields) > 0) {
            foreach ($this->hiddenFields as $hiddenName => $hiddenValue) {
                $html .= $this->inputHidden($hiddenName, $hiddenValue);
            }
        }

        return $html;
    }

    /**
     * @param $name
     * @param $value
     * @param $checked
     * @param array      $params
     */
    public function radio($name, $value = '', $checked = false, $params = [])
    {
        $id = $class = $this->generateId();
        $type = 'radio';
        $defaults = compact('name', 'value', 'id', 'class', 'type');
        if ($this->request->isMethod('post')) {
            $checked = ($value == $this->catchValue($name));
        }

        if (true === $checked) {
            $defaults['checked'] = 'checked';
        } elseif (!is_bool($checked)) {
            $params = $checked;
        }

        return '<input ' . $this->addAttributes($params, $defaults) . ($this->html5 ? ' >' : ' />') . "\n";
    }

    /**
     * @param $name
     * @param $value
     * @param array    $params
     */
    public function textarea($name, $value = '', $params = [])
    {
        $id = $class = $this->generateId();
        $defaults = compact('name', 'id', 'class');

        return '<textarea ' . $this->addAttributes($params, $defaults) . '>' . $value . '</textarea>' . "\n";
    }

    /**
     * @param $params
     * @param array     $defaults
     */
    protected function addAttributes($params, $defaults = [])
    {
        $args = [];
        if (is_array($params)) {
            $args = $params;
        } elseif (is_object($params)) {
            $args = get_object_vars($params);
        } elseif (is_string($params)) {
            parse_str($params, $args);
        }

        $attributes = array_merge((array) $defaults, $args);
        $validAttributes = [];
        if (!empty($attributes)) {
            foreach ($attributes as $attrKey => $attrVal) {
                $validAttributes[] = sprintf('%s="%s"', $attrKey, is_array($attrVal) ? implode(' ', $attrVal) : $attrVal);
            }
        }

        return implode(' ', $validAttributes);
    }

    /**
     * @return mixed
     */
    protected function generateId()
    {
        $id = 'form-' . static::$form_id . '-';

        return $id . (++$this->id);
    }
}
