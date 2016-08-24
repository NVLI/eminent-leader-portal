<?php

/**
 * @file
 * Contains \Drupal\custom_solr_search\Form\FacetFieldsForm.
 */

namespace Drupal\custom_solr_search\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class FacetFieldsForm.
 *
 * @package Drupal\custom_solr_search\Form
 */
class FacetFieldsForm extends EntityForm {
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $facet_fields = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $facet_fields->label(),
      '#description' => $this->t("Label for the Facet fields."),
      '#required' => TRUE,
    );
    $form['fields'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Fields'),
      '#multiple' => TRUE,
      '#maxlength' => 255,
      '#default_value' => $facet_fields->get('fields'),
      '#description' => $this->t("Facet field in format <strong>label:solr_field_name</strong>. Use comma to separate more than one. <italic>Example:</italic> <strong>Author:author,Publish date:publishDate</strong>"),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $facet_fields->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\custom_solr_search\Entity\FacetFields::load',
      ),
      '#disabled' => !$facet_fields->isNew(),
    );

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $facet_fields = $this->entity;
    $status = $facet_fields->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Facet fields.', [
          '%label' => $facet_fields->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Facet fields.', [
          '%label' => $facet_fields->label(),
        ]));
    }
    $form_state->setRedirectUrl($facet_fields->urlInfo('collection'));
  }

}
