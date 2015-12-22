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
		if (empty($data)) return $this;

		if (is_string($data)) {
			if ( $data == 'data' ) {
				$this->dataAttributes[$value] = (string) $param;
			} elseif ( $data == 'class' ) {
				$classes = is_null($param) ? $value : array_slice(func_get_args(), 1);
				$this->addClass($classes);
			} else {
				$this->addAttr(array($data => $value));
			}
			return $this;
		}

		$attributes = (array) $data;

		if ( isset($attributes['class']) ) {
			$this->addClass($attributes['class']);
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
		if ( $data == 'data' ) {
			if ( empty($part) ) {
				$this->dataAttributes = array();
			} elseif ( isset($this->dataAttributes[$part]) ) {
				unset($this->dataAttributes[$part]);
			}
		} elseif ( $data == 'class' ) {
			$this->classes = array();
		} elseif ( isset($this->attributes[$data]) ) {
			unset($this->attributes[$data]);
		}

		return $this;
	}

	/**
	 * addClass
	 * 
	 * @access	public
	 * @param	Array|String class
	 * @param	[mixed]
	 * @return	void
	 */

	public function addClass($value) {
		if ( empty($value) )
			return $this;

		$classes = is_string($value) ? func_get_args() : (array) $value;
		foreach ( $classes as $class ) {
			$this->classes[] = (string) $class;
		}
		return $this;
	}

	/**
	 * removeClass
	 * 
	 * @access	public
	 * @param	Array|String class(es)
	 * @param	[mixed]
	 * @return	void
	 */

	public function removeClass($value) {
		if ( empty($value) || empty($this->classes) )
			return $this;

		$classes = is_string($value) ? func_get_args() : (array) $value;
		$this->classes = array_diff($this->classes, $classes);
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
			$classes = array_filter($this->classes, 'trim');
			$attr .= ' class="' . implode(' ', array_unique($classes)) . '"';
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
