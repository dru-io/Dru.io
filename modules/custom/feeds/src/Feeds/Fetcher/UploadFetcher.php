<?php

namespace Drupal\feeds\Feeds\Fetcher;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\Plugin\Type\Fetcher\FetcherInterface;
use Drupal\feeds\Plugin\Type\PluginBase;
use Drupal\feeds\Result\FetcherResult;
use Drupal\feeds\StateInterface;
use Drupal\file\FileUsage\FileUsageInterface;

/**
 * Defines a file upload fetcher.
 *
 * @FeedsFetcher(
 *   id = "upload",
 *   title = @Translation("Upload"),
 *   description = @Translation("Upload content from a local file."),
 *   arguments = {
 *     "@file.usage",
 *     "@entity_type.manager",
 *     "@stream_wrapper_manager"
 *   },
 *   form = {
 *     "configuration" = "Drupal\feeds\Feeds\Fetcher\Form\UploadFetcherForm",
 *     "feed" = "Drupal\feeds\Feeds\Fetcher\Form\UploadFetcherFeedForm",
 *   },
 * )
 */
class UploadFetcher extends PluginBase implements FetcherInterface {

  /**
   * The file usage backend.
   *
   * @var \Drupal\file\FileUsage\FileUsageInterface
   */
  protected $fileUsage;

  /**
   * The file storage backend.
   *
   * @var \Drupal\file\FileStorageInterface
   */
  protected $fileStorage;

  /**
   * The stream wrapper manager.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface
   */
  protected $streamWrapperManager;

  /**
   * Constructs an UploadFetcher object.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin id.
   * @param array $plugin_definition
   *   The plugin definition.
   * @param \Drupal\file\FileUsage\FileUsageInterface $file_usage
   *   The file usage backend.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface $stream_wrapper_manager
   *   The stream wrapper manager.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, FileUsageInterface $file_usage, EntityTypeManagerInterface $entity_type_manager, StreamWrapperManagerInterface $stream_wrapper_manager) {
    $this->fileUsage = $file_usage;
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->streamWrapperManager = $stream_wrapper_manager;

    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function fetch(FeedInterface $feed, StateInterface $state) {
    $file = $feed->getSource();

    if (is_file($file) && is_readable($file)) {
      return new FetcherResult($file);
    }

    // File does not exist.
    throw new \RuntimeException(new FormattableMarkup('Resource is not a file: %source', ['%source' => $file]));
  }

  /**
   * {@inheritdoc}
   */
  public function defaultFeedConfiguration() {
    return ['fid' => 0, 'usage_id' => ''];
  }

  /**
   * {@inheritdoc}
   */
  public function onFeedDeleteMultiple(array $feeds) {
    foreach ($feeds as $feed) {
      $feed_config = $feed->getConfigurationFor($this);
      if ($feed_config['fid']) {
        $this->deleteFile($feed_config['fid'], $feed_config['usage_id']);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $schemes = $this->getSchemes();
    $scheme = in_array('private', $schemes) ? 'private' : reset($schemes);

    return [
      'allowed_extensions' => 'txt csv tsv xml opml',
      'directory' => $scheme . '://feeds',
    ];
  }

  /**
   * Deletes a file.
   *
   * @param int $file_id
   *   The file id.
   * @param string $uuid
   *   The file UUID associated with this file.
   *
   * @see file_delete()
   */
  protected function deleteFile($file_id, $uuid) {
    if ($file = $this->fileStorage->load($file_id)) {
      $this->fileUsage->delete($file, 'feeds', $this->pluginType(), $uuid);
    }
  }

  /**
   * Returns available schemes.
   *
   * @return string[]
   *   The available schemes.
   */
  protected function getSchemes() {
    return array_keys($this->streamWrapperManager->getWrappers(StreamWrapperInterface::WRITE_VISIBLE));
  }

}
