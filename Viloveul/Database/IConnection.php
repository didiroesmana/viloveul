<?php namespace Viloveul\Database;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Database
 */

interface IConnection {

	/**
	 * Setter
	 */

	public function __set($name, $table);

	/**
	 * Getter
	 */

	public function __get($name);

	/**
	 * prepTable
	 * 
	 * @access	public
	 * @param	String
	 * @param	String
	 */

	public function prepTable($name, $protectIdentifier);

	/**
	 * protectIdentifier
	 * 
	 * @access	public
	 * @param	String
	 */

	public function protectIdentifier($name);

	/**
	 * command
	 * 
	 * @access	public
	 * @param	String
	 * @param	Array
	 */

	public function command($statement, array $params);

}
