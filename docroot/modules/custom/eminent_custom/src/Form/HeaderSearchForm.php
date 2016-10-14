<?php
/**
 * @file
 * Contains \Drupal\eminent_admin\Form\AddPlayListForm.
 */

namespace Drupal\eminent_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Play list form class.
 */
class HeaderSearchForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'eminent_custom_header_search_form';
  }

  /**
   * Form to add media item to play list.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $media_id = NULL) {
    $keyword = NULL;
    if (isset($_GET['keyword'])) {
      $keyword = $_GET['keyword'];
    }
    $form['keyword'] = [
      '#title' => t('keyword'),
      '#title_display' => FALSE,
      '#type' => 'textfield',
      '#required' => TRUE,
    ];
    $form['keyword']['#default_value'] = $keyword;
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
      '#prefix' => '<div><div class="input-group-addon">',
      '#suffix' => '</div></div>',
      '#attributes' => array(
        'id' => 'headersearch',
        'class' => array('btn btn-primary'),
      ),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $keyword = $form_state->getValue('keyword');
    $search_route = 'view.eminent_search.page_1';
    $form_state->setRedirect($search_route, ['keyword' => $keyword]);
  }

}
