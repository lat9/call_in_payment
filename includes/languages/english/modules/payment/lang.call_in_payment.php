<?php
// -----
// A Zen Cart payment method to accept customers' credit-card payment via a phone call.
//
// Copyright (C) 2024, Vinos de Frutas Tropicales (lat9).
//
$define = [
    'MODULE_PAYMENT_CALL_IN_PAYMENT_TEXT_TITLE' => 'Call Us With Credit Card',

    'MODULE_PAYMENT_CALL_IN_PAYMENT_TEXT_DESCRIPTION_ADMIN' => 'Customers can make a payment by calling you with their credit-card information. Their order-confirmation email will include the phone number that you provide in the payment method\'s configuration.',
    'MODULE_PAYMENT_CALL_IN_PAYMENT_TEXT_DESCRIPTION' => 'Call us with your credit-card information. For your security, <b>do not send that information via text message</b>!<br><br>Your order-confirmation email will include the phone number to call and we will ship after we confirm payment.',

    // -----
    // The '%s' will be filled in with the store-specific phone number.
    //
    'MODULE_PAYMENT_CALL_IN_PAYMENT_TEXT_EMAIL_FOOTER' => "Call us at %s to provide your credit-card information for this order.\n\nYour order will ship after we confirm payment.",
];
return $define;
