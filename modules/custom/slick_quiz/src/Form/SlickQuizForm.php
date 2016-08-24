<?php

/**
 * @file
 * Contains Drupal\slick_quiz\Form\SlickQuizForm.
 */

namespace Drupal\slick_quiz\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Language\Language;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Url;

/**
 * Form controller for the slick_quiz entity edit forms.
 *
 * @ingroup slick_quiz
 */
class SlickQuizForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\slick_quiz\Entity\SlickQuiz */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;

    $form['langcode'] = array(
      '#title' => $this->t('Language'),
      '#type' => 'language_select',
      '#default_value' => $entity->getUntranslated()->language()->getId(),
      '#languages' => Language::STATE_ALL,
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->getEntity();
    $entity->save();

    // Take userid of the respective SlickQuiz entity.
    $uid_value = $entity->user_id->getValue();
    $uid = $uid_value['0']['target_id'];

    // Take eid of the respective SlickQuiz entity.
    $eid_value = $entity->id->getValue();
    $eid = $eid_value['0']['value'];

    // Take status of respective SlickQuiz entity.
    $entity_status = $entity->verification_status->getValue();
    $status = $entity_status['0']['value'];

    if ($status == 1) {
      $user_role = User::load($uid);
      $user_role->addRole('slick_quiz');
      $user_role->save();
    }
    $form_state->setRedirectUrl(Url::fromRoute('entity.slick_quiz.canonical', ['slick_quiz' => $eid]));
  }

}
