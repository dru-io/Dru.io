<?php

namespace Drupal\feeds\Plugin\Type;

use Drupal\Core\Plugin\PluginBase as DrupalPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\feeds\FeedInterface;

/**
 * The base class for the fetcher, parser, and processor plugins.
 *
 * @todo Move source* methods to another interface.
 * @todo This class is currently a dumping ground for methods that should be
 *   implemented by other interfaces. We're working on it.
 */
abstract class PluginBase extends DrupalPluginBase implements FeedsPluginInterface {

  /**
   * The importer this plugin is working for.
   *
   * @var \Drupal\feeds\FeedTypeInterface
   */
  protected $feedType;

  /**
   * The url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * The link generator.
   *
   * @var \Drupal\Core\Utility\LinkGeneratorInterface
   */
  protected $linkGenerator;

  /**
   * Constructs a PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    assert('isset($configuration["feed_type"]) && $configuration["feed_type"] instanceof Drupal\feeds\FeedTypeInterface');

    $this->feedType = $configuration['feed_type'];
    unset($configuration['feed_type']);
    $this->setConfiguration($configuration);
    $this->pluginId = $plugin_id;
    $this->pluginDefinition = $plugin_definition;
  }

  /**
   * {@inheritdoc}
   */
  public function pluginType() {
    return $this->pluginDefinition['plugin_type'];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration($key = NULL) {
    if ($key === NULL) {
      return $this->configuration;
    }

    if (isset($this->configuration[$key])) {
      return $this->configuration[$key];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $defaults = $this->defaultConfiguration();
    $this->configuration = array_intersect_key($configuration, $defaults) + $defaults;
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
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultFeedConfiguration() {
    return [];
  }

  /**
   * A feed is being saved.
   */
  public function onFeedSave(FeedInterface $feed) {}

  /**
   * A feed is being deleted.
   */
  public function onFeedDeleteMultiple(array $feeds) {}

  /**
   * The feed type is being saved.
   */
  public function onFeedTypeSave($update = TRUE) {}

  /**
   * The feed type is being deleted.
   */
  public function onFeedTypeDelete() {}

  /**
   * Renders a link to a route given a route name and its parameters.
   *
   * @see \Drupal\Core\Utility\LinkGeneratorInterface::generate() for details
   *   on the arguments, usage, and possible exceptions.
   *
   * @return string
   *   An HTML string containing a link to the given route and parameters.
   */
  protected function l($text, $route_name, array $parameters = [], array $options = []) {
    return $this->linkGenerator()->generate($text, $route_name, $parameters, $options);
  }

  /**
   * Generates a URL or path for a specific route based on the given parameters.
   *
   * @see \Drupal\Core\Routing\UrlGeneratorInterface::generateFromRoute() for
   *   details on the arguments, usage, and possible exceptions.
   *
   * @return string
   *   The generated URL for the given route.
   */
  protected function url($route_name, $route_parameters = [], $options = []) {
    return $this->urlGenerator()->generateFromRoute($route_name, $route_parameters, $options);
  }

  /**
   * Returns the link generator service.
   *
   * @return \Drupal\Core\Utility\LinkGeneratorInterface
   *   The link generator service.
   */
  protected function linkGenerator() {
    if (!$this->linkGenerator) {
      $this->linkGenerator = $this->container()->get('link_generator');
    }
    return $this->linkGenerator;
  }

  /**
   * Returns the URL generator service.
   *
   * @return \Drupal\Core\Routing\UrlGeneratorInterface
   *   The URL generator service.
   */
  protected function urlGenerator() {
    if (!$this->urlGenerator) {
      $this->urlGenerator = $this->container()->get('url_generator');
    }
    return $this->urlGenerator;
  }

  /**
   * Returns the service container.
   *
   * @return \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   */
  private function container() {
    return \Drupal::getContainer();
  }

}
