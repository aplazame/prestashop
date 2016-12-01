<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2016 Aplazame
 * @license   see file: LICENSE
 */

/**
 * Decimal Type.
 */
class Aplazame_Sdk_Serializer_Decimal implements Aplazame_Sdk_Serializer_JsonSerializable
{
    public static function fromFloat($value)
    {
        return new self((int) number_format($value, 2, '', ''));
    }

    /**
     * @var null|int
     */
    public $value;

    /**
     * @param int $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return float
     */
    public function asFloat()
    {
        return $this->value / 100;
    }

    public function jsonSerialize()
    {
        return $this->value;
    }
}
