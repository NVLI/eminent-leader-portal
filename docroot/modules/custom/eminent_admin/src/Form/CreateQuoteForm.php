<?php

namespace Drupal\eminent_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\node\Entity\Node;
use Drupal\Component\Utility\Unicode;

/**
 * Create quote form.
 */
class CreateQuoteForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'eminent_admin_create_quote_form';
  }

  /**
   * Form to create quote.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $category_options = array();
    // Load the quote category vocabulary.
    $quotes_category = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('subject_classification');
    foreach ($quotes_category as $category) {
      $category_options[$category->tid] = $category->name;
    }
    $form['description'] = [
      '#title' => t('Quote'),
      '#type' => 'textarea',
      '#required' => TRUE,
      '#description' => t('Enter the quote here'),
    ];
    $form['category'] = [
      '#title' => t('Category'),
      '#type' => 'select',
      '#options' => $category_options,
      '#empty_option' => t('Select Category'),
    ];
    $form['featured'] = [
      '#title' => t('Mark as featured'),
      '#type' => 'checkbox',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Quote'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $description = $form_state->getValue('description');
    // Set node title.
    $title = Unicode::truncate($description, 10);
    $featured = $form_state->getValue('featured');
    $category = $form_state->getValue('category');

    // Create node object.
    $node = Node::create([
      'type' => 'quote',
      'body' => $description,
      'title'  => $title,
      'field_quote_featured' => $featured,
      'field_category' => $category,
    ]);
    $node->save();
    $quotes_route = 'view.quotes.page_1';
    $form_state->setRedirect($quotes_route);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'eminent_admin.settings',
    ];
  }

}
