<?php

/**
 * @file
 * Contains Drupal\viewerjs\Plugin\Field\FieldFormatter\ViewerJsFormatter.
 */

namespace Drupal\viewerjs\Plugin\Field\FieldFormatter;

use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatterBase;
use Drupal\image\Entity\ImageStyle;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'viewer_js' formatter.
 *
 * @FieldFormatter(
 *   id = "viewerjs",
 *   module = "viewerjs",
 *   label = @Translation("ViewerJS Previewer"),
 *   field_types = {
 *     "file", "image"
 *   }
 * )
 */
class ViewerJsFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'link_type' => 'overlay',
      'template' => 'index.html',
      'overlay_size' => '1000x500',
      'preview_max_size' => '1000x700',
      'max_image_size' => 'viewerjs',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $image_styles = image_style_options(FALSE);

    $link_types = array(
      'overlay' => t('Overlay'),
      'new_window' => t('New Window'),
    );
    $element['link_type'] = array(
      '#title' => t('Preview Type'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('link_type'),
      '#options' => $link_types,
      '#required' => TRUE,
    );
    $element['template'] = array(
      '#title' => t('ViewerJs Template file'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('template'),
      '#required' => TRUE,
      '#description' => t('The template html file to use. Default index.html. The template MUST be in the Viewerjs library folder.'),
    );
    $element['overlay_size'] = array(
      '#title' => t('Overlay Size'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('overlay_size'),
      '#required' => TRUE,
      '#description' => t('The overlay size in the format widthxheight, for example 400x300.<br/>Used only when Overlay is set as Preview Type'),
    );
    $element['preview_max_size'] = array(
      '#title' => t('Max size of the preview'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('preview_max_size'),
      '#required' => TRUE,
      '#description' => t('Only for images: if the image size widthXheight is bigger that this value, the preview will use "viewerjs" image style.'),
    );
    $element['max_image_size'] = array(
      '#title' => t('Image max size'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('max_image_size'),
      '#required' => TRUE,
      '#options' => $image_styles,
      '#description' => t('Only for images: when the image is bigger than "Max size of the preview", this image style will be used to resize it.'),
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();

    if ('overlay' == $this->getSetting('link_type')) {
      $summary[] = t('Overlay size @size;', array('@size' => $this->getSetting('overlay_size')));
    }
    else {
      $summary[] = t('New Window;');
    }
    $summary[] = t('Using template @template;', array('@template' => $this->getSetting('template')));

    $size = array(
      '@size' => $this->getSetting('preview_max_size'),
      '@style' => $this->getSetting('max_image_size'),
    );
    $summary[] = t('Max preview size: @size; resized with @style image style.', $size);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    global $base_url;
    $elements = array(
      '#type' => 'markup',
      '#markup' => '',
      '#prefix' => '<div class="viewerjs-formatter-field">',
      '#suffix' => '</div>',
    );

    list($ow, $oh) = explode('x', $this->getSetting('overlay_size'));

    foreach ($items as $delta => $item) {

      $filename = $item->entity->getFilename();
      $splited_uri = explode('.', $filename);
      $extension = end($splited_uri);
      $size = format_size($item->entity->getSize());

      $viewerjs_path = viewerjs_get_viewerjs_path();
      $download_url = file_create_url($item->entity->getFileUri());
      $path = $base_url . '/' . $viewerjs_path . '/' . $this->getSetting('template') . '#' . $download_url;

      $preview_link = FALSE;
      if (in_array(strtolower($extension), $this->viewerjsSupportedExtensions())) {
        $preview_class = array('preview');
        if ('overlay' == $this->getSetting('link_type')) {
          $preview_class[] = 'viewerjs-overlay';
        }
        $preview_link = array(
          'path' => $path,
          'class' => $preview_class,
          'width' => $ow,
          'height' => $oh,
          'id' => $item->entity->id(),
        );
      }
      if ($item->isDisplayed() && $item->entity) {
        $elements[$delta] = array(
          '#theme' => 'viewerjs',
          '#file' => $item->entity,
          '#description' => $item->description,
          '#size' => $size,
          '#download_link' => $download_url,
          '#preview_link' => $preview_link,
          '#attributes' => array(),
        );
      }

    }

    if (!empty($elements)) {
      $elements['#attached']['library'][] = 'viewerjs/viewerjs';
    }

    return $elements;
  }

  /**
   * Return a list of extension supported by Viewerjs's PluginLoader.
   */
  protected function viewerjsSupportedExtensions() {
    return array(
      'pdf',
      'odt',
      'odp',
      'ods',
      'fodt',
      'jpg',
      'jpeg',
      'png',
      'gif',
    );
  }

}
