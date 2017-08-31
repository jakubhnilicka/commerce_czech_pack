<?php

namespace Drupal\commerce_czech_tax\Plugin\Commerce\TaxType;

use Drupal\commerce_tax\Plugin\Commerce\TaxType\LocalTaxTypeBase;
use Drupal\commerce_tax\TaxZone;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the Swiss VAT tax type.
 *
 * @CommerceTaxType(
 *   id = "czech_vat",
 *   label = "Czech VAT",
 * )
 */
class CzechVat extends LocalTaxTypeBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['rates'] = $this->buildRateSummary();
    // Replace the phrase "tax rates" with "VAT rates" to be more precise.
    $form['rates']['#markup'] = $this->t('The following VAT rates are provided:');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildZones() {
    $zones = [];
    $zones['cz'] = new TaxZone([
      'id' => 'cz',
      'label' => $this->t('Czech'),
      'display_label' => $this->t('VAT'),
      'territories' => [
        ['country_code' => 'CZ'],
      ],
      'rates' => [
        [
          'id' => 'standard',
          'label' => $this->t('Standard'),
          'amounts' => [
            ['amount' => '0.21', 'start_date' => '2011-01-01'],
          ],
          'default' => TRUE,
        ],
        [
          'id' => 'reduced',
          'label' => $this->t('Reduced'),
          'amounts' => [
            ['amount' => '0.15', 'start_date' => '2011-01-01'],
          ],
        ],
        [
          'id' => 'superreduced',
          'label' => $this->t('Super Reduced'),
          'amounts' => [
            ['amount' => '0.10', 'start_date' => '2011-01-01'],
          ],
        ],
        [
          'id' => 'zero',
          'label' => $this->t('Without Tax'),
          'amounts' => [
            ['amount' => '0', 'start_date' => '2011-01-01'],
          ],
        ],
      ],
    ]);

    return $zones;
  }

}
