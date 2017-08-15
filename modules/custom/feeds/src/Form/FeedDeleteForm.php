<?php

namespace Drupal\feeds\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for deleting a Feed.
 */
class FeedDeleteForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the feed %feed?', ['%feed' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   *
   * @todo Set the correct route once views can override paths.
   */
  public function getCancelUrl() {
    return $this->entity->toUrl();
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

    $args = ['@type' => $this->entity->getType()->label(), '%title' => $this->entity->label()];
    $this->logger('feeds')->notice('@type: deleted %title.', $args);
    drupal_set_message($this->t('%title has been deleted.', $args));

    $form_state->setRedirect('feeds.admin');
  }

}
