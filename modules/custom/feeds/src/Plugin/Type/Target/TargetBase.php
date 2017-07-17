<?php

namespace Drupal\feeds\Plugin\Type\Target;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\FeedTypeInterface;
use Drupal\feeds\Plugin\Type\ConfigurablePluginBase;
use Drupal\feeds\Plugin\Type\Target\TargetInterface;

/**
 * @todo Document this.
 */
abstract class TargetBase extends ConfigurablePluginBase implements TargetInterface {

  /**
   * The target definition.
   *
   * @var \Drupal\feeds\TargetDefinitionInterface
   */
  protected $targetDefinition;

  /**
   * Constructs a TargetBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    // Do not call parent, we handle everything ourselves.
    $this->feedType = $configuration['feed_type'];
    $this->pluginId = $plugin_id;
    $this->pluginDefinition = $plugin_definition;
    $this->targetDefinition = $configuration['target_definition'];

    unset($configuration['feed_type']);
    unset($configuration['target_definition']);

    // Calling setConfiguration() ensures the configuration is clean and
    // defaults are set.
    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $delta = $form_state->getTriggeringElement()['#delta'];
    $configuration = $form_state->getValue(['mappings', $delta, 'settings']);
    $this->setConfiguration($configuration);
  }

}
