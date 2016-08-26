<?php

/**
 * @file
 * Contains \Drupal\slick_quiz\SlickQuizInterface.
 */

namespace Drupal\slick_quiz;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a Slick Quiz entity.
 *
 * We have this interface so we can join the other interfaces it extends.
 *
 * @ingroup slick_quiz
 */
interface SlickQuizInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface{

}
