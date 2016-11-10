<?php

namespace Drupal\curtain_raiser\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Configure curtain raiser for this site.
 */
class CurtainRaiser extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function validate($password) {

    $config = \Drupal::service('config.factory')->getEditable('curtain_raiser.settings');

    $master_password = $config->get('master_password');
    $test_password = $config->get('test_password');

    if ($password == $master_password) {
      $config->set('inauguration_status', TRUE)
        ->save();
      $response = array(
        'success' => TRUE,
      );
    }
    elseif ($password == $test_password) {
      $response = array(
        'success' => TRUE,
      );
    }
    else {
      $response = array(
        'success' => FALSE,
      );
    }

    return new JsonResponse($response);
  }

}
