<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2018 Aplazame
 * @license   see file: LICENSE
 */

interface Aplazame_Sdk_Http_ClientInterface
{
    /**
     * @param Aplazame_Sdk_Http_RequestInterface $request
     *
     * @return Aplazame_Sdk_Http_ResponseInterface
     *
     * @throws RuntimeException If requests cannot be performed due network issues.
     */
    public function send(Aplazame_Sdk_Http_RequestInterface $request);
}
