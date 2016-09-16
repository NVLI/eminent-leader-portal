<?php

namespace Drupal\custom_solr_search\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\custom_solr_search\Entity\CustomSolrConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CustomSolrConfigForm.
 *
 * @package Drupal\custom_solr_search\Form
 */
class CustomSolrConfigForm extends EntityForm {

  /**
   * The server storage controller.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * Constructs a ServerForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->storage = $entity_type_manager->getStorage('custom_solr_config');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $container->get('entity_type.manager');
    return new static($entity_type_manager);
  }

  /**
   * Retrieves the server storage controller.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   *   The server storage controller.
   */
  protected function getStorage(){
    return $this->storage ?: \Drupal::service('entity_type.manager')->getStorage('custom_solr_config');
  }
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    // If the form is being rebuilt, rebuild the entity with the current form
    // values.
    if ($form_state->isRebuilding()) {
      $this->entity = $this->buildEntity($form, $form_state);
    }

    $form = parent::form($form, $form_state);

    /** @var \Drupal\search_api\ServerInterface $server */
    $custom_solr_config = $this->getEntity();

    // Set the page title according to whether we are creating or editing the
    // server.
    if ($custom_solr_config->isNew()) {
      $form['#title'] = $this->t('Add search server');
    }
    else {
      $form['#title'] = $this->t('Edit search server %label', array('%label' => $custom_solr_config->label()));
    }

    $this->buildEntityForm($form, $form_state, $custom_solr_config);
    
    
    return $form;
  }

  public function buildEntityForm(array &$form, FormStateInterface $form_state, CustomSolrConfig $server) {
    $form['name'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Server name'),
        '#description' => $this->t('Enter the displayed name for the server.'),
        '#default_value' => $server->label(),
        '#required' => TRUE,
    );
    $form['id'] = array(
        '#type' => 'machine_name',
        '#default_value' => $server->id(),
        '#maxlength' => 50,
        '#required' => TRUE,
        '#machine_name' => array(
            'exists' => array($this->getStorage(), 'load'),
            'source' => array('name'),
        ),
        '#disabled' => !$server->isNew(),
    );
    $form['status'] = array(
        '#type' => 'checkbox',
        '#title' => $this->t('Enabled'),
        '#description' => $this->t('Only enabled servers can index items or execute searches.'),
        '#default_value' => $server->status(),
    );
    $form['description'] = array(
        '#type' => 'textarea',
        '#title' => $this->t('Description'),
        '#description' => $this->t('Enter a description for the server.'),
        //'#default_value' => $server->getDescription(),
    );
    $form['protocall'] = array(
        '#type' => 'textarea',
        '#title' => $this->t('Description'),
        '#description' => $this->t('Enter a description for the server.'),
      //'#default_value' => $server->getDescription(),
    );
    //return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $custom_solr_config = $this->entity;
    $status = $custom_solr_config->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Custom solr config.', [
          '%label' => $custom_solr_config->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Custom solr config.', [
          '%label' => $custom_solr_config->label(),
        ]));
    }
    $form_state->setRedirectUrl($custom_solr_config->urlInfo('collection'));
  }

}
