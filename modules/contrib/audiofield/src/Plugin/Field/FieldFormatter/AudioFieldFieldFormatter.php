<?php

/**
 * @file
 * Contains \Drupal\audiofield\Plugin\Field\FieldFormatter\AudioFieldFieldFormatter. 
 */

namespace Drupal\audiofield\Plugin\Field\FieldFormatter;

use Drupal\audiofield\AudioFieldPlayerManager;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @FieldFormatter(
 *   id = "audiofield_audioplayer",
 *   label = @Translation("Audio Player"),
 *   field_types = {
 *     "file"
 *   }
 * )
 */
class AudioFieldFieldFormatter extends FileFormatterBase implements ContainerFactoryPluginInterface {

    protected $audioPlayerManager;

    public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, AudioFieldPlayerManager $audio_player_manager) {
        parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

        $this->audioPlayerManager = $audio_player_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('audiofield.player_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $plugin_definitions = $this->audioPlayerManager->getDefinitions();
    $plugins = array();
    foreach ($plugin_definitions as $plugin_id => $plugin) {
        $plugins[$plugin_id] = $plugin['title'];
    }

    $elements = parent::settingsForm($form, $form_state);
    $elements['audio_player'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Select Player'),
      '#default_value' => $this->getSetting('audio_player'),
      '#options' => $plugins,
    );

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'audio_player' => 'default_mp3_player',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();
    $files = $this->getEntitiesToView($items, $langcode);

    // Early opt-out if the field is empty.
    if (empty($files)) {
      return $elements;
    }

    $plugin_id = $this->getSetting('audio_player');
    $player = $this->audioPlayerManager->createInstance($plugin_id);

    foreach ($files as $delta => $file) {
      $elements[$delta] = $player->renderPlayer($file);
    }

    return $elements;
  }
    
}
