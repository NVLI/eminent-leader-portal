<?php

/**
 * @file
 * Contains \Drupal\audiofield\AudioFieldPluginInterface.
 */

namespace Drupal\audiofield;

use Drupal\file\FileInterface;

interface AudioFieldPluginInterface {

    /**
     * Renders the player.
     *
     * @param \Drupal\file\FileInterface $file
     *   The uploaded file.
     *
     * @return []
     *   Returns the rendered array.
     */
    public function renderPlayer(FileInterface $file);
}