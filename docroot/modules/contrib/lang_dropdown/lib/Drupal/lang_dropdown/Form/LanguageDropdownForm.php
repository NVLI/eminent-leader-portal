<?php

/**
 * @file
 * Contains \Drupal\lang_dropdown\Form\LanguageDropdownForm.
 */

namespace Drupal\lang_dropdown\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Language\Language;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Language Switch Form.
 */
class LanguageDropdownForm extends FormBase {

  protected $languages;
  protected $type;
  protected $settings;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lang_dropdown_form';
  }

  /**
   * Constructs a \Drupal\lang_dropdown\Form\LanguageDropdownForm object.
   *
   * @param $languages
   *   The languages for the switcher.
   * @param $type
   *   The type of negotiation.
   * @param $settings
   *   The configuration for the switcher form.
   */
  public function __construct($languages, $type, $settings) {
    $this->languages = $languages;
    $this->type = $type;
    $this->settings = $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {

    $active = \Drupal::linkGenerator()->getActive();
    $language_url = \Drupal::languageManager()->getLanguage(Language::TYPE_URL);

    $unique_id = uniqid();

    $module_path = drupal_get_path('module', 'lang_dropdown');

    $options = $js_settings = $hidden_elements = array();
    $selected_option_language_icon = $language_selected = $language_session_selected = '';

    $form['lang_dropdown_type'] = array(
      '#type' => 'value',
      '#default_value' => $this->type,
    );
    $form['lang_dropdown_tohome'] = array(
      '#type' => 'value',
      '#default_value' => $this->settings['tohome'],
    );

    $language_objects = language_list();

    // Support Domain access
    if ($domain_locale_exists = module_exists('domain_locale')) {
      global $_domain;
      $domain_languages = domain_locale_lookup($_domain['domain_id']);
    }

    // Now we iterate on $languages to build the needed options for the select element.
    foreach ($this->languages as $lang_code => $lang_options) {

      // The language is not enabled on this domain
      if ($domain_locale_exists && !array_key_exists($lang_code, $domain_languages)) continue;

      // There is no translation for this language and not all languages are shown
      if (in_array('locale-untranslated', $lang_options['attributes']['class']) && !$this->settings['showall']) continue;

      // Build the options in an assosiative array, so it will be ready for #options in select form element.
      switch ($this->settings['display']) {
        case LANGDROPDOWN_DISPLAY_TRANSLATED:
          $options += array($lang_code => t($language_objects[$lang_code]->name));
          break;
        case LANGDROPDOWN_DISPLAY_NATIVE:
          $options += array($lang_code => t($language_objects[$lang_code]->name, array(), array('langcode' => $lang_code)));
          break;
        case LANGDROPDOWN_DISPLAY_LANGCODE:
          $options += array($lang_code => $lang_code);
          break;
        default:
          $options += array($lang_code => t($language_objects[$lang_code]->name));
          break;
      }

      // Identify selected language
      if (isset($lang_options['route_name'])) {
        $variables = array(
          'options' => array(),
        );
        if (!empty($link['language'])) {
          $variables['options']['language'] = $link['language'];
        }

        if (($lang_options['route_name'] == $active['route_name'])
        // The language of an active link is equal to the current language.
        && (empty($variables['options']['language']) || ($variables['options']['language']->id == $active['language']))
        && ($lang_options['route_parameters'] == $active['parameters'])) {
          $language_selected = $lang_code;
        }

      }
      elseif (isset($lang_options['href'])) {
        $is_current_path = ($lang_options['href'] == current_path() || ($lang_options['href'] == '<front>' && drupal_is_front_page()));
        $is_current_language = (empty($lang_options['language']) || $lang_options['language']->id == $language_url->id);
        if ($is_current_path && $is_current_language) {
          $language_selected = $lang_code;
        }
      }

      // Identify if session negotiation had set session-active class
      // the trim is needed because of a bug in locale.inc, drupal version <7.24 at least
      if (in_array('session-active', array_map('trim', $lang_options['attributes']['class']))) {
        $language_session_selected = $lang_code;
      }

      // Now we build our hidden form inputs to handle the redirections.
      $href = (isset($lang_options['href']) && $this->settings['tohome'] == 0) ? $lang_options['href'] : '<front>';
      if (!isset($lang_options['query'])) {
        $lang_options['query'] = drupal_get_query_parameters();
      }
      $hidden_elements[$lang_code] = array(
        '#type' => 'hidden',
        '#default_value' => url($href, $lang_options),
      );

      // Handle flags with Language icons module using JS widget.
      if (module_exists('languageicons') && $this->settings['widget']) {
        $languageicons_path = variable_get('languageicons_path', drupal_get_path('module', 'languageicons') . '/flags/*.png');
        $js_settings['languageicons'][$lang_code] = file_create_url(str_replace('*', $lang_code, $languageicons_path));
      }

    }

    // If session-active is set that's the selected language otherwise rely on $language_selected
    $selected_option = ($language_session_selected == '') ? $language_selected : $language_session_selected;

    // Icon for the selected language
    if (module_exists('languageicons') && !$this->settings['widget']) {
      $selected_option_language_icon = theme('languageicons_icon', array(
        'language' => (object) array('language' => $selected_option),
        'title' => $language_names[$selected_option],
      ));
    }

    // Add required files and settings for JS widget.
    if ($this->settings['widget'] == LANGDROPDOWN_MSDROPDOWN) {
      drupal_add_library('lang_dropdown', 'msdropdown');

      $js_settings += array(
        'widget' => 'msdropdown',
        'visibleRows' => $this->settings['msdropdown']['visible_rows'],
        'roundedCorner' => $this->settings['msdropdown']['rounded'],
        'animStyle' => $this->settings['msdropdown']['animation'],
        'event' => $this->settings['msdropdown']['event'],
      );

      $selected_skin = $this->settings['msdropdown']['skin'];
      if ($selected_skin == 'custom') {
        $custom_skin = check_plain($this->settings['msdropdown']['custom_skin']);
        drupal_add_css(_lang_dropdown_get_msdropdown_path() . '/css/msdropdown/' . $custom_skin . '.css');
        $js_settings += array(
          'mainCSS' => $custom_skin,
        );
      } else {
        $skins = _lang_dropdown_get_msdropdown_skins();
        $skin_data = $skins[$selected_skin];
        drupal_add_css($skin_data['file']);
        $js_settings += array(
          'mainCSS' => $skin_data['mainCSS'],
        );
      }

      drupal_add_js(array('lang_dropdown' => array( $unique_id => array('jsWidget' => $js_settings))), 'setting');

    }
    else if ($this->settings['widget'] == LANGDROPDOWN_CHOSEN) {

      drupal_add_library('lang_dropdown', 'chosen');

      $js_settings += array(
        'widget' => 'chosen',
        'disable_search' => $this->settings['chosen']['disable_search'],
        'no_results_text' => $this->settings['chosen']['no_results_text'],
      );

      drupal_add_js(array('lang_dropdown' => array( $unique_id => array('jsWidget' => $js_settings))), 'setting');

    }
    else if ($this->settings['widget'] == LANGDROPDOWN_DDSLICK) {

      drupal_add_library('lang_dropdown', 'ddslick');

      $selected_skin = $this->settings['ddslick']['skin'];
      if ($selected_skin == 'custom') {
        $custom_skin = check_plain($this->settings['ddslick']['custom_skin']);
        drupal_add_css(_lang_dropdown_get_ddslick_path() . '/' . $custom_skin . '.css');
        $ddsSkin = $custom_skin;
      } else {
        $skins = _lang_dropdown_get_ddslick_skins();
        $skin_data = $skins[$selected_skin];
        drupal_add_css($skin_data['file']);
        $ddsSkin = $selected_skin;
      }

      $js_settings += array(
        'widget' => 'ddslick',
        'width' => $this->settings['width'],
        'height' => $this->settings['ddslick']['ddslick_height'],
        'showSelectedHTML' => $this->settings['ddslick']['showSelectedHTML'],
        'imagePosition' => $this->settings['ddslick']['imagePosition'],
      );

      drupal_add_js(array('lang_dropdown' => array( $unique_id => array('jsWidget' => $js_settings))), 'setting');

    }

    ($this->settings['languageicons']['flag_position']) ? $flag_position = '#suffix' : $flag_position = '#prefix';

    // Now we build the $form array.
    $form['lang_dropdown_select'] = array(
      '#type' => 'select',
      '#default_value' => isset($selected_option) ? $selected_option : key($options),
      '#options' => $options,
      '#attributes' => array(
        'style' => 'width:' . $this->settings['width'] . 'px',
        'class' => array('lang-dropdown-select-element'),
        'id' => 'lang-dropdown-select-' . $unique_id,
      ),
      '#attached' => array(
        'js' => array($module_path . '/js/lang_dropdown.js'),
        'css' => ($this->settings['widget']) ? array() : array($module_path . '/css/lang_dropdown.css'),
      ),
    );

    if (empty($hidden_elements)) return array();

    $form += $hidden_elements;
    if (module_exists('languageicons')) {
      $form['lang_dropdown_select'][$flag_position] = $selected_option_language_icon;
    }

    $form['#attributes']['class'] = array('lang_dropdown_form', $this->type);
    $form['#attributes']['id'] = 'lang_dropdown_form_' . $unique_id;

    if ($this->settings['widget'] == LANGDROPDOWN_DDSLICK) {
      $form['#attributes']['class'][] = $ddsSkin;
    }

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Go'),
      // The below prefix & suffix for gracefull fallback if JavaScript was disabled
      '#prefix' => "<noscript><div>\n",
      '#suffix' => "\n</div></noscript>",
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {

    $language_code = check_plain($form_state['values']['lang_dropdown_select']);
    $type = $form_state['values']['lang_dropdown_type'];
    $tohome = $form_state['values']['lang_dropdown_tohome'];

    $language_codes = language_list();
    if (!array_key_exists($language_code, $language_codes)) return;

    $types = language_types_info();
    if (!array_key_exists($type, $types)) return;

    $path = drupal_is_front_page() ? '<front>' : current_path();
    $languages = language_negotiation_get_switch_links($type, $path);

    $language = $languages->links[$language_code];

    $newpath = (isset($language['href']) && $tohome == 0) ? $language['href'] : '<front>';

    if (!isset($language['query'])) {
      $language['query'] = drupal_get_query_parameters();
    }

    $form_state['redirect'] = array($newpath, $language);
  }

}
