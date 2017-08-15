<?php

namespace Drupal\feeds\Plugin\Type;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\feeds\Plugin\Type\PluginBase;

/**
 * Base class for Feeds plugins that have configuration forms.
 */
abstract class ConfigurablePluginBase extends PluginBase implements PluginFormInterface {

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Validation is optional.
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->setConfiguration($form_state->getValues());
  }

}
