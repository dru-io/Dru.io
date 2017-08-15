<?php

namespace Drupal\feeds\Result;

use Drupal\Component\Render\FormattableMarkup;

/**
 * The default fetcher result object.
 */
class FetcherResult implements FetcherResultInterface {

  /**
   * The filepath of the fetched item.
   *
   * @var string
   */
  protected $filePath;

  /**
   * Constructs a new FetcherResult object.
   *
   * @param string $file_path
   *   The path to the result file.
   */
  public function __construct($file_path) {
    $this->filePath = $file_path;
  }

  /**
   * {@inheritdoc}
   */
  public function getRaw() {
    $this->checkFile();
    return $this->sanitizeRaw(file_get_contents($this->filePath));
  }

  /**
   * {@inheritdoc}
   */
  public function getFilePath() {
    $this->checkFile();
    return $this->sanitizeFile();
  }

  /**
   * Checks that a file exists and is readable.
   *
   * @throws \RuntimeException
   *   Thrown if the file isn't readable or writable.
   */
  protected function checkFile() {
    if (!file_exists($this->filePath)) {
      throw new \RuntimeException(new FormattableMarkup('File %filepath does not exist.', ['%filepath' => $this->filePath]));
    }

    if (!is_readable($this->filePath)) {
      throw new \RuntimeException(new FormattableMarkup('File %filepath is not readable.', ['%filepath' => $this->filePath]));
    }
  }

  /**
   * Sanitizes the raw content string.
   *
   * Currently supported sanitizations:
   * - Remove BOM header from UTF-8 files.
   *
   * @param string $raw
   *   The raw content string to be sanitized.
   *
   * @return string
   *   The sanitized content as a string.
   */
  protected function sanitizeRaw($raw) {
    if (substr($raw, 0, 3) == pack('CCC', 0xef, 0xbb, 0xbf)) {
      $raw = substr($raw, 3);
    }

    return $raw;
  }

  /**
   * Sanitizes the file in place.
   *
   * Currently supported sanitizations:
   * - Remove BOM header from UTF-8 files.
   *
   * @return string
   *   The file path of the sanitized file.
   *
   * @throws \RuntimeException
   *   Thrown if the file is not writable.
   */
  protected function sanitizeFile() {
    $handle = fopen($this->filePath, 'r');
    $line = fgets($handle);
    fclose($handle);

    // If BOM header is present, read entire contents of file and overwrite the
    // file with corrected contents.
    if (substr($line, 0, 3) !== pack('CCC', 0xef, 0xbb, 0xbf)) {
      return $this->filePath;
    }

    if (!is_writable($this->filePath)) {
      throw new \RuntimeException(new FormattableMarkup('File %filepath is not writable.', ['%filepath' => $this->filePath]));
    }

    $contents = file_get_contents($this->filePath);
    $contents = substr($contents, 3);
    $status = file_put_contents($this->filePath, $contents);

    return $this->filePath;
  }

}
