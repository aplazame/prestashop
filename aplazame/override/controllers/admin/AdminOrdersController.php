<?php

class AdminOrdersController extends AdminOrdersControllerCore
{
    public function postProcess()
    {
        if (Module::isInstalled('aplazame') && Module::isEnabled('aplazame')) {
            if (Tools::isSubmit('id_order') && Tools::getValue('id_order') > 0) {
                $order = new Order(Tools::getValue('id_order'));

                if (Validate::isLoadedObject($order)) {
                    if (Tools::isSubmit('partialRefund') && isset($order)) {
                        if ($this->tabAccess['edit'] == '1') {
                            if (is_array($_POST['partialRefundProduct'])) {
                                $amount = 0;
                                $order_detail_list = array();
                                foreach ($_POST['partialRefundProduct'] as $id_order_detail => $amount_detail) {
                                    $order_detail_list[$id_order_detail] = array(
                                            'quantity' => (int)$_POST['partialRefundProductQuantity'][$id_order_detail],
                                            'id_order_detail' => (int)$id_order_detail
                                    );

                                    $order_detail = new OrderDetail((int)$id_order_detail);

                                    if (empty($amount_detail)) {
                                        $order_detail_list[$id_order_detail]['unit_price'] = $order_detail->unit_price_tax_excl;
                                        $order_detail_list[$id_order_detail]['amount'] = $order_detail->unit_price_tax_incl * $order_detail_list[$id_order_detail]['quantity'];
                                    } else {
                                        $order_detail_list[$id_order_detail]['unit_price'] = (float)str_replace(',', '.', $amount_detail / $order_detail_list[$id_order_detail]['quantity']);
                                        $order_detail_list[$id_order_detail]['amount'] = (float)str_replace(',', '.', $amount_detail);
                                    }
                                    $amount += $order_detail_list[$id_order_detail]['amount'];
                                }
                                $choosen = false;
                                $voucher = 0;

                                if ((int)Tools::getValue('refund_voucher_off') == 1) {
                                    $amount -= $voucher = (float)Tools::getValue('order_discount_price');
                                } elseif ((int)Tools::getValue('refund_voucher_off') == 2) {
                                    $choosen = true;
                                    $amount = $voucher = (float)Tools::getValue('refund_voucher_choose');
                                }

                                $shipping_cost_amount = (float)str_replace(',', '.', Tools::getValue('partialRefundShippingCost')) ? (float)str_replace(',', '.', Tools::getValue('partialRefundShippingCost')) : false;

                                if ($shipping_cost_amount > 0) {
                                    $amount += $shipping_cost_amount;
                                }

                                if ($amount > 0) {
                                    if (!Tools::isSubmit('generateDiscountRefund') && $order->module == 'aplazame') {
                                        $aplazame = ModuleCore::getInstanceByName('aplazame');
                                        $aplazame->refundAmount($order, $amount);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        parent::postProcess();
    }
}
