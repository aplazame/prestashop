<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2019 Aplazame
 * @license   see file: LICENSE
 */

/**
 * Exception thrown when there is communication possible with the API.
 */
class Aplazame_Sdk_Api_ApiCommunicationException extends RuntimeException implements Aplazame_Sdk_Api_AplazameExceptionInterface
{
    /**
     * @param Exception $exception
     *
     * @return Aplazame_Sdk_Api_ApiCommunicationException
     */
    public static function fromException(Exception $exception)
    {
        return new self($exception->getMessage(), $exception->getCode(), $exception);
    }
}
