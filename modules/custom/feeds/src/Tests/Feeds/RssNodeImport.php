<?php

namespace Drupal\feeds\Tests\Feeds;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\feeds\Entity\Feed;
use Drupal\feeds\FeedTypeInterface;
use Drupal\feeds\Plugin\Type\Processor\ProcessorInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\simpletest\WebTestBase;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\node\Entity\Node;

/**
 * Integration test that imports nodes from an RSS feed.
 *
 * @group feeds
 */
class RssNodeImport extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['block', 'taxonomy', 'node', 'feeds'];

  protected function setUp() {
    parent::setUp();
    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);

    Vocabulary::create(['vid' => 'tags', 'name' => 'Tags'])->save();

    FieldStorageConfig::create([
      'field_name' => 'field_tags',
      'entity_type' => 'node',
      'type' => 'entity_reference',
      'settings' => [
        'target_type' => 'taxonomy_term',
      ],
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
    ])->save();

    FieldConfig::create([
      'label' => 'Tags',
      'description' => '',
      'field_name' => 'field_tags',
      'entity_type' => 'node',
      'bundle' => 'article',
      'settings' => [
        'handler_settings' => [
          'target_bundles' => [
            'tags' => 'tags',
          ],
        ],
      ],
    ])->save();

    $web_user = $this->drupalCreateUser(['administer feeds', 'bypass node access']);
    $this->drupalLogin($web_user);

    $this->type = entity_create('feeds_feed_type', [
      'id' => Unicode::strtolower($this->randomMachineName()),
      'mappings' => [
        [
          'target' => 'title',
          'map' => ['value' => 'title'],
        ],
        [
          'target' => 'body',
          'map' => ['value' => 'description'],
        ],
        [
          'target' => 'feeds_item',
          'map' => ['guid' => 'guid', 'url' => 'url'],
          'unique' => ['guid' => TRUE],
        ],
        [
          'target' => 'created',
          'map' => ['value' => 'timestamp'],
        ],
        [
          'target' => 'field_tags',
          'map' => ['target_id' => 'tags'],
          'settings' => ['autocreate' => TRUE],
        ],
      ],
      'processor' => 'entity:node',
      'processor_configuration' => ['values' => ['type' => 'article']],
      'import_period' => FeedTypeInterface::SCHEDULE_NEVER,
    ]);
    $this->type->save();

    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('local_actions_block');
    $this->drupalPlaceBlock('system_messages_block');
  }

  public function testHttpImport() {
    $filepath = drupal_get_path('module', 'feeds') . '/tests/resources/googlenewstz.rss2';

    $feed = entity_create('feeds_feed', [
      'title' => $this->randomString(),
      'source' => file_create_url($filepath),
      'type' => $this->type->id(),
    ]);
    $feed->save();
    $this->drupalGet('feed/' . $feed->id());
    $this->clickLink(t('Import'));
    $this->drupalPostForm(NULL, [], t('Import'));
    $this->assertText('Created 6');
    $this->assertEqual(\Drupal::database()->query("SELECT COUNT(*) FROM {node}")->fetchField(), 6);

    $xml = new \SimpleXMLElement($filepath, 0, TRUE);

    foreach (range(1, 6) as $nid) {
      $item = $xml->channel->item[$nid - 1];
      $node = Node::load($nid);
      $this->assertEqual($node->title->value, (string) $item->title);
      $this->assertEqual($node->body->value, (string) $item->description);
      $this->assertEqual($node->feeds_item->guid, (string) $item->guid);
      $this->assertEqual($node->feeds_item->url, (string) $item->link);
      $this->assertEqual($node->created->value, strtotime((string) $item->pubDate));

      $terms = [];
      foreach ($node->field_tags as $value) {
        // $terms[] = Term::load([$value['target_id']])->label();
      }
    }

    // Test cache.
    $this->drupalPostForm('feed/' . $feed->id() . '/import', [], t('Import'));
    $this->assertText('The feed has not been updated.');

    // Import again.
    \Drupal::cache('feeds_download')->deleteAll();
    $this->drupalPostForm('feed/' . $feed->id() . '/import', [], t('Import'));
    $this->assertText('There are no new');

    // Test force-import.
    \Drupal::cache('feeds_download')->deleteAll();
    $configuration = $this->type->getProcessor()->getConfiguration();
    $configuration['skip_hash_check'] = TRUE;
    $configuration['update_existing'] = ProcessorInterface::UPDATE_EXISTING;
    $this->type->getProcessor()->setConfiguration($configuration);
    $this->type->save();
    $this->drupalPostForm('feed/' . $feed->id() . '/import', [], t('Import'));
    $this->assertEqual(\Drupal::database()->query("SELECT COUNT(*) FROM {node}")->fetchField(), 6);
    $this->assertText('Updated 6');

    // Delete items.
    $this->clickLink(t('Delete items'));
    $this->drupalPostForm(NULL, [], t('Delete items'));
    $this->assertEqual(\Drupal::database()->query("SELECT COUNT(*) FROM {node}")->fetchField(), 0);
    $this->assertText('Deleted 6');
  }

  public function testCron() {
    // Run cron once before, so any other bookkeeping can get done.
    $this->cronRun();

    $this->type->setImportPeriod(3600);
    $mappings = $this->type->getMappings();
    unset($mappings[2]['unique']);
    $this->type->setMappings($mappings);
    $this->type->save();

    $filepath = drupal_get_path('module', 'feeds') . '/tests/resources/googlenewstz.rss2';

    $feed = Feed::create([
      'title' => $this->randomString(),
      'source' => file_create_url($filepath),
      'type' => $this->type->id(),
    ]);
    $feed->save();

    // Verify initial values.
    $feed = $this->reloadFeed($feed->id());

    $this->assertEqual($feed->getImportedTime(), 0);
    $this->assertEqual($feed->getNextImportTime(), 0);
    $this->assertEqual($feed->getItemCount(), 0);

    // Cron should import some nodes.
    // Clear the download cache so that the http fetcher doesn't trick us.
    \Drupal::cache('feeds_download')->deleteAll();
    sleep(1);
    $this->cronRun(); // Run cron twice for testbot.
    $this->cronRun();
    $feed = $this->reloadFeed($feed->id());

    $this->assertEqual($feed->getItemCount(), 6);
    $imported = $feed->getImportedTime();
    $this->assertTrue($imported > 0);
    $this->assertEqual($feed->getNextImportTime(), $imported + 3600);

    // Nothing should change on this cron run.
    \Drupal::cache('feeds_download')->deleteAll();
    sleep(1);
    $this->cronRun();
    $this->cronRun();
    $feed = $this->reloadFeed($feed->id());

    $this->assertEqual($feed->getItemCount(), 6);
    $this->assertEqual($feed->getImportedTime(), $imported);
    $this->assertEqual($feed->getNextImportTime(), $imported + 3600);

    // Check that items import normally.
    \Drupal::cache('feeds_download')->deleteAll();
    sleep(1);
    $this->drupalPostForm('feed/' . $feed->id() . '/import', [], t('Import'));
    $feed = $this->reloadFeed($feed->id());

    $manual_imported_time = $feed->getImportedTime();
    $this->assertEqual($feed->getItemCount(), 12);
    $this->assertTrue($manual_imported_time > $imported);
    $this->assertEqual($feed->getNextImportTime(), $feed->getImportedTime() + 3600);

    // Change the next time so that the feed should be scheduled. Then, disable
    // it to ensure the status is respected.
    // Nothing should change on this cron run.
    $feed = $this->reloadFeed($feed->id());
    $feed->set('next', 0);
    $feed->setActive(FALSE);
    $feed->save();

    \Drupal::cache('feeds_download')->deleteAll();
    sleep(1);
    $this->cronRun();
    $this->cronRun();
    $feed = $this->reloadFeed($feed->id());

    $this->assertEqual($feed->getItemCount(), 12);
    $this->assertEqual($feed->getImportedTime(), $manual_imported_time);
    $this->assertEqual($feed->getNextImportTime(), 0);
  }

  protected function reloadFeed($fid) {
    $this->container
      ->get('entity_type.manager')
      ->getStorage('feeds_feed')
      ->resetCache();

    return Feed::load($fid);
  }

}
