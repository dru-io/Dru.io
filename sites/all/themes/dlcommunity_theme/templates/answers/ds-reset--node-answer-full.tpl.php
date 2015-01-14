<?php
/**
 * @file
 * HTML for a node.
 *
 * Default variables you can find here: https://api.drupal.org/api/drupal/modules!node!node.tpl.php/7
 * New variables available:
 * - $clean_classes: This is replacement for default $classes. They are more
 *   tidy and handy. F.e.:
 *     .[node-type]
 *     .[display-mode]
 *     .teaser
 */
$author = user_load($node->uid);
if (arg(0) == 'node') {
  drupal_goto('node/' . $field_answer_question_reference[0]['target_id']);
}
?>
<article class="gl-g <?php print $clean_classes; ?>"<?php print $attributes; ?>>

  <div class="gl-s-5-24 gl-s-lg-3-24 gl-s-xl-2-24">
    <?php
    print rate_embed($node, 'vote_up_down', RATE_FULL);
    print flag_create_link('best_answer', $node->nid);

    /*if ($user->uid != $node->uid) {
      $flag = flag_get_flag('best_answer');
      if ($flag->get_count($node->nid)) {
        print '<span class="best-answer icon-ok-circle" title="Автор вопроса пометил данный ответ как \'Как лучший ответ\' "></span>';
      }
    }*/
    ?>
  </div>

  <div class="gl-s-19-24 gl-s-lg-21-24 gl-s-xl-22-24">
    <?php if ($title_prefix || $title_suffix): ?>
      <?php print render($title_prefix); ?>
      <?php print render($title_suffix); ?>
    <?php endif; ?>

    <div class="answer__user-info gl-g">

      <div class="gl-s-7-24 gl-s-sm-5-24 gl-s-md-5-24 gl-s-lg-3-24 gl-s-xl-2-24">
        <?php print theme('user_picture', array('account' => $author)); ?>
      </div>

      <div class="gl-s-17-24 gl-s-sm-19-24 gl-s-md-19-24 gl-s-lg-21-24 gl-s-xl-22-24">

        <div class="answer__user-name">
          <a
            href="<?php print url('user/' . $author->uid); ?>"><?php print $author->name; ?></a>
        </div>

        <div class="answer__date">Ответ
          дан <?php print format_date($node->created, 'ru_medium'); ?></div>

      </div>

    </div>

    <?php
    hide($content['comments']);
    hide($content['links']);
    unset($content['links']['node']['#links']['node-readmore']);
    print render($content);

    global $user;
    if ($user->uid == $node->uid || $user->uid == 1) {
      print '<a href="/node/'.$node->nid.'/edit>Редактировать ответ</a>';
    }
    ?>



    <?php print render($content['links']); ?>

    <?php print render($content['comments']); ?>
  </div>

</article>
