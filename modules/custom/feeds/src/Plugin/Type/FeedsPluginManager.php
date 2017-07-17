<?php

namespace Drupal\feeds\Plugin\Type;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Manages Feeds plugins.
 */
class FeedsPluginManager extends DefaultPluginManager {

  /**
   * The plugin being managed.
   *
   * @var string
   */
  protected $pluginType;

  /**
   * Constructs a new \Drupal\feeds\Plugin\Type\FeedsPluginManager object.
   *
   * @param string $type
   *   The plugin type. Either fetcher, parser, or processor, handler, source,
   *   target, or other.
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Language\LanguageManager $language_manager
   *   The language manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct($type, \Traversable $namespaces, CacheBackendInterface $cache_backend, LanguageManager $language_manager, ModuleHandlerInterface $module_handler) {
    $type_annotations = [
      'fetcher' => 'Drupal\feeds\Annotation\FeedsFetcher',
      'parser' => 'Drupal\feeds\Annotation\FeedsParser',
      'processor' => 'Drupal\feeds\Annotation\FeedsProcessor',
      'source' => 'Drupal\feeds\Annotation\FeedsSource',
      'target' => 'Drupal\feeds\Annotation\FeedsTarget',
    ];
    $plugin_interfaces = [
      'fetcher' => 'Drupal\feeds\Plugin\Type\Fetcher\FetcherInterface',
      'parser' => 'Drupal\feeds\Plugin\Type\Parser\ParserInterface',
      'processor' => 'Drupal\feeds\Plugin\Type\Processor\ProcessorInterface',
      'source' => 'Drupal\feeds\Plugin\Type\Source\SourceInterface',
      'target' => 'Drupal\feeds\Plugin\Type\Target\TargetInterface',
    ];

    $this->pluginType = $type;
    $this->subdir = 'Feeds/' . ucfirst($type);
    $this->discovery = new AnnotatedClassDiscovery($this->subdir, $namespaces, $type_annotations[$type]);
    $this->discovery = new ContainerDerivativeDiscoveryDecorator($this->discovery);
    $this->factory = new FeedsAnnotationFactory($this, $plugin_interfaces[$type]);
    $this->moduleHandler = $module_handler;
    $this->alterInfo("feeds_{$type}_plugins");
    $this->setCacheBackend($cache_backend, "feeds_{$type}_plugins");
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);
    // Add plugin_type key so that we can determine the plugin type later.
    $definition['plugin_type'] = $this->pluginType;

    // If no default form is defined and this plugin implements
    // \Drupal\Core\Plugin\PluginFormInterface, use that for the default form.
    if (!isset($definition['form']['configuration']) && isset($definition['class']) && is_subclass_of($definition['class'], PluginFormInterface::class)) {
      $definition['form']['configuration'] = $definition['class'];
    }
  }

}
