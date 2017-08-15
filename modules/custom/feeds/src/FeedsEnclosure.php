<?php

namespace Drupal\feeds;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Utility\Html;
use Drupal\file\Entity\File;

/**
 * Enclosure element, can be part of the result array.
 */
class FeedsEnclosure {

  /**
   * The uri of the enclosure.
   *
   * This value can contain be a URL or a filepath.
   *
   * @var string
   */
  protected $uri;

  /**
   * The mimetype of this enclosure.
   *
   * @var string
   */
  protected $mimeType;

  /**
   * Constructs a FeedsEnclosure object.
   *
   * @param string $uri
   *   A path to a local file or a URL to a remote document.
   * @param string $mime_type
   *   (optional) The mime type of the resource. If not provided, the mime type
   *   will be guessed based on the file name.
   */
  public function __construct($uri, $mime_type = NULL) {
    $this->uri = $uri;

    if ($mime_type) {
      $this->mimeType = $mime_type;
    }
    else {
      $this->mimeType = file_get_mimetype($uri);
    }
  }

  /**
   * Returns the content of the enclosure as a string.
   *
   * @return string|false
   *   The content of the referenced resource, or false if the file could not be
   *   read. This should be checked with an ===, since an empty string could be
   *   returned.
   *
   * @throws \RuntimeException
   *   Thrown if the download failed.
   *
   * @todo Better error handling.
   */
  public function getContent() {
    $response = \Drupal::httpClient()->get($this->uri)->send();

    if ($response->getStatusCode() != 200) {
      $args = [
        '%url' => $this->uri,
        '@code' => $response->getStatusCode(),
      ];
      throw new \RuntimeException(new FormattableMarkup('Download of %url failed with code @code.', $args));
    }

    return $response->getBody(TRUE);
  }

  /**
   * Returns a Drupal file object of the enclosed resource.
   *
   * @param string $destination
   *   The path or uri specifying the target directory in which the file is
   *   expected. Don't use trailing slashes unless it's a streamwrapper scheme.
   * @param int $replace
   *   (optional) Replace behavior when the destination file already exists:
   *   - FILE_EXISTS_REPLACE - Replace the existing file. If a managed file with
   *       the destination name exists then its database entry will be updated.
   *       If no database entry is found then a new one will be created.
   *   - FILE_EXISTS_RENAME - Append _{incrementing number} until the filename
   *       is unique.
   *   - FILE_EXISTS_ERROR - Do nothing and return FALSE.
   *   Defaults to FILE_EXISTS_RENAME.
   *
   * @return \Drupal\file\Entity\FileInterface
   *   A Drupal temporary file object of the enclosed resource.
   *
   * @throws \RuntimeException
   *   If file object could not be created.
   *
   * @todo Refactor this
   */
  public function getFile($destination, $replace = FILE_EXISTS_RENAME) {
    $file = FALSE;

    if (!$this->uri) {
      return $file;
    }

    // Prepare destination directory.
    file_prepare_directory($destination, FILE_MODIFY_PERMISSIONS | FILE_CREATE_DIRECTORY);
    // Copy or save file depending on whether it is remote or local.
    if (drupal_realpath($this->uri)) {
      $file = File::create([
        'uid' => 0,
        'uri' => $this->uri,
        'filemime' => $this->mimeType,
        'filename' => basename($this->uri),
      ]);
      if (drupal_dirname($file->getFileUri()) != $destination) {
        $file = file_copy($file, $destination, $replace);
      }
      else {
        // If file is not to be copied, check whether file already exists,
        // as file_save() won't do that for us (compare file_copy() and
        // file_save())
        $existing_files = file_load_multiple([], ['uri' => $file->getFileUri()]);
        if ($existing_files) {
          return reset($existing_files);
        }
        $file->save();
      }
    }
    // Downloading file.
    else {
      $filename = drupal_basename($this->uri);
      if (\Drupal::moduleHandler()->moduleExists('transliteration')) {
        require_once drupal_get_path('module', 'transliteration') . '/transliteration.inc';
        $filename = transliteration_clean_filename($filename);
      }
      if (file_uri_target($destination)) {
        $destination = trim($destination, '/') . '/';
      }
      try {
        $file = file_save_data($this->getContent(), $destination . $filename, $replace);
      }
      catch (\Exception $e) {
        watchdog_exception('Feeds', $e, nl2br(Html::escape($e)));
      }
    }

    // We couldn't make sense of this enclosure, throw an exception.
    if (!$file) {
      throw new \RuntimeException(new FormattableMarkup('Invalid enclosure %enclosure', ['%enclosure' => $this->uri]));
    }

    return $file;
  }

}
