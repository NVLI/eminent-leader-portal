<?php

/**
 * @file
 * Contains \Drupal\curtain_raiser\Controller\CurtainRaiser.
 */

namespace Drupal\curtain_raiser\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class CurtainRaiser extends ControllerBase {

  public function validate($password) {

      $config = \Drupal::service('config.factory')->getEditable('curtain_raiser.settings');

      $master_password = $config->get('master_password');
      $test_password = $config->get('test_password');


    if ($password == $master_password) {
      $config->set('inauguration_status', true)
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
