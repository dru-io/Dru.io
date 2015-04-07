<?php
function ds_question_full() {
  return array(
    'label' => 'Question - full',
    'regions' => array(
      'left' => t('Left'),
      'right' => t('Right'),
      'bottom' => t('Bottom'),
    ),
    // Add this line if you're using DS 2.x for icon preview
    'image' => TRUE,
  );
}
