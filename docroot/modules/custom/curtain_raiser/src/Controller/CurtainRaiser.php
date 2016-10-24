<?php

/**
 * @file
 * Contains \Drupal\curtain_raiser\Controller\MediaAdd.
 */

namespace Drupal\curtain_raiser\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

class CurtainRaiser extends ControllerBase {

  public function validate($password) {
    if ($password == "user0123") {
      $response = array(
        'success' => TRUE,
      );
      return new JsonResponse($response);
    }
    else {
      $response = array(
        'success' => FALSE,
      );
      return new JsonResponse($response);
    }
  }

}
