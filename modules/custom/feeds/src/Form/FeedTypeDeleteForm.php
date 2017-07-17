<?php

namespace Drupal\feeds\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form for deleting a feed type.
 */
class FeedTypeDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the feed type %type?', ['%type' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('feeds.overview_types');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();

    $args = ['%type' => $this->entity->label()];
    $this->logger('feeds')->notice('Deleted feed type: %type.', $args);
    drupal_set_message($this->t('%type has been deleted.', $args));

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
