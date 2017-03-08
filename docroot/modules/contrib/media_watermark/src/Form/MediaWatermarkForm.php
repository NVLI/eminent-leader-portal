<?php
/**
 * @file
 * Contains \Drupal\example\Form\ExampleForm.
 */

namespace Drupal\media_watermark\Form;

use Drupal\Core\Entity\EntityForm;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Language\LanguageInterface;


class MediaWatermarkForm extends EntityForm {

  /**
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The entity query.
   */
  public function __construct(QueryFactory $entity_query) {
    $this->entityQuery = $entity_query;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $media_watermark = $this->entity;

    $is_new = $media_watermark->isNew();

    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $media_watermark->label(),
      '#description' => $this->t("Media Watermark Label."),
      '#required' => TRUE,
    );
    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $media_watermark->id(),
      '#machine_name' => array(
        'exists' => array($this, 'exist'),
      ),
      '#disabled' => !$is_new,
    );
    $form['fid'] = array(
      '#type' => 'managed_file',
      '#title' => t('Watermark image'),
      '#upload_location' => 'public://watermark',
      '#upload_validators' => array('file_validate_extensions' => array('png gif')),
      '#description' => t('allowed files extensions are .png and .gif'),
      '#required' => TRUE,
      '#default_value' => $is_new ? '' : $media_watermark->getFid(),
    );
    $form['horizontalPosition'] = array(
      '#type' => 'select',
      '#title' => t('Horizontal position of watermark'),
      '#options' => array(
        'left' => t('left'),
        'middle' => t('middle'),
        'right' => t('right'),
      ),
      '#default_value' => $is_new ? 'left' : $media_watermark->getHorizontalPosition(),
      '#required' => TRUE,
    );
    $form['verticalPosition'] = array(
      '#type' => 'select',
      '#title' => t('Vertical position of watermark'),
      '#options' => array(
        'top' => t('top'),
        'center' => t('center'),
        'bottom' => t('bottom'),
      ),
      '#default_value' => $is_new ? 'bottom' : $media_watermark->getVerticalPosition(),
      '#required' => TRUE,
    );
    $form['horizontalMargin'] = array(
      '#type' => 'textfield',
      '#title' => t('Horizontal margin, px'),
      '#default_value' => $is_new ? '0' : $media_watermark->getHorizontalMargin(),
      '#description' => t('Set minus or plus signed value for moving watermark to left or right from defined position.'),
      '#required' => TRUE,
    );
    $form['verticalMargin'] = array(
      '#type' => 'textfield',
      '#title' => t('Vertical margin, px'),
      '#default_value' => $is_new ? '0' : $media_watermark->getVerticalMargin(),
      '#description' => t('Set minus or plus signed value for moving watermark to higher or lower from defined position.'),
      '#required' => TRUE,
    );

    // You will need additional form elements for your custom properties.

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $media_watermark = $this->entity;
    $is_new = !$media_watermark->getOriginalId();
    $id = $media_watermark->id();
    // Check for newly created watermark.
    if ($is_new) {
      // Check if id already exists.
      if (!$this->exist($media_watermark->id())) {
        // Configuration entities need an ID manually set.
        $machine_name = \Drupal::transliteration()
                               ->transliterate($media_watermark->label(),
                                 LanguageInterface::LANGCODE_DEFAULT, '_');
        $media_watermark->set('id', Unicode::strtolower($machine_name));
        $status = $media_watermark->save();
        if ($status) {
          // Add file usage.
          $fids = $form_state->getValue('fid');
          $fid = reset($fids);
          $this->addFileUsage($form_state);

          drupal_set_message($this->t('Saved the %label Media Watermark.', array(
            '%label' => $media_watermark->label(),
          )));
        }
        else {
          drupal_set_message($this->t('The %label Media Watermark was not saved.', array(
            '%label' => $media_watermark->label(),
          )));
        }
      }
      else {
        // Show message if id already exists.
          drupal_set_message($this->t('The Media Watermark with same machine name already exists %name.', array(
            '%name' => $id,
        )));
      }
    }
    else {
      $status = $media_watermark->save();
      if ($status) {
        // Process already existent watermark.
        $fids = $form_state->getValue('fid');
        $fid = reset($fids);
        $watermark_fid = $media_watermark->getFid();
        $watermark_fid = reset($watermark_fid);
        if ($fid != $watermark_fid) {
          $this->addFileUsage($form_state);
        }
      }
      else {
        drupal_set_message($this->t('The %label Media Watermark was not saved.', array(
          '%label' => $media_watermark->label(),
        )));
      }
    }

    $form_state->setRedirect('media_watermark.list');
  }

  /**
   * Helper to add file usage.
   *
   * @param FormStateInterface $form_state
   */
  protected function addFileUsage(FormStateInterface $form_state) {
    $media_watermark = $this->entity;
    $id = $media_watermark->id();
    $fids = $form_state->getValue('fid');
    if (!empty($fids) && is_array($fids)) {
      $fid = reset($fids);
      // Process file only if it is not already part of
      if (!empty($fid) && is_numeric($fid)) {
        $file = File::load($fid);
        // Add file usage.
        \Drupal::service('file.usage')->add($file, 'media_watermark', 'media_watermark', $id);
      }
    }
  }

  public function exist($id) {
    $entity = $this->entityQuery->get('media_watermark')
                                ->condition('id', $id)
                                ->execute();
    return (bool) $entity;
  }
}
?>