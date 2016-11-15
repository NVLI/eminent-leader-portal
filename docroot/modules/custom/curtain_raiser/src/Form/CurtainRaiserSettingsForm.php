<?php

namespace Drupal\curtain_raiser\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure curtain raiser settings for this site.
 */
class CurtainRaiserSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'curtain_raiser_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'curtain_raiser.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('curtain_raiser.settings');

    $form['curtain_raiser_master_password'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Master Password:'),
      '#default_value' => $config->get('master_password'),
      '#description' => $this->t('Type the master password to inaugurate the site. Once inaugurated with the master password the curtain will not be shown again on reload.'),
    );

    $form['curtain_raiser_test_password'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Test Password:'),
      '#default_value' => $config->get('test_password'),
      '#description' => $this->t('This is for testing the site, inauguration status remains unchnaged.'),
    );

    $form['curtain_raiser_heading'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Heading:'),
      '#default_value' => $config->get('heading'),
      '#description' => $this->t('This will be show as the heading. eg : Coming Soon'),
    );

    $form['curtain_raiser_content'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Content:'),
      '#default_value' => $config->get('content'),
      '#description' => $this->t('The message to show in the page.'),
    );

    $form['curtain_raiser_inauguration_status'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Inaugurated'),
      '#default_value' => $config->get('inauguration_status'),
      '#description' => $this->t('Check if the site is already inaugurated, the curtain will not show anymore.'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')->getEditable('curtain_raiser.settings');
    $config->set('master_password', $form_state->getValue('curtain_raiser_master_password'))
      ->save();
    $config->set('test_password', $form_state->getValue('curtain_raiser_test_password'))
      ->save();
    $config->set('heading', $form_state->getValue('curtain_raiser_heading'))
      ->save();
    $config->set('content', $form_state->getValue('curtain_raiser_content'))
      ->save();
    $config->set('inauguration_status', $form_state->getValue('curtain_raiser_inauguration_status'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
