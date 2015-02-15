<?php
/**
 * @file
 * Default simple view template to display a list of rows.
 *
 * @ingroup views_templates
 */
?>
<?php if (!empty($title)): ?>
  <h3><?php print $title; ?></h3>
<?php endif; ?>
<?php foreach ($rows as $id => $row): ?>
  <div class="<?php print $variables['view']->name; ?>-item <?php $id%2 == 0 ? print 'even' : print 'odd'; ?>">
    <?php print $row; ?>
  </div>
<?php endforeach; ?>