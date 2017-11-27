<?php

/**
 * @file
 * Defines OrderItemTypeTaxRateResolver.php.
 */

namespace Drupal\order_item_type_tax_rate_resolver\Resolver;

use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_tax\Resolver\TaxRateResolverInterface;
use Drupal\commerce_tax\TaxZone;
use Drupal\profile\Entity\ProfileInterface;

/**
 * Returns the tax zone's default tax rate.
 */
class OrderItemTypeTaxRateResolver implements TaxRateResolverInterface {

  /**
   * {@inheritdoc}
   */
  public function resolve(TaxZone $zone, OrderItemInterface $order_item, ProfileInterface $customer_profile) {
    $rates = $zone->getRates();
    $orderItemType = $order_item->bundle();

    $ratesMap = [
      'physical_goods' => 'standard',
      'digital_goods' => 'reduced',
      'services' => 'super_reduced',
      'events' => 'zero',
    ];

    if (isset($ratesMap[$orderItemType])) {
      $productRateId = $ratesMap[$orderItemType];

      $selectedRate = array_filter($rates, function ($rate) use ($productRateId) {
        return $rate->getId() == $productRateId;
      });

      if ($selectedRate) {
        return reset($selectedRate);
      }
    }

    return NULL;
  }

}
