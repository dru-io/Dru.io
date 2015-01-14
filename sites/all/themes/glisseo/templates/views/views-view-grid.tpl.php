<?php
/**
 * @file
 * Вывод grid div'ами.
 */
?>
<?php if (!empty($title)) : ?>
  <h3><?php print $title; ?></h3>
<?php endif; ?>
<div class="<?php print $class; ?>"<?php print $attributes; ?>>
  <?php foreach ($rows as $row_number => $columns): ?>
    <div <?php if ($row_classes[$row_number]) { print 'class="row ' . $row_classes[$row_number] .'"';  } ?>>
      <?php foreach ($columns as $column_number => $item): ?>
        <div <?php if ($column_classes[$row_number][$column_number]) { print 'class="col ' . $column_classes[$row_number][$column_number] .'"';  } ?>>
          <?php if ($item): ?>
            <?php print $item; ?>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
</div>