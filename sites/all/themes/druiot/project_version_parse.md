~~~
<?php
$release_values = field_get_items('node', $entity, 'field_project_releases');
$releases = json_decode($release_values[0]['value']);
$drupal_major_version_pattern = "/(.\\.x)/";
$combied_versions = array();
// Drupal 8 has no 8.x releases.
$combied_versions['8.x'] = array();

// Write major versions.
foreach ($releases as $key => $release) {
  if (preg_match($drupal_major_version_pattern, $key)) {
    $combied_versions[$key] = array();
  }
}

foreach ($releases as $key => $release) {
  if (!preg_match($drupal_major_version_pattern, $key)) {
    $version = $key[0] . '.x';
    if (isset($release->version)) {
      $combied_versions[$version][$key] = $release;
    }
    else {
      foreach($release as $sub_key => $sub_release) {
        $combied_versions[$version][$sub_key] = $sub_release;
      }
    }
  }
}

// sort
ksort($combied_versions);
foreach ($combied_versions as $key => $versions) {
  ksort($combied_versions[$key]);
}

dpm($combied_versions);
?>
~~~