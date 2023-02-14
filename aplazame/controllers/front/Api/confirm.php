<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2023 Aplazame
 * @license   see file: LICENSE
 */

final class AplazameApiConfirm
{
    private static function ok(array $extra = null)
    {
        $response = array(
            'status_code' => 200,
            'payload' => array(
                'status' => 'ok',
            ),
        );

        if ($extra) {
            $response['payload'] += $extra;
        }

        return $response;
    }

    private static function ko($reason)
    {
        return array(
            'status_code' => 200,
            'payload' => array(
                'status' => 'ko',
                'reason' => $reason,
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

    public function confirm(array $queryArguments, $payload)
    {
        if (!$payload) {
            return AplazameApiModuleFrontController::client_error('Payload is malformed');
        }

        if (!isset($payload['sandbox']) || $payload['sandbox'] !== $this->sandbox) {
            return AplazameApiModuleFrontController::client_error('"sandbox" not provided');
        }

        if (!isset($payload['mid'])) {
            return AplazameApiModuleFrontController::client_error('"mid" not provided');
        }

        if (isset($queryArguments['cart_id'])) {
            $isCartIdQueryParamSet = true;
            $cartId = (int) $queryArguments['cart_id'];
        } else {
            $isCartIdQueryParamSet = false;
            $cartId = (int) $payload['mid'];
        }

        $cart = new Cart($cartId);
        if (!Validate::isLoadedObject($cart)) {
            return AplazameApiModuleFrontController::not_found();
        }

        if ($cart->orderExists()) {
            $order = $this->getOrder($cartId);
            if (Validate::isLoadedObject($cart) && ($order->module != $this->module->name)) {
                return self::ko('Aplazame is not the payment method');
            }
        }

        if (Configuration::get('APLAZAME_CREATE_ORDER_AT_CHECKOUT') !== false) {
            $amount = $cart->getOrderTotal(true);
            if ($payload['total_amount'] !== Aplazame_Sdk_Serializer_Decimal::fromFloat($amount)->jsonSerialize()) {
                if (!$this->module->deny($cart)) {
                    return self::ko("'deny' function failed (at fraud)");
                }

                return self::ko('Fraud detected');
            }
        }

        switch ($payload['status']) {
            case 'pending':
                switch ($payload['status_reason']) {
                    case 'challenge_required':
                        if (!$this->module->pending($cart)) {
                            return self::ko("'pending' function failed");
                        }

                        return self::ok($this->buildMid($isCartIdQueryParamSet, $cartId));
                    case 'confirmation_required':
                        if (!$this->module->accept($cart)) {
                            return self::ko("'accept' function failed");
                        }

                        return self::ok($this->buildMid($isCartIdQueryParamSet, $cartId));
                }
                break;
            case 'ko':
                if (!$this->module->deny($cart)) {
                    return self::ko("'deny' function failed");
                }
                break;
        }

        return self::ok();
    }

    public function getOrder($cartId)
    {
        $order = new Order((int) Order::getOrderByCartId((int) $cartId));

        return $order;
    }

    public function buildMid($isCartIdQueryParamSet, $cartId)
    {
        if (!$isCartIdQueryParamSet) {
            return null;
        }
        $order = $this->getOrder($cartId);
        $newMid = array('order_id' => $order->reference);

        return $newMid;
    }
}
