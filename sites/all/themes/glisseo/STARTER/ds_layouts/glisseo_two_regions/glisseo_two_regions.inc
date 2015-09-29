<?php
function ds_glisseo_two_regions() {
  return array(
    'label' => t('Glisseo - Two regions'),
    'regions' => array(
      'first' => t('First'),
      'second' => t('Second'),
    ),
    // Add this line if you're using DS 2.x for icon preview
    'image' => TRUE,
  );
}
