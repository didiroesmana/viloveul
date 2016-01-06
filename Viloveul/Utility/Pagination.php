<?php

namespace Viloveul\Utility;

/**
 * @author      Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 */

/**
 * Example :.
 * 
 * $configPagination = array(
 *    'total' => 30,                  // count all results
 *    'qs' => true,                   // true for using query string or false for uri segment
 *    'current' => 9,                 // current page : pakek $_GET['page'] kalo qs === true,
 *    'perpage' => 3,                 // limit
 *    'base' => 'http://domain.com'   // output -> http://domain.com/?page=N or http://domain.com/page/N
 * );
 * 
 * $pagination = new Viloveul\Utility\Pagination($configPagination);
 * $pagination->config('before', '<ul class="pagination pagination-md">')
 * $pagination->config('after', '</ul>')
 * 
 * echo $pagination->display('<a href=":link" class=":class">:number</a>', '<li class=":class">', '</li>');
 * 
 * result : [<<] [...] [5] [6] [7] [8] [9] [10] [>>]
 */
class Pagination
{
    protected $configs = array(
        'total' => 0,
        'current' => 0,
        'perpage' => 0,
        'numlink' => 5,
        'before' => '<ul>',
        'after' => '</ul>',
        'firstlink' => '&laquo;',
        'lastlink' => '&raquo;',
        'base' => '',
        'qs' => false,
    );

    protected $beforeLink = '';

    protected $afterLink = '';

    /**
     * Constructor.
     * 
     * @param	array configuration
     */
    public function __construct(array $params = array())
    {
        empty($params) or $this->config($params);
    }

    /**
     * config.
     * 
     * @param	string|array
     * @param	string value
     */
    public function config($name, $value = null)
    {
        if (is_string($name)) {
            return $this->config(array($name => $value));
        }

        foreach ((array) $name as $key => $val) {
            if (isset($this->configs[$key])) {
                $this->configs[$key] = $val;
            }
        }

        return $this;
    }

    /**
     * display.
     * 
     * @param	string format link
     * @param	array wrapper
     *
     * @return string output
     */
    public function display($string = '<a href=":link">:number</a>')
    {
        extract($this->configs);

        if ($total < 1 || $perpage < 1 || $total < $perpage) {
            return false;
        }

        $totalPages = (int) ceil($total / $perpage);

        if ($current < 1) {
            $current = 1;
        }

        $params = func_get_args();
        $format = array_shift($params);

        $start = (($current - $numlink) > 0) ? ($current - ($numlink - 1)) : 1;
        $end = (($current + $numlink) < $totalPages) ? $current + $numlink : $totalPages;

        $output = '';

        $this->beforeLink = array_shift($params);
        $this->afterLink = array_shift($params);

        if (false === $qs) {
            $baseurl = rtrim($base, '/').'/page/';
        } else {
            $baseurl = (strpos($base, '?') !== false) ? $base.'&page=' : rtrim($base, '/').'/?page=';
        }

        $first = $this->createElement($format, $baseurl.'1', $firstlink, 'first-page');
        if ($start > 1) {
            $first .= $this->createElement($format, '#', '...', 'disabled');
        }

        $last = $this->createElement($format, $baseurl.$end, $lastlink, 'last-page');
        if ($end != $totalPages) {
            $last = $this->createElement($format, '#', '...', 'disabled').$last;
        }

        for ($numberPage = $start; $numberPage <= $end; ++$numberPage) {
            $output .= $this->createElement(
                $format,
                $baseurl.$numberPage,
                $numberPage,
                (($numberPage == $current) ? 'active' : '')
            );
        }

        return $before.$first.$output.$last.$after;
    }

    /**
     * createElement.
     * 
     * @param	string format
     * @param	string url
     * @param	string text
     * @param	string classes
     *
     * @return string a-element
     */
    protected function createElement($format, $url, $text, $classes)
    {
        return str_replace(
            array(':link', ':number', ':class'),
            array($url, $text, $classes),
            $this->beforeLink.$format.$this->afterLink
        );
    }
}
