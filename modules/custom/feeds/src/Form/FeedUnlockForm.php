<?php

namespace Drupal\feeds\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for unlocking a feed.
 */
class FeedUnlockForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to unlock the feed %feed?', ['%feed' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->entity->toUrl();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Unlock');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->unlock();
    $args = ['@type' => $this->entity->getType()->label(), '%title' => $this->entity->label()];

    $this->logger('feeds')->notice('@type: unlocked %title.', $args);
    drupal_set_message($this->t('%title has been unlocked.', $args));

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
