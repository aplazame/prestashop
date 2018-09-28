<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2018 Aplazame
 * @license   see file: LICENSE
 */

class Aplazame_Sdk_Http_Request implements Aplazame_Sdk_Http_RequestInterface
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var string
     */
    private $body;

    /**
     * @param string $method The HTTP method of the request.
     * @param string $uri The URI of the request.
     * @param array $headers The headers of the request.
     * @param string $body The body of the message.
     */
    public function __construct($method, $uri, array $headers = array(), $body = '')
    {
        $this->method = strtoupper($method);
        $this->uri = $uri;
        $this->headers = $headers;
        $this->body = $body;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getBody()
    {
        return $this->body;
    }
}
