<?php

namespace Viloveul\Utility;

/*
 * @author      Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package     Viloveul
 * @subpackage  Utility
 */

use Viloveul\Core\Object;
use Viloveul\Http\Request;
use Viloveul\Core\Configure;

class Anchor extends Object
{
    protected $src = '#';

    protected $text = '';

    protected $title = '';

    protected $autoActive = false;

    protected $htmlAttribute;

    /**
     * Constructor.
     */
    public function __construct($src, $text = null, $param = null)
    {
        $this->htmlAttribute = new HtmlAttribute();

        $this->src = $src;
        $this->text = $text;

        if (is_array($param)) {
            $this->addAttr($param);
        } else {
            $this->title = $param;
        }
    }

    /**
     * To String.
     */
    public function __toString()
    {
        return $this->show();
    }

    /**
     * Call.
     *
     * @param   string method name
     * @param   array arguments
     */
    public function __call($method, $params)
    {
        if (method_exists($this->htmlAttribute, $method)) {
            call_user_func_array(array(&$this->htmlAttribute, $method), $params);
        }

        return $this;
    }

    /**
     * show.
     *
     * @return string anchor element
     */
    public function show()
    {
        if ($this->src == '#') {
            $src = '#';
        } else {
            $src = !preg_match('#^\w+\:\/\/#', $this->src) ?
                Configure::siteurl($this->src) :
                    $this->src;
        }

        $text = empty($this->text) ? $src : $this->text;
        $title = empty($this->title) ? $text : $this->title;

        if ($this->autoActive === true) {
            if ($href == Request::currenturl()) {
                $this->addAttr('class', 'active');
            }
        }

        $this->addAttr('title', $title)->addAttr('href', $src);

        return "<a{$this->htmlAttribute}>{$text}</a>";
    }

    /**
     * autoActiveClass.
     *
     * @param   bool
     */
    public function autoActiveClass($value)
    {
        if (is_boolean($value)) {
            $this->autoActive = $value;
        }

        return $this;
    }

    /**
     * create.
     *
     * @param   string href
     *
     * @return object class
     */
    public static function create($src, $text = null, $param = null)
    {
        return self::createInstance($src, $text, $param);
    }
}
