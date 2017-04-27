<?php
/**
 * @file
 * Contains Drupal\media_watermark\Form\BatchWatermarkForm.
 */

namespace Drupal\media_watermark\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\user\Entity\User;
use Drupal\media_watermark\Entity\MediaWatermark;



/**
 * Class BatchWatermarkForm.
 * @package Drupal\media_watermark\Form
 * @ingroup media_watermark
 */
class BatchWatermarkForm extends FormBase {

  /**
   * Property to store watermarks options info.
   *
   * @var array
   */
  protected $watermarksOptions = array();

  /**
   * Property to store watermarks file Ids.
   *
   * @var array
   */
  protected $watermarksFids = array();

  /**
   * Helper to get Watermarks options array or watermark id.
   *
   * @param null|string|int $fid
   *
   * @return array
   */
  protected function getWatermarksOptions($fid = NULL) {
    $this->watermarksOptions = &drupal_static(__CLASS__ . __FUNCTION__);

    if (!isset($this->watermarksOptions)) {
      $watermarks = MediaWatermark::loadMultiple();
      $this->watermarksOptions = MediaWatermark::prepareNames($watermarks);
    }

    return !empty($this->watermarksOptions[$fid]) ? $this->watermarksOptions[$fid] : $this->watermarksOptions;
  }



  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'media_watermark_batch';
  }


  /**
   * Define the form used for ContentEntityExample settings.
   * @return array
   *   Form definition array.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Set actions field set.
    $form['actions'] = array(
      '#type' => 'fieldset',
      '#collapsed' => FALSE,
      '#collapsible' => FALSE,
    );
    $form['actions']['search_name'] = array(
      '#type' => 'textfield',
      '#title' => t('Enter file name'),
      '#default_value' => !empty($_GET['search']) ? $_GET['search'] : '',
    );
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Search'),
      '#submit' =>  array(array($this, 'searchRedirect')),
    );
    $form['actions']['back'] = array(
      '#type' => 'submit',
      '#value' => t('Reset'),
      '#submit' =>  array(array($this, 'resetRedirect')),
    );
    // Get watermarks.
    $watermarks = MediaWatermark::loadMultiple();
    if (!empty($watermarks)) {
      $names = $this->getWatermarksOptions();
      $form['actions']['watermarks_names'] = array(
        '#type' => 'select',
        '#options' => $names,
        '#description' => t('Choose watermark you need in dropdown'),
        '#weight' => 19,
      );
      // Hide select list if one watermark.
      if (count($watermarks) == 1) {
        $form['actions']['watermarks_names']['#prefix'] = '<div class="hide-select-list">';
        $form['actions']['watermarks_names']['#suffix'] = '</div>';
      }
      $form['actions']['watermarks_images'] = $this->prepareImages($watermarks);
      $form['#attached']['library'][] =
        'media_watermark/media_watermark.scripts';

      $this->watermarksFids = array_keys($names);
    }

    $form += $this->getImageFiles();

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Add watermark'),
    );

    return $form;
  }

  /**
   * Form validation handler.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $fids = $this->checkFiles($form_state->getValue('files'));
    if (empty($fids)) {
      $form_state->setErrorByName('files', t('Please select files to add watermarks'));
    }
  }

  /**
   * Helper to check file fids.
   *
   * @param array $fids
   *   File ids array.
   *
   * @return mixed
   */
  protected function checkFiles($fids) {
    $file_fids = array();
    foreach ($fids as $key => $fid) {
      if (!empty($fid)) {
        $file_fids[] = $fid;
      }
    }

    return $file_fids;
  }

  /**
   * Form submission handler.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $batch = $this->addWatermarks($form_state);
    batch_set($batch);
  }


  /**
   * Submit callback for search button.
   */
  protected function searchRedirect(array &$form, FormStateInterface $form_state) {
    $search_name = $form_state->getValue('search_name');
    if (!empty($search_name) && is_string($search_name)) {
      $form_state->setRedirect(
        'media_watermark.batch',
        array(),
        array(
          'query' => array(
            'search' => $search_name,
          )
        )
      );
    }
  }

  /**
   * Submit callback for reset button.
   */
  protected function resetRedirect(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('media_watermark.batch');
  }

  /**
   * Get files sortable table.
   *
   * @return mixed
   */
  protected function getImageFiles() {
    $admin_access = \Drupal::currentUser()->hasPermission('administer files');

    $header = $this->buildHeader();
    $result = $this->getFiles($header);
    $files = File::loadMultiple(array_keys($result));

    $uids = array();
    foreach ($files as $file) {
      $uids[] = $file->getOwnerId();
    }

    $accounts = !empty($uids) ? User::loadMultiple(array_unique($uids)) : array();

    // Prepare the list of files.
    $options = array();
    foreach ($files as $file) {
      $file_url = parse_url($file->url());
      $options[$file->id()] = array(
        'filename' => array(
          'data' => array(
            '#type' => 'link',
            '#title' => $file->getFilename(),
            '#url' => Url::fromUserInput($file_url['path']),
            '#attributes' => array(
              'target' => '_blank',
            ),
          ),
        ),
        'image_preview' => array(
          'data' =>  array(
            '#theme' => 'image_style',
            '#width' => 100,
            '#height' => NULL,
            '#style_name' => 'media_watermark',
            '#uri' => $file->getFileUri(),
          ),
        ),
        'type' => 'image',
        'filesize' => format_size($file->getSize()),
        'author' => array(
          'data' => array(
            '#type' => 'link',
            '#title' => is_object($accounts[$file->getOwnerId()]) ? $accounts[$file->getOwnerId()]->getUsername() : 'Anonymous',
            '#url' => is_object($accounts[$file->getOwnerId()]) ? Url::fromRoute('entity.user.canonical', array('user' => $file->getOwnerId())) : Url::fromUserInput('/user/0'),
            '#attributes' => array(
              'target' => '_blank',
            ),
          ),
        ),
        'timestamp' => \Drupal::service('date.formatter')->format($file->getCreatedTime(), 'short'),
      );

      // Show a warning for files that do not exist.
      if (@!is_file($file->getFileUri())) {
        $options[$file->id()]['#attributes']['class'][] = 'error';
        if ($file->getFileUri()) {
          $options[$file->id()]['#attributes']['title'] = t('The stream wrapper for @scheme files is missing.', array('@scheme' => file_uri_scheme($file->uri)));
        }
        else {
          $options[$file->id()]['#attributes']['title'] = t('The file does not exist.');
        }
      }
    }

    // Only use a tableselect when the current user is able to perform any
    // operations.
    if ($admin_access) {
      $form['files'] =  array(
        '#type' => 'tableselect',
        '#header' => $header,
        '#options' => $options,
        '#empty' => t('No files available.'),
        '#multiple' => TRUE,
      );
    }
    // Otherwise, use a simple table.
    else {
      $form['files'] = array(
        '#theme' => 'table',
        '#header' => $header,
        '#rows' => $options,
        '#empty' => t('No files available.'),
      );
    }

    $form['pager'] = array('#type' => 'pager');

    return $form;
  }

  /**
   * Batch helper function.
   *
   * @param FormStateInterface $form_state
   *
   * @return array
   */
  protected function addWatermarks(FormStateInterface $form_state) {
    // We should not check if selected files empty because validation handle it.
    $file_fids = $this->checkFiles($form_state->getValue('files'));
    // Load files to add watermark.
    $files = File::loadMultiple($file_fids);
    // Get chosen watermark file id.
    $watermark_fid = $form_state->getValue('watermarks_names');
    // Load watermarks object.
    $watermark = MediaWatermark::load($this->getWatermarksOptions($watermark_fid));

    // Prepare batch operations array.
    foreach ($files as $file) {
      if (!empty($file)) {
        $operations[] = array(
         array('\Drupal\media_watermark\Watermark\Watermark' , 'batchCreateImage'), array($file, $watermark),
        );
      }
    }

    if (!empty($operations)) {
      $batch = array(
        'operations'       => $operations,
        'title'            => t('Adding multiple watermarks'),
        'init_message'     => t('Adding multiple watermarks is starting.'),
        'progress_message' => t('Processed @current out of @total.'),
        'error_message'    => t('Adding multiple watermarks has encountered an error.'),
      );

      return $batch;
    }
    else {
      drupal_set_message($this->t('Please select images to add watermarks'));
      return array();
    }
  }

  /**
   * Helper to get image files.
   *
   * @param array $headers
   *   table sort headers array.
   *
   * @return mixed
   */
  protected function getFiles($headers) {
    $query=\Drupal::entityQuery('file');
    $query->tableSort($headers);
    // Load only image files.
    $query->condition('filemime', 'image/%', 'LIKE');
    $query->pager(50);


    // Add condition to hide watermarks on batch add page.
    if (!empty($this->watermarksFids)) {
      $query->condition('fid', $this->watermarksFids, 'NOT IN');
    }

    if (!empty($_GET['search'])) {
      $query->condition('filename', $_GET['search'] . '%', 'LIKE');
    }

    $fids = $query->execute();

    return $fids;
  }

  /**
   * Helper to get table sort header.
   *
   * @return array
   */
  protected function buildHeader() {
    // Build the sortable table header.
    return array(
      'filename' => array(
        'data' => t('File name'),
        'field' => 'filename',
        'specifier' => 'filename',
        //'sort' => 'desc',
      ),
      'image_preview' => array(
        'data' => t('Image Preview'),
        'field' => 'image_preview',
        'specifier' => 'image_preview',
      ),
      'type' => array(
        'data' => t('Type'),
      ),
      'filesize' => array(
        'data' => t('Size'),
        'field' => 'filesize',
        'specifier' => 'filesize'
      ),
      'author' => array(
        'data' => t('Author'),
        'field' => 'uid',
        'specifier' => 'uid',
      ),
      'timestamp' => array(
        'data' => t('Updated'),
        'field' => 'created',
        'specifier' => 'created'
      ),
    );
  }

  /**
   * Helper to prepare images.
   *
   * @param $results
   *
   * @return array
   */
  protected function prepareImages($results) {
    $output = array();
    // Build render array.
    $output['images'] = array(
      '#type' => 'container',
      '#prefix' => '<div id="edit-watermarks-images">',
      '#suffix' => '</div>',
      '#weight' => 18,
    );
    foreach ($results as $value) {
      $fids = $value->getFid();
      if (!empty($fids)) {
        $fid = reset($fids);
        $file = File::load($fid);
        if (is_object($file)) {
          $output['images']['image-' . $fid] = array(
            '#theme' => 'image_style',
            '#width' => 200,
            '#height' => NULL,
            '#style_name' => 'media_watermark',
            '#uri' => $file->getFileUri(),
            '#prefix' => '<div class="image-hidden" id="image-' . $fid . '">',
            '#suffix' => '</div>',
          );
        }
      }
    }

    return $output;
  }

}
