<?php
/**
 * @file
 * Rate widget theme
 * @TODO: Переписать node_load на простой запрос.
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

// Load not for node-type.
$node = node_load($content_id);

if ($node && $node->type == 'question' || $node->type == 'answer'): ?>
  <?php
  if ($links[0]['href']) {
    $plus_class = '';
    $user_vote == 'plus' ? $plus_class = ' voted' : '';
    print '<a href="' . $links[0]['href'] . '" class="rate-plus' . $plus_class . '"></a>';
  }
  else {
    print '<span class="rate-plus disabled"></span>';
  }
  ?>
  <div class="rate-result"><?php print $results['rating']; ?></div>
  <?php
  if ($links[1]['href']) {
    $minus_class = '';
    $user_vote == 'minus' ? $minus_class = ' voted' : '';
    print '<a href="' . $links[1]['href'] . '" class="rate-minus' . $minus_class . '"></a>';
  }
  else {
    print '<span class="rate-minus disabled"></span>';
  }
  ?>
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