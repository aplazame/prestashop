<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2018 Aplazame
 * @license   see file: LICENSE
 */

final class AplazameApiConfirm
{
    private static function ok()
    {
        return array(
            'status_code' => 200,
            'payload' => array(
                'status' => 'ok',
            ),
        );
    }

    private static function ko()
    {
        return array(
            'status_code' => 200,
            'payload' => array(
                'status' => 'ko',
            ),
        );
    }

    /**
     * @var Db
     */
    private $db;

    /**
     * @var bool
     */
    private $sandbox;

    /**
     * @var Aplazame
     */
    private $module;

    public function __construct(Db $db, $sandbox, Aplazame $module)
    {
        $this->db = $db;
        $this->sandbox = $sandbox;
        $this->module = $module;
    }

    public function confirm($payload)
    {
        if (!$payload) {
            return AplazameApiModuleFrontController::clientError('Payload is malformed');
        }

        if (!isset($payload['sandbox']) || $payload['sandbox'] !== $this->sandbox) {
            return AplazameApiModuleFrontController::clientError('"sandbox" not provided');
        }

        if (!isset($payload['mid'])) {
            return AplazameApiModuleFrontController::clientError('"mid" not provided');
        }
        $cartId = (int) $payload['mid'];

        $cart = new Cart($cartId);
        if (!Validate::isLoadedObject($cart)) {
            return AplazameApiModuleFrontController::not_found();
        }

        if ($cart->orderExists()) {
            $order = new Order((int) Order::getOrderByCartId((int) $cartId));
            if (Validate::isLoadedObject($cart) && ($order->module != $this->module->name)) {
                return self::ko();
            }
        }

        $amount = $cart->getOrderTotal(true);
        $currency = new Currency($cart->id_currency);
        $fraud = false;
        if ($payload['total_amount'] !== Aplazame_Sdk_Serializer_Decimal::fromFloat($amount)->jsonSerialize() ||
            $payload['currency']['code'] !== $currency->iso_code
        ) {
            $fraud = true;
        }

        switch ($payload['status']) {
            case 'pending':
                switch ($payload['status_reason']) {
                    case 'challenge_required':
                        if (!$this->module->pending($cart, $fraud)) {
                            return self::ko();
                        }
                        break;
                    case 'confirmation_required':
                        if (!$this->module->accept($cart, $fraud)) {
                            return self::ko();
                        }
                        break;
                }
                break;
            case 'ko':
                if (!$this->module->deny($cart, $fraud)) {
                    return self::ko();
                }
                break;
        }

        if ($fraud) {
            return self::ko();
        }

        return self::ok();
    }
}
