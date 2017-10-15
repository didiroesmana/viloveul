<?php

namespace Viloveul\Utility;

/**
 * @email fajrulaz@gmail.com
 * @author Fajrul Akbar Zuhdi
 */

/**
 * Example :.
 *
 * $attribute = new Viloveul\Utility\HtmlAttribute;
 * $attribute->addAttr('data', 'name', 'My Name')->addAttr('title', 'Some Title');
 * $attribute->addAttr('name', 'The Name');
 *
 * echo "<a{$attribute}">Text</a>";
 *
 * result : <a title="Some Title" name="The Name" data-name="My Name">Text</a>
 */
class HtmlAttribute
{
    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var array
     */
    protected $classes = [];

    /**
     * @var array
     */
    protected $dataAttributes = [];

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->stringify();
    }

    /**
     * @param  $data
     * @param  $value
     * @param  $param
     * @return mixed
     */
    public function addAttr($data, $value = '', $param = null)
    {
        if (empty($data)) {
            return $this;
        }

        if (is_string($data)) {
            if ($data == 'data') {
                $this->dataAttributes[$value] = (string) $param;
            } elseif ($data == 'class') {
                $classes = is_null($param) ? $value : array_slice(func_get_args(), 1);
                $this->addClass($classes);
            } else {
                $this->addAttr([$data => $value]);
            }

            return $this;
        }

        $attributes = (array) $data;

        if (isset($attributes['class'])) {
            $this->addClass($attributes['class']);
            unset($attributes['class']);
        }

        foreach ($attributes as $key => $val) {
            $this->attributes[$key] = $val;
        }

        return $this;
    }

    /**
     * @param  $value
     * @return mixed
     */
    public function addClass($value)
    {
        if (empty($value)) {
            return $this;
        }

        $classes = is_string($value) ? func_get_args() : (array) $value;
        foreach ($classes as $class) {
            $this->classes[] = (string) $class;
        }

        return $this;
    }

    /**
     * @param  $data
     * @param  $part
     * @return mixed
     */
    public function removeAttr($data, $part = null)
    {
        if ($data == 'data') {
            if (empty($part)) {
                $this->dataAttributes = [];
            } elseif (isset($this->dataAttributes[$part])) {
                unset($this->dataAttributes[$part]);
            }
        } elseif ($data == 'class') {
            $this->classes = [];
        } elseif (isset($this->attributes[$data])) {
            unset($this->attributes[$data]);
        }

        return $this;
    }

    /**
     * @param  $value
     * @return mixed
     */
    public function removeClass($value)
    {
        if (empty($value) || empty($this->classes)) {
            return $this;
        }

        $classes = is_string($value) ? func_get_args() : (array) $value;
        $this->classes = array_diff($this->classes, $classes);

        return $this;
    }

    /**
     * @return mixed
     */
    public function stringify()
    {
        $attr = '';

        if (!empty($this->attributes)) {
            foreach ($this->attributes as $attrKey => $attrValue) {
                $attr .= sprintf(' %s="%s"', $attrKey, $attrValue);
            }
        }

        if (!empty($this->classes)) {
            $classes = array_filter($this->classes, 'trim');
            $attr .= ' class="' . implode(' ', array_unique($classes)) . '"';
        }

        if (!empty($this->dataAttributes)) {
            foreach ($this->dataAttributes as $dataKey => $dataValue) {
                if (!empty($dataValue)) {
                    $attr .= sprintf(' data-%s="%s"', $dataKey, $dataValue);
                }
            }
        }

        return $attr;
    }
}
