<?php
// Paypal Shopping Cart plugin for Siteframe
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
// $Id: shoppingcart.php,v 1.5 2003/12/22 04:28:37 glen Exp $

$PRINT_SIZES = array(
  array( active => 1,   item => '4x6 print',    price => 8.00 ),
  array( active => 1,   item => '5x7 print',    price => 12.00 ),
  array( active => 1,   item => '8x10 print',   price => 20.00 ),
);

$SHIPPING_HANDLING = 5.00;

// -- DO NOT EDIT BELOW THIS LINE --

$ShoppingCart = new Plugin('PaypalShoppingCart');

// PAYPAL_CART_ENABLE
// global property determines whether or not this feature is activated
$ShoppingCart->set_global(
  'Paypal Shopping Cart Plugin',
  'PAYPAL_CART_ENABLE',
  array(
    type => 'checkbox',
    rval => 1,
    prompt => 'Enable Shopping Cart',
    doc => 'Check to enable the Paypal Shopping Cart functionality for image sales'
  )
);

// PAYPAL_CART_EMAIL
// the e-mail address of the associated Paypal account
$ShoppingCart->set_global(
  'Paypal Shopping Cart Plugin',
  'PAYPAL_CART_EMAIL',
  array(
    type => 'text',
    size => 250,
    prompt => 'Paypal E-mail address',
    doc => 'Enter the e-mail address of your Paypal account (note: your Paypal account must be established separately from this website).'
  )
);

// paypal_cart_image_sell
// determines if a specific image is for sale
if ($PAYPAL_CART_ENABLE) {
  $ShoppingCart->set_input_property(
    'Image',
    array(
      name => 'paypal_cart_image_dont_sell',
      type => 'checkbox',
      rval => 1,
      prompt => 'Check to NOT sell this image',
      doc => 'By default, if the Paypal Image Cart option is enabled, all images will be made available for sell; by checking this box, you can make this image unavailable (for example, an image that is used for site purposes, but not for prints).',
      fcn_val => 'shoppingCartValidate'
    )
  );
  $ShoppingCart->set_output_property(
    'Image',
    array(
      name => 'paypal_cart_handling',
      callback => 'shoppingCartHandling'
    )
  );
  $ShoppingCart->set_input_property(
    'Document',
    array(
      name => 'paypal_single_item_price',
      type => 'text',
      size => 5,
      prompt => 'Enter price to sell single copies of this item',
      doc => 'If this item is not using the global image prices, then enter a price for the item here. If this box has a numeric value, then users will be able to order multiple copies of this item at the price specified.',
      fcn_val => 'shoppingCartSingleItem'
    )
  );
}
// validation function for paypal_cart_image_sell
function shoppingCartValidate(&$obj, $property, $value) {
  return 1;
}
function shoppingCartSingleItem(&$obj, $property, $value) {
  $value = $value+0;
  if ($value < 0)
    return 0;
  else
    return 1;
}
function shoppingCartHandling() {
  global $SHIPPING_HANDLING;
  return $SHIPPING_HANDLING;
}

// autoblock to generate print sizes and prices
$ShoppingCart->set_autoblock( 'paypal_cart_items', 'paypal_cart_items');
function paypal_cart_items() {
  global $PRINT_SIZES;
  return $PRINT_SIZES;
}

// register the plugin (MANDATORY)
$ShoppingCart->register();

?>
