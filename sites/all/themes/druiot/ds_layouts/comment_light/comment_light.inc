<?php

function ds_comment_light() {
  return array(
    'label' => 'Comment - light',
    'regions' => array(
      'left' => t('Left'),
      'right' => t('Right'),
    ),
    // Add this line if you're using DS 2.x for icon preview.
    'image' => TRUE,
  );
}
