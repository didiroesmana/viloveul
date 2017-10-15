<?php

namespace Viloveul\Utility;

/**
 * @email fajrulaz@gmail.com
 * @author Fajrul Akbar Zuhdi
 */

use Viloveul\Core\Configure;
use Viloveul\Core\Factory;

class Anchor extends Factory
{
    /**
     * @var mixed
     */
    protected $autoActive = false;

    /**
     * @var mixed
     */
    protected $htmlAttribute;

    /**
     * @var string
     */
    protected $src = '#';

    /**
     * @var string
     */
    protected $text = '';

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @param  $method
     * @param  $params
     * @return mixed
     */
    public function __call($method, $params)
    {
        if (method_exists($this->htmlAttribute, $method)) {
            call_user_func_array(array(&$this->htmlAttribute, $method), $params);
        }

        return $this;
    }

    /**
     * @param $src
     * @param $text
     * @param null    $param
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
     * @return mixed
     */
    public function __toString()
    {
        return $this->show();
    }

    /**
     * @param  $value
     * @return mixed
     */
    public function autoActiveClass($value)
    {
        if (is_boolean($value)) {
            $this->autoActive = $value;
        }

        return $this;
    }

    /**
     * @param $src
     * @param $text
     * @param null    $param
     */
    public static function create($src, $text = null, $param = null)
    {
        return new static($src, $text, $param);
    }

    public function show()
    {
        if ($this->src == '#') {
            $src = '#';
        } else {
            $src = !preg_match('#^\w+\:\/\/#', $this->src) ? Configure::siteurl($this->src) : $this->src;
        }

        $text = empty($this->text) ? $src : $this->text;
        $title = empty($this->title) ? $text : $this->title;

        if ($this->autoActive === true) {
            if ($href == $this->uri->currentUrl()) {
                $this->addAttr('class', 'active');
            }
        }

        $this->addAttr('title', $title)->addAttr('href', $src);

        return "<a{$this->htmlAttribute}>{$text}</a>";
    }
}
