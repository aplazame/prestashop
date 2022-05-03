<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2022 Aplazame
 * @license   see file: LICENSE
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_6_0_2(Aplazame $module)
{
    $orderStatePending = Configuration::get($module::ORDER_STATE_PENDING);
    $order_state = new OrderState($orderStatePending);

    if ($orderStatePending && Validate::isLoadedObject($order_state)) {
        $order_state->unremovable = true;
        $order_state->save();
    }

    return true;
}
