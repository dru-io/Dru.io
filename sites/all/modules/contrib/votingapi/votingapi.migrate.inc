<?php

/**
 * @file
 * Migration support for voting api.
 */
class MigrateDestinationVotingApiVote extends MigrateDestination {

  protected $importedIds = array();

  /**
   * Threshold of count of id's after which votes will be recalculated.
   *
   * @var int
   */
  protected $idRecalculationThreshold = 1000;

  static public function getKeySchema() {
    return array(
      'vote_id' => array(
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => TRUE,
        'description' => 'Vote ID',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return t('Voting API Vote');
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'vote_id' => 'Vote ID',
      'entity_type' => "Entity Type (defaults to 'node')",
      'entity_id' => 'Entity ID',
      'value' => 'Numeric vote value',
      'value_type' => "Value type (percent/points, defaults to 'percent')",
      'tag' => "Tag (defaults to 'vote')",
      'uid' => 'User ID',
      'timestamp' => 'Timestamp',
      'vote_source' => 'Vote Source IP Address',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function import(stdClass $votingapi_vote, stdClass $row) {
    $updating = FALSE;
    $vote = array();
    if (isset($row->migrate_map_destid1)) {
      // We're updating an existing vote - start from the previous data.
      $votes = votingapi_select_votes(array('vote_id' => $row->migrate_map_destid1));
      if (count($votes)) {
        $vote = reset($votes);
        $updating = TRUE;
      }
    }

    if (!$updating) {
      unset($votingapi_vote->vote_id);
    }

    $this->prepare($votingapi_vote, $row);

    // Votes have to be assigned to an entity.
    if (empty($votingapi_vote->entity_id)) {
      watchdog('VotingAPI Import', 'Skipped import due to empty entity_id for vote import: @serial', array('@serial' => json_encode($row)), WATCHDOG_ERROR);
      return FALSE;
    }

    migrate_instrument_start('votingapi_vote_save');
    // Just converting $entity to an array doesn't work...
    $vote = array();
    foreach ((array) $votingapi_vote as $field => $value) {
      $vote[$field] = $value;
    }
    votingapi_add_votes($vote);
    migrate_instrument_stop('votingapi_vote_save');

    if (isset($vote[0]['vote_id'])) {
      $votingapi_vote->vote_id = $vote[0]['vote_id'];
      $this->complete($votingapi_vote, $row);

      // Store the ID in the hash as it is faster than getting uniques later.
      $this->importedIds[$votingapi_vote->entity_type][$votingapi_vote->entity_id] = TRUE;

      // If we hit the threshold of entity id's we imported, we should
      // recalculate to avoid excessive memory usage (and clear out the array).
      if (count($this->importedIds[$votingapi_vote->entity_type]) >= $this->idRecalculationThreshold) {
        $this->recalculateResults($votingapi_vote->entity_type, array_keys($this->importedIds[$votingapi_vote->entity_type]));
        $this->importedIds[$votingapi_vote->entity_type] = array();
      }

      if ($updating) {
        $this->numUpdated++;
      }
      else {
        $this->numCreated++;
      }
      return array($votingapi_vote->vote_id);
    }
    else {
      return FALSE;
    }
  }

  /**
   * Recalculate votes for a list of id's.
   *
   * @param string $entity_type
   *   The entity type of the id's
   * @param array $ids
   *   List of the ID's for which votes are to be recalculated.
   */
  protected function recalculateResults($entity_type, array $ids) {
    foreach ($ids as $entity_id) {
      votingapi_recalculate_results($entity_type, $entity_id, TRUE);
    }
  }

  /**
   * We're done with importing votes, recalculate the results.
   */
  public function postImport() {
    foreach ($this->importedIds as $entity_type => $entity_ids) {
      $this->recalculateResults($entity_type, array_keys($entity_ids));
    }
  }

  /**
   * Delete the provided votes and recalculate the results.
   *
   * @param array $ids
   *   ID's to be deleted.
   */
  public function bulkRollback(array $ids) {
    migrate_instrument_start(__METHOD__);

    foreach ($ids as $id) {
      $votes[]['vote_id'] = $id;
    }
    votingapi_delete_votes($votes);

    migrate_instrument_stop(__METHOD__);
  }

  public function prepare($vote, stdClass $source_row) {
    $migration = Migration::currentMigration();
    if (method_exists($migration, 'prepare')) {
      $vote->migrate = array(
        'machineName' => $migration->getMachineName(),
      );
      $migration->prepare($vote, $source_row);
    }
  }

  public function complete($entity, stdClass $source_row) {
    // Call any complete handler for this specific Migration.
    $migration = Migration::currentMigration();
    if (method_exists($migration, 'complete')) {
      try {
        $migration->complete($entity, $source_row);
      } catch (Exception $e) {
        // If we catch any errors here, save the messages without letting
        // the exception prevent the saving of the question.
        $migration->saveMessage($e->getMessage());
      }
    }
  }

  /**
   * Give handlers a shot at cleaning up before a question has been rolled back.
   *
   * @param $entity_id
   *  ID of the entity about to be deleted..
   */
  public function prepareRollback($entity_id) {
    // Call any prepare handler for this specific Migration.
    $migration = Migration::currentMigration();
    if (method_exists($migration, 'prepareRollback')) {
      $migration->prepareRollback($entity_id);
    }
  }

  /**
   * Give handlers a shot at cleaning up after an entity has been rolled back.
   *
   * @param $entity_id
   *  ID of the entity which has been deleted.
   */
  public function completeRollback($entity_id) {
    // Call any complete handler for this specific Migration.
    $migration = Migration::currentMigration();
    if (method_exists($migration, 'completeRollback')) {
      $migration->completeRollback($entity_id);
    }
  }

}
