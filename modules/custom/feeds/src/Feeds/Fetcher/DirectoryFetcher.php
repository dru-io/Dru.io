<?php

namespace Drupal\feeds\Feeds\Fetcher;

use Drupal\Core\Form\FormStateInterface;
use Drupal\feeds\Exception\EmptyFeedException;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\Plugin\Type\Fetcher\FetcherInterface;
use Drupal\feeds\Plugin\Type\PluginBase;
use Drupal\feeds\Result\FetcherResult;
use Drupal\feeds\StateInterface;
use Drupal\feeds\Utility\File;

/**
 * Defines a directory fetcher.
 *
 * @FeedsFetcher(
 *   id = "directory",
 *   title = @Translation("Directory"),
 *   description = @Translation("Uses a directory, or file, on the server."),
 *   form = {
 *     "configuration" = "Drupal\feeds\Feeds\Fetcher\Form\DirectoryFetcherForm",
 *     "feed" = "\Drupal\feeds\Feeds\Fetcher\Form\DirectoryFetcherFeedForm",
 *   },
 * )
 */
class DirectoryFetcher extends PluginBase implements FetcherInterface {

  /**
   * {@inheritdoc}
   */
  public function fetch(FeedInterface $feed, StateInterface $state) {
    $path = $feed->getSource();
    // Just return a file fetcher result if this is a file. Make sure to
    // re-validate the file extension in case the feed type settings have
    // changed.
    if (is_file($path)) {
      if (File::validateExtension($path, $this->configuration['allowed_extensions'])) {
        return new FetcherResult($path);
      }
      else {
        throw new \RuntimeException($this->t('%source has an invalid file extension.', ['%source' => $path]));
      }
    }

    if (!is_dir($path) || !is_readable($path)) {
      throw new \RuntimeException($this->t('%source is not a readable directory or file.', ['%source' => $path]));
    }

    // Batch if this is a directory.
    if (!isset($state->files)) {
      $state->files = $this->listFiles($path);
      $state->total = count($state->files);
    }
    if ($state->files) {
      $file = array_shift($state->files);
      $state->progress($state->total, $state->total - count($state->files));
      return new FetcherResult($file);
    }

    throw new EmptyFeedException();
  }

  /**
   * Returns an array of files in a directory.
   *
   * @param string $dir
   *   A stream wrapper URI that is a directory.
   *
   * @return string[]
   *   An array of stream wrapper URIs pointing to files.
   */
  protected function listFiles($dir) {
    $flags =
      \FilesystemIterator::KEY_AS_PATHNAME |
      \FilesystemIterator::CURRENT_AS_FILEINFO |
      \FilesystemIterator::SKIP_DOTS;

    if ($this->configuration['recursive_scan']) {
      $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, $flags));
    }
    else {
      $iterator = new \FilesystemIterator($dir, $flags);
    }

    $files = [];
    foreach ($iterator as $path => $file) {
      if ($file->isFile() && $file->isReadable() && File::validateExtension($path, $this->configuration['allowed_extensions'])) {
        $files[] = $path;
      }
    }

    return $files;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultFeedConfiguration() {
    return ['source' => ''];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'allowed_extensions' => 'txt csv tsv xml opml',
      'allowed_schemes' => ['public'],
      'recursive_scan' => FALSE,
    ];
  }

}
