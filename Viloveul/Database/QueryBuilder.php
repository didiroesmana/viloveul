<?php namespace Viloveul\Database;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Database
 */

use Exception;

class QueryBuilder {

	protected $fieldset = array();

	protected $conditions = array();

	protected $selections = array();

	protected $tables = array();

	protected $relations = array();

	protected $orders = array();

	protected $limited = 0;

	protected $offset = 0;

	protected $groups = array();

	protected $havings = array();

	protected $values = array();

	protected $marker = 0;

	protected $scenario;

	/**
	 * Constructor
	 * 
	 * @access	public
	 * @param	Object scenario
	 */

	public function __construct(IConnection $scenario) {
		$this->scenario = $scenario;
	}

	/**
	 * Call
	 * 
	 * @access	public
	 * @param	String method name
	 * @param	Array parameters
	 * @return	void
	 */

	public function __call($method, $params) {
		return call_user_func_array(array($this->scenario, $method), $params);
	}

	/**
	 * clear
	 * 
	 * @access	public
	 */

	public function clear() {
		$this->fieldset = array();
		$this->conditions = array();
		$this->selections = array();
		$this->tables = array();
		$this->joins = array();
		$this->orders = array();
		$this->limited = 0;
		$this->offset = 0;
		$this->groups = array();
		$this->havings = array();
		$this->values = array();
		$this->marker = 0;

		return $this;
	}

	/**
	 * select
	 * 
	 * @access	public
	 * @param	[mixed] String|Array name
	 * @param	Boolean
	 * @return	void
	 */

	public function select($column, $protectIdentifier = true) {
		$fields = is_string($column) ?
			array_map('trim', explode(',', $column)) :
				(array) $column;

		$selections =& $this->selections;

		array_walk($fields, function($field) use(&$selections, $protectIdentifier){
			$selections[] = array($field, $protectIdentifier);
		});

		return $this;
	}

	/**
	 * from
	 * 
	 * @access	public
	 * @param	[mixed] String|Array name
	 * @param	[mixed] Boolean|String
	 * @param	Boolean protectIdentifier
	 */

	public function from($table, $prepTable = true, $protectIdentifier = true) {
		$this->tables[] = array($table, $prepTable, $protectIdentifier);
		return $this;
	}

	/**
	 * get
	 * 
	 * @access	public
	 * @param	String table
	 */

	public function get() {
		if ( count($this->tables) < 1 )
			throw new Exception('table not selected');

		if ( count($this->selections) < 1 ) {
			$selectionField = '*';
		} else {
			$selections = array();
			foreach ( $this->selections as $selection ) {
				$field = array_shift($selection);
				$pos = strpos($field, '|');
				if ( $pos !== false ) {
					$selections[] = $this->trackAliases(
						substr($field, 0, $pos),
						substr($field, $pos),
						$selection[0]
					);
				} else {
					$selections[] = (true === $selection[0]) ?
						$this->scenario->protectIdentifier($field) :
							$field;
				}
			}
			$selectionField = implode(', ', $selections);
		}

		$sql = "SELECT {$selectionField}\n ";

		$fromTable = '';
		$tables = array();
		foreach ( $this->tables as $table ) {
			$tableName = array_shift($table);
			$prepTable = array_shift($table);
			$protect = isset($table[0]) ? (boolean) $table[0] : true;
			$pos = strpos($tableName, '|');

			if ( $pos !== false ) {

				if ( true === $prepTable ) {
					$tables[] = $this->trackAliases(
						$this->scenario->prepTable(substr($field, 0, $pos), $protect),
							$this->scenario->protectIdentifier($tableName);
				} else {
					$tables[] = (true === $prepTable) ?
						$this->scenario->prepTable($tableName, false) :
							$tableName;
				}
				
			} else {

				if ( true === $prepTable ) {
					$tables[] = $this->scenario->prepTable($tableName, $protect);
				} else {
					$tables[] = (true === $protect) ?
						$this->scenario->protectIdentifier($tableName) :
							$tableName;
				}

			}
		}
		$sql .= "FROM {$fromTable}\n ";

	}

	/**
	 * join
	 * 
	 * @access	public
	 * @param	String
	 * @param	String relation
	 * @param	String join mode
	 * @return	void
	 */

	public function join($table, $on, $mode = 'inner') {
		$this->relations[] = array($table, $on, $mode);
		return $this;
	}

	/**
	 * where
	 * 
	 * @access	public
	 * @param	String|Array
	 * @param	
	 */

	/**
	 * trackAliases
	 * 
	 * @access	protected
	 * @param	String field name
	 * @param	Boolean
	 * @return	String field
	 */

	protected function trackAliases($real, $alias, $protectIdentifier = true) {
		if ( true !== $protectIdentifier ) {
			return sprintf('%s AS %s', $real, $alias);
		}
		return sprintf(
			'%s AS %s',
			$this->scenario->protectIdentifier($real),
			$this->scenario->protectIdentifier($alias)
		);
	}

}
