<?php namespace Viloveul\Database;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Database
 */

use PDO;
use PDOException;
use Exception;

class Scenario extends PDO implements IConnection {

	protected $tables = array();

	protected $prefix;

	/**
	 * Constructor
	 */

	public function __construct($dsn, $user, $pass, $prefix = '') {
		try {
			$this->prefix = $prefix;

			parent::__construct($dsn, $user, $pass);
			parent::setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			throw new Exception(sprintf('Connection Failed : %s', $e->getMessage()));
		}
	}

	/**
	 * Setter
	 * 
	 * @access	public
	 * @param	String name
	 * @param	String table name
	 */

	public function __set($name, $table) {
		$this->tables[$name] = $table;
	}

	/**
	 * Getter
	 * 
	 * @access	public
	 * @Param	String name
	 * @return	String prefixed table name
	 */

	public function __get($name) {
		return isset($this->tables[$name]) ?
			$this->tables[$name] :
				$this->prepTable($name);
	}

	/**
	 * prepTable
	 * 
	 * @access	public
	 * @param	String table name
	 * @return	String table name with table prefix
	 */

	public function prepTable($name, $protectIdentifier = true) {
		return (true === $protectIdentifier) ?
			$this->protectIdentifier($this->prefix.$name) :
				$this->prefix.$name;
	}

	/**
	 * protectIdentifier
	 * 
	 * @access	public
	 * @param	String name
	 * @return	String protected name with identifier
	 */

	public function protectIdentifier($name) {
		if ( strpos($name, '(') !== false ) {
			preg_match('#(.+)\((.+)\)#', $name, $match);
			return $match[1].'('.$this->protectIdentifier($match[2]).')';
		}

		return implode('.', array_map(array($this, 'createIdentifier'), explode('.', $name)));
	}

	/**
	 * createIdentifier
	 * 
	 * @access	public
	 * @param	String name
	 * @return	String name
	 */

	public function createIdentifier($name) {
		$driver = $this->getAttribute(PDO::ATTR_DRIVER_NAME);

		$backtick = array('mysql', 'sqlite', 'sqlite2');

		$quote = array('pgsql', 'sqlsrv', 'dblib', 'mssql', 'sybase', 'firebird');

		if ( in_array($driver, $backtick, true) ) {
			return ($name == '*') ? '*' : "`{$name}`";
		} elseif ( in_array($driver, $quote, true) ) {
			return ($name == '*') ? '*' : "'{$name}'";
		} else {
			return $name;
		}
	}

	/**
	 * command
	 * 
	 * @access	public
	 * @param	String sql statement
	 * @param	Array bind-params
	 * @return	Object query
	 */

	public function command($statement, $params = array()) {
		try {
			$query = $this->prepare($statement);
			$query->execute($params);

			return $query;
		} catch (PDOException $e) {
			throw new Exception($e->getMessage());
		}
	}

}
