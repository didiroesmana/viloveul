<?php

namespace Viloveul\Http;

/*
 * @author Fajrul Akbar Zuhdi
 * @email fajrulaz@gmail.com
 */

use ArrayAccess;

class Session implements ArrayAccess
{
    /**
     * @var array
     */
    protected $flashvars = array();

    /**
     * @var string
     */
    protected $sessionName = 'zafex';

    public function &all()
    {
        return $_SESSION['__vars'];
    }

    /**
     * @param $sessionName
     */
    public function __construct($sessionName)
    {
        $defaultOptions = array(
            'name' => null,
            'lifetime' => 0,
            'path' => '/',
            'domain' => null,
            'secure' => true,
            'httponly' => false,
        );

        if (is_string($sessionName)) {
            $this->options = array_merge($defaultOptions, array('name' => $sessionName));
        } else {
            $this->options = array_merge($defaultOptions, (array) $sessionName);
        }

        if (empty($this->options['name'])) {
            $this->options['name'] = 'zafex';
        }

        $this->sessionName = $sessionName;

        register_shutdown_function('session_write_close');

        if (session_id() === '') {
            session_name($this->sessionName);
            session_start();
        }

        if (!isset($_SESSION['__vars'])) {
            $_SESSION['__vars'] = array();
        } elseif (!is_array($_SESSION['__vars'])) {
            unset($_SESSION['__vars']);
            $_SESSION['__vars'] = array();
        }

        if (isset($_SESSION['__flashdata'])) {
            $this->flashvars = (array) $_SESSION['__flashdata'];
            unset($_SESSION['__flashdata']);
        }
    }

    /**
     * @param  $data
     * @param  $value
     * @return mixed
     */
    public function createFlashdata($data, $value = null)
    {
        if (is_string($data)) {
            return $this->createFlashdata(array($data => $value));
        }

        foreach ((array) $data as $key => $val) {
            $_SESSION['__flashdata'][$key] = $val;
        }

        return $this;
    }

    /**
     * @param $name
     */
    public function delete($name)
    {
        if ($this->has($name)) {
            unset($_SESSION['__vars'][$name]);
        }
    }

    public function destroy()
    {
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        $_SESSION = array();
        session_unset();
        session_destroy();
    }

    /**
     * @param  $name
     * @param  $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return $this->has($name) ? $_SESSION['__vars'][$name] : $default;
    }

    /**
     * @param $name
     */
    public function has($name)
    {
        return array_key_exists($name, $_SESSION['__vars']);
    }

    /**
     * @param  $key
     * @return mixed
     */
    public function keepFlashdata($key)
    {
        if (array_key_exists($key, $this->flashvars)) {
            $_SESSION['__flashdata'][$key] = $this->flashvars[$key];
        }

        return $this;
    }

    /**
     * @param  $name
     * @return mixed
     */
    public function offsetExists($name)
    {
        return $this->has($name);
    }

    /**
     * @param  $name
     * @return mixed
     */
    public function offsetGet($name)
    {
        return $this->get($name, null);
    }

    /**
     * @param $name
     * @param $value
     */
    public function offsetSet($name, $value)
    {
        if (!is_null($name)) {
            $this->set(array($name => $value));
        }
    }

    /**
     * @param  $name
     * @return mixed
     */
    public function offsetUnset($name)
    {
        return $this->delete($name);
    }

    /**
     * @param $key
     * @param $default
     */
    public function readFlashdata($key, $default = null)
    {
        return array_key_exists($key, $this->flashvars) ? $this->flashvars[$key] : $default;
    }

    /**
     * @param  $data
     * @param  $value
     * @return mixed
     */
    public function set($data, $value = null)
    {
        if (is_string($data)) {
            return $this->set(array($data => $value));
        }

        foreach ((array) $data as $key => $val) {
            $_SESSION['__vars'][$key] = $val;
        }

        return $this;
    }
}
