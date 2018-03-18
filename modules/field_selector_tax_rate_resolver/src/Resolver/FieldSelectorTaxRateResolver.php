<?php

/**
 * @file
 * Defines FieldSelectorTaxRateResolver.php.
 */

namespace Drupal\field_selector_tax_rate_resolver\Resolver;

use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_tax\Resolver\TaxRateResolverInterface;
use Drupal\commerce_tax\TaxZone;
use Drupal\profile\Entity\ProfileInterface;

/**
 * Returns the tax rate by product tax selection field.
 */
class FieldSelectorTaxRateResolver implements TaxRateResolverInterface {

  /**
   * Machine name of field with tax rate selection.
   * Its string keys must match with ids of TaxZone::getRates().
   * @example
   *   standard|Základní 21 %
   *   reduced|Snížená 15 %
   *   super_reduced|Snížená 10 %
   *   zero|Žádná 0 %
   * @see Example of field configuration YML file
   *   field.storage.commerce_product.field_product_tax_selection.yml
   */
  const PRODUCT_TAX_SELECTION_FIELD_NAME = 'field_product_tax_selection';

  /**
   * {@inheritdoc}
   */
  public function resolve(TaxZone $zone, OrderItemInterface $order_item, ProfileInterface $customer_profile) {
    $rates = $zone->getRates();

    /** @var \Drupal\commerce_product\Entity\ProductVariation $purchasableEntity */
    $purchasableEntity = $order_item->getPurchasedEntity();

    if ($purchasableEntity) {
      /** @var \Drupal\commerce_product\Entity\Product $productEntity */
      $productEntity = $purchasableEntity->getProduct();
      $selectedTaxType = $productEntity->get(FieldSelectorTaxRateResolver::PRODUCT_TAX_SELECTION_FIELD_NAME)->value;

      foreach ($rates as $rate) {
        if ($rate->getId() == $selectedTaxType) {
          return $rate;
        }
      }
    }

    // Fallback is standard tax rate.
    return NULL;
  }

}
