<?php

/**
 * @file
 * Contains \Drupal\custom_solr_search\Form\SearchForm.
 */

namespace Drupal\custom_solr_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SearchForm.
 *
 * @package Drupal\custom_solr_search\Form
 */
class SearchForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'search_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $server = NULL, $keyword = NULL) {
    $servers = array('all' => 'ALL');
    $servers += \Drupal::service('custom_solr_search.solr_servers')->getServers();
    $form['custom_servers'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Server'),
      '#options' => $servers,
      '#default_value' => $server,
    ];
    $form['custom_search'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Search'),
      '#default_value' => empty($keyword) ? '' : $keyword,
      '#description' => $this->t('Please type the keyword to search.'),
      '#maxlength' => 64,
      '#size' => 64,
    );
    $form['search'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Search'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (strlen($form_state->getValue('custom_search')) < 3) {
      $form_state->setErrorByName('custom_search', $this->t('Please type search keyword of atleast 3 characters.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $search_keyword = $form_state->getValue('custom_search');
    $search_server = $form_state->getValue('custom_servers');
    $form_state->setRedirect('custom_solr_search.basic_search_result', array('keyword' => $search_keyword, 'server' => $search_server));
  }

}
