<?php

/**
 * @file
 * Contains \Drupal\custom_solr_search\Form\CustomSolrSearchFilterQueryForm.
 */

namespace Drupal\custom_solr_search\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\custom_solr_search\SolrServerDetails;
/**
 * Class CustomSolrSearchFilterQueryForm.
 *
 * @package Drupal\custom_solr_search\Form
 */
class CustomSolrSearchFilterQueryForm extends EntityForm {

  public function __construct(){

  }
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $server_details = \Drupal::service('custom_solr_search.solr_servers');
    // Get the Core Details.
    $servers = array('all' => 'ALL');
    $servers += $server_details->getServers();
    $custom_solr_search_filter_query = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Query Settings name'),
      '#maxlength' => 255,
      '#default_value' => $custom_solr_search_filter_query->label(),
      '#description' => $this->t("Label for the Custom solr search filter query."),
      '#required' => TRUE,
    );
    $form['server'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Server Name'),
      '#options' => $servers,
      '#default_value' =>  $custom_solr_search_filter_query->get('server'),
    ];
    $form['filter'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Filters'),
      '#description' => $this->t('Add the multiple filter identifier with AND/OR operator in SOLR based on you can differentiate the queries.e.g. (format:"Note" OR format:"Article") AND (id: "ir-10054-5936").'),
      '#default_value' =>  $custom_solr_search_filter_query->get('filter'),
    ];
    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $custom_solr_search_filter_query->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\custom_solr_search\Entity\CustomSolrSearchFilterQuery::load',
      ),
      '#disabled' => !$custom_solr_search_filter_query->isNew(),
    );

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $custom_solr_search_filter_query = $this->entity;
    $status = $custom_solr_search_filter_query->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Custom solr search filter query.', [
          '%label' => $custom_solr_search_filter_query->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Custom solr search filter query.', [
          '%label' => $custom_solr_search_filter_query->label(),
        ]));
    }
    $form_state->setRedirectUrl($custom_solr_search_filter_query->urlInfo('collection'));
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $custom_solr_search_filter_query = $this->entity;
    $status = $custom_solr_search_filter_query->save();
    parent::submitForm($form, $form_state);
  }

}


