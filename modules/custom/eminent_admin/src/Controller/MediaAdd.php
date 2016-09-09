<?php

/**
 * @file
 * Contains \Drupal\eminent_admin\Controller\MediaAdd.
 */

namespace Drupal\eminent_admin\Controller;

use Drupal\Core\Controller\ControllerBase;
use \Drupal\Core\Ajax;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\CssCommand;

class MediaAdd extends ControllerBase {

    public function AddMedia($media_id, $group) {
      // Get the form form.
      if ($group == 'playlist') {
        $form = \Drupal::formBuilder()->getForm('\Drupal\eminent_admin\Form\AddPlayListForm', $media_id);
        $title = $this->t('Add to playlist');
      }
      elseif ($group == 'timeline')  {
        $form = \Drupal::formBuilder()->getForm('\Drupal\eminent_admin\Form\AddTimeLineForm', $media_id);
        $title = $this->t('Add to timeline');
      }
      //$response = new AjaxResponse();
      //$form['#attached']['library'][] = 'core/drupal.dialog.ajax';
      //$response->setAttachments($form['#attached']);

      $options = array(
        'dialogClass' => 'popup-dialog-class',
        'width' => '50%',
      );

      //$modal = new OpenModalDialogCommand($title, $form, $options);
      //$response->addCommand($modal);
      return $form;
    }

}
