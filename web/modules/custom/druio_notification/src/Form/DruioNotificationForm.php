<?php

namespace Drupal\druio_notification\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Druio notification edit forms.
 *
 * @ingroup druio_notification
 */
class DruioNotificationForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\druio_notification\Entity\DruioNotification */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Druio notification.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Druio notification.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.druio_notification.canonical', ['druio_notification' => $entity->id()]);
  }

}
