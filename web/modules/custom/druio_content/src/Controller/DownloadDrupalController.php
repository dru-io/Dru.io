<?php

namespace Drupal\druio_content\Controller;

use Composer\Semver\VersionParser;
use Drupal\Core\Controller\ControllerBase;
use http\Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 * Class DownloadDrupalController.
 */
class DownloadDrupalController extends ControllerBase {

  /**
   * Get content for route.
   */
  public function content() {
    try {
      $releases = $this->checkReleases();
      $latest = $this->getLatestStable($releases);
      $link = \Drupal::cache()->get('drupal_download_link')->data;
      $expire = 3600 * 2;
      if (!$link) {
        $link = $this->createLink($latest);
        \Drupal::cache()->set('drupal_download_link', $link, time() + $expire);
      }

      $response = new RedirectResponse($link);
      $response->send();
    }
    catch (Exception $e) {
      \Drupal::logger('druio_content')->error($e->getMessage());
    }

    $response = new RedirectResponse('https://drupal.org/project/drupal');
    $response->send();
  }

  /**
   * Looking for actual releases of core.
   */
  private function checkReleases() {
    $drupal_release_history = file_get_contents('https://updates.drupal.org/release-history/drupal/8.x');
    $result = new \SimpleXMLElement($drupal_release_history);
    $releases = [];
    foreach ($result->releases->release as $release) {
      $version = (string) $release->version;
      $releases[] = [
        'version' => $version,
        'stability' => VersionParser::parseStability($version),
      ];
    }
    return $releases;
  }

  /**
   * Looking for latest stable version in releases.
   */
  private function getLatestStable($releases) {
    foreach ($releases as $release) {
      if ($release['stability'] == 'stable') {
        return $release['version'];
      }
    }
  }

  /**
   * Generate link to drupal archive of specific version.
   *
   * @param string $version
   *   Drupal version. F.e. "8.5.0".
   *
   * @return string
   *   Link to download archive.
   */
  private function createLink($version) {
    return "https://ftp.drupal.org/files/projects/drupal-{$version}.tar.gz";
  }

}
