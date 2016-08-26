<?php

/**
 * @file
 * Contains \Drupal\audiofield\Annotation\AudioPlayer
 */

namespace Drupal\audiofield\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * @Annotation 
 */
class AudioPlayer extends Plugin {

    public $id;
    public $title = "";
    public $file_types = array();
    public $description = "";
}
