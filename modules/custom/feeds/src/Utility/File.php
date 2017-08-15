<?php

namespace Drupal\feeds\Utility;

/**
 * Helper functions for dealing with files.
 */
class File {

  /**
   * Checks that the filename ends with an allowed extension.
   *
   * @param string $filename
   *   A file path.
   * @param string $extensions
   *   A string with a space separated list of allowed extensions.
   *
   * @return bool
   *   Returns true if the file is valid, false if not.
   */
  public static function validateExtension($filename, $extensions) {
    $regex = '/\.(' . preg_replace('/ +/', '|', preg_quote($extensions)) . ')$/i';

    return (bool) preg_match($regex, $filename);
  }

}
