<?php

namespace Drupal\feeds\Tests;

use Drupal\file\Entity\File;
use Drupal\file\Tests\FileFieldTestBase;

/**
 * Tests private files work with Feeds module.
 *
 * @group feeds
 */
class PrivateFileTest extends FileFieldTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['feeds'];

  /**
   * Tests private files work with Feeds module.
   */
  public function testPrivateFile() {
    $node_storage = $this->container->get('entity_type.manager')->getStorage('node');
    $type_name = 'article';
    $field_name = strtolower($this->randomMachineName());
    $this->createFileField($field_name, 'node', $type_name, ['uri_scheme' => 'private']);

    $test_file = $this->getTestFile('text');
    $nid = $this->uploadNodeFile($test_file, $field_name, $type_name, TRUE, ['private' => TRUE]);
    \Drupal::entityTypeManager()->getStorage('node')->resetCache([$nid]);
    /* @var \Drupal\node\NodeInterface $node */
    $node = $node_storage->load($nid);
    $node_file = File::load($node->{$field_name}->target_id);
    // Ensure the file can be viewed.
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw($node_file->getFilename(), 'File reference is displayed after attaching it');
    // Ensure the file can be downloaded.
    $this->drupalGet(file_create_url($node_file->getFileUri()));
    $this->assertResponse(200, 'Confirmed that the generated URL is correct by downloading the shipped file.');
  }

}
