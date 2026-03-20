<?php

declare(strict_types=1);

/**
 * Example: Working with the ps_checkpayment PrestaShop module.
 *
 * ps_checkpayment adds a "Pay by Check" payment option to checkout.
 * The order is placed but marked as "Awaiting check payment" until the
 * merchant manually confirms receipt of the check.
 *
 * This file documents common integration patterns.
 */

// --- The module implements PrestaShop's PaymentModule ---
// It is automatically rendered on the checkout payment step.

// --- Checking if an order used check payment ---
// $order  = new Order($orderId);
// $isCheck = ($order->module === 'ps_checkpayment');

// --- Hook: actionPaymentConfirmation ---
// Fired when a check payment order is confirmed by the merchant.
// Use to trigger custom post-payment logic:
//
// Hook::register('actionPaymentConfirmation', 'MyModule', 'onCheckConfirmed');
//
// public function onCheckConfirmed(array $params): void
// {
//     $order = new Order($params['id_order']);
//     if ($order->module === 'ps_checkpayment') {
//         InventoryService::reserveStock($order);
//     }
// }

// --- Transitioning order status ---
// After receiving the check, the merchant transitions the order:
//   Back Office > Orders > {Order} > Change Status > "Payment Accepted"
//
// This can also be done programmatically:
// $order  = new Order($orderId);
// $history = new OrderHistory();
// $history->id_order = $order->id;
// $history->changeIdOrderState(_PS_OS_PAYMENT_, $order);
// $history->addWithemail();

// --- Back Office configuration ---
// Modules > Pay by Check:
//   - Payable to (name on the check)
//   - Mailing address for checks
//   - Order status after check payment selected (default: "Awaiting check payment")
