<?php
function ds_glisseo_three_regions() {
  return array(
    'label' => t('Glisseo - Three regions'),
    'regions' => array(
      'first' => t('First'),
      'second' => t('Second'),
      'third' => t('Third'),
    ),
    // Add this line if you're using DS 2.x for icon preview
    'image' => TRUE,
  );
}
