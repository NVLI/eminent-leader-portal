<?php

namespace Drupal\pdf\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * @FieldFormatter(
 *  id = "pdf_default",
 *  label = @Translation("PDF: Default viewer of PDF.js"),
 *  description = @Translation("Use the default viewer like http://mozilla.github.io/pdf.js/web/viewer.html."),
 *  field_types = {"file"}
 * )
 */
class PdfDefault extends FormatterBase {

  public static function defaultSettings() {
    return array(
      'keep_pdfjs' => true,
      'width' => '100%',
      'height' => '',
    ) + parent::defaultSettings();
  }

  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['keep_pdfjs'] = array(
      '#type' => 'checkbox',
      '#title' => t('Always use pdf.js'),
      '#default_value' => $this->getSetting('keep_pdfjs'),
      '#description' => t("Use pdf.js even the browser has Adobe Reader Plugin, WebKit PDF Reader for Safari or the PDF Reader for Chrome (Chrome's default alternative to the Adobe Reader Plugin) installed."),
    );

    $elements['width'] = array(
      '#type' => 'textfield',
      '#title' => 'Width',
      '#default_value' => $this->getSetting('width'),
      '#description' => t('Width of the viewer. Ex: 250px or 100%'),
    );

    $elements['height'] = array(
      '#type' => 'textfield',
      '#title' => 'Height',
      '#default_value' => $this->getSetting('height'),
      '#description' => t('Height of the viewer. Ex: 250px or 100%'),
    );
    return $elements;
  }

  public function settingsSummary() {
    $summary = array();

    $keep_pdfjs = $this->getSetting('keep_pdfjs');
    $width = $this->getSetting('width');
    $height = $this->getSetting('height');
    if (empty($keep_pdfjs) && empty($width) && empty($height)) {
      $summary[] = $this->t('No settings');
    }
    else {
      $summary[] = t('Use pdf.js even users have PDF reader plugin: @keep_pdfjs', array('@keep_pdfjs' => $keep_pdfjs ? t('Yes') : t('No'))) . '. ' . t('Widht: @width , Height: @height', array('@width' => $width , '@height' => $height) );
    }

    return $summary;
  }

  public function viewElements(FieldItemListInterface $items, $langcode) {
    $library = libraries_load('pdf.js');
    $elements = array();
    if ($library['loaded']) {
      foreach ($items as $delta => $item) {
        if ($item->entity->getMimeType() == 'application/pdf') {
          $file_url = file_create_url($item->entity->getFileUri());
          $iframe_src = file_create_url(libraries_get_path('pdf.js') . '/web/viewer.html') . '?file=' . rawurlencode($file_url);
          $force_pdfjs = $this->getSetting('keep_pdfjs');
          $html = array(
            '#type' => 'html_tag',
            '#tag' => 'iframe',
            '#value' => $file_url,
            '#attributes' => array(
              'class' => array('pdf'),
              'webkitallowfullscreen' => '',
              'mozallowfullscreen' => '',
              'allowfullscreen' => '',
              'frameborder' => 'no',
              'width' => $this->getSetting('width'),
              'height' => $this->getSetting('height'),
              'src' => $iframe_src,
              'data-src' => $file_url,
            ),
          );
          $elements[$delta] = array('#markup' => \Drupal::service('renderer')->render($html));
        }
        else {
          $elements[$delta] = array (
              '#theme' => 'file_link',
              '#file' => $item->entity,
          );
        }
      }
      if ($force_pdfjs != TRUE) {
        $elements['#attached']['library'][] = 'pdf/default';
      }
    }
    else {
      drupal_set_message($library['error message'], 'error');
      $elements[] = array(
        '#markup' => t('Please download and install ') . \Drupal::l( $library['name'], Url::fromUri($library['download url'])) . '!'
      );
    }
    return $elements;
  }
}
