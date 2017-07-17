<?php

namespace Drupal\feeds\Feeds\Fetcher\Form;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\Plugin\Type\ExternalPluginFormBase;
use Drupal\file\FileStorageInterface;
use Drupal\file\FileUsage\FileUsageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form on the feed edit page for the UploadFetcher.
 */
class UploadFetcherFeedForm extends ExternalPluginFormBase implements ContainerInjectionInterface {

  /**
   * The file storage backend.
   *
   * @var \Drupal\file\FileStorageInterface
   */
  protected $fileStorage;

  /**
   * The file usage backend.
   *
   * @var \Drupal\file\FileUsage\FileUsageInterface
   */
  protected $fileUsage;

  /**
   * The UUID generator.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuid;

  /**
   * Constructs an HttpFeedForm object.
   *
   * @param \Drupal\file\FileStorageInterface $file_storage
   *   The file storage backend.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid
   *   The UUID generator.
   */
  public function __construct(FileStorageInterface $file_storage, FileUsageInterface $file_usage, UuidInterface $uuid) {
    $this->fileStorage = $file_storage;
    $this->fileUsage = $file_usage;
    $this->uuid = $uuid;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('file'),
      $container->get('file.usage'),
      $container->get('uuid')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FeedInterface $feed = NULL) {
    $form['source'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('File'),
      '#description' => $this->t('Select a file from your local system.'),
      '#default_value' => [$feed->getConfigurationFor($this->plugin)['fid']],
      '#upload_validators' => [
        'file_validate_extensions' => [
          $this->plugin->getConfiguration('allowed_extensions'),
        ],
      ],
      '#upload_location' => $this->plugin->getConfiguration('directory'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state, FeedInterface $feed = NULL) {
    // We need to store this for later so that we have the feed id.
    $new_fid = reset($form_state->getValue('source'));
    $feed_config = $feed->getConfigurationFor($this->plugin);

    // Generate a UUID that maps to this feed for file usage. We can't depend
    // on the feed id since this could be called before an id is assigned.
    $feed_config['usage_id'] = $feed_config['usage_id'] ?: $this->uuid->generate();

    if ($new_fid != $feed_config['fid']) {
      $this->deleteFile($feed_config['fid'], $feed_config['usage_id']);

      if ($new_fid) {
        $file = $this->fileStorage->load($new_fid);
        $this->fileUsage->add($file, 'feeds', $this->plugin->pluginType(), $feed_config['usage_id']);
        $file->setPermanent();
        $file->save();

        $feed_config['fid'] = $new_fid;
        $feed->setSource($file->getFileUri());
      }
    }

    $feed->setConfigurationFor($this->plugin, $feed_config);
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
      $this->fileUsage->delete($file, 'feeds', $this->plugin->pluginType(), $uuid);
    }
  }

}
