<?php

namespace Drupal\feeds\Feeds\Processor\Form;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Form\FormStateInterface;
use Drupal\feeds\Plugin\Type\ExternalPluginFormBase;
use Drupal\feeds\Plugin\Type\Processor\ProcessorInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\Entity\User;

/**
 * The configuration form for the CSV parser.
 */
class EntityProcessorOptionForm extends ExternalPluginFormBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // @todo Remove hack.
    $entity_type = \Drupal::entityTypeManager()->getDefinition($this->plugin->entityType());

    if ($bundle_key = $entity_type->getKey('bundle')) {
      $form['values'][$bundle_key] = [
        '#type' => 'select',
        '#options' => $this->plugin->bundleOptions(),
        '#title' => $this->plugin->bundleLabel(),
        '#required' => TRUE,
        '#default_value' => $this->plugin->bundle() ?: key($this->plugin->bundleOptions()),
        '#disabled' => $this->plugin->isLocked(),
      ];
    }

    return $form;
  }

}
