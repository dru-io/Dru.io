<?php

/**
 * @file
 * Class DruioProjects
 * Class for manipulating projects.
 */
class DruioProjects {

  /**
   * Parse project name from url.
   */
  public function parseProjectNameFromUrl($url) {
    $parsed_url = parse_url($url);

    if ($parsed_url['host'] == 'drupal.org' || $parsed_url['host'] == 'www.drupal.org') {
      $path = explode('/', $parsed_url['path']);
      if ($path[1] == 'project' && !empty($path[2])) {
        return $path[2];
      }
      else {
        return FALSE;
      }
    }
    else {
      return FALSE;
    }
  }

  /**
   * @param $name - project machine name.
   * Send request and try to get project info.
   * Return JSON.
   * @return bool|string
   */
  private function sendRequest($name) {
    $ch = curl_init();

    $options = array(
      CURLOPT_URL => 'http://updates.drupal.org/release-history/' . $name . '/all',
      CURLOPT_HEADER => "Content-Type:application/xml",
      CURLOPT_RETURNTRANSFER => TRUE
    );

    curl_setopt_array($ch, $options);
    $data = curl_exec($ch);
    $status = curl_getinfo($ch)['http_code'];

    // If status Ok.
    if ($status == 200) {
      curl_close($ch);

      $xml_element = new SimpleXMLElement($data);
      $json = json_encode($xml_element);
      $array = json_decode($json, TRUE);

      if (count($array) <= 1) {
        // if drupal.org returns: 'No project found';
        return FALSE;
      }
      else {
        return $json;
      }
    }
    else {
      return FALSE;
    }
  }

  /**
   * Add new project.
   * Return DATA:
   * 1 - add successful.
   * 2 - drupal.org not response.
   * 3 - project already exist.
   */
  public function addProject($name) {
    $nodes = db_select('field_data_field_project_short_name', 'sn')
      ->fields('sn', array('entity_id'))
      ->condition('sn.entity_type', 'node', '=')
      ->condition('sn.bundle', 'project', '=')
      ->condition('sn.field_project_short_name_value', $name, '=')
      ->execute()
      ->fetchField();

    // Check if we have project or not.
    if (!empty($nodes)) {
      return 3;
    }
    // Create new project node.
    else {
      $result = $this->sendRequest($name);
      if ($result) {
        $result_array = json_decode($result, TRUE);

        $node = new stdClass();
        $node->type = 'project';
        node_object_prepare($node);

        $node->title = $result_array['title'];
        $node->language = LANGUAGE_NONE;

        $this->generateFieldData($node, $result_array);

        // Save new project.
        node_save($node);

        return 1;
      }
      else {
        return 2;
      }
    }
  }

  /**
   * Update project information.
   * Return DATA:
   * 1 - update successful.
   * 2 - drupal.org not response.
   * 3 - project does not exist.
   */
  public function updateProject($name) {
    $nodes = db_select('field_data_field_project_short_name', 'sn')
      ->fields('sn', array('entity_id'))
      ->condition('sn.entity_type', 'node', '=')
      ->condition('sn.bundle', 'project', '=')
      ->condition('sn.field_project_short_name_value', $name, '=')
      ->execute()
      ->fetchField();

    // Check if we have project or not.
    if (!empty($nodes)) {
      $result = $this->sendRequest($name);
      if ($result) {
        $result_array = json_decode($result, TRUE);

        $node = node_load($nodes);
        $node->title = $result_array['title'];
        $node->language = LANGUAGE_NONE;
        $this->generateFieldData($node, $result_array);
        node_save($node);

        return 1;
      }
      else {
        return 2;
      }
    }
    else {
      return 3;
    }
  }

  /**
   * Set field data.
   */
  private function generateFieldData(&$node, $data_array) {
    // Set terms
    foreach ($data_array['terms']['term'] as $term) {

      if ($term['name'] == 'Projects') {
        switch ($term['value']) {
          case 'Modules':
            $node->field_project_type[$node->language][0]['tid'] = 26;
            break;
          case 'Themes':
            $node->field_project_type[$node->language][0]['tid'] = 27;
            break;
          case 'Distributions':
            $node->field_project_type[$node->language][0]['tid'] = 25;
            break;
          case 'Drupal core':
            $node->field_project_type[$node->language][0]['tid'] = 24;
            break;
        }
      }
      elseif ($term['name'] == 'Maintenance status') {
        $term_load = taxonomy_get_term_by_name($term['value'], 'project_maintenance_status');
        if ($term_load) {
          $term_load = array_shift($term_load);
          $node->field_project_maintenance_status[$node->language][0]['tid'] = $term_load->tid;
        }
      }
      elseif ($term['name'] == 'Development status') {
        $term_load = taxonomy_get_term_by_name($term['value'], 'project_development_status');
        if ($term_load) {
          $term_load = array_shift($term_load);
          $node->field_project_development_status[$node->language][0]['tid'] = $term_load->tid;
        }
      }

      // Releases
      $releases_array = array();
      foreach ($data_array['releases']['release'] as $release) {
        preg_match('/(.\\..)/', $release['version'], $version);
        $releases_array[$version[0]][$release['version']] = array(
          'version' => $release['version'],
          'release_link' => $release['release_link'],
          'date' => $release['date'],
          'files' => array(
            'zip' => array(
              'url' => $release['files']['file'][0]['url'],
              'md5' => $release['files']['file'][0]['md5'],
              'size' => $release['files']['file'][0]['size'],
              'date' => $release['files']['file'][0]['filedate']
            ),
            'tar' => array(
              'url' => $release['files']['file'][0]['url'],
              'md5' => $release['files']['file'][0]['md5'],
              'size' => $release['files']['file'][0]['size'],
              'date' => $release['files']['file'][0]['filedate']
            ),
          )
        );
      }
      $node->field_project_releases[$node->language][0]['value'] = json_encode($releases_array);
    }

    // Drupal version
    foreach ($releases_array as $drupal_version => $release) {
      $term_load = taxonomy_get_term_by_name($drupal_version, 'drupal_versions');
      if ($term_load) {
        $term_load = array_shift($term_load);
        $node->field_drupal_version[$node->language][]['tid'] = $term_load->tid;
      }
    }

    // Other fields
    $node->field_project_short_name[$node->language][0]['value'] = $data_array['short_name'];
  }
}
