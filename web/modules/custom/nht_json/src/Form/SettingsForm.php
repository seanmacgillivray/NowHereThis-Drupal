<?php

namespace Drupal\nht_json\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure nht-json settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nht_json_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['nht_json.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['customer_subdomain'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Customer Subdomain'),
      '#default_value' => $this->config('nht_json.settings')->get('customer_subdomain'),
      '#required' => TRUE
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
//  public function validateForm(array &$form, FormStateInterface $form_state) {
//    if ($form_state->getValue('example') != 'example') {
//      $form_state->setErrorByName('example', $this->t('The value is not correct.'));
//    }
//    parent::validateForm($form, $form_state);
//  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('nht_json.settings')
      ->set('customer_subdomain', $form_state->getValue('customer_subdomain'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
