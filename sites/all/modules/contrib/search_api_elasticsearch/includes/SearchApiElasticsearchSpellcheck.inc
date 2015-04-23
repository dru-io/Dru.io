<?php

/**
 * @file
 * Contains SearchApiElasticsearchSpellcheck
 */

class SearchApiElasticsearchSpellcheck extends SearchApiSpellcheck {

  /**
   * __construct
   *
   * @param mixed $data
   * @access public
   * @return void
   */
  public function __construct($data) {
    foreach ($data['suggestions'] as $suggestion) {
      $this->addSuggestion(new SearchApiSpellcheckSuggestion($data['text'], $suggestion));
    }
  }

}
