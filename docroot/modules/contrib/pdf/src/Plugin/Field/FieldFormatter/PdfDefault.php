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
    return [
      'keep_pdfjs' => TRUE,
      'width' => '100%',
      'height' => '',
    ] + parent::defaultSettings();
  }

  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['keep_pdfjs'] = [
      '#type' => 'checkbox',
      '#title' => t('Always use pdf.js'),
      '#default_value' => $this->getSetting('keep_pdfjs'),
      '#description' => t("Use pdf.js even the browser has Adobe Reader Plugin, WebKit PDF Reader for Safari or the PDF Reader for Chrome (Chrome's default alternative to the Adobe Reader Plugin) installed."),
    ];

    $elements['width'] = [
      '#type' => 'textfield',
      '#title' => 'Width',
      '#default_value' => $this->getSetting('width'),
      '#description' => t('Width of the viewer. Ex: 250px or 100%'),
    ];

    $elements['height'] = [
      '#type' => 'textfield',
      '#title' => 'Height',
      '#default_value' => $this->getSetting('height'),
      '#description' => t('Height of the viewer. Ex: 250px or 100%'),
    ];
    return $elements;
  }

  public function settingsSummary() {
    $summary = [];

    $keep_pdfjs = $this->getSetting('keep_pdfjs');
    $width = $this->getSetting('width');
    $height = $this->getSetting('height');
    if (empty($keep_pdfjs) && empty($width) && empty($height)) {
      $summary[] = $this->t('No settings');
    }
    else {
      $summary[] = t('Use pdf.js even users have PDF reader plugin: @keep_pdfjs', ['@keep_pdfjs' => $keep_pdfjs ? t('Yes') : t('No')]) . '. ' . t('Widht: @width , Height: @height', [
          '@width' => $width,
          '@height' => $height
        ]);
    }

    return $summary;
  }

  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      if ($item->entity->getMimeType() == 'application/pdf') {
        $file_url = file_create_url($item->entity->getFileUri());
        $iframe_src = file_create_url('/libraries/pdf.js/web/viewer.html') . '?file=' . rawurlencode($file_url);
        $html = [
          '#theme' => 'file_pdf',
          '#attributes' => [
            'class' => ['pdf'],
            'webkitallowfullscreen' => '',
            'mozallowfullscreen' => '',
            'allowfullscreen' => '',
            'frameborder' => 'no',
            'width' => $this->getSetting('width'),
            'height' => $this->getSetting('height'),
            'src' => $iframe_src,
            'data-src' => $file_url,
          ],
        ];
        $elements[$delta] = $html;
      }
      else {
        $elements[$delta] = [
          '#theme' => 'file_link',
          '#file' => $item->entity,
        ];
      }
    }
    if ($this->getSetting('keep_pdfjs') != TRUE) {
      $elements['#attached']['library'][] = 'pdf/default';
    }
    return $elements;
  }
}
