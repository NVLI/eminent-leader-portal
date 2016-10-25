<?php
/**
 * @file
 * Contains \Drupal\curtain_raiser\Form\curtainRaiserSettingsForm
 */
namespace Drupal\curtain_raiser\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class curtainRaiserSettingsForm extends ConfigFormBase {
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
    $config->set('inauguration_status', $form_state->getValue('curtain_raiser_inauguration_status'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
