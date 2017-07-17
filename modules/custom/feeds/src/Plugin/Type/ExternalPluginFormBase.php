<?php

namespace Drupal\feeds\Plugin\Type;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\feeds\Plugin\PluginAwareInterface;
use Drupal\feeds\Plugin\Type\FeedsPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for Feeds plugins that have external configuration forms.
 */
abstract class ExternalPluginFormBase implements PluginFormInterface, PluginAwareInterface {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * The Feeds plugin.
   *
   * @var \Drupal\feeds\Plugin\Type\FeedsPluginInterface
   */
  protected $plugin;

  /**
   * {@inheritdoc}
   */
  public function setPlugin(FeedsPluginInterface $plugin) {
    $this->plugin = $plugin;
  }

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
    $this->plugin->setConfiguration($form_state->getValues());
  }

}
