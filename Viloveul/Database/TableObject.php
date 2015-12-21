<?php namespace Viloveul\Database;

class TableObject {

	protected $name;

	public function __construct($name) {
		$this->name = $name;
	}

	public function __toString() {
		return $this->name;
	}

}
