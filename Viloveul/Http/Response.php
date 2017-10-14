<?php

namespace Viloveul\Http;

/*
 * @author Fajrul Akbar Zuhdi
 * @email fajrulaz@gmail.com
 */

use Viloveul\Core\Configure;
use Viloveul\Core\View;

class Response
{
    /**
     * @var string
     */
    protected $contentType = 'text/html';

    /**
     * @var array
     */
    protected $headers = array();

    /**
     * @var string
     */
    protected $output = '';

    /**
     * @return mixed
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
     * @return mixed
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @return mixed
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param  $header
     * @param  $overwrite
     * @return mixed
     */
    public function httpHeader($header, $overwrite = true)
    {
        $this->headers[] = array($header, $overwrite);

        return $this;
    }

    /**
     * @param $target
     */
    public static function redirect($target)
    {
        $url = !preg_match('#^\w+\:\/\/#', $target) ? Configure::siteurl($target) : $target;

        if (!headers_sent()) {
            header("Location: {$url}");
            exit();
        }

        printf('<script type="text/javascript">window.location.href = "%s";</script>', $url);
    }

    /**
     * @param $data
     */
    public function send($data = null)
    {
        is_null($data) or $this->setOutput($data, false);

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

            @header('Content-Type: ' . $this->contentType, true);
        }

        $output = $this->getOutput();

        $this->clear();

        echo $output;
    }

    /**
     * @param  $contentType
     * @return mixed
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * @param  $data
     * @param  $apppend
     * @return mixed
     */
    public function setOutput($data, $apppend = false)
    {
        $output = ($data instanceof View) ? $data->render() : ((string) $data);

        $this->output = (true === $apppend) ? ($this->output . $output) : $output;

        return $this;
    }
}
