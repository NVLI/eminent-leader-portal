<?php

/**
 * @file
 * Contains \Drupal\lang_dropdown\Plugin\Block\LanguageDropdownBlock.
 */

namespace Drupal\lang_dropdown\Plugin\Block;

use Drupal\Component\Utility\String;
use Drupal\block\BlockBase;
use Drupal\block\Annotation\Block;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Session\AccountInterface;
use Drupal\lang_dropdown\Form\LanguageDropdownForm;

/**
 * Provides a 'Language dropdown switcher' block.
 *
 * @Block(
 *   id = "language_dropdown_block",
 *   admin_label = @Translation("Language dropdown switcher"),
 *   category = @Translation("System"),
 *   derivative = "Drupal\lang_dropdown\Plugin\Derivative\LanguageDropdownBlock"
 * )
 */
class LanguageDropdownBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'showall' => 0,
      'tohome' => 0,
      'width' => 165,
      'display' => LANGDROPDOWN_DISPLAY_NATIVE,
      'widget' => LANGDROPDOWN_SIMPLE_SELECT,
      'msdropdown' => array(
        'visible_rows' => 5,
        'rounded' => 1,
        'animation' => 'slideDown',
        'event' => 'click',
        'skin' => 'ldsSkin',
        'custom_skin' => '',
      ),
      'chosen' => array(
        'disable_search' => 1,
        'no_results_text' => t('No language match'),
      ),
      'ddslick' => array(
        'ddslick_height' => 0,
        'showSelectedHTML' => 1,
        'imagePosition' => LANGDROPDOWN_DDSLICK_LEFT,
        'skin' => 'ddsDefault',
        'custom_skin' => '',
      ),
      'languageicons' => array(
        'flag_position' => LANGDROPDOWN_FLAG_POSITION_AFTER,
      ),
      'hidden_languages' => array(),
    );
  }

  /**
   * {@inheritdoc}
   */
  function access(AccountInterface $account) {
    return language_multilingual();
  }

  /**
   * Overrides \Drupal\block\BlockBase::blockForm().
   */
  public function blockForm($form, &$form_state) {

    $form['lang_dropdown'] = array(
      '#type' => 'fieldset',
      '#title' => t('Language switcher dropdown settings'),
      '#weight' => 1,
      '#tree' => TRUE,
    );

    $form['lang_dropdown']['showall'] = array(
      '#type' => 'checkbox',
      '#title' => t('Show all enabled languages'),
      '#description' => t('Show all languages in the switcher no matter if there is a translation for the node or not. For languages without translation the switcher will redirect to homepage.'),
      '#default_value' => $this->configuration['showall'],
    );

    $form['lang_dropdown']['tohome'] = array(
      '#type' => 'checkbox',
      '#title' => t('Redirect to home on switch'),
      '#description' => t('When you change language the switcher will redirect to homepage.'),
      '#default_value' => $this->configuration['tohome'],
    );

    $form['lang_dropdown']['width'] = array(
      '#type' => 'number',
      '#title' => t('Width of dropdown element'),
      '#size' => 8,
      '#maxlength' => 3,
      '#required' => TRUE,
      '#field_suffix' => 'px',
      '#default_value' => $this->configuration['width'],
    );

    $form['lang_dropdown']['display'] = array(
      '#type' => 'select',
      '#title' => t('Display format'),
      '#options' => array(
        LANGDROPDOWN_DISPLAY_TRANSLATED => t('Translated into Current Language'),
        LANGDROPDOWN_DISPLAY_NATIVE => t('Language Native Name'),
        LANGDROPDOWN_DISPLAY_LANGCODE => t('Language Code'),
      ),
      '#default_value' => $this->configuration['display'],
    );

    $form['lang_dropdown']['widget'] = array(
      '#type' => 'select',
      '#title' => t('Output type'),
      '#options' => array(
        LANGDROPDOWN_SIMPLE_SELECT => t('Simple HTML select'),
        LANGDROPDOWN_MSDROPDOWN => t('Marghoob Suleman Dropdown jquery library'),
        LANGDROPDOWN_CHOSEN => t('Chosen jquery library'),
        LANGDROPDOWN_DDSLICK => t('ddSlick library'),
      ),
      '#default_value' => $this->configuration['widget'],
    );

    $form['lang_dropdown']['msdropdown'] = array(
      '#type' => 'fieldset',
      '#title' => t('Marghoob Suleman Dropdown Settings'),
      '#weight' => 1,
      '#states' => array(
        'visible' => array(
          ':input[name="settings[lang_dropdown][widget]"]' => array('value' => LANGDROPDOWN_MSDROPDOWN),
        ),
      ),
    );

    if (!module_exists('languageicons')) {
      $form['lang_dropdown']['msdropdown']['#description'] = t('This looks better with !languageicons module.', array('!languageicons' => l(t('language icons'), LANGDROPDOWN_LANGUAGEICONS_MOD_URL)));
    }

    if (_lang_dropdown_get_msdropdown_path()) {
      $form['lang_dropdown']['msdropdown']['visible_rows'] = array(
        '#type' => 'select',
        '#title' => t('Maximum number of visible rows'),
        '#options' => drupal_map_assoc(array(2, 3, 4, 5 , 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20)),
        '#default_value' => $this->configuration['msdropdown']['visible_rows'],
      );

      $form['lang_dropdown']['msdropdown']['rounded'] = array(
        '#type' => 'checkbox',
        '#title' => t('Rounded corners.'),
        '#default_value' => $this->configuration['msdropdown']['rounded'],
      );

      $form['lang_dropdown']['msdropdown']['animation'] = array(
        '#type' => 'select',
        '#title' => t('Animation style for dropdown'),
        '#options' => array(
          'slideDown' => t('Slide down'),
          'fadeIn' => t('Fade in'),
          'show' => t('Show'),
        ),
        '#default_value' => $this->configuration['msdropdown']['animation'],
      );

      $form['lang_dropdown']['msdropdown']['event'] = array(
        '#type' => 'select',
        '#title' => t('Event that opens the menu'),
        '#options' => array('click' => t('Click'), 'mouseover' => t('Mouse Over')),
        '#default_value' => $this->configuration['msdropdown']['event'],
      );

      $msdSkinOptions = array();
      foreach (_lang_dropdown_get_msdropdown_skins() as $key => $value) {
        $msdSkinOptions[$key] = $value['text'];
      }
      $form['lang_dropdown']['msdropdown']['skin'] = array(
        '#type' => 'select',
        '#title' => t('Skin'),
        '#options' => $msdSkinOptions,
        '#default_value' => $this->configuration['msdropdown']['skin'],
      );

      $form['lang_dropdown']['msdropdown']['custom_skin'] = array(
        '#type' => 'textfield',
        '#title' => t('Custom skin name'),
        '#size' => 80,
        '#maxlength' => 55,
        '#default_value' => $this->configuration['msdropdown']['custom_skin'],
        '#states' => array(
          'visible' => array(
            ':input[name="settings[lang_dropdown][msdropdown][skin]"]' => array('value' => 'custom'),
          ),
        ),
      );
    }
    else {
      $form['lang_dropdown']['msdropdown']['#description'] = t('You need to download the !msdropdown and extract the entire contents of the archive into the %path directory on your server.', array('!msdropdown' => l(t('Marghoob Suleman Dropdown JavaScript library'), LANGDROPDOWN_MSDROPDOWN_URL), '%path' => 'sites/all/libraries'));
      $form['lang_dropdown']['msdropdown']['visible_rows'] = array(
        '#type' => 'hidden',
        '#value' => $this->configuration['msdropdown']['visible_rows'],
      );
      $form['lang_dropdown']['msdropdown']['rounded'] = array(
        '#type' => 'hidden',
        '#value' => $this->configuration['msdropdown']['rounded'],
      );
      $form['lang_dropdown']['msdropdown']['animation'] = array(
        '#type' => 'hidden',
        '#value' => $this->configuration['msdropdown']['animation'],
      );
      $form['lang_dropdown']['msdropdown']['event'] = array(
        '#type' => 'hidden',
        '#value' => $this->configuration['msdropdown']['event'],
      );
      $form['lang_dropdown']['msdropdown']['skin'] = array(
        '#type' => 'hidden',
        '#value' => $this->configuration['msdropdown']['skin'],
      );
      $form['lang_dropdown']['msdropdown']['custom_skin'] = array(
        '#type' => 'hidden',
        '#value' => $this->configuration['msdropdown']['custom_skin'],
      );
    }

    $form['lang_dropdown']['languageicons'] = array(
      '#type' => 'fieldset',
      '#title' => t('Language icons settings'),
      '#weight' => 1,
      '#states' => array(
        'visible' => array(
          ':input[name="settings[lang_dropdown][widget]"]' => array('value' => LANGDROPDOWN_SIMPLE_SELECT),
        ),
      ),
    );

    if (module_exists('languageicons')) {
      $form['lang_dropdown']['languageicons']['flag_position'] = array(
        '#type' => 'select',
        '#title' => t('Position of the flag when the dropdown is show just as a select'),
        '#options' => array(
          LANGDROPDOWN_FLAG_POSITION_BEFORE => t('Before'),
          LANGDROPDOWN_FLAG_POSITION_AFTER => t('After'),
        ),
        '#default_value' => $this->configuration['languageicons']['flag_position'],
      );
    }
    else {
      $form['lang_dropdown']['languageicons']['#description'] = t('Enable !languageicons module to show a flag of the selected language before or after the select box.', array('!languageicons' => l(t('language icons'), LANGDROPDOWN_LANGUAGEICONS_MOD_URL)));
      $form['lang_dropdown']['languageicons']['flag_position'] = array(
        '#type' => 'hidden',
        '#value' => $this->configuration['languageicons']['flag_position'],
      );
    }

    $form['lang_dropdown']['chosen'] = array(
      '#type' => 'fieldset',
      '#title' => t('Chosen settings'),
      '#weight' => 2,
      '#states' => array(
        'visible' => array(
          ':input[name="settings[lang_dropdown][widget]"]' => array('value' => LANGDROPDOWN_CHOSEN),
        ),
      ),
    );

    if (!module_exists('chosen') && _lang_dropdown_get_chosen_path()) {
      $form['lang_dropdown']['chosen']['disable_search'] = array(
        '#type' => 'checkbox',
        '#title' => t('Disable search box'),
        '#default_value' => $this->configuration['chosen']['disable_search'],
      );

      $form['lang_dropdown']['chosen']['no_results_text'] = array(
        '#type' => 'textfield',
        '#title' => t('No Result Text'),
        '#description' => t('Text to show when no result is found on search.'),
        '#default_value' => $this->configuration['chosen']['no_results_text'],
        '#states' => array(
          'visible' => array(
            ':input[name="settings[lang_dropdown][chosen][disable_search]"]' => array('checked' => FALSE),
          ),
        ),
      );
    }
    else {
      $form['lang_dropdown']['chosen']['disable_search'] = array(
        '#type' => 'hidden',
        '#value' => $this->configuration['chosen']['disable_search'],
      );
      $form['lang_dropdown']['chosen']['no_results_text'] = array(
        '#type' => 'hidden',
        '#value' => $this->configuration['chosen']['no_results_text'],
      );
      if (module_exists('chosen')) {
        $form['lang_dropdown']['chosen']['#description'] = t('If you are already using the !chosenmod you must just choose to output language dropdown as a simple HTML select and allow !chosenmod to turn it into a chosen style select.', array('!chosenmod' => l(t('Chosen module'), LANGDROPDOWN_CHOSEN_MOD_URL)));
      } else {
        $form['lang_dropdown']['chosen']['#description'] = t('You need to download the !chosen and extract the entire contents of the archive into the %path directory on your server.', array('!chosen' => l(t('Chosen library'), LANGDROPDOWN_CHOSEN_WEB_URL), '%path' => 'sites/all/libraries'));
      }
    }

    $form['lang_dropdown']['ddslick'] = array(
      '#type' => 'fieldset',
      '#title' => t('ddSlick settings'),
      '#weight' => 3,
      '#states' => array(
        'visible' => array(
          ':input[name="settings[lang_dropdown][widget]"]' => array('value' => LANGDROPDOWN_DDSLICK),
        ),
      ),
    );

    if (_lang_dropdown_get_ddslick_path()) {
      $form['lang_dropdown']['ddslick']['ddslick_height'] = array(
        '#type' => 'number',
        '#title' => t('Height'),
        '#description' => t('Height in px for the drop down options i.e. 300. The scroller will automatically be added if options overflows the height. Use 0 for full height.'),
        '#size' => 8,
        '#maxlength' => 3,
        '#field_suffix' => 'px',
        '#default_value' => $this->configuration['ddslick']['ddslick_height'],
      );

      if (module_exists('languageicons')) {
        $form['lang_dropdown']['ddslick']['showSelectedHTML'] = array(
          '#type' => 'checkbox',
          '#title' => t('Show Flag'),
          '#default_value' => $this->configuration['ddslick']['showSelectedHTML'],
        );

        $form['lang_dropdown']['ddslick']['imagePosition'] = array(
          '#type' => 'select',
          '#title' => t('Flag Position'),
          '#options' => array(
            LANGDROPDOWN_DDSLICK_LEFT => t('left'),
            LANGDROPDOWN_DDSLICK_RIGHT => t('right'),
          ),
          '#default_value' => $this->configuration['ddslick']['imagePosition'],
          '#states' => array(
            'visible' => array(
              ':input[name="settings[lang_dropdown][ddslick][showSelectedHTML]"]' => array('checked' => TRUE),
            ),
          ),
        );
      }
      else {
        $form['lang_dropdown']['ddslick']['#description'] = t('This looks better with !languageicons module.', array('!languageicons' => l(t('language icons'), LANGDROPDOWN_LANGUAGEICONS_MOD_URL)));
        $form['lang_dropdown']['ddslick']['showSelectedHTML'] = array(
          "#type" => 'hidden',
          "#value" => $this->configuration['ddslick']['showSelectedHTML'],
        );
        $form['lang_dropdown']['ddslick']['imagePosition'] = array(
          "#type" => 'hidden',
          "#value" => $this->configuration['ddslick']['imagePosition'],
        );
      }

      $ddsSkinOptions = array();
      foreach (_lang_dropdown_get_ddslick_skins() as $key => $value) {
        $ddsSkinOptions[$key] = $value['text'];
      }
      $form['lang_dropdown']['ddslick']['skin'] = array(
        '#type' => 'select',
        '#title' => t('Skin'),
        '#options' => $ddsSkinOptions,
        '#default_value' => $this->configuration['ddslick']['skin'],
      );

      $form['lang_dropdown']['ddslick']['custom_skin'] = array(
        '#type' => 'textfield',
        '#title' => t('Custom skin name'),
        '#size' => 80,
        '#maxlength' => 55,
        '#default_value' => $this->configuration['ddslick']['custom_skin'],
        '#states' => array(
          'visible' => array(
            ':input[name="settings[lang_dropdown][ddslick][skin]"]' => array('value' => 'custom'),
          ),
        ),
      );

    }
    else {
      $form['lang_dropdown']['ddslick']['#description'] = t('You need to download the !ddslick and extract the entire contents of the archive into the %path directory on your server.', array('!ddslick' => l(t('ddSlick library'), LANGDROPDOWN_DDSLICK_WEB_URL), '%path' => 'sites/all/libraries/ddslick'));
      $form['lang_dropdown']['ddslick']['ddslick_height'] = array(
        "#type" => 'hidden',
        "#value" => $this->configuration['ddslick']['ddslick_height'],
      );
      $form['lang_dropdown']['ddslick']['showSelectedHTML'] = array(
        "#type" => 'hidden',
        "#value" => $this->configuration['ddslick']['showSelectedHTML'],
      );
      $form['lang_dropdown']['ddslick']['imagePosition'] = array(
        "#type" => 'hidden',
        "#value" => $this->configuration['ddslick']['imagePosition'],
      );
      $form['lang_dropdown']['ddslick']['skin'] = array(
        "#type" => 'hidden',
        "#value" => $this->configuration['ddslick']['skin'],
      );
      $form['lang_dropdown']['ddslick']['custom_skin'] = array(
        "#type" => 'hidden',
        "#value" => $this->configuration['ddslick']['custom_skin'],
      );
    }

    // configuration options that allow to hide a specific language to specific roles
    $form['lang_dropdown']['hideout'] = array(
      '#type' => 'fieldset',
      '#title' => t('Hide language settings'),
      '#description' => t('Select which languages you want to hide to specific roles.'),
      '#weight' => 4,
    );

    $languages = language_list();
    $roles = user_roles();

    $role_names = array();
    $role_languages = array();
    foreach ($roles as $rid => $role) {
      // Retrieve role names for columns.
      $role_names[$rid] = String::checkPlain($role->label());
      // Fetch languages for the roles.
      $role_languages[$rid] = isset($this->configuration['hidden_languages'][$rid]) ? $this->configuration['hidden_languages'][$rid] : array();
    }

    // Store $role_names for use when saving the data.
    $form['lang_dropdown']['hideout']['role_names'] = array(
      '#type' => 'value',
      '#value' => $role_names,
    );

    $form['lang_dropdown']['hideout']['languages'] = array(
      '#type' => 'table',
      '#header' => array($this->t('Languages')),
      '#id' => 'hidden_languages_table',
      '#sticky' => TRUE,
    );

    foreach ($role_names as $name) {
      $form['lang_dropdown']['hideout']['languages']['#header'][] = array(
        'data' => $name,
        'class' => array('checkbox'),
      );
    }

    foreach ($languages as $code => $language) {
      $options[$code] = '';
      $form['lang_dropdown']['hideout']['languages'][$code]['language'] = array(
        '#type' => 'item',
        '#markup' => $language->name,
      );

      foreach ($role_names as $rid => $role) {
        $form['lang_dropdown']['hideout']['languages'][$code][$rid] = array(
          '#title' => $rid . ': ' . $language->name,
          '#title_display' => 'invisible',
          '#wrapper_attributes' => array(
            'class' => array('checkbox'),
          ),
          '#type' => 'checkbox',
          '#default_value' => in_array($code,$role_languages[$rid]) ? 1 : 0,
          '#attributes' => array('class' => array('rid-' . $rid)),
          // TODO: review why parents and tree doesn't work properly
          //'#parents' => array($rid, $code),
        );
      }
    }

    return $form;
  }

  /**
   * Overrides \Drupal\block\BlockBase::blockValidate().
   */
  public function blockValidate($form, &$form_state) {

    switch ($form_state['values']['lang_dropdown']['widget']) {

      case LANGDROPDOWN_MSDROPDOWN:
        if (!_lang_dropdown_get_msdropdown_path()) {
          form_error($form['settings']['lang_dropdown']['widget'], $form_state, $this->t('You can\'t use !msdropdown output. You don\'t have !msdropdown library installed.', array('!msdropdown' => l(t('Marghoob Suleman Dropdown'), LANGDROPDOWN_MSDROPDOWN_URL))));
        }
        break;

      case LANGDROPDOWN_CHOSEN:
        if (module_exists('chosen')) {
          form_error($form['settings']['lang_dropdown']['widget'], $form_state, $this->t('You can\'t use !chosen output directly on language dropdown switcher. You have !chosenmod installed. Use simple HTML select as output and !chosenmod will render it with the !chosen library.', array('!chosen' => l(t('Chosen'), LANGDROPDOWN_CHOSEN_WEB_URL), '!chosenmod' => l(t('Chosen module'), LANGDROPDOWN_CHOSEN_MOD_URL))));
        }
        else {
          if (!_lang_dropdown_get_chosen_path()) {
            form_error($form['settings']['lang_dropdown']['widget'], $form_state, $this->t('You can\'t use !chosen output. You don\'t have !chosen library installed.', array('!chosen' => l(t('Chosen'), LANGDROPDOWN_CHOSEN_WEB_URL))));
          }
        }
        break;

      case LANGDROPDOWN_DDSLICK:
        if (!_lang_dropdown_get_ddslick_path()) {
          form_error($form['settings']['lang_dropdown']['widget'], $form_state, $this->t('You can\'t use !ddslick output. You don\'t have !ddslick library installed.', array('!ddslick' => l(t('ddSlick'), LANGDROPDOWN_DDSLICK_WEB_URL))));
        }
        break;
      
      default:
        break;

    }

  }

  /**
   * Overrides \Drupal\block\BlockBase::blockSubmit().
   */
  public function blockSubmit($form, &$form_state) {
    $this->configuration['showall'] = $form_state['values']['lang_dropdown']['showall'];
    $this->configuration['tohome'] = $form_state['values']['lang_dropdown']['tohome'];
    $this->configuration['width'] = $form_state['values']['lang_dropdown']['width'];
    $this->configuration['display'] = $form_state['values']['lang_dropdown']['display'];
    $this->configuration['widget'] = $form_state['values']['lang_dropdown']['widget'];
    $this->configuration['msdropdown'] = array(
      'visible_rows' => $form_state['values']['lang_dropdown']['msdropdown']['visible_rows'],
      'rounded' => $form_state['values']['lang_dropdown']['msdropdown']['rounded'],
      'animation' => $form_state['values']['lang_dropdown']['msdropdown']['animation'],
      'event' => $form_state['values']['lang_dropdown']['msdropdown']['event'],
      'skin' => $form_state['values']['lang_dropdown']['msdropdown']['skin'],
      'custom_skin' => $form_state['values']['lang_dropdown']['msdropdown']['custom_skin'],
    );
    $this->configuration['chosen'] = array(
      'disable_search' => $form_state['values']['lang_dropdown']['chosen']['disable_search'],
      'no_results_text' => $form_state['values']['lang_dropdown']['chosen']['no_results_text'],
    );
    $this->configuration['ddslick'] = array(
      'ddslick_height' => $form_state['values']['lang_dropdown']['ddslick']['ddslick_height'],
      'showSelectedHTML' => $form_state['values']['lang_dropdown']['ddslick']['showSelectedHTML'],
      'imagePosition' => $form_state['values']['lang_dropdown']['ddslick']['imagePosition'],
      'skin' => $form_state['values']['lang_dropdown']['ddslick']['skin'],
      'custom_skin' => $form_state['values']['lang_dropdown']['ddslick']['custom_skin'],
    );
    $this->configuration['languageicons'] = array(
      'flag_position' => $form_state['values']['lang_dropdown']['languageicons']['flag_position'],
    );

    $this->configuration['hidden_languages'] = array();
    foreach($form_state['values']['lang_dropdown']['hideout']['languages'] as $code => $values) {
      unset($values['language']);
      foreach($values as $rid => $value) {
        if ($value) { $this->configuration['hidden_languages'][$rid][] = $code; }
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    // prevent erroneous configuration
    if ($this->configuration['widget'] == LANGDROPDOWN_MSDROPDOWN && !_lang_dropdown_get_msdropdown_path()) { return array(); }
    if ($this->configuration['widget'] == LANGDROPDOWN_CHOSEN && !_lang_dropdown_get_chosen_path()) { return array(); }
    if ($this->configuration['widget'] == LANGDROPDOWN_DDSLICK && !_lang_dropdown_get_ddslick_path()) { return array(); }

    $build = array();
    $path = drupal_is_front_page() ? '<front>' : current_path();
    list(, $type) = explode(':', $this->getPluginId());
    $languages = language_negotiation_get_switch_links($type, $path);

    $user = \Drupal::currentUser();

    $roles = $user->getRoles();

    foreach ($languages->links as $langcode => $link) {
      $hide_language = true;
      foreach($roles as $key => $role) {
        if (!isset($this->configuration['hidden_languages'][$role]) || !in_array($langcode, $this->configuration['hidden_languages'][$role])) {
          $hide_language = false;
          break;
        }
      }
      if ($hide_language) {
        unset($languages->links[$langcode]['href']);
        $languages->links[$langcode]['attributes']['class'][] = 'locale-untranslated';
      }
    }


    if (empty($languages->links)) { return array(); }

    //$form = drupal_get_form('lang_dropdown_form', $languages, $type, $this->configuration);
    $lang_dropdown_form = new LanguageDropdownForm($languages->links, $type, $this->configuration);
    $form = \Drupal::formBuilder()->getForm($lang_dropdown_form);

    return array(
      'lang_dropdown_form' => $form,
    );






    if (!empty($languages->links)) {
      $build = array(
        '#theme' => 'links__language_block',
        '#links' => $languages->links,
        '#attributes' => array(
          'class' => array(
            "language-dropdown-switcher-{$links->method_id}",
          ),
        ),
      );
    }


    return $build;
  }

}
