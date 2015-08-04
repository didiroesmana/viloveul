<?php namespace Viloveul\Utility;

/**
 * Example :
 * 
 * $attribute = new Viloveul\Utility\HtmlAttribute;
 * $attribute->addAttr('data', 'name', 'My Name')->addAttr('title', 'Some Title');
 * $attribute->addAttr('name', 'The Name');
 * 
 * echo "<a{$attribute}">Text</a>";
 * 
 * result : <a title="Some Title" name="The Name" data-name="My Name">Text</a>
 */

class HtmlAttribute {

	protected $classes = array();

	protected $attributes = array();

	protected $dataAttributes = array();

	/**
	 * To String
	 * 
	 * @access	public
	 * @return	String
	 */

	public function __toString() {
		return $this->stringify();
	}

	/**
	 * addAttr
	 * 
	 * @access	public
	 * @param	Array|String name
	 * @param	[mixed]
	 * @return	void
	 */

	public function addAttr($data, $value = '', $param = null) {
		if ( empty($data) ) return $this;

		if ( is_string($data) ) {
			if ( $data == 'data' ) {
				$this->dataAttributes[$value] = (string) $param;
			} elseif ( $data == 'class' ) {
				$classes = is_array($value) ? $value : array_slice(func_get_args(), 1);
				foreach ( $classes as $class ) {
					$this->classes[] = $class;
				}
			} else {
				$this->addAttr(array($data => $value));
			}
			return $this;
		}

		$attributes = (array) $data;

		if ( isset($attributes['class']) ) {
			$this->classes[] = $attributes['class'];
			unset($attributes['class']);
		}

		foreach ( $attributes as $key => $val) {
			$this->attributes[$key] = $val;
		}

		return $this;
	}

	/**
	 * removeAttr
	 * 
	 * @access	public
	 * @param	String key
	 * @param	[mixed]
	 * @return	void
	 */

	public function removeAttr($data, $part = null) {
		if ( $data == 'data' && isset($this->dataAttributes[$part]) ) {
			unset($this->dataAttributes[$part]);
		} elseif ( $data == 'class' ) {
			$this->classes = empty($part) ? array() : array_diff($this->classes, array_slice(func_get_args(), 1));
		} elseif ( isset($this->attributes[$data]) ) {
			unset($this->attributes[$data]);
		}

		return $this;
	}

	/**
	 * stringify
	 * 
	 * @access	public
	 * @return	String
	 */

	public function stringify() {
		$attr = '';

		if ( ! empty($this->attributes) ) {
			foreach ( $this->attributes as $attrKey => $attrValue ) {
				$attr .= sprintf(' %s="%s"', $attrKey, $attrValue);
			}
		}

		if ( ! empty($this->classes) ) {
			$attr .= ' class="' . implode(' ', array_unique($this->classes)) . '"';
		}

		if ( ! empty($this->dataAttributes) ) {
			foreach ( $this->dataAttributes as $dataKey => $dataValue ) {
				if ( ! empty($dataValue) ) {
					$attr .= sprintf(' data-%s="%s"', $dataKey, $dataValue);
				}
			}
		}

		return $attr;
	}

}
