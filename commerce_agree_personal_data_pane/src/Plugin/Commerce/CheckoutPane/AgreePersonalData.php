<?php

namespace Drupal\commerce_agree_personal_data_pane\Plugin\Commerce\CheckoutPane;

use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Link;
use Drupal\user\Entity\User;

/**
 * Provides commerce checkout pane for agreement to personal data processing.
 *
 * @CommerceCheckoutPane(
 *   id = "agree_personal_data",
 *   label = @Translation("Agree to the personal data processing"),
 *   default_step = "review",
 * )
 */
class AgreePersonalData extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $defaultConfig = [
      'nid' => NULL,
      'link_text' => 'Personal data processing',
      'prefix_text' => 'I agree with the %terms',
      'revoke_agreement_text' => 'Agreement to personal data processing can be revoked anytime in your user profile.',
      'new_window' => 1,
      'user_agreement_field_name' => '',
      'user_agreement_log_field_name' => '',
    ];
    $defaultConfig += parent::defaultConfiguration();
    return $defaultConfig;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationSummary() {
    $prefix = $this->configuration['prefix_text'];
    $link_text = $this->configuration['link_text'];
    $new_window = $this->configuration['new_window'];
    $nid = $this->configuration['nid'];
    $user_agreement_field_name = $this->configuration['user_agreement_field_name'];
    $user_agreement_log_field_name = $this->configuration['user_agreement_log_field_name'];
    $revoke_agreement_text = $this->configuration['revoke_agreement_text'];
    $summary = '';
    if (!empty($prefix)) {
      $summary = $this->t('Prefix text: @text', ['@text' => $prefix]) . '<br/>';
    }
    if (!empty($link_text)) {
      $summary .= $this->t('Link text: @text', ['@text' => $link_text]) . '<br/>';
    }
    if (!empty($window_target)) {
      $window_text = ($new_window === 1) ? $this->t('New window') : $this->t('Same window');
      $summary .= $this->t('Window opens in: @opens', ['@text' => $window_text]) . '<br/>';
    }
    if (!empty($nid)) {
      $node = Node::load($nid);
      if ($node) {
        $summary .= $this->t('Personal data processing page: @title', ['@title' => $node->getTitle()]);
        $summary .= '<br/>';
      }
    }
    if (!empty($user_agreement_field_name)) {
      $summary .= $this->t('User profile agreement checkbox field: @text', ['@text' => $user_agreement_field_name]) . '<br/>';
    }
    if (!empty($user_agreement_log_field_name)) {
      $summary .= $this->t('User profile agreement log field: @text', ['@text' => $user_agreement_log_field_name]) . '<br/>';
    }
    if (!empty($revoke_agreement_text)) {
      $summary .= $this->t('Revoke agreement text: @text', ['@text' => $revoke_agreement_text]) . '<br/>';
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form,
                                         FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['prefix_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Prefix text'),
      '#default_value' => $this->configuration['prefix_text'],
      '#required' => TRUE,
    ];
    $form['link_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link text'),
      '#default_value' => $this->configuration['link_text'],
      '#required' => TRUE,
    ];
    $form['new_window'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Open window link in new window'),
      '#default_value' => $this->configuration['new_window'],
    ];
    if ($this->configuration['nid']) {
      $node = Node::load($this->configuration['nid']);
    }
    else {
      $node = NULL;
    }
    $form['nid'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Link to content'),
      '#target_type' => 'node',
      '#default_value' => $node,
      '#required' => TRUE,
    ];

    $form['user_agreement_field_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Machine name of user profile agreement field'),
      '#description' => $this->t('The field must be of checkbox type.'),
      '#default_value' => $this->configuration['user_agreement_field_name'],
      '#required' => TRUE,
    ];
    $form['user_agreement_log_field_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Machine name of user profile agreement log field'),
      '#description' => $this->t('The field must be of long text (textarea) type.'),
      '#default_value' => $this->configuration['user_agreement_log_field_name'],
      '#required' => TRUE,
    ];
    $form['revoke_agreement_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Revoke agreement instruction'),
      '#description' => $this->t('When user has already confirmed agreement, this text is displayed instead of agreement checkbox.'),
      '#default_value' => $this->configuration['revoke_agreement_text'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form,
                                            FormStateInterface $form_state) {
    parent::validateConfigurationForm($form, $form_state);
    // Check if given user profile field exists.
    $userAgreementFieldName = $form['user_agreement_field_name']['#value'];
    $userAgreementLogFieldName = $form['user_agreement_log_field_name']['#value'];
    $currentUser = \Drupal::currentUser();
    $user = User::load($currentUser->id());
    if ((!$user->getFieldDefinition($userAgreementFieldName))) {
      $form_state->setError($form['user_agreement_field_name'], t('User profile agreement field not found. Check /admin/config/people/accounts/fields for correct machine name.'));
    }
    if ((!$user->getFieldDefinition($userAgreementLogFieldName))) {
      $form_state->setError($form['user_agreement_log_field_name'], t('User profile agreement log field not found. Check /admin/config/people/accounts/fields for correct machine name.'));
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form,
                                          FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['prefix_text'] = $values['prefix_text'];
      $this->configuration['link_text'] = $values['link_text'];
      $this->configuration['user_agreement_field_name'] = $values['user_agreement_field_name'];
      $this->configuration['user_agreement_log_field_name'] = $values['user_agreement_log_field_name'];
      $this->configuration['new_window'] = $values['new_window'];
      $this->configuration['nid'] = $values['nid'];
      $this->configuration['revoke_agreement_text'] = $values['revoke_agreement_text'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form,
                                FormStateInterface $form_state,
                                array &$complete_form) {
    $userAgreementFieldName = $this->configuration['user_agreement_field_name'];
    $currentUser = \Drupal::currentUser();
    // Show pane only for registered users.
    if ($currentUser->id() != 0) {
      $user = User::load($currentUser->id());
      // If user profile field exists,
      if (($user->getFieldDefinition($userAgreementFieldName))) {
        // and user has not agreement set yet, show agreement pane.
        if ((int) $user->get($userAgreementFieldName)->value == 0) {
          $prefix_text = $this->configuration['prefix_text'];
          $link_text = $this->configuration['link_text'];
          $nid = $this->configuration['nid'];
          if ($nid) {
            $attributes = [];
            if ($this->configuration['new_window']) {
              $attributes = ['attributes' => ['target' => '_blank']];
            }
            $link = Link::createFromRoute(
              $this->t($link_text),
              'entity.node.canonical',
              ['node' => $nid],
              $attributes
            )->toString();
            $pane_form['personal_data_agreement'] = [
              '#type' => 'checkbox',
              '#default_value' => FALSE,
              '#title' => $this->t($prefix_text, ['%terms' => $link]),
              '#required' => FALSE,
              '#weight' => $this->getWeight(),
            ];
          }
        }
        else {
          $pane_form['personal_data_agreement_info'] = [
            '#markup' => $this->t($this->configuration['revoke_agreement_text']),
          ];
        }
      }
      return $pane_form;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form,
                                 FormStateInterface $form_state,
                                 array &$complete_form) {
    if (isset($pane_form['personal_data_agreement']['#value'])
      && $pane_form['personal_data_agreement']['#value'] == 1) {
      // Update User profile.
      $user_agreement_field_name = $this->configuration['user_agreement_field_name'];
      $user_agreement_log_field_name = $this->configuration['user_agreement_log_field_name'];
      $ip = \Drupal::request()->getClientIp();
      $agreement_log_record = date('d.m.Y H:i:s') . ' | IP: ' . $ip . ' | agreement confirmed';
      $agreement_log_record .= "\n";
      /** @var \Drupal\Core\Session\AccountProxyInterface $user */
      $currentUser = \Drupal::currentUser();
      // For registered users, store agreement to user profile.
      if ($currentUser->id() != 0) {
        $user = User::load($currentUser->id());
        $user->set($user_agreement_field_name, TRUE);
        $log_field_content = $user->get($user_agreement_log_field_name)->value;
        $agreement_log_record .= $log_field_content;
        $user->set($user_agreement_log_field_name, $agreement_log_record);
        $user->save();
      }
    }
  }

}
