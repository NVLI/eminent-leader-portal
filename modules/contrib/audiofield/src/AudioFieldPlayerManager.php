<?php

/**
 * @file
 * Contains \Drupal\audiofield\AudioFieldPlayerManager
 */

namespace Drupal\audiofield;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

class AudioFieldPlayerManager extends DefaultPluginManager {

    public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
        parent::__construct('Plugin/AudioPlayer', $namespaces, $module_handler, 'Drupal\audiofield\AudioFieldPluginInterface', 'Drupal\audiofield\Annotation\AudioPlayer');

        $this->alterInfo('audiofield');
        $this->setCacheBackend($cache_backend, 'audiofield_audioplayer');
    }
  
}