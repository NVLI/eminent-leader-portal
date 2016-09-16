<?php

namespace Drupal\pdf\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * @FieldFormatter(
 *  id = "pdf_pages",
 *  label = @Translation("PDF: Continuous scroll"),
 *  description = @Translation("Don&#039;t use this to display big PDF file."),
 *  field_types = {"file"}
 * )
 */
class PdfPages extends FormatterBase {

  public static function defaultSettings() {
    return array(
      'scale' => 1,
    ) + parent::defaultSettings();
  }

  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements['scale'] = array(
      '#type' => 'textfield',
      '#title' => t('Set the scale of PDF pages'),
      '#default_value' => $this->getSetting('scale'),
    );
    return $elements;
  }

  public function settingsSummary() {
    $summary = array();
    $scale = $this->getSetting('scale');
    if (empty($scale)) {
      $summary[] = $this->t('No settings');
    }
    else {
      $summary[] = t('Scale: @scale', array('@scale' => $scale));
    }

    return $summary;
  }

  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();
    foreach ($items as $delta => $item) {
      $filename = $item->entity->getFilename();
      if ($item->entity->getMimeType() == 'application/pdf') {
        $scale = $this->getSetting('scale');
        $file_url = file_create_url($item->entity->getFileUri());
        $html = array(
          '#type' => 'html_tag',
          '#tag' => 'div',
          //'#value' => TODO,
          '#attributes' => array(
            'class' => array('pdf-pages'),
            'id' => array('pdf-pages-' . $delta),
            'file' => array($file_url),
            'scale' => array($scale)
          ),
        );
        $elements[$delta] = array(
          '#markup' => \Drupal::service('renderer')->render($html),
        );
      }
      else {
        $elements[$delta] = array (
            '#theme' => 'file_link',
            '#file' => $item->entity,
        );
      }
    }
    $elements['#attached']['library'][] = 'pdf/drupal.pdf';
    $library = libraries_load('pdf.js');
    if ($library['loaded']) {
      $worker = file_create_url(libraries_get_path('pdf.js') . '/build/pdf.worker.js');
      $elements['#attached']['drupalSettings'] = array(
        'pdf' => array(
          'workerSrc' => $worker,
        ),
      );
    }
    else {
      $elements['#attached']['drupalSettings'] = array(
        'pdf' => array(
          'workerSrc' => 'https://mozilla.github.io/pdf.js/build/pdf.worker.js',
        ),
      );
    }
    return $elements;
  }
}
