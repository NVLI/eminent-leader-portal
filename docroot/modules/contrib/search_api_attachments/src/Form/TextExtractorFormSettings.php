<?php

namespace Drupal\search_api_attachments\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\search_api_attachments\TextExtractorPluginManager;

/**
 * Configuration form.
 */
class TextExtractorFormSettings extends ConfigFormBase {

  /**
   * Name of the config being edited.
   */
  const CONFIGNAME = 'search_api_attachments.admin_config';

  /**
   * Drupal\search_api_attachments\TextExtractorPluginManager service.
   */
  private $textExtractorPluginManager;

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, TextExtractorPluginManager $text_extractor_plugin_manager, EntityTypeManagerInterface $entity_type_manager, AccountProxyInterface $current_user) {
    parent::__construct($config_factory);
    $this->textExtractorPluginManager = $text_extractor_plugin_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.search_api_attachments.text_extractor'),
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return array(static::CONFIGNAME);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'search_api_attachments_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::CONFIGNAME);
    $form['extraction_method'] = array(
      '#type' => 'select',
      '#title' => $this->t('Extraction method'),
      '#description' => $this->t('Select the extraction method you want to use.'),
      '#empty_value' => '',
      '#options' => $this->getExtractionPluginInformations()['labels'],
      '#default_value' => $config->get('extraction_method'),
      '#required' => TRUE,
      '#ajax' => array(
        'callback' => array(get_class($this), 'buildAjaxTextExtractorConfigForm'),
        'wrapper' => 'search-api-attachments-extractor-config-form',
        'method' => 'replace',
        'effect' => 'fade',
      ),
    );

    $this->buildTextExtractorConfigForm($form, $form_state);
    $trigger = $form_state->getTriggeringElement();
    if (!empty($trigger['#is_button'])) {
      $this->buildTextExtractorTestResultForm($form, $form_state);
    }
    $url = Url::fromRoute('system.performance_settings')->toString();
    $form['preserve_cache'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Preserve cached extractions across cache clears.'),
      '#default_value' => $config->get('preserve_cache'),
      '#description' => $this->t('When checked, <a href=":url">clearing the sitewide cache</a> will not clear the cache of extracted files.', array(':url' => $url)),
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit and test extraction'),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config(static::CONFIGNAME);
    // If it is from the configuration.
    $extractor_plugin_id = $form_state->getValue('extraction_method');
    if ($extractor_plugin_id) {
      $configuration = $config->get($extractor_plugin_id . '_configuration');
      $extractor_plugin = $this->getTextExtractorPluginManager()->createInstance($extractor_plugin_id, $configuration);
      $extractor_plugin->validateConfigurationForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config(static::CONFIGNAME);

    // It is due to the ajax.
    $extractor_plugin_id = $form_state->getValue('extraction_method');
    if ($extractor_plugin_id) {
      $configuration = $config->get($extractor_plugin_id . '_configuration');
      $extractor_plugin = $this->getTextExtractorPluginManager()->createInstance($extractor_plugin_id, $configuration);
      $extractor_plugin->submitConfigurationForm($form, $form_state);
    }

    // Set the extraction method variable.
    $config = $this->configFactory()->getEditable(static::CONFIGNAME);
    $config->set('extraction_method', $extractor_plugin_id);
    $config->save();

    // Test the extraction.
    $file = $this->getTestFile();
    $error = '';
    $extracted_data = NULL;
    try {
      $extracted_data = $extractor_plugin->extract($file);
    }
    catch (\Exception $e) {
      $error = $e->getMessage();
    }
    $file->delete();
    if (empty($extracted_data)) {
      if (empty($error)) {
        $error = $this->t('No error message was catched');
      }
      $data = $this->t("Unfortunately, the extraction doesn't seem to work with this configuration! (@error)", array('@error' => $error));
    }
    else {
      $data = $this->t('Extracted data: %extracted_data', array('%extracted_data' => $extracted_data));
    }
    $storage = array(
      'extracted_test_text' => $data,
    );

    $form_state->setStorage($storage);
    $form_state->setRebuild();
  }

  /**
   * Get definition of Extraction plugins from their annotation definition.
   *
   * @return array
   *   Array with 'labels' and 'descriptions' as keys containing plugin ids
   *   and their labels or descriptions.
   */
  public function getExtractionPluginInformations() {
    $options = array(
      'labels' => array(),
      'descriptions' => array(),
    );
    foreach ($this->getTextExtractorPluginManager()->getDefinitions() as $plugin_id => $plugin_definition) {
      $options['labels'][$plugin_id] = Html::escape($plugin_definition['label']);
      $options['descriptions'][$plugin_id] = Html::escape($plugin_definition['description']);
    }
    return $options;
  }

  /**
   * Subform.
   *
   * It will be updated with Ajax to display the configuration of an
   * extraction plugin method.
   *
   * @param array $form
   *   The form array.
   * @param FormStateInterface $form_state
   *   The form state object.
   */
  public function buildTextExtractorConfigForm(array &$form, FormStateInterface $form_state) {
    $form['text_extractor_config'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'id' => 'search-api-attachments-extractor-config-form',
      ),
      '#tree' => TRUE,
    );
    $config = $this->config(static::CONFIGNAME);
    if ($form_state->getValue('extraction_method') != '') {
      // It is due to the ajax.
      $extractor_plugin_id = $form_state->getValue('extraction_method');
    }
    else {
      $extractor_plugin_id = $config->get('extraction_method');
      $ajax_submitted_empty_value = $form_state->getValue('form_id');
    }
    $form['text_extractor_config']['#type'] = 'details';
    $form['text_extractor_config']['#open'] = TRUE;
    // If the form is submitted with ajax and the empty value is chosen or if
    // there is no configuration yet and no extraction method was chosen in the
    // form.
    if (isset($ajax_submitted_empty_value) || $extractor_plugin_id == '') {
      $form['text_extractor_config']['#title'] = $this->t('Please make a choice');
      $form['text_extractor_config']['#description'] = $this->t('Please choose an extraction method in the list above.');
    }
    else {
      $configuration = $config->get($extractor_plugin_id . '_configuration');
      $extractor_plugin = $this->getTextExtractorPluginManager()->createInstance($extractor_plugin_id, $configuration);
      $text_extractor_form = $extractor_plugin->buildConfigurationForm(array(), $form_state);

      $form['text_extractor_config'] += $text_extractor_form;
    }
  }

  /**
   * Subform to test the configuration of an extraction plugin method.
   *
   * @param array $form
   *   The form array.
   * @param FormStateInterface $form_state
   *   The form state object.
   */
  public function buildTextExtractorTestResultForm(array &$form, FormStateInterface $form_state) {
    if (isset($form['text_extractor_config'])) {
      $extractor_plugin_id = $form_state->getValue('extraction_method');
      $form['text_extractor_config']['test_result']['#type'] = 'details';
      $form['text_extractor_config']['test_result']['#title'] = $this->t('Test extractor %plugin', array('%plugin' => $this->getExtractionPluginInformations()['labels'][$extractor_plugin_id]));
      $form['text_extractor_config']['test_result']['#open'] = TRUE;

      $storage = $form_state->getStorage();
      if (empty($storage)) {
        // Put the initial thing into the storage.
        $storage = array(
          'extracted_test_text' => $this->t("Extraction doesn't seem to work."),
        );
        $form_state->setStorage($storage);
      }
      $form['text_extractor_config']['test_result']['test_file_path_result']['#markup'] = $storage['extracted_test_text'];
    }
  }

  /**
   * Ajax callback.
   *
   * @param array $form
   *   The form array.
   * @param FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   subform
   */
  public static function buildAjaxTextExtractorConfigForm(array $form, FormStateInterface $form_state) {
    // We just need to return the relevant part of the form here.
    return $form['text_extractor_config'];
  }

  /**
   * Helper method to get/create a pdf test file and extract its data.
   *
   * The file created is then deleted after successful extraction.
   *
   * @return object
   *   $file
   */
  public function getTestFile() {
    $filepath = 'public://search_api_attachments_test_extraction.pdf';
    $values = array('uri' => $filepath);
    $file = $this->entityTypeManager->getStorage('file')->loadByProperties($values);
    if (empty($file)) {
      // Copy the source file to public directory.
      $source = drupal_get_path('module', 'search_api_attachments');
      $source .= '/data/search_api_attachments_test_extraction.pdf';
      copy($source, $filepath);
      // Create the file object.
      $file = File::create(array(
        'uri' => $filepath,
        'uid' => $this->currentUser->id(),
      ));
      $file->save();
    }
    else {
      $file = reset($file);
    }
    return $file;
  }

  /**
   * Returns the text extractor plugin manager.
   *
   * @return \Drupal\search_api_attachments\TextExtractorPluginManager
   *   The text extractor plugin manager.
   */
  protected function getTextExtractorPluginManager() {
    return $this->textExtractorPluginManager ?: \Drupal::service('plugin.manager.search_api_attachments.text_extractor');
  }

}