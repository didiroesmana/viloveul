<?php namespace Viloveul\Utility;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Utility
 */

class Pagination {

	protected $configs = array(
		'total' => 0,
		'current' => 0,
		'perpage' => 0,
		'numlink' => 5,
		'before' => '<ul>',
		'after' => '</ul>',
		'base' => '',
		'qs' => false
	);

	/**
	 * Constructor
	 * 
	 * @access	public
	 * @param	Array configuration
	 */

	public function __construct(array $params = array()) {
		empty($params) or $this->config($params);
	}

	/**
	 * config
	 * 
	 * @access	public
	 * @param	String|Array
	 * @param	String value
	 */

	public function config($name, $value = null) {
		if ( is_string($name) ) {
			return $this->config(array($name => $value));
		}

		foreach ( (array) $name as $key => $val ) {
			if ( isset($this->configs[$key]) ) {
				$this->configs[$key] = $val;
			}
		}

		return $this;
	}

	/**
	 * display
	 * 
	 * @access	public
	 * @param	String format link
	 * @param	Array wrapper
	 * @return	String output
	 */

	public function display($string = '<a href=":link">:number</a>') {
		extract($this->configs);

		if ( $total < 1 || $perpage < 1 || $total < $perpage) {
			return false;
		}

		$totalPages = (int) ceil($total/$perpage);

		if ( $current < 1 ) {
			$current = 1;
		}

		$params = func_get_args();
		$format = array_shift($params);

		$start = (($current - $numlink) > 0) ? ($current - ($numlink - 1)) : 1;
		$end = (($current + $numlink) < $totalPages) ? $current + $numlink : $totalPages;

		$output = '';

		$beforeLink = array_shift($params);
		$afterLink = array_shift($params);

		if ( false === $qs ) {
			$baseUrl = rtrim($base, '/') . '/';
		} else {
			$baseUrl = (strpos($base, '?') !== false) ? $base.'&page=' : $base.'?page=';
		}

		$first = $beforeLink.str_replace(
			array(':link', ':number', ':class'),
			array($baseUrl.'1', '&laquo;', 'first'),
			$format
		).$afterLink;

		$last = $beforeLink.str_replace(
			array(':link', ':number', ':class'),
			array($baseUrl.$end, '&raquo;', 'last'),
			$format
		).$afterLink;

		if ( $start > 1 ) {
			$output .= str_replace(
				array(':link', ':number', ':class'),
				array('#', '...', 'disabled'),
				$beforeLink.$format.$afterLink
			);
		}

		for ( $i = $start; $i <= $end; $i++ ) {
			$class = ($i == $current) ? 'active' : '';
			$output .= str_replace(
				array(':link', ':number', ':class'),
				array($baseUrl.$i, $i, $class),
				$beforeLink.$format.$afterLink
			);
		}

		if ( $end != $totalPages ) {
			$output .= str_replace(
				array(':link', ':number', ':class'),
				array('#', '...', 'disabled'),
				$beforeLink.$format.$afterLink
			);
		}

		return $before.$first.$output.$last.$after;
	}

}