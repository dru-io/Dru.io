<?php

namespace Drupal\druio_question;

use Drupal\Core\Entity\EntityTypeManager;

/**
 * Class DruioQuestionService.
 */
class DruioQuestionService {

  /**
   * @var EntityTypeManager.
   */
  protected $entityTypeManager;

  /**
   * Constructs a new DruioQuestionService object.
   */
  public function __construct(EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Returns how much question has answers.
   *
   * @param int $qid
   *   Question node ID for which answer count must be returned.
   *
   * @return int
   *   The answers count for question.
   */
  public function getQuestionAnswerCount($qid) {
    $query = $this->entityTypeManager->getStorage('comment')
      ->getQuery()
      ->condition('comment_type', 'question_answer')
      ->condition('entity_id', $qid);
    return $query->count()->execute();
  }

}
