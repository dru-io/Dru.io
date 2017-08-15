<?php

namespace Drupal\Tests\feeds\Unit {

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use org\bovigo\vfs\vfsStream;

/**
 * Base class for Feeds unit tests.
 */
abstract class FeedsUnitTestCase extends UnitTestCase {

  public function setUp() {
    parent::setUp();

    $this->defineConstants();
    vfsStream::setup('feeds');
  }

  protected function getMockFeedType() {
    $feed_type = $this->getMock('\Drupal\feeds\FeedTypeInterface');
    $feed_type->id = 'test_feed_type';
    $feed_type->description = 'This is a test feed type';
    $feed_type->label = 'Test feed type';
    $feed_type->expects($this->any())
             ->method('label')
             ->will($this->returnValue($feed_type->label));
    return $feed_type;
  }

  protected function getMockFeed() {
    $feed = $this->getMock('Drupal\feeds\FeedInterface');
    $feed->expects($this->any())
      ->method('getType')
      ->will($this->returnValue($this->getMockFeedType()));
    return $feed;
  }

  /**
   * Returns a mock stream wrapper manager.
   *
   * @return \Drupal\Core\StreamWrapper\StreamWrapperManager
   *   A mocked stream wrapper manager.
   */
  protected function getMockStreamWrapperManager() {
    $mock = $this->getMock('Drupal\Core\StreamWrapper\StreamWrapperManager', [], [], '', FALSE);

    $wrappers = [
      'vfs' => 'VFS',
      'public' => 'Public',
    ];

    $mock->expects($this->any())
      ->method('getDescriptions')
      ->will($this->returnValue($wrappers));

    $mock->expects($this->any())
      ->method('getWrappers')
      ->will($this->returnValue($wrappers));

    return $mock;
  }

  protected function getMethod($class, $name) {
    $class = new \ReflectionClass($class);
    $method = $class->getMethod($name);
    $method->setAccessible(TRUE);
    return $method;
  }

  /**
   * Returns a mocked AccountSwitcher object.
   *
   * The returned object verifies that if switchTo() is called, switchBack() is
   * also called.
   *
   * @return \Drupal\Core\Session\AccountSwitcherInterface
   */
  protected function getMockedAccountSwitcher() {
    $switcher = $this->prophesize(AccountSwitcherInterface::class);

    $switcher->switchTo(Argument::type(AccountInterface::class))
      ->will(function () use ($switcher) {
        $switcher->switchBack()->shouldBeCalled();

        return $switcher->reveal();
    });

    return $switcher->reveal();
  }

  protected function getProtectedClosure($object, $method) {
    return $this->getMethod(get_class($object), $method)->getClosure($object);
  }

  protected function callProtectedMethod($object, $method, array $args = []) {
    $closure = $this->getProtectedClosure($object, $method);
    return call_user_func_array($closure, $args);
  }

  protected function getMockAccount(array $perms = []) {
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    if ($perms) {
      $map = [];
      foreach ($perms as $perm => $has) {
        $map[] = [$perm, $has];
      }
      $account->expects($this->any())
              ->method('hasPermission')
              ->will($this->returnValueMap($map));
    }

    return $account;
  }

  protected function getMockFieldDefinition(array $settings = []) {
    $definition = $this->getMock('Drupal\Core\Field\FieldDefinitionInterface');
    $definition->expects($this->any())
      ->method('getSettings')
      ->will($this->returnValue($settings));
    return $definition;
  }

  /**
   * Defines stub constants.
   */
  protected function defineConstants() {
    if (!defined('DATETIME_STORAGE_TIMEZONE')) {
      define('DATETIME_STORAGE_TIMEZONE', 'UTC');
    }
    if (!defined('DATETIME_DATETIME_STORAGE_FORMAT')) {
      define('DATETIME_DATETIME_STORAGE_FORMAT', 'Y-m-d\TH:i:s');
    }
    if (!defined('DATETIME_DATE_STORAGE_FORMAT')) {
      define('DATETIME_DATE_STORAGE_FORMAT', 'Y-m-d');
    }

    if (!defined('FILE_MODIFY_PERMISSIONS')) {
      define('FILE_MODIFY_PERMISSIONS', 2);
    }
    if (!defined('FILE_CREATE_DIRECTORY')) {
      define('FILE_CREATE_DIRECTORY', 1);
    }
    if (!defined('FILE_EXISTS_REPLACE')) {
      define('FILE_EXISTS_REPLACE', 1);
    }
    if (!defined('FILE_STATUS_PERMANENT')) {
      define('FILE_STATUS_PERMANENT', 1);
    }
  }

}
}

namespace {
  use Drupal\Core\Session\AccountInterface;

  if (!function_exists('drupal_set_message')) {
    function drupal_set_message() {}
  }

  if (!function_exists('filter_formats')) {
    function filter_formats(AccountInterface $account) {
      return ['test_format' => new FeedsFilterStub('Test format')];
    }
  }

  if (!function_exists('file_stream_wrapper_uri_normalize')) {
    function file_stream_wrapper_uri_normalize($dir) {
      return $dir;
    }
  }

  if (!function_exists('drupal_tempnam')) {
    function drupal_tempnam($scheme, $dir) {
      mkdir('vfs://feeds/' . $dir);
      $file = 'vfs://feeds/' . $dir . '/' . mt_rand(10, 1000);
      touch($file);
      return $file;
    }
  }

  if (!function_exists('file_prepare_directory')) {
    function file_prepare_directory(&$directory) {
      return mkdir($directory);
    }
  }

  if (!function_exists('drupal_basename')) {
    function drupal_basename($uri, $suffix = NULL) {
      return basename($uri, $suffix);
    }
  }

  if (!function_exists('drupal_get_user_timezone')) {
    function drupal_get_user_timezone() {
      return 'UTC';
    }
  }

  if (!function_exists('batch_set')) {
    function batch_set() {}
  }


  if (!function_exists('_format_date_callback')) {
    function _format_date_callback(array $matches = NULL, $new_langcode = NULL) {
      // We cache translations to avoid redundant and rather costly calls to t().
      static $cache, $langcode;

      if (!isset($matches)) {
        $langcode = $new_langcode;
        return;
      }

      $code = $matches[1];
      $string = $matches[2];

      if (!isset($cache[$langcode][$code][$string])) {
        $options = [
          'langcode' => $langcode,
        ];

        if ($code == 'F') {
          $options['context'] = 'Long month name';
        }

        if ($code == '') {
          $cache[$langcode][$code][$string] = $string;
        }
        else {
          $cache[$langcode][$code][$string] = t($string, [], $options);
        }
      }
      return $cache[$langcode][$code][$string];
    }
  }

  class FeedsFilterStub {
    public function __construct($label) {
      $this->label = $label;
    }

    public function label() {
      return $this->label;
    }

  }
}
