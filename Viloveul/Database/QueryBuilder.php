<?php namespace Viloveul\Database;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Database
 */

class QueryBuilder {

	protected $scenario;

	public function __construct(IConnection $scenario) {
		$this->scenario = $scenario;
	}

	public function __call($method, $params) {
		return call_user_func_array(array($this->scenario, $method), $params);
	}

}
