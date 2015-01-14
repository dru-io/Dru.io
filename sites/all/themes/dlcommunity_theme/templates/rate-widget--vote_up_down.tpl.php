<?php
/**
 * @file
 * Rate widget theme
 *
 * This is the default template for rate widgets. See section 3 of the README
 * file for information on theming widgets.
 *
 * Available variables:
 * - $links: Array with vote links
 *     array(
 *       array(
 *         'text' => 'Button label',
 *         'href' => 'Link href',
 *         'value' => 20,
 *         'votes' => 6,
 *       ),
 *     )
 * - $results: Array with voting results
 *     array(
 *       'rating' => 12, // Average rating
 *       'options' => array( // Votes per option. Only available when value_type == 'options'
 *         1 => 234,
 *         2 => 34,
 *       ),
 *       'count' => 23, // Number of votes
 *       'up' => 2, // Number of up votes. Only available for thumbs up / down.
 *       'down' => 3, // Number of down votes. Only available for thumbs up / down.
 *       'up_percent' => 40, // Percentage of up votes. Only available for thumbs up / down.
 *       'down_percent' => 60, // Percentage of down votes. Only available for thumbs up / down.
 *       'user_vote' => 80, // Value for user vote. Only available when user has voted.
 *     )
 * - $mode: Display mode.
 * - $just_voted: Indicator whether the user has just voted (boolean).
 * - $content_type: "node" or "comment".
 * - $content_id: Node or comment id.
 * - $buttons: Array with themed buttons (built in preprocess function).
 * - $info: String with user readable information (built in preprocess function).
 */
if (isset($results['user_vote'])) {
  if ($results['user_vote'] == 1) {
    $user_vote = 'plus';
  }
  elseif ($results['user_vote'] == -1) {
    $user_vote = 'minus';
  }
}
else {
  $user_vote = 'none';
}

$node = node_load($content_id);

if ($node && $node->type == 'question'):
  ?>
  <div>
    <?php
    if ($links[0]['href']) {
      $plus_class = '';
      $user_vote == 'plus' ? $plus_class = ' user-vote' : '';
      print '<a href="' . $links[0]['href'] . '" class="rate-button rate-btn icon-up-open rate-plus' . $plus_class . '"></a>';
    }
    else {
      print '<span class="icon-up-open rate-btn disabled"></span>';
    }
    ?>
  </div>

  <div class="rate-result"><?php print $results['rating']; ?></div>

  <div>
    <?php
    if ($links[1]['href']) {
      $minus_class = '';
      $user_vote == 'minus' ? $minus_class = ' user-vote' : '';
      print '<a href="' . $links[1]['href'] . '" class="rate-button rate-btn icon-down-open rate-minus' . $minus_class . '"></a>';
    }
    else {
      print '<span class="icon-down-open rate-btn disabled"></span>';
    }
    ?>
  </div>
<?php
else: ?>
  <div class="post-bottom__item">
    <div class="rate-item">
      <?php
      if ($links[0]['href']) {
        $plus_class = '';
        $user_vote == 'plus' ? $plus_class = ' user-vote' : '';
        print '<a href="' . $links[0]['href'] . '" class="rate-button rate-btn icon-up-open rate-plus' . $plus_class . '"></a>';
      }
      else {
        print '<span class="icon-up-open rate-btn disabled"></span>';
      }
      ?>
    </div>

    <div class="rate-item rate-result"><?php print $results['rating']; ?></div>

    <div class="rate-item">
      <?php
      if ($links[1]['href']) {
        $minus_class = '';
        $user_vote == 'minus' ? $minus_class = ' user-vote' : '';
        print '<a href="' . $links[1]['href'] . '" class="rate-button rate-btn icon-down-open rate-minus' . $minus_class . '"></a>';
      }
      else {
        print '<span class="icon-down-open rate-btn disabled"></span>';
      }
      ?>
    </div>
  </div>
<?php endif; ?>