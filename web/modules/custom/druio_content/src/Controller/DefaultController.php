<?php

namespace Drupal\druio_content\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector\CssSelectorConverter;
use Composer\Semver\VersionParser;
use Composer\Semver\Semver;



/**
 * Class DefaultController.
 */
class DefaultController extends ControllerBase {
  
  /**
   * Getlink.
   *
   * @return string
   *   Return Hello string.
   */
  public function getLink() {
    $releases = $this->checkReleases();
    $latest = $this->checkLatest($releases);
    $link = \Drupal::cache()->get('drupal_download_link')->data;
    $expire = 3600*2;
    if (!$link) {
      $link = $this->createLink($latest);
      \Drupal::cache()->set('drupal_download_link', $link, time() + $expire);
    }

    $response = new RedirectResponse($link);
    $response->send();
    return;
  }

  private function checkReleases() {
    $html = file_get_contents('https://www.drupal.org/project/drupal/releases');
    $crawler = new Crawler($html);
    $domElements = $crawler->filter('.field-name-field-release-vcs-label .field-item')->each(
      function (Crawler $node, $i) {
        return $node->text();
      }
    );
    $releases = array();
    foreach($domElements as $release) {
      $releases[] = array(
        'release' => $release,
        'stability' => VersionParser::parseStability($release)
      );
    }
    return $releases;
  }

  private function checkLatest($releases) {
    $stable = array_filter($releases, array($this, 'checkStable'));
    $stable_semver = array();
    foreach($stable as $el) {
      $el = $el['release'];
      if (!strpos($el, 'x')) {
        $stable_semver[] = $el;
      }
    }
    $latest = Semver::rsort($stable_semver)[0];
    return $latest;
  }

  private function checkStable($release) {
    return ($release['stability'] == 'stable');
  }

  private function createLink($version) {
    $link = 'https://ftp.drupal.org/files/projects/drupal-' . $version . '.tar.gz';
    return $link;
  }

}
