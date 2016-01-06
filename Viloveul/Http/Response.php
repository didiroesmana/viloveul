<?php

namespace Viloveul\Http;

/*
 * @author      Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package     Viloveul
 * @subpackage  Http
 */

use Viloveul\Core\Configure;
use Viloveul\Core\View;

class Response
{
    protected $output = '';

    protected $contentType = 'text/html';

    protected $headers = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * clear.
     */
    public function clear()
    {
        $this->output = '';
        $this->contentType = 'text/html';
        $this->headers = array();

        if ($lvl = ob_get_level()) {
            for ($i = $lvl; $i > 0; --$i) {
                ob_flush();
            }
        }

        return $this;
    }

    /**
     * header.
     *
     * @param   string header
     * @param   bool
     */
    public function httpHeader($header, $overwrite = true)
    {
        $this->headers[] = array($header, $overwrite);

        return $this;
    }

    /**
     * setContentType.
     *
     * @param   string content_type
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * getContentType.
     *
     * @return string content_type
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * setOutput.
     *
     * @param   string data
     * @param   bool
     */
    public function setOutput($data, $apppend = false)
    {
        $output = ($data instanceof View) ? $data->render() : ((string) $data);

        $this->output = (true === $apppend) ?
            ($this->output.$output) :
                $output;

        return $this;
    }

    /**
     * getOutput.
     *
     * @return string output
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * send.
     *
     * @param   string output if any
     */
    public function send($data = null)
    {
        is_null($data) or $this->setOutput($data, true);

        if (!headers_sent()) {
            $headers = array_map(
                'unserialize',
                array_unique(
                    array_map('serialize', $this->headers)
                )
            );

            foreach ($headers as $header) {
                header($header[0], $header[1]);
            }

            @header('Content-Type: '.$this->contentType, true);
        }

        $output = $this->getOutput();

        $this->clear();

        echo $output;
    }

    /**
     * redirect.
     *
     * @param   string dynamic/static url
     *
     * @return string fixed url
     */
    public static function redirect($target)
    {
        $url = !preg_match('#^\w+\:\/\/#', $target) ?
            Configure::siteurl($target) :
                $target;

        if (!headers_sent()) {
            header("Location: {$url}");
            exit();
        }

        printf('<script type="text/javascript">window.location.href = "%s";</script>', $url);
    }
}
